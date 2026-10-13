<?php
/**
 * WooCommerce prekės laukai: frakcija, tankis, karjerai, panaudojimas.
 *
 * @package Alkuras
 */

defined( 'ABSPATH' ) || exit;

/**
 * Prekės duomenų kortelė.
 *
 * @param array $tabs Kortelės.
 * @return array
 */
function alkuras_product_data_tab( $tabs ) {
	$tabs['alkuras'] = array(
		'label'    => __( 'Alkuras', 'alkuras' ),
		'target'   => 'alkuras_product_data',
		'class'    => array(),
		'priority' => 21,
	);
	return $tabs;
}
add_filter( 'woocommerce_product_data_tabs', 'alkuras_product_data_tab' );

/**
 * Kortelės turinys.
 */
function alkuras_product_data_panel() {
	global $post;
	$quarries = alk_get_quarries();
	$selected = (array) get_post_meta( $post->ID, '_alk_karjerai', true );
	?>
	<div id="alkuras_product_data" class="panel woocommerce_options_panel">
		<div class="options_group">
			<?php
			woocommerce_wp_text_input(
				array(
					'id'          => '_alk_frakcija',
					'label'       => __( 'Frakcija', 'alkuras' ),
					'placeholder' => '0/16 mm',
					'desc_tip'    => true,
					'description' => __( 'Rodoma kortelėje ir prekės puslapyje.', 'alkuras' ),
				)
			);
			woocommerce_wp_text_input(
				array(
					'id'                => '_alk_tankis',
					'label'             => __( 'Tankis, t/m³', 'alkuras' ),
					'placeholder'       => '1.75',
					'type'              => 'number',
					'custom_attributes' => array( 'step' => '0.01', 'min' => '0' ),
					'desc_tip'          => true,
					'description'       => __( 'Būtinas medžiagų kiekio skaičiuoklei. Be jo prekė skaičiuoklėje nerodoma.', 'alkuras' ),
				)
			);
			woocommerce_wp_text_input(
				array(
					'id'          => '_alk_vnt',
					'label'       => __( 'Matavimo vienetas', 'alkuras' ),
					'placeholder' => 't',
					'desc_tip'    => true,
					'description' => __( 'Pvz. t (tona) arba m³.', 'alkuras' ),
				)
			);
			woocommerce_wp_text_input(
				array(
					'id'          => '_alk_zyma',
					'label'       => __( 'Žymė ant nuotraukos', 'alkuras' ),
					'placeholder' => __( 'Populiaru / Nauja / Perdirbta', 'alkuras' ),
					'desc_tip'    => true,
					'description' => __( 'Palikite tuščią, jei žymos nereikia.', 'alkuras' ),
				)
			);
			woocommerce_wp_textarea_input(
				array(
					'id'          => '_alk_panaudojimas',
					'label'       => __( 'Panaudojimas', 'alkuras' ),
					'placeholder' => __( 'Pagrindų įrengimas, Betonavimas, Aikštelės', 'alkuras' ),
					'desc_tip'    => true,
					'description' => __( 'Atskirkite kableliais.', 'alkuras' ),
				)
			);
			?>
		</div>

		<?php if ( $quarries ) : ?>
		<div class="options_group">
			<p class="form-field">
				<label><?php esc_html_e( 'Karjerai', 'alkuras' ); ?></label>
				<span class="wrap" style="display:inline-block">
					<?php foreach ( $quarries as $q ) : ?>
						<label style="display:block;margin:.2em 0;font-weight:400">
							<input type="checkbox" name="_alk_karjerai[]" value="<?php echo esc_attr( $q['id'] ); ?>"
								<?php checked( in_array( $q['id'], $selected, true ) ); ?>>
							<?php echo esc_html( $q['name'] ); ?>
						</label>
					<?php endforeach; ?>
				</span>
			</p>
			<p class="form-field"><span class="description"><?php esc_html_e( 'Iš kurių karjerų ši medžiaga pakraunama. Naudojama prekės puslapyje.', 'alkuras' ); ?></span></p>
		</div>
		<?php endif; ?>
	</div>
	<?php
}
add_action( 'woocommerce_product_data_panels', 'alkuras_product_data_panel' );

/**
 * Išsaugo prekės laukus.
 *
 * @param int $post_id Prekės ID.
 */
function alkuras_save_product_fields( $post_id ) {
	// WooCommerce jau patikrino nonce (woocommerce_meta_nonce) prieš iškviečiant šį veiksmą.
	// phpcs:disable WordPress.Security.NonceVerification.Missing
	$text = array( '_alk_frakcija', '_alk_vnt', '_alk_zyma' );
	foreach ( $text as $key ) {
		if ( isset( $_POST[ $key ] ) ) {
			update_post_meta( $post_id, $key, sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) );
		}
	}

	if ( isset( $_POST['_alk_tankis'] ) ) {
		update_post_meta( $post_id, '_alk_tankis', (float) str_replace( ',', '.', sanitize_text_field( wp_unslash( $_POST['_alk_tankis'] ) ) ) );
	}
	if ( isset( $_POST['_alk_panaudojimas'] ) ) {
		update_post_meta( $post_id, '_alk_panaudojimas', sanitize_textarea_field( wp_unslash( $_POST['_alk_panaudojimas'] ) ) );
	}

	$quarries = isset( $_POST['_alk_karjerai'] ) ? array_map( 'sanitize_key', (array) wp_unslash( $_POST['_alk_karjerai'] ) ) : array();
	update_post_meta( $post_id, '_alk_karjerai', $quarries );
	// phpcs:enable WordPress.Security.NonceVerification.Missing
}
add_action( 'woocommerce_process_product_meta', 'alkuras_save_product_fields' );

/**
 * Prekės specifikacijų lentelė viešoje dalyje.
 *
 * @param int $product_id Prekės ID.
 * @return array
 */
function alkuras_product_specs( $product_id ) {
	$rows = array();

	$frac = alk_meta( $product_id, '_alk_frakcija' );
	if ( $frac ) {
		$rows[ __( 'Frakcija', 'alkuras' ) ] = $frac;
	}

	$density = (float) alk_meta( $product_id, '_alk_tankis', 0 );
	if ( $density > 0 ) {
		$rows[ __( 'Tankis', 'alkuras' ) ] = number_format_i18n( $density, 2 ) . ' t/m³';
	}

	$unit = alk_meta( $product_id, '_alk_vnt', 't' );
	$rows[ __( 'Matavimo vnt.', 'alkuras' ) ] = $unit;

	$use = alk_meta( $product_id, '_alk_panaudojimas' );
	if ( $use ) {
		$rows[ __( 'Panaudojimas', 'alkuras' ) ] = $use;
	}

	$slugs = (array) get_post_meta( $product_id, '_alk_karjerai', true );
	$names = array();
	foreach ( $slugs as $slug ) {
		$q = alk_get_quarry( $slug );
		if ( $q ) {
			$names[] = str_replace( ' karjeras', '', $q['name'] );
		}
	}
	if ( $names ) {
		$rows[ __( 'Karjerai', 'alkuras' ) ] = implode( ', ', $names );
	}

	return $rows;
}

/**
 * Kiekio laukelio žingsnis — prekyba tonomis leidžia dalines reikšmes.
 *
 * @param array      $args    Argumentai.
 * @param WC_Product $product Prekė.
 * @return array
 */
function alkuras_quantity_args( $args, $product = null ) {
	if ( $product instanceof WC_Product && (float) alk_meta( $product->get_id(), '_alk_tankis', 0 ) > 0 ) {
		$args['step']  = 1;
		$args['min_value'] = 1;
		$args['max_value'] = 500;
	}
	return $args;
}
add_filter( 'woocommerce_quantity_input_args', 'alkuras_quantity_args', 10, 2 );

/**
 * Prie kainos prirašo matavimo vienetą.
 *
 * @param string     $price   Kainos HTML.
 * @param WC_Product $product Prekė.
 * @return string
 */
function alkuras_price_suffix( $price, $product ) {
	if ( is_admin() && ! wp_doing_ajax() ) {
		return $price;
	}
	$unit = alk_meta( $product->get_id(), '_alk_vnt', '' );
	if ( ! $unit ) {
		return $price;
	}
	return $price . ' <span class="alk-unit">/ ' . esc_html( $unit ) . '</span>';
}
add_filter( 'woocommerce_get_price_html', 'alkuras_price_suffix', 10, 2 );
