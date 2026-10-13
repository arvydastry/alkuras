<?php
/**
 * Šablonų pagalbinės funkcijos.
 *
 * @package Alkuras
 */

defined( 'ABSPATH' ) || exit;

/**
 * Ar sekcija rodoma.
 *
 * @param string $key Sekcijos raktas.
 * @return bool
 */
function alkuras_show( $key ) {
	return (bool) get_theme_mod( 'alkuras_show_' . $key, true );
}

/**
 * Kontaktinis duomuo.
 *
 * @param string $key     Raktas be priešdėlio.
 * @param string $default Numatytoji reikšmė.
 * @return string
 */
function alkuras_info( $key, $default = '' ) {
	return (string) get_theme_mod( 'alkuras_' . $key, $default );
}

/**
 * Antraštė, suskaidyta į kylančias eilutes.
 *
 * @param string $text  Tekstas (eilutės skiriamos \n).
 * @param string $class Papildoma klasė.
 * @param int    $delay Uždelsimo žingsnis ms.
 */
function alkuras_lines( $text, $class = '', $delay = 90 ) {
	$lines = preg_split( '/\r\n|\r|\n/', (string) $text );
	$i     = 0;
	foreach ( $lines as $line ) {
		$line = trim( $line );
		if ( '' === $line ) {
			continue;
		}
		printf(
			'<span class="rv-line %1$s" data-rv style="transition-delay:%2$dms"><span>%3$s</span></span>',
			esc_attr( $class ),
			(int) ( $i * $delay ),
			wp_kses( $line, array( 'em' => array(), 'strong' => array(), 'br' => array() ) )
		);
		$i++;
	}
}

/**
 * Pilno pločio video arba nuotraukos fonas.
 *
 * @param string $video  Video nuoroda.
 * @param string $poster Plakato nuoroda.
 * @param string $class  Konteinerio klasė.
 * @param string $px     data-px reikšmė.
 * @param bool   $auto   Ar paleisti automatiškai.
 */
function alkuras_media_bg( $video, $poster = '', $class = 'pxs__bg', $px = '.16', $auto = true ) {
	if ( ! $video && ! $poster ) {
		return;
	}
	?>
	<div class="<?php echo esc_attr( $class ); ?>" data-px="<?php echo esc_attr( $px ); ?>">
		<?php if ( $video ) : ?>
			<video <?php echo $auto ? 'autoplay ' : ''; ?>muted loop playsinline preload="none"
				<?php if ( $poster ) : ?>poster="<?php echo esc_url( $poster ); ?>"<?php endif; ?>
				data-src="<?php echo esc_url( $video ); ?>"></video>
		<?php else : ?>
			<img src="<?php echo esc_url( $poster ); ?>" alt="" loading="lazy">
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Mygtukas su užpildymo efektu.
 *
 * @param string $text  Tekstas.
 * @param string $url   Nuoroda.
 * @param string $style solid|line|signal.
 * @param array  $args  Papildomi: icon, class, magnet.
 */
function alkuras_button( $text, $url, $style = 'solid', $args = array() ) {
	if ( ! $text ) {
		return;
	}
	$icon   = $args['icon'] ?? '→';
	$class  = 'btn btn--' . $style . ( ! empty( $args['class'] ) ? ' ' . $args['class'] : '' );
	$magnet = $args['magnet'] ?? '.28';
	printf(
		'<a class="%1$s" href="%2$s" data-magnet="%3$s"%5$s><span class="btn__fill"></span>%4$s <span class="btn__ico">%6$s</span></a>',
		esc_attr( $class ),
		esc_url( $url ? $url : '#' ),
		esc_attr( $magnet ),
		esc_html( $text ),
		! empty( $args['style'] ) ? ' style="' . esc_attr( $args['style'] ) . '"' : '',
		esc_html( $icon )
	);
}

/**
 * Sekcijos antraštė su „eyebrow“.
 *
 * @param string $eyebrow Viršutinė eilutė.
 * @param string $title   Antraštė.
 * @param array  $args    lead, link, link_text, max.
 */
function alkuras_section_head( $eyebrow, $title, $args = array() ) {
	?>
	<div style="display:flex;flex-wrap:wrap;gap:2rem;justify-content:space-between;align-items:flex-end;margin-bottom:clamp(2rem,4vw,3.4rem)">
		<div style="max-width:<?php echo esc_attr( $args['max'] ?? '44ch' ); ?>">
			<?php if ( $eyebrow ) : ?>
				<p class="eyebrow rv-fade" data-rv><?php echo esc_html( $eyebrow ); ?></p>
			<?php endif; ?>
			<h2 class="t-h2 rv-up" data-rv style="margin-top:1rem"><?php echo wp_kses_post( $title ); ?></h2>
			<?php if ( ! empty( $args['lead'] ) ) : ?>
				<p class="lead rv-up" data-rv style="margin-top:1.2rem;transition-delay:.08s"><?php echo wp_kses_post( $args['lead'] ); ?></p>
			<?php endif; ?>
		</div>
		<?php if ( ! empty( $args['link'] ) ) : ?>
			<a class="ulink" href="<?php echo esc_url( $args['link'] ); ?>"><?php echo esc_html( $args['link_text'] ?? __( 'Daugiau', 'alkuras' ) ); ?></a>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Puslapio antraštė (vidiniams puslapiams).
 */
function alkuras_page_header() {
	$id      = get_queried_object_id();
	$eyebrow = alk_meta( $id, '_alk_hero_eyebrow' );
	$title   = alk_meta( $id, '_alk_hero_title' );
	$text    = alk_meta( $id, '_alk_hero_text' );
	$video   = alk_meta( $id, '_alk_hero_video' );
	$poster  = alk_meta( $id, '_alk_hero_poster' );
	$btn     = alk_meta( $id, '_alk_hero_btn_text' );
	$btn_url = alk_meta( $id, '_alk_hero_btn_url' );

	if ( ! $poster && has_post_thumbnail( $id ) ) {
		$poster = get_the_post_thumbnail_url( $id, 'alkuras-hero' );
	}
	if ( ! $title ) {
		$title = get_the_title( $id );
	}
	?>
	<section class="phead">
		<?php alkuras_media_bg( $video, $poster, 'phead__media', '.2' ); ?>
		<div class="phead__scrim"></div>
		<div class="dust" data-n="18"></div>
		<div class="grain"></div>
		<div class="phead__in wrap">
			<p class="crumbs">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Pradžia', 'alkuras' ); ?></a>
				<?php
				$ancestors = get_post_ancestors( $id );
				foreach ( array_reverse( $ancestors ) as $anc ) {
					echo ' / <a href="' . esc_url( get_permalink( $anc ) ) . '">' . esc_html( get_the_title( $anc ) ) . '</a>';
				}
				?>
				/ <?php echo esc_html( get_the_title( $id ) ); ?>
			</p>
			<h1 class="t-h1 display" data-mx="12" style="max-width:15ch">
				<?php alkuras_lines( $title ); ?>
			</h1>
			<?php if ( $text ) : ?>
				<p class="lead rv-up" data-rv style="margin-top:1.6rem;max-width:54ch;color:rgba(239,235,227,.78)"><?php echo wp_kses_post( $text ); ?></p>
			<?php endif; ?>
			<?php if ( $btn ) : ?>
				<div class="hero__cta rv-up" data-rv style="margin-top:2rem">
					<?php alkuras_button( $btn, $btn_url, 'signal' ); ?>
				</div>
			<?php endif; ?>
		</div>
	</section>
	<?php
}

/**
 * Numatytoji užklausos forma (kai nenurodytas trumpasis kodas).
 */
function alkuras_contact_form() {
	$shortcode = alkuras_info( 'cta_shortcode' );
	if ( $shortcode ) {
		echo do_shortcode( $shortcode );
		return;
	}
	?>
	<form class="grid alk-form" style="gap:1.6rem" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="alkuras_contact">
		<?php wp_nonce_field( 'alkuras_contact' ); ?>
		<p class="alk-hp" aria-hidden="true" style="position:absolute;left:-9999px">
			<label><?php esc_html_e( 'Palikite tuščią', 'alkuras' ); ?><input type="text" name="alk_website" tabindex="-1" autocomplete="off"></label>
		</p>
		<div class="grid g-2" style="gap:1.6rem">
			<label class="field"><input type="text" name="alk_name" placeholder=" " required><label><?php esc_html_e( 'Vardas, pavardė', 'alkuras' ); ?></label></label>
			<label class="field"><input type="tel" name="alk_phone" placeholder=" " required><label><?php esc_html_e( 'Telefonas', 'alkuras' ); ?></label></label>
		</div>
		<label class="field"><input type="email" name="alk_email" placeholder=" " required><label><?php esc_html_e( 'El. paštas', 'alkuras' ); ?></label></label>
		<label class="field field--sel">
			<select name="alk_service" required>
				<option value="" selected disabled></option>
				<option><?php esc_html_e( 'Medžiagų užsakymas', 'alkuras' ); ?></option>
				<option><?php esc_html_e( 'Griovimo darbai', 'alkuras' ); ?></option>
				<option><?php esc_html_e( 'Smulkinimas objekte', 'alkuras' ); ?></option>
				<option><?php esc_html_e( 'Kita', 'alkuras' ); ?></option>
			</select>
			<label><?php esc_html_e( 'Paslauga', 'alkuras' ); ?></label>
		</label>
		<label class="field"><textarea name="alk_message" placeholder=" " required></textarea><label><?php esc_html_e( 'Objektas, kiekiai, terminai', 'alkuras' ); ?></label></label>
		<div style="display:flex;flex-wrap:wrap;gap:1.5rem;align-items:center;justify-content:flex-end">
			<button type="submit" class="btn btn--signal" data-magnet=".3"><span class="btn__fill"></span><?php esc_html_e( 'Siųsti užklausą', 'alkuras' ); ?> <span class="btn__ico">&rarr;</span></button>
		</div>
		<p class="muted" style="font-size:.78rem;margin:0"><?php esc_html_e( 'Pateikdami užklausą sutinkate su duomenų tvarkymu pagal privatumo politiką.', 'alkuras' ); ?></p>
	</form>
	<?php
}

/**
 * Užklausos formos apdorojimas.
 */
function alkuras_handle_contact() {
	if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_wpnonce'] ) ), 'alkuras_contact' ) ) {
		wp_safe_redirect( home_url( '/' ) );
		exit;
	}

	// Spąstai botams.
	if ( ! empty( $_POST['alk_website'] ) ) {
		wp_safe_redirect( add_query_arg( 'uzklausa', 'ok', wp_get_referer() ?: home_url( '/' ) ) );
		exit;
	}

	$name    = sanitize_text_field( wp_unslash( $_POST['alk_name'] ?? '' ) );
	$phone   = sanitize_text_field( wp_unslash( $_POST['alk_phone'] ?? '' ) );
	$email   = sanitize_email( wp_unslash( $_POST['alk_email'] ?? '' ) );
	$service = sanitize_text_field( wp_unslash( $_POST['alk_service'] ?? '' ) );
	$message = sanitize_textarea_field( wp_unslash( $_POST['alk_message'] ?? '' ) );

	$to      = alkuras_info( 'email', get_option( 'admin_email' ) );
	$subject = sprintf( '[%s] %s — %s', get_bloginfo( 'name' ), __( 'Nauja užklausa', 'alkuras' ), $service );
	$body    = sprintf(
		"%s: %s\n%s: %s\n%s: %s\n%s: %s\n\n%s\n",
		__( 'Vardas', 'alkuras' ),
		$name,
		__( 'Telefonas', 'alkuras' ),
		$phone,
		__( 'El. paštas', 'alkuras' ),
		$email,
		__( 'Paslauga', 'alkuras' ),
		$service,
		$message
	);

	wp_mail( $to, $subject, $body, array( 'Reply-To: ' . $email ) );

	wp_safe_redirect( add_query_arg( 'uzklausa', 'ok', wp_get_referer() ?: home_url( '/' ) ) );
	exit;
}
add_action( 'admin_post_alkuras_contact', 'alkuras_handle_contact' );
add_action( 'admin_post_nopriv_alkuras_contact', 'alkuras_handle_contact' );
