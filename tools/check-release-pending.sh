#!/usr/bin/env bash
# ADR 0020 companion. Reports when the trunk carries deployable changes that
# no release tag covers yet, so "we forgot to cut a release" is visible before
# somebody needs a production deploy instead of after.
#
# Advisory by design: it never blocks a merge and never deploys. The hard gate
# stays tools/require-production-release-tag.sh, run by deploy-wordpress.yml.
#
# Usage:
#   ./tools/check-release-pending.sh            # report, always exit 0
#   ./tools/check-release-pending.sh --strict   # exit 1 when a release is pending
set -uo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

STRICT=0
[[ "${1:-}" == "--strict" ]] && STRICT=1

# Everything below is a question about history, so say plainly when there is no
# history to ask. Without this, an exported copy (tarball, FTPS upload) would
# reach last_release_tag, find nothing, and report "no annotated tag" — which
# reads as "you forgot to cut a release" when the truth is "this is not a repo".
if ! git rev-parse --is-inside-work-tree >/dev/null 2>&1; then
	echo "Not a Git repository: ${ROOT}"
	echo "This check compares HEAD against the newest annotated release tag,"
	echo "so it needs the repository itself, not an exported copy of the tree."
	exit $(( STRICT ))
fi

if ! git rev-parse --verify --quiet HEAD >/dev/null 2>&1; then
	echo "No commit is checked out in ${ROOT}; there is nothing to compare"
	echo "against a release tag yet."
	exit $(( STRICT ))
fi

PLUGIN_FILE="wordpress/wp-content/plugins/revistalogos-core/revistalogos-core.php"
THEME_FILE="wordpress/wp-content/themes/revistalogos/style.css"
DEPLOYABLE=(
	"wordpress/wp-content/themes/"
	"wordpress/wp-content/plugins/"
)

# Newest annotated vMAJOR.MINOR.PATCH reachable from HEAD. Lightweight tags are
# skipped on purpose: ADR 0020 §3 only accepts annotated ones.
last_release_tag() {
	local t
	while IFS= read -r t; do
		[[ "$t" =~ ^v[0-9]+\.[0-9]+\.[0-9]+$ ]] || continue
		[[ "$(git cat-file -t "$t" 2>/dev/null || true)" == "tag" ]] || continue
		printf '%s\n' "$t"
		return 0
	done < <(git tag --sort=-v:refname --merged HEAD)
	return 1
}

# Version string declared at a given ref, or empty when the file is absent there.
version_at() {
	local ref="$1" file="$2" pattern="$3"
	git show "${ref}:${file}" 2>/dev/null \
		| sed -n -E "s/${pattern}/\1/p" \
		| head -1
}

PLUGIN_PATTERN=".*REVISTALOGOS_CORE_VERSION', '([0-9]+\.[0-9]+\.[0-9]+)'.*"
THEME_PATTERN="^Version:[[:space:]]*([0-9]+\.[0-9]+\.[0-9]+).*"

TAG="$(last_release_tag)" || {
	echo "No annotated vMAJOR.MINOR.PATCH tag is reachable from HEAD."
	echo "A production deploy needs one (ADR 0020). See VERSION.md."
	exit $(( STRICT ))
}

RANGE="${TAG}..HEAD"
COMMITS="$(git rev-list --count "$RANGE" -- "${DEPLOYABLE[@]}")"

echo "Last release tag reachable from HEAD: ${TAG}"

if [[ "$COMMITS" -eq 0 ]]; then
	echo "No theme/plugin commits since ${TAG}. Nothing to release."
	exit 0
fi

echo ""
echo "::warning::${COMMITS} commit(s) touch theme/plugin since ${TAG}; a production deploy needs a newer release tag (ADR 0020)."
echo "Deployable commits since ${TAG}:"
git log --oneline "$RANGE" -- "${DEPLOYABLE[@]}" | sed 's/^/  /'

# The sharper signal: shipped code changed but its declared version did not, so
# the artifact would install over production under a version string already
# live and Plugin::maybe_upgrade() would not fire.
stale_version() {
	local label="$1" file="$2" pattern="$3"
	local changed before after
	changed="$(git rev-list --count "$RANGE" -- "$(dirname "$file")")"
	[[ "$changed" -gt 0 ]] || return 0
	before="$(version_at "$TAG" "$file" "$pattern")"
	after="$(version_at HEAD "$file" "$pattern")"
	if [[ -n "$before" && "$before" == "$after" ]]; then
		echo ""
		echo "::warning::${label} code changed since ${TAG} but its declared version is still ${after}. Bump it before releasing (ADR 0020 §2 step 4)."
		return 1
	fi
	return 0
}

STALE=0
stale_version "Plugin revistalogos-core" "$PLUGIN_FILE" "$PLUGIN_PATTERN" || STALE=1
stale_version "Theme revistalogos" "$THEME_FILE" "$THEME_PATTERN" || STALE=1

echo ""
echo "To release: bump package.json, move CHANGELOG [Sin publicar] to [X.Y.Z],"
echo "update VERSION.md, bump changed theme/plugin headers, land by PR, then"
echo "git tag -a vX.Y.Z -m 'vX.Y.Z' && git push origin vX.Y.Z."

if [[ "$STRICT" -eq 1 ]]; then
	exit 1
fi
exit 0
