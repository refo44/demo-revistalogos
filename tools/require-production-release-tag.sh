#!/usr/bin/env bash
# ADR 0020: production FTPS only from an annotated git tag vMAJOR.MINOR.PATCH.
# Merge to main is not a deploy. Does not talk to hosting.
#
# Checking that such a tag *exists* is not enough. A well-formed annotated tag
# sitting on the wrong commit passes that test and deploys the wrong tree in
# silence — including reinstalling a plugin version older than the one already
# live. That happened on 2026-08-29: v0.3.0 landed on a CI working branch
# carrying project 0.2.0 and plugin 0.2.8, and only dispatching from `main`
# (which this gate does reject) avoided the bad deploy.
#
# So the tag must also be what it claims to be:
#   1. an annotated vMAJOR.MINOR.PATCH tag on HEAD;
#   2. package.json declaring exactly that version (VERSION.md: package.json is
#      the canonical source and every tag must reflect it);
#   3. the tagged commit reachable from main (ADR 0020 §4: the tagged commit
#      lives on the trunk).
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

# --- 1. an annotated vMAJOR.MINOR.PATCH tag on HEAD -------------------------
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

# Remediation for both failures below. `git tag -a` refuses to move a tag that
# already exists, and by the time either failure prints, the tag does exist and
# has almost certainly been pushed — that is how the workflow was dispatched.
# So the instruction has to be delete-and-recreate, on the remote too, or it is
# not executable in the one situation where it is printed.
retag_instructions() {
	local tag="$1"
	{
		echo "Re-tag the release commit on main. The tag already exists, so moving it"
		echo "means deleting and recreating it, locally and on the remote:"
		echo "  git tag -d ${tag}"
		echo "  git push origin :refs/tags/${tag}"
		echo "  git tag -a ${tag} -m \"${tag}\" origin/main"
		echo "  git push origin ${tag}"
		echo "Anyone who already fetched ${tag} keeps the old object until they prune,"
		echo "so announce it rather than assuming the rewrite is invisible."
	} >&2
}

# --- 2. package.json must declare exactly that version ----------------------
TAG_VERSION="${found#v}"
PKG_VERSION="$(sed -n 's/.*"version"[[:space:]]*:[[:space:]]*"\([^"]*\)".*/\1/p' package.json | head -1)"

if [[ -z "$PKG_VERSION" ]]; then
	echo "Could not read \"version\" from package.json; cannot confirm ${found} is what it claims to be." >&2
	exit 1
fi

if [[ "$PKG_VERSION" != "$TAG_VERSION" ]]; then
	echo "Tag ${found} does not match the tree it points at (ADR 0020)." >&2
	echo "  tag says       : ${TAG_VERSION}" >&2
	echo "  package.json   : ${PKG_VERSION}" >&2
	echo "  commit         : ${HEAD}" >&2
	echo "The tag was almost certainly created on the wrong commit. Deploying it would ship a tree that is not this release." >&2
	retag_instructions "$found"
	exit 1
fi

# --- 3. the tagged commit must be reachable from main -----------------------
# Resolved explicitly rather than skipped when missing: a check that quietly
# does nothing on a shallow clone is the failure mode this gate exists to stop.
MAIN_REF=""
for candidate in refs/remotes/origin/main refs/heads/main; do
	if git rev-parse --verify --quiet "$candidate" >/dev/null; then
		MAIN_REF="$candidate"
		break
	fi
done

if [[ -z "$MAIN_REF" ]]; then
	echo "Cannot resolve main, so ${found} cannot be confirmed to live on the trunk (ADR 0020 §4)." >&2
	echo "In CI this means the checkout is shallow: set fetch-depth: 0 on the tag-gate job." >&2
	exit 1
fi

if ! git merge-base --is-ancestor "$HEAD" "$MAIN_REF"; then
	echo "Tag ${found} points at a commit that is not reachable from main (ADR 0020 §4)." >&2
	echo "  commit   : ${HEAD}" >&2
	echo "  main     : $(git rev-parse "$MAIN_REF")" >&2
	echo "The tagged commit must live on the trunk, not on a working branch." >&2
	retag_instructions "$found"
	exit 1
fi

echo "Release ${found} (${HEAD})"
echo "  package.json ${PKG_VERSION} matches the tag"
echo "  commit is reachable from $(git rev-parse --abbrev-ref "$MAIN_REF" 2>/dev/null || echo main)"
