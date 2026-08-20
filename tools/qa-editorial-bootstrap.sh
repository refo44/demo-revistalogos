#!/usr/bin/env bash
# Isolated local Docker QA for Volume 1 editorial bootstrap and recovery-UI removal.
# Never points at production and never reuses the primary local Docker volumes.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
PROJECT="${LES_BOOTSTRAP_QA_PROJECT:-revistalogos-bootstrap-qa}"
PORT="${LES_BOOTSTRAP_QA_PORT:-8082}"
BASE_URL="http://localhost:${PORT}"
TMP="$(mktemp -d "${TMPDIR:-/tmp}/les-bootstrap-qa.XXXXXX")"
ADMIN_USER="les_bootstrap_admin"
ADMIN_PASSWORD="local-qa-admin-$(openssl rand -hex 8)"

cd "$ROOT"

compose() {
	WORDPRESS_PORT="$PORT" docker compose -p "$PROJECT" "$@"
}

cli() {
	compose run --rm wpcli wp --url="$BASE_URL" "$@"
}

cli_production() {
	compose run --rm \
		-e WORDPRESS_CONFIG_EXTRA="define( 'WP_ENVIRONMENT_TYPE', 'production' );" \
		wpcli wp --url="$BASE_URL" "$@"
}

fail() {
	echo "FAIL: $*" >&2
	exit 1
}

pass() {
	echo "PASS: $*"
}

cleanup() {
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

relevant_db_hash() {
	local prefix
	prefix="$(cli db prefix)"

	{
		cli db query "SELECT * FROM ${prefix}posts ORDER BY ID" --skip-column-names --batch --raw
		cli db query "SELECT * FROM ${prefix}postmeta ORDER BY meta_id" --skip-column-names --batch --raw
		cli db query "SELECT * FROM ${prefix}options WHERE option_name IN ('show_on_front','page_on_front','page_for_posts','wp_page_for_privacy_policy') ORDER BY option_name" --skip-column-names --batch --raw
	} | shasum -a 256 | awk '{print $1}'
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
	--title="LOGO ET SPES bootstrap QA" \
	--admin_user="$ADMIN_USER" \
	--admin_password="$ADMIN_PASSWORD" \
	--admin_email="bootstrap-admin@example.invalid" \
	--skip-email >/dev/null
cli config set DISABLE_WP_CRON true --raw >/dev/null
cli theme activate revistalogos >/dev/null
cli plugin activate revistalogos-core >/dev/null
cli rewrite structure '/%postname%/' --hard >/dev/null

CORE_VERSION="$(cli core version)"
PLUGIN_STATUS="$(cli plugin status revistalogos-core)"
THEME_STATUS="$(cli theme status revistalogos)"
[[ "$CORE_VERSION" == "7.0.4" ]] || fail "expected WordPress 7.0.4, got $CORE_VERSION"
printf '%s' "$PLUGIN_STATUS" | rg -F -q "Status: Active" || fail "plugin not active"
printf '%s' "$THEME_STATUS" | rg -F -q "Status: Active" || fail "theme not active"
pass "WordPress 7.0.4, revistalogos theme and revistalogos-core plugin available"

echo "== PHP syntax and recovery UI removal =="
compose run --rm --entrypoint php wpcli -l wp-content/plugins/revistalogos-core/includes/class-plugin.php >/dev/null
compose run --rm --entrypoint php wpcli -l wp-content/plugins/revistalogos-core/includes/fixtures/class-fixtures.php >/dev/null
compose run --rm --entrypoint php wpcli -l wp-content/plugins/revistalogos-core/includes/cli/class-fixtures-command.php >/dev/null
compose run --rm --entrypoint php wpcli -l wp-content/plugins/revistalogos-core/includes/migration/class-content-migrator.php >/dev/null
compose run --rm --entrypoint php wpcli -l wp-content/plugins/revistalogos-core/includes/cli/class-content-command.php >/dev/null
RECOVERY_CLASS="$(cli eval 'echo class_exists( "Revistalogos_Core\\Content_Recovery_Admin" ) ? "yes" : "no";')"
[[ "$RECOVERY_CLASS" == "no" ]] || fail "Content_Recovery_Admin should be gone, got $RECOVERY_CLASS"
[[ ! -f "$ROOT/wordpress/wp-content/plugins/revistalogos-core/includes/migration/class-content-recovery-admin.php" ]] || fail "class-content-recovery-admin.php still present"
pass "recovery admin class and file are gone"

echo "== durable institutional migrator/CLI =="
cli revistalogos content validate >"$TMP/cli-validate.txt"
assert_contains "$TMP/cli-validate.txt" "Payload is valid"
cli revistalogos content import --apply >"$TMP/cli-import.txt"
cli revistalogos content verify >"$TMP/cli-verify.txt"
assert_contains "$TMP/cli-verify.txt" "All migrated objects verified"
PAGE_COUNT_BEFORE="$(cli post list --post_type=page --format=count)"
pass "Content_Migrator validate/import/verify still works"

echo "== production guards =="
if cli_production revistalogos fixtures seed --apply >"$TMP/seed-prod.txt" 2>&1; then
	fail "demo seed --apply must be refused on production"
fi
assert_contains "$TMP/seed-prod.txt" "allow-production"
if cli_production revistalogos fixtures bootstrap --apply >"$TMP/boot-prod.txt" 2>&1; then
	fail "bootstrap --apply must be refused on production without --confirm-production"
fi
assert_contains "$TMP/boot-prod.txt" "confirm-production"
pass "production guards block demo seed and unconfirmed bootstrap writes"

echo "== bootstrap fails without canonical author =="
if cli revistalogos fixtures plan >"$TMP/plan-missing-author.txt" 2>&1; then
	fail "plan must fail when the canonical author is absent"
fi
assert_contains "$TMP/plan-missing-author.txt" "rafael-eduardo-figueredo-oropeza"
pass "missing author is fail-safe"

echo "== create representative manual author (not bootstrap-owned) =="
AUTHOR_ID="$(cli post create \
	--post_type=author \
	--post_title='Rafael Eduardo Figueredo Oropeza' \
	--post_name=rafael-eduardo-figueredo-oropeza \
	--post_status=publish \
	--porcelain)"
[[ "$AUTHOR_ID" =~ ^[0-9]+$ ]] || fail "could not create representative author"

echo "== plan/dry-run performs no writes =="
cli help revistalogos fixtures | rg -q bootstrap || fail "bootstrap subcommand missing"
cli help revistalogos fixtures | rg -q plan || fail "plan subcommand missing"
DB_BEFORE="$(relevant_db_hash)"
cli revistalogos fixtures plan >"$TMP/plan.txt"
cli revistalogos fixtures bootstrap >"$TMP/dry-run.txt"
DB_AFTER="$(relevant_db_hash)"
[[ "$DB_BEFORE" == "$DB_AFTER" ]] || fail "plan/dry-run changed posts/postmeta/reading options"
assert_contains "$TMP/plan.txt" "would create issue vol-1-n-1"
assert_contains "$TMP/plan.txt" "author: reuse"
pass "plan/dry-run is read-only"
echo "read-only relevant DB hash: $DB_BEFORE"

echo "== bootstrap --apply =="
cli revistalogos fixtures bootstrap --apply >"$TMP/apply.txt"
assert_contains "$TMP/apply.txt" "volume-1-issue-1: created"
assert_contains "$TMP/apply.txt" "volume-1-article-1: created"
ISSUE_ID="$(cli post list --post_type=issue --meta_key=_les_bootstrap_key --meta_value=volume-1-issue-1 --format=ids)"
ARTICLE1_ID="$(cli post list --post_type=article --meta_key=_les_bootstrap_key --meta_value=volume-1-article-1 --format=ids)"
ARTICLE2_ID="$(cli post list --post_type=article --meta_key=_les_bootstrap_key --meta_value=volume-1-article-2 --format=ids)"
EDITORIAL_ID="$(cli post list --post_type=article --meta_key=_les_bootstrap_key --meta_value=volume-1-editorial --format=ids)"
[[ "$ISSUE_ID" =~ ^[0-9]+$ ]] || fail "missing Volume 1 issue"
[[ "$ARTICLE1_ID" =~ ^[0-9]+$ ]] || fail "missing article 1"
[[ "$ARTICLE2_ID" =~ ^[0-9]+$ ]] || fail "missing article 2"
[[ "$EDITORIAL_ID" =~ ^[0-9]+$ ]] || fail "missing editorial"
ISSUE_COUNT="$(cli post list --post_type=issue --post_status=any --format=count)"
ARTICLE_COUNT="$(cli post list --post_type=article --post_status=any --format=count)"
AUTHOR_COUNT="$(cli post list --post_type=author --post_status=any --format=count)"
[[ "$ISSUE_COUNT" == "1" ]] || fail "expected 1 issue, got $ISSUE_COUNT"
[[ "$ARTICLE_COUNT" == "7" ]] || fail "expected 7 articles (editorial + 6), got $ARTICLE_COUNT"
[[ "$AUTHOR_COUNT" == "1" ]] || fail "expected 1 author (Rafael reused), got $AUTHOR_COUNT"

ORDER="$(cli eval "echo implode(',', wp_list_pluck( Revistalogos_Core\\Queries::issue_articles( $ISSUE_ID ), 'post_name' ));")"
EXPECTED_ORDER="editorial-vol-1-n-1,la-naturaleza-del-ser-en-la-filosofia-contemporanea,fundamentos-de-la-etica-aplicada-en-el-siglo-xxi,justicia-distributiva-y-responsabilidad-social,el-problema-del-conocimiento-en-la-era-digital,secularizacion-y-experiencia-religiosa-en-la-modernidad,teodicea-y-el-problema-del-mal-en-el-pensamiento-contemporaneo"
[[ "$ORDER" == "$EXPECTED_ORDER" ]] || fail "article order mismatch: $ORDER"

ARTICLE_ISSUE="$(cli post meta get "$ARTICLE1_ID" issue)"
[[ "$ARTICLE_ISSUE" == "$ISSUE_ID" ]] || fail "article 1 is not linked to the Volume 1 issue"
AUTHORS_META="$(cli eval "echo implode(',', array_map('strval', (array) get_post_meta( $ARTICLE1_ID, 'authors', true )));")"
printf '%s' "$AUTHORS_META" | rg -q "^${AUTHOR_ID}$|^${AUTHOR_ID},|,${AUTHOR_ID}$|,${AUTHOR_ID}," || [[ "$AUTHORS_META" == "$AUTHOR_ID" ]] || fail "article 1 is not linked to Rafael (authors=$AUTHORS_META)"

RAFAEL_BOOTSTRAP="$(cli post meta get "$AUTHOR_ID" _les_bootstrap || true)"
RAFAEL_FIXTURE="$(cli post meta get "$AUTHOR_ID" _les_fixture || true)"
[[ -z "$RAFAEL_BOOTSTRAP" ]] || fail "Rafael was marked _les_bootstrap"
[[ -z "$RAFAEL_FIXTURE" ]] || fail "Rafael was marked _les_fixture"

issn="$(cli post meta get "$ISSUE_ID" issn || true)"
doi_issue="$(cli post meta get "$ISSUE_ID" doi || true)"
doi_article="$(cli post meta get "$ARTICLE1_ID" doi || true)"
orcid="$(cli post meta get "$AUTHOR_ID" orcid || true)"
[[ -z "$issn" ]] || fail "Volume 1 issue has ISSN"
[[ -z "$doi_issue" ]] || fail "Volume 1 issue has DOI"
[[ -z "$doi_article" ]] || fail "article 1 has DOI"
[[ -z "$orcid" ]] || fail "Rafael has ORCID"

PAGE_COUNT_AFTER="$(cli post list --post_type=page --format=count)"
[[ "$PAGE_COUNT_BEFORE" == "$PAGE_COUNT_AFTER" ]] || fail "bootstrap changed Page count"
pass "bootstrap created one issue, seven articles, reused Rafael, left Pages untouched"

echo "== idempotent re-run =="
cli revistalogos fixtures bootstrap --apply >"$TMP/rerun.txt"
assert_contains "$TMP/rerun.txt" "volume-1-issue-1: exists"
ISSUE_ID2="$(cli post list --post_type=issue --meta_key=_les_bootstrap_key --meta_value=volume-1-issue-1 --format=ids)"
AUTHOR_COUNT2="$(cli post list --post_type=author --post_status=any --format=count)"
[[ "$ISSUE_ID2" == "$ISSUE_ID" ]] || fail "re-run duplicated or replaced the issue"
[[ "$AUTHOR_COUNT2" == "1" ]] || fail "re-run created a duplicate author"
cli revistalogos fixtures verify >"$TMP/verify.txt"
pass "re-run is idempotent; verify passed"

echo "== HTTP regression =="
cli rewrite flush --hard >/dev/null
for path in \
	/ \
	/normas/ \
	/acerca/ \
	/contacto/ \
	/revista/numeros/ \
	/revista/articulos/ \
	/revista/autores/ \
	/revista/numeros/vol-1-n-1/ \
	/revista/articulos/la-naturaleza-del-ser-en-la-filosofia-contemporanea/ \
	/revista/articulos/editorial-vol-1-n-1/ \
	/revista/autores/rafael-eduardo-figueredo-oropeza/
do
	code="$(http_code "${BASE_URL}${path}")"
	[[ "$code" == "200" ]] || fail "$path expected 200, got $code"
	echo "  $code $path"
done
pass "public archives, singles and institutional Pages return 200"

echo "== adoption =="
cli post update "$ARTICLE1_ID" --post_title='Título adoptado QA Volume 1' >/dev/null
cli revistalogos fixtures verify >"$TMP/verify-adopted.txt"
assert_contains "$TMP/verify-adopted.txt" "adopted; left as editorial content"
cli revistalogos fixtures bootstrap --apply >"$TMP/rerun-adopted.txt"
assert_contains "$TMP/rerun-adopted.txt" "volume-1-article-1: adopted"
TITLE_AFTER="$(cli post get "$ARTICLE1_ID" --field=post_title)"
[[ "$TITLE_AFTER" == "Título adoptado QA Volume 1" ]] || fail "adopted article was overwritten"
pass "adopted article is classified and not overwritten"

echo "== teardown refuses adopted content and leaves Rafael/Pages =="
MANUAL_POST_ID="$(cli post create --post_type=post --post_title='Nota manual no bootstrap' --post_status=publish --porcelain)"
[[ "$MANUAL_POST_ID" =~ ^[0-9]+$ ]] || fail "could not create unrelated manual post"

echo "== editorial ownership immediately before teardown =="
cli eval '$id = (int) '"$EDITORIAL_ID"';
$p = get_post($id);
if ( ! $p ) { echo "missing editorial"; exit(1); }
$ref = new ReflectionMethod("Revistalogos_Core\\Fixtures", "snapshot_hash");
$ref->setAccessible(true);
$computed = $ref->invoke(null, $id);
$stored = (string) get_post_meta($id, "_les_bootstrap_source_hash", true);
$ids = Revistalogos_Core\Fixtures::all_bootstrap_ids();
echo "post_type=" . $p->post_type . PHP_EOL;
echo "slug=" . $p->post_name . PHP_EOL;
echo "post_status=" . $p->post_status . PHP_EOL;
echo "_les_bootstrap=" . get_post_meta($id, "_les_bootstrap", true) . PHP_EOL;
echo "_les_bootstrap_key=" . get_post_meta($id, "_les_bootstrap_key", true) . PHP_EOL;
echo "_les_bootstrap_kind=" . get_post_meta($id, "_les_bootstrap_kind", true) . PHP_EOL;
echo "_les_bootstrap_version=" . get_post_meta($id, "_les_bootstrap_version", true) . PHP_EOL;
echo "_les_bootstrap_source_hash=" . $stored . PHP_EOL;
echo "_les_bootstrap_adopted=" . get_post_meta($id, "_les_bootstrap_adopted", true) . PHP_EOL;
echo "_les_fixture=" . get_post_meta($id, "_les_fixture", true) . PHP_EOL;
echo "computed_hash=" . $computed . PHP_EOL;
echo "hash_match=" . ( $computed === $stored ? "1" : "0" ) . PHP_EOL;
echo "is_adopted=" . ( Revistalogos_Core\Fixtures::is_adopted($id) ? "1" : "0" ) . PHP_EOL;
echo "in_all_bootstrap_ids=" . ( in_array($id, $ids, true) ? "1" : "0" ) . PHP_EOL;
echo "all_bootstrap_count=" . count($ids) . PHP_EOL;
'

cli revistalogos fixtures teardown --kind=bootstrap >"$TMP/teardown-plan.txt"
assert_contains "$TMP/teardown-plan.txt" "would delete article ${EDITORIAL_ID}"
assert_contains "$TMP/teardown-plan.txt" "would delete issue ${ISSUE_ID}"
assert_contains "$TMP/teardown-plan.txt" "would delete article ${ARTICLE2_ID}"
assert_contains "$TMP/teardown-plan.txt" "adopted Volume 1 content; teardown refused"
assert_contains "$TMP/teardown-plan.txt" "kept article ${ARTICLE1_ID} (adopted Volume 1 content; teardown refused)"
if rg -q "would delete author ${AUTHOR_ID}" "$TMP/teardown-plan.txt"; then
	fail "teardown dry-run would delete Rafael"
fi
if rg -q "would delete post ${MANUAL_POST_ID}" "$TMP/teardown-plan.txt"; then
	fail "teardown dry-run would delete unrelated manual content"
fi
cli revistalogos fixtures teardown --kind=bootstrap --apply >"$TMP/teardown-apply.txt"
assert_contains "$TMP/teardown-apply.txt" "adopted Volume 1 content; teardown refused"
assert_contains "$TMP/teardown-apply.txt" "deleted article ${EDITORIAL_ID}"
assert_contains "$TMP/teardown-apply.txt" "deleted issue ${ISSUE_ID}"
assert_contains "$TMP/teardown-apply.txt" "deleted article ${ARTICLE2_ID}"
if rg -q "kept article ${EDITORIAL_ID}" "$TMP/teardown-apply.txt"; then
	fail "teardown classified unadopted editorial as adopted"
fi
STILL_ARTICLE="$(cli post get "$ARTICLE1_ID" --field=ID)"
[[ "$STILL_ARTICLE" == "$ARTICLE1_ID" ]] || fail "teardown deleted the adopted article"
STILL_AUTHOR="$(cli post get "$AUTHOR_ID" --field=ID)"
[[ "$STILL_AUTHOR" == "$AUTHOR_ID" ]] || fail "teardown deleted Rafael"
STILL_PAGE="$(cli post list --post_type=page --name=normas --format=ids)"
[[ -n "$STILL_PAGE" ]] || fail "teardown deleted institutional Page normas"
STILL_MANUAL="$(cli post get "$MANUAL_POST_ID" --field=ID)"
[[ "$STILL_MANUAL" == "$MANUAL_POST_ID" ]] || fail "teardown deleted unrelated manual content"
if cli post get "$EDITORIAL_ID" --field=ID >/dev/null 2>&1; then
	fail "unadopted editorial should have been removed"
fi
if cli post get "$ISSUE_ID" --field=ID >/dev/null 2>&1; then
	fail "unadopted issue should have been removed"
fi
if cli post get "$ARTICLE2_ID" --field=ID >/dev/null 2>&1; then
	fail "unadopted article 2 should have been removed"
fi
cli revistalogos fixtures teardown --kind=bootstrap --apply >"$TMP/teardown-2.txt"
STILL_ARTICLE2="$(cli post get "$ARTICLE1_ID" --field=ID)"
[[ "$STILL_ARTICLE2" == "$ARTICLE1_ID" ]] || fail "second teardown deleted the adopted article"
STILL_AUTHOR2="$(cli post get "$AUTHOR_ID" --field=ID)"
[[ "$STILL_AUTHOR2" == "$AUTHOR_ID" ]] || fail "second teardown deleted Rafael"
STILL_MANUAL2="$(cli post get "$MANUAL_POST_ID" --field=ID)"
[[ "$STILL_MANUAL2" == "$MANUAL_POST_ID" ]] || fail "second teardown deleted unrelated manual content"
if rg -q "ERROR deleting" "$TMP/teardown-2.txt"; then
	fail "second teardown was not safe"
fi
pass "teardown removes only unadopted bootstrap objects; second run is safe"

echo "== HTTP after teardown =="
for path in / /normas/ /acerca/ /contacto/ /revista/numeros/ /revista/articulos/ /revista/autores/; do
	code="$(http_code "${BASE_URL}${path}")"
	[[ "$code" == "200" ]] || fail "after teardown $path expected 200, got $code"
done
ADOPTED_CODE="$(http_code "${BASE_URL}/revista/articulos/la-naturaleza-del-ser-en-la-filosofia-contemporanea/")"
[[ "$ADOPTED_CODE" == "200" ]] || fail "adopted article single expected 200, got $ADOPTED_CODE"
RAFAEL_CODE="$(http_code "${BASE_URL}/revista/autores/rafael-eduardo-figueredo-oropeza/")"
[[ "$RAFAEL_CODE" == "200" ]] || fail "Rafael single expected 200, got $RAFAEL_CODE"
pass "institutional Pages, archives, adopted article and Rafael remain 200"

echo "PASS: Volume 1 editorial bootstrap QA"
