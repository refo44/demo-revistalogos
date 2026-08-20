<?php
/**
 * WP-CLI: wp revistalogos fixtures <seed|bootstrap|plan|verify|teardown>.
 * Dry-run by default; --apply writes. Full demo seed is refused on
 * production without --allow-production. Volume 1 editorial bootstrap
 * and teardown on production require --confirm-production and --backup.
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
	 * Seed the full local demo dataset (idempotent; existing fixtures skipped).
	 * Not the production editorial bootstrap. Production refuses without
	 * --allow-production; do not use that override for the live journal.
	 *
	 * ## OPTIONS
	 *
	 * [--apply]
	 * : Actually write. Without it, reports what would be created.
	 *
	 * [--allow-production]
	 * : Emergency override of the production guard for the full demo
	 * dataset. Do not use on the live journal.
	 *
	 * @param array $args       Positional args.
	 * @param array $assoc_args Named args.
	 */
	public function seed( $args, $assoc_args ) {
		$guard = Fixtures::environment_guard( isset( $assoc_args['allow-production'] ) );

		if ( is_wp_error( $guard ) ) {
			WP_CLI::error( $guard->get_error_message() );
		}

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
	 * Volume 1 editorial bootstrap: one published issue and the sample
	 * article structure as normal editable objects. Reuses the existing
	 * Author CPT by slug (default rafael-eduardo-figueredo-oropeza).
	 * Never creates, marks or deletes that author. Never overwrites
	 * adopted or colliding content. No fake DOI/ORCID/ISSN.
	 *
	 * ## OPTIONS
	 *
	 * [--apply]
	 * : Actually write. Without it, reports what would be created.
	 *
	 * [--confirm-production]
	 * : Required when the environment type is production.
	 *
	 * [--backup=<evidence>]
	 * : Required with --confirm-production: where the pre-write backup
	 * lives (path, ticket or snapshot id). Recorded in the output.
	 *
	 * [--author-slug=<slug>]
	 * : Canonical Author CPT slug to reuse. Default:
	 * rafael-eduardo-figueredo-oropeza. Bootstrap never creates this
	 * author. 0 or >1 matches fail safe.
	 *
	 * @param array $args       Positional args.
	 * @param array $assoc_args Named args.
	 */
	public function bootstrap( $args, $assoc_args ) {
		self::run_bootstrap( $assoc_args, isset( $assoc_args['apply'] ) );
	}

	/**
	 * Read-only Volume 1 plan. Same as `fixtures bootstrap` without
	 * --apply. Never writes.
	 *
	 * ## OPTIONS
	 *
	 * [--author-slug=<slug>]
	 * : Canonical Author CPT slug to reuse.
	 *
	 * @param array $args       Positional args.
	 * @param array $assoc_args Named args.
	 */
	public function plan( $args, $assoc_args ) {
		unset( $assoc_args['apply'] );
		self::run_bootstrap( $assoc_args, false );
	}

	/**
	 * Shared bootstrap/plan runner.
	 *
	 * @param array $assoc_args Named args.
	 * @param bool  $apply      Write when true.
	 */
	private static function run_bootstrap( $assoc_args, $apply ) {
		if ( $apply ) {
			$guard = Fixtures::production_write_guard( array_merge( $assoc_args, array( 'apply' => true ) ) );

			if ( is_wp_error( $guard ) ) {
				WP_CLI::error( $guard->get_error_message() );
			}
		}

		if ( $apply && isset( $assoc_args['confirm-production'] ) ) {
			WP_CLI::log( 'Production bootstrap confirmed. Backup evidence: ' . $assoc_args['backup'] );
		}

		if ( ! $apply ) {
			WP_CLI::log( 'Dry-run (pass --apply to write).' );
		}

		$slug = isset( $assoc_args['author-slug'] )
			? sanitize_title( $assoc_args['author-slug'] )
			: Fixtures::CANONICAL_AUTHOR_SLUG;

		$result = Fixtures::bootstrap( $apply, $slug );

		if ( is_wp_error( $result ) ) {
			WP_CLI::error( $result->get_error_message() );
		}

		foreach ( $result as $line ) {
			WP_CLI::log( $line );
		}

		WP_CLI::success( $apply ? 'Volume 1 editorial bootstrap applied.' : 'Dry-run complete.' );
	}

	/**
	 * Verify fixture and Volume 1 bootstrap state (idempotency,
	 * identifier rules, adoption, isolation from canonical content).
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
			WP_CLI::error( sprintf( '%d fixture/bootstrap problem(s).', $result['failures'] ) );
		}

		WP_CLI::success( 'Fixture and bootstrap state verified.' );
	}

	/**
	 * Remove disposable fixture objects. `--kind=bootstrap` also removes
	 * unadopted Volume 1 bootstrap objects. Never deletes adopted
	 * Volume 1 content, the canonical author, or institutional Pages.
	 * Safe no-op when nothing remains.
	 *
	 * ## OPTIONS
	 *
	 * [--apply]
	 * : Actually delete. Without it, reports what would be removed.
	 *
	 * [--kind=<kind>]
	 * : Limit to demo or bootstrap. Empty (default) removes every
	 * _les_fixture=1 object and does not touch Volume 1 bootstrap.
	 *
	 * [--confirm-production]
	 * : Required when the environment type is production.
	 *
	 * [--backup=<evidence>]
	 * : Required with --confirm-production.
	 *
	 * [--allow-production]
	 * : Accepted only so older local invocations keep working when the
	 * environment is not production. Ignored as a production bypass;
	 * production still requires --confirm-production and --backup.
	 *
	 * @param array $args       Positional args.
	 * @param array $assoc_args Named args.
	 */
	public function teardown( $args, $assoc_args ) {
		$guard = Fixtures::production_write_guard( $assoc_args );

		if ( is_wp_error( $guard ) ) {
			WP_CLI::error( $guard->get_error_message() );
		}

		$apply = isset( $assoc_args['apply'] );
		$kind  = isset( $assoc_args['kind'] ) ? (string) $assoc_args['kind'] : '';

		if ( '' !== $kind && ! in_array( $kind, array( Fixtures::KIND_DEMO, Fixtures::KIND_BOOTSTRAP ), true ) ) {
			WP_CLI::error( 'Invalid --kind. Use demo, bootstrap, or omit for all _les_fixture objects.' );
		}

		if ( ! $apply ) {
			WP_CLI::log( 'Dry-run (pass --apply to delete).' );
		}

		foreach ( Fixtures::teardown( $apply, $kind ) as $line ) {
			WP_CLI::log( $line );
		}

		WP_CLI::success( $apply ? 'Teardown complete.' : 'Dry-run complete.' );
	}
}
