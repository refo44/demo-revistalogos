#!/usr/bin/env bash
# Isolated Docker QA for article author picker, publish-author rule,
# native PDF Media Library picker, and CTA visited contrast. Not a formal test suite.
# Never points at production and never reuses the primary local volumes.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
PROJECT="${LES_EDITORIAL_UX_QA_PROJECT:-revistalogos-editorial-ux-qa}"
PORT="${LES_EDITORIAL_UX_QA_PORT:-8084}"
BASE_URL="http://localhost:${PORT}"
TMP="$(mktemp -d "${TMPDIR:-/tmp}/les-editorial-ux-qa.XXXXXX")"
EVAL_HOST="$ROOT/wordpress/wp-content/plugins/revistalogos-core/.qa-editorial-ux-eval.php"
ADMIN_USER="les_editorial_ux_admin"
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

echo "== CTA visited contrast (static tokens) =="
python3 - <<'PY'
def rel_lum(hex_color):
    hex_color = hex_color.lstrip('#')
    rgb = [int(hex_color[i:i+2], 16) / 255 for i in (0, 2, 4)]
    def f(c):
        return c / 12.92 if c <= 0.04045 else ((c + 0.055) / 1.055) ** 2.4
    r, g, b = (f(c) for c in rgb)
    return 0.2126 * r + 0.7152 * g + 0.0722 * b

def contrast(fg, bg):
    l1, l2 = rel_lum(fg), rel_lum(bg)
    lighter, darker = max(l1, l2), min(l1, l2)
    return (lighter + 0.05) / (darker + 0.05)

pairs = [
    ('primary CTA', '#ffffff', '#18597c', 4.5),
    ('primary hover', '#ffffff', '#144866', 4.5),
    ('pdf CTA', '#ffffff', '#0a7599', 4.5),
    ('pdf hover', '#ffffff', '#18597c', 4.5),
    ('secondary CTA', '#18597c', '#ffffff', 4.5),
    ('login CTA', '#1e293b', '#e7f2f8', 4.5),
    ('register CTA', '#1e293b', '#ffbf00', 4.5),
    ('content link', '#0a7599', '#f8fafc', 4.5),
]
failed = 0
for name, fg, bg, need in pairs:
    ratio = contrast(fg, bg)
    status = 'PASS' if ratio >= need else 'FAIL'
    print(f'{status} {name} {fg} on {bg} = {ratio:.2f}:1 (need {need}:1)')
    if ratio < need:
        failed += 1
raise SystemExit(failed)
PY
pass "CTA and content-link contrast ratios meet AA"

rg -q "\.btn--primary:visited" "$ROOT/wordpress/wp-content/themes/revistalogos/assets/css/components.css" \
	|| fail "missing .btn--primary:visited component rule"
rg -q "\.btn--pdf:visited" "$ROOT/wordpress/wp-content/themes/revistalogos/assets/css/components.css" \
	|| fail "missing .btn--pdf:visited component rule"
rg -q "\.pagination__link:visited" "$ROOT/wordpress/wp-content/themes/revistalogos/assets/css/components.css" \
	|| fail "missing .pagination__link:visited component rule"
if rg -q "a:visited \{[^}]*color:\s*#fff" "$ROOT/wordpress/wp-content/themes/revistalogos/assets/css/base.css"; then
	fail "must not globally force visited links to white"
fi
rg -q "a:visited" "$ROOT/wordpress/wp-content/themes/revistalogos/assets/css/base.css" \
	|| fail "ordinary a:visited rule must remain"
rg -F -q "display: inline-flex" "$ROOT/wordpress/wp-content/themes/revistalogos/assets/css/components.css" \
	|| fail ".btn must remain inline-flex (static wrap preflight for 320px / 200% zoom)"
if rg -q "^\.btn[^_].*nowrap|\.btn \{[^}]*white-space:\s*nowrap" "$ROOT/wordpress/wp-content/themes/revistalogos/assets/css/components.css"; then
	fail ".btn must not force nowrap (static wrap preflight for 320px / 200% zoom)"
fi
pass "CTA visited CSS is component-level; ordinary visited links preserved"
pass "320px / 200% zoom: PASS static/preflight (NOT LIVE-VERIFIED; manual browser check after deploy)"

cleanup() {
	rm -f "$EVAL_HOST"
	compose down -v --remove-orphans >/dev/null 2>&1 || true
	rm -rf "$TMP"
}
trap cleanup EXIT

assert_contains() {
	local file="$1"
	local text="$2"

	rg -F -q "$text" "$file" || fail "expected '$text' in $file"
}

http_code() {
	curl -sS -o /dev/null -w '%{http_code}' "$1"
}

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
	--title="LOGO ET SPES editorial UX QA" \
	--admin_user="$ADMIN_USER" \
	--admin_password="$ADMIN_PASSWORD" \
	--admin_email="editorial-ux@example.invalid" \
	--skip-email >/dev/null
cli config set DISABLE_WP_CRON true --raw >/dev/null
cli theme activate revistalogos >/dev/null
cli plugin activate revistalogos-core >/dev/null
cli rewrite structure '/%postname%/' --hard >/dev/null

PLUGIN_VERSION="$(cli eval 'echo REVISTALOGOS_CORE_VERSION;')"
THEME_VERSION="$(cli eval 'echo REVISTALOGOS_THEME_VERSION;')"
[[ "$PLUGIN_VERSION" == "0.2.6" ]] || fail "expected plugin 0.2.6, got $PLUGIN_VERSION"
[[ "$THEME_VERSION" == "0.2.1" ]] || fail "expected theme 0.2.1, got $THEME_VERSION"
pass "plugin 0.2.6 and theme 0.2.1 active in isolated WordPress"

echo "== PHP syntax =="
compose run --rm --entrypoint php wpcli -l wp-content/plugins/revistalogos-core/includes/metadata/class-meta-boxes.php >/dev/null
compose run --rm --entrypoint php wpcli -l wp-content/plugins/revistalogos-core/includes/metadata/class-metadata.php >/dev/null
compose run --rm --entrypoint php wpcli -l wp-content/plugins/revistalogos-core/includes/relationships/class-relationships.php >/dev/null
compose run --rm --entrypoint php wpcli -l wp-content/plugins/revistalogos-core/includes/content-types/class-content-types.php >/dev/null
compose run --rm --entrypoint php wpcli -l wp-content/plugins/revistalogos-core/includes/fixtures/class-fixtures.php >/dev/null
pass "PHP syntax of changed plugin files"

rg -F -q "library: { type: 'application/pdf' }" "$ROOT/wordpress/wp-content/plugins/revistalogos-core/assets/js/admin-meta.js" \
	|| fail "PDF picker JS must filter application/pdf"
pass "PDF picker JS filters application/pdf"

rg -F -q "wp.apiFetch" "$ROOT/wordpress/wp-content/plugins/revistalogos-core/assets/js/admin-meta.js" \
	|| fail "author picker must use wp.apiFetch"
rg -F -q "per_page" "$ROOT/wordpress/wp-content/plugins/revistalogos-core/assets/js/admin-meta.js" \
	|| fail "author picker must bound per_page"
rg -F -q "minLength" "$ROOT/wordpress/wp-content/plugins/revistalogos-core/assets/js/admin-meta.js" \
	|| fail "author picker must enforce minLength"
if rg -q "use_block_editor_for_post_type" "$ROOT/wordpress/wp-content/plugins/revistalogos-core/includes/class-plugin.php"; then
	fail "plugin must not register use_block_editor_for_post_type"
fi
if [[ -f "$ROOT/wordpress/wp-content/plugins/revistalogos-core/includes/fixtures/class-bootstrap-admin.php" ]]; then
	fail "Bootstrap_Admin file must be absent"
fi
if rg -q "Bootstrap_Admin" "$ROOT/wordpress/wp-content/plugins/revistalogos-core/includes/class-plugin.php"; then
	fail "plugin must not wire Bootstrap_Admin"
fi
pass "Classic Editor override absent; Bootstrap_Admin absent; author picker uses core REST"

echo "== canonical author + Volume 1 bootstrap (must still publish authorless samples) =="
AUTHOR_ID="$(cli post create \
	--post_type=author \
	--post_title='Rafael Eduardo Figueredo Oropeza' \
	--post_name=rafael-eduardo-figueredo-oropeza \
	--post_status=publish \
	--porcelain)"
[[ "$AUTHOR_ID" =~ ^[0-9]+$ ]] || fail "could not create Rafael"

cli revistalogos fixtures bootstrap --apply >"$TMP/bootstrap.txt"
ARTICLE1_ID="$(cli post list --post_type=article --meta_key=_les_bootstrap_key --meta_value=volume-1-article-1 --format=ids)"
ARTICLE2_ID="$(cli post list --post_type=article --meta_key=_les_bootstrap_key --meta_value=volume-1-article-2 --format=ids)"
ISSUE_ID="$(cli post list --post_type=issue --meta_key=_les_bootstrap_key --meta_value=volume-1-issue-1 --format=ids)"
[[ "$ARTICLE1_ID" =~ ^[0-9]+$ ]] || fail "missing article 1"
[[ "$ARTICLE2_ID" =~ ^[0-9]+$ ]] || fail "missing article 2"
[[ "$ISSUE_ID" =~ ^[0-9]+$ ]] || fail "missing issue"

A2_STATUS="$(cli post get "$ARTICLE2_ID" --field=post_status)"
[[ "$A2_STATUS" == "publish" ]] || fail "bootstrap article 2 should remain publish, got $A2_STATUS"
A2_AUTHORS="$(cli eval "echo implode(',', array_map('strval', (array) get_post_meta( $ARTICLE2_ID, 'authors', true )));")"
[[ -z "$A2_AUTHORS" ]] || fail "bootstrap article 2 should have authors=[], got $A2_AUTHORS"
pass "bootstrap still publishes authorless sample articles"

echo "== authors + publication + PDF domain QA =="
cat >"$EVAL_HOST" <<'PHP'
<?php
$admin = get_user_by( 'login', getenv( 'QA_ADMIN' ) ?: 'les_editorial_ux_admin' );
if ( ! $admin ) {
	$users = get_users( array( 'role' => 'administrator', 'number' => 1 ) );
	$admin = $users ? $users[0] : null;
}
if ( ! $admin ) {
	fwrite( STDERR, "no admin\n" );
	exit( 1 );
}
wp_set_current_user( $admin->ID );

$rafael = get_page_by_path( 'rafael-eduardo-figueredo-oropeza', OBJECT, 'author' );
$article1 = get_page_by_path( 'la-naturaleza-del-ser-en-la-filosofia-contemporanea', OBJECT, 'article' );
$article2 = get_page_by_path( 'fundamentos-de-la-etica-aplicada-en-el-siglo-xxi', OBJECT, 'article' );
$issue    = get_page_by_path( 'vol-1-n-1', OBJECT, 'issue' );
if ( ! $rafael || ! $article1 || ! $article2 || ! $issue ) {
	fwrite( STDERR, "missing bootstrap objects\n" );
	exit( 1 );
}

function qa_metabox_post( $post_id, $authors = null, $pdf = null, $omit_pdf = false ) {
	$post = get_post( $post_id );
	$_POST = array();
	$_POST['revistalogos_core_meta_nonce'] = wp_create_nonce( 'revistalogos_core_meta' );
	foreach ( array( 'title_en', 'abstract', 'abstract_en', 'doi', 'pages', 'language', 'publication_date', 'received_date', 'accepted_date' ) as $key ) {
		$_POST[ $key ] = (string) get_post_meta( $post_id, $key, true );
	}
	if ( null !== $authors ) {
		$_POST['authors'] = $authors;
	}
	if ( ! $omit_pdf ) {
		$_POST['pdf_file'] = ( null === $pdf ) ? (string) get_post_meta( $post_id, 'pdf_file', true ) : (string) $pdf;
	}
	$_POST['issue'] = (string) get_post_meta( $post_id, 'issue', true );
	Revistalogos_Core\Meta_Boxes::save( $post_id, $post );
	clean_post_cache( $post_id );
}

function qa_render_rel( $post_id ) {
	ob_start();
	Revistalogos_Core\Meta_Boxes::render_relationships_box( get_post( $post_id ) );
	return ob_get_clean();
}

function qa_render_fields( $post_id ) {
	ob_start();
	Revistalogos_Core\Meta_Boxes::render_fields_box( get_post( $post_id ) );
	return ob_get_clean();
}

$fail = 0;
function qa_ok( $cond, $label ) {
	if ( $cond ) {
		echo "PASS $label\n";
	} else {
		echo "FAIL $label\n";
		$GLOBALS['fail']++;
	}
}

$html2 = qa_render_rel( $article2->ID );
qa_ok( false !== strpos( $html2, 'Ningún autor asignado' ), 'empty authors shows Ningún autor asignado' );
qa_ok( false !== strpos( $html2, 'id="revistalogos-author-search"' ), 'author search field present' );
qa_ok( false === strpos( $html2, 'type="checkbox"' ), 'authors control is not a checkbox catalog' );
qa_ok( false === strpos( $html2, 'name="authors[]" multiple' ), 'no multiple select' );

for ( $i = 1; $i <= 40; $i++ ) {
	wp_insert_post( array(
		'post_type'   => 'author',
		'post_title'  => sprintf( 'QA Scale Author %03d', $i ),
		'post_name'   => sprintf( 'qa-scale-author-%03d', $i ),
		'post_status' => 'publish',
	), true );
}
wp_insert_post( array(
	'post_type'   => 'issue',
	'post_title'  => 'Rafael Dummy Issue',
	'post_name'   => 'rafael-dummy-issue',
	'post_status' => 'publish',
), true );
$html_scale = qa_render_rel( $article2->ID );
qa_ok( false === strpos( $html_scale, 'QA Scale Author' ), 'full Author catalog is not preloaded' );
qa_ok( false === strpos( $html_scale, 'name="authors[]"' ), 'initial HTML contains no author IDs when none assigned' );
qa_ok( false !== strpos( $html_scale, 'Ningún autor asignado' ), 'empty state still present with large catalog' );

$author_route = rest_get_route_for_post_type_items( 'author' );
$search_req = new WP_REST_Request( 'GET', $author_route );
$search_req->set_param( 'search', 'Rafael' );
$search_req->set_param( 'per_page', 15 );
$search_req->set_param( 'status', 'publish' );
$search_req->set_param( 'orderby', 'title' );
$search_req->set_param( 'order', 'asc' );
$search_res = rest_do_request( $search_req );
$search_data = $search_res->get_data();
$search_ids = array();
$search_titles = array();
if ( is_array( $search_data ) ) {
	foreach ( $search_data as $row ) {
		if ( isset( $row['id'] ) ) {
			$search_ids[] = (int) $row['id'];
		}
		if ( isset( $row['title']['rendered'] ) ) {
			$search_titles[] = $row['title']['rendered'];
		}
	}
}
qa_ok( in_array( (int) $rafael->ID, $search_ids, true ), 'partial search returns matching Author CPT' );
qa_ok( count( $search_data ) <= 15, 'configured result limit enforced' );
$scale_req = new WP_REST_Request( 'GET', $author_route );
$scale_req->set_param( 'search', 'QA Scale Author' );
$scale_req->set_param( 'per_page', 15 );
$scale_req->set_param( 'status', 'publish' );
$scale_res = rest_do_request( $scale_req );
$scale_data = $scale_res->get_data();
qa_ok( is_array( $scale_data ) && count( $scale_data ) === 15, 'search returns bounded result set' );
$issue_in_search = false;
foreach ( $search_titles as $title ) {
	if ( false !== strpos( $title, 'Dummy Issue' ) ) {
		$issue_in_search = true;
	}
}
qa_ok( ! $issue_in_search, 'non-Author posts excluded' );

qa_metabox_post( $article2->ID, array( (string) $rafael->ID ) );
$saved = get_post_meta( $article2->ID, 'authors', true );
qa_ok( is_array( $saved ) && array( (int) $rafael->ID ) === array_map( 'intval', $saved ), 'assign Rafael persists' );
$html2b = qa_render_rel( $article2->ID );
qa_ok( false !== strpos( $html2b, 'name="authors[]"' ) && false !== strpos( $html2b, (string) $rafael->ID ) && false !== strpos( $html2b, 'revistalogos-authors-remove' ), 'reload restores assignments' );
qa_ok( false !== strpos( $html2b, 'revistalogos-authors-empty hidden' ) && false !== strpos( $html2b, 'Autores asignados' ), 'assigned Author displayed separately' );
qa_ok( false === strpos( $html2b, 'QA Scale Author' ), 'assigned view still does not preload catalog' );

$draft_id = wp_insert_post( array(
	'post_type'    => 'article',
	'post_title'   => 'QA draft no authors',
	'post_name'    => 'qa-draft-no-authors',
	'post_status'  => 'draft',
	'post_content' => 'draft',
), true );
qa_metabox_post( $draft_id, array() );
qa_ok( 'draft' === get_post_status( $draft_id ) && array() === (array) get_post_meta( $draft_id, 'authors', true ), 'draft empty authors saves' );

$pending_id = wp_insert_post( array(
	'post_type'    => 'article',
	'post_title'   => 'QA pending no authors',
	'post_name'    => 'qa-pending-no-authors',
	'post_status'  => 'pending',
	'post_content' => 'pending',
), true );
qa_metabox_post( $pending_id, array() );
qa_ok( 'pending' === get_post_status( $pending_id ), 'pending empty authors saves' );

$_POST = array();
$_POST['revistalogos_core_meta_nonce'] = wp_create_nonce( 'revistalogos_core_meta' );
$blocked = wp_update_post( array(
	'ID'          => $draft_id,
	'post_status' => 'publish',
), true );
qa_ok( 'draft' === get_post_status( $draft_id ), 'draft to publish without author refused' );

$_POST['authors'] = array( (string) $rafael->ID );
$_POST['revistalogos_core_meta_nonce'] = wp_create_nonce( 'revistalogos_core_meta' );
wp_update_post( array(
	'ID'          => $draft_id,
	'post_status' => 'publish',
), true );
qa_metabox_post( $draft_id, array( (string) $rafael->ID ) );
qa_ok( 'publish' === get_post_status( $draft_id ) && Revistalogos_Core\Relationships::has_published_author( get_post_meta( $draft_id, 'authors', true ) ), 'publish with published author succeeds' );

$second = wp_insert_post( array(
	'post_type'    => 'author',
	'post_title'   => 'QA Second Author',
	'post_name'    => 'qa-second-author',
	'post_status'  => 'publish',
), true );
$multi_id = wp_insert_post( array(
	'post_type'    => 'article',
	'post_title'   => 'QA multi authors',
	'post_name'    => 'qa-multi-authors',
	'post_status'  => 'draft',
	'post_content' => 'multi',
), true );
update_post_meta( $multi_id, 'issue', $issue->ID );
qa_metabox_post( $multi_id, array( (string) $rafael->ID, (string) $second ) );
$_POST = array();
$_POST['revistalogos_core_meta_nonce'] = wp_create_nonce( 'revistalogos_core_meta' );
$_POST['authors'] = array( (string) $rafael->ID, (string) $second );
wp_update_post( array( 'ID' => $multi_id, 'post_status' => 'publish' ), true );
qa_metabox_post( $multi_id, array( (string) $rafael->ID, (string) $second ) );
$multi_saved = array_map( 'intval', (array) get_post_meta( $multi_id, 'authors', true ) );
qa_ok( 'publish' === get_post_status( $multi_id ) && in_array( (int) $rafael->ID, $multi_saved, true ) && in_array( (int) $second, $multi_saved, true ), 'multiple authors persist on publish' );
qa_ok( array( (int) $rafael->ID, (int) $second ) === $multi_saved, 'order preserved' );
qa_metabox_post( $multi_id, array( (string) $rafael->ID, (string) $rafael->ID, (string) $second ) );
qa_ok( array( (int) $rafael->ID, (int) $second ) === array_map( 'intval', (array) get_post_meta( $multi_id, 'authors', true ) ), 'duplicate assignment prevented' );
$toc_multi = function_exists( 'revistalogos_article_author_names' ) ? revistalogos_article_author_names( $multi_id ) : '';
qa_ok( false !== strpos( $toc_multi, 'Rafael' ) && false !== strpos( $toc_multi, 'QA Second Author' ), 'multiple author names helper' );

qa_metabox_post( $multi_id, array( (string) $rafael->ID ) );
qa_ok( 'publish' === get_post_status( $multi_id ) && array( (int) $rafael->ID ) === array_map( 'intval', (array) get_post_meta( $multi_id, 'authors', true ) ), 'removing one of two authors stays publish' );

$before_final = get_post_meta( $multi_id, 'authors', true );
qa_metabox_post( $multi_id, array() );
qa_ok( 'publish' === get_post_status( $multi_id ) && array_map( 'intval', (array) $before_final ) === array_map( 'intval', (array) get_post_meta( $multi_id, 'authors', true ) ), 'removing final author refused; previous authors kept' );
qa_ok( 'author' === get_post_type( $rafael->ID ) && 'publish' === get_post_status( $rafael->ID ), 'remove does not delete Author' );
qa_ok( ! class_exists( 'Revistalogos_Core\\Bootstrap_Admin', false ), 'Bootstrap_Admin class absent' );

$draft_author = wp_insert_post( array(
	'post_type'   => 'author',
	'post_title'  => 'QA Draft Author',
	'post_name'   => 'qa-draft-author',
	'post_status' => 'draft',
), true );
$draft_only = wp_insert_post( array(
	'post_type'    => 'article',
	'post_title'   => 'QA draft-author only',
	'post_name'    => 'qa-draft-author-only',
	'post_status'  => 'draft',
), true );
$_POST = array();
$_POST['revistalogos_core_meta_nonce'] = wp_create_nonce( 'revistalogos_core_meta' );
$_POST['authors'] = array( (string) $draft_author );
wp_update_post( array( 'ID' => $draft_only, 'post_status' => 'publish' ), true );
qa_ok( 'publish' !== get_post_status( $draft_only ), 'draft Author does not satisfy publish' );

$issue_as_author = wp_insert_post( array(
	'post_type'    => 'article',
	'post_title'   => 'QA invalid author id',
	'post_name'    => 'qa-invalid-author-id',
	'post_status'  => 'draft',
), true );
$_POST = array();
$_POST['revistalogos_core_meta_nonce'] = wp_create_nonce( 'revistalogos_core_meta' );
$_POST['authors'] = array( (string) $issue->ID );
wp_update_post( array( 'ID' => $issue_as_author, 'post_status' => 'publish' ), true );
qa_ok( 'publish' !== get_post_status( $issue_as_author ), 'non-author ID does not satisfy publish' );

$editorial = get_page_by_path( 'editorial-vol-1-n-1', OBJECT, 'article' );
$ed_status = get_post_status( $editorial );
$ed_authors = get_post_meta( $editorial->ID, 'authors', true );
Revistalogos_Core\Plugin::maybe_upgrade();
qa_ok( $ed_status === get_post_status( $editorial ) && (array) $ed_authors === (array) get_post_meta( $editorial->ID, 'authors', true ), 'plugin upgrade does not mutate published authorless article' );

$fields_empty = qa_render_fields( $editorial->ID );
qa_ok( false !== strpos( $fields_empty, 'Ningún PDF seleccionado' ) && false !== strpos( $fields_empty, 'Seleccionar PDF' ) && false !== strpos( $fields_empty, 'name="pdf_file"' ), 'empty PDF UI' );

$fields_a1 = qa_render_fields( $article1->ID );
qa_ok( false !== strpos( $fields_a1, 'vol-1-articulo-01.pdf' ) && false !== strpos( $fields_a1, 'Reemplazar PDF' ) && false !== strpos( $fields_a1, 'Quitar PDF' ), 'placeholder PDF filename visible' );

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';
$src = REVISTALOGOS_CORE_DIR . 'resources/fixtures/numero-v12n2-2025.pdf';
$tmp = wp_tempnam( 'qa-real.pdf' );
copy( $src, $tmp );
$real_pdf = media_handle_sideload( array( 'name' => 'qa-real-article.pdf', 'tmp_name' => $tmp ), 0, 'QA real PDF' );
$permalink_before = get_permalink( $article1->ID );
$old_pdf = absint( get_post_meta( $article1->ID, 'pdf_file', true ) );
qa_metabox_post( $article1->ID, array( (string) $rafael->ID ), (string) $real_pdf );
qa_ok( (int) $real_pdf === absint( get_post_meta( $article1->ID, 'pdf_file', true ) ), 'replacement PDF persists' );
qa_ok( $permalink_before === get_permalink( $article1->ID ), 'permalink unchanged after PDF replace' );
qa_ok( 'attachment' === get_post_type( $old_pdf ), 'placeholder attachment not deleted on replace' );

qa_metabox_post( $article1->ID, array( (string) $rafael->ID ), null, true );
qa_ok( (int) $real_pdf === absint( get_post_meta( $article1->ID, 'pdf_file', true ) ), 'omitted pdf_file preserves relation' );

qa_metabox_post( $article1->ID, array( (string) $rafael->ID ), '0' );
qa_ok( 0 === absint( get_post_meta( $article1->ID, 'pdf_file', true ) ), 'remove sets relation to 0' );
qa_ok( 'attachment' === get_post_type( $real_pdf ), 'remove does not delete attachment' );

$cover = absint( get_post_meta( $issue->ID, '_thumbnail_id', true ) );
if ( ! $cover ) {
	$cover_posts = get_posts( array( 'post_type' => 'attachment', 'post_mime_type' => 'image/jpeg', 'posts_per_page' => 1, 'post_status' => 'inherit' ) );
	$cover = $cover_posts ? (int) $cover_posts[0]->ID : 0;
}
qa_ok( $cover > 0, 'jpeg attachment available for reject check' );
qa_metabox_post( $article1->ID, array( (string) $rafael->ID ), (string) $cover );
qa_ok( 0 === absint( get_post_meta( $article1->ID, 'pdf_file', true ) ), 'JPEG rejected server-side' );

qa_metabox_post( $article1->ID, array( (string) $rafael->ID ), (string) $real_pdf );
qa_metabox_post( $multi_id, array( (string) $rafael->ID, (string) $second ) );
update_post_meta( $multi_id, 'issue', $issue->ID );

$names = revistalogos_article_author_names( $article2->ID );
qa_ok( false !== strpos( $names, 'Rafael Eduardo Figueredo Oropeza' ), 'Queries resolve saved published author' );

qa_ok( use_block_editor_for_post_type( 'article' ), 'article still uses Gutenberg' );
qa_ok( false === has_filter( 'use_block_editor_for_post_type', array( 'Revistalogos_Core\\Content_Types', 'use_block_editor' ) ), 'plugin does not force Classic Editor' );

$_POST = array();

function qa_rest_article( $params, $id = 0 ) {
	$route   = $id ? '/wp/v2/article/' . $id : '/wp/v2/article';
	$request = new WP_REST_Request( 'POST', $route );
	$request->set_header( 'Content-Type', 'application/json' );
	foreach ( $params as $key => $value ) {
		$request->set_param( $key, $value );
	}
	return rest_do_request( $request );
}

$rest_draft = qa_rest_article( array(
	'title'   => 'QA REST draft no authors',
	'status'  => 'draft',
	'content' => 'draft',
	// No slug: WP 7.0.4 WP_REST_Posts_Controller::create_item() calls
	// wp_unique_post_slug() with $prepared_post->id / ->post_parent on a
	// stdClass that has neither when status is draft|pending and post_name
	// is set. That is core, not this plugin. Omit slug so QA stays silent.
) );
$rest_draft_data = $rest_draft->get_data();
qa_ok( ! $rest_draft->is_error() && in_array( (int) $rest_draft->get_status(), array( 200, 201 ), true ), 'REST draft without author saves' );

$rest_denied = qa_rest_article( array(
	'title'   => 'QA REST publish no authors',
	'status'  => 'publish',
	'content' => 'no',
	'slug'    => 'qa-rest-publish-no-authors',
) );
qa_ok( $rest_denied->is_error() || (int) $rest_denied->get_status() >= 400, 'REST publication without author refused' );

$rest_ok = qa_rest_article( array(
	'title'   => 'QA REST publish with author',
	'status'  => 'publish',
	'content' => 'yes',
	'slug'    => 'qa-rest-publish-with-author',
	'meta'    => array( 'authors' => array( (int) $rafael->ID ) ),
) );
$rest_ok_data = $rest_ok->get_data();
qa_ok( ! $rest_ok->is_error() && isset( $rest_ok_data['status'] ) && 'publish' === $rest_ok_data['status'], 'REST publication with valid author succeeds' );

if ( ! $rest_draft->is_error() && ! empty( $rest_draft_data['id'] ) ) {
	$saved_id = (int) $rest_draft_data['id'];
	update_post_meta( $saved_id, 'authors', array( (int) $rafael->ID ) );
	$rest_saved_pub  = qa_rest_article( array( 'status' => 'publish' ), $saved_id );
	$rest_saved_data = $rest_saved_pub->get_data();
	qa_ok( ! $rest_saved_pub->is_error() && isset( $rest_saved_data['status'] ) && 'publish' === $rest_saved_data['status'], 'publication with saved valid author succeeds' );
} else {
	qa_ok( false, 'publication with saved valid author succeeds' );
}

echo 'FAIL_COUNT=' . $fail . "\n";
echo 'ARTICLE1=' . $article1->ID . "\n";
echo 'ARTICLE2=' . $article2->ID . "\n";
echo 'MULTI=' . $multi_id . "\n";
echo 'REAL_PDF=' . $real_pdf . "\n";
echo 'RAFAEL=' . $rafael->ID . "\n";
exit( $fail ? 1 : 0 );
PHP
cli eval-file wp-content/plugins/revistalogos-core/.qa-editorial-ux-eval.php >"$TMP/domain.txt" 2>"$TMP/domain.err"
rm -f "$EVAL_HOST"
cat "$TMP/domain.txt"
if [[ -s "$TMP/domain.err" ]]; then
	cat "$TMP/domain.err" >&2
fi

pass "domain eval finished"
rg -q '^FAIL_COUNT=0$' "$TMP/domain.txt" || fail "domain FAIL_COUNT is not 0"
if rg -q '^FAIL ' "$TMP/domain.txt"; then
	fail "domain QA reported FAIL lines"
fi
if rg -q "Undefined property|PHP Warning|PHP Notice|PHP Deprecated" "$TMP/domain.txt" "$TMP/domain.err"; then
	fail "domain eval emitted PHP warnings (see domain.txt / domain.err)"
fi
pass "authors, publication and PDF domain checks"

ARTICLE1_ID="$(rg -o 'ARTICLE1=[0-9]+' "$TMP/domain.txt" | cut -d= -f2)"
ARTICLE2_ID="$(rg -o 'ARTICLE2=[0-9]+' "$TMP/domain.txt" | cut -d= -f2)"
MULTI_ID="$(rg -o 'MULTI=[0-9]+' "$TMP/domain.txt" | cut -d= -f2)"
REAL_PDF="$(rg -o 'REAL_PDF=[0-9]+' "$TMP/domain.txt" | cut -d= -f2)"
RAFAEL_ID="$(rg -o 'RAFAEL=[0-9]+' "$TMP/domain.txt" | cut -d= -f2)"

echo "== Gutenberg REST HTTP =="
APP_PASS="$(cli user application-password create "$ADMIN_USER" editorial-ux-qa --porcelain)"
[[ -n "$APP_PASS" && -n "$RAFAEL_ID" ]] || fail "missing application password or Rafael ID"
code="$(curl -sS -o "$TMP/rest-no-author.json" -w '%{http_code}' \
	-u "${ADMIN_USER}:${APP_PASS}" \
	-H 'Content-Type: application/json' \
	-d '{"title":"HTTP REST no author","status":"publish","content":"x","slug":"http-rest-no-author"}' \
	"${BASE_URL}/wp-json/wp/v2/article")"
[[ "$code" == "400" ]] || fail "expected 400 REST publish without author, got ${code}: $(cat "$TMP/rest-no-author.json")"
pass "HTTP REST publication without author refused"

code="$(curl -sS -o "$TMP/rest-with-author.json" -w '%{http_code}' \
	-u "${ADMIN_USER}:${APP_PASS}" \
	-H 'Content-Type: application/json' \
	-d "{\"title\":\"HTTP REST with author\",\"status\":\"publish\",\"content\":\"x\",\"slug\":\"http-rest-with-author\",\"meta\":{\"authors\":[${RAFAEL_ID}]}}" \
	"${BASE_URL}/wp-json/wp/v2/article")"
[[ "$code" == "201" ]] || fail "expected 201 REST publish with author, got ${code}: $(cat "$TMP/rest-with-author.json")"
pass "HTTP REST publication with valid author succeeds"

echo "== HTTP regression =="
A1_URL="$(cli post url "$ARTICLE1_ID")"
A2_URL="$(cli post url "$ARTICLE2_ID")"
MULTI_URL="$(cli post url "$MULTI_ID")"
ISSUE_URL="$(cli post url "$ISSUE_ID")"
RAFAEL_URL="$(cli post url "$AUTHOR_ID")"
PDF_URL="$(cli eval "echo wp_get_attachment_url( $REAL_PDF );")"

for path in /revista/numeros/ /revista/articulos/ /revista/autores/; do
	code="$(curl -sS -o /dev/null -w '%{http_code}' "${BASE_URL}${path}")"
	[[ "$code" == "200" ]] || fail "expected 200 for $path, got $code"
done
pass "archive routes 200"

curl -sS "$A1_URL" -o "$TMP/a1.html"
curl -sS "$A2_URL" -o "$TMP/a2.html"
curl -sS "$MULTI_URL" -o "$TMP/multi.html"
curl -sS "$ISSUE_URL" -o "$TMP/issue.html"
curl -sS "$RAFAEL_URL" -o "$TMP/rafael.html"

assert_contains "$TMP/a1.html" "qa-real-article.pdf"
assert_contains "$TMP/a1.html" "Descargar PDF del artículo"
assert_contains "$TMP/a1.html" "citation_pdf_url"
assert_contains "$TMP/a2.html" "Rafael Eduardo Figueredo Oropeza"
assert_contains "$TMP/multi.html" "QA Second Author"
assert_contains "$TMP/issue.html" "Rafael Eduardo Figueredo Oropeza"
assert_contains "$TMP/issue.html" "QA Second Author"
assert_contains "$TMP/rafael.html" "Rafael Eduardo Figueredo Oropeza"
rg -q 'class="btn btn--pdf' "$TMP/a1.html" || fail "article PDF CTA uses btn btn--pdf"
rg -q 'class="btn btn--primary' "$TMP/issue.html" || fail "issue PDF CTA uses btn btn--primary"

curl -sS "${BASE_URL}/wp-content/themes/revistalogos/assets/css/components.css?v=20260820-1" -o "$TMP/components.css"
rg -q "\.btn--primary:visited" "$TMP/components.css" || fail "served components.css missing .btn--primary:visited"
rg -q "\.btn--pdf:visited" "$TMP/components.css" || fail "served components.css missing .btn--pdf:visited"
pass "served CTA CSS includes visited component rules"

# article 2 has authors but no PDF after our tests? article2 never got a PDF.
if rg -F -q "Descargar PDF del artículo" "$TMP/a2.html"; then
	fail "article 2 should not show PDF buttons"
fi
pass "frontend authors, TOC names, PDF href and citation_pdf_url"

# empty PDF buttons: editorial
EDITORIAL_URL="$(cli post url "$(cli post list --post_type=article --name=editorial-vol-1-n-1 --format=ids)")"
curl -sS "$EDITORIAL_URL" -o "$TMP/editorial.html"
if rg -F -q "btn--pdf" "$TMP/editorial.html"; then
	fail "editorial without PDF should hide PDF buttons"
fi
pass "frontend PDF buttons hidden when empty"

echo "== all editorial UX QA checks passed =="
