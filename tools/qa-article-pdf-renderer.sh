#!/usr/bin/env bash
# Isolated Docker QA for ADR 0017 WU4: real in-memory PDF renderer.
# Never points at production and never reuses the primary local volumes.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
PLUGIN_DIR="$ROOT/wordpress/wp-content/plugins/revistalogos-core"
PROJECT="${LES_PDF_RENDERER_QA_PROJECT:-revistalogos-article-pdf-renderer-qa}"
PORT="${LES_PDF_RENDERER_QA_PORT:-8086}"
BASE_URL="http://localhost:${PORT}"
TMP="$(mktemp -d "${TMPDIR:-/tmp}/les-article-pdf-renderer-qa.XXXXXX")"
EVAL_HOST="$PLUGIN_DIR/.qa-article-pdf-renderer-eval.php"
EVAL_APACHE="$PLUGIN_DIR/.qa-article-pdf-renderer-apache.php"
ADMIN_USER="les_pdf_renderer_admin"
ADMIN_PASSWORD="local-qa-admin-$(openssl rand -hex 8)"

cd "$ROOT"

compose() {
	WORDPRESS_PORT="$PORT" docker compose -p "$PROJECT" "$@"
}

cli() {
	compose run --rm wpcli wp --url="$BASE_URL" "$@"
}

wp_runtime() {
	compose exec -T wordpress php "$@"
}

fail() {
	echo "FAIL: $*" >&2
	exit 1
}

pass() {
	echo "PASS: $*"
}

cleanup() {
	rm -f "$EVAL_HOST" "$EVAL_APACHE"
	compose down -v --remove-orphans >/dev/null 2>&1 || true
	rm -rf "$TMP"
}
trap cleanup EXIT

echo "== static guards (no persistence in concrete renderer) =="
RENDERER="$PLUGIN_DIR/includes/article-pdf/class-dompdf-article-pdf-renderer.php"
[[ -f "$RENDERER" ]] || fail "Dompdf_Article_Pdf_Renderer file missing"
if grep -Eq "media_handle_sideload|wp_insert_attachment|wp_generate_attachment_metadata|wp_update_attachment_metadata" "$RENDERER"; then
	fail "renderer must not create or update attachments"
fi
if grep -Eq "update_post_meta|add_post_meta|delete_post_meta|wp_update_post|wp_insert_post" "$RENDERER"; then
	fail "renderer must not write posts or meta"
fi
if grep -Eq "add_action|add_filter" "$RENDERER"; then
	fail "renderer must not register WordPress hooks"
fi
pass "renderer source has no write/hook/persistence APIs"

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
[[ -f "$PLUGIN_DIR/vendor/dompdf/dompdf/src/Dompdf.php" ]] || fail "dompdf package missing after install"
pass "plugin runtime dependency installed (not tracked)"

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
	--title="LOGO ET SPES PDF renderer QA" \
	--admin_user="$ADMIN_USER" \
	--admin_password="$ADMIN_PASSWORD" \
	--admin_email="pdf-renderer@example.invalid" \
	--skip-email >/dev/null
cli config set DISABLE_WP_CRON true --raw >/dev/null
cli theme activate revistalogos >/dev/null
cli plugin activate revistalogos-core >/dev/null

PLUGIN_VERSION="$(cli eval 'echo REVISTALOGOS_CORE_VERSION;')"
[[ "$PLUGIN_VERSION" == "0.2.9" ]] || fail "expected plugin 0.2.9, got $PLUGIN_VERSION"
WP_VERSION="$(cli core version)"
WPCLI_PHP="$(cli eval 'echo PHP_VERSION;')"
echo "WordPress $WP_VERSION / WP-CLI PHP $WPCLI_PHP"
[[ "$WP_VERSION" == "7.1" ]] || fail "expected WordPress 7.1, got $WP_VERSION"
[[ "$WPCLI_PHP" == 8.3.* ]] || fail "expected WP-CLI PHP 8.3, got $WPCLI_PHP"
pass "plugin 0.2.9 active in isolated WordPress 7.1 / WP-CLI PHP 8.3"

echo "== required PHP extensions (WP-CLI container) =="
WPCLI_EXT="$(cli eval 'foreach ( array( "dom", "mbstring" ) as $ext ) { echo $ext, "=", extension_loaded( $ext ) ? "yes" : "no", "\n"; }')"
echo "$WPCLI_EXT"
echo "$WPCLI_EXT" | grep -q '^dom=yes$' || fail "WP-CLI ext-dom missing"
echo "$WPCLI_EXT" | grep -q '^mbstring=yes$' || fail "WP-CLI ext-mbstring missing"
pass "required renderer extensions loaded in WP-CLI"

echo "== WordPress Apache-container PHP runtime =="
APACHE_PHP="$(wp_runtime -r 'echo PHP_VERSION;')"
echo "WordPress Apache-container PHP $APACHE_PHP"
[[ "$APACHE_PHP" == 8.3.* ]] || fail "expected Apache-container PHP 8.3, got $APACHE_PHP"
APACHE_EXT="$(wp_runtime -r 'foreach ( array( "dom", "mbstring" ) as $ext ) { echo $ext, "=", extension_loaded( $ext ) ? "yes" : "no", "\n"; }')"
echo "$APACHE_EXT"
echo "$APACHE_EXT" | grep -q '^dom=yes$' || fail "Apache-container ext-dom missing"
echo "$APACHE_EXT" | grep -q '^mbstring=yes$' || fail "Apache-container ext-mbstring missing"
pass "required renderer extensions loaded in WordPress Apache container"

echo "== real in-memory renderer (WordPress Apache container) =="
cat >"$EVAL_APACHE" <<'PHP'
<?php
require '/var/www/html/wp-load.php';

if ( ! class_exists( 'Revistalogos_Core\\Dompdf_Article_Pdf_Renderer' ) ) {
	fwrite( STDERR, "Dompdf_Article_Pdf_Renderer not found in Apache container\n" );
	exit( 1 );
}
if ( ! class_exists( 'Dompdf\\Dompdf' ) ) {
	fwrite( STDERR, "Dompdf runtime class not found in Apache container\n" );
	exit( 1 );
}

$html     = '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Filosofía</title></head><body><p>Filosofía, razón y ética en España: ñ á é í ó ú</p></body></html>';
$renderer = new Revistalogos_Core\Dompdf_Article_Pdf_Renderer();
$artifact = $renderer->render( $html );

if ( ! is_string( $artifact ) || '' === $artifact ) {
	fwrite( STDERR, "Apache-container renderer returned empty output\n" );
	exit( 1 );
}
if ( 0 !== strpos( $artifact, '%PDF-' ) ) {
	fwrite( STDERR, "Apache-container renderer output does not start with %PDF-\n" );
	exit( 1 );
}

echo "PASS Apache renderer class exists\n";
echo "PASS Apache Dompdf class exists\n";
echo "PASS Apache renderer returns non-empty string\n";
echo "PASS Apache artifact starts with %PDF-\n";
PHP

wp_runtime wp-content/plugins/revistalogos-core/.qa-article-pdf-renderer-apache.php
pass "real in-memory PDF from WordPress Apache-container PHP"

echo "== real in-memory renderer + publication regression (WP-CLI) =="
cat >"$EVAL_HOST" <<'PHP'
<?php
if ( ! class_exists( 'Revistalogos_Core\\Dompdf_Article_Pdf_Renderer' ) ) {
	fwrite( STDERR, "Dompdf_Article_Pdf_Renderer not found\n" );
	exit( 1 );
}
if ( ! class_exists( 'Dompdf\\Dompdf' ) ) {
	fwrite( STDERR, "Dompdf runtime class not found\n" );
	exit( 1 );
}

$admin = get_user_by( 'login', getenv( 'QA_ADMIN' ) ?: 'les_pdf_renderer_admin' );
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

$attachments_before = (int) wp_count_posts( 'attachment' )->inherit;

$author_id = wp_insert_post(
	array(
		'post_type'   => 'author',
		'post_title'  => 'QA Renderer Author',
		'post_status' => 'publish',
		'post_name'   => 'qa-renderer-author',
	),
	true
);
if ( is_wp_error( $author_id ) ) {
	fwrite( STDERR, "author create failed\n" );
	exit( 1 );
}

$article_id = wp_insert_post(
	array(
		'post_type'    => 'article',
		'post_title'   => 'QA renderer without PDF',
		'post_status'  => 'draft',
		'post_content' => 'Body without PDF.',
		'post_name'    => 'qa-renderer-without-pdf',
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

$html     = '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Filosofía</title></head><body><p>Filosofía, razón y ética en España: ñ á é í ó ú</p></body></html>';
$renderer = new Revistalogos_Core\Dompdf_Article_Pdf_Renderer();
$artifact = $renderer->render( $html );

qa_ok( is_string( $artifact ) && '' !== $artifact, 'renderer returns non-empty string' );
qa_ok( 0 === strpos( $artifact, '%PDF-' ), 'artifact starts with %PDF-' );
qa_ok( false === strpos( $artifact, $html ), 'artifact is not the HTML input' );
qa_ok( $attachments_before === (int) wp_count_posts( 'attachment' )->inherit, 'Media Library attachment count unchanged' );
qa_ok( 0 === absint( get_post_meta( $article_id, 'pdf_file', true ) ), 'pdf_file remains empty after render' );

$orchestrator = new Revistalogos_Core\Article_Pdf_Generation_Orchestrator( $renderer );
$result       = $orchestrator->orchestrate(
	Revistalogos_Core\Article_Pdf_Publication_Policy::GENERATE_REQUIRED,
	$html
);
qa_ok( Revistalogos_Core\Article_Pdf_Publication_Policy::GENERATION_SUCCESS === $result['generation_result'], 'WU3 generation success with real artifact' );
qa_ok( Revistalogos_Core\Article_Pdf_Publication_Policy::PUBLICATION_ALLOWED === $result['publication_decision'], 'WU3 publication allowed with real artifact' );
qa_ok( 0 === strpos( $result['artifact'], '%PDF-' ), 'WU3 artifact starts with %PDF-' );

$published = wp_update_post(
	array(
		'ID'          => $article_id,
		'post_status' => 'publish',
	),
	true
);
qa_ok( ! is_wp_error( $published ) && 'publish' === get_post_status( $article_id ), 'publish without PDF still allowed when author exists' );
qa_ok( 0 === absint( get_post_meta( $article_id, 'pdf_file', true ) ), 'publish without PDF does not write pdf_file' );

$no_author = wp_insert_post(
	array(
		'post_type'    => 'article',
		'post_title'   => 'QA renderer no author',
		'post_status'  => 'draft',
		'post_content' => 'No author.',
		'post_name'    => 'qa-renderer-no-author',
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
qa_ok( $attachments_before === (int) wp_count_posts( 'attachment' )->inherit, 'renderer session created no attachments' );

if ( $GLOBALS['qa_fail'] ) {
	exit( 1 );
}
PHP

cli eval-file wp-content/plugins/revistalogos-core/.qa-article-pdf-renderer-eval.php
pass "real in-memory PDF and publication composition"

echo "== all article PDF renderer QA checks passed =="
