<?php
/**
 * The Template for displaying all single posts.
 *
 * @package Wisteria
 */

get_header(); ?>

	<div class="container">
		<div class="row">

			<div id="primary" class="content-area <?php wisteria_layout_class( 'content' ); ?>">
				<main id="main" class="site-main">

				<?php while ( have_posts() ) : the_post(); ?>

					<?php get_template_part( 'template-parts/content', 'single' ); ?>

					<?php
					if ( '' !== get_the_author_meta( 'description' ) ) {
						get_template_part( 'template-parts/biography' );
					}
					?>

					<?php wisteria_the_post_navigation(); ?>

					<?php
						// If comments are open or we have at least one comment, load up the comment template
						if ( comments_open() || '0' != get_comments_number() ) :
							comments_template();
						endif;
					?>

				<?php endwhile; // end of the loop. ?>

				</main><!-- #main -->
			</div><!-- #primary -->

			<?php get_sidebar(); ?>

		</div><!-- .row -->
	</div><!-- .container -->

<?php get_footer(); ?>
