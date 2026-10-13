<?php
/**
 * Bendras sąrašo šablonas.
 *
 * @package Alkuras
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<section class="phead" style="min-height:46vh">
	<div class="phead__scrim"></div>
	<div class="grain"></div>
	<div class="phead__in wrap">
		<h1 class="t-h1 display" style="max-width:16ch">
			<?php
			if ( is_search() ) {
				/* translators: %s: paieškos užklausa */
				printf( esc_html__( 'Paieška: %s', 'alkuras' ), '<em>' . esc_html( get_search_query() ) . '</em>' );
			} elseif ( is_archive() ) {
				the_archive_title();
			} else {
				echo esc_html( get_the_title( (int) get_option( 'page_for_posts' ) ) ?: __( 'Naujienos', 'alkuras' ) );
			}
			?>
		</h1>
	</div>
</section>

<section class="section">
	<div class="wrap">
		<?php if ( have_posts() ) : ?>
			<div class="grid g-3" data-stagger="90">
				<?php
				while ( have_posts() ) :
					the_post();
					?>
					<article class="prod rv-up" data-rv>
						<div class="tilt__in">
							<?php if ( has_post_thumbnail() ) : ?>
								<div class="prod__top">
									<a class="prod__fig" href="<?php the_permalink(); ?>">
										<?php the_post_thumbnail( 'alkuras-card', array( 'class' => 'prod__img', 'loading' => 'lazy' ) ); ?>
									</a>
								</div>
							<?php endif; ?>
							<div class="prod__body">
								<span class="prod__frac"><?php echo esc_html( get_the_date() ); ?></span>
								<h2 class="prod__name"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
								<p class="prod__meta"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22 ) ); ?></p>
							</div>
						</div>
					</article>
					<?php
				endwhile;
				?>
			</div>
			<div style="margin-top:3rem"><?php the_posts_pagination( array( 'mid_size' => 1 ) ); ?></div>
		<?php else : ?>
			<p class="lead"><?php esc_html_e( 'Nieko nerasta.', 'alkuras' ); ?></p>
			<?php get_search_form(); ?>
		<?php endif; ?>
	</div>
</section>
<?php
get_footer();
