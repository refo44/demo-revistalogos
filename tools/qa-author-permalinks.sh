#!/usr/bin/env bash
# Local Docker QA: CPT author singles must resolve at /revista/autores/{slug}/.
# Never points at production.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

cli() {
	docker compose run --rm wpcli wp "$@"
}

fail() {
	echo "FAIL: $*" >&2
	exit 1
}

http_code() {
	curl -sS -o /dev/null -w '%{http_code}' "$1"
}

echo "== php -l =="
docker compose run --rm --entrypoint php wpcli -l wp-content/plugins/revistalogos-core/includes/content-types/class-content-types.php
docker compose run --rm --entrypoint php wpcli -l wp-content/plugins/revistalogos-core/includes/class-plugin.php

echo "== query_var must not be native author =="
QUERY_VAR="$(cli eval 'echo get_post_type_object( "author" )->query_var;')"
[[ "$QUERY_VAR" == "journal_author" ]] || fail "expected query_var journal_author, got: $QUERY_VAR"

echo "== published author for permalink test =="
EXISTING="$(cli post list --post_type=author --name=autor-qa-permalinks --format=ids)"
if [[ -n "$EXISTING" ]]; then
	cli post delete $EXISTING --force >/dev/null
fi
AUTHOR_ID="$(cli post create --post_type=author --post_title='Autor QA permalinks' --post_name='autor-qa-permalinks' --post_status=publish --porcelain)"
[[ "$AUTHOR_ID" =~ ^[0-9]+$ ]] || fail "could not create published author"
cleanup() {
	cli post delete "$AUTHOR_ID" --force >/dev/null 2>&1 || true
}
trap cleanup EXIT

PERMALINK="$(cli eval "echo get_permalink( $AUTHOR_ID );")"
echo "permalink: $PERMALINK"
[[ "$PERMALINK" == *"/revista/autores/autor-qa-permalinks/" ]] || fail "unexpected permalink: $PERMALINK"

echo "== flush rewrites =="
cli rewrite flush --hard

echo "== rewrite rule maps to journal_author, not native author =="
QUERY="$(cli rewrite list --match='revista/autores/autor-qa-permalinks' --fields=query --format=csv | tail -n +2 | head -n 1)"
echo "matched query: $QUERY"
case "$QUERY" in
	index.php?journal_author=*) ;;
	*) fail "first matching query must start with index.php?journal_author=, got: $QUERY" ;;
esac

echo "== url_to_postid =="
RESOLVED="$(cli eval "echo url_to_postid( get_permalink( $AUTHOR_ID ) );")"
[[ "$RESOLVED" == "$AUTHOR_ID" ]] || fail "url_to_postid expected $AUTHOR_ID got $RESOLVED"

echo "== HTTP =="
ARCHIVE_CODE="$(http_code 'http://localhost:8080/revista/autores/')"
SINGLE_CODE="$(http_code 'http://localhost:8080/revista/autores/autor-qa-permalinks/')"
ISSUE_CODE="$(http_code 'http://localhost:8080/revista/numeros/')"
ARTICLE_CODE="$(http_code 'http://localhost:8080/revista/articulos/')"
[[ "$ARCHIVE_CODE" == "200" ]] || fail "author archive HTTP $ARCHIVE_CODE"
[[ "$SINGLE_CODE" == "200" ]] || fail "author single HTTP $SINGLE_CODE"
[[ "$ISSUE_CODE" == "200" ]] || fail "issue archive HTTP $ISSUE_CODE (regression)"
[[ "$ARTICLE_CODE" == "200" ]] || fail "article archive HTTP $ARTICLE_CODE (regression)"

echo "== no Page shadows revista/autores =="
PAGE_HIT="$(cli post list --post_type=page --name=autores --format=ids)"
[[ -z "$PAGE_HIT" ]] || fail "unexpected Page with slug autores: $PAGE_HIT"
REVISTA_PAGE="$(cli post list --post_type=page --name=revista --format=ids)"
[[ -z "$REVISTA_PAGE" ]] || fail "unexpected Page with slug revista: $REVISTA_PAGE"

echo "PASS: author permalink QA"
