<?php
/**
 * Vienas įrašas (taip pat karjeras ir projektas).
 *
 * @package Alkuras
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$alk_id   = get_the_ID();
	$alk_type = get_post_type();
	?>
	<section class="phead">
		<?php
		$alk_poster = has_post_thumbnail() ? get_the_post_thumbnail_url( $alk_id, 'alkuras-hero' ) : '';
		alkuras_media_bg( alk_meta( $alk_id, '_alk_hero_video' ), $alk_poster, 'phead__media', '.2' );
		?>
		<div class="phead__scrim"></div>
		<div class="dust" data-n="16"></div>
		<div class="grain"></div>
		<div class="phead__in wrap">
			<p class="crumbs">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Pradžia', 'alkuras' ); ?></a>
				<?php if ( 'alk_karjeras' === $alk_type ) : ?>
					/ <?php esc_html_e( 'Karjerai', 'alkuras' ); ?>
				<?php elseif ( 'alk_projektas' === $alk_type ) : ?>
					/ <?php esc_html_e( 'Projektai', 'alkuras' ); ?>
				<?php endif; ?>
			</p>
			<h1 class="t-h1 display" data-mx="12" style="max-width:15ch"><?php alkuras_lines( get_the_title() ); ?></h1>
			<?php if ( 'alk_karjeras' === $alk_type && alk_meta( $alk_id, '_alk_medziagos' ) ) : ?>
				<p class="lead rv-up" data-rv style="margin-top:1.4rem;color:rgba(239,235,227,.78)">
					<?php echo esc_html( alk_meta( $alk_id, '_alk_medziagos' ) ); ?>
				</p>
			<?php endif; ?>
		</div>
	</section>

	<section class="section">
		<div class="wrap">
			<div class="grid" style="grid-template-columns:1.4fr 1fr;gap:clamp(2rem,5vw,4.5rem);align-items:start">
				<div class="alk-content"><?php the_content(); ?></div>

				<aside>
					<dl>
						<?php if ( 'alk_karjeras' === $alk_type ) : ?>
							<?php
							$alk_rows = array(
								__( 'Rajonas', 'alkuras' )      => alk_meta( $alk_id, '_alk_regionas' ),
								__( 'Adresas', 'alkuras' )      => alk_meta( $alk_id, '_alk_adresas' ),
								__( 'Medžiagos', 'alkuras' )    => alk_meta( $alk_id, '_alk_medziagos' ),
								__( 'Darbo laikas', 'alkuras' ) => alk_meta( $alk_id, '_alk_darbo_laikas' ),
								__( 'Krovimas', 'alkuras' )     => alk_meta( $alk_id, '_alk_krovimas' ),
							);
							?>
						<?php elseif ( 'alk_projektas' === $alk_type ) : ?>
							<?php
							$alk_terms = get_the_terms( $alk_id, 'alk_proj_kat' );
							$alk_rows  = array(
								__( 'Kategorija', 'alkuras' ) => ( $alk_terms && ! is_wp_error( $alk_terms ) ) ? $alk_terms[0]->name : '',
								__( 'Vieta', 'alkuras' )      => alk_meta( $alk_id, '_alk_vieta' ),
								__( 'Metai', 'alkuras' )      => alk_meta( $alk_id, '_alk_metai' ),
								__( 'Apimtis', 'alkuras' )    => implode( ' · ', array_filter( array( alk_meta( $alk_id, '_alk_faktas1' ), alk_meta( $alk_id, '_alk_faktas2' ), alk_meta( $alk_id, '_alk_faktas3' ) ) ) ),
							);
							?>
						<?php else : ?>
							<?php $alk_rows = array( __( 'Data', 'alkuras' ) => get_the_date() ); ?>
						<?php endif; ?>

						<?php foreach ( array_filter( $alk_rows ) as $alk_k => $alk_v ) : ?>
							<div class="deliv__row"><dt><?php echo esc_html( $alk_k ); ?></dt><dd><?php echo esc_html( $alk_v ); ?></dd></div>
						<?php endforeach; ?>
					</dl>

					<?php alkuras_button( __( 'Pateikti užklausą', 'alkuras' ), alkuras_contact_url(), 'solid', array( 'style' => 'margin-top:2rem' ) ); ?>
				</aside>
			</div>
		</div>
	</section>

	<?php if ( 'alk_karjeras' === $alk_type ) : ?>
		<section class="section pxs on-dark">
			<?php alkuras_media_bg( alkuras_info( 'deliv_video' ), '', 'pxs__bg', '.16' ); ?>
			<div class="pxs__scrim"></div>
			<div class="pxs__in wrap">
				<div class="grid" style="grid-template-columns:1fr 1.45fr;gap:clamp(2rem,5vw,5rem);align-items:center">
					<div>
						<p class="eyebrow rv-fade" data-rv style="color:var(--signal)"><?php esc_html_e( 'Pristatymas', 'alkuras' ); ?></p>
						<h2 class="t-h2 rv-up" data-rv style="margin-top:1rem"><?php esc_html_e( 'Patikrinkite kainą iki savo objekto', 'alkuras' ); ?></h2>
					</div>
					<?php echo do_shortcode( '[alkuras_pristatymas]' ); ?>
				</div>
			</div>
		</section>
	<?php endif; ?>
	<?php
endwhile;

get_footer();
