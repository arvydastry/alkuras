<?php
/**
 * Meniu „walker“ su raidžių ritinio efektu.
 *
 * @package Alkuras
 */

defined( 'ABSPATH' ) || exit;

/**
 * Antraštės meniu.
 */
class Alkuras_Nav_Walker extends Walker_Nav_Menu {

	/**
	 * Pradeda elementą.
	 *
	 * @param string   $output Išvestis.
	 * @param WP_Post  $item   Elementas.
	 * @param int      $depth  Gylis.
	 * @param stdClass $args   Argumentai.
	 * @param int      $id     ID.
	 */
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$classes = empty( $item->classes ) ? array() : (array) $item->classes;
		$current = in_array( 'current-menu-item', $classes, true ) ? ' is-current' : '';
		$url     = ! empty( $item->url ) ? $item->url : '#';

		$output .= sprintf(
			'<a href="%1$s" class="hdr__link%2$s" data-roll>%3$s</a>',
			esc_url( $url ),
			esc_attr( $current ),
			esc_html( $item->title )
		);
	}

	/**
	 * Uždaro elementą.
	 *
	 * @param string   $output Išvestis.
	 * @param WP_Post  $item   Elementas.
	 * @param int      $depth  Gylis.
	 * @param stdClass $args   Argumentai.
	 */
	public function end_el( &$output, $item, $depth = 0, $args = null ) {}

	/**
	 * Be <ul>.
	 *
	 * @param string   $output Išvestis.
	 * @param int      $depth  Gylis.
	 * @param stdClass $args   Argumentai.
	 */
	public function start_lvl( &$output, $depth = 0, $args = null ) {}

	/**
	 * Be </ul>.
	 *
	 * @param string   $output Išvestis.
	 * @param int      $depth  Gylis.
	 * @param stdClass $args   Argumentai.
	 */
	public function end_lvl( &$output, $depth = 0, $args = null ) {}
}

/**
 * Mobilaus meniu „walker“.
 */
class Alkuras_Mobile_Walker extends Walker_Nav_Menu {

	/**
	 * Pradeda elementą.
	 *
	 * @param string   $output Išvestis.
	 * @param WP_Post  $item   Elementas.
	 * @param int      $depth  Gylis.
	 * @param stdClass $args   Argumentai.
	 * @param int      $id     ID.
	 */
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$output .= sprintf(
			'<a href="%1$s"><span>%2$s</span></a>',
			esc_url( ! empty( $item->url ) ? $item->url : '#' ),
			esc_html( $item->title )
		);
	}

	/**
	 * Uždaro elementą.
	 *
	 * @param string   $output Išvestis.
	 * @param WP_Post  $item   Elementas.
	 * @param int      $depth  Gylis.
	 * @param stdClass $args   Argumentai.
	 */
	public function end_el( &$output, $item, $depth = 0, $args = null ) {}

	/**
	 * Be <ul>.
	 *
	 * @param string   $output Išvestis.
	 * @param int      $depth  Gylis.
	 * @param stdClass $args   Argumentai.
	 */
	public function start_lvl( &$output, $depth = 0, $args = null ) {}

	/**
	 * Be </ul>.
	 *
	 * @param string   $output Išvestis.
	 * @param int      $depth  Gylis.
	 * @param stdClass $args   Argumentai.
	 */
	public function end_lvl( &$output, $depth = 0, $args = null ) {}
}
