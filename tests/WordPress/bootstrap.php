<?php
/**
 * PHPUnit WordPress-integration bootstrap (Level 2). Boots the real
 * WordPress test framework (wp-phpunit) against the isolated Docker
 * environment started by tools/run-phpunit-wp.sh. Never points at
 * production or the primary local volumes.
 *
 * @package Revistalogos
 */

$revistalogos_root = dirname( __DIR__, 2 );

require_once $revistalogos_root . '/vendor/autoload.php';

$wp_phpunit_dir = getenv( 'WP_PHPUNIT__DIR' );
if ( ! $wp_phpunit_dir ) {
	$wp_phpunit_dir = $revistalogos_root . '/vendor/wp-phpunit/wp-phpunit';
}

if ( ! getenv( 'WP_PHPUNIT__TESTS_CONFIG' ) ) {
	putenv( 'WP_PHPUNIT__TESTS_CONFIG=' . __DIR__ . '/wp-tests-config.php' );
}

require_once $wp_phpunit_dir . '/includes/functions.php';

tests_add_filter(
	'muplugins_loaded',
	static function () use ( $revistalogos_root ) {
		require $revistalogos_root . '/wordpress/wp-content/plugins/revistalogos-core/revistalogos-core.php';
	}
);

require $wp_phpunit_dir . '/includes/bootstrap.php';
