<?php
/**
 * Paprasta laukų sistema turinio tipams (be mokamų įskiepių).
 *
 * @package Alkuras
 */

defined( 'ABSPATH' ) || exit;

/**
 * Laukų aprašai pagal įrašo tipą.
 *
 * @return array[]
 */
function alkuras_field_map() {
	return array(
		'alk_karjeras' => array(
			'title'  => __( 'Karjero duomenys', 'alkuras' ),
			'fields' => array(
				'_alk_regionas'    => array( 'label' => __( 'Rajonas', 'alkuras' ), 'type' => 'text', 'desc' => __( 'Pvz. „Kretingos r.“', 'alkuras' ) ),
				'_alk_adresas'     => array( 'label' => __( 'Adresas / privažiavimas', 'alkuras' ), 'type' => 'text' ),
				'_alk_medziagos'   => array( 'label' => __( 'Medžiagos', 'alkuras' ), 'type' => 'text', 'desc' => __( 'Pvz. „Žvyras · žvirgždas · skalda“', 'alkuras' ) ),
				'_alk_darbo_laikas'=> array( 'label' => __( 'Darbo laikas', 'alkuras' ), 'type' => 'text' ),
				'_alk_krovimas'    => array( 'label' => __( 'Krovimo įranga', 'alkuras' ), 'type' => 'text', 'desc' => __( 'Pvz. „Krautuvas vietoje“', 'alkuras' ) ),
				'_alk_x'           => array( 'label' => __( 'Padėtis žemėlapyje — X', 'alkuras' ), 'type' => 'number', 'step' => '0.1', 'desc' => __( '0–100. Kairė → dešinė.', 'alkuras' ) ),
				'_alk_y'           => array( 'label' => __( 'Padėtis žemėlapyje — Y', 'alkuras' ), 'type' => 'number', 'step' => '0.1', 'desc' => __( '0–100. Viršus → apačia.', 'alkuras' ) ),
			),
		),
		'alk_projektas' => array(
			'title'  => __( 'Projekto duomenys', 'alkuras' ),
			'fields' => array(
				'_alk_vieta'   => array( 'label' => __( 'Vieta', 'alkuras' ), 'type' => 'text', 'desc' => __( 'Pvz. „Klaipėda“', 'alkuras' ) ),
				'_alk_metai'   => array( 'label' => __( 'Metai', 'alkuras' ), 'type' => 'text' ),
				'_alk_faktas1' => array( 'label' => __( 'Faktas 1', 'alkuras' ), 'type' => 'text', 'desc' => __( 'Pvz. „4 200 m²“', 'alkuras' ) ),
				'_alk_faktas2' => array( 'label' => __( 'Faktas 2', 'alkuras' ), 'type' => 'text' ),
				'_alk_faktas3' => array( 'label' => __( 'Faktas 3', 'alkuras' ), 'type' => 'text' ),
				'_alk_dydis'   => array(
					'label'   => __( 'Dydis tinklelyje', 'alkuras' ),
					'type'    => 'select',
					'options' => array(
						'a' => __( 'Platus (7/12), 16:11', 'alkuras' ),
						'b' => __( 'Siauras aukštas (5/12), 4:5', 'alkuras' ),
						'c' => __( 'Vidutinis (5/12)', 'alkuras' ),
						'd' => __( 'Platus (7/12), 16:10', 'alkuras' ),
						'e' => __( 'Siauras (4/12), 3:4', 'alkuras' ),
						'f' => __( 'Labai platus (8/12), 16:9', 'alkuras' ),
					),
					'desc'    => __( 'Nustato kortelės proporcijas asimetriniame tinklelyje.', 'alkuras' ),
				),
			),
		),
		'alk_technika' => array(
			'title'  => __( 'Technikos duomenys', 'alkuras' ),
			'fields' => array(
				'_alk_specifikacija' => array( 'label' => __( 'Specifikacija', 'alkuras' ), 'type' => 'text', 'desc' => __( 'Pvz. „30 t klasė · griovimo strėlė“', 'alkuras' ) ),
				'_alk_video'         => array( 'label' => __( 'Video nuoroda (.mp4)', 'alkuras' ), 'type' => 'url', 'desc' => __( 'Rodoma užvedus pelę ant eilutės. Nebūtina.', 'alkuras' ) ),
			),
		),
		'alk_atsiliepimas' => array(
			'title'  => __( 'Atsiliepimo duomenys', 'alkuras' ),
			'fields' => array(
				'_alk_vardas'  => array( 'label' => __( 'Vardas, pavardė', 'alkuras' ), 'type' => 'text' ),
				'_alk_pareigos'=> array( 'label' => __( 'Įmonė / pareigos', 'alkuras' ), 'type' => 'text' ),
			),
		),
	);
}

/**
 * Prideda meta laukelių dėžutes.
 */
function alkuras_add_meta_boxes() {
	foreach ( alkuras_field_map() as $type => $cfg ) {
		add_meta_box( 'alkuras_fields_' . $type, $cfg['title'], 'alkuras_render_meta_box', $type, 'normal', 'high' );
	}

	add_meta_box(
		'alkuras_page_hero',
		__( 'Puslapio antraštė (hero)', 'alkuras' ),
		'alkuras_render_hero_box',
		'page',
		'normal',
		'default'
	);
}
add_action( 'add_meta_boxes', 'alkuras_add_meta_boxes' );

/**
 * Atvaizduoja laukus.
 *
 * @param WP_Post $post Įrašas.
 */
function alkuras_render_meta_box( $post ) {
	$map = alkuras_field_map();
	if ( empty( $map[ $post->post_type ] ) ) {
		return;
	}
	wp_nonce_field( 'alkuras_save_meta', 'alkuras_meta_nonce' );
	echo '<table class="form-table" role="presentation"><tbody>';
	foreach ( $map[ $post->post_type ]['fields'] as $key => $f ) {
		alkuras_render_field( $post->ID, $key, $f );
	}
	echo '</tbody></table>';
}

/**
 * Vienas laukas.
 *
 * @param int    $post_id Įrašo ID.
 * @param string $key     Meta raktas.
 * @param array  $f       Lauko aprašas.
 */
function alkuras_render_field( $post_id, $key, $f ) {
	$value = get_post_meta( $post_id, $key, true );
	$id    = 'fld' . $key;
	echo '<tr><th scope="row"><label for="' . esc_attr( $id ) . '">' . esc_html( $f['label'] ) . '</label></th><td>';

	switch ( $f['type'] ) {
		case 'textarea':
			printf(
				'<textarea id="%1$s" name="%2$s" rows="4" class="large-text">%3$s</textarea>',
				esc_attr( $id ),
				esc_attr( $key ),
				esc_textarea( $value )
			);
			break;

		case 'select':
			printf( '<select id="%1$s" name="%2$s">', esc_attr( $id ), esc_attr( $key ) );
			foreach ( $f['options'] as $ov => $ol ) {
				printf( '<option value="%1$s"%2$s>%3$s</option>', esc_attr( $ov ), selected( $value, $ov, false ), esc_html( $ol ) );
			}
			echo '</select>';
			break;

		case 'number':
			printf(
				'<input type="number" step="%4$s" id="%1$s" name="%2$s" value="%3$s" class="small-text">',
				esc_attr( $id ),
				esc_attr( $key ),
				esc_attr( $value ),
				esc_attr( $f['step'] ?? '1' )
			);
			break;

		case 'url':
			printf(
				'<input type="url" id="%1$s" name="%2$s" value="%3$s" class="large-text code">',
				esc_attr( $id ),
				esc_attr( $key ),
				esc_attr( $value )
			);
			break;

		default:
			printf(
				'<input type="text" id="%1$s" name="%2$s" value="%3$s" class="regular-text">',
				esc_attr( $id ),
				esc_attr( $key ),
				esc_attr( $value )
			);
	}

	if ( ! empty( $f['desc'] ) ) {
		echo '<p class="description">' . esc_html( $f['desc'] ) . '</p>';
	}
	echo '</td></tr>';
}

/**
 * Puslapio hero laukai (antraštės video ir tekstas).
 *
 * @param WP_Post $post Įrašas.
 */
function alkuras_render_hero_box( $post ) {
	wp_nonce_field( 'alkuras_save_meta', 'alkuras_meta_nonce' );
	$fields = array(
		'_alk_hero_eyebrow'  => array( 'label' => __( 'Viršutinė eilutė', 'alkuras' ), 'type' => 'text', 'desc' => __( 'Maža mono tipo eilutė virš antraštės.', 'alkuras' ) ),
		'_alk_hero_title'    => array( 'label' => __( 'Antraštė', 'alkuras' ), 'type' => 'textarea', 'desc' => __( 'Palikite tuščią — bus naudojamas puslapio pavadinimas. Nauja eilutė = naujas kilimo efektas.', 'alkuras' ) ),
		'_alk_hero_text'     => array( 'label' => __( 'Įvadinis tekstas', 'alkuras' ), 'type' => 'textarea' ),
		'_alk_hero_video'    => array( 'label' => __( 'Fono video (.mp4)', 'alkuras' ), 'type' => 'url', 'desc' => __( 'Pilno pločio video antraštėje. Nebūtina.', 'alkuras' ) ),
		'_alk_hero_poster'   => array( 'label' => __( 'Video plakatas (nuotrauka)', 'alkuras' ), 'type' => 'url', 'desc' => __( 'Rodoma, kol video kraunasi. Galima palikti tuščią ir naudoti „Įrašo paveikslėlį“.', 'alkuras' ) ),
		'_alk_hero_btn_text' => array( 'label' => __( 'Mygtuko tekstas', 'alkuras' ), 'type' => 'text' ),
		'_alk_hero_btn_url'  => array( 'label' => __( 'Mygtuko nuoroda', 'alkuras' ), 'type' => 'url' ),
	);
	echo '<table class="form-table" role="presentation"><tbody>';
	foreach ( $fields as $key => $f ) {
		alkuras_render_field( $post->ID, $key, $f );
	}
	echo '</tbody></table>';
	echo '<p class="description">' . esc_html__( 'Jei fono video nenurodytas, naudojamas įrašo paveikslėlis. Jei nėra nė vieno — antraštė rodoma tamsiu fonu.', 'alkuras' ) . '</p>';
}

/**
 * Išsaugo laukus.
 *
 * @param int $post_id Įrašo ID.
 */
function alkuras_save_meta( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( empty( $_POST['alkuras_meta_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['alkuras_meta_nonce'] ) ), 'alkuras_save_meta' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$map  = alkuras_field_map();
	$type = get_post_type( $post_id );
	$keys = array();

	if ( ! empty( $map[ $type ] ) ) {
		$keys = array_keys( $map[ $type ]['fields'] );
	}
	if ( 'page' === $type ) {
		$keys = array( '_alk_hero_eyebrow', '_alk_hero_title', '_alk_hero_text', '_alk_hero_video', '_alk_hero_poster', '_alk_hero_btn_text', '_alk_hero_btn_url' );
	}

	foreach ( $keys as $key ) {
		if ( ! isset( $_POST[ $key ] ) ) {
			continue;
		}
		$raw = wp_unslash( $_POST[ $key ] );

		if ( str_contains( $key, 'video' ) || str_contains( $key, 'poster' ) || str_contains( $key, 'url' ) ) {
			$clean = esc_url_raw( $raw );
		} elseif ( str_contains( $key, '_alk_x' ) || str_contains( $key, '_alk_y' ) ) {
			$clean = (float) $raw;
		} elseif ( str_contains( $key, 'title' ) || str_contains( $key, 'text' ) ) {
			$clean = sanitize_textarea_field( $raw );
		} else {
			$clean = sanitize_text_field( $raw );
		}

		if ( '' === $clean && 0 !== $clean ) {
			delete_post_meta( $post_id, $key );
		} else {
			update_post_meta( $post_id, $key, $clean );
		}
	}
}
add_action( 'save_post', 'alkuras_save_meta' );

/**
 * Alkuras turinio tipams naudojamas klasikinis redaktorius —
 * taip laukai matomi iš karto, o ne paslėpti bloko redaktoriaus stalčiuje.
 *
 * @param bool   $use  Ar naudoti blokų redaktorių.
 * @param string $type Įrašo tipas.
 * @return bool
 */
function alkuras_classic_editor( $use, $type ) {
	$classic = array( 'alk_karjeras', 'alk_projektas', 'alk_technika', 'alk_atsiliepimas' );
	return in_array( $type, $classic, true ) ? false : $use;
}
add_filter( 'use_block_editor_for_post_type', 'alkuras_classic_editor', 10, 2 );

/**
 * Puslapiams — paaiškinimas, kur ieškoti antraštės laukų.
 */
function alkuras_page_hint() {
	$screen = get_current_screen();
	if ( ! $screen || 'page' !== $screen->post_type || 'post' !== $screen->base ) {
		return;
	}
	?>
	<div class="notice notice-info" style="margin:1em 0">
		<p>
			<strong><?php esc_html_e( 'Alkuras:', 'alkuras' ); ?></strong>
			<?php esc_html_e( 'puslapio antraštės (hero) tekstą ir fono video rasite apačioje, bloke „Puslapio antraštė (hero)“. Sekcijas į turinį dėkite bloku „Trumpasis kodas“ — kodų sąrašas yra Alkuras → Nustatymai.', 'alkuras' ); ?>
		</p>
	</div>
	<?php
}
add_action( 'edit_form_after_title', 'alkuras_page_hint' );

/**
 * Trumpųjų kodų mygtukas puslapio redaktoriuje (klasikiniame).
 */
function alkuras_admin_help_styles() {
	$screen = get_current_screen();
	if ( ! $screen || ! in_array( $screen->post_type, array( 'alk_karjeras', 'alk_projektas', 'alk_technika', 'alk_atsiliepimas' ), true ) ) {
		return;
	}
	echo '<style>
		#alkuras_fields_alk_karjeras,#alkuras_fields_alk_projektas,
		#alkuras_fields_alk_technika,#alkuras_fields_alk_atsiliepimas{
			border-left:3px solid #D8F250;
		}
		#alkuras_fields_alk_karjeras .form-table th,
		#alkuras_fields_alk_projektas .form-table th,
		#alkuras_fields_alk_technika .form-table th,
		#alkuras_fields_alk_atsiliepimas .form-table th{ width:220px; }
	</style>';
}
add_action( 'admin_head', 'alkuras_admin_help_styles' );
