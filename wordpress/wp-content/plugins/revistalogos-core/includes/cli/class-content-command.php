<?php
/**
 * WP-CLI: wp revistalogos content <validate|plan|import|verify>.
 * Dry-run by default; writes require --apply; production writes require
 * an additional explicit confirmation and backup evidence.
 *
 * @package Revistalogos_Core
 */

namespace Revistalogos_Core\CLI;

use Revistalogos_Core\Content_Migrator;
use WP_CLI;

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit;
}

/**
 * Institutional content migration commands (operator-run only).
 */
class Content_Command {

	/**
	 * Validate the generated payload (structure, hashes, token syntax).
	 *
	 * ## EXAMPLES
	 *
	 *     wp revistalogos content validate
	 *
	 * @param array $args       Positional args.
	 * @param array $assoc_args Named args.
	 */
	public function validate( $args, $assoc_args ) {
		$migrator = new Content_Migrator();
		$loaded   = $migrator->load();

		if ( is_wp_error( $loaded ) ) {
			WP_CLI::error( $loaded->get_error_message() );
		}

		$payload = $migrator->payload();

		WP_CLI::log( sprintf( 'Payload version %s, generator %s, generated %s.', $payload['payload_version'], $payload['generator_version'], $payload['generated_at'] ) );
		WP_CLI::log( sprintf( '%d entries, %d media seeds.', count( $payload['entries'] ), count( $payload['media'] ?? array() ) ) );

		$problems = 0;

		foreach ( (array) ( $payload['media'] ?? array() ) as $media ) {
			$file = REVISTALOGOS_CORE_DIR . 'resources/' . $media['file'];

			if ( ! file_exists( $file ) ) {
				WP_CLI::warning( sprintf( 'media seed missing: %s', $media['file'] ) );
				$problems++;
			} elseif ( hash_file( 'sha256', $file ) !== $media['sha256'] ) {
				WP_CLI::warning( sprintf( 'media seed checksum mismatch: %s', $media['file'] ) );
				$problems++;
			}
		}

		foreach ( (array) ( $payload['warnings'] ?? array() ) as $warning ) {
			WP_CLI::log( 'generator warning: ' . $warning );
		}

		foreach ( (array) ( $payload['coverage_report'] ?? array() ) as $coverage ) {
			WP_CLI::log(
				sprintf(
					'canonical coverage %s: %d/%d paragraphs verbatim (%s)',
					$coverage['source_key'],
					$coverage['found_verbatim'],
					$coverage['canonical_paragraphs'],
					$coverage['note']
				)
			);
		}

		if ( $problems > 0 ) {
			WP_CLI::error( sprintf( '%d problem(s) found.', $problems ) );
		}

		WP_CLI::success( 'Payload is valid.' );
	}

	/**
	 * Show what an import would do, without writing.
	 *
	 * ## OPTIONS
	 *
	 * [--force]
	 * : Plan as if --force were passed to import.
	 *
	 * @param array $args       Positional args.
	 * @param array $assoc_args Named args.
	 */
	public function plan( $args, $assoc_args ) {
		$migrator = $this->load_or_die();
		$force    = isset( $assoc_args['force'] );

		foreach ( $migrator->import_media( false ) as $row ) {
			WP_CLI::log( sprintf( 'media %-45s %s', $row['key'], $row['action'] ) );
		}

		foreach ( $migrator->payload()['entries'] as $entry ) {
			$plan = $migrator->plan_entry( $entry, $force );
			WP_CLI::log( sprintf( 'entry %-25s %-14s %s', $entry['source_key'], $plan['action'], $plan['reason'] ) );
		}

		foreach ( $migrator->apply_site_settings( false, $force ) as $line ) {
			WP_CLI::log( 'site  ' . $line );
		}

		WP_CLI::success( 'Plan complete (dry-run; nothing written).' );
	}

	/**
	 * Import the payload. Dry-run unless --apply is passed.
	 *
	 * ## OPTIONS
	 *
	 * [--apply]
	 * : Actually write. Without it this behaves like plan.
	 *
	 * [--force]
	 * : Reassert migration-owned fields over manual WordPress edits and
	 * rebuild managed menus. Never touches non-owned fields.
	 *
	 * [--confirm-production]
	 * : Required when the environment type is production.
	 *
	 * [--backup=<evidence>]
	 * : Required with --confirm-production: where the pre-import backup
	 * lives (path, ticket or snapshot id). Recorded in the output.
	 *
	 * @param array $args       Positional args.
	 * @param array $assoc_args Named args.
	 */
	public function import( $args, $assoc_args ) {
		$migrator = $this->load_or_die();
		$apply    = isset( $assoc_args['apply'] );
		$force    = isset( $assoc_args['force'] );

		if ( $apply && 'production' === wp_get_environment_type() ) {
			if ( ! isset( $assoc_args['confirm-production'] ) || empty( $assoc_args['backup'] ) ) {
				WP_CLI::error( 'Production import requires --confirm-production and --backup=<evidence> (pre-import database backup).' );
			}

			WP_CLI::log( 'Production import confirmed. Backup evidence: ' . $assoc_args['backup'] );
		}

		if ( ! $apply ) {
			WP_CLI::log( 'Dry-run (pass --apply to write).' );
		}

		foreach ( $migrator->import_media( $apply ) as $row ) {
			WP_CLI::log( sprintf( 'media %-45s %s (id %d)', $row['key'], $row['action'], $row['id'] ) );
		}

		foreach ( $migrator->import_entries( $apply, $force ) as $row ) {
			$extra = ! empty( $row['unresolved'] ) ? ' UNRESOLVED: ' . implode( ', ', $row['unresolved'] ) : '';
			WP_CLI::log( sprintf( 'entry %-25s %-14s %s%s', $row['key'], $row['action'], $row['reason'] ?? '', $extra ) );
		}

		foreach ( $migrator->apply_site_settings( $apply, $force ) as $line ) {
			WP_CLI::log( 'site  ' . $line );
		}

		WP_CLI::success( $apply ? 'Import applied.' : 'Dry-run complete; nothing written.' );
	}

	/**
	 * Verify imported objects against the payload (missing, stale,
	 * drifted, fixture contamination).
	 *
	 * @param array $args       Positional args.
	 * @param array $assoc_args Named args.
	 */
	public function verify( $args, $assoc_args ) {
		$migrator = $this->load_or_die();
		$failures = 0;

		foreach ( $migrator->verify() as $row ) {
			WP_CLI::log( sprintf( '%-25s %s', $row['key'], $row['status'] ) );

			if ( 'OK' !== $row['status'] ) {
				$failures++;
			}
		}

		if ( $failures > 0 ) {
			WP_CLI::error( sprintf( '%d object(s) not in the expected state.', $failures ) );
		}

		WP_CLI::success( 'All migrated objects verified.' );
	}

	/**
	 * Load the payload or abort.
	 *
	 * @return Content_Migrator
	 */
	private function load_or_die() {
		$migrator = new Content_Migrator();
		$loaded   = $migrator->load();

		if ( is_wp_error( $loaded ) ) {
			WP_CLI::error( $loaded->get_error_message() );
		}

		return $migrator;
	}
}
