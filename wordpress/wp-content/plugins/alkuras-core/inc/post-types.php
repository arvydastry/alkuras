<?php
/**
 * Turinio tipai: karjerai, projektai, technika, atsiliepimai.
 *
 * @package Alkuras
 */

defined( 'ABSPATH' ) || exit;

/**
 * Užregistruoja visus Alkuras turinio tipus.
 */
function alkuras_register_post_types() {

	register_post_type(
		'alk_karjeras',
		array(
			'labels'        => array(
				'name'               => __( 'Karjerai', 'alkuras' ),
				'singular_name'      => __( 'Karjeras', 'alkuras' ),
				'add_new'            => __( 'Pridėti naują', 'alkuras' ),
				'add_new_item'       => __( 'Pridėti karjerą', 'alkuras' ),
				'edit_item'          => __( 'Redaguoti karjerą', 'alkuras' ),
				'new_item'           => __( 'Naujas karjeras', 'alkuras' ),
				'view_item'          => __( 'Peržiūrėti karjerą', 'alkuras' ),
				'search_items'       => __( 'Ieškoti karjerų', 'alkuras' ),
				'not_found'          => __( 'Karjerų nerasta', 'alkuras' ),
				'menu_name'          => __( 'Karjerai', 'alkuras' ),
				'all_items'          => __( 'Visi karjerai', 'alkuras' ),
			),
			'public'        => true,
			'show_in_menu'  => 'alkuras',
			'show_in_rest'  => true,
			'menu_icon'     => 'dashicons-location',
			'supports'      => array( 'title', 'editor', 'thumbnail', 'page-attributes', 'excerpt' ),
			'has_archive'   => false,
			'rewrite'       => array( 'slug' => 'karjerai', 'with_front' => false ),
			'hierarchical'  => false,
		)
	);

	register_post_type(
		'alk_projektas',
		array(
			'labels'        => array(
				'name'          => __( 'Projektai', 'alkuras' ),
				'singular_name' => __( 'Projektas', 'alkuras' ),
				'add_new_item'  => __( 'Pridėti projektą', 'alkuras' ),
				'edit_item'     => __( 'Redaguoti projektą', 'alkuras' ),
				'menu_name'     => __( 'Projektai', 'alkuras' ),
				'all_items'     => __( 'Visi projektai', 'alkuras' ),
			),
			'public'        => true,
			'show_in_menu'  => 'alkuras',
			'show_in_rest'  => true,
			'menu_icon'     => 'dashicons-portfolio',
			'supports'      => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
			'has_archive'   => false,
			'rewrite'       => array( 'slug' => 'projektai', 'with_front' => false ),
		)
	);

	register_taxonomy(
		'alk_proj_kat',
		array( 'alk_projektas' ),
		array(
			'labels'            => array(
				'name'          => __( 'Projektų kategorijos', 'alkuras' ),
				'singular_name' => __( 'Kategorija', 'alkuras' ),
				'menu_name'     => __( 'Kategorijos', 'alkuras' ),
			),
			'public'            => true,
			'hierarchical'      => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'projektu-kategorija', 'with_front' => false ),
		)
	);

	register_post_type(
		'alk_technika',
		array(
			'labels'        => array(
				'name'          => __( 'Technika', 'alkuras' ),
				'singular_name' => __( 'Technikos vienetas', 'alkuras' ),
				'add_new_item'  => __( 'Pridėti techniką', 'alkuras' ),
				'edit_item'     => __( 'Redaguoti techniką', 'alkuras' ),
				'menu_name'     => __( 'Technikos parkas', 'alkuras' ),
				'all_items'     => __( 'Visa technika', 'alkuras' ),
			),
			'public'        => false,
			'show_ui'       => true,
			'show_in_menu'  => 'alkuras',
			'show_in_rest'  => true,
			'menu_icon'     => 'dashicons-admin-tools',
			'supports'      => array( 'title', 'thumbnail', 'page-attributes' ),
		)
	);

	register_post_type(
		'alk_atsiliepimas',
		array(
			'labels'        => array(
				'name'          => __( 'Atsiliepimai', 'alkuras' ),
				'singular_name' => __( 'Atsiliepimas', 'alkuras' ),
				'add_new_item'  => __( 'Pridėti atsiliepimą', 'alkuras' ),
				'edit_item'     => __( 'Redaguoti atsiliepimą', 'alkuras' ),
				'menu_name'     => __( 'Atsiliepimai', 'alkuras' ),
				'all_items'     => __( 'Visi atsiliepimai', 'alkuras' ),
			),
			'public'        => false,
			'show_ui'       => true,
			'show_in_menu'  => 'alkuras',
			'show_in_rest'  => true,
			'menu_icon'     => 'dashicons-format-quote',
			'supports'      => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
		)
	);
}
add_action( 'init', 'alkuras_register_post_types' );

/**
 * Patogesni stulpeliai sąrašuose.
 *
 * @param array $cols Stulpeliai.
 * @return array
 */
function alkuras_karjeras_columns( $cols ) {
	$new = array();
	foreach ( $cols as $k => $v ) {
		$new[ $k ] = $v;
		if ( 'title' === $k ) {
			$new['alk_regionas']  = __( 'Rajonas', 'alkuras' );
			$new['alk_medziagos'] = __( 'Medžiagos', 'alkuras' );
		}
	}
	return $new;
}
add_filter( 'manage_alk_karjeras_posts_columns', 'alkuras_karjeras_columns' );

/**
 * Stulpelių turinys.
 *
 * @param string $col     Stulpelis.
 * @param int    $post_id Įrašo ID.
 */
function alkuras_karjeras_column_content( $col, $post_id ) {
	if ( 'alk_regionas' === $col ) {
		echo esc_html( alk_meta( $post_id, '_alk_regionas', '—' ) );
	}
	if ( 'alk_medziagos' === $col ) {
		echo esc_html( alk_meta( $post_id, '_alk_medziagos', '—' ) );
	}
}
add_action( 'manage_alk_karjeras_posts_custom_column', 'alkuras_karjeras_column_content', 10, 2 );

/**
 * Rūšiavimas pagal menu_order administravime.
 *
 * @param WP_Query $query Užklausa.
 */
function alkuras_admin_order( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() ) {
		return;
	}
	$types = array( 'alk_karjeras', 'alk_projektas', 'alk_technika', 'alk_atsiliepimas' );
	if ( in_array( $query->get( 'post_type' ), $types, true ) && ! $query->get( 'orderby' ) ) {
		$query->set( 'orderby', 'menu_order title' );
		$query->set( 'order', 'ASC' );
	}
}
add_action( 'pre_get_posts', 'alkuras_admin_order' );
