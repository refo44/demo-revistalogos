<?php
/**
 * WordPress test-suite configuration for the isolated Docker
 * environment (tools/run-phpunit-wp.sh). The suite installs its own
 * tables under the wptests_ prefix; the whole compose project is
 * discarded with `down -v` after the run.
 *
 * @package Revistalogos
 */

define( 'ABSPATH', '/var/www/html/' );

define( 'DB_NAME', getenv( 'WORDPRESS_DB_NAME' ) ?: 'wordpress' );
define( 'DB_USER', getenv( 'WORDPRESS_DB_USER' ) ?: 'wordpress' );
define( 'DB_PASSWORD', getenv( 'WORDPRESS_DB_PASSWORD' ) ?: '' );
define( 'DB_HOST', getenv( 'WORDPRESS_DB_HOST' ) ?: 'db' );
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );

$table_prefix = 'wptests_';

define( 'WP_TESTS_DOMAIN', 'example.org' );
define( 'WP_TESTS_EMAIL', 'admin@example.org' );
define( 'WP_TESTS_TITLE', 'Revista de Filosofía LOGO ET SPES' );

define( 'WP_PHP_BINARY', 'php' );
define( 'WP_DEBUG', true );

define( 'WP_ENVIRONMENT_TYPE', 'local' );
