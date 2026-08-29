#!/usr/bin/env bash
# Isolated Docker QA for ADR 0017 WU5: persist generated PDF bytes
# as a Media Library attachment and set Article pdf_file.
# Never points at production and never reuses the primary local volumes.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
PLUGIN_DIR="$ROOT/wordpress/wp-content/plugins/revistalogos-core"
PROJECT="${LES_PDF_PERSISTENCE_QA_PROJECT:-revistalogos-article-pdf-persistence-qa}"
PORT="${LES_PDF_PERSISTENCE_QA_PORT:-8087}"
BASE_URL="http://localhost:${PORT}"
TMP="$(mktemp -d "${TMPDIR:-/tmp}/les-article-pdf-persistence-qa.XXXXXX")"
EVAL_HOST="$PLUGIN_DIR/.qa-article-pdf-persistence-eval.php"
ADMIN_USER="les_pdf_persistence_admin"
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
	--title="LOGO ET SPES PDF persistence QA" \
	--admin_user="$ADMIN_USER" \
	--admin_password="$ADMIN_PASSWORD" \
	--admin_email="pdf-persistence@example.invalid" \
	--skip-email >/dev/null
cli config set DISABLE_WP_CRON true --raw >/dev/null
cli theme activate revistalogos >/dev/null
cli plugin activate revistalogos-core >/dev/null

PLUGIN_VERSION="$(cli eval 'echo REVISTALOGOS_CORE_VERSION;')"
[[ "$PLUGIN_VERSION" == "0.2.8" ]] || fail "expected plugin 0.2.8, got $PLUGIN_VERSION"
WP_VERSION="$(cli core version)"
WPCLI_PHP="$(cli eval 'echo PHP_VERSION;')"
echo "WordPress $WP_VERSION / WP-CLI PHP $WPCLI_PHP"
[[ "$WP_VERSION" == "7.1" ]] || fail "expected WordPress 7.1, got $WP_VERSION"
[[ "$WPCLI_PHP" == 8.3.* ]] || fail "expected WP-CLI PHP 8.3, got $WPCLI_PHP"
pass "plugin 0.2.8 active in isolated WordPress 7.1 / WP-CLI PHP 8.3"

echo "== persist real Dompdf bytes + invalid-input regressions =="
cat >"$EVAL_HOST" <<'PHP'
<?php
if ( ! class_exists( 'Revistalogos_Core\\Article_Pdf_WordPress_Persister' ) ) {
	fwrite( STDERR, "Article_Pdf_WordPress_Persister not found\n" );
	exit( 1 );
}
if ( ! class_exists( 'Revistalogos_Core\\Dompdf_Article_Pdf_Renderer' ) ) {
	fwrite( STDERR, "Dompdf_Article_Pdf_Renderer not found\n" );
	exit( 1 );
}

$admin = get_user_by( 'login', getenv( 'QA_ADMIN' ) ?: 'les_pdf_persistence_admin' );
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

$attachments_before = qa_count_attachments();

$author_id = wp_insert_post(
	array(
		'post_type'   => 'author',
		'post_title'  => 'QA Persistence Author',
		'post_status' => 'publish',
		'post_name'   => 'qa-persistence-author',
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
		'post_title'   => 'QA persistence without PDF',
		'post_status'  => 'draft',
		'post_content' => 'Body without PDF.',
		'post_name'    => 'qa-persistence-without-pdf',
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

qa_ok( is_string( $artifact ) && '' !== $artifact && 0 === strpos( $artifact, '%PDF-' ), 'WU4 renderer produced real PDF bytes' );

$persister = new Revistalogos_Core\Article_Pdf_WordPress_Persister();
$result    = $persister->persist( $article_id, $artifact );

qa_ok( ! is_wp_error( $result ) && (int) $result > 0, 'persist returns positive attachment ID' );

$attachment_id = (int) $result;
$attachment    = get_post( $attachment_id );
$attached_file = $attachment_id ? get_attached_file( $attachment_id ) : '';
$relative_file = $attachment_id ? get_post_meta( $attachment_id, '_wp_attached_file', true ) : '';
$signature     = ( is_string( $attached_file ) && is_readable( $attached_file ) )
	? substr( (string) file_get_contents( $attached_file ), 0, 5 )
	: '';
$pdf_file      = get_post_meta( $article_id, 'pdf_file', true );
$count_after   = qa_count_attachments();

qa_ok( $attachment && 'attachment' === $attachment->post_type, 'attachment post exists' );
qa_ok( $attachment && 'application/pdf' === $attachment->post_mime_type, 'attachment MIME is application/pdf' );
qa_ok( $attachment && (int) $attachment->post_parent === (int) $article_id, 'attachment parent is the Article' );
qa_ok( is_string( $attached_file ) && is_file( $attached_file ), 'physical file exists in uploads' );
qa_ok( '%PDF-' === $signature, 'physical file begins %PDF-' );
qa_ok( is_string( $relative_file ) && '' !== $relative_file && false === strpos( $relative_file, 'http' ), 'attached file is uploads-relative path' );
qa_ok( (int) $pdf_file === $attachment_id, 'pdf_file equals attachment ID' );
qa_ok( (string) (int) $pdf_file === (string) $pdf_file, 'pdf_file stores ID not URL/path/bytes' );
qa_ok( $count_after === $attachments_before + 1, 'attachment count increased by one' );
qa_ok( 'draft' === get_post_status( $article_id ), 'Article remains draft' );

echo 'EVIDENCE attachment_id=' . $attachment_id
	. ' mime=' . ( $attachment ? $attachment->post_mime_type : '' )
	. ' parent=' . ( $attachment ? (int) $attachment->post_parent : 0 )
	. ' file=' . $relative_file
	. ' signature=' . $signature
	. ' count_delta=' . ( $count_after - $attachments_before )
	. ' pdf_file=' . (int) $pdf_file
	. "\n";

$invalid_article_id = wp_insert_post(
	array(
		'post_type'    => 'article',
		'post_title'   => 'QA persistence invalid artifact',
		'post_status'  => 'draft',
		'post_content' => 'No PDF yet.',
		'post_name'    => 'qa-persistence-invalid-artifact',
		'meta_input'   => array(
			'authors' => array( (int) $author_id ),
		),
	),
	true
);
$count_before_invalid = qa_count_attachments();
$invalid              = $persister->persist( $invalid_article_id, '<html>not a pdf</html>' );

qa_ok( is_wp_error( $invalid ), 'invalid artifact returns WP_Error' );
qa_ok( is_wp_error( $invalid ) && 'article_pdf_invalid_artifact' === $invalid->get_error_code(), 'invalid artifact uses article_pdf_invalid_artifact' );
qa_ok( qa_count_attachments() === $count_before_invalid, 'invalid artifact does not create an attachment' );
qa_ok( 0 === absint( get_post_meta( $invalid_article_id, 'pdf_file', true ) ), 'invalid artifact does not write pdf_file' );

$missing = $persister->persist( 999999, $artifact );
qa_ok( is_wp_error( $missing ), 'nonexistent ID returns WP_Error' );
qa_ok( is_wp_error( $missing ) && 'article_pdf_invalid_article' === $missing->get_error_code(), 'nonexistent ID uses article_pdf_invalid_article' );
qa_ok( qa_count_attachments() === $count_before_invalid, 'invalid article creates no attachment' );

$wrong_type = $persister->persist( $author_id, $artifact );
qa_ok( is_wp_error( $wrong_type ), 'non-Article ID returns WP_Error' );
qa_ok( is_wp_error( $wrong_type ) && 'article_pdf_invalid_article' === $wrong_type->get_error_code(), 'non-Article ID uses article_pdf_invalid_article' );
qa_ok( 0 === absint( get_post_meta( $author_id, 'pdf_file', true ) ), 'non-Article persist does not write pdf_file' );
qa_ok( qa_count_attachments() === $count_before_invalid, 'non-Article persist creates no attachment' );

$publish_ok = wp_insert_post(
	array(
		'post_type'    => 'article',
		'post_title'   => 'QA persistence publish without PDF',
		'post_status'  => 'draft',
		'post_content' => 'Publish without PDF.',
		'post_name'    => 'qa-persistence-publish-without-pdf',
		'meta_input'   => array(
			'authors' => array( (int) $author_id ),
		),
	),
	true
);
wp_update_post(
	array(
		'ID'          => $publish_ok,
		'post_status' => 'publish',
	),
	true
);
qa_ok( 'publish' === get_post_status( $publish_ok ), 'publish without PDF still allowed when author exists' );
qa_ok( 0 === absint( get_post_meta( $publish_ok, 'pdf_file', true ) ), 'publish without PDF does not write pdf_file' );

$no_author = wp_insert_post(
	array(
		'post_type'    => 'article',
		'post_title'   => 'QA persistence no author',
		'post_status'  => 'draft',
		'post_content' => 'No author.',
		'post_name'    => 'qa-persistence-no-author',
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

cli eval-file wp-content/plugins/revistalogos-core/.qa-article-pdf-persistence-eval.php
pass "real PDF persisted and invalid inputs create nothing"

PERSISTER="$PLUGIN_DIR/includes/article-pdf/class-article-pdf-wordpress-persister.php"
echo "== static guards (persistence only; no publication wiring) =="
[[ -f "$PERSISTER" ]] || fail "Article_Pdf_WordPress_Persister file missing"
if grep -Eq "add_action|add_filter" "$PERSISTER"; then
	fail "persister must not register WordPress hooks"
fi
if grep -Eq "KEEP_EXISTING|GENERATE_REQUIRED|decide_pdf_action|decide_publication" "$PERSISTER"; then
	fail "persister must not reimplement WU1/WU2 publication policy"
fi
if grep -Eq "wp_insert_post_data|rest_pre_insert|transition_post_status" "$PERSISTER"; then
	fail "persister must not wire publication"
fi
pass "persister source has no hooks or publication policy"

echo "== all article PDF persistence QA checks passed =="
