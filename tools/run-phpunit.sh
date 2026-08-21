#!/usr/bin/env bash
# Run PHPUnit unit tests (Level 1). Uses local PHP+Composer when present;
# otherwise Docker. Never points at production or the primary WordPress DB.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

install_dev_dependencies() {
	if command -v composer >/dev/null 2>&1; then
		composer install --no-interaction
		return
	fi

	docker run --rm \
		--user "$(id -u):$(id -g)" \
		-e COMPOSER_HOME=/tmp/composer \
		--volume "$ROOT":/app \
		--workdir /app \
		composer:2 \
		composer install --no-interaction --ignore-platform-reqs
}

run_phpunit() {
	if command -v php >/dev/null 2>&1; then
		php vendor/bin/phpunit --testsuite unit "$@"
		return
	fi

	# wordpress:cli-php8.2 matches local PHP 8.2 and includes mbstring.
	# Do not use compose wpcli here: that service does not mount the repo root.
	docker run --rm \
		--user "$(id -u):$(id -g)" \
		--volume "$ROOT":/app \
		--workdir /app \
		wordpress:cli-php8.2 \
		php vendor/bin/phpunit --testsuite unit "$@"
}

if [[ ! -x vendor/bin/phpunit ]]; then
	install_dev_dependencies
fi

run_phpunit "$@"
