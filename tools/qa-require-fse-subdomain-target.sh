#!/usr/bin/env bash
# Isolated contract for ADR 0024 §4: the FSE subdomain FTPS only runs from
# feat/fse and only against the subdomain's own jail, never production.
# No Docker, no WordPress, no hosting, no network. Feeds env values to
# tools/require-fse-subdomain-target.sh and checks its exit code.
#
#   ./tools/qa-require-fse-subdomain-target.sh
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
GATE="$ROOT/tools/require-fse-subdomain-target.sh"

fail() {
	echo "FAIL: $*" >&2
	exit 1
}

# Every value valid. Each case overrides exactly one of them.
VALID=(
	GITHUB_REF=refs/heads/feat/fse
	FSE_FTP_SERVER=ftp.example.test
	FSE_FTP_USERNAME=deploy_fse@logos-et-spes.cenfiss.net
	FSE_FTP_PASSWORD=not-a-real-password
	FSE_THEME_REMOTE_DIR=/wp-content/themes/revistalogos/
	FSE_PLUGIN_REMOTE_DIR=/wp-content/plugins/revistalogos-core/
)

# env -i: nothing from the caller's environment leaks into a case.
run_gate() {
	env -i PATH="$PATH" "${VALID[@]}" "$@" "$GATE" >/dev/null 2>&1
}

expect_pass() {
	local label="$1"
	shift
	run_gate "$@" || fail "gate rejected: ${label}"
	echo "ok   accepts ${label}"
}

expect_fail() {
	local label="$1"
	shift
	if run_gate "$@"; then
		fail "gate accepted: ${label}"
	fi
	echo "ok   refuses ${label}"
}

echo "== ref: only the feat/fse branch =="
expect_pass "feat/fse with a valid subdomain target"
expect_fail "main" GITHUB_REF=refs/heads/main
expect_fail "a production tag" GITHUB_REF=refs/tags/v0.3.20
expect_fail "another branch" GITHUB_REF=refs/heads/feat/fse-session-9
expect_fail "a branch merely named like feat/fse" GITHUB_REF=refs/heads/x/feat/fse
expect_fail "a missing ref" GITHUB_REF=

echo "== FTP account: the subdomain's own jail, never production =="
expect_fail "the production FTP account" FSE_FTP_USERNAME=deploy_revista@logo-et-spes.cenfiss.net
expect_fail "the production domain in another case" FSE_FTP_USERNAME=deploy@LOGO-ET-SPES.cenfiss.net
expect_fail "an account on another domain" FSE_FTP_USERNAME=deploy@cenfiss.net
expect_fail "an account without a domain" FSE_FTP_USERNAME=cenfiss2
expect_pass "the subdomain domain in another case" FSE_FTP_USERNAME=deploy_fse@Logos-Et-Spes.cenfiss.net

echo "== secrets: none may be empty =="
expect_fail "an empty server" FSE_FTP_SERVER=
expect_fail "an empty username" FSE_FTP_USERNAME=
expect_fail "an empty password" FSE_FTP_PASSWORD=
expect_fail "an empty theme dir" FSE_THEME_REMOTE_DIR=
expect_fail "an empty plugin dir" FSE_PLUGIN_REMOTE_DIR=

echo "== remote dirs: exactly the theme and plugin directories =="
expect_pass "a docroot-prefixed theme dir" FSE_THEME_REMOTE_DIR=public_html/wp-content/themes/revistalogos/
expect_fail "the FTP root" FSE_THEME_REMOTE_DIR=/
expect_fail "uploads" FSE_THEME_REMOTE_DIR=/wp-content/uploads/
expect_fail "wp-content itself" FSE_PLUGIN_REMOTE_DIR=/wp-content/
expect_fail "a theme dir without the trailing slash" FSE_THEME_REMOTE_DIR=/wp-content/themes/revistalogos
expect_fail "the plugin dir as theme dir" FSE_THEME_REMOTE_DIR=/wp-content/plugins/revistalogos-core/
expect_fail "the theme dir as plugin dir" FSE_PLUGIN_REMOTE_DIR=/wp-content/themes/revistalogos/
expect_fail "a parent-directory escape" FSE_THEME_REMOTE_DIR=/x/../wp-content/themes/revistalogos/
expect_fail "the production docroot" FSE_PLUGIN_REMOTE_DIR=/logo-et-spes.cenfiss.net/wp-content/plugins/revistalogos-core/

echo "OK: require-fse-subdomain-target only lets feat/fse reach the subdomain jail"
