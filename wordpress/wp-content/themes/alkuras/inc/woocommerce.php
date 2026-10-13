<?php
/**
 * WooCommerce integracija.
 *
 * @package Alkuras
 */

defined( 'ABSPATH' ) || exit;

/**
 * Palaikymas.
 */
function alkuras_woocommerce_support() {
	add_theme_support(
		'woocommerce',
		array(
			'thumbnail_image_width' => 900,
			'single_image_width'    => 1400,
			'product_grid'          => array(
				'default_rows'    => 3,
				'default_columns' => 4,
			),
		)
	);
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );
}
add_action( 'after_setup_theme', 'alkuras_woocommerce_support' );

/**
 * Nenaudojame WooCommerce stilių — temoje viskas savo.
 *
 * @param array $styles Stiliai.
 * @return array
 */
function alkuras_wc_styles( $styles ) {
	unset( $styles['woocommerce-general'] );
	return $styles;
}
add_filter( 'woocommerce_enqueue_styles', 'alkuras_wc_styles' );

/**
 * Pašalinami numatytieji apvalkalai ir elementai.
 */
function alkuras_wc_cleanup() {
	remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
	remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
	remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
	remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );
	remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
	remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
	remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
}
add_action( 'init', 'alkuras_wc_cleanup' );

/**
 * Prekių kiekis puslapyje.
 *
 * @return int
 */
function alkuras_wc_per_page() {
	return 24;
}
add_filter( 'loop_shop_per_page', 'alkuras_wc_per_page', 20 );

/**
 * Krepšelio nuoroda po pridėjimo nereikalinga — turime šoninį skydelį.
 *
 * @return string
 */
function alkuras_wc_hide_added_message() {
	return '';
}
add_filter( 'wc_add_to_cart_message_html', 'alkuras_wc_hide_added_message' );

/**
 * Parduotuvės puslapio antraštė.
 */
function alkuras_shop_header() {
	$title    = woocommerce_page_title( false );
	$shop_id  = wc_get_page_id( 'shop' );
	$video    = $shop_id ? alk_meta( $shop_id, '_alk_hero_video' ) : '';
	$poster   = $shop_id ? alk_meta( $shop_id, '_alk_hero_poster' ) : '';
	$eyebrow  = $shop_id ? alk_meta( $shop_id, '_alk_hero_eyebrow' ) : '';
	$text     = $shop_id ? alk_meta( $shop_id, '_alk_hero_text' ) : '';

	if ( ! $text ) {
		$text = __( 'Kaina nurodyta už toną su PVM. Pristatymo įkainis apskaičiuojamas krepšelyje pagal jūsų adresą.', 'alkuras' );
	}
	?>
	<section class="phead" style="min-height:52vh">
		<?php alkuras_media_bg( $video, $poster, 'phead__media', '.2' ); ?>
		<div class="phead__scrim"></div>
		<div class="grain"></div>
		<div class="phead__in wrap">
			<p class="crumbs">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Pradžia', 'alkuras' ); ?></a> / <?php echo esc_html( $title ); ?>
			</p>
			<h1 class="t-h1 display" data-mx="12" style="max-width:14ch"><?php alkuras_lines( $title ); ?></h1>
			<p class="lead rv-up" data-rv style="margin-top:1.4rem;max-width:52ch;color:rgba(239,235,227,.78)"><?php echo esc_html( $text ); ?></p>
		</div>
	</section>
	<?php
}

/**
 * Kategorijų filtras kaip prototipe.
 */
function alkuras_shop_filter() {
	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => true,
		)
	);
	if ( is_wp_error( $terms ) || ! $terms ) {
		return;
	}

	$current = is_product_category() ? get_queried_object()->slug : '*';
	$shop    = wc_get_page_permalink( 'shop' );
	?>
	<div style="display:flex;flex-wrap:wrap;gap:1.5rem;justify-content:space-between;align-items:center;margin-bottom:clamp(2rem,4vw,3rem)">
		<div class="seg">
			<a class="<?php echo ( '*' === $current ) ? 'on' : ''; ?>" href="<?php echo esc_url( $shop ); ?>"><?php esc_html_e( 'Visos', 'alkuras' ); ?></a>
			<?php foreach ( $terms as $t ) : ?>
				<a class="<?php echo ( $current === $t->slug ) ? 'on' : ''; ?>" href="<?php echo esc_url( get_term_link( $t ) ); ?>"><?php echo esc_html( $t->name ); ?></a>
			<?php endforeach; ?>
		</div>
		<p class="eyebrow eyebrow--plain">
			<?php
			$count = wp_count_posts( 'product' );
			/* translators: 1: prekių skaičius, 2: karjerų skaičius */
			printf( esc_html__( '%1$d frakcijos · %2$d lokacijos', 'alkuras' ), (int) $count->publish, count( alk_get_quarries() ) );
			?>
		</p>
	</div>
	<?php
}

/**
 * Prekės specifikacijų lentelė po aprašymu.
 */
function alkuras_single_specs() {
	global $product;
	if ( ! $product ) {
		return;
	}
	$rows = alkuras_product_specs( $product->get_id() );
	if ( ! $rows ) {
		return;
	}
	echo '<dl style="margin-top:2.6rem">';
	foreach ( $rows as $k => $v ) {
		echo '<div class="deliv__row"><dt>' . esc_html( $k ) . '</dt><dd>' . esc_html( $v ) . '</dd></div>';
	}
	echo '</dl>';
}

/**
 * Krepšelio eilutės pavadinime rodom frakciją.
 *
 * @param string $name Pavadinimas.
 * @param array  $item Elementas.
 * @return string
 */
function alkuras_cart_item_name( $name, $item ) {
	if ( empty( $item['product_id'] ) ) {
		return $name;
	}
	$frac = alk_meta( $item['product_id'], '_alk_frakcija' );
	return $frac ? $name . ' <span class="muted">' . esc_html( $frac ) . '</span>' : $name;
}
add_filter( 'woocommerce_cart_item_name', 'alkuras_cart_item_name', 10, 2 );

/**
 * Kiekio stulpelio antraštė krepšelyje — tonos.
 *
 * @param string $label Etiketė.
 * @return string
 */
function alkuras_cart_qty_label( $label ) {
	return __( 'Kiekis, t', 'alkuras' );
}
add_filter( 'woocommerce_cart_totals_order_total_html', 'alkuras_cart_qty_label_noop' );

/**
 * Tuščias filtras (paliktas suderinamumui).
 *
 * @param string $html HTML.
 * @return string
 */
function alkuras_cart_qty_label_noop( $html ) {
	return $html;
}

/**
 * „Apmokėti“ mygtuko tekstas.
 *
 * @return string
 */
function alkuras_checkout_button_text() {
	return __( 'Pereiti prie apmokėjimo', 'alkuras' );
}
add_filter( 'woocommerce_order_button_text', 'alkuras_checkout_button_text' );
