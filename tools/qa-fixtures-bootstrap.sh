#!/usr/bin/env bash
# Local Docker QA for the restricted editorial bootstrap.
# Never points at production. Does not run the full demo seed teardown.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

cli() {
	docker compose run --rm wpcli wp "$@"
}

cli_production() {
	docker compose run --rm \
		-e WORDPRESS_CONFIG_EXTRA="define( 'WP_ENVIRONMENT_TYPE', 'production' );" \
		wpcli wp "$@"
}

fail() {
	echo "FAIL: $*" >&2
	exit 1
}

echo "== php -l =="
docker compose run --rm --entrypoint php wpcli -l wp-content/plugins/revistalogos-core/includes/fixtures/class-fixtures.php
docker compose run --rm --entrypoint php wpcli -l wp-content/plugins/revistalogos-core/includes/cli/class-fixtures-command.php

echo "== help lists bootstrap =="
cli help revistalogos fixtures | grep -q bootstrap || fail "bootstrap subcommand missing"

echo "== production: full demo seed --apply is blocked =="
if cli_production revistalogos fixtures seed --apply >/tmp/les-seed-prod.txt 2>&1; then
	fail "demo seed --apply must be refused on production"
fi
grep -q "allow-production" /tmp/les-seed-prod.txt || fail "seed production error should mention --allow-production"

echo "== production: bootstrap --apply without confirm is blocked =="
if cli_production revistalogos fixtures bootstrap --apply >/tmp/les-boot-prod.txt 2>&1; then
	fail "bootstrap --apply must be refused on production without --confirm-production"
fi
grep -q "confirm-production" /tmp/les-boot-prod.txt || fail "bootstrap production error should mention --confirm-production"

echo "== production: demo seed must stay blocked even if someone uses bootstrap flags =="
if cli_production revistalogos fixtures seed --apply --confirm-production --backup=x >/tmp/les-seed-wrong-flag.txt 2>&1; then
	fail "demo seed must not accept --confirm-production as a bypass"
fi

echo "== local dry-run =="
cli revistalogos fixtures bootstrap

echo "== non-fixture editorial record =="
REAL_ID="$(cli post create --post_type=issue --post_title='Editorial real de prueba (no fixture)' --post_status=draft --porcelain)"
[[ "$REAL_ID" =~ ^[0-9]+$ ]] || fail "could not create non-fixture issue"

echo "== bootstrap --apply (local) =="
cli revistalogos fixtures bootstrap --apply

echo "== bootstrap object types and counts =="
AUTHOR_ID="$(cli post list --post_type=author --meta_key=_les_fixture_key --meta_value=bootstrap-author-1 --format=ids)"
ISSUE_ID="$(cli post list --post_type=issue --meta_key=_les_fixture_key --meta_value=bootstrap-issue-1 --format=ids)"
ARTICLE_ID="$(cli post list --post_type=article --meta_key=_les_fixture_key --meta_value=bootstrap-article-1 --format=ids)"
[[ "$AUTHOR_ID" =~ ^[0-9]+$ ]] || fail "missing bootstrap author"
[[ "$ISSUE_ID" =~ ^[0-9]+$ ]] || fail "missing bootstrap issue"
[[ "$ARTICLE_ID" =~ ^[0-9]+$ ]] || fail "missing bootstrap article"

for id in "$AUTHOR_ID" "$ISSUE_ID" "$ARTICLE_ID"; do
	marker="$(cli post meta get "$id" _les_fixture)"
	kind="$(cli post meta get "$id" _les_fixture_kind)"
	[[ "$marker" == "1" ]] || fail "id $id missing _les_fixture=1"
	[[ "$kind" == "bootstrap" ]] || fail "id $id kind is not bootstrap"
done

issn="$(cli post meta get "$ISSUE_ID" issn || true)"
doi_issue="$(cli post meta get "$ISSUE_ID" doi || true)"
doi_article="$(cli post meta get "$ARTICLE_ID" doi || true)"
orcid="$(cli post meta get "$AUTHOR_ID" orcid || true)"
[[ -z "$issn" ]] || fail "bootstrap issue has ISSN"
[[ -z "$doi_issue" ]] || fail "bootstrap issue has DOI"
[[ -z "$doi_article" ]] || fail "bootstrap article has DOI"
[[ -z "$orcid" ]] || fail "bootstrap author has ORCID"

echo "== existing non-fixture was not overwritten =="
real_title="$(cli post get "$REAL_ID" --field=post_title)"
[[ "$real_title" == "Editorial real de prueba (no fixture)" ]] || fail "non-fixture title changed"
real_marker="$(cli post meta get "$REAL_ID" _les_fixture || true)"
[[ -z "$real_marker" ]] || fail "non-fixture was tagged as fixture"

echo "== idempotent re-run =="
cli revistalogos fixtures bootstrap --apply
AUTHOR_ID2="$(cli post list --post_type=author --meta_key=_les_fixture_key --meta_value=bootstrap-author-1 --format=ids)"
[[ "$AUTHOR_ID2" == "$AUTHOR_ID" ]] || fail "re-run created a duplicate author"

echo "== teardown dry-run does not target the non-fixture =="
cli revistalogos fixtures teardown --kind=bootstrap > /tmp/les-teardown-plan.txt
grep -q "would delete" /tmp/les-teardown-plan.txt || fail "teardown dry-run should list bootstrap objects"
if grep -q " $REAL_ID" /tmp/les-teardown-plan.txt; then
	fail "teardown dry-run listed the non-fixture id"
fi

echo "== teardown --kind=bootstrap --apply =="
cli revistalogos fixtures teardown --kind=bootstrap --apply
gone="$(cli post list --post_type=author --meta_key=_les_fixture_key --meta_value=bootstrap-author-1 --format=ids || true)"
[[ -z "$gone" ]] || fail "bootstrap author still present after teardown"
still="$(cli post get "$REAL_ID" --field=ID)"
[[ "$still" == "$REAL_ID" ]] || fail "teardown deleted the non-fixture issue"

cli post delete "$REAL_ID" --force >/dev/null

echo "PASS: restricted bootstrap QA"
