<?php
/**
 * Temporary administrator-only recovery screen for the institutional
 * content migration. Remove this class and its bootstrap wiring after the
 * production recovery has been verified.
 *
 * @package Revistalogos_Core
 */

namespace Revistalogos_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Thin wp-admin controller and view for the existing Content_Migrator.
 */
class Content_Recovery_Admin {

	const PAGE_SLUG = 'revistalogos-institutional-content';
	const NONCE_ACTION = 'revistalogos_content_recovery';
	const NONCE_FIELD = 'revistalogos_content_recovery_nonce';
	const ACTION_FIELD = 'revistalogos_content_recovery_action';
	const PLAN_FIELD = 'revistalogos_content_recovery_plan';
	const BACKUP_FIELD = 'revistalogos_content_recovery_backup';
	const CONFIRM_FIELD = 'revistalogos_content_recovery_confirm';

	/**
	 * Result prepared before the admin header is sent.
	 *
	 * @var array|null
	 */
	private static $result = null;

	/**
	 * Register the temporary admin menu hook.
	 */
	public static function register_hooks() {
		add_action( 'admin_menu', array( __CLASS__, 'register_page' ) );
	}

	/**
	 * Add the recovery screen under Tools for administrators only.
	 */
	public static function register_page() {
		$hook_suffix = add_management_page(
			__( 'Institutional Content Import', 'revistalogos-core' ),
			__( 'Institutional Content Import', 'revistalogos-core' ),
			'manage_options',
			self::PAGE_SLUG,
			array( __CLASS__, 'render_page' )
		);

		add_action( 'load-' . $hook_suffix, array( __CLASS__, 'handle_page_load' ) );
	}

	/**
	 * Handle POST before WordPress sends the admin header.
	 */
	public static function handle_page_load() {
		self::require_administrator();

		$request_method = isset( $_SERVER['REQUEST_METHOD'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) : '';

		if ( 'POST' === strtoupper( $request_method ) ) {
			self::$result = self::handle_post();
		}
	}

	/**
	 * Render the recovery screen and any prepared action result.
	 */
	public static function render_page() {
		self::require_administrator();

		$result = self::$result;

		echo '<div class="wrap">';
		echo '<h1>' . esc_html__( 'Institutional Content Import', 'revistalogos-core' ) . '</h1>';
		echo '<div class="notice notice-warning inline"><p><strong>' . esc_html__( 'Temporary recovery tool.', 'revistalogos-core' ) . '</strong> ';
		echo esc_html__( 'It exists only to restore institutional WordPress Pages and must be removed after production recovery.', 'revistalogos-core' ) . '</p></div>';
		echo '<p>' . esc_html__( 'This screen calls the existing Content_Migrator directly. It does not run fixtures, touch users, or expose force mode.', 'revistalogos-core' ) . '</p>';

		if ( $result ) {
			self::render_result( $result );
		}

		self::render_validate_form();

		if ( $result && in_array( $result['type'], array( 'plan', 'import_attempt' ), true ) && ! empty( $result['can_import'] ) ) {
			self::render_import_form( $result['plan_fingerprint'] );
		}

		if ( $result ) {
			self::render_verify_form();
		}

		echo '</div>';
	}

	/**
	 * Dispatch a sanitized, nonced POST action.
	 *
	 * @return array<string, mixed>
	 */
	private static function handle_post() {
		self::require_administrator();

		$nonce = isset( $_POST[ self::NONCE_FIELD ] )
			? sanitize_text_field( wp_unslash( $_POST[ self::NONCE_FIELD ] ) )
			: '';

		if ( ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
			wp_die(
				esc_html__( 'Security check failed. Reload the recovery screen and try again.', 'revistalogos-core' ),
				esc_html__( 'Forbidden', 'revistalogos-core' ),
				array( 'response' => 403 )
			);
		}

		$action = isset( $_POST[ self::ACTION_FIELD ] )
			? sanitize_key( wp_unslash( $_POST[ self::ACTION_FIELD ] ) )
			: '';
		$migrator = new Content_Migrator();

		if ( 'validate_plan' === $action ) {
			return self::build_plan_result( $migrator, 'plan' );
		}

		if ( 'import' === $action ) {
			return self::handle_import( $migrator );
		}

		if ( 'verify' === $action ) {
			return self::build_verify_result( $migrator );
		}

		return array(
			'type'   => 'error',
			'errors' => array( __( 'Unknown recovery action.', 'revistalogos-core' ) ),
		);
	}

	/**
	 * Build validation, collision and dry-run evidence without writing.
	 *
	 * @param Content_Migrator $migrator Migration service.
	 * @param string           $type     Result type.
	 * @return array<string, mixed>
	 */
	private static function build_plan_result( $migrator, $type ) {
		$validation = $migrator->validation_report();
		$result     = array(
			'type'             => $type,
			'validation'       => $validation,
			'plan'             => null,
			'can_import'       => false,
			'plan_fingerprint' => '',
			'errors'           => array(),
		);

		if ( ! $validation['valid'] ) {
			$result['errors'][] = __( 'Payload validation failed. Import is blocked.', 'revistalogos-core' );
			return $result;
		}

		$plan            = $migrator->plan( false );
		$has_collisions  = $migrator->has_blocking_slug_collisions( $plan['slugs'] );
		$has_fatal_error = ! empty( $plan['fatal_errors'] );

		$result['plan']       = $plan;
		$result['can_import'] = ! $has_collisions && ! $has_fatal_error;

		if ( $has_collisions ) {
			$result['errors'][] = __( 'Import is blocked by a MANUAL EXISTING or AMBIGUOUS protected slug.', 'revistalogos-core' );
		}

		if ( $has_fatal_error ) {
			$result['errors'][] = __( 'Import is blocked by fatal plan errors.', 'revistalogos-core' );
		}

		if ( $result['can_import'] ) {
			$result['plan_fingerprint'] = self::plan_fingerprint( $validation, $plan );
		}

		return $result;
	}

	/**
	 * Revalidate the current state, enforce confirmations and apply without
	 * force only when the submitted plan still matches.
	 *
	 * @param Content_Migrator $migrator Migration service.
	 * @return array<string, mixed>
	 */
	private static function handle_import( $migrator ) {
		$result = self::build_plan_result( $migrator, 'import_attempt' );
		$backup = isset( $_POST[ self::BACKUP_FIELD ] )
			? sanitize_text_field( wp_unslash( $_POST[ self::BACKUP_FIELD ] ) )
			: '';
		$confirmed = isset( $_POST[ self::CONFIRM_FIELD ] )
			&& '1' === sanitize_key( wp_unslash( $_POST[ self::CONFIRM_FIELD ] ) );
		$submitted_plan = isset( $_POST[ self::PLAN_FIELD ] )
			? sanitize_text_field( wp_unslash( $_POST[ self::PLAN_FIELD ] ) )
			: '';

		if ( ! $result['can_import'] ) {
			return $result;
		}

		if ( '' === $submitted_plan || ! hash_equals( $result['plan_fingerprint'], $submitted_plan ) ) {
			$result['errors'][] = __( 'The plan is missing or stale. Run Validate and Plan again.', 'revistalogos-core' );
		}

		if ( '' === $backup ) {
			$result['errors'][] = __( 'Backup evidence is required.', 'revistalogos-core' );
		}

		if ( ! $confirmed ) {
			$result['errors'][] = __( 'Explicit import confirmation is required.', 'revistalogos-core' );
		}

		if ( $result['errors'] ) {
			return $result;
		}

		$result['type']       = 'import_result';
		$result['backup']     = $backup;
		$result['import']     = $migrator->import_report( true, false );
		$result['import_errors'] = $migrator->import_report_errors( $result['import'] );
		$result['import_ok']  = empty( $result['import_errors'] );
		$result['verify']     = $migrator->verify();
		$result['can_import'] = false;

		return $result;
	}

	/**
	 * Build the current verification report without writing.
	 *
	 * @param Content_Migrator $migrator Migration service.
	 * @return array<string, mixed>
	 */
	private static function build_verify_result( $migrator ) {
		$validation = $migrator->validation_report();
		$result     = array(
			'type'       => 'verify',
			'validation' => $validation,
			'verify'     => array(),
			'errors'     => array(),
		);

		if ( ! $validation['valid'] ) {
			$result['errors'][] = __( 'Payload validation failed. Verification could not run.', 'revistalogos-core' );
			return $result;
		}

		$result['verify'] = $migrator->verify();

		return $result;
	}

	/**
	 * Bind import authorization to the exact read-only plan shown.
	 *
	 * @param array $validation Validation report.
	 * @param array $plan       Plan report.
	 * @return string
	 */
	private static function plan_fingerprint( $validation, $plan ) {
		$data = wp_json_encode(
			array(
				'user_id'    => get_current_user_id(),
				'validation' => $validation,
				'plan'       => $plan,
			)
		);

		return hash_hmac( 'sha256', (string) $data, wp_salt( 'nonce' ) );
	}

	/**
	 * Enforce authentication and site-administrator capability.
	 */
	private static function require_administrator() {
		if ( is_user_logged_in() && current_user_can( 'manage_options' ) ) {
			return;
		}

		wp_die(
			esc_html__( 'You are not allowed to run the institutional content recovery tool.', 'revistalogos-core' ),
			esc_html__( 'Forbidden', 'revistalogos-core' ),
			array( 'response' => 403 )
		);
	}

	/**
	 * Render one action form with its own nonce.
	 */
	private static function render_validate_form() {
		echo '<hr>';
		echo '<h2>' . esc_html__( 'Stage A: Validate and Plan', 'revistalogos-core' ) . '</h2>';
		echo '<p>' . esc_html__( 'Read-only: validates the payload, checks protected slugs and shows all planned actions.', 'revistalogos-core' ) . '</p>';
		echo '<form method="post">';
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );
		echo '<input type="hidden" name="' . esc_attr( self::ACTION_FIELD ) . '" value="validate_plan">';
		submit_button( __( 'Validate and Plan', 'revistalogos-core' ), 'primary', 'submit', false );
		echo '</form>';
	}

	/**
	 * Render the guarded, non-force import form.
	 *
	 * @param string $plan_fingerprint Signed current plan.
	 */
	private static function render_import_form( $plan_fingerprint ) {
		echo '<hr>';
		echo '<h2>' . esc_html__( 'Institutional Import', 'revistalogos-core' ) . '</h2>';
		echo '<p><strong>' . esc_html__( 'No force mode is available.', 'revistalogos-core' ) . '</strong> ';
		echo esc_html__( 'Any collision or stale plan blocks the import.', 'revistalogos-core' ) . '</p>';
		echo '<form method="post">';
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );
		echo '<input type="hidden" name="' . esc_attr( self::ACTION_FIELD ) . '" value="import">';
		echo '<input type="hidden" name="' . esc_attr( self::PLAN_FIELD ) . '" value="' . esc_attr( $plan_fingerprint ) . '">';
		echo '<table class="form-table" role="presentation"><tbody>';
		echo '<tr><th scope="row"><label for="' . esc_attr( self::BACKUP_FIELD ) . '">' . esc_html__( 'Backup evidence', 'revistalogos-core' ) . '</label></th>';
		echo '<td><input type="text" class="regular-text" required id="' . esc_attr( self::BACKUP_FIELD ) . '" name="' . esc_attr( self::BACKUP_FIELD ) . '" value="">';
		echo '<p class="description">' . esc_html__( 'Enter the real fresh backup identifier, date, ticket or snapshot reference.', 'revistalogos-core' ) . '</p></td></tr>';
		echo '<tr><th scope="row">' . esc_html__( 'Confirmation', 'revistalogos-core' ) . '</th><td><label>';
		echo '<input type="checkbox" required name="' . esc_attr( self::CONFIRM_FIELD ) . '" value="1"> ';
		echo esc_html__( 'I confirm that a fresh production backup exists and I authorize the institutional content import.', 'revistalogos-core' );
		echo '</label></td></tr>';
		echo '</tbody></table>';
		submit_button( __( 'Run Institutional Import', 'revistalogos-core' ), 'primary', 'submit', false );
		echo '</form>';
	}

	/**
	 * Render the verification action.
	 */
	private static function render_verify_form() {
		echo '<hr>';
		echo '<h2>' . esc_html__( 'Verify', 'revistalogos-core' ) . '</h2>';
		echo '<form method="post">';
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );
		echo '<input type="hidden" name="' . esc_attr( self::ACTION_FIELD ) . '" value="verify">';
		submit_button( __( 'Verify Institutional Content', 'revistalogos-core' ), 'secondary', 'submit', false );
		echo '</form>';
	}

	/**
	 * Render a sanitized recovery result.
	 *
	 * @param array $result Recovery result.
	 */
	private static function render_result( $result ) {
		if ( ! empty( $result['errors'] ) ) {
			echo '<div class="notice notice-error inline"><p><strong>' . esc_html__( 'Import unavailable.', 'revistalogos-core' ) . '</strong></p><ul>';
			foreach ( $result['errors'] as $error ) {
				echo '<li>' . esc_html( $error ) . '</li>';
			}
			echo '</ul></div>';
		}

		if ( ! empty( $result['validation'] ) ) {
			self::render_validation( $result['validation'] );
		}

		if ( ! empty( $result['plan'] ) ) {
			self::render_plan( $result['plan'], ! empty( $result['can_import'] ) );
		}

		if ( ! empty( $result['import'] ) ) {
			if ( ! empty( $result['import_ok'] ) ) {
				echo '<div class="notice notice-success inline"><p>' . esc_html__( 'Institutional import completed without force.', 'revistalogos-core' ) . '</p></div>';
			} else {
				echo '<div class="notice notice-error inline"><p><strong>' . esc_html__( 'Institutional import stopped with runtime errors.', 'revistalogos-core' ) . '</strong> ';
				echo esc_html__( 'Do not continue to production route QA until these errors are resolved.', 'revistalogos-core' ) . '</p></div>';
				self::render_string_list( __( 'Import runtime errors', 'revistalogos-core' ), $result['import_errors'] );
			}

			echo '<p><strong>' . esc_html__( 'Backup evidence:', 'revistalogos-core' ) . '</strong> ' . esc_html( $result['backup'] ) . '</p>';
			self::render_import_report( $result['import'] );
		}

		if ( array_key_exists( 'verify', $result ) && ! empty( $result['verify'] ) ) {
			self::render_verify( $result['verify'] );
			self::render_manual_qa();
		}
	}

	/**
	 * Render payload validation evidence.
	 *
	 * @param array $validation Validation report.
	 */
	private static function render_validation( $validation ) {
		echo '<h2>' . esc_html__( 'Payload validation', 'revistalogos-core' ) . '</h2>';
		echo '<p><strong>' . esc_html__( 'Result:', 'revistalogos-core' ) . '</strong> ';
		echo esc_html( $validation['valid'] ? 'PASS' : 'FAIL' ) . '</p>';

		if ( $validation['summary'] ) {
			printf(
				'<p>%1$s %2$s; %3$s %4$s; %5$d %6$s; %7$d %8$s.</p>',
				esc_html__( 'Payload', 'revistalogos-core' ),
				esc_html( $validation['summary']['payload_version'] ),
				esc_html__( 'generator', 'revistalogos-core' ),
				esc_html( $validation['summary']['generator_version'] ),
				(int) $validation['summary']['entries'],
				esc_html__( 'entries', 'revistalogos-core' ),
				(int) $validation['summary']['media'],
				esc_html__( 'media seeds', 'revistalogos-core' )
			);
		}

		self::render_string_list( __( 'Errors', 'revistalogos-core' ), $validation['errors'] );
		self::render_string_list( __( 'Warnings', 'revistalogos-core' ), $validation['warnings'] );

		if ( $validation['coverage'] ) {
			echo '<h3>' . esc_html__( 'Canonical coverage', 'revistalogos-core' ) . '</h3>';
			echo '<table class="widefat striped"><thead><tr><th>' . esc_html__( 'Source', 'revistalogos-core' ) . '</th><th>' . esc_html__( 'Coverage', 'revistalogos-core' ) . '</th><th>' . esc_html__( 'Note', 'revistalogos-core' ) . '</th></tr></thead><tbody>';
			foreach ( $validation['coverage'] as $row ) {
				echo '<tr><td>' . esc_html( $row['source_key'] ) . '</td><td>' . (int) $row['found_verbatim'] . '/' . (int) $row['canonical_paragraphs'] . '</td><td>' . esc_html( $row['note'] ) . '</td></tr>';
			}
			echo '</tbody></table>';
		}
	}

	/**
	 * Render collision and dry-run details.
	 *
	 * @param array $plan       Plan report.
	 * @param bool  $can_import Whether all gates passed.
	 */
	private static function render_plan( $plan, $can_import ) {
		echo '<h2>' . esc_html__( 'Protected slug preflight', 'revistalogos-core' ) . '</h2>';
		echo '<table class="widefat striped"><thead><tr><th>' . esc_html__( 'Slug', 'revistalogos-core' ) . '</th><th>' . esc_html__( 'Expected source', 'revistalogos-core' ) . '</th><th>' . esc_html__( 'Classification', 'revistalogos-core' ) . '</th><th>' . esc_html__( 'Existing Page', 'revistalogos-core' ) . '</th><th>' . esc_html__( 'Detail', 'revistalogos-core' ) . '</th></tr></thead><tbody>';
		foreach ( $plan['slugs'] as $row ) {
			$existing = $row['post_id'] ? sprintf( '#%d %s', $row['post_id'], $row['title'] ) : '—';
			echo '<tr><td><code>' . esc_html( $row['slug'] ) . '</code></td><td><code>' . esc_html( $row['source_key'] ) . '</code></td><td><strong>' . esc_html( $row['status'] ) . '</strong></td><td>' . esc_html( $existing ) . '</td><td>' . esc_html( $row['detail'] ) . '</td></tr>';
		}
		echo '</tbody></table>';

		echo '<h2>' . esc_html__( 'Page actions', 'revistalogos-core' ) . '</h2>';
		echo '<table class="widefat striped"><thead><tr><th>' . esc_html__( 'Source', 'revistalogos-core' ) . '</th><th>' . esc_html__( 'Slug', 'revistalogos-core' ) . '</th><th>' . esc_html__( 'Action', 'revistalogos-core' ) . '</th><th>' . esc_html__( 'Reason', 'revistalogos-core' ) . '</th></tr></thead><tbody>';
		foreach ( $plan['entries'] as $row ) {
			echo '<tr><td><code>' . esc_html( $row['key'] ) . '</code></td><td><code>' . esc_html( $row['slug'] ) . '</code></td><td>' . esc_html( $row['action'] ) . '</td><td>' . esc_html( $row['reason'] ) . '</td></tr>';
		}
		echo '</tbody></table>';

		self::render_media_rows( __( 'Media actions', 'revistalogos-core' ), $plan['media'] );
		self::render_site_rows( $plan['site'] );
		self::render_string_list( __( 'Fatal plan errors', 'revistalogos-core' ), $plan['fatal_errors'] );

		echo '<p><strong>' . esc_html__( 'Import gate:', 'revistalogos-core' ) . '</strong> ';
		echo esc_html( $can_import ? 'PASS' : 'BLOCKED' ) . '</p>';
	}

	/**
	 * Render applied media, Page, menu and reading-setting actions.
	 *
	 * @param array $report Import report.
	 */
	private static function render_import_report( $report ) {
		self::render_media_rows( __( 'Applied media actions', 'revistalogos-core' ), $report['media'] );

		echo '<h3>' . esc_html__( 'Applied Page actions', 'revistalogos-core' ) . '</h3>';
		echo '<table class="widefat striped"><thead><tr><th>' . esc_html__( 'Source', 'revistalogos-core' ) . '</th><th>' . esc_html__( 'Action', 'revistalogos-core' ) . '</th><th>' . esc_html__( 'Post ID', 'revistalogos-core' ) . '</th><th>' . esc_html__( 'Result', 'revistalogos-core' ) . '</th></tr></thead><tbody>';
		foreach ( $report['entries'] as $row ) {
			$unresolved = ! empty( $row['unresolved'] ) ? 'Unresolved: ' . implode( ', ', $row['unresolved'] ) : ( $row['reason'] ?? '' );
			echo '<tr><td><code>' . esc_html( $row['key'] ) . '</code></td><td>' . esc_html( $row['action'] ) . '</td><td>' . (int) ( $row['post_id'] ?? 0 ) . '</td><td>' . esc_html( $unresolved ) . '</td></tr>';
		}
		echo '</tbody></table>';

		self::render_site_rows( $report['site'] );
	}

	/**
	 * Render verification status and categorized failure counts.
	 *
	 * @param array $rows Verification rows.
	 */
	private static function render_verify( $rows ) {
		$summary = array(
			'missing' => 0,
			'stale'   => 0,
			'drifted' => 0,
			'errors'  => 0,
		);
		$failures = 0;

		foreach ( $rows as $row ) {
			if ( 'OK' === $row['status'] ) {
				continue;
			}

			$failures++;

			if ( 0 === strpos( $row['status'], 'MISSING' ) ) {
				$summary['missing']++;
			} elseif ( 0 === strpos( $row['status'], 'STALE' ) ) {
				$summary['stale']++;
			} elseif ( 0 === strpos( $row['status'], 'DRIFTED' ) ) {
				$summary['drifted']++;
			} else {
				$summary['errors']++;
			}
		}

		echo '<h2>' . esc_html__( 'Verify result', 'revistalogos-core' ) . '</h2>';
		echo '<p><strong>' . esc_html__( 'Overall:', 'revistalogos-core' ) . '</strong> ' . esc_html( 0 === $failures ? 'PASS' : 'FAIL' ) . '</p>';
		printf(
			'<p>%1$s: %2$d; %3$s: %4$d; %5$s: %6$d; %7$s: %8$d.</p>',
			esc_html__( 'Missing', 'revistalogos-core' ),
			(int) $summary['missing'],
			esc_html__( 'Stale', 'revistalogos-core' ),
			(int) $summary['stale'],
			esc_html__( 'Drifted', 'revistalogos-core' ),
			(int) $summary['drifted'],
			esc_html__( 'Contamination/errors', 'revistalogos-core' ),
			(int) $summary['errors']
		);

		echo '<table class="widefat striped"><thead><tr><th>' . esc_html__( 'Source', 'revistalogos-core' ) . '</th><th>' . esc_html__( 'Status', 'revistalogos-core' ) . '</th></tr></thead><tbody>';
		foreach ( $rows as $row ) {
			echo '<tr><td><code>' . esc_html( $row['key'] ) . '</code></td><td>' . esc_html( $row['status'] ) . '</td></tr>';
		}
		echo '</tbody></table>';
	}

	/**
	 * Render media action rows.
	 *
	 * @param string $heading Section heading.
	 * @param array  $rows    Media rows.
	 */
	private static function render_media_rows( $heading, $rows ) {
		echo '<h3>' . esc_html( $heading ) . '</h3>';
		echo '<table class="widefat striped"><thead><tr><th>' . esc_html__( 'Source', 'revistalogos-core' ) . '</th><th>' . esc_html__( 'Action', 'revistalogos-core' ) . '</th><th>' . esc_html__( 'Attachment ID', 'revistalogos-core' ) . '</th></tr></thead><tbody>';
		foreach ( $rows as $row ) {
			echo '<tr><td><code>' . esc_html( $row['key'] ) . '</code></td><td>' . esc_html( $row['action'] ) . '</td><td>' . (int) $row['id'] . '</td></tr>';
		}
		echo '</tbody></table>';
	}

	/**
	 * Split the existing site report into reading and menu actions.
	 *
	 * @param array $rows Site report lines.
	 */
	private static function render_site_rows( $rows ) {
		$reading = array();
		$menus   = array();

		foreach ( $rows as $row ) {
			if ( 0 === strpos( $row, 'menu "' ) || 0 === strpos( $row, 'location ' ) ) {
				$menus[] = $row;
			} else {
				$reading[] = $row;
			}
		}

		self::render_string_list( __( 'Reading-setting actions', 'revistalogos-core' ), $reading );
		self::render_string_list( __( 'Menu actions', 'revistalogos-core' ), $menus );
	}

	/**
	 * Render an escaped list only when it has entries.
	 *
	 * @param string $heading List heading.
	 * @param array  $items   String items.
	 */
	private static function render_string_list( $heading, $items ) {
		if ( ! $items ) {
			return;
		}

		echo '<h3>' . esc_html( $heading ) . '</h3><ul>';
		foreach ( $items as $item ) {
			echo '<li>' . esc_html( $item ) . '</li>';
		}
		echo '</ul>';
	}

	/**
	 * Show the manual public-route checklist for the future production run.
	 */
	private static function render_manual_qa() {
		$routes = array(
			'/',
			'/normas/',
			'/enviar-colaboracion/',
			'/acerca/',
			'/contacto/',
			'/noticias/',
			'/etica/',
			'/politicas/',
			'/comite-editorial/',
			'/privacidad/',
			'/buscar/',
			'/enlaces/',
		);

		echo '<h2>' . esc_html__( 'Manual production QA for later', 'revistalogos-core' ) . '</h2>';
		echo '<p>' . esc_html__( 'Do not open search-engine indexing as part of this recovery.', 'revistalogos-core' ) . '</p><ul>';
		foreach ( $routes as $route ) {
			echo '<li><code>' . esc_html( $route ) . '</code></li>';
		}
		echo '</ul>';
	}
}
