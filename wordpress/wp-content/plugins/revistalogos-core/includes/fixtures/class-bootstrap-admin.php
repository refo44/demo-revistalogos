<?php
/**
 * Temporary administrator-only screen for the Volume 1 editorial
 * bootstrap. Remove this class and its Plugin wiring after production
 * bootstrap and frontend verification. Domain logic stays in Fixtures.
 *
 * @package Revistalogos_Core
 */

namespace Revistalogos_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Thin wp-admin controller for Fixtures::plan/bootstrap/verify.
 */
class Bootstrap_Admin {

	const PAGE_SLUG     = 'revistalogos-volume-1-bootstrap';
	const NONCE_ACTION  = 'revistalogos_volume1_bootstrap';
	const NONCE_FIELD   = 'revistalogos_volume1_bootstrap_nonce';
	const ACTION_FIELD  = 'revistalogos_volume1_bootstrap_action';
	const PLAN_FIELD    = 'revistalogos_volume1_bootstrap_plan';
	const CONFIRM_FIELD = 'revistalogos_volume1_bootstrap_confirm';

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
	 * Add the bootstrap screen under Tools for administrators only.
	 */
	public static function register_page() {
		$hook_suffix = add_management_page(
			__( 'Volume 1 Editorial Bootstrap', 'revistalogos-core' ),
			__( 'Volume 1 Editorial Bootstrap', 'revistalogos-core' ),
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
	 * Render the bootstrap screen and any prepared action result.
	 */
	public static function render_page() {
		self::require_administrator();

		$result = self::$result;

		echo '<div class="wrap">';
		echo '<h1>' . esc_html__( 'Volume 1 Editorial Bootstrap', 'revistalogos-core' ) . '</h1>';
		echo '<div class="notice notice-warning inline"><p><strong>' . esc_html__( 'Temporary execution tool.', 'revistalogos-core' ) . '</strong> ';
		echo esc_html__( 'Hosting has no usable SSH/WP-CLI path. This screen exists only as a bridge and must be removed after production bootstrap and frontend verification.', 'revistalogos-core' ) . '</p></div>';
		echo '<p>' . esc_html__( 'This screen calls the existing Fixtures plan, bootstrap and verify methods. It does not run the demo seed or provide force mode.', 'revistalogos-core' ) . '</p>';

		if ( $result ) {
			self::render_result( $result );
		}

		self::render_plan_form();

		if ( $result && in_array( $result['type'], array( 'plan', 'apply_attempt' ), true ) && ! empty( $result['can_apply'] ) ) {
			self::render_apply_form( $result['plan_fingerprint'] );
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
				esc_html__( 'Security check failed. Reload the Volume 1 bootstrap screen and try again.', 'revistalogos-core' ),
				esc_html__( 'Forbidden', 'revistalogos-core' ),
				array( 'response' => 403 )
			);
		}

		$action = isset( $_POST[ self::ACTION_FIELD ] )
			? sanitize_key( wp_unslash( $_POST[ self::ACTION_FIELD ] ) )
			: '';

		if ( 'validate_plan' === $action ) {
			return self::build_plan_result( 'plan' );
		}

		if ( 'apply' === $action ) {
			return self::handle_apply();
		}

		if ( 'verify' === $action ) {
			return self::build_verify_result();
		}

		return array(
			'type'   => 'error',
			'errors' => array( __( 'Unknown bootstrap action.', 'revistalogos-core' ) ),
		);
	}

	/**
	 * Build the read-only plan. Writes nothing.
	 *
	 * @param string $type Result type.
	 * @return array<string, mixed>
	 */
	private static function build_plan_result( $type ) {
		$state  = Fixtures::bootstrap_plan_state();
		$result = array(
			'type'             => $type,
			'plan'             => $state,
			'can_apply'        => ! empty( $state['can_apply'] ),
			'plan_fingerprint' => '',
			'errors'           => $state['collisions'],
		);

		if ( $result['can_apply'] ) {
			$result['plan_fingerprint'] = self::plan_fingerprint( $state );
		}

		return $result;
	}

	/**
	 * Re-run the plan, require confirmation, then apply the same
	 * Fixtures::bootstrap(true) path as WP-CLI. No backup field, no force.
	 *
	 * @return array<string, mixed>
	 */
	private static function handle_apply() {
		$result    = self::build_plan_result( 'apply_attempt' );
		$confirmed = isset( $_POST[ self::CONFIRM_FIELD ] )
			&& '1' === sanitize_key( wp_unslash( $_POST[ self::CONFIRM_FIELD ] ) );
		$submitted_plan = isset( $_POST[ self::PLAN_FIELD ] )
			? sanitize_text_field( wp_unslash( $_POST[ self::PLAN_FIELD ] ) )
			: '';

		if ( ! $result['can_apply'] ) {
			$result['errors'][] = __( 'Bootstrap is blocked until Validate and Plan passes with exactly one canonical Author and no collisions.', 'revistalogos-core' );
			return $result;
		}

		if ( '' === $submitted_plan || ! hash_equals( $result['plan_fingerprint'], $submitted_plan ) ) {
			$result['errors'][] = __( 'The plan is missing or stale. Run Validate and Plan again.', 'revistalogos-core' );
		}

		if ( ! $confirmed ) {
			$result['errors'][] = __( 'Explicit production bootstrap confirmation is required.', 'revistalogos-core' );
		}

		if ( $result['errors'] ) {
			return $result;
		}

		$applied = Fixtures::bootstrap( true );

		if ( is_wp_error( $applied ) ) {
			$result['errors'][] = $applied->get_error_message();
			$result['can_apply'] = false;
			return $result;
		}

		$result['type']      = 'apply_result';
		$result['lines']     = $applied;
		$result['verify']    = Fixtures::bootstrap_verify_state();
		$result['can_apply'] = false;

		return $result;
	}

	/**
	 * Current verification report without writing.
	 *
	 * @return array<string, mixed>
	 */
	private static function build_verify_result() {
		return array(
			'type'   => 'verify',
			'verify' => Fixtures::bootstrap_verify_state(),
			'errors' => array(),
		);
	}

	/**
	 * Bind apply authorization to the exact read-only plan shown.
	 *
	 * @param array $state bootstrap_plan_state() result.
	 * @return string
	 */
	private static function plan_fingerprint( $state ) {
		$data = wp_json_encode(
			array(
				'user_id'    => get_current_user_id(),
				'author_id'  => $state['author']['author'] ? (int) $state['author']['author']->ID : 0,
				'author_n'   => (int) $state['author']['count'],
				'gate'       => $state['gate'],
				'objects'    => $state['objects'],
				'collisions' => $state['collisions'],
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
			esc_html__( 'You are not allowed to run the Volume 1 editorial bootstrap tool.', 'revistalogos-core' ),
			esc_html__( 'Forbidden', 'revistalogos-core' ),
			array( 'response' => 403 )
		);
	}

	/**
	 * Render Validate and Plan.
	 */
	private static function render_plan_form() {
		echo '<hr>';
		echo '<h2>' . esc_html__( 'Validate and Plan', 'revistalogos-core' ) . '</h2>';
		echo '<p>' . esc_html__( 'Read-only: resolves Rafael, checks collisions and shows every planned action. Writes nothing.', 'revistalogos-core' ) . '</p>';
		echo '<form method="post">';
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );
		echo '<input type="hidden" name="' . esc_attr( self::ACTION_FIELD ) . '" value="validate_plan">';
		submit_button( __( 'Validate and Plan', 'revistalogos-core' ), 'primary', 'submit', false );
		echo '</form>';
	}

	/**
	 * Render the confirmation-gated apply form. No backup field, no force.
	 *
	 * @param string $plan_fingerprint Signed current plan.
	 */
	private static function render_apply_form( $plan_fingerprint ) {
		echo '<hr>';
		echo '<h2>' . esc_html__( 'Apply Volume 1 bootstrap', 'revistalogos-core' ) . '</h2>';
		echo '<p><strong>' . esc_html__( 'No force mode is available.', 'revistalogos-core' ) . '</strong> ';
		echo esc_html__( 'A stale plan, a Rafael problem or a collision blocks apply. A fresh backup is not required for this owner-approved operation; explicit confirmation is still mandatory.', 'revistalogos-core' ) . '</p>';
		echo '<form method="post">';
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );
		echo '<input type="hidden" name="' . esc_attr( self::ACTION_FIELD ) . '" value="apply">';
		echo '<input type="hidden" name="' . esc_attr( self::PLAN_FIELD ) . '" value="' . esc_attr( $plan_fingerprint ) . '">';
		echo '<p><label>';
		echo '<input type="checkbox" required name="' . esc_attr( self::CONFIRM_FIELD ) . '" value="1"> ';
		echo esc_html__( 'I authorize the Volume 1 editorial bootstrap in production.', 'revistalogos-core' );
		echo '</label></p>';
		submit_button( __( 'Run Volume 1 Bootstrap', 'revistalogos-core' ), 'primary', 'submit', false );
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
		submit_button( __( 'Verify Volume 1 Bootstrap', 'revistalogos-core' ), 'secondary', 'submit', false );
		echo '</form>';
	}

	/**
	 * Render a sanitized action result.
	 *
	 * @param array $result Action result.
	 */
	private static function render_result( $result ) {
		if ( ! empty( $result['errors'] ) ) {
			echo '<div class="notice notice-error inline"><p><strong>' . esc_html__( 'Bootstrap unavailable.', 'revistalogos-core' ) . '</strong></p><ul>';
			foreach ( $result['errors'] as $error ) {
				echo '<li>' . esc_html( $error ) . '</li>';
			}
			echo '</ul></div>';
		}

		if ( ! empty( $result['plan'] ) ) {
			self::render_plan( $result['plan'] );
		}

		if ( ! empty( $result['lines'] ) && 'apply_result' === $result['type'] ) {
			echo '<div class="notice notice-success inline"><p>' . esc_html__( 'Volume 1 editorial bootstrap applied without force.', 'revistalogos-core' ) . '</p></div>';
			self::render_string_list( __( 'Apply report', 'revistalogos-core' ), $result['lines'] );
		}

		if ( ! empty( $result['verify'] ) ) {
			self::render_verify( $result['verify'] );
		}
	}

	/**
	 * Render Rafael, collisions and object actions.
	 *
	 * @param array $plan bootstrap_plan_state() result.
	 */
	private static function render_plan( $plan ) {
		$author = $plan['author'];
		$match  = $author['author'];

		echo '<h2>' . esc_html__( 'Import gate', 'revistalogos-core' ) . '</h2>';
		echo '<p><strong>' . esc_html__( 'Import gate:', 'revistalogos-core' ) . '</strong> ' . esc_html( $plan['gate'] ) . '</p>';
		echo '<p><strong>' . esc_html__( 'Placeholder source:', 'revistalogos-core' ) . '</strong> ' . esc_html( $plan['source'] ) . '</p>';

		echo '<h2>' . esc_html__( 'Rafael preflight', 'revistalogos-core' ) . '</h2>';
		echo '<table class="widefat striped"><tbody>';
		echo '<tr><th>' . esc_html__( 'Canonical slug', 'revistalogos-core' ) . '</th><td><code>' . esc_html( $author['slug'] ) . '</code></td></tr>';
		echo '<tr><th>' . esc_html__( 'Matching Author CPT objects', 'revistalogos-core' ) . '</th><td>' . (int) $author['count'] . '</td></tr>';
		echo '<tr><th>' . esc_html__( 'Resolved post ID', 'revistalogos-core' ) . '</th><td>' . ( $match ? (int) $match->ID : 0 ) . '</td></tr>';
		echo '<tr><th>' . esc_html__( 'Post type', 'revistalogos-core' ) . '</th><td>' . ( $match ? esc_html( $match->post_type ) : '—' ) . '</td></tr>';
		echo '<tr><th>' . esc_html__( 'Status', 'revistalogos-core' ) . '</th><td>' . ( $match ? esc_html( $match->post_status ) : '—' ) . '</td></tr>';
		echo '<tr><th>' . esc_html__( 'Rafael gate', 'revistalogos-core' ) . '</th><td><strong>' . esc_html( $author['pass'] ? 'PASS' : 'BLOCK' ) . '</strong></td></tr>';
		echo '</tbody></table>';

		echo '<h2>' . esc_html__( 'Planned objects', 'revistalogos-core' ) . '</h2>';
		echo '<table class="widefat striped"><thead><tr>';
		echo '<th>' . esc_html__( 'Kind', 'revistalogos-core' ) . '</th>';
		echo '<th>' . esc_html__( 'Key', 'revistalogos-core' ) . '</th>';
		echo '<th>' . esc_html__( 'Title', 'revistalogos-core' ) . '</th>';
		echo '<th>' . esc_html__( 'Public slug', 'revistalogos-core' ) . '</th>';
		echo '<th>' . esc_html__( 'Action', 'revistalogos-core' ) . '</th>';
		echo '<th>' . esc_html__( 'Source', 'revistalogos-core' ) . '</th>';
		echo '<th>' . esc_html__( 'Detail', 'revistalogos-core' ) . '</th>';
		echo '</tr></thead><tbody>';

		foreach ( $plan['objects'] as $row ) {
			echo '<tr>';
			echo '<td>' . esc_html( $row['kind'] ) . '</td>';
			echo '<td><code>' . esc_html( $row['key'] ) . '</code></td>';
			echo '<td>' . esc_html( $row['title'] ) . '</td>';
			echo '<td><code>' . esc_html( $row['slug'] ) . '</code></td>';
			echo '<td><strong>' . esc_html( $row['status'] ) . '</strong></td>';
			echo '<td>' . esc_html( $row['source'] ) . '</td>';
			echo '<td>' . esc_html( $row['detail'] ) . '</td>';
			echo '</tr>';
		}

		echo '</tbody></table>';

		self::render_string_list( __( 'Collision / blocked reasons', 'revistalogos-core' ), $plan['collisions'] );
		self::render_string_list( __( 'Dry-run report', 'revistalogos-core' ), $plan['lines'] );
	}

	/**
	 * Render verify evidence and public URLs.
	 *
	 * @param array $state bootstrap_verify_state() result.
	 */
	private static function render_verify( $state ) {
		$verify = $state['verify'];
		$author = $state['author'];
		$issue  = $state['issue'];

		echo '<h2>' . esc_html__( 'Verify', 'revistalogos-core' ) . '</h2>';
		echo '<p><strong>' . esc_html__( 'Result:', 'revistalogos-core' ) . '</strong> ' . esc_html( $state['pass'] ? 'PASS' : 'FAIL' ) . '</p>';

		echo '<table class="widefat striped"><tbody>';
		echo '<tr><th>' . esc_html__( 'Target Issue found', 'revistalogos-core' ) . '</th><td>' . ( $issue ? 'yes #' . (int) $issue->ID : 'no' ) . '</td></tr>';
		echo '<tr><th>' . esc_html__( 'Expected articles found', 'revistalogos-core' ) . '</th><td>' . (int) count( array_filter( wp_list_pluck( $state['articles'], 'found' ) ) ) . '/' . (int) count( $state['articles'] ) . '</td></tr>';
		echo '<tr><th>' . esc_html__( 'Rafael reused', 'revistalogos-core' ) . '</th><td>' . esc_html( $author['pass'] ? 'yes' : 'no' ) . '</td></tr>';
		echo '<tr><th>' . esc_html__( 'Rafael match count', 'revistalogos-core' ) . '</th><td>' . (int) $author['count'] . '</td></tr>';
		echo '</tbody></table>';

		echo '<h3>' . esc_html__( 'Issue → article relationships', 'revistalogos-core' ) . '</h3>';
		echo '<table class="widefat striped"><thead><tr><th>' . esc_html__( 'Article', 'revistalogos-core' ) . '</th><th>' . esc_html__( 'Issue ID', 'revistalogos-core' ) . '</th><th>' . esc_html__( 'Authors', 'revistalogos-core' ) . '</th><th>' . esc_html__( 'Adopted', 'revistalogos-core' ) . '</th></tr></thead><tbody>';
		foreach ( $state['articles'] as $row ) {
			echo '<tr><td><code>' . esc_html( $row['key'] ) . '</code></td>';
			echo '<td>' . (int) $row['issue_id'] . '</td>';
			echo '<td>' . esc_html( implode( ',', $row['authors'] ) ) . '</td>';
			echo '<td>' . ( $row['adopted'] ? 'yes' : 'no' ) . '</td></tr>';
		}
		echo '</tbody></table>';

		self::render_string_list( __( 'Verify report', 'revistalogos-core' ), $verify['report'] );

		echo '<h3>' . esc_html__( 'Public URLs for manual verification', 'revistalogos-core' ) . '</h3>';
		echo '<ul>';
		foreach ( $state['urls'] as $url ) {
			echo '<li><a href="' . esc_url( $url ) . '">' . esc_html( $url ) . '</a></li>';
		}
		echo '</ul>';
	}

	/**
	 * Render a list of plain strings.
	 *
	 * @param string   $title Heading.
	 * @param string[] $items Lines.
	 */
	private static function render_string_list( $title, $items ) {
		if ( ! $items ) {
			return;
		}

		echo '<h3>' . esc_html( $title ) . '</h3><ul>';
		foreach ( $items as $item ) {
			echo '<li>' . esc_html( $item ) . '</li>';
		}
		echo '</ul>';
	}
}
