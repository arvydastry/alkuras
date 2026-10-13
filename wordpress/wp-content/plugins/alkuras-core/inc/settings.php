<?php
/**
 * Nustatymų puslapis: skaičiuoklių įkainiai ir pristatymo zonos.
 *
 * @package Alkuras
 */

defined( 'ABSPATH' ) || exit;

const ALKURAS_OPTION = 'alkuras_settings';

/**
 * Numatytieji nustatymai.
 *
 * @return array
 */
function alkuras_default_options() {
	return array(
		// Griovimo skaičiuoklė — įkainiai €/m².
		'demo_med'        => 9,
		'demo_mur'        => 14,
		'demo_gzb'        => 22,
		'demo_mis'        => 17,
		// Objekto tipo koeficientai.
		'demo_k_gyv'      => 1.00,
		'demo_k_ukio'     => 0.85,
		'demo_k_gam'      => 1.15,
		'demo_k_inz'      => 1.30,
		// Papildomi darbai.
		'demo_pamatai'    => 18,   // %.
		'demo_isvezimas'  => 4.20, // €/m².
		'demo_smulk'      => 10,   // % nuolaida.
		'demo_tempas'     => 160,  // m² per darbo dieną.
		'demo_nuokrypis'  => 13,   // ± %.
		// Pristatymas.
		'del_reisas'      => 28,   // € už reisą (pakrovimas).
		'del_km_t'        => 0.16, // €/km/t.
		'del_talpa'       => 25,   // t savivartyje.
		'del_min'         => 0,    // Minimali pristatymo kaina.
		// Zonos.
		'zones'           => "Kretinga|prudupio-karjeras|8\nPalanga|prudupio-karjeras|16\nKlaipėda|imbares-karjeras|34\nŠilutė|katyciu-karjeras|22\nPlungė|imbares-karjeras|41\nSkuodas|ruigiu-karjeras|38\nTelšiai|imbares-karjeras|63\nŠiauliai|ruigiu-karjeras|118\nTauragė|katyciu-karjeras|74\nKaunas|katyciu-karjeras|196\nVilnius|katyciu-karjeras|298",
	);
}

/**
 * Paima vieną nustatymą.
 *
 * @param string $key     Raktas.
 * @param mixed  $default Numatytoji reikšmė.
 * @return mixed
 */
function alkuras_get_option( $key, $default = null ) {
	$opts = get_option( ALKURAS_OPTION, array() );

	if ( isset( $opts[ $key ] ) && '' !== $opts[ $key ] ) {
		return $opts[ $key ];
	}

	$defaults = alkuras_default_options();
	if ( array_key_exists( $key, $defaults ) ) {
		return $defaults[ $key ];
	}

	return $default;
}

/**
 * Registruoja nustatymus.
 */
function alkuras_register_settings() {
	register_setting(
		'alkuras_settings_group',
		ALKURAS_OPTION,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'alkuras_sanitize_settings',
			'default'           => alkuras_default_options(),
		)
	);
}
add_action( 'admin_init', 'alkuras_register_settings' );

/**
 * Išvalo nustatymus.
 *
 * @param array $input Įvestis.
 * @return array
 */
function alkuras_sanitize_settings( $input ) {
	$out      = array();
	$defaults = alkuras_default_options();

	foreach ( $defaults as $key => $def ) {
		if ( ! isset( $input[ $key ] ) ) {
			$out[ $key ] = $def;
			continue;
		}
		if ( 'zones' === $key ) {
			$out[ $key ] = sanitize_textarea_field( $input[ $key ] );
		} else {
			$out[ $key ] = (float) str_replace( ',', '.', (string) $input[ $key ] );
		}
	}

	return $out;
}

/**
 * Nustatymų puslapis.
 */
function alkuras_settings_page_render() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$groups = array(
		array(
			'title'  => __( 'Griovimo kainos skaičiuoklė', 'alkuras' ),
			'desc'   => __( 'Bazinis įkainis už kvadratinį metrą pagal konstrukcijos tipą.', 'alkuras' ),
			'fields' => array(
				'demo_med'   => __( 'Medinė konstrukcija, €/m²', 'alkuras' ),
				'demo_mur'   => __( 'Mūrinė konstrukcija, €/m²', 'alkuras' ),
				'demo_gzb'   => __( 'Gelžbetonis, €/m²', 'alkuras' ),
				'demo_mis'   => __( 'Mišri konstrukcija, €/m²', 'alkuras' ),
			),
		),
		array(
			'title'  => __( 'Objekto tipo koeficientai', 'alkuras' ),
			'desc'   => __( 'Bazinis įkainis dauginamas iš šio skaičiaus. 1,00 = be pakeitimo.', 'alkuras' ),
			'fields' => array(
				'demo_k_gyv'  => __( 'Gyvenamasis namas', 'alkuras' ),
				'demo_k_ukio' => __( 'Ūkinis pastatas', 'alkuras' ),
				'demo_k_gam'  => __( 'Gamybinis pastatas', 'alkuras' ),
				'demo_k_inz'  => __( 'Inžinerinės konstrukcijos', 'alkuras' ),
			),
		),
		array(
			'title'  => __( 'Papildomi darbai ir tikslumas', 'alkuras' ),
			'fields' => array(
				'demo_pamatai'   => __( 'Pamatų ardymas, % nuo griovimo kainos', 'alkuras' ),
				'demo_isvezimas' => __( 'Atliekų išvežimas, €/m²', 'alkuras' ),
				'demo_smulk'     => __( 'Nuolaida smulkinant objekte, %', 'alkuras' ),
				'demo_tempas'    => __( 'Darbo tempas, m² per darbo dieną', 'alkuras' ),
				'demo_nuokrypis' => __( 'Rodomas nuokrypis, ± %', 'alkuras' ),
			),
		),
		array(
			'title'  => __( 'Pristatymas', 'alkuras' ),
			'desc'   => __( 'Naudojama ir krepšelyje, ir pristatymo skaičiuoklėje.', 'alkuras' ),
			'fields' => array(
				'del_reisas' => __( 'Pakrovimas ir reisas, € už reisą', 'alkuras' ),
				'del_km_t'   => __( 'Atstumo įkainis, € už km už toną', 'alkuras' ),
				'del_talpa'  => __( 'Savivarčio talpa, t', 'alkuras' ),
				'del_min'    => __( 'Minimali pristatymo kaina, €', 'alkuras' ),
			),
		),
	);
	?>
	<div class="wrap">
		<h1><?php echo esc_html__( 'Alkuras — nustatymai', 'alkuras' ); ?></h1>
		<p class="description" style="max-width:62em">
			<?php echo esc_html__( 'Čia keičiami skaičiuoklių įkainiai ir pristatymo tarifai. Pakeitimai iškart atsispindi svetainėje — kodo keisti nereikia.', 'alkuras' ); ?>
		</p>
		<form method="post" action="options.php">
			<?php settings_fields( 'alkuras_settings_group' ); ?>

			<?php foreach ( $groups as $g ) : ?>
				<h2><?php echo esc_html( $g['title'] ); ?></h2>
				<?php if ( ! empty( $g['desc'] ) ) : ?>
					<p class="description"><?php echo esc_html( $g['desc'] ); ?></p>
				<?php endif; ?>
				<table class="form-table" role="presentation"><tbody>
				<?php foreach ( $g['fields'] as $key => $label ) : ?>
					<tr>
						<th scope="row"><label for="alk_<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label></th>
						<td>
							<input type="number" step="0.01" class="small-text"
								id="alk_<?php echo esc_attr( $key ); ?>"
								name="<?php echo esc_attr( ALKURAS_OPTION ); ?>[<?php echo esc_attr( $key ); ?>]"
								value="<?php echo esc_attr( alkuras_get_option( $key ) ); ?>">
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody></table>
			<?php endforeach; ?>

			<h2><?php echo esc_html__( 'Pristatymo zonos', 'alkuras' ); ?></h2>
			<p class="description" style="max-width:62em">
				<?php echo esc_html__( 'Viena eilutė = vienas miestas. Formatas: Miestas | karjero nuoroda (slug) | atstumas km. Karjero slug matomas redaguojant karjerą (nuolatinė nuoroda).', 'alkuras' ); ?>
			</p>
			<textarea name="<?php echo esc_attr( ALKURAS_OPTION ); ?>[zones]" rows="14" class="large-text code"><?php echo esc_textarea( alkuras_get_option( 'zones' ) ); ?></textarea>

			<h2><?php echo esc_html__( 'Trumpieji kodai (shortcodes)', 'alkuras' ); ?></h2>
			<p class="description"><?php echo esc_html__( 'Šiuos kodus galima įdėti į bet kurį puslapį per bloką „Trumpasis kodas“.', 'alkuras' ); ?></p>
			<table class="widefat striped" style="max-width:62em">
				<tbody>
					<tr><td><code>[alkuras_skaiciuokle tipas="griovimas"]</code></td><td><?php echo esc_html__( 'Griovimo kainos skaičiuoklė', 'alkuras' ); ?></td></tr>
					<tr><td><code>[alkuras_skaiciuokle tipas="medziagos"]</code></td><td><?php echo esc_html__( 'Medžiagų kiekio skaičiuoklė', 'alkuras' ); ?></td></tr>
					<tr><td><code>[alkuras_pristatymas]</code></td><td><?php echo esc_html__( 'Pristatymo skaičiuoklė su karjerų žemėlapiu', 'alkuras' ); ?></td></tr>
					<tr><td><code>[alkuras_karjerai]</code></td><td><?php echo esc_html__( 'Karjerų sąrašas', 'alkuras' ); ?></td></tr>
					<tr><td><code>[alkuras_zemelapis]</code></td><td><?php echo esc_html__( 'Tik karjerų žemėlapis', 'alkuras' ); ?></td></tr>
					<tr><td><code>[alkuras_projektai kiekis="6"]</code></td><td><?php echo esc_html__( 'Projektų tinklelis', 'alkuras' ); ?></td></tr>
					<tr><td><code>[alkuras_technika]</code></td><td><?php echo esc_html__( 'Technikos parko sąrašas', 'alkuras' ); ?></td></tr>
					<tr><td><code>[alkuras_atsiliepimai]</code></td><td><?php echo esc_html__( 'Atsiliepimai', 'alkuras' ); ?></td></tr>
					<tr><td><code>[alkuras_produktai kategorija="zvyras" kiekis="4"]</code></td><td><?php echo esc_html__( 'Produkcijos kortelės', 'alkuras' ); ?></td></tr>
					<tr><td><code>[alkuras_veiklos]</code></td><td><?php echo esc_html__( 'Trys veiklų panelės su video', 'alkuras' ); ?></td></tr>
				</tbody>
			</table>

			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}
