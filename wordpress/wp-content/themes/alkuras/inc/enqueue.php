<?php
/**
 * Stilių ir scenarijų įkėlimas.
 *
 * @package Alkuras
 */

defined( 'ABSPATH' ) || exit;

/**
 * Šriftai iš Google Fonts.
 *
 * @return string
 */
function alkuras_fonts_url() {
	return 'https://fonts.googleapis.com/css2?family=Archivo:ital,wdth,wght@0,75..125,400..900;1,75..125,400..900&family=Inter+Tight:ital,wght@0,300..700;1,300..600&family=JetBrains+Mono:wght@400;500&display=swap';
}

/**
 * Viešosios dalies ištekliai.
 */
function alkuras_enqueue() {
	$ver = ALKURAS_VERSION;

	wp_enqueue_style( 'alkuras-fonts', alkuras_fonts_url(), array(), null ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
	wp_enqueue_style( 'alkuras-base', ALKURAS_URI . '/assets/css/base.css', array(), $ver );
	wp_enqueue_style( 'alkuras-components', ALKURAS_URI . '/assets/css/components.css', array( 'alkuras-base' ), $ver );
	wp_enqueue_style( 'alkuras-wp', ALKURAS_URI . '/assets/css/wp.css', array( 'alkuras-components' ), $ver );
	wp_enqueue_style( 'alkuras-theme', get_stylesheet_uri(), array( 'alkuras-wp' ), $ver );

	wp_enqueue_script( 'alkuras-app', ALKURAS_URI . '/assets/js/app.js', array(), $ver, true );
	wp_enqueue_script( 'alkuras-site', ALKURAS_URI . '/assets/js/site.js', array( 'alkuras-app' ), $ver, true );

	if ( function_exists( 'alkuras_js_config' ) ) {
		wp_localize_script( 'alkuras-app', 'ALKURAS', alkuras_js_config() );
	}

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'alkuras_enqueue' );

/**
 * Preconnect į medijos serverius.
 *
 * @param array  $urls Nuorodos.
 * @param string $rel  Ryšio tipas.
 * @return array
 */
function alkuras_resource_hints( $urls, $rel ) {
	if ( 'preconnect' === $rel ) {
		$urls[] = array( 'href' => 'https://fonts.gstatic.com', 'crossorigin' );
	}
	return $urls;
}
add_filter( 'wp_resource_hints', 'alkuras_resource_hints', 10, 2 );

/**
 * Redaktoriaus stiliai Gutenberge.
 */
function alkuras_editor_assets() {
	wp_enqueue_style( 'alkuras-fonts', alkuras_fonts_url(), array(), null ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
}
add_action( 'enqueue_block_editor_assets', 'alkuras_editor_assets' );
