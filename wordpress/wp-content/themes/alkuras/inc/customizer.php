<?php
/**
 * Customizer — visas pradinio puslapio ir kontaktų turinys redaguojamas be kodo.
 *
 * @package Alkuras
 */

defined( 'ABSPATH' ) || exit;

/**
 * Pagalbinė: prideda nustatymą ir valdiklį.
 *
 * @param WP_Customize_Manager $wp_customize Customizer.
 * @param string               $id           Nustatymo ID.
 * @param string               $section      Sekcija.
 * @param string               $label        Etiketė.
 * @param string               $type         text|textarea|url|color|image|video|checkbox|number|select.
 * @param array                $args         Papildomi: default, description, choices.
 */
function alkuras_customize_add( $wp_customize, $id, $section, $label, $type = 'text', $args = array() ) {
	$sanitize = 'sanitize_text_field';
	if ( in_array( $type, array( 'url', 'image', 'video' ), true ) ) {
		$sanitize = 'esc_url_raw';
	} elseif ( 'textarea' === $type ) {
		$sanitize = 'wp_kses_post';
	} elseif ( 'color' === $type ) {
		$sanitize = 'sanitize_hex_color';
	} elseif ( 'checkbox' === $type ) {
		$sanitize = 'alkuras_sanitize_checkbox';
	} elseif ( 'number' === $type ) {
		$sanitize = 'sanitize_text_field';
	}

	$wp_customize->add_setting(
		$id,
		array(
			'default'           => $args['default'] ?? '',
			'sanitize_callback' => $sanitize,
			'transport'         => 'refresh',
		)
	);

	$common = array(
		'label'       => $label,
		'section'     => $section,
		'description' => $args['description'] ?? '',
	);

	if ( 'image' === $type ) {
		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, $id, array_merge( $common, array( 'settings' => $id ) ) ) );
		return;
	}

	if ( 'video' === $type ) {
		$wp_customize->add_control(
			new WP_Customize_Upload_Control(
				$wp_customize,
				$id,
				array_merge(
					$common,
					array(
						'settings'      => $id,
						'mime_type'     => 'video',
						'button_labels' => array(
							'select'       => __( 'Pasirinkti video', 'alkuras' ),
							'change'       => __( 'Keisti video', 'alkuras' ),
							'remove'       => __( 'Pašalinti', 'alkuras' ),
							'placeholder'  => __( 'Video nepasirinktas', 'alkuras' ),
							'frame_title'  => __( 'Pasirinkite video', 'alkuras' ),
							'frame_button' => __( 'Naudoti šį video', 'alkuras' ),
						),
					)
				)
			)
		);
		return;
	}

	if ( 'color' === $type ) {
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, $id, array_merge( $common, array( 'settings' => $id ) ) ) );
		return;
	}

	$control = array_merge( $common, array( 'type' => $type ) );
	if ( ! empty( $args['choices'] ) ) {
		$control['choices'] = $args['choices'];
	}
	$wp_customize->add_control( $id, $control );
}

/**
 * Checkbox valymas.
 *
 * @param mixed $v Reikšmė.
 * @return bool
 */
function alkuras_sanitize_checkbox( $v ) {
	return (bool) $v;
}

/**
 * Registruoja Customizer struktūrą.
 *
 * @param WP_Customize_Manager $wp_customize Customizer.
 */
function alkuras_customize_register( $wp_customize ) {

	$wp_customize->add_panel(
		'alkuras_panel',
		array(
			'title'       => __( 'Alkuras — svetainės turinys', 'alkuras' ),
			'description' => __( 'Čia redaguojamas pradinio puslapio turinys, kontaktai ir sekcijų video. Visi pakeitimai matomi iškart peržiūroje.', 'alkuras' ),
			'priority'    => 20,
		)
	);

	/* ---------------- Kontaktai ---------------- */
	$wp_customize->add_section( 'alkuras_contacts', array( 'title' => __( 'Kontaktai ir rekvizitai', 'alkuras' ), 'panel' => 'alkuras_panel' ) );
	alkuras_customize_add( $wp_customize, 'alkuras_company', 'alkuras_contacts', __( 'Įmonės pavadinimas', 'alkuras' ) );
	alkuras_customize_add( $wp_customize, 'alkuras_phone', 'alkuras_contacts', __( 'Telefonas', 'alkuras' ) );
	alkuras_customize_add( $wp_customize, 'alkuras_email', 'alkuras_contacts', __( 'El. paštas', 'alkuras' ) );
	alkuras_customize_add( $wp_customize, 'alkuras_address', 'alkuras_contacts', __( 'Adresas (biuras)', 'alkuras' ) );
	alkuras_customize_add( $wp_customize, 'alkuras_address2', 'alkuras_contacts', __( 'Adresas (registracijos)', 'alkuras' ) );
	alkuras_customize_add( $wp_customize, 'alkuras_reg', 'alkuras_contacts', __( 'Rekvizitai', 'alkuras' ), 'text', array( 'description' => __( 'Pvz. „Įm. k. 302312279 · PVM LT100005138216“', 'alkuras' ) ) );
	alkuras_customize_add( $wp_customize, 'alkuras_about', 'alkuras_contacts', __( 'Trumpas aprašymas poraštėje', 'alkuras' ), 'textarea' );

	/* ---------------- Hero ---------------- */
	$wp_customize->add_section( 'alkuras_hero', array( 'title' => __( 'Pradinis — antraštė (hero)', 'alkuras' ), 'panel' => 'alkuras_panel' ) );
	alkuras_customize_add( $wp_customize, 'alkuras_hero_eyebrow', 'alkuras_hero', __( 'Viršutinė eilutė', 'alkuras' ) );
	alkuras_customize_add( $wp_customize, 'alkuras_hero_title', 'alkuras_hero', __( 'Antraštė', 'alkuras' ), 'textarea', array( 'description' => __( 'Nauja eilutė = atskiras kilimo efektas. Žodį galima paryškinti akcento spalva naudojant &lt;em&gt;žodis&lt;/em&gt;.', 'alkuras' ) ) );
	alkuras_customize_add( $wp_customize, 'alkuras_hero_text', 'alkuras_hero', __( 'Įvadinis tekstas', 'alkuras' ), 'textarea' );
	alkuras_customize_add( $wp_customize, 'alkuras_hero_video', 'alkuras_hero', __( 'Fono video', 'alkuras' ), 'video' );
	alkuras_customize_add( $wp_customize, 'alkuras_hero_poster', 'alkuras_hero', __( 'Video plakatas', 'alkuras' ), 'image' );
	alkuras_customize_add( $wp_customize, 'alkuras_hero_btn1', 'alkuras_hero', __( 'Pirmas mygtukas — tekstas', 'alkuras' ) );
	alkuras_customize_add( $wp_customize, 'alkuras_hero_btn1_url', 'alkuras_hero', __( 'Pirmas mygtukas — nuoroda', 'alkuras' ), 'url' );
	alkuras_customize_add( $wp_customize, 'alkuras_hero_btn2', 'alkuras_hero', __( 'Antras mygtukas — tekstas', 'alkuras' ) );
	alkuras_customize_add( $wp_customize, 'alkuras_hero_btn2_url', 'alkuras_hero', __( 'Antras mygtukas — nuoroda', 'alkuras' ), 'url' );

	/* ---------------- Skaičiai ---------------- */
	$wp_customize->add_section( 'alkuras_stats', array( 'title' => __( 'Pradinis — skaičiai', 'alkuras' ), 'panel' => 'alkuras_panel' ) );
	for ( $i = 1; $i <= 4; $i++ ) {
		/* translators: %d: eilės numeris */
		alkuras_customize_add( $wp_customize, "alkuras_stat{$i}_n", 'alkuras_stats', sprintf( __( '%d — skaičius', 'alkuras' ), $i ), 'number' );
		/* translators: %d: eilės numeris */
		alkuras_customize_add( $wp_customize, "alkuras_stat{$i}_suffix", 'alkuras_stats', sprintf( __( '%d — priesaga', 'alkuras' ), $i ), 'text', array( 'description' => __( 'Pvz. „ val.“ arba „ %“.', 'alkuras' ) ) );
		/* translators: %d: eilės numeris */
		alkuras_customize_add( $wp_customize, "alkuras_stat{$i}_l", 'alkuras_stats', sprintf( __( '%d — paaiškinimas', 'alkuras' ), $i ) );
	}

	/* ---------------- Veiklos ---------------- */
	$wp_customize->add_section( 'alkuras_acts', array( 'title' => __( 'Pradinis — trys veiklos', 'alkuras' ), 'panel' => 'alkuras_panel', 'description' => __( 'Trys panelės su video, kuris paleidžiamas užvedus pelę.', 'alkuras' ) ) );
	for ( $i = 1; $i <= 3; $i++ ) {
		/* translators: %d: panelės numeris */
		$p = sprintf( __( '%d panelė — ', 'alkuras' ), $i );
		alkuras_customize_add( $wp_customize, "alkuras_act{$i}_idx", 'alkuras_acts', $p . __( 'numeris', 'alkuras' ), 'text', array( 'description' => __( 'Pvz. „01 / Gavyba“', 'alkuras' ) ) );
		alkuras_customize_add( $wp_customize, "alkuras_act{$i}_title", 'alkuras_acts', $p . __( 'pavadinimas', 'alkuras' ) );
		alkuras_customize_add( $wp_customize, "alkuras_act{$i}_text", 'alkuras_acts', $p . __( 'aprašymas', 'alkuras' ), 'textarea' );
		alkuras_customize_add( $wp_customize, "alkuras_act{$i}_cta", 'alkuras_acts', $p . __( 'nuorodos tekstas', 'alkuras' ) );
		alkuras_customize_add( $wp_customize, "alkuras_act{$i}_url", 'alkuras_acts', $p . __( 'nuoroda', 'alkuras' ), 'url' );
		alkuras_customize_add( $wp_customize, "alkuras_act{$i}_img", 'alkuras_acts', $p . __( 'nuotrauka', 'alkuras' ), 'image' );
		alkuras_customize_add( $wp_customize, "alkuras_act{$i}_video", 'alkuras_acts', $p . __( 'video (užvedus)', 'alkuras' ), 'video' );
		alkuras_customize_add( $wp_customize, "alkuras_act{$i}_color", 'alkuras_acts', $p . __( 'akcento spalva', 'alkuras' ), 'color', array( 'default' => '#D8F250' ) );
	}

	/* ---------------- Sekcijos ---------------- */
	$wp_customize->add_section( 'alkuras_sections', array( 'title' => __( 'Pradinis — sekcijos', 'alkuras' ), 'panel' => 'alkuras_panel' ) );

	$toggles = array(
		'intro'   => __( 'Įvadas („Kas mes esame“)', 'alkuras' ),
		'acts'    => __( 'Trys veiklos', 'alkuras' ),
		'vtext'   => __( 'Video per tekstą', 'alkuras' ),
		'shop'    => __( 'Produkcijos juosta', 'alkuras' ),
		'deliv'   => __( 'Pristatymas pagal lokaciją', 'alkuras' ),
		'steps'   => __( 'Užsakymo eiga (4 žingsniai)', 'alkuras' ),
		'calc'    => __( 'Skaičiuoklės', 'alkuras' ),
		'folio'   => __( 'Projektai', 'alkuras' ),
		'mach'    => __( 'Technikos parkas', 'alkuras' ),
		'figs'    => __( 'Kodėl Alkuras (skaičiai)', 'alkuras' ),
		'quotes'  => __( 'Atsiliepimai', 'alkuras' ),
		'cta'     => __( 'Užklausos forma', 'alkuras' ),
	);
	foreach ( $toggles as $key => $label ) {
		alkuras_customize_add( $wp_customize, "alkuras_show_{$key}", 'alkuras_sections', $label, 'checkbox', array( 'default' => true ) );
	}

	alkuras_customize_add( $wp_customize, 'alkuras_intro_title', 'alkuras_sections', __( 'Įvado antraštė', 'alkuras' ), 'textarea', array( 'default' => 'Sunkieji darbai, kuriuos galima užsisakyti taip pat paprastai, kaip parduotuvėje.' ) );
	alkuras_customize_add( $wp_customize, 'alkuras_intro_text', 'alkuras_sections', __( 'Įvado tekstas', 'alkuras' ), 'textarea' );

	alkuras_customize_add( $wp_customize, 'alkuras_vtext_title', 'alkuras_sections', __( 'Video-teksto antraštė', 'alkuras' ), 'textarea', array( 'description' => __( 'Didelės raidės, pro kurias matosi video.', 'alkuras' ), 'default' => "INERTINĖS\nMEDŽIAGOS" ) );
	alkuras_customize_add( $wp_customize, 'alkuras_vtext_video', 'alkuras_sections', __( 'Video-teksto video', 'alkuras' ), 'video' );
	alkuras_customize_add( $wp_customize, 'alkuras_vtext_cap', 'alkuras_sections', __( 'Video-teksto parašas', 'alkuras' ) );

	alkuras_customize_add( $wp_customize, 'alkuras_deliv_video', 'alkuras_sections', __( 'Pristatymo sekcijos video', 'alkuras' ), 'video' );
	alkuras_customize_add( $wp_customize, 'alkuras_mach_video', 'alkuras_sections', __( 'Technikos sekcijos video', 'alkuras' ), 'video' );
	alkuras_customize_add( $wp_customize, 'alkuras_figs_video', 'alkuras_sections', __( '„Kodėl Alkuras“ video', 'alkuras' ), 'video' );
	alkuras_customize_add( $wp_customize, 'alkuras_cta_video', 'alkuras_sections', __( 'Užklausos sekcijos video', 'alkuras' ), 'video' );
	alkuras_customize_add( $wp_customize, 'alkuras_calc_demo_video', 'alkuras_sections', __( 'Griovimo skaičiuoklės video', 'alkuras' ), 'video' );
	alkuras_customize_add( $wp_customize, 'alkuras_calc_mat_video', 'alkuras_sections', __( 'Medžiagų skaičiuoklės video', 'alkuras' ), 'video' );

	alkuras_customize_add( $wp_customize, 'alkuras_cta_shortcode', 'alkuras_sections', __( 'Užklausos formos trumpasis kodas', 'alkuras' ), 'text', array( 'description' => __( 'Pvz. Contact Form 7 kodas. Palikus tuščią rodoma paprasta forma, siunčianti laišką į aukščiau nurodytą el. paštą.', 'alkuras' ) ) );

	/* ---------------- Puslapis ---------------- */
	$wp_customize->add_section( 'alkuras_misc', array( 'title' => __( 'Kita', 'alkuras' ), 'panel' => 'alkuras_panel' ) );
	$wp_customize->add_setting( 'alkuras_contact_page', array( 'sanitize_callback' => 'absint' ) );
	$wp_customize->add_control(
		'alkuras_contact_page',
		array(
			'label'      => __( 'Kontaktų puslapis', 'alkuras' ),
			'section'    => 'alkuras_misc',
			'type'       => 'dropdown-pages',
			'description'=> __( 'Į jį veda skaičiuoklių mygtukai.', 'alkuras' ),
		)
	);
	alkuras_customize_add( $wp_customize, 'alkuras_credits', 'alkuras_misc', __( 'Poraštės kreditai', 'alkuras' ), 'textarea', array( 'default' => 'Dizainas ir kūrimas — <a href="https://www.simplex.lt">Simplex.lt</a>' ) );
}
add_action( 'customize_register', 'alkuras_customize_register' );
