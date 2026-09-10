#!/usr/bin/env bash
# Isolated contract for ADR 0020: production FTPS only from a tag ref.
# No Docker, no WordPress, no hosting. Builds a throwaway git repo and
# runs tools/require-production-release-tag.sh against it.
#
#   ./tools/qa-require-production-release-tag.sh
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
GATE="$ROOT/tools/require-production-release-tag.sh"
WORKDIR=""

cleanup() {
	if [[ -n "$WORKDIR" && -d "$WORKDIR" ]]; then
		rm -rf "$WORKDIR"
	fi
}
trap cleanup EXIT

fail() {
	echo "FAIL: $*" >&2
	exit 1
}

# Arrange a repo where the existing tag/version/trunk checks would pass.
# The only variable under test is the GitHub ref the workflow was started from.
seed_release_repo() {
	local dest="$1"
	mkdir -p "$dest/tools"
	cp "$GATE" "$dest/tools/require-production-release-tag.sh"
	chmod +x "$dest/tools/require-production-release-tag.sh"

	git -C "$dest" init --initial-branch=main >/dev/null
	git -C "$dest" config user.email "qa@example.test"
	git -C "$dest" config user.name "QA"
	printf '%s\n' '{"version":"1.2.3"}' >"$dest/package.json"
	git -C "$dest" add package.json
	git -C "$dest" commit -m "release 1.2.3" >/dev/null
	git -C "$dest" tag -a v1.2.3 -m "v1.2.3"
}

run_gate() {
	local dest="$1"
	shift
	(
		cd "$dest"
		env "$@" ./tools/require-production-release-tag.sh
	)
}

WORKDIR="$(mktemp -d "${TMPDIR:-/tmp}/les-release-tag-qa.XXXXXX")"
seed_release_repo "$WORKDIR"

echo "== dispatch from main must fail even when HEAD is tagged =="
# Dado: commit en main + etiqueta anotada que coincide con package.json
# Cuando: workflow_dispatch con ref de rama
# Entonces: el gate aborta
if run_gate "$WORKDIR" GITHUB_REF=refs/heads/main GITHUB_REF_TYPE=branch; then
	fail "gate accepted a branch ref (refs/heads/main) on a tagged commit"
fi

echo "== dispatch from the matching tag may continue =="
# Dado: el mismo release
# Cuando: workflow_dispatch con ref de etiqueta
# Entonces: el gate acepta
run_gate "$WORKDIR" GITHUB_REF=refs/tags/v1.2.3 GITHUB_REF_TYPE=tag \
	|| fail "gate rejected a valid tag ref"

echo "== local preflight without GITHUB_REF still works =="
run_gate "$WORKDIR" \
	|| fail "gate rejected a local tagged checkout (GITHUB_REF unset)"

echo "OK: require-production-release-tag refuses branch refs"
