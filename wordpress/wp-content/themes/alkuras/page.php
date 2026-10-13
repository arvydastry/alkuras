<?php
/**
 * Puslapis.
 *
 * @package Alkuras
 */

defined( 'ABSPATH' ) || exit;

get_header();
alkuras_page_header();

while ( have_posts() ) :
	the_post();
	$alk_content = trim( get_the_content() );
	?>
	<?php if ( $alk_content ) : ?>
		<section class="section">
			<div class="wrap alk-content">
				<?php the_content(); ?>
			</div>
		</section>
	<?php endif; ?>
	<?php
endwhile;

if ( is_page( 'kontaktai' ) || ( alkuras_contact_url() === get_permalink() ) ) :
	?>
	<section class="section pxs on-dark" id="uzklausa">
		<?php alkuras_media_bg( alkuras_info( 'cta_video' ), '', 'pxs__bg', '.14' ); ?>
		<div class="pxs__scrim"></div>
		<div class="grain"></div>
		<div class="pxs__in wrap">
			<div class="grid" style="grid-template-columns:1fr 1.15fr;gap:clamp(2rem,5vw,5rem);align-items:start">
				<div>
					<p class="eyebrow rv-fade" data-rv style="color:var(--signal)"><?php esc_html_e( 'Užklausa', 'alkuras' ); ?></p>
					<h2 class="t-h2 rv-up" data-rv style="margin-top:1rem"><?php esc_html_e( 'Vienas atsakingas kontaktas', 'alkuras' ); ?></h2>
					<dl style="margin-top:2.2rem">
						<?php if ( alkuras_info( 'phone' ) ) : ?>
							<div class="deliv__row"><dt><?php esc_html_e( 'Telefonas', 'alkuras' ); ?></dt><dd><a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', alkuras_info( 'phone' ) ) ); ?>"><?php echo esc_html( alkuras_info( 'phone' ) ); ?></a></dd></div>
						<?php endif; ?>
						<?php if ( alkuras_info( 'email' ) ) : ?>
							<div class="deliv__row"><dt><?php esc_html_e( 'El. paštas', 'alkuras' ); ?></dt><dd><a href="mailto:<?php echo esc_attr( alkuras_info( 'email' ) ); ?>"><?php echo esc_html( alkuras_info( 'email' ) ); ?></a></dd></div>
						<?php endif; ?>
						<?php if ( alkuras_info( 'address' ) ) : ?>
							<div class="deliv__row"><dt><?php esc_html_e( 'Biuras', 'alkuras' ); ?></dt><dd><?php echo esc_html( alkuras_info( 'address' ) ); ?></dd></div>
						<?php endif; ?>
						<?php if ( alkuras_info( 'address2' ) ) : ?>
							<div class="deliv__row"><dt><?php esc_html_e( 'Registracijos adresas', 'alkuras' ); ?></dt><dd><?php echo esc_html( alkuras_info( 'address2' ) ); ?></dd></div>
						<?php endif; ?>
					</dl>
				</div>
				<?php alkuras_contact_form(); ?>
			</div>
		</div>
	</section>
	<?php
endif;

get_footer();
