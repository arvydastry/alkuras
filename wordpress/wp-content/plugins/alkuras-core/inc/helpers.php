<?php
/**
 * Pagalbinės funkcijos.
 *
 * @package Alkuras
 */

defined( 'ABSPATH' ) || exit;

/**
 * Saugiai paima įrašo meta reikšmę su numatytąja verte.
 *
 * @param int    $post_id Įrašo ID.
 * @param string $key     Meta raktas.
 * @param mixed  $default Numatytoji reikšmė.
 * @return mixed
 */
function alk_meta( $post_id, $key, $default = '' ) {
	$value = get_post_meta( $post_id, $key, true );
	return ( '' === $value || null === $value ) ? $default : $value;
}

/**
 * Suformatuoja kainą eurais pagal LT formatą.
 *
 * @param float $amount Suma.
 * @param int   $dec    Skaičių po kablelio.
 * @return string
 */
function alk_price( $amount, $dec = 2 ) {
	return number_format_i18n( (float) $amount, $dec ) . ' €';
}

/**
 * Gražina visus karjerus kaip masyvą atvaizdavimui ir skaičiavimams.
 *
 * @return array[]
 */
function alk_get_quarries() {
	$cache = wp_cache_get( 'alk_quarries' );
	if ( is_array( $cache ) ) {
		return $cache;
	}

	$posts = get_posts(
		array(
			'post_type'      => 'alk_karjeras',
			'posts_per_page' => -1,
			'orderby'        => 'menu_order title',
			'order'          => 'ASC',
			'post_status'    => 'publish',
		)
	);

	$out = array();
	foreach ( $posts as $p ) {
		$out[] = array(
			'id'        => $p->post_name,
			'post_id'   => $p->ID,
			'name'      => $p->post_title,
			'region'    => alk_meta( $p->ID, '_alk_regionas' ),
			'materials' => alk_meta( $p->ID, '_alk_medziagos' ),
			'hours'     => alk_meta( $p->ID, '_alk_darbo_laikas' ),
			'load'      => alk_meta( $p->ID, '_alk_krovimas' ),
			'x'         => (float) alk_meta( $p->ID, '_alk_x', 50 ),
			'y'         => (float) alk_meta( $p->ID, '_alk_y', 50 ),
			'address'   => alk_meta( $p->ID, '_alk_adresas' ),
			'permalink' => get_permalink( $p ),
		);
	}

	wp_cache_set( 'alk_quarries', $out );
	return $out;
}

/**
 * Suranda karjerą pagal slug.
 *
 * @param string $slug Karjero slug.
 * @return array|null
 */
function alk_get_quarry( $slug ) {
	foreach ( alk_get_quarries() as $q ) {
		if ( $q['id'] === $slug ) {
			return $q;
		}
	}
	return null;
}

/**
 * Išvalo karjerų kešą išsaugojus įrašą.
 *
 * @param int $post_id Įrašo ID.
 */
function alk_flush_quarry_cache( $post_id ) {
	if ( 'alk_karjeras' === get_post_type( $post_id ) ) {
		wp_cache_delete( 'alk_quarries' );
	}
}
add_action( 'save_post', 'alk_flush_quarry_cache' );
add_action( 'deleted_post', 'alk_flush_quarry_cache' );

/**
 * Pristatymo zonų lentelė: miestas → artimiausias karjeras → atstumas km.
 *
 * @return array[]
 */
function alk_get_zones() {
	$raw  = (string) alkuras_get_option( 'zones' );
	$out  = array();
	$rows = preg_split( '/\r\n|\r|\n/', $raw );

	foreach ( $rows as $row ) {
		$row = trim( $row );
		if ( '' === $row || str_starts_with( $row, '#' ) ) {
			continue;
		}
		$parts = array_map( 'trim', explode( '|', $row ) );
		if ( count( $parts ) < 3 ) {
			continue;
		}
		$out[] = array(
			'city'   => $parts[0],
			'quarry' => $parts[1],
			'km'     => (float) $parts[2],
		);
	}

	return $out;
}

/**
 * Suranda artimiausią karjerą ir atstumą pagal miesto pavadinimą.
 *
 * @param string $city Miestas.
 * @return array|null {quarry: array|null, km: float, city: string}
 */
function alk_match_zone( $city ) {
	$city  = trim( (string) $city );
	$zones = alk_get_zones();
	if ( '' === $city || ! $zones ) {
		return null;
	}

	$needle = function_exists( 'mb_strtolower' ) ? mb_strtolower( $city, 'UTF-8' ) : strtolower( $city );
	$found  = null;

	foreach ( $zones as $z ) {
		$hay = function_exists( 'mb_strtolower' ) ? mb_strtolower( $z['city'], 'UTF-8' ) : strtolower( $z['city'] );
		if ( $hay === $needle ) {
			$found = $z;
			break;
		}
		if ( null === $found && str_starts_with( $needle, $hay ) ) {
			$found = $z;
		}
	}

	if ( ! $found ) {
		return null;
	}

	return array(
		'city'   => $found['city'],
		'km'     => (float) $found['km'],
		'quarry' => alk_get_quarry( $found['quarry'] ),
	);
}

/**
 * Sekcijų video/nuotraukų nuorodos iš Customizer.
 *
 * @param string $key Raktas be „alkuras_“ priešdėlio.
 * @return string
 */
function alkuras_get_media_url( $key ) {
	return (string) get_theme_mod( 'alkuras_' . $key, '' );
}

/**
 * Kontaktų puslapio nuoroda.
 *
 * @return string
 */
function alkuras_contact_url() {
	$id = (int) get_theme_mod( 'alkuras_contact_page', 0 );
	if ( $id && get_post_status( $id ) ) {
		return get_permalink( $id );
	}
	$page = get_page_by_path( 'kontaktai' );
	return $page ? get_permalink( $page ) : home_url( '/' );
}

/**
 * Prekės, tinkamos kiekio skaičiuoklei (turinčios tankį).
 *
 * @return array[]
 */
function alkuras_get_material_products() {
	if ( ! function_exists( 'wc_get_products' ) ) {
		return array();
	}
	$products = wc_get_products(
		array(
			'status'  => 'publish',
			'limit'   => 50,
			'orderby' => 'menu_order',
			'order'   => 'ASC',
		)
	);

	$out = array();
	foreach ( $products as $p ) {
		$density = (float) alk_meta( $p->get_id(), '_alk_tankis', 0 );
		if ( $density <= 0 ) {
			continue;
		}
		$out[] = array(
			'id'      => $p->get_id(),
			'name'    => $p->get_name(),
			'frac'    => alk_meta( $p->get_id(), '_alk_frakcija' ),
			'density' => $density,
			'price'   => (float) wc_get_price_including_tax( $p ),
		);
	}
	return $out;
}
