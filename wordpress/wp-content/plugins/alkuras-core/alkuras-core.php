<?php
/**
 * Plugin Name:       Alkuras Core
 * Plugin URI:        https://www.simplex.lt
 * Description:       UAB „Alkuras“ verslo logika: karjerai, projektai, technika, atsiliepimai, skaičiuoklės ir pristatymas pagal artimiausią karjerą. Laikoma atskirai nuo temos, kad išliktų pakeitus dizainą.
 * Version:           1.0.0
 * Requires at least: 6.4
 * Requires PHP:      8.0
 * Author:            Simplex.lt · MB „At Solutions“
 * Author URI:        https://www.simplex.lt
 * Text Domain:       alkuras
 * Domain Path:       /languages
 *
 * @package Alkuras
 */

defined( 'ABSPATH' ) || exit;

define( 'ALKURAS_CORE_VERSION', '1.0.0' );
define( 'ALKURAS_CORE_FILE', __FILE__ );
define( 'ALKURAS_CORE_DIR', plugin_dir_path( __FILE__ ) );
define( 'ALKURAS_CORE_URL', plugin_dir_url( __FILE__ ) );

require_once ALKURAS_CORE_DIR . 'inc/helpers.php';
require_once ALKURAS_CORE_DIR . 'inc/post-types.php';
require_once ALKURAS_CORE_DIR . 'inc/meta-boxes.php';
require_once ALKURAS_CORE_DIR . 'inc/settings.php';
require_once ALKURAS_CORE_DIR . 'inc/calculators.php';
require_once ALKURAS_CORE_DIR . 'inc/shortcodes.php';
require_once ALKURAS_CORE_DIR . 'inc/ajax.php';
require_once ALKURAS_CORE_DIR . 'inc/demo-content.php';

if ( class_exists( 'WooCommerce' ) || in_array( 'woocommerce/woocommerce.php', (array) get_option( 'active_plugins', array() ), true ) ) {
	require_once ALKURAS_CORE_DIR . 'inc/product-fields.php';
	require_once ALKURAS_CORE_DIR . 'inc/shipping.php';
}

/**
 * Aktyvacija — užregistruojam įrašų tipus ir atnaujinam nuorodų struktūrą.
 */
function alkuras_core_activate() {
	alkuras_register_post_types();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'alkuras_core_activate' );

function alkuras_core_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'alkuras_core_deactivate' );

/**
 * Vertimų failai.
 */
function alkuras_core_load_textdomain() {
	load_plugin_textdomain( 'alkuras', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'alkuras_core_load_textdomain' );

/**
 * Bendras „Alkuras“ meniu administravimo skydelyje.
 */
function alkuras_core_admin_menu() {
	add_menu_page(
		__( 'Alkuras', 'alkuras' ),
		__( 'Alkuras', 'alkuras' ),
		'manage_options',
		'alkuras',
		'alkuras_settings_page_render',
		'dashicons-hammer',
		58
	);
	add_submenu_page( 'alkuras', __( 'Nustatymai', 'alkuras' ), __( 'Nustatymai', 'alkuras' ), 'manage_options', 'alkuras', 'alkuras_settings_page_render' );
}
add_action( 'admin_menu', 'alkuras_core_admin_menu', 9 );
