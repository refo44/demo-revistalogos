#!/usr/bin/env bash
# Run the PHPUnit WordPress-integration suite (Level 2) inside an
# isolated Docker environment. The wp-phpunit framework installs its own
# wptests_ tables; the compose project and volumes are destroyed on
# exit. Never points at production or the primary local volumes
# (ADR 0014, docs/24 §6).
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
PROJECT="${LES_WP_PHPUNIT_PROJECT:-revistalogos-wp-phpunit}"
PORT="${LES_WP_PHPUNIT_PORT:-8090}"

cd "$ROOT"

compose() {
	WORDPRESS_PORT="$PORT" docker compose -p "$PROJECT" "$@"
}

cleanup() {
	compose down -v --remove-orphans >/dev/null 2>&1 || true
}
trap cleanup EXIT

install_root_dev_dependencies() {
	if [[ -f "$ROOT/vendor/bin/phpunit" && -d "$ROOT/vendor/wp-phpunit/wp-phpunit" ]]; then
		return
	fi
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

echo "== root dev dependencies (phpunit + wp-phpunit) =="
install_root_dev_dependencies

echo "== isolated Docker environment =="
compose down -v --remove-orphans >/dev/null 2>&1 || true
compose up -d db wordpress

for _attempt in {1..30}; do
	if compose run --rm --no-deps wpcli wp core version >/dev/null 2>&1; then
		break
	fi
	sleep 2
done
compose run --rm --no-deps wpcli wp core version >/dev/null \
	|| { echo "FAIL: WordPress core files did not become ready" >&2; exit 1; }

echo "== PHPUnit WordPress suite =="
compose run --rm --no-deps \
	--volume "$ROOT":/repo \
	--workdir /repo \
	-e WP_PHPUNIT__TESTS_CONFIG=/repo/tests/WordPress/wp-tests-config.php \
	wpcli \
	php vendor/bin/phpunit -c phpunit-wp.xml.dist "$@"
