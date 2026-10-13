<?php
/**
 * Trumpieji kodai — tas pats markup'as kaip dizaino prototipe.
 *
 * @package Alkuras
 */

defined( 'ABSPATH' ) || exit;

/**
 * Karjerų žemėlapio schema (SVG).
 *
 * @param string $active Aktyvaus karjero slug.
 * @return string
 */
function alkuras_map_svg( $active = '' ) {
	$quarries = alk_get_quarries();

	$grid = '';
	for ( $i = 0; $i <= 20; $i++ ) {
		$p     = $i * 5;
		$grid .= '<line x1="' . $p . '" y1="0" x2="' . $p . '" y2="100" stroke="rgba(239,235,227,.045)" stroke-width=".18"/>';
		$grid .= '<line x1="0" y1="' . $p . '" x2="100" y2="' . $p . '" stroke="rgba(239,235,227,.045)" stroke-width=".18"/>';
	}

	$contours = '';
	for ( $i = 0; $i < 8; $i++ ) {
		$r         = 10 + $i * 6.5;
		$op        = number_format( 0.15 - $i * 0.014, 3, '.', '' );
		$contours .= '<ellipse cx="40" cy="48" rx="' . number_format( $r * 1.4, 1, '.', '' ) . '" ry="' . number_format( $r, 1, '.', '' ) . '" fill="none" stroke="rgba(239,235,227,' . $op . ')" stroke-width=".28"/>';
	}

	$pins = '';
	foreach ( $quarries as $q ) {
		$label = $q['name'];
		$label = str_replace( array( ' karjeras', 'Smulkinimo aikštelė' ), array( '', 'Smulkinimas' ), $label );
		$on    = ( $active && $q['id'] === $active ) ? ' on' : '';
		$pins .= '<g class="pin' . $on . '" data-q="' . esc_attr( $q['id'] ) . '" transform="translate(' . esc_attr( $q['x'] ) . ',' . esc_attr( $q['y'] ) . ')">'
			. '<circle class="halo" r="3.5" fill="none" stroke="#D8F250" stroke-width=".3"/>'
			. '<circle class="dot" r="1.15" fill="#EFEBE3"/>'
			. '<circle r="5" fill="transparent"/>'
			. '<text x="2.4" y="0.7">' . esc_html( $label ) . '</text>'
			. '</g>';
	}

	return '<svg viewBox="0 0 100 100" preserveAspectRatio="xMidYMid slice" role="img" aria-label="' . esc_attr__( 'Karjerų žemėlapio schema', 'alkuras' ) . '">'
		. '<rect x="-10" y="-10" width="120" height="120" fill="#131517"/>' . $grid . $contours
		. '<g opacity=".5"><path d="M6 10 h5 M6 10 v5" stroke="#D8F250" stroke-width=".3" fill="none"/>'
		. '<text x="6" y="17" font-family="ui-monospace,monospace" font-size="2" fill="rgba(239,235,227,.4)" letter-spacing=".3">' . esc_html__( 'VAKARŲ LIETUVA', 'alkuras' ) . '</text></g>'
		. $pins . '</svg>';
}

/**
 * [alkuras_zemelapis]
 *
 * @param array $atts Atributai.
 * @return string
 */
function alkuras_sc_map( $atts ) {
	$a = shortcode_atts( array( 'aktyvus' => '' ), $atts );
	return '<div class="deliv__map rv-up" data-rv style="aspect-ratio:4/3.4">' . alkuras_map_svg( $a['aktyvus'] ) . '</div>';
}
add_shortcode( 'alkuras_zemelapis', 'alkuras_sc_map' );

/**
 * [alkuras_pristatymas] — pristatymo skaičiuoklė su žemėlapiu.
 *
 * @return string
 */
function alkuras_sc_delivery() {
	$zones = alk_get_zones();
	if ( ! $zones ) {
		return '';
	}
	$default_city = $zones[2]['city'] ?? $zones[0]['city'];
	$quote        = alkuras_calc_delivery( $default_city, 20 );

	ob_start();
	?>
	<div class="deliv rv-up" data-rv data-deliv>
		<div class="deliv__map" data-map><?php echo alkuras_map_svg( $quote && $quote['quarry'] ? $quote['quarry']['id'] : '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
		<div class="deliv__out">
			<p class="eyebrow"><?php esc_html_e( 'Pristatymas pagal lokaciją', 'alkuras' ); ?></p>
			<h3 class="t-h3" style="margin:.6rem 0 1.2rem"><?php esc_html_e( 'Sistema pati parenka artimiausią karjerą', 'alkuras' ); ?></h3>

			<label class="field field--sel">
				<select data-dv-city>
					<?php foreach ( $zones as $z ) : ?>
						<option<?php selected( $z['city'], $default_city ); ?>><?php echo esc_html( $z['city'] ); ?></option>
					<?php endforeach; ?>
				</select>
				<label><?php esc_html_e( 'Pristatymo vieta', 'alkuras' ); ?></label>
			</label>

			<div>
				<div style="display:flex;justify-content:space-between;align-items:baseline;margin-top:1.4rem">
					<span class="eyebrow eyebrow--plain"><?php esc_html_e( 'Kiekis', 'alkuras' ); ?></span>
					<b class="num" data-dv-tons-l>20 t</b>
				</div>
				<input class="range" type="range" min="5" max="200" step="5" value="20" data-dv-tons
					aria-label="<?php esc_attr_e( 'Kiekis tonomis', 'alkuras' ); ?>">
			</div>

			<dl style="margin:1.2rem 0 0">
				<div class="deliv__row"><dt><?php esc_html_e( 'Artimiausias karjeras', 'alkuras' ); ?></dt><dd data-dv-q><?php echo esc_html( $quote && $quote['quarry'] ? $quote['quarry']['name'] : '—' ); ?></dd></div>
				<div class="deliv__row"><dt><?php esc_html_e( 'Atstumas', 'alkuras' ); ?></dt><dd data-dv-km><?php echo esc_html( $quote ? $quote['km'] . ' km' : '—' ); ?></dd></div>
				<div class="deliv__row"><dt><?php esc_html_e( 'Reisai', 'alkuras' ); ?> (<?php echo esc_html( (int) alkuras_get_option( 'del_talpa' ) ); ?> t)</dt><dd data-dv-loads><?php echo esc_html( $quote ? $quote['loads'] . ' vnt.' : '—' ); ?></dd></div>
			</dl>

			<div class="deliv__total">
				<span class="eyebrow eyebrow--plain" style="color:rgba(239,235,227,.6)"><?php esc_html_e( 'Pristatymas', 'alkuras' ); ?></span>
				<b class="num flip" data-dv-price><?php echo esc_html( $quote ? alk_price( $quote['price'] ) : '—' ); ?></b>
			</div>

			<p class="muted" style="font-size:.8rem"><?php esc_html_e( 'Preliminaru. Galutinė kaina patvirtinama užsakymo metu — priklauso nuo privažiavimo ir iškrovimo sąlygų.', 'alkuras' ); ?></p>
		</div>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'alkuras_pristatymas', 'alkuras_sc_delivery' );

/**
 * [alkuras_skaiciuokle tipas="griovimas|medziagos"]
 *
 * @param array $atts Atributai.
 * @return string
 */
function alkuras_sc_calculator( $atts ) {
	$a = shortcode_atts( array( 'tipas' => 'griovimas' ), $atts );
	return 'medziagos' === $a['tipas'] ? alkuras_render_calc_material() : alkuras_render_calc_demolition();
}
add_shortcode( 'alkuras_skaiciuokle', 'alkuras_sc_calculator' );

/**
 * Griovimo skaičiuoklės markup'as.
 *
 * @return string
 */
function alkuras_render_calc_demolition() {
	$types = array(
		'gyv'  => __( 'Gyvenamasis namas', 'alkuras' ),
		'ukio' => __( 'Ūkinis pastatas', 'alkuras' ),
		'gam'  => __( 'Gamybinis pastatas', 'alkuras' ),
		'inz'  => __( 'Inž. konstrukcijos', 'alkuras' ),
	);
	$cons  = array(
		'med' => __( 'Medinė', 'alkuras' ),
		'mur' => __( 'Mūrinė', 'alkuras' ),
		'gzb' => __( 'Gelžbetonis', 'alkuras' ),
		'mis' => __( 'Mišri', 'alkuras' ),
	);
	$r     = alkuras_calc_demolition( array() );
	$video = alkuras_get_media_url( 'calc_demo_video' );

	ob_start();
	?>
	<div class="calc rv-up" data-rv data-calc-demo>
		<div class="calc__in">
			<p class="eyebrow"><?php esc_html_e( 'Skaičiuoklė 01', 'alkuras' ); ?></p>
			<h3 class="t-h3"><?php esc_html_e( 'Griovimo darbų kaina', 'alkuras' ); ?></h3>

			<div>
				<p class="eyebrow eyebrow--plain" style="margin-bottom:.7rem"><?php esc_html_e( 'Objekto tipas', 'alkuras' ); ?></p>
				<div class="seg" data-c-type>
					<?php $i = 0; foreach ( $types as $k => $l ) : ?>
						<button type="button" class="<?php echo 0 === $i ? 'on' : ''; ?>" data-k="<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $l ); ?></button>
					<?php $i++; endforeach; ?>
				</div>
			</div>

			<div>
				<p class="eyebrow eyebrow--plain" style="margin-bottom:.7rem"><?php esc_html_e( 'Konstrukcija', 'alkuras' ); ?></p>
				<div class="seg" data-c-cons>
					<?php $i = 0; foreach ( $cons as $k => $l ) : ?>
						<button type="button" class="<?php echo 1 === $i ? 'on' : ''; ?>" data-k="<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $l ); ?></button>
					<?php $i++; endforeach; ?>
				</div>
			</div>

			<div>
				<div style="display:flex;justify-content:space-between;align-items:baseline">
					<span class="eyebrow eyebrow--plain"><?php esc_html_e( 'Plotas', 'alkuras' ); ?></span>
					<b class="num" data-c-area-l>250 m²</b>
				</div>
				<input class="range" type="range" min="20" max="4000" step="10" value="250" data-c-area
					aria-label="<?php esc_attr_e( 'Objekto plotas kvadratiniais metrais', 'alkuras' ); ?>">
			</div>

			<div>
				<p class="eyebrow eyebrow--plain" style="margin-bottom:.7rem"><?php esc_html_e( 'Papildomai', 'alkuras' ); ?></p>
				<div class="seg" data-c-extra data-multi="1">
					<button type="button" data-k="pam"><?php esc_html_e( 'Pamatų ardymas', 'alkuras' ); ?></button>
					<button type="button" class="on" data-k="isv"><?php esc_html_e( 'Atliekų išvežimas', 'alkuras' ); ?></button>
					<button type="button" data-k="smu"><?php esc_html_e( 'Smulkinimas vietoje', 'alkuras' ); ?></button>
				</div>
			</div>
		</div>

		<div class="calc__out">
			<?php if ( $video ) : ?>
				<video muted loop playsinline preload="none" data-src="<?php echo esc_url( $video ); ?>"></video>
			<?php endif; ?>
			<div>
				<p class="eyebrow eyebrow--plain" style="color:rgba(239,235,227,.55)"><?php esc_html_e( 'Preliminari kaina', 'alkuras' ); ?></p>
				<div class="calc__result flip" data-c-res><?php echo esc_html( alk_price( round( $r['total'] / 10 ) * 10, 2 ) ); ?><small data-c-range><?php
					/* translators: 1: min price, 2: max price */
					printf( esc_html__( 'Galimas nuokrypis %1$s – %2$s', 'alkuras' ), esc_html( alk_price( round( $r['min'] / 10 ) * 10 ) ), esc_html( alk_price( round( $r['max'] / 10 ) * 10 ) ) );
				?></small></div>
			</div>
			<div class="calc__break" data-c-break></div>
			<a class="btn btn--signal" href="<?php echo esc_url( alkuras_contact_url() ); ?>" data-magnet=".25" style="justify-content:center">
				<span class="btn__fill"></span><?php esc_html_e( 'Pateikti užklausą', 'alkuras' ); ?> <span class="btn__ico">&rarr;</span>
			</a>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Medžiagų kiekio skaičiuoklės markup'as.
 *
 * @return string
 */
function alkuras_render_calc_material() {
	$products = alkuras_get_material_products();
	if ( ! $products ) {
		return '<p class="muted">' . esc_html__( 'Skaičiuoklei reikia bent vienos prekės kataloge.', 'alkuras' ) . '</p>';
	}
	$video = alkuras_get_media_url( 'calc_mat_video' );

	ob_start();
	?>
	<div class="calc rv-up" data-rv data-calc-mat>
		<div class="calc__in">
			<p class="eyebrow"><?php esc_html_e( 'Skaičiuoklė 02', 'alkuras' ); ?></p>
			<h3 class="t-h3"><?php esc_html_e( 'Medžiagų kiekis', 'alkuras' ); ?></h3>

			<label class="field field--sel">
				<select data-m-prod>
					<?php foreach ( $products as $p ) : ?>
						<option value="<?php echo esc_attr( $p['id'] ); ?>"
							data-tankis="<?php echo esc_attr( $p['density'] ); ?>"
							data-kaina="<?php echo esc_attr( $p['price'] ); ?>"
							data-pav="<?php echo esc_attr( $p['name'] ); ?>">
							<?php echo esc_html( $p['name'] . ( $p['frac'] ? ' · ' . $p['frac'] : '' ) ); ?>
						</option>
					<?php endforeach; ?>
				</select>
				<label><?php esc_html_e( 'Medžiaga', 'alkuras' ); ?></label>
			</label>

			<div>
				<div style="display:flex;justify-content:space-between;align-items:baseline;margin-top:.8rem">
					<span class="eyebrow eyebrow--plain"><?php esc_html_e( 'Plotas', 'alkuras' ); ?></span>
					<b class="num" data-m-area-l>100 m²</b>
				</div>
				<input class="range" type="range" min="5" max="2000" step="5" value="100" data-m-area
					aria-label="<?php esc_attr_e( 'Plotas kvadratiniais metrais', 'alkuras' ); ?>">
			</div>

			<div>
				<div style="display:flex;justify-content:space-between;align-items:baseline">
					<span class="eyebrow eyebrow--plain"><?php esc_html_e( 'Sluoksnio storis', 'alkuras' ); ?></span>
					<b class="num" data-m-thick-l>15 cm</b>
				</div>
				<input class="range" type="range" min="2" max="80" step="1" value="15" data-m-thick
					aria-label="<?php esc_attr_e( 'Sluoksnio storis centimetrais', 'alkuras' ); ?>">
			</div>

			<div>
				<p class="eyebrow eyebrow--plain" style="margin-bottom:.7rem"><?php esc_html_e( 'Tankinimas', 'alkuras' ); ?></p>
				<div class="seg" data-m-comp>
					<button type="button" data-k="1"><?php esc_html_e( 'Be tankinimo', 'alkuras' ); ?></button>
					<button type="button" class="on" data-k="1.2"><?php esc_html_e( 'Vidutinis ×1,2', 'alkuras' ); ?></button>
					<button type="button" data-k="1.35"><?php esc_html_e( 'Stiprus ×1,35', 'alkuras' ); ?></button>
				</div>
			</div>
		</div>

		<div class="calc__out">
			<?php if ( $video ) : ?>
				<video muted loop playsinline preload="none" data-src="<?php echo esc_url( $video ); ?>"></video>
			<?php endif; ?>
			<div>
				<p class="eyebrow eyebrow--plain" style="color:rgba(239,235,227,.55)"><?php esc_html_e( 'Reikiamas kiekis', 'alkuras' ); ?></p>
				<div class="calc__result flip" data-m-res>—<small data-m-sub></small></div>
			</div>
			<div class="calc__break" data-m-break></div>
			<button type="button" class="btn btn--signal" data-m-add data-magnet=".25" style="justify-content:center;width:100%">
				<span class="btn__fill"></span><?php esc_html_e( 'Įdėti į krepšelį', 'alkuras' ); ?> <span class="btn__ico">&rarr;</span>
			</button>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * [alkuras_karjerai]
 *
 * @return string
 */
function alkuras_sc_quarries() {
	$quarries = alk_get_quarries();
	if ( ! $quarries ) {
		return '';
	}

	ob_start();
	echo '<div>';
	$i = 0;
	foreach ( $quarries as $q ) {
		?>
		<article id="<?php echo esc_attr( $q['id'] ); ?>" class="rv-up" data-rv
			style="transition-delay:<?php echo esc_attr( $i * 70 ); ?>ms;padding:clamp(1.2rem,2.4vw,1.9rem) 0;border-bottom:1px solid var(--line)">
			<div style="display:flex;justify-content:space-between;gap:1.5rem;align-items:baseline">
				<h3 class="t-h4" style="font-family:var(--f-display);font-weight:700;letter-spacing:-.03em"><?php echo esc_html( $q['name'] ); ?></h3>
				<span class="eyebrow eyebrow--plain"><?php echo esc_html( $q['region'] ); ?></span>
			</div>
			<?php if ( $q['materials'] ) : ?>
				<p class="soft" style="margin-top:.7rem;font-size:.95rem"><?php echo esc_html( $q['materials'] ); ?></p>
			<?php endif; ?>
			<div style="display:flex;flex-wrap:wrap;gap:.5rem;margin-top:1rem">
				<?php if ( $q['hours'] ) : ?><span class="chip"><?php echo esc_html( $q['hours'] ); ?></span><?php endif; ?>
				<?php if ( $q['load'] ) : ?><span class="chip"><?php echo esc_html( $q['load'] ); ?></span><?php endif; ?>
			</div>
		</article>
		<?php
		$i++;
	}
	echo '</div>';
	return ob_get_clean();
}
add_shortcode( 'alkuras_karjerai', 'alkuras_sc_quarries' );

/**
 * [alkuras_projektai kiekis="6"]
 *
 * @param array $atts Atributai.
 * @return string
 */
function alkuras_sc_projects( $atts ) {
	$a = shortcode_atts( array( 'kiekis' => 6, 'kategorija' => '' ), $atts );

	$args = array(
		'post_type'      => 'alk_projektas',
		'posts_per_page' => (int) $a['kiekis'],
		'orderby'        => 'menu_order date',
		'order'          => 'ASC',
	);
	if ( $a['kategorija'] ) {
		$args['tax_query'] = array( array( 'taxonomy' => 'alk_proj_kat', 'field' => 'slug', 'terms' => $a['kategorija'] ) ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
	}

	$q = new WP_Query( $args );
	if ( ! $q->have_posts() ) {
		return '';
	}

	$sizes = array( 'a', 'b', 'c', 'd', 'e', 'f' );
	ob_start();
	echo '<div class="folio" data-projects-grid>';
	$i = 0;
	while ( $q->have_posts() ) {
		$q->the_post();
		$id    = get_the_ID();
		$size  = alk_meta( $id, '_alk_dydis', $sizes[ $i % 6 ] );
		$terms = get_the_terms( $id, 'alk_proj_kat' );
		$cat   = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->name : '';
		$meta  = array_filter( array( $cat, alk_meta( $id, '_alk_vieta' ), alk_meta( $id, '_alk_metai' ) ) );
		$facts = array_filter( array( alk_meta( $id, '_alk_faktas1' ), alk_meta( $id, '_alk_faktas2' ), alk_meta( $id, '_alk_faktas3' ) ) );
		?>
		<a class="fitem fitem--<?php echo esc_attr( $size ); ?>" href="<?php the_permalink(); ?>" data-cursor="<?php esc_attr_e( 'Projektas', 'alkuras' ); ?>">
			<?php if ( has_post_thumbnail() ) : ?>
				<?php the_post_thumbnail( 'alkuras-folio', array( 'class' => 'fitem__img', 'loading' => 'lazy', 'data-px' => '.07' ) ); ?>
			<?php endif; ?>
			<span class="fitem__tiles"><?php echo str_repeat( '<i></i>', 24 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<span class="fitem__meta">
				<?php if ( $meta ) : ?><span class="fitem__cat"><?php echo esc_html( implode( ' · ', $meta ) ); ?></span><?php endif; ?>
				<span class="fitem__t"><?php the_title(); ?></span>
				<?php if ( $facts ) : ?>
					<span class="fitem__facts"><?php foreach ( $facts as $f ) : ?><span><?php echo esc_html( $f ); ?></span><?php endforeach; ?></span>
				<?php endif; ?>
			</span>
		</a>
		<?php
		$i++;
	}
	echo '</div>';
	wp_reset_postdata();
	return ob_get_clean();
}
add_shortcode( 'alkuras_projektai', 'alkuras_sc_projects' );

/**
 * [alkuras_technika]
 *
 * @return string
 */
function alkuras_sc_machines() {
	$q = new WP_Query(
		array(
			'post_type'      => 'alk_technika',
			'posts_per_page' => -1,
			'orderby'        => 'menu_order title',
			'order'          => 'ASC',
		)
	);
	if ( ! $q->have_posts() ) {
		return '';
	}

	ob_start();
	echo '<div class="mach">';
	$i = 1;
	while ( $q->have_posts() ) {
		$q->the_post();
		$id  = get_the_ID();
		$img = get_the_post_thumbnail_url( $id, 'large' );
		$vid = alk_meta( $id, '_alk_video' );
		?>
		<div class="mach__row" data-img="<?php echo esc_url( (string) $img ); ?>" data-vid="<?php echo esc_url( $vid ); ?>">
			<span class="mach__idx"><?php echo esc_html( str_pad( (string) $i, 2, '0', STR_PAD_LEFT ) ); ?></span>
			<span class="mach__n"><?php the_title(); ?></span>
			<span class="mach__s"><?php echo esc_html( alk_meta( $id, '_alk_specifikacija' ) ); ?></span>
			<span class="mach__arrow">&#8599;</span>
		</div>
		<?php
		$i++;
	}
	echo '</div>';
	wp_reset_postdata();
	return ob_get_clean();
}
add_shortcode( 'alkuras_technika', 'alkuras_sc_machines' );

/**
 * [alkuras_atsiliepimai]
 *
 * @param array $atts Atributai.
 * @return string
 */
function alkuras_sc_testimonials( $atts ) {
	$a = shortcode_atts( array( 'kiekis' => 3 ), $atts );
	$q = new WP_Query(
		array(
			'post_type'      => 'alk_atsiliepimas',
			'posts_per_page' => (int) $a['kiekis'],
			'orderby'        => 'menu_order date',
			'order'          => 'ASC',
		)
	);
	if ( ! $q->have_posts() ) {
		return '';
	}

	ob_start();
	echo '<div class="grid g-3" data-stagger="120">';
	while ( $q->have_posts() ) {
		$q->the_post();
		$id = get_the_ID();
		?>
		<article class="quote rv-up" data-rv>
			<div class="quote__t"><?php echo wp_kses_post( wpautop( get_the_content() ) ); ?></div>
			<div class="quote__by">
				<?php if ( has_post_thumbnail() ) : ?>
					<?php the_post_thumbnail( 'thumbnail', array( 'class' => 'quote__av', 'loading' => 'lazy', 'alt' => '' ) ); ?>
				<?php endif; ?>
				<span>
					<span class="quote__n"><?php echo esc_html( alk_meta( $id, '_alk_vardas', get_the_title() ) ); ?></span><br>
					<span class="quote__r"><?php echo esc_html( alk_meta( $id, '_alk_pareigos' ) ); ?></span>
				</span>
			</div>
		</article>
		<?php
	}
	echo '</div>';
	wp_reset_postdata();
	return ob_get_clean();
}
add_shortcode( 'alkuras_atsiliepimai', 'alkuras_sc_testimonials' );

/**
 * [alkuras_produktai kategorija="zvyras" kiekis="4" stilius="grid|rail"]
 *
 * @param array $atts Atributai.
 * @return string
 */
function alkuras_sc_products( $atts ) {
	if ( ! function_exists( 'wc_get_products' ) ) {
		return '';
	}
	$a = shortcode_atts(
		array(
			'kategorija' => '',
			'kiekis'     => 8,
			'stilius'    => 'grid',
		),
		$atts
	);

	$args = array(
		'status'  => 'publish',
		'limit'   => (int) $a['kiekis'],
		'orderby' => 'menu_order',
		'order'   => 'ASC',
	);
	if ( $a['kategorija'] ) {
		$args['category'] = array_map( 'trim', explode( ',', $a['kategorija'] ) );
	}

	$products = wc_get_products( $args );
	if ( ! $products ) {
		return '';
	}

	$cards = '';
	foreach ( $products as $product ) {
		$cards .= alkuras_product_card( $product );
	}

	if ( 'rail' === $a['stilius'] ) {
		return '<div class="rail"><div class="rail__track">' . $cards . '</div></div>'; // phpcs:ignore
	}
	return '<div class="grid g-4" data-stagger="70">' . $cards . '</div>';
}
add_shortcode( 'alkuras_produktai', 'alkuras_sc_products' );

/**
 * Viena produkto kortelė — identiška prototipo `.prod`.
 *
 * @param WC_Product $product Prekė.
 * @return string
 */
function alkuras_product_card( $product ) {
	if ( ! $product instanceof WC_Product ) {
		return '';
	}
	$id    = $product->get_id();
	$frac  = alk_meta( $id, '_alk_frakcija' );
	$badge = alk_meta( $id, '_alk_zyma' );
	$unit  = alk_meta( $id, '_alk_vnt', 't' );

	$img  = get_the_post_thumbnail_url( $id, 'alkuras-card' );
	$gal  = $product->get_gallery_image_ids();
	$img2 = $gal ? wp_get_attachment_image_url( $gal[0], 'alkuras-card' ) : '';

	if ( ! $img ) {
		$img = wc_placeholder_img_src( 'alkuras-card' );
	}

	ob_start();
	?>
	<article class="prod rv-up" data-tilt="5" data-rv>
		<div class="tilt__in">
			<div class="prod__top">
				<a class="prod__fig" href="<?php echo esc_url( get_permalink( $id ) ); ?>" data-cursor="<?php esc_attr_e( 'Žiūrėti', 'alkuras' ); ?>">
					<img class="prod__img" src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( $product->get_name() . ' ' . $frac ); ?>" loading="lazy">
					<?php if ( $img2 ) : ?>
						<img class="prod__img2" src="<?php echo esc_url( $img2 ); ?>" alt="" loading="lazy">
					<?php endif; ?>
					<span class="prod__sieve"></span><span class="prod__grains"></span>
					<?php if ( $badge ) : ?>
						<span class="prod__tag<?php echo ( 'Nauja' === $badge ) ? ' prod__tag--new' : ''; ?>"><?php echo esc_html( $badge ); ?></span>
					<?php endif; ?>
				</a>
				<div class="prod__quick">
					<button type="button" class="btn btn--solid btn--sm" data-alk-add="<?php echo esc_attr( $id ); ?>" data-qty="1" style="flex:1;justify-content:center">
						<span class="btn__fill"></span><?php esc_html_e( 'Į krepšelį', 'alkuras' ); ?>
					</button>
				</div>
			</div>
			<div class="prod__body">
				<?php if ( $frac ) : ?><span class="prod__frac"><?php echo esc_html( $frac ); ?></span><?php endif; ?>
				<h3 class="prod__name"><?php echo esc_html( $product->get_name() ); ?></h3>
				<p class="prod__meta"><?php echo esc_html( wp_strip_all_tags( $product->get_short_description() ) ); ?></p>
				<div class="prod__price">
					<b class="num"><?php echo wp_kses_post( $product->get_price_html() ); ?></b>
					<span><?php
						/* translators: %s: matavimo vienetas */
						printf( esc_html__( '/ %s su PVM', 'alkuras' ), esc_html( 't' === $unit ? __( 'tona', 'alkuras' ) : $unit ) );
					?></span>
				</div>
			</div>
			<span class="tilt__sheen"></span>
		</div>
	</article>
	<?php
	return ob_get_clean();
}

/**
 * [alkuras_veiklos] — trys veiklų panelės su video užvedus.
 *
 * @return string
 */
function alkuras_sc_activities() {
	$items = array();
	for ( $i = 1; $i <= 3; $i++ ) {
		$title = get_theme_mod( "alkuras_act{$i}_title" );
		if ( ! $title ) {
			continue;
		}
		$items[] = array(
			'idx'   => get_theme_mod( "alkuras_act{$i}_idx", '' ),
			'title' => $title,
			'text'  => get_theme_mod( "alkuras_act{$i}_text", '' ),
			'cta'   => get_theme_mod( "alkuras_act{$i}_cta", __( 'Sužinoti daugiau', 'alkuras' ) ),
			'url'   => get_theme_mod( "alkuras_act{$i}_url", '#' ),
			'img'   => get_theme_mod( "alkuras_act{$i}_img", '' ),
			'video' => get_theme_mod( "alkuras_act{$i}_video", '' ),
			'acc'   => get_theme_mod( "alkuras_act{$i}_color", '#D8F250' ),
		);
	}
	if ( ! $items ) {
		return '';
	}

	ob_start();
	echo '<div class="acts">';
	foreach ( $items as $it ) {
		?>
		<a class="act" href="<?php echo esc_url( $it['url'] ); ?>" style="--acc:<?php echo esc_attr( $it['acc'] ); ?>"
			data-hover-video data-cursor="<?php echo esc_attr( wp_strip_all_tags( $it['title'] ) ); ?>">
			<?php if ( $it['img'] ) : ?>
				<div class="act__media"><img src="<?php echo esc_url( $it['img'] ); ?>" alt="<?php echo esc_attr( $it['title'] ); ?>" loading="lazy"></div>
			<?php endif; ?>
			<?php if ( $it['video'] ) : ?>
				<div class="act__video"><video muted loop playsinline preload="none" data-hover-only="1" data-src="<?php echo esc_url( $it['video'] ); ?>"></video></div>
			<?php endif; ?>
			<div class="act__scrim"></div>
			<div class="act__body">
				<?php if ( $it['idx'] ) : ?><span class="act__idx"><?php echo esc_html( $it['idx'] ); ?></span><?php endif; ?>
				<h3 class="act__t"><?php echo esc_html( $it['title'] ); ?></h3>
				<?php if ( $it['text'] ) : ?><p class="act__d"><?php echo esc_html( $it['text'] ); ?></p><?php endif; ?>
				<span class="act__go"><?php echo esc_html( $it['cta'] ); ?> <i></i></span>
			</div>
		</a>
		<?php
	}
	echo '</div>';
	return ob_get_clean();
}
add_shortcode( 'alkuras_veiklos', 'alkuras_sc_activities' );
