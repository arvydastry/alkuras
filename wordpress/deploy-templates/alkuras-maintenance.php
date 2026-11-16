<?php
/**
 * Plugin Name: Alkuras — laikina apsauga
 * Description: Neleidžia neprisijungusiems lankytojams matyti svetainės, kol ji ruošiama. Administratoriai mato viską įprastai.
 * Version:     1.0.0
 * Author:      Simplex.lt
 *
 * PASLEPTI SVETAINĘ  — šis failas yra wp-content/mu-plugins/
 * PADARYTI VIEŠĄ     — ištrinkite šį failą.
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
		foreach ( array( '/wp-login.php', '/wp-admin', '/wp-json', '/wp-cron.php', '/.well-known' ) as $allow ) {
			if ( str_starts_with( $uri, $allow ) ) {
				return;
			}
		}

		nocache_headers();
		status_header( 503 );
		header( 'Retry-After: 3600' );
		header( 'Content-Type: text/html; charset=utf-8' );

		$name  = get_bloginfo( 'name' );
		$login = esc_url( wp_login_url() );
		?>
<!DOCTYPE html>
<html lang="lt">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title><?php echo esc_html( $name ); ?> — svetainė ruošiama</title>
<link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;800&family=JetBrains+Mono:wght@400&display=swap" rel="stylesheet">
<style>
  :root{ --ink:#0B0C0D; --bone:#EFEBE3; --signal:#D8F250; --mute:#8A8478; }
  *{ box-sizing:border-box; }
  body{
    margin:0; min-height:100svh; display:grid; place-items:center;
    background:var(--ink); color:var(--bone);
    font-family:"Archivo","Helvetica Neue",Arial,sans-serif;
    padding:2rem; text-align:center;
  }
  .mark{
    font-weight:800; font-size:clamp(1.6rem,5vw,2.6rem);
    letter-spacing:.34em; text-indent:.34em; margin-bottom:2.5rem;
  }
  h1{
    font-weight:800; font-size:clamp(2rem,6vw,3.6rem);
    letter-spacing:-.04em; line-height:1; margin:0 0 1.2rem; max-width:16ch;
  }
  h1 em{ font-style:normal; color:var(--signal); }
  p{ color:var(--mute); max-width:44ch; margin:0 auto; line-height:1.6; font-size:1rem; }
  .meta{
    margin-top:3rem; font-family:"JetBrains Mono",ui-monospace,monospace;
    font-size:.7rem; letter-spacing:.2em; text-transform:uppercase; color:var(--mute);
  }
  .meta a{ color:var(--mute); text-decoration:none; border-bottom:1px solid rgba(239,235,227,.2); }
  .meta a:hover{ color:var(--signal); border-color:var(--signal); }
  .bar{ width:3rem; height:2px; background:var(--signal); margin:0 auto 2rem; }
</style>
</head>
<body>
  <div>
    <div class="mark">ALKURAS</div>
    <div class="bar"></div>
    <h1>Svetainė <em>ruošiama</em></h1>
    <p>Netrukus čia rasite karjerų produkciją, griovimo darbų paslaugas ir skaičiuokles. Dėl užsakymų kol kas skambinkite.</p>
    <div class="meta"><a href="<?php echo $login; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">Prisijungti</a></div>
  </div>
</body>
</html>
		<?php
		exit;
	},
	0
);
