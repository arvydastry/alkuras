<?php
/**
 * Skaičiavimų logika (serverio pusėje). JS naudoja tuos pačius įkainius per wp_localize_script.
 *
 * @package Alkuras
 */

defined( 'ABSPATH' ) || exit;

/**
 * Griovimo darbų kainos skaičiavimas.
 *
 * @param array $args tipas, konstrukcija, plotas, pamatai, isvezimas, smulkinimas.
 * @return array
 */
function alkuras_calc_demolition( $args ) {
	$a = wp_parse_args(
		$args,
		array(
			'tipas'        => 'gyv',
			'konstrukcija' => 'mur',
			'plotas'       => 250,
			'pamatai'      => false,
			'isvezimas'    => true,
			'smulkinimas'  => false,
		)
	);

	$rates = array(
		'med' => (float) alkuras_get_option( 'demo_med' ),
		'mur' => (float) alkuras_get_option( 'demo_mur' ),
		'gzb' => (float) alkuras_get_option( 'demo_gzb' ),
		'mis' => (float) alkuras_get_option( 'demo_mis' ),
	);
	$coef  = array(
		'gyv'  => (float) alkuras_get_option( 'demo_k_gyv' ),
		'ukio' => (float) alkuras_get_option( 'demo_k_ukio' ),
		'gam'  => (float) alkuras_get_option( 'demo_k_gam' ),
		'inz'  => (float) alkuras_get_option( 'demo_k_inz' ),
	);

	$area = max( 1, (float) $a['plotas'] );
	$rate = $rates[ $a['konstrukcija'] ] ?? $rates['mur'];
	$k    = $coef[ $a['tipas'] ] ?? 1.0;

	$base  = $area * $rate * $k;
	$found = $a['pamatai'] ? $base * ( (float) alkuras_get_option( 'demo_pamatai' ) / 100 ) : 0.0;
	$haul  = $a['isvezimas'] ? $area * (float) alkuras_get_option( 'demo_isvezimas' ) : 0.0;
	$disc  = $a['smulkinimas'] ? -( ( $base + $haul ) * ( (float) alkuras_get_option( 'demo_smulk' ) / 100 ) ) : 0.0;
	$total = $base + $found + $haul + $disc;

	$dev   = (float) alkuras_get_option( 'demo_nuokrypis' ) / 100;
	$tempo = max( 1, (float) alkuras_get_option( 'demo_tempas' ) );

	return array(
		'base'      => $base,
		'foundation'=> $found,
		'haulage'   => $haul,
		'discount'  => $disc,
		'total'     => $total,
		'min'       => $total * ( 1 - $dev ),
		'max'       => $total * ( 1 + $dev ),
		'days'      => max( 2, (int) round( $area / $tempo ) ),
	);
}

/**
 * Medžiagų kiekio skaičiavimas.
 *
 * @param array $args plotas, storis (cm), tankinimas, tankis (t/m³), kaina (€/t).
 * @return array
 */
function alkuras_calc_material( $args ) {
	$a = wp_parse_args(
		$args,
		array(
			'plotas'     => 100,
			'storis'     => 15,
			'tankinimas' => 1.2,
			'tankis'     => 1.75,
			'kaina'      => 0,
		)
	);

	$m3    = max( 0, (float) $a['plotas'] ) * ( max( 0, (float) $a['storis'] ) / 100 ) * (float) $a['tankinimas'];
	$tons  = $m3 * (float) $a['tankis'];
	$talpa = max( 1, (float) alkuras_get_option( 'del_talpa' ) );

	return array(
		'm3'    => $m3,
		'tons'  => $tons,
		'loads' => (int) ceil( $tons / $talpa ),
		'value' => $tons * (float) $a['kaina'],
	);
}

/**
 * Pristatymo kainos skaičiavimas pagal miestą ir kiekį.
 *
 * @param string $city Miestas.
 * @param float  $tons Tonos.
 * @return array|null
 */
function alkuras_calc_delivery( $city, $tons ) {
	$zone = alk_match_zone( $city );
	if ( ! $zone ) {
		return null;
	}

	$tons  = max( 0.1, (float) $tons );
	$talpa = max( 1, (float) alkuras_get_option( 'del_talpa' ) );
	$loads = (int) ceil( $tons / $talpa );
	$price = ( $loads * (float) alkuras_get_option( 'del_reisas' ) )
		+ ( $zone['km'] * (float) alkuras_get_option( 'del_km_t' ) * $tons );
	$price = max( (float) alkuras_get_option( 'del_min' ), $price );

	return array(
		'city'   => $zone['city'],
		'km'     => $zone['km'],
		'quarry' => $zone['quarry'],
		'loads'  => $loads,
		'tons'   => $tons,
		'price'  => $price,
	);
}

/**
 * Įkainiai, perduodami į JS.
 *
 * @return array
 */
function alkuras_js_config() {
	$zones = array();
	foreach ( alk_get_zones() as $z ) {
		$q       = alk_get_quarry( $z['quarry'] );
		$zones[] = array(
			'city'   => $z['city'],
			'km'     => $z['km'],
			'quarry' => $q ? $q['name'] : $z['quarry'],
			'slug'   => $z['quarry'],
		);
	}

	return array(
		'ajax'    => admin_url( 'admin-ajax.php' ),
		'nonce'   => wp_create_nonce( 'alkuras_front' ),
		'rates'   => array(
			'med'       => (float) alkuras_get_option( 'demo_med' ),
			'mur'       => (float) alkuras_get_option( 'demo_mur' ),
			'gzb'       => (float) alkuras_get_option( 'demo_gzb' ),
			'mis'       => (float) alkuras_get_option( 'demo_mis' ),
			'gyv'       => (float) alkuras_get_option( 'demo_k_gyv' ),
			'ukio'      => (float) alkuras_get_option( 'demo_k_ukio' ),
			'gam'       => (float) alkuras_get_option( 'demo_k_gam' ),
			'inz'       => (float) alkuras_get_option( 'demo_k_inz' ),
			'pamatai'   => (float) alkuras_get_option( 'demo_pamatai' ),
			'isvezimas' => (float) alkuras_get_option( 'demo_isvezimas' ),
			'smulk'     => (float) alkuras_get_option( 'demo_smulk' ),
			'tempas'    => (float) alkuras_get_option( 'demo_tempas' ),
			'nuokrypis' => (float) alkuras_get_option( 'demo_nuokrypis' ),
			'reisas'    => (float) alkuras_get_option( 'del_reisas' ),
			'kmT'       => (float) alkuras_get_option( 'del_km_t' ),
			'talpa'     => (float) alkuras_get_option( 'del_talpa' ),
			'delMin'    => (float) alkuras_get_option( 'del_min' ),
		),
		'zones'   => $zones,
		'quarries'=> alk_get_quarries(),
		'currency'=> function_exists( 'get_woocommerce_currency' ) ? get_woocommerce_currency() : 'EUR',
		'i18n'    => array(
			'tons'   => __( 't', 'alkuras' ),
			'days'   => __( 'd. d.', 'alkuras' ),
			'loads'  => __( 'vnt.', 'alkuras' ),
			'added'  => __( 'Įdėta į krepšelį', 'alkuras' ),
			'error'  => __( 'Nepavyko. Bandykite dar kartą.', 'alkuras' ),
			'deviation' => __( 'Galimas nuokrypis', 'alkuras' ),
			'trips'  => __( 'savivarčio reisai', 'alkuras' ),
		),
	);
}
