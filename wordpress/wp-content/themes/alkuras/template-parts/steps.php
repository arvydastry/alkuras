<?php
/**
 * Užsakymo eigos pakopos.
 *
 * @package Alkuras
 */

defined( 'ABSPATH' ) || exit;

$alk_steps = array(
	array(
		'n' => '01',
		't' => __( 'Klientas įveda pristatymo adresą', 'alkuras' ),
		'd' => __( 'Adresas arba miestas nurodomas krepšelyje. Sistema iš karto pasiūlo artimiausią karjerą ir galimą atsiėmimo tašką.', 'alkuras' ),
	),
	array(
		'n' => '02',
		't' => __( 'Sistema parenka artimiausią karjerą', 'alkuras' ),
		'd' => __( 'Kiekvienas karjeras turi savo asortimentą ir kainoraštį — rodoma tik ta produkcija, kurią realiai galima pakrauti.', 'alkuras' ),
	),
	array(
		'n' => '03',
		't' => __( 'Apskaičiuojama pristatymo kaina', 'alkuras' ),
		'd' => __( 'Pagal atstumą, kiekį ir savivarčio talpą. Klientas mato galutinę sumą prieš apmokėjimą — be paslėptų mokesčių.', 'alkuras' ),
	),
	array(
		'n' => '04',
		't' => __( 'Užsakymas ir apmokėjimas internetu', 'alkuras' ),
		'd' => __( 'Paysera arba Montonio, taip pat pavedimu. Sąskaita faktūra išrašoma automatiškai, užsakymas matomas administravimo aplinkoje.', 'alkuras' ),
	),
);

$alk_quarries = alk_get_quarries();
?>
<div class="steps">
	<?php foreach ( $alk_steps as $i => $s ) : ?>
		<article class="step">
			<span class="step__n num"><?php echo esc_html( $s['n'] ); ?></span>
			<div>
				<h3 class="step__t"><?php echo esc_html( $s['t'] ); ?></h3>
				<p class="step__d"><?php echo esc_html( $s['d'] ); ?></p>
			</div>
			<?php
			$alk_q = $alk_quarries[ $i ] ?? null;
			$alk_i = ( $alk_q && has_post_thumbnail( $alk_q['post_id'] ) ) ? get_the_post_thumbnail_url( $alk_q['post_id'], 'alkuras-card' ) : '';
			if ( $alk_i ) :
				?>
				<div class="step__fig"><img src="<?php echo esc_url( $alk_i ); ?>" alt="" loading="lazy"></div>
			<?php endif; ?>
		</article>
	<?php endforeach; ?>
</div>
