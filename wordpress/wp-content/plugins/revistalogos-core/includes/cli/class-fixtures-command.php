<?php
/**
 * WP-CLI: wp revistalogos fixtures <seed|verify|teardown>.
 * Dry-run by default; --apply writes; production refuses without
 * --allow-production (ADR 0004: fixtures never reach production).
 *
 * @package Revistalogos_Core
 */

namespace Revistalogos_Core\CLI;

use Revistalogos_Core\Fixtures;
use WP_CLI;

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit;
}

/**
 * Fixture lifecycle commands.
 */
class Fixtures_Command {

	/**
	 * Seed the demo dataset (idempotent; existing fixtures skipped).
	 *
	 * ## OPTIONS
	 *
	 * [--apply]
	 * : Actually write. Without it, reports what would be created.
	 *
	 * [--allow-production]
	 * : Explicit override of the production guard.
	 *
	 * @param array $args       Positional args.
	 * @param array $assoc_args Named args.
	 */
	public function seed( $args, $assoc_args ) {
		$this->guard( $assoc_args );

		$apply = isset( $assoc_args['apply'] );

		if ( ! $apply ) {
			WP_CLI::log( 'Dry-run (pass --apply to write).' );
		}

		foreach ( Fixtures::seed( $apply ) as $line ) {
			WP_CLI::log( $line );
		}

		WP_CLI::success( $apply ? 'Fixtures seeded.' : 'Dry-run complete.' );
	}

	/**
	 * Verify fixture state (idempotency, fake identifiers, isolation
	 * from canonical content).
	 *
	 * @param array $args       Positional args.
	 * @param array $assoc_args Named args.
	 */
	public function verify( $args, $assoc_args ) {
		$result = Fixtures::verify();

		foreach ( $result['report'] as $line ) {
			WP_CLI::log( $line );
		}

		if ( $result['failures'] > 0 ) {
			WP_CLI::error( sprintf( '%d fixture problem(s).', $result['failures'] ) );
		}

		WP_CLI::success( 'Fixture state verified.' );
	}

	/**
	 * Remove every fixture object (posts, media with files, meta, term
	 * relationships and unused fixture-created terms). Safe no-op when
	 * nothing remains.
	 *
	 * ## OPTIONS
	 *
	 * [--apply]
	 * : Actually delete. Without it, reports what would be removed.
	 *
	 * [--allow-production]
	 * : Explicit override of the production guard.
	 *
	 * @param array $args       Positional args.
	 * @param array $assoc_args Named args.
	 */
	public function teardown( $args, $assoc_args ) {
		$this->guard( $assoc_args );

		$apply = isset( $assoc_args['apply'] );

		if ( ! $apply ) {
			WP_CLI::log( 'Dry-run (pass --apply to delete).' );
		}

		foreach ( Fixtures::teardown( $apply ) as $line ) {
			WP_CLI::log( $line );
		}

		WP_CLI::success( $apply ? 'Fixtures removed.' : 'Dry-run complete.' );
	}

	/**
	 * Environment guard shared by seed/teardown.
	 *
	 * @param array $assoc_args Named args.
	 */
	private function guard( $assoc_args ) {
		$guard = Fixtures::environment_guard( isset( $assoc_args['allow-production'] ) );

		if ( is_wp_error( $guard ) ) {
			WP_CLI::error( $guard->get_error_message() );
		}
	}
}
