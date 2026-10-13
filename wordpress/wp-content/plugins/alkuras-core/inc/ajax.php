<?php
/**
 * AJAX: pristatymo skaičiavimas ir įdėjimas į krepšelį iš skaičiuoklės.
 *
 * @package Alkuras
 */

defined( 'ABSPATH' ) || exit;

/**
 * Pristatymo kaina pagal miestą ir kiekį.
 */
function alkuras_ajax_delivery() {
	check_ajax_referer( 'alkuras_front', 'nonce' );

	$city = isset( $_POST['city'] ) ? sanitize_text_field( wp_unslash( $_POST['city'] ) ) : '';
	$tons = isset( $_POST['tons'] ) ? (float) wp_unslash( $_POST['tons'] ) : 0;

	$quote = alkuras_calc_delivery( $city, $tons );
	if ( ! $quote ) {
		wp_send_json_error( array( 'message' => __( 'Šiam miestui pristatymo įkainis dar nenustatytas — susisiekite su mumis.', 'alkuras' ) ) );
	}

	wp_send_json_success(
		array(
			'city'       => $quote['city'],
			'km'         => $quote['km'],
			'quarry'     => $quote['quarry']['name'] ?? '',
			'quarrySlug' => $quote['quarry']['id'] ?? '',
			'loads'      => $quote['loads'],
			'price'      => round( $quote['price'], 2 ),
			'priceHtml'  => alk_price( $quote['price'] ),
		)
	);
}
add_action( 'wp_ajax_alkuras_delivery', 'alkuras_ajax_delivery' );
add_action( 'wp_ajax_nopriv_alkuras_delivery', 'alkuras_ajax_delivery' );

/**
 * Įdeda prekę į krepšelį ir grąžina atnaujintą krepšelio būseną.
 */
function alkuras_ajax_add_to_cart() {
	check_ajax_referer( 'alkuras_front', 'nonce' );

	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		wp_send_json_error( array( 'message' => __( 'Krepšelis nepasiekiamas.', 'alkuras' ) ) );
	}

	$product_id = isset( $_POST['product_id'] ) ? absint( wp_unslash( $_POST['product_id'] ) ) : 0;
	$qty        = isset( $_POST['qty'] ) ? max( 1, (int) wp_unslash( $_POST['qty'] ) ) : 1;

	$product = wc_get_product( $product_id );
	if ( ! $product ) {
		wp_send_json_error( array( 'message' => __( 'Prekė nerasta.', 'alkuras' ) ) );
	}

	$added = WC()->cart->add_to_cart( $product_id, $qty );
	if ( ! $added ) {
		wp_send_json_error( array( 'message' => __( 'Nepavyko įdėti į krepšelį.', 'alkuras' ) ) );
	}

	WC()->cart->calculate_totals();

	wp_send_json_success(
		array(
			'name'     => $product->get_name(),
			'frac'     => alk_meta( $product_id, '_alk_frakcija' ),
			'qty'      => $qty,
			'count'    => WC()->cart->get_cart_contents_count(),
			'total'    => wp_strip_all_tags( WC()->cart->get_cart_subtotal() ),
			'fragments'=> alkuras_cart_fragments( array() ),
		)
	);
}
add_action( 'wp_ajax_alkuras_add_to_cart', 'alkuras_ajax_add_to_cart' );
add_action( 'wp_ajax_nopriv_alkuras_add_to_cart', 'alkuras_ajax_add_to_cart' );

/**
 * Krepšelio fragmentai — antraštės skaitiklis ir šoninis skydelis.
 *
 * @param array $fragments Fragmentai.
 * @return array
 */
function alkuras_cart_fragments( $fragments ) {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return $fragments;
	}

	$count = WC()->cart->get_cart_contents_count();

	ob_start();
	?>
	<span class="hdr__badge<?php echo $count ? ' on' : ''; ?>" data-cart-count><?php echo esc_html( (string) $count ); ?></span>
	<?php
	$fragments['[data-cart-count]'] = ob_get_clean();

	ob_start();
	alkuras_render_cart_items();
	$fragments['[data-cart-items]'] = '<div class="drawer__body" data-cart-items>' . ob_get_clean() . '</div>';

	ob_start();
	?>
	<b class="num" style="font-size:1.4rem" data-cart-total><?php echo wp_kses_post( WC()->cart->get_cart_subtotal() ); ?></b>
	<?php
	$fragments['[data-cart-total]'] = ob_get_clean();

	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'alkuras_cart_fragments' );

/**
 * Krepšelio eilutės šoniniame skydelyje.
 */
function alkuras_render_cart_items() {
	if ( ! function_exists( 'WC' ) || ! WC()->cart || WC()->cart->is_empty() ) {
		echo '<p class="muted" style="padding:2.5rem 0">' . esc_html__( 'Krepšelis tuščias. Pasirinkite medžiagas kataloge arba pasinaudokite kiekio skaičiuokle.', 'alkuras' ) . '</p>';
		return;
	}

	foreach ( WC()->cart->get_cart() as $key => $item ) {
		$product = $item['data'];
		if ( ! $product instanceof WC_Product ) {
			continue;
		}
		$id   = $product->get_id();
		$unit = alk_meta( $id, '_alk_vnt', 't' );
		$img  = get_the_post_thumbnail_url( $id, 'thumbnail' );
		?>
		<div class="citem">
			<img src="<?php echo esc_url( $img ? $img : wc_placeholder_img_src( 'thumbnail' ) ); ?>" alt="<?php echo esc_attr( $product->get_name() ); ?>">
			<div>
				<div class="citem__n"><?php echo esc_html( $product->get_name() ); ?></div>
				<div class="citem__m"><?php echo esc_html( alk_meta( $id, '_alk_frakcija' ) ); ?> · <?php echo wp_kses_post( wc_price( wc_get_price_including_tax( $product ) ) ); ?> / <?php echo esc_html( $unit ); ?></div>
				<div class="qty" style="margin-top:.5rem">
					<button type="button" data-alk-q="-" data-key="<?php echo esc_attr( $key ); ?>" aria-label="<?php esc_attr_e( 'Mažinti', 'alkuras' ); ?>">&ndash;</button>
					<span><?php echo esc_html( $item['quantity'] . ' ' . $unit ); ?></span>
					<button type="button" data-alk-q="+" data-key="<?php echo esc_attr( $key ); ?>" aria-label="<?php esc_attr_e( 'Didinti', 'alkuras' ); ?>">+</button>
				</div>
			</div>
			<div style="text-align:right">
				<div class="num" style="font-weight:600"><?php echo wp_kses_post( WC()->cart->get_product_subtotal( $product, $item['quantity'] ) ); ?></div>
				<button type="button" class="ulink" data-alk-rm="<?php echo esc_attr( $key ); ?>" style="margin-top:.6rem;font-size:10px"><?php esc_html_e( 'Šalinti', 'alkuras' ); ?></button>
			</div>
		</div>
		<?php
	}
}

/**
 * Kiekio keitimas ir šalinimas šoniniame skydelyje.
 */
function alkuras_ajax_update_cart() {
	check_ajax_referer( 'alkuras_front', 'nonce' );

	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		wp_send_json_error();
	}

	$key    = isset( $_POST['key'] ) ? sanitize_text_field( wp_unslash( $_POST['key'] ) ) : '';
	$action = isset( $_POST['op'] ) ? sanitize_key( wp_unslash( $_POST['op'] ) ) : '';
	$item   = WC()->cart->get_cart_item( $key );

	if ( ! $item ) {
		wp_send_json_error();
	}

	if ( 'remove' === $action ) {
		WC()->cart->remove_cart_item( $key );
	} else {
		$qty = (int) $item['quantity'] + ( '+' === $action ? 1 : -1 );
		WC()->cart->set_quantity( $key, max( 0, $qty ), true );
	}

	WC()->cart->calculate_totals();
	wp_send_json_success( array( 'fragments' => alkuras_cart_fragments( array() ) ) );
}
add_action( 'wp_ajax_alkuras_update_cart', 'alkuras_ajax_update_cart' );
add_action( 'wp_ajax_nopriv_alkuras_update_cart', 'alkuras_ajax_update_cart' );
