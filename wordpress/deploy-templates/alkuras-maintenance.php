<?php
/**
 * Plugin Name: Alkuras — „Coming soon“ puslapis
 * Description: Lankytojams rodo firminį „netrukus atidarome“ puslapį su kontaktais. Prisijungę administratoriai mato visą svetainę įprastai. Išjunkite šį įskiepį, kad svetainė taptų matoma visa.
 * Version:     2.0.0
 * Author:      Simplex.lt
 *
 * @package Alkuras
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'template_redirect',
	function () {
		// Prisijungusieji mato svetainę įprastai.
		if ( is_user_logged_in() ) {
			return;
		}

		// Nekliudome sisteminiams keliams.
		$uri = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
		foreach ( array( '/wp-login.php', '/wp-admin', '/wp-json', '/wp-cron.php', '/.well-known', '/robots.txt', '/favicon.ico' ) as $allow ) {
			if ( str_starts_with( $uri, $allow ) ) {
				return;
			}
		}

		// Visus kitus adresus nukreipiame į pradžią — kad Google indeksuotų
		// tik vieną puslapį, o ne dešimtis identiškų.
		$path = (string) wp_parse_url( $uri, PHP_URL_PATH );
		if ( '/' !== $path && '' !== $path ) {
			wp_safe_redirect( home_url( '/' ), 302 );
			exit;
		}

		status_header( 200 );
		nocache_headers();
		header( 'Content-Type: text/html; charset=utf-8' );

		$name  = get_bloginfo( 'name' );
		$phone = (string) get_theme_mod( 'alkuras_phone', '' );
		$email = (string) get_theme_mod( 'alkuras_email', '' );
		$desc  = (string) get_theme_mod( 'alkuras_about', '' );
		$tel   = preg_replace( '/[^0-9+]/', '', $phone );
		$video = (string) get_theme_mod( 'alkuras_hero_video', '' );
		$post  = (string) get_theme_mod( 'alkuras_hero_poster', '' );
		?>
<!DOCTYPE html>
<html lang="lt">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?php echo esc_html( $name ); ?> — karjerai, griovimo darbai, smulkinimas</title>
<meta name="description" content="<?php echo esc_attr( $desc ); ?>">
<meta property="og:title" content="<?php echo esc_attr( $name ); ?>">
<meta property="og:description" content="<?php echo esc_attr( $desc ); ?>">
<meta property="og:type" content="website">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@75..125,400..900&family=Inter+Tight:wght@300..600&family=JetBrains+Mono:wght@400&display=swap" rel="stylesheet">
<style>
  :root{ --ink:#0B0C0D; --bone:#EFEBE3; --signal:#D8F250; --mute:#8A8478; }
  *{ box-sizing:border-box; }
  html,body{ margin:0; height:100%; }
  body{
    background:var(--ink); color:var(--bone);
    font-family:"Inter Tight","Helvetica Neue",Arial,sans-serif;
    display:grid; place-items:center; padding:2rem; position:relative; overflow:hidden;
  }
  .bg{ position:absolute; inset:0; z-index:0; }
  .bg video,.bg img{ width:100%; height:100%; object-fit:cover; filter:brightness(.34) grayscale(.3); }
  .scrim{
    position:absolute; inset:0; z-index:1;
    background:radial-gradient(120% 90% at 50% 45%, transparent 20%, rgba(11,12,13,.75) 100%);
  }
  .in{ position:relative; z-index:2; text-align:center; max-width:58ch; }
  .mark{
    font-family:"Archivo",sans-serif; font-weight:800; font-size:clamp(1.3rem,4vw,2rem);
    letter-spacing:.36em; text-indent:.36em; margin-bottom:2rem;
  }
  .bar{ width:3rem; height:2px; background:var(--signal); margin:0 auto 2.2rem; }
  h1{
    font-family:"Archivo",sans-serif; font-weight:800;
    font-size:clamp(2.2rem,7vw,4.4rem); letter-spacing:-.045em; line-height:.95;
    margin:0 0 1.4rem;
  }
  h1 em{ font-style:normal; color:var(--signal); }
  p.lead{ color:#C9C4BA; font-size:clamp(1rem,1.6vw,1.15rem); line-height:1.6; margin:0 auto 2.6rem; max-width:46ch; }
  .acts{
    font-family:"JetBrains Mono",ui-monospace,monospace; font-size:.68rem;
    letter-spacing:.22em; text-transform:uppercase; color:var(--mute); margin-bottom:2.6rem;
  }
  .cta{ display:flex; gap:.8rem; justify-content:center; flex-wrap:wrap; }
  .btn{
    display:inline-flex; align-items:center; gap:.6em;
    padding:1em 1.9em; border-radius:100px; text-decoration:none;
    font-family:"JetBrains Mono",ui-monospace,monospace; font-size:.75rem;
    letter-spacing:.1em; text-transform:uppercase;
    transition:background .35s, color .35s, transform .35s;
  }
  .btn--p{ background:var(--signal); color:#2C3405; }
  .btn--s{ border:1px solid rgba(239,235,227,.3); color:var(--bone); }
  .btn:hover{ transform:translateY(-2px); }
  .btn--s:hover{ background:var(--bone); color:var(--ink); border-color:var(--bone); }
  footer{
    position:absolute; bottom:1.6rem; left:0; right:0; z-index:2; text-align:center;
    font-family:"JetBrains Mono",ui-monospace,monospace; font-size:.62rem;
    letter-spacing:.18em; text-transform:uppercase; color:var(--mute);
  }
  @media (prefers-reduced-motion:reduce){ .bg video{ display:none; } }
</style>
</head>
<body>
  <div class="bg">
    <?php if ( $video ) : ?>
      <video autoplay muted loop playsinline <?php if ( $post ) : ?>poster="<?php echo esc_url( $post ); ?>"<?php endif; ?>>
        <source src="<?php echo esc_url( $video ); ?>" type="video/mp4">
      </video>
    <?php elseif ( $post ) : ?>
      <img src="<?php echo esc_url( $post ); ?>" alt="">
    <?php endif; ?>
  </div>
  <div class="scrim"></div>

  <div class="in">
    <div class="mark">ALKURAS</div>
    <div class="bar"></div>
    <h1>Netrukus — <em>visa svetainė</em></h1>
    <p class="lead"><?php echo esc_html( $desc ? $desc : 'Karjerų eksploatavimas, griovimo darbai ir statybinių atliekų smulkinimas Vakarų Lietuvoje.' ); ?></p>
    <div class="acts">Karjerai &middot; Griovimas &middot; Smulkinimas</div>
    <div class="cta">
      <?php if ( $tel ) : ?><a class="btn btn--p" href="tel:<?php echo esc_attr( $tel ); ?>"><?php echo esc_html( $phone ); ?></a><?php endif; ?>
      <?php if ( $email ) : ?><a class="btn btn--s" href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a><?php endif; ?>
    </div>
  </div>

  <footer><?php echo esc_html( $name ); ?> &middot; <?php echo esc_html( (string) get_theme_mod( 'alkuras_reg', '' ) ); ?></footer>
</body>
</html>
		<?php
		exit;
	},
	0
);
