<?php
/**
 * 404.
 *
 * @package Alkuras
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<section class="phead" style="min-height:70vh">
	<div class="phead__scrim"></div>
	<div class="dust" data-n="20"></div>
	<div class="grain"></div>
	<div class="phead__in wrap">
		<p class="crumbs">404</p>
		<h1 class="t-h1 display" style="max-width:16ch"><?php alkuras_lines( __( "Tokio puslapio\nčia neiškasėme", 'alkuras' ) ); ?></h1>
		<p class="lead rv-up" data-rv style="margin-top:1.4rem;max-width:46ch;color:rgba(239,235,227,.78)">
			<?php esc_html_e( 'Puslapis buvo perkeltas arba niekada neegzistavo. Grįžkite į pradžią arba peržiūrėkite produkciją.', 'alkuras' ); ?>
		</p>
		<div class="hero__cta rv-up" data-rv style="margin-top:2rem">
			<?php
			alkuras_button( __( 'Į pradžią', 'alkuras' ), home_url( '/' ), 'signal' );
			if ( function_exists( 'wc_get_page_permalink' ) ) {
				alkuras_button( __( 'Produkcija', 'alkuras' ), wc_get_page_permalink( 'shop' ), 'line', array( 'icon' => '', 'style' => 'border-color:rgba(239,235,227,.32);color:#EFEBE3' ) );
			}
			?>
		</div>
	</div>
</section>
<?php
get_footer();
