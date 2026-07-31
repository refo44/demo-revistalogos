<?php
/**
 * Plugin orchestrator: loads modules and wires hooks.
 *
 * @package Revistalogos_Core
 */

namespace Revistalogos_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Boots every module. No work happens on include; all behavior is hooked.
 */
class Plugin {

	/**
	 * Option storing the schema/capability version last installed.
	 */
	const VERSION_OPTION = 'revistalogos_core_version';

	/**
	 * Whether boot() already ran.
	 *
	 * @var bool
	 */
	private static $booted = false;

	/**
	 * Load modules and register hooks. Idempotent.
	 */
	public static function boot() {
		if ( self::$booted ) {
			return;
		}
		self::$booted = true;

		self::load_modules();

		add_action( 'init', array( Content_Types::class, 'register' ) );
		add_action( 'init', array( Taxonomies::class, 'register' ), 11 ); // After CPTs.
		add_action( 'init', array( Metadata::class, 'register' ), 12 );
		add_action( 'init', array( Comments_Disabler::class, 'register' ), 13 );

		Meta_Boxes::register_hooks();
		Relationships::register_hooks();
		Contact_Form_Integration::register_hooks();

		// Idempotent upgrade routine for future schema/capability changes.
		add_action( 'admin_init', array( __CLASS__, 'maybe_upgrade' ) );
	}

	/**
	 * Require module files.
	 */
	private static function load_modules() {
		$includes = REVISTALOGOS_CORE_DIR . 'includes/';

		require_once $includes . 'content-types/class-content-types.php';
		require_once $includes . 'taxonomies/class-taxonomies.php';
		require_once $includes . 'metadata/class-metadata.php';
		require_once $includes . 'metadata/class-meta-boxes.php';
		require_once $includes . 'relationships/class-relationships.php';
		require_once $includes . 'roles/class-roles.php';
		require_once $includes . 'queries/class-queries.php';
		require_once $includes . 'integrations/class-comments-disabler.php';
		require_once $includes . 'integrations/class-contact-form-integration.php';
	}

	/**
	 * Activation: register content model, install role and base terms,
	 * then flush rewrite rules once (never at request time).
	 */
	public static function activate() {
		self::load_modules();

		Content_Types::register();
		Taxonomies::register();
		Taxonomies::insert_initial_terms();
		Roles::install();

		flush_rewrite_rules();

		update_option( self::VERSION_OPTION, REVISTALOGOS_CORE_VERSION );
	}

	/**
	 * Deactivation: flush rewrite rules. Content, meta, terms and the
	 * Managing Editor role are deliberately left in place.
	 */
	public static function deactivate() {
		flush_rewrite_rules();
	}

	/**
	 * Run idempotent upgrade steps when the stored version is older than
	 * the code version (roles/terms re-install safely).
	 */
	public static function maybe_upgrade() {
		$installed = get_option( self::VERSION_OPTION );

		if ( REVISTALOGOS_CORE_VERSION === $installed ) {
			return;
		}

		Taxonomies::insert_initial_terms();
		Roles::install();

		update_option( self::VERSION_OPTION, REVISTALOGOS_CORE_VERSION );
	}
}
