<?php
/**
 * Plugin Name:       Revista LOGO ET SPES — Core
 * Plugin URI:        https://github.com/refo44/demo-revistalogos
 * Description:       Modelo de contenido de la Revista de Filosofía LOGO ET SPES: números, artículos, autores, taxonomías, rol Managing Editor, migración de contenido institucional y fixtures. El theme revistalogos solo presenta; este plugin es el dueño del dominio (ADR 0005).
 * Version:           0.2.3
 * Requires at least: 6.4
 * Tested up to:      7.0
 * Requires PHP:      7.4
 * Author:            CENFISS
 * Author URI:        https://cenfiss.net
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       revistalogos-core
 *
 * @package Revistalogos_Core
 */

// Direct access guard.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'REVISTALOGOS_CORE_VERSION', '0.2.3' );
define( 'REVISTALOGOS_CORE_FILE', __FILE__ );
define( 'REVISTALOGOS_CORE_DIR', plugin_dir_path( __FILE__ ) );
define( 'REVISTALOGOS_CORE_URL', plugin_dir_url( __FILE__ ) );

require_once REVISTALOGOS_CORE_DIR . 'includes/class-plugin.php';

register_activation_hook( __FILE__, array( 'Revistalogos_Core\Plugin', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Revistalogos_Core\Plugin', 'deactivate' ) );

// No side effects on include: everything else hooks from Plugin::boot().
Revistalogos_Core\Plugin::boot();
