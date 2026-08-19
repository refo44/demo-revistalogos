<?php
/**
 * WP-CLI: wp revistalogos fixtures <seed|bootstrap|verify|teardown>.
 * Dry-run by default; --apply writes. Full demo seed is refused on
 * production without --allow-production. Editorial bootstrap and
 * teardown on production require --confirm-production and --backup.
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
	 * Restricted editorial bootstrap: one draft issue, one draft article,
	 * one draft author. No fake DOI/ORCID/ISSN. Does not overwrite
	 * existing editorial content. Production writes require
	 * --confirm-production and --backup.
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
	 * [--author-name=<name>]
	 * : Temporary author display name. Neutral placeholder if omitted.
	 * Do not pass email, ORCID or credentials.
	 *
	 * [--author-affiliation=<affiliation>]
	 * : Optional affiliation. Empty if omitted. Never invented.
	 *
	 * [--author-bio=<bio>]
	 * : Optional biography. Empty if omitted. Never invented.
	 *
	 * @param array $args       Positional args.
	 * @param array $assoc_args Named args.
	 */
	public function bootstrap( $args, $assoc_args ) {
		$guard = Fixtures::production_write_guard( $assoc_args );

		if ( is_wp_error( $guard ) ) {
			WP_CLI::error( $guard->get_error_message() );
		}

		if ( isset( $assoc_args['apply'] ) && isset( $assoc_args['confirm-production'] ) ) {
			WP_CLI::log( 'Production bootstrap confirmed. Backup evidence: ' . $assoc_args['backup'] );
		}

		$apply = isset( $assoc_args['apply'] );

		if ( ! $apply ) {
			WP_CLI::log( 'Dry-run (pass --apply to write).' );
		}

		$author = array(
			'name'        => isset( $assoc_args['author-name'] ) ? sanitize_text_field( $assoc_args['author-name'] ) : '',
			'affiliation' => isset( $assoc_args['author-affiliation'] ) ? sanitize_text_field( $assoc_args['author-affiliation'] ) : '',
			'bio'         => isset( $assoc_args['author-bio'] ) ? sanitize_textarea_field( $assoc_args['author-bio'] ) : '',
		);

		foreach ( Fixtures::bootstrap( $apply, $author ) as $line ) {
			WP_CLI::log( $line );
		}

		WP_CLI::success( $apply ? 'Editorial bootstrap applied.' : 'Dry-run complete.' );
	}

	/**
	 * Verify fixture state (idempotency, identifier rules, isolation
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
	 * Remove fixture objects (posts, media with files, meta, term
	 * relationships and unused fixture-created terms). Never deletes
	 * records that lack _les_fixture=1. Safe no-op when nothing remains.
	 *
	 * ## OPTIONS
	 *
	 * [--apply]
	 * : Actually delete. Without it, reports what would be removed.
	 *
	 * [--kind=<kind>]
	 * : Limit to demo or bootstrap. Empty (default) removes every
	 * _les_fixture=1 object.
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
			WP_CLI::error( 'Invalid --kind. Use demo, bootstrap, or omit for all fixtures.' );
		}

		if ( ! $apply ) {
			WP_CLI::log( 'Dry-run (pass --apply to delete).' );
		}

		foreach ( Fixtures::teardown( $apply, $kind ) as $line ) {
			WP_CLI::log( $line );
		}

		WP_CLI::success( $apply ? 'Fixtures removed.' : 'Dry-run complete.' );
	}
}
