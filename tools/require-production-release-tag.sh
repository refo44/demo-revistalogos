#!/usr/bin/env bash
# ADR 0020: production FTPS only from an annotated git tag vMAJOR.MINOR.PATCH.
# Merge to main is not a deploy. Does not talk to hosting.
#
# Usage:
#   ./tools/require-production-release-tag.sh
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

if ! git rev-parse --is-inside-work-tree >/dev/null 2>&1; then
	echo "Not a git repository." >&2
	exit 1
fi

HEAD="$(git rev-parse HEAD)"
found=""
while IFS= read -r t; do
	[[ -n "$t" ]] || continue
	if [[ "$t" =~ ^v[0-9]+\.[0-9]+\.[0-9]+$ ]] && [[ "$(git cat-file -t "$t" 2>/dev/null || true)" == "tag" ]]; then
		found="$t"
		break
	fi
done < <(git tag --points-at HEAD)

if [[ -z "$found" ]]; then
	echo "Production deploy requires an annotated git tag vMAJOR.MINOR.PATCH on this commit (ADR 0020)." >&2
	echo "HEAD: ${HEAD}" >&2
	echo "Tags pointing at HEAD:" >&2
	git tag --points-at HEAD >&2 || true
	echo "Do not deploy after every merge. Bump the project version (VERSION.md), tag, then Run workflow from that tag." >&2
	exit 1
fi

echo "Release ${found} (${HEAD})"
