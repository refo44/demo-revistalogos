#!/usr/bin/env bash
# Isolated Docker QA for ADR 0017 WU6A: Article source HTML + explicit
# end-to-end PDF composition. Never points at production and never
# reuses the primary local volumes.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
PLUGIN_DIR="$ROOT/wordpress/wp-content/plugins/revistalogos-core"
PROJECT="${LES_PDF_COMPOSITION_QA_PROJECT:-revistalogos-article-pdf-composition-qa}"
PORT="${LES_PDF_COMPOSITION_QA_PORT:-8088}"
BASE_URL="http://localhost:${PORT}"
TMP="$(mktemp -d "${TMPDIR:-/tmp}/les-article-pdf-composition-qa.XXXXXX")"
EVAL_HOST="$PLUGIN_DIR/.qa-article-pdf-composition-eval.php"
ADMIN_USER="les_pdf_composition_admin"
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
	--title="LOGO ET SPES PDF composition QA" \
	--admin_user="$ADMIN_USER" \
	--admin_password="$ADMIN_PASSWORD" \
	--admin_email="pdf-composition@example.invalid" \
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

echo "== source builder + explicit generation composition =="
cat >"$EVAL_HOST" <<'PHP'
<?php
if ( ! class_exists( 'Revistalogos_Core\\Article_Pdf_WordPress_Source_Builder' ) ) {
	fwrite( STDERR, "Article_Pdf_WordPress_Source_Builder not found\n" );
	exit( 1 );
}

$admin = get_user_by( 'login', getenv( 'QA_ADMIN' ) ?: 'les_pdf_composition_admin' );
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

$attachments_after_load = qa_count_attachments();
qa_ok( 0 === $attachments_after_load, 'plugin load/upgrade created no attachments' );

$author_id = wp_insert_post(
	array(
		'post_type'   => 'author',
		'post_title'  => 'QA Composition Author',
		'post_status' => 'publish',
		'post_name'   => 'qa-composition-author',
	),
	true
);
if ( is_wp_error( $author_id ) ) {
	fwrite( STDERR, "author create failed\n" );
	exit( 1 );
}

$title   = 'Ética y razón en España';
$body    = 'Filosofía, razón y ética en España: ñ á é í ó ú';
$content = '<!-- wp:paragraph --><p>' . $body . '</p><!-- /wp:paragraph -->';

$article_id = wp_insert_post(
	array(
		'post_type'    => 'article',
		'post_title'   => $title,
		'post_status'  => 'draft',
		'post_content' => $content,
		'post_name'    => 'qa-composition-source-article',
		'meta_input'   => array(
			'authors' => array( (int) $author_id ),
		),
	),
	true
);
if ( is_wp_error( $article_id ) ) {
	fwrite( STDERR, "article create failed\n" );
	exit( 1 );
}

$title_before   = get_the_title( $article_id );
$content_before = get_post_field( 'post_content', $article_id );
$count_before   = qa_count_attachments();

$builder = new Revistalogos_Core\Article_Pdf_WordPress_Source_Builder();
$html    = $builder->build( $article_id );

qa_ok( is_string( $html ) && '' !== $html, 'source builder returns non-empty string' );
qa_ok( is_string( $html ) && false !== stripos( $html, '<!DOCTYPE html>' ), 'source contains HTML document wrapper' );
qa_ok( is_string( $html ) && false !== strpos( $html, 'UTF-8' ), 'source declares UTF-8' );
qa_ok( is_string( $html ) && false !== strpos( $html, 'lang="es"' ), 'source declares lang=es' );
qa_ok( is_string( $html ) && false !== strpos( $html, $title ), 'source contains Article title' );
qa_ok( is_string( $html ) && false !== strpos( $html, $body ), 'source contains rendered body text' );
qa_ok( is_string( $html ) && false === strpos( $html, '<!-- wp:' ), 'source does not expose Gutenberg block comments' );
qa_ok( $title_before === get_the_title( $article_id ), 'source build leaves title unchanged' );
qa_ok( $content_before === get_post_field( 'post_content', $article_id ), 'source build leaves content unchanged' );
qa_ok( 'draft' === get_post_status( $article_id ), 'source build leaves Article draft' );
qa_ok( qa_count_attachments() === $count_before, 'source build creates no attachment' );
qa_ok( 0 === absint( get_post_meta( $article_id, 'pdf_file', true ) ), 'source build does not write pdf_file' );

$invalid_source = $builder->build( 999999 );
qa_ok( is_wp_error( $invalid_source ), 'invalid Article source returns WP_Error' );
qa_ok( is_wp_error( $invalid_source ) && 'article_pdf_invalid_article' === $invalid_source->get_error_code(), 'invalid Article source uses article_pdf_invalid_article' );

if ( ! class_exists( 'Revistalogos_Core\\Article_Pdf_WordPress_Generator' ) ) {
	fwrite( STDERR, "Article_Pdf_WordPress_Generator not found\n" );
	exit( 1 );
}

$generate_article_id = wp_insert_post(
	array(
		'post_type'    => 'article',
		'post_title'   => $title,
		'post_status'  => 'draft',
		'post_content' => $content,
		'post_name'    => 'qa-composition-generate-article',
		'meta_input'   => array(
			'authors' => array( (int) $author_id ),
		),
	),
	true
);
if ( is_wp_error( $generate_article_id ) ) {
	fwrite( STDERR, "generate article create failed\n" );
	exit( 1 );
}

$gen_title_before   = get_the_title( $generate_article_id );
$gen_content_before = get_post_field( 'post_content', $generate_article_id );
$gen_count_before   = qa_count_attachments();

$generator     = new Revistalogos_Core\Article_Pdf_WordPress_Generator();
$attachment_id = $generator->generate_for_article( $generate_article_id );

qa_ok( ! is_wp_error( $attachment_id ) && (int) $attachment_id > 0, 'generator returns positive attachment ID' );

$attachment    = ( (int) $attachment_id > 0 ) ? get_post( (int) $attachment_id ) : null;
$attached_file = ( (int) $attachment_id > 0 ) ? get_attached_file( (int) $attachment_id ) : '';
$signature     = ( is_string( $attached_file ) && is_readable( $attached_file ) )
	? substr( (string) file_get_contents( $attached_file ), 0, 5 )
	: '';
$pdf_file      = get_post_meta( $generate_article_id, 'pdf_file', true );
$gen_count_after = qa_count_attachments();

qa_ok( $attachment && 'application/pdf' === $attachment->post_mime_type, 'generated attachment MIME is application/pdf' );
qa_ok( $attachment && (int) $attachment->post_parent === (int) $generate_article_id, 'generated attachment parent is the Article' );
qa_ok( is_string( $attached_file ) && is_file( $attached_file ), 'generated physical file exists' );
qa_ok( '%PDF-' === $signature, 'generated file begins %PDF-' );
qa_ok( (int) $pdf_file === (int) $attachment_id, 'pdf_file equals generated attachment ID' );
qa_ok( $gen_count_after === $gen_count_before + 1, 'generator creates exactly one attachment' );
qa_ok( 'draft' === get_post_status( $generate_article_id ), 'generated Article remains draft' );
qa_ok( $gen_title_before === get_the_title( $generate_article_id ), 'generator leaves title unchanged' );
qa_ok( $gen_content_before === get_post_field( 'post_content', $generate_article_id ), 'generator leaves content unchanged' );

echo 'EVIDENCE article_id=' . (int) $generate_article_id
	. ' status=' . get_post_status( $generate_article_id )
	. ' attachment_id=' . (int) $attachment_id
	. ' mime=' . ( $attachment ? $attachment->post_mime_type : '' )
	. ' parent=' . ( $attachment ? (int) $attachment->post_parent : 0 )
	. ' signature=' . $signature
	. ' pdf_file=' . (int) $pdf_file
	. ' count_delta=' . ( $gen_count_after - $gen_count_before )
	. "\n";

$kept = $generator->generate_for_article( $generate_article_id );
qa_ok( (int) $kept === (int) $attachment_id, 'second generate returns the same attachment ID' );
qa_ok( qa_count_attachments() === $gen_count_after, 'second generate attachment delta is 0' );
qa_ok( (int) get_post_meta( $generate_article_id, 'pdf_file', true ) === (int) $attachment_id, 'second generate leaves pdf_file unchanged' );
qa_ok( get_post( (int) $attachment_id ), 'original attachment remains after second generate' );
qa_ok( 'draft' === get_post_status( $generate_article_id ), 'second generate leaves Article draft' );

echo 'EVIDENCE_KEEP existing_id=' . (int) $attachment_id
	. ' returned_id=' . (int) $kept
	. ' pdf_file_after=' . (int) get_post_meta( $generate_article_id, 'pdf_file', true )
	. ' count_delta=0'
	. "\n";

$article_pdf_before_wrong_type = (int) get_post_meta( $generate_article_id, 'pdf_file', true );
$article_status_before_wrong_type = get_post_status( $generate_article_id );
update_post_meta( $author_id, 'pdf_file', (int) $attachment_id );
$wrong_type_count = qa_count_attachments();
$wrong_type       = $generator->generate_for_article( $author_id );
qa_ok( is_wp_error( $wrong_type ), 'non-Article with valid pdf_file returns WP_Error' );
qa_ok( is_wp_error( $wrong_type ) && 'article_pdf_invalid_article' === $wrong_type->get_error_code(), 'non-Article with valid pdf_file uses article_pdf_invalid_article' );
qa_ok( qa_count_attachments() === $wrong_type_count, 'non-Article generate attachment delta is 0' );
qa_ok( get_post( (int) $attachment_id ), 'original valid attachment remains after non-Article generate' );
qa_ok( (int) get_post_meta( $generate_article_id, 'pdf_file', true ) === $article_pdf_before_wrong_type, 'real generated Article pdf_file unchanged after non-Article generate' );
qa_ok( $article_status_before_wrong_type === get_post_status( $generate_article_id ), 'real generated Article status unchanged after non-Article generate' );

echo 'EVIDENCE_WRONG_TYPE post_type=author existing_pdf=' . (int) $attachment_id
	. ' error=' . ( is_wp_error( $wrong_type ) ? $wrong_type->get_error_code() : 'none' )
	. ' count_delta=' . ( qa_count_attachments() - $wrong_type_count )
	. "\n";

$invalid_generate = $generator->generate_for_article( 999999 );
qa_ok( is_wp_error( $invalid_generate ), 'invalid Article generate returns WP_Error' );
qa_ok( is_wp_error( $invalid_generate ) && 'article_pdf_invalid_article' === $invalid_generate->get_error_code(), 'invalid Article generate uses article_pdf_invalid_article' );
qa_ok( qa_count_attachments() === $gen_count_after, 'invalid generate creates no attachment' );

$publish_ok = wp_insert_post(
	array(
		'post_type'    => 'article',
		'post_title'   => 'QA composition publish without PDF',
		'post_status'  => 'draft',
		'post_content' => 'Publish without calling the generator.',
		'post_name'    => 'qa-composition-publish-without-pdf',
		'meta_input'   => array(
			'authors' => array( (int) $author_id ),
		),
	),
	true
);
$publish_count = qa_count_attachments();
wp_update_post(
	array(
		'ID'          => $publish_ok,
		'post_status' => 'publish',
	),
	true
);
qa_ok( 'publish' === get_post_status( $publish_ok ), 'publish without PDF still allowed when author exists' );
qa_ok( 0 === absint( get_post_meta( $publish_ok, 'pdf_file', true ) ), 'publish without PDF does not write pdf_file' );
qa_ok( qa_count_attachments() === $publish_count, 'publish without explicit generate creates no attachment' );

$no_author = wp_insert_post(
	array(
		'post_type'    => 'article',
		'post_title'   => 'QA composition no author',
		'post_status'  => 'draft',
		'post_content' => 'No author.',
		'post_name'    => 'qa-composition-no-author',
	),
	true
);
wp_update_post(
	array(
		'ID'          => $no_author,
		'post_status' => 'publish',
	),
	true
);
qa_ok( 'publish' !== get_post_status( $no_author ), 'author publication guard still blocks publish' );

if ( $GLOBALS['qa_fail'] ) {
	exit( 1 );
}
PHP

cli eval-file wp-content/plugins/revistalogos-core/.qa-article-pdf-composition-eval.php
pass "source builder and explicit generator composition"

BUILDER="$PLUGIN_DIR/includes/article-pdf/class-article-pdf-wordpress-source-builder.php"
GENERATOR="$PLUGIN_DIR/includes/article-pdf/class-article-pdf-wordpress-generator.php"
echo "== static guards (no persistence, hooks, or enforcement) =="
[[ -f "$BUILDER" ]] || fail "Article_Pdf_WordPress_Source_Builder file missing"
[[ -f "$GENERATOR" ]] || fail "Article_Pdf_WordPress_Generator file missing"
if rg -q "add_action|add_filter" "$BUILDER" "$GENERATOR"; then
	fail "WU6A classes must not register WordPress hooks"
fi
if rg -q "wp_insert_post_data|rest_pre_insert|transition_post_status|register_setting|revistalogos_article_pdf_publication_enforcement" "$BUILDER" "$GENERATOR"; then
	fail "WU6A classes must not wire publication or settings"
fi
pass "WU6A classes have no hooks, settings, or publication wiring"

echo "== all article PDF composition QA checks passed =="
