<?php
/**
 * Antraštė.
 *
 * @package Alkuras
 */

defined( 'ABSPATH' ) || exit;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip" href="#main"><?php esc_html_e( 'Pereiti prie turinio', 'alkuras' ); ?></a>
<div class="prog"></div>

<?php if ( is_front_page() ) : ?>
<div class="loader">
	<div class="loader__inner">
		<div class="loader__mark">
			<?php
			$mark = get_bloginfo( 'name' );
			$mark = $mark ? $mark : 'ALKURAS';
			$mark = function_exists( 'mb_strtoupper' ) ? mb_strtoupper( $mark, 'UTF-8' ) : strtoupper( $mark );
			$mark = str_replace( array( 'UAB ', '„', '“' ), '', $mark );
			$chars = preg_split( '//u', $mark, -1, PREG_SPLIT_NO_EMPTY );
			foreach ( $chars as $i => $ch ) {
				printf( '<span style="animation-delay:%ss">%s</span>', esc_attr( number_format( 0.02 + $i * 0.05, 2 ) ), esc_html( $ch ) );
			}
			?>
		</div>
		<div class="loader__bar"><i></i></div>
		<div class="loader__pct">000</div>
	</div>
</div>
<?php endif; ?>

<header class="hdr<?php echo alkuras_has_dark_header() ? ' hdr--over' : ''; ?>">
	<div class="hdr__in">
		<nav class="hdr__nav" aria-label="<?php esc_attr_e( 'Pagrindinė navigacija', 'alkuras' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'items_wrap'     => '%3$s',
					'depth'          => 1,
					'walker'         => new Alkuras_Nav_Walker(),
					'fallback_cb'    => '__return_empty_string',
				)
			);
			?>
		</nav>

		<?php if ( has_custom_logo() ) : ?>
			<div class="hdr__logo"><?php the_custom_logo(); ?></div>
		<?php else : ?>
			<a class="hdr__brand" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<?php
				$name = get_bloginfo( 'name' );
				$name = str_replace( array( 'UAB ', '„', '“' ), '', $name );
				echo esc_html( function_exists( 'mb_strtoupper' ) ? mb_strtoupper( $name, 'UTF-8' ) : strtoupper( $name ) );
				?>
			</a>
		<?php endif; ?>

		<div class="hdr__util">
			<?php if ( alkuras_info( 'phone' ) ) : ?>
				<a class="hdr__tel" href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', alkuras_info( 'phone' ) ) ); ?>"><?php echo esc_html( alkuras_info( 'phone' ) ); ?></a>
			<?php endif; ?>

			<?php
			$calc = get_page_by_path( 'skaiciuokles' );
			if ( $calc ) :
				?>
				<a class="hdr__ico" href="<?php echo esc_url( get_permalink( $calc ) ); ?>" aria-label="<?php esc_attr_e( 'Skaičiuoklės', 'alkuras' ); ?>" data-cursor="<?php esc_attr_e( 'Skaičiuoti', 'alkuras' ); ?>">
					<svg viewBox="0 0 24 24"><rect x="4" y="2" width="16" height="20" rx="2"/><path d="M8 6h8M8 11h2m3 0h3M8 15h2m3 0h3M8 19h8"/></svg>
				</a>
			<?php endif; ?>

			<a class="hdr__ico" href="<?php echo esc_url( alkuras_contact_url() ); ?>" aria-label="<?php esc_attr_e( 'Kontaktai', 'alkuras' ); ?>">
				<svg viewBox="0 0 24 24"><path d="M12 21s7-5.5 7-11a7 7 0 1 0-14 0c0 5.5 7 11 7 11z"/><circle cx="12" cy="10" r="2.6"/></svg>
			</a>

			<?php if ( function_exists( 'WC' ) ) : ?>
				<button class="hdr__ico" data-open-cart aria-label="<?php esc_attr_e( 'Krepšelis', 'alkuras' ); ?>" data-cursor="<?php esc_attr_e( 'Krepšelis', 'alkuras' ); ?>">
					<svg viewBox="0 0 24 24"><path d="M4 7h16l-1.4 12.2a2 2 0 0 1-2 1.8H7.4a2 2 0 0 1-2-1.8z"/><path d="M9 7V5.5a3 3 0 0 1 6 0V7"/></svg>
					<?php
					$count = ( WC()->cart ) ? WC()->cart->get_cart_contents_count() : 0;
					?>
					<span class="hdr__badge<?php echo $count ? ' on' : ''; ?>" data-cart-count><?php echo esc_html( (string) $count ); ?></span>
				</button>
			<?php endif; ?>

			<button class="burger" aria-label="<?php esc_attr_e( 'Meniu', 'alkuras' ); ?>" aria-expanded="false"><i></i><i></i><i></i></button>
		</div>
	</div>
</header>

<nav class="mnav" aria-label="<?php esc_attr_e( 'Mobilus meniu', 'alkuras' ); ?>">
	<?php
	wp_nav_menu(
		array(
			'theme_location' => has_nav_menu( 'mobile' ) ? 'mobile' : 'primary',
			'container'      => false,
			'items_wrap'     => '%3$s',
			'depth'          => 1,
			'walker'         => new Alkuras_Mobile_Walker(),
			'fallback_cb'    => '__return_empty_string',
		)
	);
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
	<p style="margin-top:2.5rem" class="eyebrow eyebrow--plain">
		<?php echo esc_html( alkuras_info( 'phone' ) ); ?> · <?php echo esc_html( alkuras_info( 'email' ) ); ?>
	</p>
</nav>

<?php if ( function_exists( 'WC' ) ) : ?>
<div class="drawer">
	<div class="drawer__bg" data-close-cart></div>
	<aside class="drawer__panel" aria-label="<?php esc_attr_e( 'Krepšelis', 'alkuras' ); ?>">
		<div class="drawer__hd">
			<span class="eyebrow eyebrow--plain"><?php esc_html_e( 'Krepšelis', 'alkuras' ); ?></span>
			<button data-close-cart aria-label="<?php esc_attr_e( 'Uždaryti', 'alkuras' ); ?>" style="font-size:1.4rem;line-height:1">&times;</button>
		</div>
		<div class="drawer__body" data-cart-items><?php alkuras_render_cart_items(); ?></div>
		<div class="drawer__ft">
			<div style="display:flex;justify-content:space-between;align-items:baseline">
				<span class="eyebrow eyebrow--plain"><?php esc_html_e( 'Medžiagų suma', 'alkuras' ); ?></span>
				<b class="num" style="font-size:1.4rem" data-cart-total><?php echo wp_kses_post( WC()->cart ? WC()->cart->get_cart_subtotal() : '' ); ?></b>
			</div>
			<p class="muted" style="font-size:.78rem;margin:0"><?php esc_html_e( 'Pristatymo kaina apskaičiuojama užsakymo metu pagal artimiausią karjerą ir atstumą.', 'alkuras' ); ?></p>
			<a class="btn btn--solid" href="<?php echo esc_url( wc_get_cart_url() ); ?>" style="justify-content:center">
				<span class="btn__fill"></span><?php esc_html_e( 'Pereiti prie užsakymo', 'alkuras' ); ?> <span class="btn__ico">&rarr;</span>
			</a>
		</div>
	</aside>
</div>
<?php endif; ?>

<main id="main">
