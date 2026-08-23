#!/usr/bin/env bash
# First-party PHP syntax gate: native `php -l` only (not PHPStan/Psalm/PHPCS).
# Read-only. Never points at production. Host PHP is optional (ADR 0014).
#
# Usage:
#   ./tools/php-lint.sh              # canonical first-party trees
#   ./tools/php-lint.sh path [path…] # extra/isolated paths (negative checks)
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

DEFAULT_TREES=(
	wordpress/wp-content/plugins/revistalogos-core
	wordpress/wp-content/themes/revistalogos
	tests
)

emit_php_files() {
	local path
	for path in "$@"; do
		if [[ -d "$path" ]]; then
			find "$path" -type f -name '*.php' ! -path '*/vendor/*' -print
		elif [[ -f "$path" ]]; then
			printf '%s\n' "$path"
		else
			echo "ERROR: path not found: $path" >&2
			return 1
		fi
	done
}

lint_stream() {
	local php_bin="$1"
	local file
	local failed=0
	local count=0

	while IFS= read -r file; do
		[[ -z "$file" ]] && continue
		count=$((count + 1))
		if ! "$php_bin" -l "$file" </dev/null >/dev/null; then
			echo "FAIL: $file" >&2
			"$php_bin" -l "$file" </dev/null >&2 || true
			failed=1
		fi
	done

	if [[ "$count" -eq 0 ]]; then
		echo "ERROR: no PHP files to lint" >&2
		return 1
	fi

	if [[ "$failed" -ne 0 ]]; then
		echo "php-lint: $count files, syntax errors found" >&2
		return 1
	fi

	echo "php-lint: $count files OK"
	return 0
}

normalize_tree() {
	local path="$1"
	if [[ "$path" = /* ]]; then
		if [[ "$path" == "$ROOT"/* ]]; then
			path="${path#"$ROOT"/}"
		elif [[ "$path" == "$ROOT" ]]; then
			path="."
		else
			echo "ERROR: path outside repository: $path" >&2
			return 1
		fi
	fi
	printf '%s\n' "$path"
}

if [[ "$#" -gt 0 ]]; then
	TREES=()
	for raw in "$@"; do
		TREES+=( "$(normalize_tree "$raw")" )
	done
else
	TREES=( "${DEFAULT_TREES[@]}" )
fi

if command -v php >/dev/null 2>&1; then
	emit_php_files "${TREES[@]}" | lint_stream php
	exit $?
fi

# Find and php -l run inside one PHP 8.3 container. Extra paths must be under $ROOT.
docker run --rm \
	--user "$(id -u):$(id -g)" \
	--volume "$ROOT":/app \
	--workdir /app \
	--entrypoint sh \
	wordpress:cli-php8.3 \
	-c '
set -eu
# php -l reads stdin; keep the file list on another path and close stdin per file.
list=$(mktemp)
trap "rm -f \"$list\"" EXIT
failed=0
count=0
for path in "$@"; do
	if [ -d "$path" ]; then
		find "$path" -type f -name "*.php" ! -path "*/vendor/*" -print >> "$list"
	elif [ -f "$path" ]; then
		printf "%s\n" "$path" >> "$list"
	else
		echo "ERROR: path not found: $path" >&2
		exit 1
	fi
done
while IFS= read -r file; do
	if [ -z "$file" ]; then
		continue
	fi
	count=$((count + 1))
	if ! php -l "$file" </dev/null >/dev/null; then
		echo "FAIL: $file" >&2
		php -l "$file" </dev/null >&2 || true
		failed=1
	fi
done < "$list"
if [ "$count" -eq 0 ]; then
	echo "ERROR: no PHP files to lint" >&2
	exit 1
fi
if [ "$failed" -ne 0 ]; then
	echo "php-lint: $count files, syntax errors found" >&2
	exit 1
fi
echo "php-lint: $count files OK"
' php-lint "${TREES[@]}"
