#!/usr/bin/env bash
# ADR 0024 §4: the FSE subdomain logos-et-spes.cenfiss.net has its own
# deploy, never production's. This gate runs before any FTPS upload in
# .github/workflows/deploy-fse-subdomain.yml and refuses:
#
#   1. any ref other than the feat/fse branch. While the subdomain is a
#      noindex beta it ships from feat/fse with no production tag (#75).
#      main and vX.Y.Z tags carry the classic line, which must not land
#      there, and the FSE major must not ride a production tag;
#   2. an empty secret, so a half-configured Environment fails here and
#      not halfway through an upload;
#   3. an FTP account outside the subdomain. The account must be
#      user@logos-et-spes.cenfiss.net (mind the S). The production account
#      lives on logo-et-spes.cenfiss.net, so this is an allow-list, not a
#      deny-list: a typo cannot fall through to production;
#   4. remote dirs other than exactly wp-content/themes/revistalogos/ and
#      wp-content/plugins/revistalogos-core/ (optionally under a docroot
#      prefix), with no `..` and no production docroot. uploads/, the
#      WordPress root and wp-config.php are never a target.
#
# It never prints a secret value. It does not talk to hosting.
#
# Inputs (environment):
#   GITHUB_REF, FSE_FTP_SERVER, FSE_FTP_USERNAME, FSE_FTP_PASSWORD,
#   FSE_THEME_REMOTE_DIR, FSE_PLUGIN_REMOTE_DIR
#
# Contract: ./tools/qa-require-fse-subdomain-target.sh
set -euo pipefail

SUBDOMAIN="logos-et-spes.cenfiss.net"
PRODUCTION="logo-et-spes.cenfiss.net"
ALLOWED_REF="refs/heads/feat/fse"

errors=0

refuse() {
	echo "$*" >&2
	errors=$((errors + 1))
}

lower() {
	printf '%s' "$1" | tr '[:upper:]' '[:lower:]'
}

# --- 1. ref ----------------------------------------------------------------
REF="${GITHUB_REF:-}"
if [[ "$REF" != "$ALLOWED_REF" ]]; then
	refuse "The FSE subdomain deploys only from ${ALLOWED_REF}, not from '${REF:-<unset>}' (ADR 0024 §4, #75)."
	refuse "Actions → Run workflow → Use workflow from → Branch: feat/fse. Never main, never a vX.Y.Z tag."
fi

# --- 2. no empty secret ----------------------------------------------------
for name in FSE_FTP_SERVER FSE_FTP_USERNAME FSE_FTP_PASSWORD FSE_THEME_REMOTE_DIR FSE_PLUGIN_REMOTE_DIR; do
	if [[ -z "${!name:-}" ]]; then
		refuse "Secret ${name} is empty or missing in Environment wordpress-fse-subdomain."
	fi
done

# --- 3. the subdomain's own FTP account -----------------------------------
USERNAME="$(lower "${FSE_FTP_USERNAME:-}")"
if [[ -n "$USERNAME" ]]; then
	if [[ "$USERNAME" == *"@${PRODUCTION}" ]]; then
		refuse "FSE_FTP_USERNAME is the production FTP account (@${PRODUCTION}). The FSE major never reaches production (ADR 0024)."
	elif [[ "$USERNAME" != *"@${SUBDOMAIN}" ]]; then
		refuse "FSE_FTP_USERNAME must be an account of the subdomain's own jail, user@${SUBDOMAIN}."
	fi
fi

# --- 4. bounded remote directories ----------------------------------------
check_dir() {
	local name="$1" suffix="$2"
	local value
	value="$(lower "${!name:-}")"
	[[ -n "$value" ]] || return 0

	if [[ "$value" == *".."* ]]; then
		refuse "${name} must not contain '..'."
	fi
	if [[ "$value" == *"${PRODUCTION}"* ]]; then
		refuse "${name} points into the production docroot (${PRODUCTION})."
	fi
	if [[ "$value" != "$suffix" && "$value" != *"/${suffix}" ]]; then
		refuse "${name} must be exactly the ${suffix} directory (with its trailing slash), optionally under a docroot prefix."
	fi
}

check_dir FSE_THEME_REMOTE_DIR "wp-content/themes/revistalogos/"
check_dir FSE_PLUGIN_REMOTE_DIR "wp-content/plugins/revistalogos-core/"

if ((errors > 0)); then
	echo "Refused: ${errors} problem(s). Nothing was uploaded." >&2
	exit 1
fi

echo "FSE subdomain target OK"
echo "  ref     ${REF}"
echo "  account user@${SUBDOMAIN}"
echo "  theme   wp-content/themes/revistalogos/"
echo "  plugin  wp-content/plugins/revistalogos-core/"
