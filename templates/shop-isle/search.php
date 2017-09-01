<?php
/**
 * The template for displaying search results pages.
 *
 * @package WordPress
 * @subpackage Shop Isle
 */

get_header(); ?>

	<!-- Wrapper start -->
	<div class="main">
		<!-- Post single start -->
			<?php
			$shop_isle_header_image = get_header_image();
			if ( ! empty( $shop_isle_header_image ) ) :
				echo '<section class="page-header-module module bg-dark" data-background="' . esc_url( $shop_isle_header_image ) . '">';
				else :
					echo '<section class="page-header-module module bg-dark">';
				endif;
			?>
			
					<div class="container">
						<div class="row">
							<div class="col-sm-6 col-sm-offset-3">
								<h1 class="module-title font-alt">
								<?php
								printf(
									/* translators: s: Search term. */
										__( 'Search Results for: %s', 'shop-isle' ),
									'<span>' . get_search_query() . '</span>'
								);
								?>
								</h1>
							</div>
						</div>
					</div><!-- .container -->
			
			
			<?php
				echo '</section>';

				echo '<section class="module">';
			?>
			<div class="container">

				<div class="row">

					<!-- Content column start -->
					<div class="col-sm-8">

						<?php if ( have_posts() ) : ?>

							<?php get_template_part( 'loop' ); ?>

						<?php else : ?>

							<?php get_template_part( 'content', 'none' ); ?>

						<?php endif; ?>

					</div><!-- Content column end -->	
					
					<!-- Sidebar column start -->
					<div class="col-sm-4 col-md-3 col-md-offset-1 sidebar">

						<?php do_action( 'shop_isle_sidebar' ); ?>

					</div>
					<!-- Sidebar column end -->
					
				</div><!-- .row -->

			</div>
		</section>
		<!-- Post single end -->


<?php get_footer(); ?>
