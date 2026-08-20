#!/usr/bin/env bash
# Isolated local Docker QA for the temporary institutional recovery screen.
# Never points at production and never reuses the primary local Docker volumes.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
PROJECT="${LES_RECOVERY_QA_PROJECT:-revistalogos-recovery-qa}"
PORT="${LES_RECOVERY_QA_PORT:-8081}"
BASE_URL="http://localhost:${PORT}"
ADMIN_URL="${BASE_URL}/wp-admin/tools.php?page=revistalogos-institutional-content"
TMP="$(mktemp -d "${TMPDIR:-/tmp}/les-recovery-qa.XXXXXX")"
ADMIN_USER="les_recovery_admin"
ADMIN_PASSWORD="local-qa-admin-$(openssl rand -hex 8)"
SUBSCRIBER_USER="les_recovery_subscriber"
SUBSCRIBER_PASSWORD="local-qa-subscriber-$(openssl rand -hex 8)"
NONCE_FIELD="revistalogos_content_recovery_nonce"
ACTION_FIELD="revistalogos_content_recovery_action"
PLAN_FIELD="revistalogos_content_recovery_plan"
BACKUP_FIELD="revistalogos_content_recovery_backup"
CONFIRM_FIELD="revistalogos_content_recovery_confirm"

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

relevant_db_hash() {
	local prefix
	prefix="$(cli db prefix)"

	{
		cli db query "SELECT * FROM ${prefix}posts ORDER BY ID" --skip-column-names --batch --raw
		cli db query "SELECT * FROM ${prefix}postmeta ORDER BY meta_id" --skip-column-names --batch --raw
		cli db query "SELECT * FROM ${prefix}options WHERE option_name IN ('show_on_front','page_on_front','page_for_posts','wp_page_for_privacy_policy','theme_mods_revistalogos') ORDER BY option_name" --skip-column-names --batch --raw
		cli db query "SELECT * FROM ${prefix}terms ORDER BY term_id" --skip-column-names --batch --raw
		cli db query "SELECT * FROM ${prefix}term_taxonomy ORDER BY term_taxonomy_id" --skip-column-names --batch --raw
		cli db query "SELECT * FROM ${prefix}term_relationships ORDER BY object_id, term_taxonomy_id" --skip-column-names --batch --raw
		cli db query "SELECT * FROM ${prefix}termmeta ORDER BY meta_id" --skip-column-names --batch --raw
	} | shasum -a 256 | awk '{print $1}'
}

count_html_matches() {
	local file="$1"
	local text="$2"

	node - "$file" "$text" <<'NODE'
const fs = require('node:fs');
const [file, text] = process.argv.slice(2);
const html = fs.readFileSync(file, 'utf8');
process.stdout.write(String(html.split(text).length - 1));
NODE
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
	--title="LOGO ET SPES recovery QA" \
	--admin_user="$ADMIN_USER" \
	--admin_password="$ADMIN_PASSWORD" \
	--admin_email="recovery-admin@example.invalid" \
	--skip-email >/dev/null
cli config set DISABLE_WP_CRON true --raw >/dev/null
cli theme activate revistalogos >/dev/null
cli plugin activate revistalogos-core >/dev/null
cli rewrite structure '/%postname%/' --hard >/dev/null
cli user create "$SUBSCRIBER_USER" "recovery-subscriber@example.invalid" \
	--role=subscriber \
	--user_pass="$SUBSCRIBER_PASSWORD" >/dev/null

CORE_VERSION="$(cli core version)"
DB_OK="$(cli db check)"
PLUGIN_STATUS="$(cli plugin status revistalogos-core)"
THEME_STATUS="$(cli theme status revistalogos)"
assert_contains <(printf '%s' "$DB_OK") "Database checked"
assert_contains <(printf '%s' "$PLUGIN_STATUS") "Status: Active"
assert_contains <(printf '%s' "$THEME_STATUS") "Status: Active"
[[ "$CORE_VERSION" == "7.0.4" ]] || fail "expected WordPress 7.0.4, got $CORE_VERSION"
pass "WordPress 7.0.4, database, revistalogos theme and revistalogos-core plugin available"

echo "== PHP syntax and shared WP-CLI service =="
compose run --rm --entrypoint php wpcli -l wp-content/plugins/revistalogos-core/includes/migration/class-content-migrator.php >/dev/null
compose run --rm --entrypoint php wpcli -l wp-content/plugins/revistalogos-core/includes/migration/class-content-recovery-admin.php >/dev/null
compose run --rm --entrypoint php wpcli -l wp-content/plugins/revistalogos-core/includes/cli/class-content-command.php >/dev/null
cli revistalogos content validate >"$TMP/cli-validate.txt"
assert_contains "$TMP/cli-validate.txt" "Payload is valid"
pass "Content_Migrator validation remains available through WP-CLI"

echo "== authentication and capability =="
ANON_CODE="$(curl -sS -o "$TMP/anonymous.html" -w '%{http_code}' "$ADMIN_URL")"
[[ "$ANON_CODE" == "302" ]] || fail "anonymous access expected 302 to login, got $ANON_CODE"

login "$ADMIN_USER" "$ADMIN_PASSWORD" "$TMP/admin.cookies" "$TMP/admin-login.html"
ADMIN_CODE="$(curl -sS -b "$TMP/admin.cookies" -o "$TMP/admin-page.html" -w '%{http_code}' "$ADMIN_URL")"
[[ "$ADMIN_CODE" == "200" ]] || fail "admin access expected 200, got $ADMIN_CODE"
assert_contains "$TMP/admin-page.html" "Institutional Content Import"
assert_contains "$TMP/admin-page.html" "Validate and Plan"
pass "administrator can access; anonymous user is redirected to login"

login "$SUBSCRIBER_USER" "$SUBSCRIBER_PASSWORD" "$TMP/subscriber.cookies" "$TMP/subscriber-login.html"
SUBSCRIBER_CODE="$(curl -sS -b "$TMP/subscriber.cookies" -o "$TMP/subscriber-page.html" -w '%{http_code}' "$ADMIN_URL")"
[[ "$SUBSCRIBER_CODE" == "403" ]] || fail "subscriber access expected 403, got $SUBSCRIBER_CODE"
pass "non-administrator receives HTTP 403"

echo "== nonce enforcement =="
NONCELESS_CODE="$(curl -sS -X POST -b "$TMP/admin.cookies" -o "$TMP/nonceless.html" -w '%{http_code}' \
	--data-urlencode "${ACTION_FIELD}=validate_plan" \
	"$ADMIN_URL")"
assert_contains "$TMP/nonceless.html" "Security check failed"
[[ "$NONCELESS_CODE" == "403" ]] || fail "missing nonce expected 403, got $NONCELESS_CODE"
pass "POST without nonce is rejected"

echo "== read-only Validate and Plan =="
NONCE="$(extract_field "$TMP/admin-page.html" "$NONCE_FIELD")"
DB_BEFORE="$(relevant_db_hash)"
PLAN_CODE="$(post_action "$TMP/admin.cookies" "$TMP/plan.html" validate_plan "$NONCE")"
DB_AFTER="$(relevant_db_hash)"
[[ "$PLAN_CODE" == "200" ]] || fail "Validate and Plan expected 200, got $PLAN_CODE"
[[ "$DB_BEFORE" == "$DB_AFTER" ]] || fail "Validate and Plan changed relevant database state"
assert_contains "$TMP/plan.html" "Result:</strong> PASS"
assert_contains "$TMP/plan.html" "Import gate:</strong> PASS"
assert_contains "$TMP/plan.html" "would import"
assert_contains "$TMP/plan.html" ">create<"
MISSING_COUNT="$(count_html_matches "$TMP/plan.html" "MISSING")"
[[ "$MISSING_COUNT" == "12" ]] || fail "expected 12 MISSING classifications, got $MISSING_COUNT"
SAFE_PLAN="$(extract_field "$TMP/plan.html" "$PLAN_FIELD")"
pass "Validate and Plan is read-only; all 12 protected slugs are MISSING/create"
echo "read-only relevant DB hash before: $DB_BEFORE"
echo "read-only relevant DB hash after:  $DB_AFTER"

echo "== manual collision blocks import =="
MANUAL_ID="$(cli post create --post_type=page --post_title='Normas manual QA' --post_name=normas --post_status=publish --porcelain)"
MANUAL_PLAN_CODE="$(post_action "$TMP/admin.cookies" "$TMP/manual-plan.html" validate_plan "$NONCE")"
[[ "$MANUAL_PLAN_CODE" == "200" ]] || fail "manual collision plan expected 200"
assert_contains "$TMP/manual-plan.html" "MANUAL EXISTING"
assert_contains "$TMP/manual-plan.html" "Import gate:</strong> BLOCKED"
MANUAL_IMPORT_CODE="$(post_action "$TMP/admin.cookies" "$TMP/manual-import.html" import "$NONCE" \
	--data-urlencode "${PLAN_FIELD}=${SAFE_PLAN}" \
	--data-urlencode "${BACKUP_FIELD}=LOCAL-QA-BACKUP" \
	--data-urlencode "${CONFIRM_FIELD}=1")"
[[ "$MANUAL_IMPORT_CODE" == "200" ]] || fail "blocked manual collision import expected 200"
assert_contains "$TMP/manual-import.html" "Import is blocked by a MANUAL EXISTING or AMBIGUOUS protected slug"
[[ -z "$(cli post list --post_type=page --name=normas-2 --format=ids)" ]] || fail "blocked import created normas-2"
cli post delete "$MANUAL_ID" --force >/dev/null
[[ -z "$(cli post list --post_type=page --name=normas --format=ids)" ]] || fail "manual collision Page cleanup failed"
pass "MANUAL EXISTING blocks import; no normas-2 is created; local test Page removed"

echo "== ambiguous collision blocks import =="
AMBIGUOUS_ID="$(cli post create --post_type=page --post_title='Acerca ambiguous QA' --post_name=acerca --post_status=publish --porcelain)"
cli post meta update "$AMBIGUOUS_ID" _les_source_key wrong-source >/dev/null
AMBIGUOUS_PLAN_CODE="$(post_action "$TMP/admin.cookies" "$TMP/ambiguous-plan.html" validate_plan "$NONCE")"
[[ "$AMBIGUOUS_PLAN_CODE" == "200" ]] || fail "ambiguous collision plan expected 200"
assert_contains "$TMP/ambiguous-plan.html" "AMBIGUOUS"
assert_contains "$TMP/ambiguous-plan.html" "Import gate:</strong> BLOCKED"
[[ -z "$(cli post list --post_type=page --name=acerca-2 --format=ids)" ]] || fail "ambiguous preflight created acerca-2"
cli post delete "$AMBIGUOUS_ID" --force >/dev/null

FIXTURE_ID="$(cli post create --post_type=page --post_title='Ética fixture contamination QA' --post_name=etica --post_status=publish --porcelain)"
cli post meta update "$FIXTURE_ID" _les_source_key etica >/dev/null
cli post meta update "$FIXTURE_ID" _les_fixture 1 >/dev/null
FIXTURE_PLAN_CODE="$(post_action "$TMP/admin.cookies" "$TMP/fixture-plan.html" validate_plan "$NONCE")"
[[ "$FIXTURE_PLAN_CODE" == "200" ]] || fail "fixture contamination plan expected 200"
assert_contains "$TMP/fixture-plan.html" "AMBIGUOUS"
assert_contains "$TMP/fixture-plan.html" "contaminated by a fixture marker"
assert_contains "$TMP/fixture-plan.html" "Import gate:</strong> BLOCKED"
cli post delete "$FIXTURE_ID" --force >/dev/null
pass "mismatched source keys and fixture contamination are AMBIGUOUS and block import"

echo "== backup, confirmation, stale-plan and force guards =="
SAFE_PLAN_CODE="$(post_action "$TMP/admin.cookies" "$TMP/safe-plan.html" validate_plan "$NONCE")"
[[ "$SAFE_PLAN_CODE" == "200" ]] || fail "safe plan expected 200"
SAFE_PLAN="$(extract_field "$TMP/safe-plan.html" "$PLAN_FIELD")"
assert_not_contains "$TMP/safe-plan.html" 'name="force"'
rg -F -q '$migrator->import_report( true, false )' \
	wordpress/wp-content/plugins/revistalogos-core/includes/migration/class-content-recovery-admin.php \
	|| fail "admin import does not visibly pin force=false"

NO_BACKUP_CODE="$(post_action "$TMP/admin.cookies" "$TMP/no-backup.html" import "$NONCE" \
	--data-urlencode "${PLAN_FIELD}=${SAFE_PLAN}" \
	--data-urlencode "${CONFIRM_FIELD}=1")"
[[ "$NO_BACKUP_CODE" == "200" ]] || fail "missing backup response expected 200"
assert_contains "$TMP/no-backup.html" "Backup evidence is required"

NO_CONFIRM_CODE="$(post_action "$TMP/admin.cookies" "$TMP/no-confirm.html" import "$NONCE" \
	--data-urlencode "${PLAN_FIELD}=${SAFE_PLAN}" \
	--data-urlencode "${BACKUP_FIELD}=LOCAL-QA-BACKUP")"
[[ "$NO_CONFIRM_CODE" == "200" ]] || fail "missing confirmation response expected 200"
assert_contains "$TMP/no-confirm.html" "Explicit import confirmation is required"

NO_PLAN_CODE="$(post_action "$TMP/admin.cookies" "$TMP/no-plan.html" import "$NONCE" \
	--data-urlencode "${BACKUP_FIELD}=LOCAL-QA-BACKUP" \
	--data-urlencode "${CONFIRM_FIELD}=1")"
[[ "$NO_PLAN_CODE" == "200" ]] || fail "missing plan response expected 200"
assert_contains "$TMP/no-plan.html" "The plan is missing or stale"
[[ "$(cli post list --post_type=page --meta_key=_les_source_key --format=count)" == "0" ]] || fail "guard failure wrote migrated Pages"
pass "backup evidence, explicit confirmation and current read-only plan are required; force is unavailable"

echo "== runtime media failure stops later import stages =="
cli option update upload_path '/var/www/html/wp-content/plugins/revistalogos-core/resources/content-payload.json' >/dev/null
RUNTIME_ERROR_CODE="$(post_action "$TMP/admin.cookies" "$TMP/runtime-error.html" import "$NONCE" \
	--data-urlencode "${PLAN_FIELD}=${SAFE_PLAN}" \
	--data-urlencode "${BACKUP_FIELD}=LOCAL-QA-BACKUP" \
	--data-urlencode "${CONFIRM_FIELD}=1")"
[[ "$RUNTIME_ERROR_CODE" == "200" ]] || fail "runtime-error import expected 200"
assert_contains "$TMP/runtime-error.html" "Institutional import stopped with runtime errors"
assert_contains "$TMP/runtime-error.html" "Page and site-setting stages were not run because media import failed"
assert_contains "$TMP/runtime-error.html" "Overall:</strong> FAIL"
[[ "$(cli post list --post_type=page --meta_key=_les_source_key --format=count)" == "0" ]] || fail "media failure still imported Pages"
[[ "$(cli post list --post_type=attachment --meta_key=_les_source_key --format=count)" == "0" ]] || fail "media failure left migrated attachments"
cli option delete upload_path >/dev/null
RECOVERY_PLAN_CODE="$(post_action "$TMP/admin.cookies" "$TMP/recovered-plan.html" validate_plan "$NONCE")"
[[ "$RECOVERY_PLAN_CODE" == "200" ]] || fail "post-error recovery plan expected 200"
SAFE_PLAN="$(extract_field "$TMP/recovered-plan.html" "$PLAN_FIELD")"
pass "media runtime errors are visible, Verify fails and Page/settings stages do not run"

echo "== institutional import and automatic verify =="
USERS_BEFORE="$(cli user list --format=count)"
ISSUES_BEFORE="$(cli post list --post_type=issue --format=count)"
ARTICLES_BEFORE="$(cli post list --post_type=article --format=count)"
AUTHORS_BEFORE="$(cli post list --post_type=author --format=count)"
IMPORT_CODE="$(post_action "$TMP/admin.cookies" "$TMP/import.html" import "$NONCE" \
	--data-urlencode "${PLAN_FIELD}=${SAFE_PLAN}" \
	--data-urlencode "${BACKUP_FIELD}=LOCAL-QA-JETBACKUP-2026-08-19" \
	--data-urlencode "${CONFIRM_FIELD}=1" \
	--data-urlencode "force=1")"
[[ "$IMPORT_CODE" == "200" ]] || fail "institutional import expected 200, got $IMPORT_CODE"
assert_contains "$TMP/import.html" "Institutional import completed without force"
assert_contains "$TMP/import.html" "Overall:</strong> PASS"

[[ "$(cli post list --post_type=page --meta_key=_les_source_key --format=count)" == "12" ]] || fail "expected 12 migrated Pages"
[[ "$(cli post list --post_type=attachment --meta_key=_les_source_key --format=count)" == "3" ]] || fail "expected 3 migrated media attachments"

cli eval '
$migrator = new \Revistalogos_Core\Content_Migrator();
$loaded = $migrator->load();
if ( is_wp_error( $loaded ) ) {
	fwrite( STDERR, $loaded->get_error_message() );
	exit( 1 );
}
foreach ( $migrator->payload()["entries"] as $entry ) {
	$page = $migrator->find_by_source_key( $entry["source_key"], "page" );
	if ( ! $page || $page->post_name !== $entry["slug"] ) {
		fwrite( STDERR, "missing Page or wrong slug: " . $entry["source_key"] );
		exit( 1 );
	}
	foreach ( array( "_les_source_key", "_les_source_hash", "_les_migration_version", "_les_migration_owned", "_les_imported_hash" ) as $meta_key ) {
		if ( "" === get_post_meta( $page->ID, $meta_key, true ) ) {
			fwrite( STDERR, "missing marker " . $meta_key . ": " . $entry["source_key"] );
			exit( 1 );
		}
	}
	if ( "" !== (string) get_post_meta( $page->ID, "_les_fixture", true ) ) {
		fwrite( STDERR, "fixture marker found: " . $entry["source_key"] );
		exit( 1 );
	}
}
echo "12";
' >"$TMP/markers.txt"
[[ "$(tr -d '[:space:]' <"$TMP/markers.txt")" == "12" ]] || fail "migration marker verification failed"

cli eval '
$migrator = new \Revistalogos_Core\Content_Migrator();
$loaded = $migrator->load();
if ( is_wp_error( $loaded ) ) {
	fwrite( STDERR, $loaded->get_error_message() );
	exit( 1 );
}
$site = $migrator->payload()["site"];
$front = $migrator->find_by_source_key( $site["front_page"] );
$posts = $migrator->find_by_source_key( $site["posts_page"] );
$privacy = $migrator->find_by_source_key( $site["privacy_page"] );
$expected_settings = array(
	"show_on_front" => $site["show_on_front"],
	"page_on_front" => $front ? $front->ID : 0,
	"page_for_posts" => $posts ? $posts->ID : 0,
	"wp_page_for_privacy_policy" => $privacy ? $privacy->ID : 0,
);
foreach ( $expected_settings as $option => $expected ) {
	if ( (string) get_option( $option ) !== (string) $expected ) {
		fwrite( STDERR, "wrong setting: " . $option );
		exit( 1 );
	}
}
$locations = get_theme_mod( "nav_menu_locations", array() );
foreach ( $site["menus"] as $location => $config ) {
	$menu = wp_get_nav_menu_object( $config["name"] );
	if ( ! $menu || empty( $locations[ $location ] ) || (int) $locations[ $location ] !== (int) $menu->term_id ) {
		fwrite( STDERR, "missing menu or location: " . $location );
		exit( 1 );
	}
	$expected_titles = array();
	foreach ( $config["items"] as $item ) {
		$expected_titles[] = $item["title"];
		foreach ( $item["children"] ?? array() as $child ) {
			$expected_titles[] = $child["title"];
		}
	}
	$actual_titles = array_map(
		static function ( $item ) {
			return $item->title;
		},
		(array) wp_get_nav_menu_items( $menu->term_id )
	);
	if ( $expected_titles !== $actual_titles ) {
		fwrite( STDERR, "wrong menu items: " . $location );
		exit( 1 );
	}
}
echo "settings+menus";
' >"$TMP/site-state.txt"
[[ "$(tr -d '[:space:]' <"$TMP/site-state.txt")" == "settings+menus" ]] || fail "reading settings/menu verification failed"

[[ "$(cli post list --post_type=page --meta_key=_les_fixture --meta_value=1 --format=count)" == "0" ]] || fail "institutional Page has fixture marker"
[[ "$(cli post list --post_type=issue --format=count)" == "$ISSUES_BEFORE" ]] || fail "import touched issue records"
[[ "$(cli post list --post_type=article --format=count)" == "$ARTICLES_BEFORE" ]] || fail "import touched article records"
[[ "$(cli post list --post_type=author --format=count)" == "$AUTHORS_BEFORE" ]] || fail "import touched author records"
[[ "$(cli user list --format=count)" == "$USERS_BEFORE" ]] || fail "import touched users"
cli revistalogos content verify >"$TMP/cli-verify.txt"
assert_contains "$TMP/cli-verify.txt" "All migrated objects verified"
pass "import created 12 Pages, 3 media, 3 menus and reading settings with migration markers only"
pass "no fixtures, issues, articles, authors or users were created or changed"

echo "== migration-owned and idempotent re-plan =="
POST_IMPORT_NONCE="$(extract_field "$TMP/import.html" "$NONCE_FIELD")"
REPLAN_CODE="$(post_action "$TMP/admin.cookies" "$TMP/replan.html" validate_plan "$POST_IMPORT_NONCE")"
[[ "$REPLAN_CODE" == "200" ]] || fail "post-import plan expected 200"
OWNED_COUNT="$(count_html_matches "$TMP/replan.html" "MIGRATION OWNED")"
UNCHANGED_COUNT="$(count_html_matches "$TMP/replan.html" ">skip<")"
[[ "$OWNED_COUNT" == "12" ]] || fail "expected 12 MIGRATION OWNED rows, got $OWNED_COUNT"
[[ "$UNCHANGED_COUNT" == "12" ]] || fail "expected 12 skip plan actions, got $UNCHANGED_COUNT"
assert_contains "$TMP/replan.html" "Import gate:</strong> PASS"
pass "all protected slugs are MIGRATION OWNED; re-plan is idempotent with 12 skip actions"

echo "== explicit Verify action =="
VERIFY_NONCE="$(extract_field "$TMP/replan.html" "$NONCE_FIELD")"
VERIFY_CODE="$(post_action "$TMP/admin.cookies" "$TMP/verify.html" verify "$VERIFY_NONCE")"
[[ "$VERIFY_CODE" == "200" ]] || fail "Verify action expected 200"
assert_contains "$TMP/verify.html" "Overall:</strong> PASS"
assert_contains "$TMP/verify.html" "Missing: 0"
assert_contains "$TMP/verify.html" "Contamination/errors"
VERIFY_OK_COUNT="$(count_html_matches "$TMP/verify.html" ">OK<")"
[[ "$VERIFY_OK_COUNT" == "15" ]] || fail "expected 15 Verify OK rows, got $VERIFY_OK_COUNT"
pass "Verify reports overall PASS with no missing, stale, drifted or contaminated entries"

echo "== institutional HTTP smoke =="
ROUTES=(
	"/"
	"/normas/"
	"/enviar-colaboracion/"
	"/acerca/"
	"/contacto/"
	"/noticias/"
	"/etica/"
	"/politicas/"
	"/comite-editorial/"
	"/privacidad/"
	"/buscar/"
	"/enlaces/"
)

for route in "${ROUTES[@]}"; do
	status="$(curl -sS -o /dev/null -w '%{http_code}' "${BASE_URL}${route}")"
	echo "${route} ${status}"
	[[ "$status" == "200" ]] || fail "${route} returned HTTP ${status}"
done
pass "all 12 representative institutional routes return HTTP 200"

echo "PASS: temporary institutional recovery admin QA"
