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
		composer install --no-interaction
}

run_phpunit() {
	if command -v php >/dev/null 2>&1; then
		php vendor/bin/phpunit --testsuite unit "$@"
		return
	fi

	# wordpress:cli-php8.3 matches local PHP 8.3 and includes mbstring.
	# Do not use compose wpcli here: that service does not mount the repo root.
	docker run --rm \
		--user "$(id -u):$(id -g)" \
		--volume "$ROOT":/app \
		--workdir /app \
		wordpress:cli-php8.3 \
		php vendor/bin/phpunit --testsuite unit "$@"
}

PLUGIN_COMPOSER_DIR="$ROOT/wordpress/wp-content/plugins/revistalogos-core"

install_plugin_runtime_dependencies() {
	if [[ ! -f "$PLUGIN_COMPOSER_DIR/composer.json" ]]; then
		return
	fi
	if [[ -f "$PLUGIN_COMPOSER_DIR/vendor/autoload.php" ]]; then
		return
	fi

	if command -v composer >/dev/null 2>&1; then
		composer --working-dir="$PLUGIN_COMPOSER_DIR" install --no-interaction --prefer-dist --no-progress
		return
	fi

	docker run --rm \
		--user "$(id -u):$(id -g)" \
		-e COMPOSER_HOME=/tmp/composer \
		--volume "$ROOT":/app \
		--workdir /app \
		composer:2 \
		composer --working-dir=wordpress/wp-content/plugins/revistalogos-core install --no-interaction --prefer-dist --no-progress
}

if [[ ! -x vendor/bin/phpunit ]]; then
	install_dev_dependencies
fi

install_plugin_runtime_dependencies

run_phpunit "$@"
