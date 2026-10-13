<?php
/**
 * Poraštė.
 *
 * @package Alkuras
 */

defined( 'ABSPATH' ) || exit;
?>
</main>

<footer class="ftr">
	<div class="wrap">
		<?php
		$big = get_bloginfo( 'name' );
		$big = str_replace( array( 'UAB ', '„', '“' ), '', $big );
		?>
		<div class="ftr__big" data-mx="10"><?php echo esc_html( function_exists( 'mb_strtoupper' ) ? mb_strtoupper( $big, 'UTF-8' ) : strtoupper( $big ) ); ?></div>

		<div class="ftr__grid">
			<div>
				<h4><?php esc_html_e( 'Įmonė', 'alkuras' ); ?></h4>
				<?php if ( alkuras_info( 'about' ) ) : ?>
					<p class="soft" style="max-width:38ch;font-size:.95rem"><?php echo wp_kses_post( alkuras_info( 'about' ) ); ?></p>
				<?php endif; ?>
				<?php if ( alkuras_info( 'reg' ) ) : ?>
					<p class="eyebrow eyebrow--plain" style="margin-top:1.4rem"><?php echo esc_html( alkuras_info( 'reg' ) ); ?></p>
				<?php endif; ?>
			</div>

			<div>
				<h4><?php esc_html_e( 'Veiklos', 'alkuras' ); ?></h4>
				<div class="ftr__list">
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'primary',
							'container'      => false,
							'items_wrap'     => '%3$s',
							'depth'          => 1,
							'walker'         => new Alkuras_Mobile_Walker(),
							'fallback_cb'    => '__return_empty_string',
						)
					);
					?>
				</div>
			</div>

			<div>
				<h4><?php esc_html_e( 'Karjerai', 'alkuras' ); ?></h4>
				<div class="ftr__list">
					<?php foreach ( alk_get_quarries() as $q ) : ?>
						<a href="<?php echo esc_url( $q['permalink'] ); ?>"><?php echo esc_html( str_replace( ' karjeras', '', $q['name'] ) ); ?></a>
					<?php endforeach; ?>
				</div>
			</div>

			<div>
				<h4><?php esc_html_e( 'Kontaktai', 'alkuras' ); ?></h4>
				<div class="ftr__list">
					<?php if ( alkuras_info( 'phone' ) ) : ?>
						<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', alkuras_info( 'phone' ) ) ); ?>"><?php echo esc_html( alkuras_info( 'phone' ) ); ?></a>
					<?php endif; ?>
					<?php if ( alkuras_info( 'email' ) ) : ?>
						<a href="mailto:<?php echo esc_attr( alkuras_info( 'email' ) ); ?>"><?php echo esc_html( alkuras_info( 'email' ) ); ?></a>
					<?php endif; ?>
					<?php if ( alkuras_info( 'address' ) ) : ?>
						<a href="<?php echo esc_url( alkuras_contact_url() ); ?>"><?php echo esc_html( alkuras_info( 'address' ) ); ?></a>
					<?php endif; ?>
					<?php if ( alkuras_info( 'address2' ) ) : ?>
						<a href="<?php echo esc_url( alkuras_contact_url() ); ?>"><?php echo esc_html( alkuras_info( 'address2' ) ); ?></a>
					<?php endif; ?>
				</div>
				<?php
				alkuras_button(
					__( 'Pateikti užklausą', 'alkuras' ),
					alkuras_contact_url(),
					'line',
					array( 'style' => 'margin-top:1.6rem;border-color:rgba(239,235,227,.25)', 'magnet' => '.22' )
				);
				?>
			</div>
		</div>

		<div class="ftr__bot">
			<span>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( alkuras_info( 'company', get_bloginfo( 'name' ) ) ); ?></span>
			<span><?php echo wp_kses_post( alkuras_info( 'credits' ) ); ?></span>
			<span class="ftr__mini">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'footer',
						'container'      => false,
						'items_wrap'     => '%3$s',
						'depth'          => 1,
						'walker'         => new Alkuras_Mobile_Walker(),
						'fallback_cb'    => '__return_empty_string',
					)
				);
				?>
			</span>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
