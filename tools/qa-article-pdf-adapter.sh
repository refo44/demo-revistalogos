#!/usr/bin/env bash
# Isolated Docker QA for ADR 0017 WU2: read-only WordPress PDF adapter.
# Never points at production and never reuses the primary local volumes.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
PROJECT="${LES_PDF_ADAPTER_QA_PROJECT:-revistalogos-article-pdf-adapter-qa}"
PORT="${LES_PDF_ADAPTER_QA_PORT:-8085}"
BASE_URL="http://localhost:${PORT}"
TMP="$(mktemp -d "${TMPDIR:-/tmp}/les-article-pdf-adapter-qa.XXXXXX")"
EVAL_HOST="$ROOT/wordpress/wp-content/plugins/revistalogos-core/.qa-article-pdf-adapter-eval.php"
ADMIN_USER="les_pdf_adapter_admin"
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

echo "== static guards (no renderer, no generation APIs in adapter) =="
ADAPTER="$ROOT/wordpress/wp-content/plugins/revistalogos-core/includes/article-pdf/class-article-pdf-wordpress-adapter.php"
[[ -f "$ADAPTER" ]] || fail "Article_Pdf_WordPress_Adapter file missing"
if rg -q "media_handle_sideload|wp_insert_attachment|wp_generate_attachment_metadata|wp_update_attachment_metadata" "$ADAPTER"; then
	fail "adapter must not create or update attachments"
fi
if rg -q "update_post_meta|add_post_meta|delete_post_meta|wp_update_post|wp_insert_post" "$ADAPTER"; then
	fail "adapter must be read-only (no post/meta writes)"
fi
if rg -q "add_action|add_filter" "$ADAPTER"; then
	fail "adapter must not register WordPress hooks"
fi
pass "adapter source has no write/hook/generation APIs"

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
	--title="LOGO ET SPES PDF adapter QA" \
	--admin_user="$ADMIN_USER" \
	--admin_password="$ADMIN_PASSWORD" \
	--admin_email="pdf-adapter@example.invalid" \
	--skip-email >/dev/null
cli config set DISABLE_WP_CRON true --raw >/dev/null
cli theme activate revistalogos >/dev/null
cli plugin activate revistalogos-core >/dev/null

PLUGIN_VERSION="$(cli eval 'echo REVISTALOGOS_CORE_VERSION;')"
[[ "$PLUGIN_VERSION" == "0.2.7" ]] || fail "expected plugin 0.2.7, got $PLUGIN_VERSION"
pass "plugin 0.2.7 active in isolated WordPress"

echo "== adapter publication PDF decisions =="
cat >"$EVAL_HOST" <<'PHP'
<?php
if ( ! class_exists( 'Revistalogos_Core\\Article_Pdf_WordPress_Adapter' ) ) {
	fwrite( STDERR, "Article_Pdf_WordPress_Adapter not found\n" );
	exit( 1 );
}

$admin = get_user_by( 'login', getenv( 'QA_ADMIN' ) ?: 'les_pdf_adapter_admin' );
if ( ! $admin ) {
	$users = get_users( array( 'role' => 'administrator', 'number' => 1 ) );
	$admin = $users ? $users[0] : null;
}
if ( ! $admin ) {
	fwrite( STDERR, "no admin\n" );
	exit( 1 );
}
wp_set_current_user( $admin->ID );

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$GLOBALS['qa_fail'] = 0;
function qa_ok( $cond, $label ) {
	if ( $cond ) {
		echo "PASS $label\n";
	} else {
		echo "FAIL $label\n";
		$GLOBALS['qa_fail']++;
	}
}

$author_id = wp_insert_post(
	array(
		'post_type'   => 'author',
		'post_title'  => 'QA Adapter Author',
		'post_status' => 'publish',
		'post_name'   => 'qa-adapter-author',
	),
	true
);
if ( is_wp_error( $author_id ) ) {
	fwrite( STDERR, "author create failed\n" );
	exit( 1 );
}

$src = REVISTALOGOS_CORE_DIR . 'resources/fixtures/articulo-01.pdf';
$tmp = wp_tempnam( 'qa-adapter.pdf' );
copy( $src, $tmp );
$pdf_id = media_handle_sideload(
	array(
		'name'     => 'qa-adapter-article.pdf',
		'tmp_name' => $tmp,
	),
	0,
	'QA adapter PDF'
);
if ( is_wp_error( $pdf_id ) ) {
	fwrite( STDERR, "pdf sideload failed\n" );
	exit( 1 );
}

$jpeg_src = get_template_directory() . '/assets/img/placeholder-banner.jpg';
$jpeg_tmp = wp_tempnam( 'qa-adapter.jpg' );
copy( $jpeg_src, $jpeg_tmp );
$jpeg_id = media_handle_sideload(
	array(
		'name'     => 'qa-adapter-cover.jpg',
		'tmp_name' => $jpeg_tmp,
	),
	0,
	'QA adapter JPEG'
);
if ( is_wp_error( $jpeg_id ) ) {
	fwrite( STDERR, "jpeg sideload failed\n" );
	exit( 1 );
}

$with_pdf = wp_insert_post(
	array(
		'post_type'    => 'article',
		'post_title'   => 'QA adapter with PDF',
		'post_status'  => 'draft',
		'post_content' => 'Body with PDF.',
		'post_name'    => 'qa-adapter-with-pdf',
		'meta_input'   => array(
			'authors'  => array( (int) $author_id ),
			'pdf_file' => (int) $pdf_id,
		),
	),
	true
);
$without_pdf = wp_insert_post(
	array(
		'post_type'    => 'article',
		'post_title'   => 'QA adapter without PDF',
		'post_status'  => 'draft',
		'post_content' => 'Body without PDF.',
		'post_name'    => 'qa-adapter-without-pdf',
		'meta_input'   => array(
			'authors' => array( (int) $author_id ),
		),
	),
	true
);
$zero_pdf = wp_insert_post(
	array(
		'post_type'    => 'article',
		'post_title'   => 'QA adapter zero PDF',
		'post_status'  => 'draft',
		'post_content' => 'Body zero PDF.',
		'post_name'    => 'qa-adapter-zero-pdf',
		'meta_input'   => array(
			'authors'  => array( (int) $author_id ),
			'pdf_file' => 0,
		),
	),
	true
);

if ( is_wp_error( $with_pdf ) || is_wp_error( $without_pdf ) || is_wp_error( $zero_pdf ) ) {
	fwrite( STDERR, "article create failed\n" );
	exit( 1 );
}

$adapter = new Revistalogos_Core\Article_Pdf_WordPress_Adapter();
$policy  = new Revistalogos_Core\Article_Pdf_Publication_Policy();
$attachments_before = (int) wp_count_posts( 'attachment' )->inherit;
$pdf_before = absint( get_post_meta( $with_pdf, 'pdf_file', true ) );
$permalink_before = get_permalink( $with_pdf );

$keep = $adapter->decide_pdf_action_for_article( $with_pdf );
qa_ok( Revistalogos_Core\Article_Pdf_Publication_Policy::KEEP_EXISTING === $keep, 'valid pdf_file is KEEP_EXISTING' );
qa_ok( ! $policy->requests_generation( $keep ), 'valid pdf_file does not request generation' );
qa_ok( $pdf_before === absint( get_post_meta( $with_pdf, 'pdf_file', true ) ), 'valid path does not write pdf_file' );
qa_ok( $permalink_before === get_permalink( $with_pdf ), 'valid path does not change permalink' );
qa_ok( $attachments_before === (int) wp_count_posts( 'attachment' )->inherit, 'valid path creates no attachment' );

$missing = $adapter->decide_pdf_action_for_article( $without_pdf );
qa_ok( Revistalogos_Core\Article_Pdf_Publication_Policy::GENERATE_REQUIRED === $missing, 'missing pdf_file is GENERATE_REQUIRED' );
qa_ok( $policy->requests_generation( $missing ), 'missing pdf_file requests generation' );
qa_ok( 0 === absint( get_post_meta( $without_pdf, 'pdf_file', true ) ), 'missing path does not write pdf_file' );

$zero = $adapter->decide_pdf_action_for_article( $zero_pdf );
qa_ok( Revistalogos_Core\Article_Pdf_Publication_Policy::GENERATE_REQUIRED === $zero, 'pdf_file 0 is GENERATE_REQUIRED' );

$gone = $adapter->decide_pdf_action_for_article( $without_pdf, 999999 );
qa_ok( Revistalogos_Core\Article_Pdf_Publication_Policy::GENERATE_REQUIRED === $gone, 'missing attachment ID is GENERATE_REQUIRED' );
qa_ok( 0 === absint( get_post_meta( $without_pdf, 'pdf_file', true ) ), 'missing attachment check does not write' );

$jpeg = $adapter->decide_pdf_action_for_article( $without_pdf, $jpeg_id );
qa_ok( Revistalogos_Core\Article_Pdf_Publication_Policy::GENERATE_REQUIRED === $jpeg, 'non-PDF attachment is GENERATE_REQUIRED' );
qa_ok( 0 === absint( get_post_meta( $without_pdf, 'pdf_file', true ) ), 'JPEG check does not write pdf_file' );
qa_ok( 'attachment' === get_post_type( $jpeg_id ), 'JPEG attachment remains after read-only check' );

$draft_pdf = absint( get_post_meta( $without_pdf, 'pdf_file', true ) );
$draft_status = get_post_status( $without_pdf );
wp_update_post(
	array(
		'ID'           => $without_pdf,
		'post_content' => 'Ordinary draft save after adapter.',
		'post_status'  => 'draft',
	),
	true
);
qa_ok( 'draft' === get_post_status( $without_pdf ), 'ordinary save stays draft' );
qa_ok( $draft_pdf === absint( get_post_meta( $without_pdf, 'pdf_file', true ) ), 'ordinary save does not generate pdf_file' );
qa_ok( $draft_status === get_post_status( $without_pdf ), 'adapter does not change draft status' );

$published = wp_update_post(
	array(
		'ID'          => $without_pdf,
		'post_status' => 'publish',
	),
	true
);
qa_ok( ! is_wp_error( $published ) && 'publish' === get_post_status( $without_pdf ), 'publish without PDF still allowed when author exists' );
qa_ok( 0 === absint( get_post_meta( $without_pdf, 'pdf_file', true ) ), 'publish without PDF does not write pdf_file' );

$no_author = wp_insert_post(
	array(
		'post_type'    => 'article',
		'post_title'   => 'QA adapter no author',
		'post_status'  => 'draft',
		'post_content' => 'No author.',
		'post_name'    => 'qa-adapter-no-author',
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

$upgrade_pdf = absint( get_post_meta( $with_pdf, 'pdf_file', true ) );
$upgrade_status = get_post_status( $without_pdf );
Revistalogos_Core\Plugin::maybe_upgrade();
qa_ok( $upgrade_pdf === absint( get_post_meta( $with_pdf, 'pdf_file', true ) ), 'upgrade/load does not mutate pdf_file' );
qa_ok( $upgrade_status === get_post_status( $without_pdf ), 'upgrade/load does not unpublish' );
qa_ok( $attachments_before === (int) wp_count_posts( 'attachment' )->inherit, 'adapter session created no extra attachments after setup' );

if ( $GLOBALS['qa_fail'] ) {
	exit( 1 );
}
PHP

cli eval-file wp-content/plugins/revistalogos-core/.qa-article-pdf-adapter-eval.php
pass "read-only adapter decisions and publication composition"

echo "== all article PDF adapter QA checks passed =="
