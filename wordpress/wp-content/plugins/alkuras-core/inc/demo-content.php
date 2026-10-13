<?php
/**
 * Demonstracinio turinio importas — atkuria dizaino prototipo turinį.
 *
 * @package Alkuras
 */

defined( 'ABSPATH' ) || exit;

const ALK_V = 'https://videos.pexels.com/video-files/';
const ALK_P = 'https://images.pexels.com/photos/';

/**
 * Nuotraukos nuoroda.
 *
 * @param int $id Pexels ID.
 * @param int $w  Plotis.
 * @return string
 */
function alk_img( $id, $w = 1400 ) {
	return ALK_P . $id . '/pexels-photo-' . $id . '.jpeg?auto=compress&cs=tinysrgb&w=' . $w;
}

/**
 * Demo video rinkinys.
 *
 * @return array
 */
function alkuras_demo_videos() {
	return array(
		'hero'      => ALK_V . '30911567/13223367_1920_1080_30fps.mp4',
		'aggregate' => ALK_V . '27130531/12081391_1920_1080_30fps.mp4',
		'sandSite'  => ALK_V . '35025189/14837858_1280_720_25fps.mp4',
		'conveyorQ' => ALK_V . '30911545/13223504_1280_720_30fps.mp4',
		'conveyorG' => ALK_V . '30911632/13223372_1280_720_30fps.mp4',
		'unload'    => ALK_V . '30911560/13223284_1280_720_30fps.mp4',
		'demoHouse' => ALK_V . '10971670/10971670-hd_1280_720_60fps.mp4',
		'demoPower' => ALK_V . '34241190/14511140_1280_720_60fps.mp4',
		'crusher'   => ALK_V . '31232154/13340365_1280_720_30fps.mp4',
		'loadTruck' => ALK_V . '36251027/15373408_1280_720_30fps.mp4',
		'excavator' => ALK_V . '35649966/15107549_1280_720_50fps.mp4',
	);
}

/**
 * Įkelia nuotrauką iš nuorodos į medijos biblioteką (su kešavimu pagal nuorodą).
 *
 * @param string $url   Nuoroda.
 * @param string $title Pavadinimas.
 * @return int Priedo ID arba 0.
 */
function alkuras_sideload( $url, $title = '' ) {
	if ( ! $url ) {
		return 0;
	}

	$existing = get_posts(
		array(
			'post_type'   => 'attachment',
			'post_status' => 'inherit',
			'numberposts' => 1,
			'fields'      => 'ids',
			'meta_key'    => '_alk_source_url', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'  => $url,              // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
		)
	);
	if ( $existing ) {
		return (int) $existing[0];
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$tmp = download_url( $url, 60 );
	if ( is_wp_error( $tmp ) ) {
		return 0;
	}

	$name = sanitize_title( $title ? $title : 'alkuras' ) . '-' . wp_rand( 1000, 9999 ) . '.jpg';
	$file = array(
		'name'     => $name,
		'tmp_name' => $tmp,
	);

	$id = media_handle_sideload( $file, 0, $title );
	if ( is_wp_error( $id ) ) {
		if ( file_exists( $tmp ) ) {
			wp_delete_file( $tmp );
		}
		return 0;
	}

	update_post_meta( $id, '_alk_source_url', $url );
	update_post_meta( $id, '_wp_attachment_image_alt', $title );
	return (int) $id;
}

/**
 * Sukuria arba atnaujina įrašą pagal slug.
 *
 * @param string $type  Įrašo tipas.
 * @param string $slug  Slug.
 * @param array  $data  Duomenys (post_title, post_content, menu_order).
 * @param array  $meta  Meta laukai.
 * @param string $image Nuotraukos nuoroda.
 * @return int
 */
function alkuras_upsert_post( $type, $slug, $data, $meta = array(), $image = '' ) {
	$existing = get_page_by_path( $slug, OBJECT, $type );

	$args = wp_parse_args(
		$data,
		array(
			'post_type'   => $type,
			'post_name'   => $slug,
			'post_status' => 'publish',
		)
	);

	if ( $existing ) {
		$args['ID'] = $existing->ID;
		$id         = wp_update_post( $args );
	} else {
		$id = wp_insert_post( $args );
	}

	if ( is_wp_error( $id ) || ! $id ) {
		return 0;
	}

	foreach ( $meta as $k => $v ) {
		update_post_meta( $id, $k, $v );
	}

	if ( $image && ! has_post_thumbnail( $id ) ) {
		$att = alkuras_sideload( $image, $args['post_title'] ?? '' );
		if ( $att ) {
			set_post_thumbnail( $id, $att );
		}
	}

	return (int) $id;
}

/**
 * Pagrindinis importas.
 *
 * @param bool $with_images Ar siųstis nuotraukas.
 * @return array Ataskaita.
 */
function alkuras_import_demo( $with_images = true ) {
	$log = array();
	$v   = alkuras_demo_videos();

	/* ---------- Karjerai ---------- */
	$quarries = array(
		array( 'prudupio-karjeras', 'Prūdupio karjeras', 'Kretingos r.', 'Smėlis · žvyras · juodžemis', 'I–V 8:00–17:00', 'Krautuvas vietoje', 20, 26, alk_img( 33122152 ) ),
		array( 'imbares-karjeras', 'Imbarės karjeras', 'Kretingos r.', 'Žvyras · žvirgždas · skalda', 'I–V 8:00–17:00, VI 9:00–14:00', 'Sijojimo įranga', 39, 57, alk_img( 32507739 ) ),
		array( 'ruigiu-karjeras', 'Ruigių karjeras', 'Kretingos r.', 'Smėlis · smėlio–žvyro mišinys', 'I–V 8:00–16:00', 'Krautuvas vietoje', 53, 21, alk_img( 31925763 ) ),
		array( 'katyciu-karjeras', 'Katyčių karjeras', 'Šilutės r.', 'Skalda · stambus žvyras', 'I–V 8:00–17:00', 'Krautuvas vietoje', 62, 76, alk_img( 33122146 ) ),
		array( 'smulkinimo-aikstele', 'Smulkinimo aikštelė', 'Kretinga', 'Betono ir mūro skalda', 'I–V 8:00–17:00', 'Mobilus smulkintuvas', 28, 42, alk_img( 17320062 ) ),
	);
	foreach ( $quarries as $i => $q ) {
		list( $slug, $title, $region, $materials, $hours, $load, $x, $y, $img ) = $q;
		$id = alkuras_upsert_post(
			'alk_karjeras',
			$slug,
			array(
				'post_title'   => $title,
				'post_content' => '',
				'menu_order'   => $i,
			),
			array(
				'_alk_regionas'     => $region,
				'_alk_medziagos'    => $materials,
				'_alk_darbo_laikas' => $hours,
				'_alk_krovimas'     => $load,
				'_alk_x'            => $x,
				'_alk_y'            => $y,
			),
			$with_images ? $img : ''
		);
		if ( $id ) {
			$log[] = 'Karjeras: ' . $title;
		}
	}
	wp_cache_delete( 'alk_quarries' );

	/* ---------- Projektai ---------- */
	$cats = array( 'Griovimas', 'Karjerai', 'Smulkinimas', 'Tiekimas' );
	foreach ( $cats as $c ) {
		if ( ! term_exists( $c, 'alk_proj_kat' ) ) {
			wp_insert_term( $c, 'alk_proj_kat' );
		}
	}

	$projects = array(
		array( 'gamybinio-pastato-griovimas', 'Buvusio gamybinio pastato griovimas', 'Griovimas', 'Klaipėda', '2025', array( '4 200 m²', '18 d. d.', '96 % perdirbta' ), 'a', alk_img( 27946397 ) ),
		array( 'imbares-karjero-pletra', 'Imbarės karjero plėtra', 'Karjerai', 'Kretingos r.', '2025', array( '12 ha', 'Naujas sijotuvas' ), 'b', alk_img( 31925763 ) ),
		array( 'betono-smulkinimas-objekte', 'Betono atliekų smulkinimas objekte', 'Smulkinimas', 'Palanga', '2024', array( '8 400 t', '0/40 frakcija' ), 'c', alk_img( 17320062 ) ),
		array( 'sandeliu-demontazas', 'Sandėlių komplekso demontažas', 'Griovimas', 'Šilutės r.', '2024', array( '2 900 m²', 'Metalo atskyrimas' ), 'd', alk_img( 11818456 ) ),
		array( 'kelio-pagrindo-tiekimas', 'Kelio pagrindo medžiagų tiekimas', 'Tiekimas', 'Kretingos r.', '2024', array( '14 500 t', '0/32 mišinys' ), 'e', alk_img( 26953857 ) ),
		array( 'teritorijos-paruosimas', 'Teritorijos paruošimas statyboms', 'Griovimas', 'Klaipėdos r.', '2023', array( '6 ha', 'Grunto keitimas' ), 'f', alk_img( 10451774 ) ),
	);
	foreach ( $projects as $i => $p ) {
		list( $slug, $title, $cat, $place, $year, $facts, $size, $img ) = $p;
		$id = alkuras_upsert_post(
			'alk_projektas',
			$slug,
			array(
				'post_title'   => $title,
				'post_content' => '',
				'menu_order'   => $i,
			),
			array(
				'_alk_vieta'   => $place,
				'_alk_metai'   => $year,
				'_alk_faktas1' => $facts[0] ?? '',
				'_alk_faktas2' => $facts[1] ?? '',
				'_alk_faktas3' => $facts[2] ?? '',
				'_alk_dydis'   => $size,
			),
			$with_images ? $img : ''
		);
		if ( $id ) {
			wp_set_object_terms( $id, $cat, 'alk_proj_kat' );
			$log[] = 'Projektas: ' . $title;
		}
	}

	/* ---------- Technika ---------- */
	$machines = array(
		array( 'viksrinis-ekskavatorius', 'Vikšrinis ekskavatorius', '30 t klasė · griovimo strėlė', $v['excavator'], alk_img( 30751525 ) ),
		array( 'mobilus-smulkintuvas', 'Mobilus žandikaulinis smulkintuvas', 'iki 250 t/val. · 0/63 mm', $v['crusher'], alk_img( 6890368 ) ),
		array( 'mobilus-sijotuvas', 'Mobilus sijotuvas', '3 frakcijų atskyrimas', $v['conveyorG'], alk_img( 38862400 ) ),
		array( 'frontalinis-krautuvas', 'Frontalinis krautuvas', '4,2 m³ kaušas', $v['loadTruck'], alk_img( 12835355 ) ),
		array( 'savivarciai', 'Savivarčiai', '20–30 t · tentai', $v['unload'], alk_img( 29174547 ) ),
		array( 'buldozeris', 'Buldozeris', 'Teritorijų lyginimas', $v['excavator'], alk_img( 416988 ) ),
		array( 'hidraulinis-kujis', 'Hidraulinis kūjis ir žirklės', 'Gelžbetonio ardymas', $v['demoPower'], alk_img( 14446390 ) ),
		array( 'vilkikas-su-tralu', 'Vilkikas su tralu', 'Technikos pervežimas', $v['loadTruck'], alk_img( 14206819 ) ),
	);
	foreach ( $machines as $i => $m ) {
		list( $slug, $title, $spec, $vid, $img ) = $m;
		$id = alkuras_upsert_post(
			'alk_technika',
			$slug,
			array( 'post_title' => $title, 'menu_order' => $i ),
			array( '_alk_specifikacija' => $spec, '_alk_video' => $vid ),
			$with_images ? $img : ''
		);
		if ( $id ) {
			$log[] = 'Technika: ' . $title;
		}
	}

	/* ---------- Atsiliepimai ---------- */
	$quotes = array(
		array( 'atsiliepimas-1', 'Mindaugas Jurgutis', 'UAB „Vakarų statyba“', 'Užsakėme 400 t žvyro į du objektus — atvežė per parą, kiekiai sutapo su važtaraščiais. Skaičiuoklė padėjo iš karto suprasti biudžetą.', alk_img( 8961524, 900 ) ),
		array( 'atsiliepimas-2', 'Rasa Petraitienė', 'Nekilnojamojo turto vystytoja', 'Griovimo darbai buvo atlikti tvarkingai, atliekos išvežtos ir perdirbtos. Gavome visus dokumentus atliekų apskaitai.', alk_img( 8487760, 900 ) ),
		array( 'atsiliepimas-3', 'Tomas Bagdonas', 'Rangovas, Klaipėda', 'Smulkintuvas atvažiavo į objektą — sutaupėme ir vežimo, ir naujos skaldos pirkimo. Pagrindui panaudojome savo betoną.', alk_img( 34670926, 900 ) ),
	);
	foreach ( $quotes as $i => $q ) {
		list( $slug, $name, $role, $text, $img ) = $q;
		$id = alkuras_upsert_post(
			'alk_atsiliepimas',
			$slug,
			array( 'post_title' => $name, 'post_content' => $text, 'menu_order' => $i ),
			array( '_alk_vardas' => $name, '_alk_pareigos' => $role ),
			$with_images ? $img : ''
		);
		if ( $id ) {
			$log[] = 'Atsiliepimas: ' . $name;
		}
	}

	return $log;
}

/**
 * Demo prekės (WooCommerce).
 *
 * @param bool $with_images Ar siųstis nuotraukas.
 * @return array
 */
function alkuras_import_demo_products( $with_images = true ) {
	if ( ! function_exists( 'wc_get_product' ) ) {
		return array( 'WooCommerce neaktyvus — prekės nekurtos.' );
	}

	$log  = array();
	$cats = array(
		'zvyras'    => 'Žvyras',
		'smelis'    => 'Smėlis',
		'skalda'    => 'Skalda',
		'perdirbta' => 'Perdirbta',
		'gruntas'   => 'Gruntas',
	);
	foreach ( $cats as $slug => $name ) {
		if ( ! term_exists( $slug, 'product_cat' ) ) {
			wp_insert_term( $name, 'product_cat', array( 'slug' => $slug ) );
		}
	}

	$items = array(
		array( 'zvyras-0-16', 'Žvyras', '0/16 mm', 'zvyras', 'Universalus statybinis žvyras pagrindams, aikštelėms ir betonavimui.', 12.90, 1.75, 'Populiaru', 'Pagrindų įrengimas, Betonavimas, Aikštelės', array( 'prudupio-karjeras', 'imbares-karjeras', 'ruigiu-karjeras' ), 10921738, 12044663 ),
		array( 'zvyras-16-32', 'Žvyras stambus', '16/32 mm', 'zvyras', 'Stambesnė frakcija drenažui ir apkrovą laikantiems sluoksniams.', 14.50, 1.70, '', 'Drenažas, Kelio pagrindas', array( 'imbares-karjeras', 'katyciu-karjeras' ), 7690440, 15186541 ),
		array( 'smelis-0-4', 'Smėlis smulkus', '0/4 mm', 'smelis', 'Sijotas smėlis mūrijimo ir tinkavimo skiediniams.', 9.80, 1.60, 'Sijotas', 'Mūro skiedinys, Tinkavimas', array( 'prudupio-karjeras', 'ruigiu-karjeras' ), 36214462, 30520816 ),
		array( 'smelis-statybinis', 'Smėlis statybinis', '0/8 mm', 'smelis', 'Užpildams, grindinio pasluoksniui ir teritorijos lyginimui.', 8.40, 1.62, '', 'Užpildas, Grindinio pasluoksnis', array( 'prudupio-karjeras', 'imbares-karjeras', 'katyciu-karjeras', 'ruigiu-karjeras' ), 9653855, 24182398 ),
		array( 'skalda-4-16', 'Dolomito skalda', '4/16 mm', 'skalda', 'Kietos uolienos skalda takams, aikštelėms ir betono mišiniams.', 18.60, 1.45, 'Populiaru', 'Takai, Betono mišiniai', array( 'imbares-karjeras', 'katyciu-karjeras' ), 5799727, 13367122 ),
		array( 'skalda-32-63', 'Dolomito skalda stambi', '32/63 mm', 'skalda', 'Stambi skalda sunkiai apkrautiems pagrindams ir šlaitams.', 17.20, 1.42, '', 'Šlaitų tvirtinimas, Sunkūs pagrindai', array( 'katyciu-karjeras' ), 36235847, 12029157 ),
		array( 'betono-skalda-0-40', 'Betono skalda', '0/40 mm', 'perdirbta', 'Perdirbta betono atliekų skalda — ekonomiškas pagrindų sprendimas.', 7.50, 1.50, 'Perdirbta', 'Kelio pagrindas, Aikštelės, Užpildas', array( 'smulkinimo-aikstele', 'prudupio-karjeras' ), 14562040, 31249540 ),
		array( 'plytu-skalda', 'Plytų / mūro skalda', '0/63 mm', 'perdirbta', 'Smulkintos mūro atliekos laikinų kelių ir aikštelių įrengimui.', 6.20, 1.35, 'Perdirbta', 'Laikini keliai, Teritorijos lyginimas', array( 'smulkinimo-aikstele' ), 32491406, 30991817 ),
		array( 'zvirgzdas', 'Žvirgždas', '5/20 mm', 'zvyras', 'Plautas žvirgždas apdailai, drenažui ir kraštovaizdžiui.', 16.40, 1.55, 'Plautas', 'Kraštovaizdis, Drenažas', array( 'imbares-karjeras' ), 8657953, 9608836 ),
		array( 'juodzemis', 'Juodžemis sijotas', '0/10 mm', 'gruntas', 'Sijotas derlingas gruntas vejoms ir želdiniams.', 15.90, 1.30, '', 'Vejos įrengimas, Želdiniai', array( 'prudupio-karjeras', 'ruigiu-karjeras' ), 29555030, 13931827 ),
		array( 'akmenys-dekoratyviniai', 'Dekoratyviniai akmenys', '63/150 mm', 'skalda', 'Stambūs akmenys kraštovaizdžio akcentams ir gabionams.', 29.00, 1.40, 'Nauja', 'Gabionai, Kraštovaizdis', array( 'imbares-karjeras' ), 37181458, 38960403 ),
		array( 'smelis-zvyro-misinys', 'Smėlio–žvyro mišinys', '0/32 mm', 'zvyras', 'Kelių ir aikštelių pagrindams — optimalus tankinimas.', 11.20, 1.80, '', 'Kelio pagrindas, Aikštelės', array( 'prudupio-karjeras', 'imbares-karjeras', 'ruigiu-karjeras', 'katyciu-karjeras' ), 4622069, 14091422 ),
	);

	foreach ( $items as $i => $it ) {
		list( $slug, $name, $frac, $cat, $desc, $price, $density, $badge, $use, $quarries, $img1, $img2 ) = $it;

		$existing = get_page_by_path( $slug, OBJECT, 'product' );
		$product  = $existing ? wc_get_product( $existing->ID ) : new WC_Product_Simple();

		$product->set_name( $name );
		$product->set_slug( $slug );
		$product->set_status( 'publish' );
		$product->set_catalog_visibility( 'visible' );
		$product->set_short_description( $desc );
		$product->set_description( $desc . ' ' . __( 'Parduodama tonomis, kaina nurodyta su PVM. Pristatymas skaičiuojamas pagal artimiausią karjerą ir jūsų adresą.', 'alkuras' ) );
		$product->set_regular_price( (string) $price );
		$product->set_sold_individually( false );
		$product->set_manage_stock( false );
		$product->set_menu_order( $i );

		$term = get_term_by( 'slug', $cat, 'product_cat' );
		if ( $term ) {
			$product->set_category_ids( array( $term->term_id ) );
		}

		$pid = $product->save();
		if ( ! $pid ) {
			continue;
		}

		update_post_meta( $pid, '_alk_frakcija', $frac );
		update_post_meta( $pid, '_alk_tankis', $density );
		update_post_meta( $pid, '_alk_vnt', 't' );
		update_post_meta( $pid, '_alk_zyma', $badge );
		update_post_meta( $pid, '_alk_panaudojimas', $use );
		update_post_meta( $pid, '_alk_karjerai', $quarries );

		if ( $with_images ) {
			if ( ! has_post_thumbnail( $pid ) ) {
				$a1 = alkuras_sideload( alk_img( $img1 ), $name . ' ' . $frac );
				if ( $a1 ) {
					set_post_thumbnail( $pid, $a1 );
				}
			}
			$gal = get_post_meta( $pid, '_product_image_gallery', true );
			if ( ! $gal ) {
				$a2 = alkuras_sideload( alk_img( $img2 ), $name . ' ' . $frac );
				if ( $a2 ) {
					update_post_meta( $pid, '_product_image_gallery', (string) $a2 );
				}
			}
		}

		$log[] = 'Prekė: ' . $name . ' ' . $frac;
	}

	return $log;
}

/**
 * Puslapiai ir meniu.
 *
 * @return array
 */
function alkuras_import_demo_pages() {
	$log = array();
	$v   = alkuras_demo_videos();

	$pages = array(
		'karjerai'     => array(
			'title'   => 'Karjerai',
			'eyebrow' => 'Gavyba',
			'hero'    => "Smėlio ir žvyro\nkarjerai",
			'text'    => 'Keturi eksploatuojami karjerai Kretingos ir Šilutės rajonuose. Gavyba, sijojimas ir krovimas vyksta vietoje — todėl kaina lieka be tarpininkų antkainio, o pristatymo atstumas — trumpiausias įmanomas.',
			'video'   => $v['sandSite'],
			'content' => "<!-- wp:heading -->\n<h2>Lokacijos</h2>\n<!-- /wp:heading -->\n\n<!-- wp:shortcode -->\n[alkuras_zemelapis]\n<!-- /wp:shortcode -->\n\n<!-- wp:shortcode -->\n[alkuras_karjerai]\n<!-- /wp:shortcode -->\n\n<!-- wp:shortcode -->\n[alkuras_pristatymas]\n<!-- /wp:shortcode -->",
		),
		'griovimas'    => array(
			'title'   => 'Griovimo darbai',
			'eyebrow' => 'Ardymas',
			'hero'    => "Griovimo\ndarbai",
			'text'    => 'Nuo pavienio ūkinio pastato iki gamybinio komplekso. Griauname, išrūšiuojame, susmulkiname vietoje ir paruošiame teritoriją tolimesnei statybai — su visa atliekų apskaita.',
			'video'   => $v['demoPower'],
			'content' => "<!-- wp:heading -->\n<h2>Preliminari kaina per 30 sekundžių</h2>\n<!-- /wp:heading -->\n\n<!-- wp:shortcode -->\n[alkuras_skaiciuokle tipas=\"griovimas\"]\n<!-- /wp:shortcode -->\n\n<!-- wp:heading -->\n<h2>Įvykdyti objektai</h2>\n<!-- /wp:heading -->\n\n<!-- wp:shortcode -->\n[alkuras_projektai kiekis=\"4\" kategorija=\"griovimas\"]\n<!-- /wp:shortcode -->",
		),
		'smulkinimas'  => array(
			'title'   => 'Smulkinimas ir frakcijos',
			'eyebrow' => 'Perdirbimas',
			'hero'    => "Smulkinimas\nir frakcijos",
			'text'    => 'Betono, gelžbetonio ir mūro atliekos nėra šiukšlės — tai medžiaga, kurią galima panaudoti tame pačiame objekte. Mobilus smulkintuvas atvažiuoja pas jus.',
			'video'   => $v['crusher'],
			'content' => "<!-- wp:heading -->\n<h2>Perdirbtos medžiagos kataloge</h2>\n<!-- /wp:heading -->\n\n<!-- wp:shortcode -->\n[alkuras_produktai kategorija=\"perdirbta\" kiekis=\"4\"]\n<!-- /wp:shortcode -->",
		),
		'skaiciuokles' => array(
			'title'   => 'Skaičiuoklės',
			'eyebrow' => 'Interaktyvūs įrankiai',
			'hero'    => 'Skaičiuoklės',
			'text'    => 'Du įrankiai, kurie padeda apsispręsti dar prieš skambutį: preliminari griovimo darbų kaina ir reikiamas medžiagų kiekis pagal plotą.',
			'video'   => $v['conveyorQ'],
			'content' => "<!-- wp:shortcode -->\n[alkuras_skaiciuokle tipas=\"griovimas\"]\n<!-- /wp:shortcode -->\n\n<!-- wp:shortcode -->\n[alkuras_skaiciuokle tipas=\"medziagos\"]\n<!-- /wp:shortcode -->\n\n<!-- wp:shortcode -->\n[alkuras_pristatymas]\n<!-- /wp:shortcode -->",
		),
		'projektai'    => array(
			'title'   => 'Projektai',
			'eyebrow' => 'Portfolio',
			'hero'    => "Įvykdyti\nprojektai",
			'text'    => 'Griovimas, karjerų plėtra, smulkinimas objektuose ir medžiagų tiekimas kelių statybai.',
			'video'   => $v['demoHouse'],
			'content' => "<!-- wp:shortcode -->\n[alkuras_projektai kiekis=\"12\"]\n<!-- /wp:shortcode -->\n\n<!-- wp:heading -->\n<h2>Ką sako užsakovai</h2>\n<!-- /wp:heading -->\n\n<!-- wp:shortcode -->\n[alkuras_atsiliepimai]\n<!-- /wp:shortcode -->",
		),
		'apie'         => array(
			'title'   => 'Apie įmonę',
			'eyebrow' => 'Nuo 2009 m.',
			'hero'    => "Vietinė įmonė,\nsunki technika",
			'text'    => 'UAB „Alkuras“ nuo 2009 m. eksploatuoja karjerus, griauna pastatus ir perdirba statybines atliekas Kretingos, Klaipėdos ir Šilutės rajonuose.',
			'video'   => $v['excavator'],
			'content' => "<!-- wp:heading -->\n<h2>Technikos parkas</h2>\n<!-- /wp:heading -->\n\n<!-- wp:shortcode -->\n[alkuras_technika]\n<!-- /wp:shortcode -->",
		),
		'kontaktai'    => array(
			'title'   => 'Kontaktai',
			'eyebrow' => 'Susisiekime',
			'hero'    => 'Susisiekime',
			'text'    => 'Mielai atsakysime į klausimus ir aptarsime projekto apimtį jums patogiu laiku.',
			'video'   => $v['loadTruck'],
			'content' => "<!-- wp:heading -->\n<h2>Karjerai žemėlapyje</h2>\n<!-- /wp:heading -->\n\n<!-- wp:shortcode -->\n[alkuras_zemelapis]\n<!-- /wp:shortcode -->\n\n<!-- wp:shortcode -->\n[alkuras_karjerai]\n<!-- /wp:shortcode -->",
		),
	);

	$ids = array();
	foreach ( $pages as $slug => $p ) {
		$id = alkuras_upsert_post(
			'page',
			$slug,
			array(
				'post_title'   => $p['title'],
				'post_content' => $p['content'],
			),
			array(
				'_alk_hero_eyebrow' => $p['eyebrow'],
				'_alk_hero_title'   => $p['hero'],
				'_alk_hero_text'    => $p['text'],
				'_alk_hero_video'   => $p['video'],
			)
		);
		if ( $id ) {
			$ids[ $slug ] = $id;
			$log[]        = 'Puslapis: ' . $p['title'];
		}
	}

	/* Pradinis puslapis */
	$front = alkuras_upsert_post(
		'page',
		'pradzia',
		array(
			'post_title'   => 'Pradžia',
			'post_content' => '',
		)
	);
	if ( $front ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $front );
		$ids['pradzia'] = $front;
		$log[]          = 'Pradinis puslapis nustatytas';
	}

	if ( ! empty( $ids['kontaktai'] ) ) {
		set_theme_mod( 'alkuras_contact_page', $ids['kontaktai'] );
	}

	/* Meniu */
	$menu_name = 'Pagrindinis meniu';
	$menu      = wp_get_nav_menu_object( $menu_name );
	$menu_id   = $menu ? $menu->term_id : wp_create_nav_menu( $menu_name );

	if ( ! is_wp_error( $menu_id ) ) {
		foreach ( wp_get_nav_menu_items( $menu_id ) as $item ) {
			wp_delete_post( $item->ID, true );
		}

		$order = array(
			'karjerai'    => __( 'Karjerai', 'alkuras' ),
			'griovimas'   => __( 'Griovimas', 'alkuras' ),
			'smulkinimas' => __( 'Smulkinimas', 'alkuras' ),
		);
		$pos   = 1;
		foreach ( $order as $slug => $short ) {
			if ( empty( $ids[ $slug ] ) ) {
				continue;
			}
			wp_update_nav_menu_item(
				$menu_id,
				0,
				array(
					'menu-item-title'     => $short,
					'menu-item-object-id' => $ids[ $slug ],
					'menu-item-object'    => 'page',
					'menu-item-type'      => 'post_type',
					'menu-item-status'    => 'publish',
					'menu-item-position'  => $pos++,
				)
			);
		}

		$shop_id = wc_get_page_id( 'shop' );
		if ( $shop_id > 0 ) {
			wp_update_nav_menu_item(
				$menu_id,
				0,
				array(
					'menu-item-title'     => __( 'Produkcija', 'alkuras' ),
					'menu-item-object-id' => $shop_id,
					'menu-item-object'    => 'page',
					'menu-item-type'      => 'post_type',
					'menu-item-status'    => 'publish',
					'menu-item-position'  => $pos++,
				)
			);
		}

		set_theme_mod( 'nav_menu_locations', array( 'primary' => $menu_id, 'mobile' => $menu_id ) );
		$log[] = 'Meniu sukurtas';
	}

	/* Antrinis meniu poraštėje */
	$footer_name = 'Poraštės meniu';
	$fmenu       = wp_get_nav_menu_object( $footer_name );
	$fmenu_id    = $fmenu ? $fmenu->term_id : wp_create_nav_menu( $footer_name );
	if ( ! is_wp_error( $fmenu_id ) ) {
		foreach ( wp_get_nav_menu_items( $fmenu_id ) as $item ) {
			wp_delete_post( $item->ID, true );
		}
		$pos = 1;
		foreach ( array( 'skaiciuokles', 'projektai', 'apie', 'kontaktai' ) as $slug ) {
			if ( empty( $ids[ $slug ] ) ) {
				continue;
			}
			wp_update_nav_menu_item(
				$fmenu_id,
				0,
				array(
					'menu-item-title'     => get_the_title( $ids[ $slug ] ),
					'menu-item-object-id' => $ids[ $slug ],
					'menu-item-object'    => 'page',
					'menu-item-type'      => 'post_type',
					'menu-item-status'    => 'publish',
					'menu-item-position'  => $pos++,
				)
			);
		}
		$locations             = (array) get_theme_mod( 'nav_menu_locations', array() );
		$locations['footer']   = $fmenu_id;
		set_theme_mod( 'nav_menu_locations', $locations );
	}

	return $log;
}

/**
 * Dizaino nustatymai (Customizer) — hero, veiklų panelės, kontaktai.
 *
 * @return array
 */
function alkuras_import_demo_settings() {
	$v = alkuras_demo_videos();

	$mods = array(
		'alkuras_hero_eyebrow'  => 'Karjerai · Griovimas · Smulkinimas · Nuo 2009 m.',
		'alkuras_hero_title'    => "Nuo karjero\niki jūsų <em>objekto</em>",
		'alkuras_hero_text'     => 'Smėlis, žvyras ir skalda iš keturių nuosavų karjerų Vakarų Lietuvoje. Griovimo darbai, statybinių atliekų smulkinimas ir pristatymas iki objekto — viena komanda, vienas atsakingas kontaktas.',
		'alkuras_hero_video'    => $v['hero'],
		'alkuras_hero_poster'   => 'https://images.pexels.com/videos/30911567/pexels-photo-30911567.jpeg?auto=compress&cs=tinysrgb&w=1600',
		'alkuras_hero_btn1'     => 'Užsisakyti medžiagas',
		'alkuras_hero_btn2'     => 'Skaičiuoti griovimo kainą',

		'alkuras_stat1_n'       => '4',
		'alkuras_stat1_l'       => 'Nuosavi karjerai',
		'alkuras_stat2_n'       => '12',
		'alkuras_stat2_l'       => 'Frakcijos sandėlyje',
		'alkuras_stat3_n'       => '17',
		'alkuras_stat3_l'       => 'Metai rinkoje',
		'alkuras_stat4_n'       => '24',
		'alkuras_stat4_suffix'  => ' val.',
		'alkuras_stat4_l'       => 'Pristatymas nuo užsakymo',

		'alkuras_act1_idx'      => '01 / Gavyba',
		'alkuras_act1_title'    => 'Smėlio ir žvyro karjerai',
		'alkuras_act1_text'     => 'Keturi nuosavi karjerai, sijojimo įranga vietoje ir krovimas savivarčiais. Didmena ir mažmena.',
		'alkuras_act1_cta'      => 'Žiūrėti karjerus',
		'alkuras_act1_img'      => alk_img( 31925763 ),
		'alkuras_act1_video'    => $v['sandSite'],
		'alkuras_act1_color'    => '#D9A521',

		'alkuras_act2_idx'      => '02 / Ardymas',
		'alkuras_act2_title'    => 'Griovimo darbai',
		'alkuras_act2_text'     => 'Pastatų ir statinių griovimas, teritorijos paruošimas statyboms, atliekų surinkimas ir apskaita.',
		'alkuras_act2_cta'      => 'Skaičiuoti kainą',
		'alkuras_act2_img'      => alk_img( 21914025 ),
		'alkuras_act2_video'    => $v['demoHouse'],
		'alkuras_act2_color'    => '#D9541E',

		'alkuras_act3_idx'      => '03 / Perdirbimas',
		'alkuras_act3_title'    => 'Smulkinimas ir frakcijos',
		'alkuras_act3_text'     => 'Betono ir mūro atliekos virsta 0/40 arba 0/63 skalda. Mobilus smulkintuvas dirba jūsų objekte.',
		'alkuras_act3_cta'      => 'Sužinoti daugiau',
		'alkuras_act3_img'      => alk_img( 17320062 ),
		'alkuras_act3_video'    => $v['crusher'],
		'alkuras_act3_color'    => '#D8F250',

		'alkuras_vtext_title'   => "INERTINĖS\nMEDŽIAGOS",
		'alkuras_vtext_video'   => $v['aggregate'],
		'alkuras_vtext_cap'     => 'Imbarės karjeras · sijojimo linija',

		'alkuras_deliv_video'   => $v['loadTruck'],
		'alkuras_cta_video'     => $v['excavator'],
		'alkuras_calc_demo_video' => $v['demoPower'],
		'alkuras_calc_mat_video'  => $v['conveyorG'],

		'alkuras_phone'         => '+370 460 32097',
		'alkuras_email'         => 'info@alkuras.lt',
		'alkuras_address'       => 'Medvalakio g. 27, Palanga',
		'alkuras_address2'      => 'Laukų g. 2, Asteikiai, Kretingos r.',
		'alkuras_company'       => 'UAB „Alkuras“',
		'alkuras_reg'           => 'Įm. k. 302312279 · PVM LT100005138216',
		'alkuras_about'         => 'UAB „Alkuras“ — karjerų eksploatavimas, griovimo darbai ir statybinių atliekų smulkinimas Vakarų Lietuvoje nuo 2009 m.',
	);

	foreach ( $mods as $k => $val ) {
		set_theme_mod( $k, $val );
	}

	foreach ( array( 'act1' => 'karjerai', 'act2' => 'griovimas', 'act3' => 'smulkinimas' ) as $act => $slug ) {
		$page = get_page_by_path( $slug );
		if ( $page ) {
			set_theme_mod( 'alkuras_' . $act . '_url', get_permalink( $page ) );
		}
	}

	return array( 'Dizaino nustatymai įrašyti' );
}

/**
 * Pilnas importas.
 *
 * @param bool $with_images Ar siųstis nuotraukas.
 * @return array
 */
function alkuras_import_all( $with_images = true ) {
	$log = array();
	$log = array_merge( $log, alkuras_import_demo( $with_images ) );
	$log = array_merge( $log, alkuras_import_demo_products( $with_images ) );
	$log = array_merge( $log, alkuras_import_demo_pages() );
	$log = array_merge( $log, alkuras_import_demo_settings() );
	flush_rewrite_rules();
	return $log;
}

/**
 * Importo puslapis administravime.
 */
function alkuras_demo_admin_menu() {
	add_submenu_page(
		'alkuras',
		__( 'Demo turinys', 'alkuras' ),
		__( 'Demo turinys', 'alkuras' ),
		'manage_options',
		'alkuras-demo',
		'alkuras_demo_page_render'
	);
}
add_action( 'admin_menu', 'alkuras_demo_admin_menu', 11 );

/**
 * Importo puslapis.
 */
function alkuras_demo_page_render() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$log = array();
	if ( isset( $_POST['alkuras_import'] ) && check_admin_referer( 'alkuras_import_demo' ) ) {
		@set_time_limit( 600 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		$with_images = ! empty( $_POST['with_images'] );
		$log         = alkuras_import_all( $with_images );
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Demonstracinis turinys', 'alkuras' ); ?></h1>
		<p class="description" style="max-width:62em">
			<?php esc_html_e( 'Įkelia tą patį turinį, kuris matomas dizaino prototipe: karjerus, projektus, techniką, atsiliepimus, 12 prekių ir visus puslapius su meniu. Esami įrašai tuo pačiu pavadinimu atnaujinami, nauji nekuriami du kartus — paleisti galima kelis kartus.', 'alkuras' ); ?>
		</p>
		<p class="description" style="max-width:62em">
			<strong><?php esc_html_e( 'Svarbu:', 'alkuras' ); ?></strong>
			<?php esc_html_e( 'nuotraukos ir video — iš Pexels, naudojami tik demonstracijai. Kainos ir karjerų duomenys yra pavyzdiniai; prieš paleidžiant juos reikia pakeisti tikraisiais.', 'alkuras' ); ?>
		</p>

		<form method="post">
			<?php wp_nonce_field( 'alkuras_import_demo' ); ?>
			<p>
				<label>
					<input type="checkbox" name="with_images" value="1" checked>
					<?php esc_html_e( 'Kartu atsisiųsti nuotraukas į medijos biblioteką (lėčiau, bet turinys atrodo pilnai)', 'alkuras' ); ?>
				</label>
			</p>
			<p>
				<button type="submit" name="alkuras_import" class="button button-primary button-hero">
					<?php esc_html_e( 'Įkelti demonstracinį turinį', 'alkuras' ); ?>
				</button>
			</p>
		</form>

		<?php if ( $log ) : ?>
			<h2><?php esc_html_e( 'Rezultatas', 'alkuras' ); ?></h2>
			<ol class="widefat" style="max-width:62em;background:#fff;padding:1em 2.5em">
				<?php foreach ( $log as $line ) : ?>
					<li><?php echo esc_html( $line ); ?></li>
				<?php endforeach; ?>
			</ol>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * WP-CLI komanda: wp alkuras demo [--no-images]
 */
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command(
		'alkuras demo',
		function ( $args, $assoc ) {
			$with_images = empty( $assoc['no-images'] );
			$log         = alkuras_import_all( $with_images );
			foreach ( $log as $line ) {
				WP_CLI::log( '  ' . $line );
			}
			WP_CLI::success( sprintf( 'Importuota įrašų: %d', count( $log ) ) );
		}
	);
}
