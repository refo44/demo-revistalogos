#!/usr/bin/env bash
# Isolated Docker QA for ADR 0017 WU6B: admin-configurable Article PDF
# publication enforcement. Never points at production and never reuses
# the primary local volumes.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
PLUGIN_DIR="$ROOT/wordpress/wp-content/plugins/revistalogos-core"
PROJECT="${LES_PDF_ENFORCEMENT_QA_PROJECT:-revistalogos-article-pdf-enforcement-qa}"
PORT="${LES_PDF_ENFORCEMENT_QA_PORT:-8089}"
BASE_URL="http://localhost:${PORT}"
TMP="$(mktemp -d "${TMPDIR:-/tmp}/les-article-pdf-enforcement-qa.XXXXXX")"
EVAL_HOST="$PLUGIN_DIR/.qa-article-pdf-enforcement-eval.php"
ADMIN_USER="les_pdf_enforcement_admin"
ADMIN_PASSWORD="local-qa-admin-$(openssl rand -hex 8)"

cd "$ROOT"

compose() {
	WORDPRESS_PORT="$PORT" docker compose -p "$PROJECT" "$@"
}

cli() {
	compose run --rm wpcli wp --url="$BASE_URL" "$@"
}

fail() {
	echo "FAIL: $*" >&2
	exit 1
}

pass() {
	echo "PASS: $*"
}

cleanup() {
	rm -f "$EVAL_HOST"
	compose down -v --remove-orphans >/dev/null 2>&1 || true
	rm -rf "$TMP"
}
trap cleanup EXIT

echo "== plugin runtime Composer =="
if [[ ! -f "$PLUGIN_DIR/vendor/autoload.php" ]]; then
	if command -v composer >/dev/null 2>&1; then
		composer --working-dir="$PLUGIN_DIR" install --no-interaction --prefer-dist --no-progress
	else
		docker run --rm \
			--user "$(id -u):$(id -g)" \
			-e COMPOSER_HOME=/tmp/composer \
			--volume "$ROOT":/app \
			--workdir /app \
			composer:2 \
			composer --working-dir=wordpress/wp-content/plugins/revistalogos-core install --no-interaction --prefer-dist --no-progress
	fi
fi
[[ -f "$PLUGIN_DIR/vendor/autoload.php" ]] || fail "plugin vendor/autoload.php missing after install"
pass "plugin runtime dependency available (not tracked)"

echo "== isolated Docker environment =="
compose down -v --remove-orphans >/dev/null 2>&1 || true
compose up -d db wordpress

for _attempt in {1..30}; do
	if cli core version >/dev/null 2>&1; then
		break
	fi
	sleep 2
done

cli core version >/dev/null || fail "WordPress core did not become ready"
cli core install \
	--url="$BASE_URL" \
	--title="LOGO ET SPES PDF enforcement QA" \
	--admin_user="$ADMIN_USER" \
	--admin_password="$ADMIN_PASSWORD" \
	--admin_email="pdf-enforcement@example.invalid" \
	--skip-email >/dev/null
cli config set DISABLE_WP_CRON true --raw >/dev/null
cli theme activate revistalogos >/dev/null
cli plugin activate revistalogos-core >/dev/null

PLUGIN_VERSION="$(cli eval 'echo REVISTALOGOS_CORE_VERSION;')"
[[ "$PLUGIN_VERSION" == "0.2.6" ]] || fail "expected plugin 0.2.6, got $PLUGIN_VERSION"
WP_VERSION="$(cli core version)"
WPCLI_PHP="$(cli eval 'echo PHP_VERSION;')"
echo "WordPress $WP_VERSION / WP-CLI PHP $WPCLI_PHP"
[[ "$WP_VERSION" == "7.1" ]] || fail "expected WordPress 7.1, got $WP_VERSION"
[[ "$WPCLI_PHP" == 8.3.* ]] || fail "expected WP-CLI PHP 8.3, got $WPCLI_PHP"
pass "plugin 0.2.6 active in isolated WordPress 7.1 / WP-CLI PHP 8.3"

echo "== publication enforcement acceptance =="
cat >"$EVAL_HOST" <<'PHP'
<?php
if ( ! class_exists( 'Revistalogos_Core\\Article_Pdf_Publication_Settings' ) ) {
	fwrite( STDERR, "Article_Pdf_Publication_Settings not found\n" );
	exit( 1 );
}
if ( ! class_exists( 'Revistalogos_Core\\Article_Pdf_Publication_Enforcer' ) ) {
	fwrite( STDERR, "Article_Pdf_Publication_Enforcer not found\n" );
	exit( 1 );
}

$admin = get_user_by( 'login', getenv( 'QA_ADMIN' ) ?: 'les_pdf_enforcement_admin' );
if ( ! $admin ) {
	$users = get_users( array( 'role' => 'administrator', 'number' => 1 ) );
	$admin = $users ? $users[0] : null;
}
if ( ! $admin ) {
	fwrite( STDERR, "no admin\n" );
	exit( 1 );
}
wp_set_current_user( $admin->ID );

$GLOBALS['qa_fail'] = 0;
function qa_ok( $cond, $label ) {
	if ( $cond ) {
		echo "PASS $label\n";
	} else {
		echo "FAIL $label\n";
		$GLOBALS['qa_fail']++;
	}
}

function qa_count_attachments() {
	return count(
		get_posts(
			array(
				'post_type'      => 'attachment',
				'post_status'    => 'any',
				'posts_per_page' => -1,
				'fields'         => 'ids',
			)
		)
	);
}

function qa_reset_editor_post() {
	$_POST = array();
}

function qa_create_article( $slug, $title, $content, $author_id, $status = 'draft' ) {
	qa_reset_editor_post();
	$id = wp_insert_post(
		array(
			'post_type'    => 'article',
			'post_title'   => $title,
			'post_status'  => $status,
			'post_content' => $content,
			'post_name'    => $slug,
			'meta_input'   => $author_id ? array( 'authors' => array( (int) $author_id ) ) : array(),
		),
		true
	);
	if ( is_wp_error( $id ) ) {
		fwrite( STDERR, "article create failed: " . $id->get_error_message() . "\n" );
		exit( 1 );
	}
	return (int) $id;
}

function qa_prepare_metabox_post( $article_id, $authors, $pdf_file ) {
	$_POST = array();
	$_POST['revistalogos_core_meta_nonce'] = wp_create_nonce( 'revistalogos_core_meta' );
	foreach ( array( 'title_en', 'abstract', 'abstract_en', 'doi', 'pages', 'language', 'publication_date', 'received_date', 'accepted_date' ) as $key ) {
		$_POST[ $key ] = (string) get_post_meta( $article_id, $key, true );
	}
	$_POST['authors']  = $authors;
	$_POST['pdf_file'] = (string) (int) $pdf_file;
	$_POST['issue']    = (string) get_post_meta( $article_id, 'issue', true );
}

function qa_classic_publish( $article_id, $title, $content, $authors, $pdf_file = 0 ) {
	qa_prepare_metabox_post( $article_id, $authors, $pdf_file );
	return wp_update_post(
		array(
			'ID'           => $article_id,
			'post_title'   => $title,
			'post_content' => $content,
			'post_status'  => 'publish',
		),
		true
	);
}

function qa_make_pdf_attachment( $parent_id, $slug ) {
	$upload = wp_upload_bits( $slug . '.pdf', null, "%PDF-1.4\n%QA-manual\n" );
	if ( ! empty( $upload['error'] ) || empty( $upload['file'] ) ) {
		fwrite( STDERR, "manual PDF upload failed\n" );
		exit( 1 );
	}
	$id = wp_insert_attachment(
		array(
			'post_mime_type' => 'application/pdf',
			'post_title'     => $slug,
			'post_status'    => 'inherit',
			'post_parent'    => (int) $parent_id,
		),
		$upload['file'],
		(int) $parent_id
	);
	if ( is_wp_error( $id ) || (int) $id <= 0 ) {
		fwrite( STDERR, "manual PDF attachment failed\n" );
		exit( 1 );
	}
	return (int) $id;
}

function qa_rest_publish( $article_id, $title, $content, $meta = array() ) {
	qa_reset_editor_post();
	$route = function_exists( 'rest_get_route_for_post' ) ? rest_get_route_for_post( $article_id ) : '';
	if ( ! is_string( $route ) || '' === $route ) {
		$route = '/wp/v2/article/' . (int) $article_id;
	}
	$request = new WP_REST_Request( 'POST', $route );
	$request->set_param( 'status', 'publish' );
	$request->set_param( 'title', $title );
	$request->set_param( 'content', $content );
	if ( $meta ) {
		$request->set_param( 'meta', $meta );
	}
	return rest_do_request( $request );
}

function qa_rest_error( $response ) {
	if ( is_wp_error( $response ) ) {
		return $response;
	}
	if ( $response instanceof WP_REST_Response && $response->is_error() ) {
		return $response->as_error();
	}
	return null;
}

if ( ! did_action( 'admin_menu' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
	global $menu, $submenu;
	if ( ! is_array( $menu ) ) {
		$menu = array();
	}
	if ( ! is_array( $submenu ) ) {
		$submenu = array();
	}
	do_action( 'admin_menu' );
}
if ( ! did_action( 'admin_init' ) ) {
	do_action( 'admin_init' );
}

delete_option( Revistalogos_Core\Article_Pdf_Publication_Settings::OPTION_NAME );
qa_ok( false === Revistalogos_Core\Article_Pdf_Publication_Settings::is_enabled(), 'missing option means OFF' );
qa_ok( false === (bool) get_option( Revistalogos_Core\Article_Pdf_Publication_Settings::OPTION_NAME, 0 ), 'get_option default is 0' );

$registered = get_registered_settings();
qa_ok( isset( $registered[ Revistalogos_Core\Article_Pdf_Publication_Settings::OPTION_NAME ] ), 'setting is registered' );

global $submenu;
$found_page = false;
if ( isset( $submenu['options-general.php'] ) ) {
	foreach ( $submenu['options-general.php'] as $item ) {
		if ( isset( $item[2] ) && 'revistalogos-settings' === $item[2] ) {
			$found_page = ( isset( $item[1] ) && 'manage_options' === $item[1] );
		}
	}
}
qa_ok( $found_page, 'Settings → LOGO ET SPES registered with manage_options' );

$author_id = wp_insert_post(
	array(
		'post_type'   => 'author',
		'post_title'  => 'QA Enforcement Author',
		'post_status' => 'publish',
		'post_name'   => 'qa-enforcement-author',
	),
	true
);
if ( is_wp_error( $author_id ) ) {
	fwrite( STDERR, "author create failed\n" );
	exit( 1 );
}

$old_title   = 'Título anterior';
$old_body    = 'Contenido anterior';
$new_title   = 'Ética y razón en España';
$new_body    = 'Contenido nuevo: filosofía, ñ á é í ó ú';
$old_content = '<!-- wp:paragraph --><p>' . $old_body . '</p><!-- /wp:paragraph -->';
$new_content = '<!-- wp:paragraph --><p>' . $new_body . '</p><!-- /wp:paragraph -->';

$builder = new Revistalogos_Core\Article_Pdf_WordPress_Source_Builder();
$cand_id = qa_create_article( 'qa-enforcement-candidate-source', $old_title, $old_content, $author_id );
$cand_html = $builder->build_for_publication( $cand_id, $new_title, $new_content );
qa_ok( is_string( $cand_html ) && false !== strpos( $cand_html, $new_title ), 'candidate source uses new title' );
qa_ok( is_string( $cand_html ) && false !== strpos( $cand_html, $new_body ), 'candidate source uses new body' );
qa_ok( is_string( $cand_html ) && false === strpos( $cand_html, $old_body ), 'candidate source does not use stale body as primary content' );
qa_ok( 'draft' === get_post_status( $cand_id ), 'candidate source build leaves draft' );

$toggle_id = qa_create_article( 'qa-enforcement-toggle', 'Toggle article', $old_content, $author_id );
$toggle_status = get_post_status( $toggle_id );
$toggle_pdf    = absint( get_post_meta( $toggle_id, 'pdf_file', true ) );
$toggle_count  = qa_count_attachments();
update_option( Revistalogos_Core\Article_Pdf_Publication_Settings::OPTION_NAME, 1 );
qa_ok( true === Revistalogos_Core\Article_Pdf_Publication_Settings::is_enabled(), 'option 1 means ON' );
qa_ok( $toggle_status === get_post_status( $toggle_id ), 'OFF→ON does not change status' );
qa_ok( $toggle_pdf === absint( get_post_meta( $toggle_id, 'pdf_file', true ) ), 'OFF→ON does not change pdf_file' );
qa_ok( $toggle_count === qa_count_attachments(), 'OFF→ON creates no attachment' );
update_option( Revistalogos_Core\Article_Pdf_Publication_Settings::OPTION_NAME, 0 );
qa_ok( false === Revistalogos_Core\Article_Pdf_Publication_Settings::is_enabled(), 'option 0 means OFF' );
qa_ok( $toggle_status === get_post_status( $toggle_id ), 'ON→OFF does not change status' );
qa_ok( $toggle_pdf === absint( get_post_meta( $toggle_id, 'pdf_file', true ) ), 'ON→OFF does not change pdf_file' );
qa_ok( $toggle_count === qa_count_attachments(), 'ON→OFF deletes no attachment' );
delete_option( Revistalogos_Core\Article_Pdf_Publication_Settings::OPTION_NAME );

$off_classic = qa_create_article( 'qa-enforcement-off-classic', $old_title, $old_content, $author_id );
$off_count   = qa_count_attachments();
qa_classic_publish( $off_classic, $new_title, $new_content, array( $author_id ), 0 );
qa_ok( 'publish' === get_post_status( $off_classic ), 'OFF classic publish without PDF succeeds' );
qa_ok( 0 === absint( get_post_meta( $off_classic, 'pdf_file', true ) ), 'OFF classic publish leaves pdf_file empty' );
qa_ok( qa_count_attachments() === $off_count, 'OFF classic publish creates no attachment' );

$off_rest = qa_create_article( 'qa-enforcement-off-rest', $old_title, $old_content, $author_id );
$off_rest_count = qa_count_attachments();
$off_rest_res   = qa_rest_publish( $off_rest, $new_title, $new_content );
qa_ok( ! $off_rest_res->is_error(), 'OFF REST publish without PDF succeeds' );
qa_ok( 'publish' === get_post_status( $off_rest ), 'OFF REST publish status is publish' );
qa_ok( 0 === absint( get_post_meta( $off_rest, 'pdf_file', true ) ), 'OFF REST publish leaves pdf_file empty' );
qa_ok( qa_count_attachments() === $off_rest_count, 'OFF REST publish creates no attachment' );

update_option( Revistalogos_Core\Article_Pdf_Publication_Settings::OPTION_NAME, 1 );

$on_classic = qa_create_article( 'qa-enforcement-on-classic', $old_title, $old_content, $author_id );
$on_count   = qa_count_attachments();
qa_classic_publish( $on_classic, $new_title, $new_content, array( $author_id ), 0 );
$on_pdf = absint( get_post_meta( $on_classic, 'pdf_file', true ) );
$on_att = $on_pdf ? get_post( $on_pdf ) : null;
$on_file = $on_pdf ? get_attached_file( $on_pdf ) : '';
$on_sig  = ( is_string( $on_file ) && is_readable( $on_file ) ) ? substr( (string) file_get_contents( $on_file ), 0, 5 ) : '';
qa_ok( 'publish' === get_post_status( $on_classic ), 'ON classic publish generates and publishes' );
qa_ok( $on_pdf > 0, 'ON classic pdf_file is attachment ID' );
qa_ok( $on_att && 'application/pdf' === $on_att->post_mime_type, 'ON classic MIME is application/pdf' );
qa_ok( $on_att && (int) $on_att->post_parent === $on_classic, 'ON classic attachment parent is Article' );
qa_ok( '%PDF-' === $on_sig, 'ON classic file begins %PDF-' );
qa_ok( qa_count_attachments() === $on_count + 1, 'ON classic attachment delta +1' );
qa_ok( $new_title === get_the_title( $on_classic ), 'ON classic stored title is candidate title' );
qa_ok( false !== strpos( (string) get_post_field( 'post_content', $on_classic ), $new_body ), 'ON classic stored content is candidate content' );
echo 'EVIDENCE_CLASSIC article=' . $on_classic . ' pdf_file=' . $on_pdf . ' delta=' . ( qa_count_attachments() - $on_count ) . "\n";

$keep_classic = qa_create_article( 'qa-enforcement-keep-classic', $old_title, $old_content, $author_id );
$keep_att     = qa_make_pdf_attachment( $keep_classic, 'qa-keep-classic' );
update_post_meta( $keep_classic, 'pdf_file', $keep_att );
$keep_count = qa_count_attachments();
qa_classic_publish( $keep_classic, $new_title, $new_content, array( $author_id ), $keep_att );
qa_ok( 'publish' === get_post_status( $keep_classic ), 'ON classic KEEP publish succeeds' );
qa_ok( $keep_att === absint( get_post_meta( $keep_classic, 'pdf_file', true ) ), 'ON classic KEEP preserves pdf_file' );
qa_ok( qa_count_attachments() === $keep_count, 'ON classic KEEP attachment delta 0' );
qa_ok( get_post( $keep_att ), 'ON classic KEEP original attachment remains' );

$manual_classic = qa_create_article( 'qa-enforcement-manual-classic', $old_title, $old_content, $author_id );
$manual_att     = qa_make_pdf_attachment( $manual_classic, 'qa-manual-classic' );
$manual_count   = qa_count_attachments();
qa_classic_publish( $manual_classic, $new_title, $new_content, array( $author_id ), $manual_att );
qa_ok( 'publish' === get_post_status( $manual_classic ), 'ON classic same-request manual PDF publishes' );
qa_ok( $manual_att === absint( get_post_meta( $manual_classic, 'pdf_file', true ) ), 'ON classic same-request manual PDF is pdf_file' );
qa_ok( qa_count_attachments() === $manual_count, 'ON classic same-request manual PDF creates no generated attachment' );

$fail_filter = static function ( $dirs ) {
	$dirs['error']   = 'QA forced upload failure';
	$dirs['path']    = '/this/path/does/not/exist/les-qa-pdf';
	$dirs['basedir'] = '/this/path/does/not/exist/les-qa-pdf';
	return $dirs;
};
add_filter( 'upload_dir', $fail_filter );
$fail_classic = qa_create_article( 'qa-enforcement-fail-classic', $old_title, $old_content, $author_id );
$fail_count   = qa_count_attachments();
qa_classic_publish( $fail_classic, $new_title, $new_content, array( $author_id ), 0 );
remove_filter( 'upload_dir', $fail_filter );
qa_ok( 'publish' !== get_post_status( $fail_classic ), 'ON classic forced failure stays unpublished' );
qa_ok( 0 === absint( get_post_meta( $fail_classic, 'pdf_file', true ) ), 'ON classic forced failure leaves pdf_file empty' );
qa_ok( qa_count_attachments() === $fail_count, 'ON classic forced failure leaves no attachment' );
$fail_location = apply_filters( 'redirect_post_location', 'http://example.test/wp-admin/post.php?post=' . $fail_classic . '&action=edit' );
qa_ok( false !== strpos( $fail_location, 'revistalogos_notice=cannot_publish_pdf' ), 'classic failure redirect carries cannot_publish_pdf' );
$_GET['revistalogos_notice'] = 'cannot_publish_pdf';
ob_start();
Revistalogos_Core\Article_Pdf_Publication_Enforcer::render_admin_notices();
$notice_html = ob_get_clean();
unset( $_GET['revistalogos_notice'] );
qa_ok( false !== strpos( $notice_html, 'No se pudo generar el PDF del artículo' ), 'classic failure notice is actionable' );
qa_ok( false !== strpos( $notice_html, 'no fue publicado' ), 'classic failure notice says publication did not occur' );
qa_ok( false !== strpos( $notice_html, 'adjunta un PDF' ), 'classic failure notice offers manual PDF' );

$on_rest = qa_create_article( 'qa-enforcement-on-rest', $old_title, $old_content, $author_id );
$on_rest_count = qa_count_attachments();
$on_rest_res   = qa_rest_publish( $on_rest, $new_title, $new_content );
$on_rest_pdf   = absint( get_post_meta( $on_rest, 'pdf_file', true ) );
$on_rest_att   = $on_rest_pdf ? get_post( $on_rest_pdf ) : null;
$on_rest_file  = $on_rest_pdf ? get_attached_file( $on_rest_pdf ) : '';
$on_rest_sig   = ( is_string( $on_rest_file ) && is_readable( $on_rest_file ) ) ? substr( (string) file_get_contents( $on_rest_file ), 0, 5 ) : '';
qa_ok( ! $on_rest_res->is_error(), 'ON REST publish without PDF succeeds' );
qa_ok( 'publish' === get_post_status( $on_rest ), 'ON REST publish status is publish' );
qa_ok( $on_rest_pdf > 0, 'ON REST pdf_file is attachment ID' );
qa_ok( $on_rest_att && 'application/pdf' === $on_rest_att->post_mime_type, 'ON REST MIME is application/pdf' );
qa_ok( '%PDF-' === $on_rest_sig, 'ON REST file begins %PDF-' );
qa_ok( qa_count_attachments() === $on_rest_count + 1, 'ON REST attachment delta +1 not +2' );
echo 'EVIDENCE_REST article=' . $on_rest . ' pdf_file=' . $on_rest_pdf . ' delta=' . ( qa_count_attachments() - $on_rest_count ) . "\n";

$no_author = qa_create_article( 'qa-enforcement-no-author-classic', $old_title, $old_content, 0 );
$no_author_count = qa_count_attachments();
qa_classic_publish( $no_author, $new_title, $new_content, array(), 0 );
qa_ok( 'publish' !== get_post_status( $no_author ), 'ON classic authorless publish is blocked' );
qa_ok( 0 === absint( get_post_meta( $no_author, 'pdf_file', true ) ), 'ON classic authorless creates no pdf_file' );
qa_ok( qa_count_attachments() === $no_author_count, 'ON classic authorless creates no PDF attachment' );

$no_author_rest = qa_create_article( 'qa-enforcement-no-author-rest', $old_title, $old_content, 0 );
$no_author_rest_count = qa_count_attachments();
$no_author_rest_res   = qa_rest_publish( $no_author_rest, $new_title, $new_content );
qa_ok( $no_author_rest_res->is_error(), 'ON REST authorless publish is blocked' );
qa_ok( 'publish' !== get_post_status( $no_author_rest ), 'ON REST authorless stays unpublished' );
qa_ok( 0 === absint( get_post_meta( $no_author_rest, 'pdf_file', true ) ), 'ON REST authorless creates no pdf_file' );
qa_ok( qa_count_attachments() === $no_author_rest_count, 'ON REST authorless creates no PDF attachment' );

add_filter( 'upload_dir', $fail_filter );
$fail_rest = qa_create_article( 'qa-enforcement-fail-rest', $old_title, $old_content, $author_id );
$fail_rest_count = qa_count_attachments();
$fail_rest_res   = qa_rest_publish( $fail_rest, $new_title, $new_content );
remove_filter( 'upload_dir', $fail_filter );
$fail_rest_err = qa_rest_error( $fail_rest_res );
qa_ok( null !== $fail_rest_err, 'ON REST forced failure returns error' );
qa_ok( $fail_rest_err && 'article_pdf_publication_blocked' === $fail_rest_err->get_error_code(), 'ON REST failure code is article_pdf_publication_blocked' );
qa_ok( $fail_rest_res instanceof WP_REST_Response && $fail_rest_res->get_status() >= 400, 'ON REST failure is non-2xx' );
qa_ok( $fail_rest_err && false !== strpos( $fail_rest_err->get_error_message(), 'No se pudo generar el PDF' ), 'ON REST failure message is actionable' );
qa_ok( 'publish' !== get_post_status( $fail_rest ), 'ON REST forced failure stays unpublished' );
qa_ok( 0 === absint( get_post_meta( $fail_rest, 'pdf_file', true ) ), 'ON REST forced failure leaves pdf_file empty' );
qa_ok( qa_count_attachments() === $fail_rest_count, 'ON REST forced failure leaves no attachment' );

$published_save = $on_classic;
$published_pdf  = absint( get_post_meta( $published_save, 'pdf_file', true ) );
$published_count = qa_count_attachments();
qa_prepare_metabox_post( $published_save, array( $author_id ), $published_pdf );
wp_update_post(
	array(
		'ID'           => $published_save,
		'post_content' => $new_content . "\n<!-- wp:paragraph --><p>Edición posterior</p><!-- /wp:paragraph -->",
		'post_status'  => 'publish',
	),
	true
);
qa_ok( $published_pdf === absint( get_post_meta( $published_save, 'pdf_file', true ) ), 'already-published save does not change pdf_file' );
qa_ok( qa_count_attachments() === $published_count, 'already-published save creates no attachment' );

Revistalogos_Core\Relationships::$skip_article_publish_guard = true;
$published_empty = qa_create_article( 'qa-enforcement-published-empty', $old_title, $old_content, $author_id, 'publish' );
Revistalogos_Core\Relationships::$skip_article_publish_guard = false;
$published_empty_count = qa_count_attachments();
qa_prepare_metabox_post( $published_empty, array( $author_id ), 0 );
wp_update_post(
	array(
		'ID'           => $published_empty,
		'post_content' => $new_content,
		'post_status'  => 'publish',
	),
	true
);
qa_ok( 0 === absint( get_post_meta( $published_empty, 'pdf_file', true ) ), 'already-published empty PDF ordinary save does not generate' );
qa_ok( qa_count_attachments() === $published_empty_count, 'already-published empty PDF ordinary save creates no attachment' );

$draft_save = qa_create_article( 'qa-enforcement-draft-save', $old_title, $old_content, $author_id );
$draft_count = qa_count_attachments();
qa_prepare_metabox_post( $draft_save, array( $author_id ), 0 );
wp_update_post(
	array(
		'ID'           => $draft_save,
		'post_content' => $new_content,
		'post_status'  => 'draft',
	),
	true
);
qa_ok( 'draft' === get_post_status( $draft_save ), 'ON draft save stays draft' );
qa_ok( 0 === absint( get_post_meta( $draft_save, 'pdf_file', true ) ), 'ON draft save does not write pdf_file' );
qa_ok( qa_count_attachments() === $draft_count, 'ON draft save creates no attachment' );

$pending_save = qa_create_article( 'qa-enforcement-pending-save', $old_title, $old_content, $author_id, 'pending' );
$pending_count = qa_count_attachments();
qa_prepare_metabox_post( $pending_save, array( $author_id ), 0 );
wp_update_post(
	array(
		'ID'           => $pending_save,
		'post_content' => $new_content,
		'post_status'  => 'pending',
	),
	true
);
qa_ok( 'pending' === get_post_status( $pending_save ), 'ON pending save stays pending' );
qa_ok( 0 === absint( get_post_meta( $pending_save, 'pdf_file', true ) ), 'ON pending save does not write pdf_file' );
qa_ok( qa_count_attachments() === $pending_count, 'ON pending save creates no attachment' );

if ( $GLOBALS['qa_fail'] ) {
	exit( 1 );
}
PHP

cli eval-file wp-content/plugins/revistalogos-core/.qa-article-pdf-enforcement-eval.php
pass "publication enforcement acceptance"

SETTINGS="$PLUGIN_DIR/includes/article-pdf/class-article-pdf-publication-settings.php"
ENFORCER="$PLUGIN_DIR/includes/article-pdf/class-article-pdf-publication-enforcer.php"
echo "== static guards =="
[[ -f "$SETTINGS" ]] || fail "settings class missing"
[[ -f "$ENFORCER" ]] || fail "enforcer class missing"
if rg -q "wp_insert_post_data|rest_pre_insert|transition_post_status" "$SETTINGS"; then
	fail "settings class must not own publication hooks"
fi
pass "settings/enforcer files present"

echo "== all article PDF publication enforcement QA checks passed =="
