<?php
/**
 * Prekės puslapis.
 *
 * @package Alkuras
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	global $product;
	if ( ! $product ) {
		$product = wc_get_product( get_the_ID() );
	}

	$alk_id    = $product->get_id();
	$alk_frac  = alk_meta( $alk_id, '_alk_frakcija' );
	$alk_unit  = alk_meta( $alk_id, '_alk_vnt', 't' );
	$alk_price = (float) wc_get_price_including_tax( $product );
	$alk_main  = get_the_post_thumbnail_url( $alk_id, 'alkuras-folio' );
	$alk_gal   = $product->get_gallery_image_ids();
	$alk_alt   = $alk_gal ? wp_get_attachment_image_url( $alk_gal[0], 'alkuras-folio' ) : $alk_main;
	?>
	<section style="padding-top:clamp(7rem,12vw,10rem)">
		<div class="wrap">
			<p class="crumbs" style="color:var(--fg-mute)">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Pradžia', 'alkuras' ); ?></a> /
				<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'Produkcija', 'alkuras' ); ?></a> /
				<?php the_title(); ?>
			</p>

			<div class="grid" style="grid-template-columns:1.15fr 1fr;gap:clamp(2rem,5vw,4.5rem);align-items:start;margin-top:1.5rem">
				<div class="rv-curtain" data-rv>
					<?php if ( $alk_main ) : ?>
						<div class="exc" data-exc="150" style="aspect-ratio:4/3">
							<div class="exc__base"><img src="<?php echo esc_url( $alk_alt ); ?>" alt="<?php echo esc_attr( $product->get_name() ); ?>"></div>
							<div class="exc__over" style="background-image:url(<?php echo esc_url( $alk_main ); ?>)"></div>
							<div class="exc__ring"></div>
						</div>
						<p class="eyebrow" style="margin-top:1rem"><?php esc_html_e( 'Užveskite pelę — medžiaga atidengiama', 'alkuras' ); ?></p>
					<?php endif; ?>
				</div>

				<div class="summary entry-summary">
					<?php if ( $alk_frac ) : ?>
						<span class="chip chip--sig"><?php echo esc_html( $alk_frac ); ?></span>
					<?php endif; ?>

					<h1 class="t-h1 display rv-up" data-rv style="margin-top:1.2rem;font-size:clamp(2.2rem,4.6vw,3.6rem)"><?php the_title(); ?></h1>

					<?php if ( $product->get_short_description() ) : ?>
						<p class="lead rv-up" data-rv style="margin-top:1.2rem"><?php echo wp_kses_post( $product->get_short_description() ); ?></p>
					<?php endif; ?>

					<div style="display:flex;align-items:baseline;gap:.6rem;margin-top:2rem">
						<b class="num display" style="font-size:clamp(2rem,4vw,3rem)"><?php echo wp_kses_post( wc_price( $alk_price ) ); ?></b>
						<span class="eyebrow eyebrow--plain"><?php
							/* translators: %s: matavimo vienetas */
							printf( esc_html__( '/ %s su PVM', 'alkuras' ), esc_html( 't' === $alk_unit ? __( 'tona', 'alkuras' ) : $alk_unit ) );
						?></span>
					</div>

					<div style="margin-top:2rem">
						<div style="display:flex;justify-content:space-between;align-items:baseline">
							<span class="eyebrow eyebrow--plain"><?php esc_html_e( 'Kiekis', 'alkuras' ); ?></span>
							<b class="num" data-s-qty-l>10 <?php echo esc_html( $alk_unit ); ?></b>
						</div>
						<input class="range" type="range" min="1" max="120" step="1" value="10"
							data-s-qty data-price="<?php echo esc_attr( $alk_price ); ?>"
							aria-label="<?php esc_attr_e( 'Kiekis', 'alkuras' ); ?>">
						<div style="display:flex;justify-content:space-between;align-items:center;margin-top:1rem;padding-top:1rem;border-top:1px solid var(--line)">
							<span class="eyebrow eyebrow--plain"><?php esc_html_e( 'Medžiagų suma', 'alkuras' ); ?></span>
							<b class="num" style="font-size:1.3rem" data-s-sum></b>
						</div>
					</div>

					<div class="alk-cart-form" style="margin-top:1.8rem">
						<?php woocommerce_template_single_add_to_cart(); ?>
					</div>

					<?php alkuras_single_specs(); ?>

					<div class="chip" style="margin-top:1.6rem"><?php esc_html_e( 'Pristatymas skaičiuojamas krepšelyje pagal artimiausią karjerą', 'alkuras' ); ?></div>
				</div>
			</div>
		</div>
	</section>

	<?php if ( $product->get_description() ) : ?>
		<section class="section">
			<div class="wrap wrap--read alk-content"><?php echo wp_kses_post( wpautop( $product->get_description() ) ); ?></div>
		</section>
	<?php endif; ?>

	<section class="section pxs on-dark">
		<?php alkuras_media_bg( alkuras_info( 'deliv_video' ), '', 'pxs__bg', '.16' ); ?>
		<div class="pxs__scrim"></div>
		<div class="pxs__in wrap">
			<div class="grid" style="grid-template-columns:1fr 1.45fr;gap:clamp(2rem,5vw,5rem);align-items:center">
				<div>
					<p class="eyebrow rv-fade" data-rv style="color:var(--signal)"><?php esc_html_e( 'Logistika', 'alkuras' ); ?></p>
					<h2 class="t-h2 rv-up" data-rv style="margin-top:1rem"><?php esc_html_e( 'Pristatymo kaina iki jūsų objekto', 'alkuras' ); ?></h2>
				</div>
				<?php echo do_shortcode( '[alkuras_pristatymas]' ); ?>
			</div>
		</div>
	</section>

	<?php
	$alk_cats = wp_get_post_terms( $alk_id, 'product_cat', array( 'fields' => 'slugs' ) );
	if ( $alk_cats ) :
		?>
		<section class="section">
			<div class="wrap">
				<h2 class="t-h3 rv-up" data-rv style="margin-bottom:clamp(1.5rem,3vw,2.5rem)"><?php esc_html_e( 'Taip pat gali tikti', 'alkuras' ); ?></h2>
				<?php
				echo do_shortcode( '[alkuras_produktai kategorija="' . esc_attr( $alk_cats[0] ) . '" kiekis="4"]' );
				?>
			</div>
		</section>
	<?php endif; ?>

	<?php
endwhile;

get_footer();
