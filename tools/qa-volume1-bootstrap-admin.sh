#!/usr/bin/env bash
# Isolated QA: Tools → Volume 1 Editorial Bootstrap UI is gone (plugin 0.2.6).
# Historical PASS of the former UI harness is in docs/fase3-validation-matrix.md.
# Domain regression remains tools/qa-editorial-bootstrap.sh.
# Not a formal test suite. Never points at production.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
PROJECT="${LES_BOOTSTRAP_ADMIN_QA_PROJECT:-revistalogos-bootstrap-admin-qa}"
PORT="${LES_BOOTSTRAP_ADMIN_QA_PORT:-8083}"
BASE_URL="http://localhost:${PORT}"
TMP="$(mktemp -d "${TMPDIR:-/tmp}/les-bootstrap-admin-removed-qa.XXXXXX")"
ADMIN_USER="les_bootstrap_admin_removed"
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
	compose down -v --remove-orphans >/dev/null 2>&1 || true
	rm -rf "$TMP"
}
trap cleanup EXIT

if [[ -f "$ROOT/wordpress/wp-content/plugins/revistalogos-core/includes/fixtures/class-bootstrap-admin.php" ]]; then
	fail "class-bootstrap-admin.php must be deleted"
fi
if grep -Eq "Bootstrap_Admin" "$ROOT/wordpress/wp-content/plugins/revistalogos-core/includes/class-plugin.php"; then
	fail "Plugin must not register Bootstrap_Admin"
fi
pass "Bootstrap_Admin source and wiring absent"

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
	--title="LOGO ET SPES bootstrap admin removed QA" \
	--admin_user="$ADMIN_USER" \
	--admin_password="$ADMIN_PASSWORD" \
	--admin_email="bootstrap-admin-removed@example.invalid" \
	--skip-email >/dev/null
cli config set DISABLE_WP_CRON true --raw >/dev/null
cli theme activate revistalogos >/dev/null
cli plugin activate revistalogos-core >/dev/null
cli rewrite structure '/%postname%/' --hard >/dev/null
pass "plugin loads normally"

ABSENT="$(cli eval 'echo class_exists( "Revistalogos_Core\\Bootstrap_Admin" ) ? "yes" : "no";')"
[[ "$ABSENT" == "no" ]] || fail "Bootstrap_Admin class must not load, got $ABSENT"
pass "Bootstrap_Admin class absent at runtime"

cli eval 'echo menu_page_url( "revistalogos-volume-1-bootstrap", false );' >"$TMP/menu.txt"
if grep -Eq "revistalogos-volume-1-bootstrap" "$TMP/menu.txt"; then
	fail "Tools bootstrap page must not be registered"
fi
pass "Tools Volume 1 bootstrap menu absent"

cli post create \
	--post_type=author \
	--post_title='Rafael Eduardo Figueredo Oropeza' \
	--post_name=rafael-eduardo-figueredo-oropeza \
	--post_status=publish \
	--porcelain >/dev/null

cli post create \
	--post_type=page \
	--post_title='QA Institutional Page' \
	--post_name=qa-institutional-page \
	--post_status=publish \
	--post_content='institutional sentinel' \
	--porcelain >/dev/null

cli revistalogos fixtures plan >"$TMP/plan.txt"
grep -Eq "volume-1" "$TMP/plan.txt" || fail "fixtures plan must still work"
pass "Fixtures domain plan still available"

cli revistalogos fixtures bootstrap --apply >"$TMP/apply.txt"
ISSUE_ID="$(cli post list --post_type=issue --meta_key=_les_bootstrap_key --meta_value=volume-1-issue-1 --format=ids)"
ARTICLE_ID="$(cli post list --post_type=article --meta_key=_les_bootstrap_key --meta_value=volume-1-article-1 --format=ids)"
AUTHOR_ID="$(cli post list --post_type=author --name=rafael-eduardo-figueredo-oropeza --format=ids)"
PAGE_ID="$(cli post list --post_type=page --name=qa-institutional-page --format=ids)"
[[ "$ISSUE_ID" =~ ^[0-9]+$ && "$ARTICLE_ID" =~ ^[0-9]+$ && "$AUTHOR_ID" =~ ^[0-9]+$ && "$PAGE_ID" =~ ^[0-9]+$ ]] || fail "bootstrap apply via CLI failed"

HASH_BEFORE="$(cli eval "echo get_post_meta( $ISSUE_ID, '_les_bootstrap_source_hash', true );")"
KEY_BEFORE="$(cli eval "echo get_post_meta( $ISSUE_ID, '_les_bootstrap_key', true );")"
STATUS_BEFORE="$(cli post get "$ARTICLE_ID" --field=post_status)"
AUTHORS_BEFORE="$(cli eval "echo implode(',', array_map('strval', (array) get_post_meta( $ARTICLE_ID, 'authors', true )));")"
RAFAEL_STATUS="$(cli post get "$AUTHOR_ID" --field=post_status)"
PAGE_CONTENT_BEFORE="$(cli post get "$PAGE_ID" --field=post_content)"

cli option update revistalogos_core_version 0.2.5 >/dev/null
cli eval 'Revistalogos_Core\Plugin::maybe_upgrade();'
HASH_AFTER="$(cli eval "echo get_post_meta( $ISSUE_ID, '_les_bootstrap_source_hash', true );")"
KEY_AFTER="$(cli eval "echo get_post_meta( $ISSUE_ID, '_les_bootstrap_key', true );")"
STATUS_AFTER="$(cli post get "$ARTICLE_ID" --field=post_status)"
AUTHORS_AFTER="$(cli eval "echo implode(',', array_map('strval', (array) get_post_meta( $ARTICLE_ID, 'authors', true )));")"
RAFAEL_AFTER="$(cli post get "$AUTHOR_ID" --field=post_status)"
PAGE_CONTENT_AFTER="$(cli post get "$PAGE_ID" --field=post_content)"
VERSION_AFTER="$(cli eval 'echo get_option( Revistalogos_Core\Plugin::VERSION_OPTION );')"

[[ "$HASH_BEFORE" == "$HASH_AFTER" ]] || fail "upgrade mutated bootstrap hash"
[[ "$KEY_BEFORE" == "$KEY_AFTER" && "$KEY_AFTER" == "volume-1-issue-1" ]] || fail "upgrade mutated bootstrap key"
[[ "$STATUS_BEFORE" == "$STATUS_AFTER" ]] || fail "upgrade mutated article status"
[[ "$AUTHORS_BEFORE" == "$AUTHORS_AFTER" ]] || fail "upgrade mutated authors"
[[ "$RAFAEL_STATUS" == "$RAFAEL_AFTER" && "$RAFAEL_AFTER" == "publish" ]] || fail "upgrade mutated Rafael"
[[ "$PAGE_CONTENT_BEFORE" == "$PAGE_CONTENT_AFTER" ]] || fail "upgrade mutated institutional page"
[[ "$VERSION_AFTER" == "0.2.8" ]] || fail "upgrade did not record plugin version 0.2.8"
pass "plugin upgrade does not alter Volume 1 objects, Rafael, or Pages"

cli eval 'echo class_exists( "Revistalogos_Core\\Fixtures" ) ? "yes" : "no";' | grep -Eq '^yes$' || fail "Fixtures class must remain"
pass "Fixtures domain still available"

cli revistalogos fixtures verify >"$TMP/verify.txt"
pass "fixtures verify still available via CLI"

cli revistalogos fixtures teardown --help >"$TMP/teardown-help.txt"
grep -Eqi "teardown" "$TMP/teardown-help.txt" || fail "teardown CLI must remain"

# Recursive guard over a directory: treat grep's exit codes explicitly
# (0 = match, 1 = no match, >=2 = grep error) so a real failure — e.g. the
# path is gone or -R is unsupported — cannot masquerade as "no admin page",
# which is exactly the silent-pass this harness change is meant to remove.
FIXTURES_INCLUDES="$ROOT/wordpress/wp-content/plugins/revistalogos-core/includes/fixtures"
[[ -d "$FIXTURES_INCLUDES" ]] || fail "fixtures includes directory missing: $FIXTURES_INCLUDES"
GREP_RC=0
grep -REq "add_management_page|add_submenu_page" "$FIXTURES_INCLUDES" || GREP_RC=$?
case "$GREP_RC" in
	0) fail "fixtures includes must not register an admin Tools page" ;;
	1) ;;
	*) fail "grep failed (exit $GREP_RC) scanning $FIXTURES_INCLUDES" ;;
esac
pass "teardown remains CLI/dev only"

echo "== HTTP routes =="
cli rewrite flush --hard >/dev/null 2>&1 || true
for path in /revista/numeros/ /revista/articulos/ /revista/autores/; do
	code="$(curl -sS -o /dev/null -w '%{http_code}' "${BASE_URL}${path}")"
	[[ "$code" == "200" ]] || fail "expected 200 for $path, got $code"
done
pass "archives 200 after Bootstrap_Admin removal"
pass "Volume 1 bootstrap admin removal QA"
