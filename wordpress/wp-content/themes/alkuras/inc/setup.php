<?php
/**
 * Temos palaikomos funkcijos.
 *
 * @package Alkuras
 */

defined( 'ABSPATH' ) || exit;

/**
 * Temos nustatymai.
 */
function alkuras_setup() {
	load_theme_textdomain( 'alkuras', ALKURAS_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' )
	);
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 48,
			'width'       => 220,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	register_nav_menus(
		array(
			'primary' => __( 'Pagrindinis meniu (antraštėje)', 'alkuras' ),
			'mobile'  => __( 'Mobilus meniu', 'alkuras' ),
			'footer'  => __( 'Poraštės meniu', 'alkuras' ),
		)
	);

	add_image_size( 'alkuras-card', 900, 760, true );
	add_image_size( 'alkuras-folio', 1400, 1000, true );
	add_image_size( 'alkuras-hero', 1920, 1080, true );

	add_editor_style( 'assets/css/editor.css' );
}
add_action( 'after_setup_theme', 'alkuras_setup' );

/**
 * Turinio plotis.
 */
function alkuras_content_width() {
	$GLOBALS['content_width'] = 1280;
}
add_action( 'after_setup_theme', 'alkuras_content_width', 0 );

/**
 * Valoma antraštė.
 */
function alkuras_clean_head() {
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );
}
add_action( 'init', 'alkuras_clean_head' );

/**
 * Emoji scenarijai nenaudojami.
 */
function alkuras_disable_emojis() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
}
add_action( 'init', 'alkuras_disable_emojis' );

/**
 * Body klasės.
 *
 * @param array $classes Klasės.
 * @return array
 */
function alkuras_body_class( $classes ) {
	if ( alkuras_has_dark_header() ) {
		$classes[] = 'has-dark-hero';
	}
	if ( is_front_page() ) {
		$classes[] = 'is-front';
	}
	return $classes;
}
add_filter( 'body_class', 'alkuras_body_class' );

/**
 * Ar puslapis prasideda tamsia antrašte.
 *
 * @return bool
 */
function alkuras_has_dark_header() {
	if ( is_front_page() ) {
		return true;
	}
	if ( is_singular( array( 'page', 'alk_karjeras', 'alk_projektas' ) ) ) {
		return true;
	}
	if ( function_exists( 'is_shop' ) && ( is_shop() || is_product_category() ) ) {
		return true;
	}
	return false;
}

/**
 * Ištrauka — trumpesnė ir be […].
 *
 * @return string
 */
function alkuras_excerpt_more() {
	return '…';
}
add_filter( 'excerpt_more', 'alkuras_excerpt_more' );
