<?php
/**
 * Pradinis puslapis — atitinka dizaino prototipą.
 *
 * @package Alkuras
 */

defined( 'ABSPATH' ) || exit;

get_header();

$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
$calc_pg  = get_page_by_path( 'skaiciuokles' );
$calc_url = $calc_pg ? get_permalink( $calc_pg ) : '#skaiciuokles';
$proj_pg  = get_page_by_path( 'projektai' );
$apie_pg  = get_page_by_path( 'apie' );
?>

<!-- ============ HERO ============ -->
<section class="hero vignette">
	<?php alkuras_media_bg( alkuras_info( 'hero_video' ), alkuras_info( 'hero_poster' ), 'hero__media', '.22' ); ?>
	<div class="hero__scrim"></div>
	<div class="dust" data-n="34"></div>
	<div class="grain"></div>

	<div class="scrollcue"><?php esc_html_e( 'Slinkite žemyn', 'alkuras' ); ?> <i></i></div>

	<div class="hero__in">
		<div class="wrap">
			<?php if ( alkuras_info( 'hero_eyebrow' ) ) : ?>
				<p class="eyebrow hero__eyebrow rv-fade" data-rv><?php echo esc_html( alkuras_info( 'hero_eyebrow' ) ); ?></p>
			<?php endif; ?>

			<h1 class="hero__title" data-mx="16">
				<?php alkuras_lines( alkuras_info( 'hero_title', get_bloginfo( 'name' ) ) ); ?>
			</h1>

			<div class="hero__foot">
				<?php if ( alkuras_info( 'hero_text' ) ) : ?>
					<p class="lead hero__lead rv-up" data-rv style="transition-delay:.2s"><?php echo wp_kses_post( alkuras_info( 'hero_text' ) ); ?></p>
				<?php endif; ?>
				<div class="hero__cta rv-up" data-rv style="transition-delay:.3s">
					<?php
					alkuras_button( alkuras_info( 'hero_btn1' ), alkuras_info( 'hero_btn1_url', $shop_url ), 'signal' );
					alkuras_button(
						alkuras_info( 'hero_btn2' ),
						alkuras_info( 'hero_btn2_url', $calc_url ),
						'line',
						array( 'icon' => '', 'style' => 'border-color:rgba(239,235,227,.32);color:#EFEBE3' )
					);
					?>
				</div>
			</div>
		</div>

		<div class="wrap" style="margin-top:clamp(2rem,4vw,3.4rem)">
			<div class="hstat">
				<?php for ( $i = 1; $i <= 4; $i++ ) : ?>
					<?php
					$n = alkuras_info( "stat{$i}_n" );
					if ( ! $n ) {
						continue;
					}
					?>
					<div class="hstat__i">
						<div class="hstat__n num">
							<span data-count="<?php echo esc_attr( (float) $n ); ?>"
								<?php if ( alkuras_info( "stat{$i}_suffix" ) ) : ?>data-suffix="<?php echo esc_attr( alkuras_info( "stat{$i}_suffix" ) ); ?>"<?php endif; ?>>0</span>
						</div>
						<div class="hstat__l"><?php echo esc_html( alkuras_info( "stat{$i}_l" ) ); ?></div>
					</div>
				<?php endfor; ?>
			</div>
		</div>
	</div>
</section>

<?php if ( alkuras_show( 'intro' ) && alkuras_info( 'intro_title' ) ) : ?>
<!-- ============ ĮVADAS ============ -->
<section class="section">
	<div class="wrap">
		<div class="grid" style="grid-template-columns:1fr 1.35fr;gap:clamp(2rem,6vw,6rem);align-items:start">
			<div><p class="eyebrow rv-fade" data-rv><?php esc_html_e( '01 — Kas mes esame', 'alkuras' ); ?></p></div>
			<div>
				<h2 class="t-h2 rv-up" data-rv style="max-width:20ch"><?php echo wp_kses_post( alkuras_info( 'intro_title' ) ); ?></h2>
				<?php if ( alkuras_info( 'intro_text' ) ) : ?>
					<p class="lead rv-up" data-rv style="margin-top:1.6rem;transition-delay:.1s;max-width:58ch"><?php echo wp_kses_post( alkuras_info( 'intro_text' ) ); ?></p>
				<?php endif; ?>

				<div class="grid g-3" style="margin-top:clamp(2rem,4vw,3.4rem);gap:2rem" data-stagger="110">
					<?php for ( $i = 1; $i <= 3; $i++ ) : ?>
						<?php if ( ! alkuras_info( "act{$i}_title" ) ) { continue; } ?>
						<div><div class="rv-up" data-rv>
							<p class="eyebrow eyebrow--plain" style="color:<?php echo esc_attr( alkuras_info( "act{$i}_color", '#D8F250' ) ); ?>">
								<?php echo esc_html( alkuras_info( "act{$i}_title" ) ); ?>
							</p>
							<p style="margin-top:.7rem;font-size:.95rem" class="soft"><?php echo esc_html( wp_trim_words( alkuras_info( "act{$i}_text" ), 14 ) ); ?></p>
						</div></div>
					<?php endfor; ?>
				</div>
			</div>
		</div>
	</div>
</section>
<?php endif; ?>

<?php if ( alkuras_show( 'acts' ) ) : ?>
<!-- ============ TRYS VEIKLOS ============ -->
<section class="section section--flush">
	<?php echo do_shortcode( '[alkuras_veiklos]' ); ?>
</section>
<?php endif; ?>

<?php if ( alkuras_show( 'vtext' ) && alkuras_info( 'vtext_video' ) ) : ?>
<!-- ============ VIDEO PER TEKSTĄ ============ -->
<section class="vtext">
	<video autoplay muted loop playsinline preload="none" data-src="<?php echo esc_url( alkuras_info( 'vtext_video' ) ); ?>"></video>
	<div class="vtext__mask">
		<h2><?php echo nl2br( esc_html( alkuras_info( 'vtext_title' ) ) ); ?></h2>
	</div>
	<?php if ( alkuras_info( 'vtext_cap' ) ) : ?>
		<span class="vtext__cap"><?php echo esc_html( alkuras_info( 'vtext_cap' ) ); ?></span>
	<?php endif; ?>
</section>
<?php endif; ?>

<?php if ( alkuras_show( 'shop' ) && function_exists( 'wc_get_products' ) ) : ?>
<!-- ============ PRODUKCIJA ============ -->
<section class="section" id="produkcija">
	<div class="wrap" style="display:flex;flex-wrap:wrap;gap:2rem;justify-content:space-between;align-items:flex-end;margin-bottom:clamp(2rem,4vw,3.4rem)">
		<div>
			<p class="eyebrow rv-fade" data-rv><?php esc_html_e( '02 — El. parduotuvė', 'alkuras' ); ?></p>
			<h2 class="t-h2 rv-up" data-rv style="margin-top:1rem;max-width:16ch"><?php esc_html_e( 'Medžiagos, parduodamos pagal svorį', 'alkuras' ); ?></h2>
			<p class="lead rv-up" data-rv style="margin-top:1.2rem;max-width:50ch;transition-delay:.08s">
				<?php esc_html_e( 'Kaina už toną su PVM. Pristatymas skaičiuojamas automatiškai pagal artimiausią karjerą ir jūsų adresą.', 'alkuras' ); ?>
			</p>
		</div>
		<div style="display:flex;gap:1rem;align-items:center">
			<a class="ulink" href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'Visas katalogas', 'alkuras' ); ?></a>
			<div class="rail__nav">
				<button class="rbtn" data-rail="prev" aria-label="<?php esc_attr_e( 'Atgal', 'alkuras' ); ?>"><svg viewBox="0 0 24 24"><path d="M15 5l-7 7 7 7"/></svg></button>
				<button class="rbtn" data-rail="next" aria-label="<?php esc_attr_e( 'Pirmyn', 'alkuras' ); ?>"><svg viewBox="0 0 24 24"><path d="M9 5l7 7-7 7"/></svg></button>
			</div>
		</div>
	</div>
	<?php echo do_shortcode( '[alkuras_produktai kiekis="8" stilius="rail"]' ); ?>
</section>
<?php endif; ?>

<?php if ( alkuras_show( 'deliv' ) ) : ?>
<!-- ============ PRISTATYMAS ============ -->
<section class="section pxs on-dark">
	<?php alkuras_media_bg( alkuras_info( 'deliv_video' ), '', 'pxs__bg', '.16' ); ?>
	<div class="pxs__scrim"></div>
	<div class="grain"></div>
	<div class="pxs__in wrap">
		<div class="grid" style="grid-template-columns:1fr 1.45fr;gap:clamp(2rem,5vw,5rem);align-items:center">
			<div>
				<p class="eyebrow rv-fade" data-rv style="color:var(--signal)"><?php esc_html_e( '03 — Logistika', 'alkuras' ); ?></p>
				<h2 class="t-h2 rv-up" data-rv style="margin-top:1rem"><?php esc_html_e( 'Artimiausias karjeras parenkamas automatiškai', 'alkuras' ); ?></h2>
				<p class="lead rv-up" data-rv style="margin-top:1.4rem;color:rgba(239,235,227,.75);max-width:44ch;transition-delay:.08s">
					<?php esc_html_e( 'Klientas įveda pristatymo adresą — sistema parodo, iš kurio karjero medžiaga bus vežama, kiek reisų reikės ir kokia bus atvežimo kaina. Galimas ir atsiėmimas savo transportu.', 'alkuras' ); ?>
				</p>
			</div>
			<?php echo do_shortcode( '[alkuras_pristatymas]' ); ?>
		</div>
	</div>
</section>
<?php endif; ?>

<?php if ( alkuras_show( 'steps' ) ) : ?>
<!-- ============ UŽSAKYMO EIGA ============ -->
<section class="section">
	<div class="wrap">
		<div style="max-width:44ch;margin-bottom:clamp(2rem,4vw,3.4rem)">
			<p class="eyebrow rv-fade" data-rv><?php esc_html_e( '04 — Kaip tai veikia', 'alkuras' ); ?></p>
			<h2 class="t-h2 rv-up" data-rv style="margin-top:1rem"><?php esc_html_e( 'Keturi žingsniai iki iškrauto savivarčio', 'alkuras' ); ?></h2>
		</div>
		<?php get_template_part( 'template-parts/steps' ); ?>
	</div>
</section>
<?php endif; ?>

<?php if ( alkuras_show( 'calc' ) ) : ?>
<!-- ============ SKAIČIUOKLĖS ============ -->
<section class="section" id="skaiciuokles" style="background:var(--bone-2)">
	<div class="wrap">
		<?php
		alkuras_section_head(
			__( '05 — Interaktyvūs įrankiai', 'alkuras' ),
			__( 'Skaičiuoklės, kurios užklausą paverčia užsakymu', 'alkuras' ),
			array( 'max' => '46ch', 'link' => $calc_url, 'link_text' => __( 'Abi skaičiuoklės atskirai', 'alkuras' ) )
		);
		?>
		<div class="grid" style="grid-template-columns:1fr;gap:clamp(1.5rem,3vw,2.5rem)">
			<?php
			echo do_shortcode( '[alkuras_skaiciuokle tipas="griovimas"]' );
			echo do_shortcode( '[alkuras_skaiciuokle tipas="medziagos"]' );
			?>
		</div>
	</div>
</section>
<?php endif; ?>

<?php if ( alkuras_show( 'folio' ) ) : ?>
<!-- ============ PROJEKTAI ============ -->
<section class="section">
	<div class="wrap">
		<?php
		alkuras_section_head(
			__( '06 — Portfolio', 'alkuras' ),
			__( 'Įvykdyti projektai', 'alkuras' ),
			array( 'max' => '40ch', 'link' => $proj_pg ? get_permalink( $proj_pg ) : '', 'link_text' => __( 'Visi projektai', 'alkuras' ) )
		);
		echo do_shortcode( '[alkuras_projektai kiekis="6"]' );
		?>
	</div>
</section>
<?php endif; ?>

<?php if ( alkuras_show( 'mach' ) ) : ?>
<!-- ============ TECHNIKA ============ -->
<section class="section on-dark" style="background:var(--ink);overflow:hidden">
	<div class="mq" data-speed=".55" style="margin-bottom:clamp(2.5rem,5vw,4.5rem)">
		<div class="mq__row">
			<?php
			$words = array( 'EKSKAVATORIAI', 'SMULKINTUVAI', 'SIJOTUVAI', 'SAVIVARČIAI', 'KRAUTUVAI' );
			foreach ( $words as $w ) :
				?>
				<span class="mq__i"><?php echo esc_html( $w ); ?><span class="mq__dot"></span></span>
			<?php endforeach; ?>
		</div>
	</div>
	<div class="wrap">
		<div class="grid" style="grid-template-columns:1fr 1.6fr;gap:clamp(2rem,5vw,5rem);align-items:start">
			<div>
				<p class="eyebrow rv-fade" data-rv style="color:var(--signal)"><?php esc_html_e( '07 — Technikos parkas', 'alkuras' ); ?></p>
				<h2 class="t-h2 rv-up" data-rv style="margin-top:1rem"><?php esc_html_e( 'Sava technika — be tarpininkų', 'alkuras' ); ?></h2>
				<p class="lead rv-up" data-rv style="margin-top:1.4rem;color:rgba(239,235,227,.72);transition-delay:.08s">
					<?php esc_html_e( 'Nuo 30 tonų ekskavatoriaus su griovimo strėle iki mobilaus smulkintuvo. Užveskite pelę ant eilutės — pamatysite techniką darbe.', 'alkuras' ); ?>
				</p>
				<?php
				if ( $apie_pg ) {
					alkuras_button( __( 'Apie įmonę', 'alkuras' ), get_permalink( $apie_pg ), 'line', array( 'style' => 'margin-top:2rem;border-color:rgba(239,235,227,.28)', 'magnet' => '.25' ) );
				}
				?>
			</div>
			<div><?php echo do_shortcode( '[alkuras_technika]' ); ?></div>
		</div>
	</div>
</section>
<?php endif; ?>

<?php if ( alkuras_show( 'figs' ) ) : ?>
<!-- ============ KODĖL ALKURAS ============ -->
<section class="section pxs on-dark">
	<?php alkuras_media_bg( alkuras_info( 'figs_video' ), '', 'pxs__bg', '.2' ); ?>
	<div class="pxs__scrim"></div>
	<div class="dust" data-n="22"></div>
	<div class="pxs__in wrap">
		<p class="eyebrow rv-fade" data-rv style="color:var(--signal)"><?php esc_html_e( '08 — Kodėl Alkuras', 'alkuras' ); ?></p>
		<h2 class="t-h2 rv-up" data-rv style="margin-top:1rem;max-width:18ch"><?php esc_html_e( 'Uždaras ciklas: nugriauname, susmulkiname, parduodame', 'alkuras' ); ?></h2>
		<div class="figs" style="margin-top:clamp(3rem,6vw,5rem)">
			<?php for ( $i = 1; $i <= 4; $i++ ) : ?>
				<?php if ( ! alkuras_info( "stat{$i}_n" ) ) { continue; } ?>
				<div class="fig">
					<div class="fig__n num">
						<span data-count="<?php echo esc_attr( (float) alkuras_info( "stat{$i}_n" ) ); ?>"
							<?php if ( alkuras_info( "stat{$i}_suffix" ) ) : ?>data-suffix="<?php echo esc_attr( alkuras_info( "stat{$i}_suffix" ) ); ?>"<?php endif; ?>>0</span>
					</div>
					<div class="fig__l"><?php echo esc_html( alkuras_info( "stat{$i}_l" ) ); ?></div>
				</div>
			<?php endfor; ?>
		</div>
	</div>
</section>
<?php endif; ?>

<?php if ( alkuras_show( 'quotes' ) ) : ?>
<!-- ============ ATSILIEPIMAI ============ -->
<section class="section">
	<div class="wrap">
		<div style="max-width:38ch;margin-bottom:clamp(2rem,4vw,3.4rem)">
			<p class="eyebrow rv-fade" data-rv><?php esc_html_e( '09 — Klientai', 'alkuras' ); ?></p>
			<h2 class="t-h2 rv-up" data-rv style="margin-top:1rem"><?php esc_html_e( 'Ką sako užsakovai', 'alkuras' ); ?></h2>
		</div>
		<?php echo do_shortcode( '[alkuras_atsiliepimai]' ); ?>
	</div>
</section>
<?php endif; ?>

<?php if ( alkuras_show( 'cta' ) ) : ?>
<!-- ============ UŽKLAUSA ============ -->
<section class="section pxs on-dark" id="uzklausa">
	<?php alkuras_media_bg( alkuras_info( 'cta_video' ), '', 'pxs__bg', '.14' ); ?>
	<div class="pxs__scrim"></div>
	<div class="grain"></div>
	<div class="pxs__in wrap">
		<div class="grid" style="grid-template-columns:1fr 1.15fr;gap:clamp(2rem,5vw,5rem);align-items:start">
			<div>
				<p class="eyebrow rv-fade" data-rv style="color:var(--signal)"><?php esc_html_e( '10 — Užklausa', 'alkuras' ); ?></p>
				<h2 class="t-h2 rv-up" data-rv style="margin-top:1rem"><?php esc_html_e( 'Pasakykite, ko reikia — atsakysime per 1 darbo dieną', 'alkuras' ); ?></h2>
				<p class="lead rv-up" data-rv style="margin-top:1.4rem;color:rgba(239,235,227,.72)">
					<?php esc_html_e( 'Galite prisegti objekto nuotraukas ar planus — taip pasiūlymas bus tikslesnis.', 'alkuras' ); ?>
				</p>
				<div style="margin-top:2.5rem;display:grid;gap:.9rem">
					<?php if ( alkuras_info( 'phone' ) ) : ?>
						<a class="ulink" href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', alkuras_info( 'phone' ) ) ); ?>"><?php echo esc_html( alkuras_info( 'phone' ) ); ?></a><br>
					<?php endif; ?>
					<?php if ( alkuras_info( 'email' ) ) : ?>
						<a class="ulink" href="mailto:<?php echo esc_attr( alkuras_info( 'email' ) ); ?>"><?php echo esc_html( alkuras_info( 'email' ) ); ?></a>
					<?php endif; ?>
				</div>
			</div>
			<?php alkuras_contact_form(); ?>
		</div>
	</div>
</section>
<?php endif; ?>

<?php
get_footer();
