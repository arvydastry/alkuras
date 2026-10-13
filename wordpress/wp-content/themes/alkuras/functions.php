<?php
/**
 * Alkuras temos paleidimas.
 *
 * @package Alkuras
 */

defined( 'ABSPATH' ) || exit;

define( 'ALKURAS_VERSION', '1.0.0' );
define( 'ALKURAS_DIR', get_template_directory() );
define( 'ALKURAS_URI', get_template_directory_uri() );

require_once ALKURAS_DIR . '/inc/setup.php';
require_once ALKURAS_DIR . '/inc/enqueue.php';
require_once ALKURAS_DIR . '/inc/customizer.php';
require_once ALKURAS_DIR . '/inc/template-tags.php';
require_once ALKURAS_DIR . '/inc/nav-walker.php';
require_once ALKURAS_DIR . '/inc/woocommerce.php';
