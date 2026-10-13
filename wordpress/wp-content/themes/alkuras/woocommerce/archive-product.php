<?php
/**
 * Produkcijos katalogas.
 *
 * @package Alkuras
 */

defined( 'ABSPATH' ) || exit;

get_header();
alkuras_shop_header();
?>

<section class="section">
	<div class="wrap">
		<?php alkuras_shop_filter(); ?>

		<?php if ( woocommerce_product_loop() && have_posts() ) : ?>
			<div class="grid g-4" data-stagger="70">
				<?php
				while ( have_posts() ) {
					the_post();
					global $product;
					echo alkuras_product_card( $product ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
				?>
			</div>

			<div style="margin-top:clamp(2.5rem,5vw,4rem)">
				<?php woocommerce_pagination(); ?>
			</div>
		<?php else : ?>
			<p class="lead"><?php esc_html_e( 'Šioje kategorijoje prekių kol kas nėra.', 'alkuras' ); ?></p>
		<?php endif; ?>
	</div>
</section>

<section class="section" style="background:var(--bone-2)">
	<div class="wrap">
		<div style="max-width:46ch;margin-bottom:clamp(2rem,4vw,3rem)">
			<p class="eyebrow rv-fade" data-rv><?php esc_html_e( 'Nežinote kiekio?', 'alkuras' ); ?></p>
			<h2 class="t-h2 rv-up" data-rv style="margin-top:1rem"><?php esc_html_e( 'Suskaičiuokite pagal plotą ir sluoksnio storį', 'alkuras' ); ?></h2>
		</div>
		<?php echo do_shortcode( '[alkuras_skaiciuokle tipas="medziagos"]' ); ?>
	</div>
</section>

<section class="section pxs on-dark">
	<?php alkuras_media_bg( alkuras_info( 'deliv_video' ), '', 'pxs__bg', '.16' ); ?>
	<div class="pxs__scrim"></div>
	<div class="pxs__in wrap">
		<div class="grid" style="grid-template-columns:1fr 1.45fr;gap:clamp(2rem,5vw,5rem);align-items:center">
			<div>
				<p class="eyebrow rv-fade" data-rv style="color:var(--signal)"><?php esc_html_e( 'Pristatymas', 'alkuras' ); ?></p>
				<h2 class="t-h2 rv-up" data-rv style="margin-top:1rem"><?php esc_html_e( 'Kiek kainuos atvežti?', 'alkuras' ); ?></h2>
			</div>
			<?php echo do_shortcode( '[alkuras_pristatymas]' ); ?>
		</div>
	</div>
</section>

<?php
get_footer();
