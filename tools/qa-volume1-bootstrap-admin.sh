#!/usr/bin/env bash
# Isolated local Docker QA for the temporary Volume 1 bootstrap admin screen.
# Never points at production and never reuses the primary local Docker volumes.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
PROJECT="${LES_BOOTSTRAP_ADMIN_QA_PROJECT:-revistalogos-bootstrap-admin-qa}"
PORT="${LES_BOOTSTRAP_ADMIN_QA_PORT:-8083}"
BASE_URL="http://localhost:${PORT}"
ADMIN_URL="${BASE_URL}/wp-admin/tools.php?page=revistalogos-volume-1-bootstrap"
TMP="$(mktemp -d "${TMPDIR:-/tmp}/les-bootstrap-admin-qa.XXXXXX")"
ADMIN_USER="les_bootstrap_admin"
ADMIN_PASSWORD="local-qa-admin-$(openssl rand -hex 8)"
SUBSCRIBER_USER="les_bootstrap_subscriber"
SUBSCRIBER_PASSWORD="local-qa-subscriber-$(openssl rand -hex 8)"
NONCE_FIELD="revistalogos_volume1_bootstrap_nonce"
ACTION_FIELD="revistalogos_volume1_bootstrap_action"
PLAN_FIELD="revistalogos_volume1_bootstrap_plan"
CONFIRM_FIELD="revistalogos_volume1_bootstrap_confirm"
AUTHOR_SLUG="rafael-eduardo-figueredo-oropeza"

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
	compose down -v --remove-orphans >/dev/null 2>&1 || true
	rm -rf "$TMP"
}
trap cleanup EXIT

assert_contains() {
	local file="$1"
	local text="$2"

	rg -F -q "$text" "$file" || fail "expected '$text' in $file"
}

assert_not_contains() {
	local file="$1"
	local text="$2"

	if rg -F -q "$text" "$file"; then
		fail "did not expect '$text' in $file"
	fi
}

extract_field() {
	local file="$1"
	local name="$2"

	node - "$file" "$name" <<'NODE'
const fs = require('node:fs');
const [file, name] = process.argv.slice(2);
const html = fs.readFileSync(file, 'utf8');
const escapedName = name.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
const match = html.match(new RegExp(`name="${escapedName}"[^>]*value="([^"]+)"`));
if (!match) {
	process.exit(1);
}
process.stdout.write(match[1]);
NODE
}

login() {
	local user="$1"
	local password="$2"
	local jar="$3"
	local login_page="$4"

	curl -sS -c "$jar" "${BASE_URL}/wp-login.php" -o "$login_page"
	curl -sS -L -b "$jar" -c "$jar" \
		--data-urlencode "log=$user" \
		--data-urlencode "pwd=$password" \
		--data-urlencode "wp-submit=Log In" \
		--data-urlencode "redirect_to=${BASE_URL}/wp-admin/" \
		--data-urlencode "testcookie=1" \
		"${BASE_URL}/wp-login.php" \
		-o "$login_page"
	assert_contains "$login_page" "wpadminbar"
}

post_action() {
	local jar="$1"
	local output="$2"
	local action="$3"
	local nonce="$4"
	shift 4

	curl -sS -b "$jar" -o "$output" -w '%{http_code}' \
		--data-urlencode "${NONCE_FIELD}=${nonce}" \
		--data-urlencode "${ACTION_FIELD}=${action}" \
		"$@" \
		"$ADMIN_URL"
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
	--title="LOGO ET SPES bootstrap admin QA" \
	--admin_user="$ADMIN_USER" \
	--admin_password="$ADMIN_PASSWORD" \
	--admin_email="bootstrap-admin@example.invalid" \
	--skip-email >/dev/null
cli config set DISABLE_WP_CRON true --raw >/dev/null
cli theme activate revistalogos >/dev/null
cli plugin activate revistalogos-core >/dev/null
cli rewrite structure '/%postname%/' --hard >/dev/null
cli user create "$SUBSCRIBER_USER" "bootstrap-subscriber@example.invalid" \
	--role=subscriber \
	--user_pass="$SUBSCRIBER_PASSWORD" >/dev/null

CORE_VERSION="$(cli core version)"
PLUGIN_STATUS="$(cli plugin status revistalogos-core)"
[[ "$CORE_VERSION" == "7.0.4" ]] || fail "expected WordPress 7.0.4, got $CORE_VERSION"
printf '%s' "$PLUGIN_STATUS" | rg -F -q "Status: Active" || fail "plugin not active"
pass "WordPress 7.0.4 and revistalogos-core available"

echo "== PHP syntax =="
compose run --rm --entrypoint php wpcli -l wp-content/plugins/revistalogos-core/includes/fixtures/class-fixtures.php >/dev/null
compose run --rm --entrypoint php wpcli -l wp-content/plugins/revistalogos-core/includes/fixtures/class-bootstrap-admin.php >/dev/null
compose run --rm --entrypoint php wpcli -l wp-content/plugins/revistalogos-core/includes/class-plugin.php >/dev/null
pass "PHP syntax of bootstrap admin and Fixtures is valid"

echo "== durable institutional Pages for later regression =="
cli revistalogos content import --apply >/dev/null
cli revistalogos content verify >/dev/null
PAGE_COUNT_BEFORE="$(cli post list --post_type=page --format=count)"
pass "institutional Pages imported before bootstrap admin tests"

echo "== authentication and capability =="
ANON_CODE="$(curl -sS -o "$TMP/anonymous.html" -w '%{http_code}' "$ADMIN_URL")"
[[ "$ANON_CODE" == "302" ]] || fail "anonymous access expected 302 to login, got $ANON_CODE"
pass "3. anonymous user cannot access"

login "$ADMIN_USER" "$ADMIN_PASSWORD" "$TMP/admin.cookies" "$TMP/admin-login.html"
ADMIN_CODE="$(curl -sS -b "$TMP/admin.cookies" -o "$TMP/admin-page.html" -w '%{http_code}' "$ADMIN_URL")"
[[ "$ADMIN_CODE" == "200" ]] || fail "admin access expected 200, got $ADMIN_CODE"
assert_contains "$TMP/admin-page.html" "Volume 1 Editorial Bootstrap"
assert_contains "$TMP/admin-page.html" "Validate and Plan"
assert_not_contains "$TMP/admin-page.html" "JetBackup"
assert_not_contains "$TMP/admin-page.html" "name=\"revistalogos_volume1_bootstrap_backup\""
assert_not_contains "$TMP/admin-page.html" "name=\"force\""
assert_not_contains "$TMP/admin-page.html" "value=\"teardown\""
assert_not_contains "$TMP/admin-page.html" "Run Teardown"
assert_not_contains "$TMP/admin-page.html" "TEARDOWN"
pass "1. administrator can access"
pass "12. no backup field exists"
pass "13. no backup gate exists"
pass "14. no force control exists"

login "$SUBSCRIBER_USER" "$SUBSCRIBER_PASSWORD" "$TMP/subscriber.cookies" "$TMP/subscriber-login.html"
SUBSCRIBER_CODE="$(curl -sS -b "$TMP/subscriber.cookies" -o "$TMP/subscriber-page.html" -w '%{http_code}' "$ADMIN_URL")"
[[ "$SUBSCRIBER_CODE" == "403" ]] || fail "subscriber access expected 403, got $SUBSCRIBER_CODE"
pass "2. non-admin cannot access"

echo "== nonce enforcement =="
NONCELESS_CODE="$(curl -sS -X POST -b "$TMP/admin.cookies" -o "$TMP/nonceless.html" -w '%{http_code}' \
	--data-urlencode "${ACTION_FIELD}=validate_plan" \
	"$ADMIN_URL")"
assert_contains "$TMP/nonceless.html" "Security check failed"
[[ "$NONCELESS_CODE" == "403" ]] || fail "missing nonce expected 403, got $NONCELESS_CODE"
pass "4. nonce required"

echo "== plan writes nothing and Rafael missing blocks =="
NONCE="$(extract_field "$TMP/admin-page.html" "$NONCE_FIELD")"
DB_BEFORE="$(relevant_db_hash)"
MISSING_CODE="$(post_action "$TMP/admin.cookies" "$TMP/plan-missing.html" validate_plan "$NONCE")"
DB_AFTER="$(relevant_db_hash)"
[[ "$MISSING_CODE" == "200" ]] || fail "missing-author plan expected 200, got $MISSING_CODE"
[[ "$DB_BEFORE" == "$DB_AFTER" ]] || fail "Validate and Plan changed relevant database state"
assert_contains "$TMP/plan-missing.html" "Import gate:</strong> BLOCKED"
assert_contains "$TMP/plan-missing.html" "Rafael gate</th><td><strong>BLOCK"
assert_contains "$TMP/plan-missing.html" "Matching Author CPT objects</th><td>0"
assert_not_contains "$TMP/plan-missing.html" "name=\"${PLAN_FIELD}\""
pass "5. plan writes nothing"
pass "7. Rafael missing blocks"
echo "read-only relevant DB hash: $DB_BEFORE"

echo "== Rafael exactly-one match passes =="
AUTHOR_ID="$(cli post create \
	--post_type=author \
	--post_title='Rafael Eduardo Figueredo Oropeza' \
	--post_name="$AUTHOR_SLUG" \
	--post_status=publish \
	--porcelain)"
[[ "$AUTHOR_ID" =~ ^[0-9]+$ ]] || fail "could not create canonical author"
OK_CODE="$(post_action "$TMP/admin.cookies" "$TMP/plan-ok.html" validate_plan "$NONCE")"
[[ "$OK_CODE" == "200" ]] || fail "valid Rafael plan expected 200, got $OK_CODE"
assert_contains "$TMP/plan-ok.html" "Import gate:</strong> PASS"
assert_contains "$TMP/plan-ok.html" "Rafael gate</th><td><strong>PASS"
assert_contains "$TMP/plan-ok.html" "Matching Author CPT objects</th><td>1"
assert_contains "$TMP/plan-ok.html" ">REUSE<"
assert_contains "$TMP/plan-ok.html" ">CREATE<"
assert_contains "$TMP/plan-ok.html" "volume-1-issue-1"
assert_contains "$TMP/plan-ok.html" "volume-1-editorial"
assert_contains "$TMP/plan-ok.html" "I authorize the Volume 1 editorial bootstrap in production."
SAFE_PLAN="$(extract_field "$TMP/plan-ok.html" "$PLAN_FIELD")"
[[ -n "$SAFE_PLAN" ]] || fail "missing signed plan fingerprint"
pass "6. Rafael exactly-one match passes"

echo "== Rafael duplicate blocks =="
PREFIX="$(cli db prefix)"
DUP_ID="$(cli post create --post_type=author --post_title='Rafael duplicate QA' --post_name="${AUTHOR_SLUG}-dup" --post_status=publish --porcelain)"
cli db query "UPDATE ${PREFIX}posts SET post_name='${AUTHOR_SLUG}' WHERE ID=${DUP_ID}" >/dev/null
DUP_CODE="$(post_action "$TMP/admin.cookies" "$TMP/plan-dup.html" validate_plan "$NONCE")"
[[ "$DUP_CODE" == "200" ]] || fail "duplicate Rafael plan expected 200"
assert_contains "$TMP/plan-dup.html" "Import gate:</strong> BLOCKED"
assert_contains "$TMP/plan-dup.html" "Matching Author CPT objects</th><td>2"
assert_not_contains "$TMP/plan-dup.html" "name=\"${PLAN_FIELD}\""
cli post delete "$DUP_ID" --force >/dev/null
pass "8. Rafael duplicate blocks"

echo "== manual Issue collision blocks =="
ISSUE_COLLISION="$(cli post create --post_type=issue --post_title='Manual Vol 1' --post_name=vol-1-n-1 --post_status=publish --porcelain)"
cli post meta update "$ISSUE_COLLISION" volume_number 1 >/dev/null
cli post meta update "$ISSUE_COLLISION" issue_number 1 >/dev/null
ISSUE_CODE="$(post_action "$TMP/admin.cookies" "$TMP/plan-issue.html" validate_plan "$NONCE")"
[[ "$ISSUE_CODE" == "200" ]] || fail "manual issue plan expected 200"
assert_contains "$TMP/plan-issue.html" "Import gate:</strong> BLOCKED"
assert_contains "$TMP/plan-issue.html" "CONFLICT"
cli post delete "$ISSUE_COLLISION" --force >/dev/null
pass "9. manual Issue collision blocks"

echo "== manual Article collision blocks =="
ARTICLE_COLLISION="$(cli post create --post_type=article --post_title='Manual editorial' --post_name=editorial-vol-1-n-1 --post_status=publish --porcelain)"
ARTICLE_CODE="$(post_action "$TMP/admin.cookies" "$TMP/plan-article.html" validate_plan "$NONCE")"
[[ "$ARTICLE_CODE" == "200" ]] || fail "manual article plan expected 200"
assert_contains "$TMP/plan-article.html" "Import gate:</strong> BLOCKED"
assert_contains "$TMP/plan-article.html" "CONFLICT"
cli post delete "$ARTICLE_COLLISION" --force >/dev/null
pass "10. manual Article collision blocks"

echo "== explicit confirmation required =="
OK_CODE2="$(post_action "$TMP/admin.cookies" "$TMP/plan-ok2.html" validate_plan "$NONCE")"
[[ "$OK_CODE2" == "200" ]] || fail "re-plan before apply expected 200"
SAFE_PLAN="$(extract_field "$TMP/plan-ok2.html" "$PLAN_FIELD")"
NO_CONFIRM_CODE="$(post_action "$TMP/admin.cookies" "$TMP/apply-noconfirm.html" apply "$NONCE" \
	--data-urlencode "${PLAN_FIELD}=${SAFE_PLAN}")"
[[ "$NO_CONFIRM_CODE" == "200" ]] || fail "apply without confirm expected 200, got $NO_CONFIRM_CODE"
assert_contains "$TMP/apply-noconfirm.html" "Explicit production bootstrap confirmation is required"
ISSUE_COUNT_BEFORE="$(cli post list --post_type=issue --format=count)"
[[ "$ISSUE_COUNT_BEFORE" == "0" ]] || fail "apply without confirm created an issue"
pass "11. explicit confirmation required"

echo "== apply creates expected objects =="
APPLY_CODE="$(post_action "$TMP/admin.cookies" "$TMP/apply.html" apply "$NONCE" \
	--data-urlencode "${PLAN_FIELD}=${SAFE_PLAN}" \
	--data-urlencode "${CONFIRM_FIELD}=1")"
[[ "$APPLY_CODE" == "200" ]] || fail "apply expected 200, got $APPLY_CODE"
assert_contains "$TMP/apply.html" "Volume 1 editorial bootstrap applied without force"
assert_contains "$TMP/apply.html" "Result:</strong> PASS"
assert_contains "$TMP/apply.html" "volume-1-issue-1: created"
assert_contains "$TMP/apply.html" "volume-1-editorial: created"
ISSUE_ID="$(cli post list --post_type=issue --meta_key=_les_bootstrap_key --meta_value=volume-1-issue-1 --format=ids)"
ARTICLE1_ID="$(cli post list --post_type=article --meta_key=_les_bootstrap_key --meta_value=volume-1-article-1 --format=ids)"
EDITORIAL_ID="$(cli post list --post_type=article --meta_key=_les_bootstrap_key --meta_value=volume-1-editorial --format=ids)"
[[ "$ISSUE_ID" =~ ^[0-9]+$ ]] || fail "missing Volume 1 issue after apply"
[[ "$ARTICLE1_ID" =~ ^[0-9]+$ ]] || fail "missing article 1 after apply"
[[ "$EDITORIAL_ID" =~ ^[0-9]+$ ]] || fail "missing editorial after apply"
AUTHOR_COUNT="$(cli post list --post_type=author --post_status=any --format=count)"
[[ "$AUTHOR_COUNT" == "1" ]] || fail "expected 1 author after apply, got $AUTHOR_COUNT"
RAFAEL_BOOTSTRAP="$(cli post meta get "$AUTHOR_ID" _les_bootstrap || true)"
RAFAEL_FIXTURE="$(cli post meta get "$AUTHOR_ID" _les_fixture || true)"
[[ -z "$RAFAEL_BOOTSTRAP" ]] || fail "Rafael was marked _les_bootstrap"
[[ -z "$RAFAEL_FIXTURE" ]] || fail "Rafael was marked _les_fixture"
issn="$(cli post meta get "$ISSUE_ID" issn || true)"
doi_issue="$(cli post meta get "$ISSUE_ID" doi || true)"
doi_article="$(cli post meta get "$ARTICLE1_ID" doi || true)"
orcid="$(cli post meta get "$AUTHOR_ID" orcid || true)"
pages="$(cli post meta get "$ARTICLE1_ID" pages || true)"
[[ -z "$issn" ]] || fail "Volume 1 issue has ISSN"
[[ -z "$doi_issue" ]] || fail "Volume 1 issue has DOI"
[[ -z "$doi_article" ]] || fail "article 1 has DOI"
[[ -z "$orcid" ]] || fail "Rafael has ORCID"
[[ -z "$pages" ]] || fail "article 1 has dummy page ranges"
PAGE_COUNT_AFTER="$(cli post list --post_type=page --format=count)"
[[ "$PAGE_COUNT_BEFORE" == "$PAGE_COUNT_AFTER" ]] || fail "bootstrap changed Page count"
pass "15. apply creates/reuses expected objects"
pass "16. no duplicate Rafael"
pass "17. Rafael remains unmarked"
pass "18. no fake DOI"
pass "19. no fake ORCID"
pass "20. no fake ISSN"
pass "21. no dummy page ranges"
pass "22. verify passes"
pass "25. institutional Pages unaffected"

echo "== rerun plan is idempotent =="
cli rewrite flush --hard >/dev/null
DB_AFTER_APPLY="$(relevant_db_hash)"
RERUN_CODE="$(post_action "$TMP/admin.cookies" "$TMP/plan-rerun.html" validate_plan "$NONCE")"
DB_AFTER_RERUN="$(relevant_db_hash)"
[[ "$RERUN_CODE" == "200" ]] || fail "rerun plan expected 200"
[[ "$DB_AFTER_APPLY" == "$DB_AFTER_RERUN" ]] || fail "rerun plan wrote to the database"
assert_contains "$TMP/plan-rerun.html" "Import gate:</strong> PASS"
assert_contains "$TMP/plan-rerun.html" ">REUSE<"
SAFE_PLAN2="$(extract_field "$TMP/plan-rerun.html" "$PLAN_FIELD")"
pass "23. rerun plan is idempotent"

echo "== adopted content is not overwritten =="
cli post update "$ARTICLE1_ID" --post_title='Título adoptado QA Volume 1' >/dev/null
ADOPT_PLAN_CODE="$(post_action "$TMP/admin.cookies" "$TMP/plan-adopted.html" validate_plan "$NONCE")"
[[ "$ADOPT_PLAN_CODE" == "200" ]] || fail "adopted plan expected 200"
assert_contains "$TMP/plan-adopted.html" "ADOPTED"
SAFE_PLAN3="$(extract_field "$TMP/plan-adopted.html" "$PLAN_FIELD")"
ADOPT_APPLY_CODE="$(post_action "$TMP/admin.cookies" "$TMP/apply-adopted.html" apply "$NONCE" \
	--data-urlencode "${PLAN_FIELD}=${SAFE_PLAN3}" \
	--data-urlencode "${CONFIRM_FIELD}=1")"
[[ "$ADOPT_APPLY_CODE" == "200" ]] || fail "adopted apply expected 200, got $ADOPT_APPLY_CODE"
TITLE_AFTER="$(cli post get "$ARTICLE1_ID" --field=post_title)"
[[ "$TITLE_AFTER" == "Título adoptado QA Volume 1" ]] || fail "adopted article was overwritten"
pass "24. adopted content is not overwritten"

echo "== HTTP routes =="
cli rewrite flush --hard >/dev/null
for path in \
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
pass "26-31. issue/article/author archives and singles return 200"

echo "PASS: Volume 1 editorial bootstrap admin QA"
