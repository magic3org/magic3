<?php
/**
 * The template for displaying full width pages.
 *
 * Template Name: Full width, no title (SiteOrigin Page builder Template)
 *
 * @package WordPress
 * @subpackage Shop Isle
 */

get_header(); ?>

	<!-- Wrapper start -->
	<div class="main">

	<!-- Pricing start -->
	<section class="module">
		<div class="container">

			<div class="row">

				<!-- Content column start -->
				<div class="col-sm-12">

					<?php
					/**
					 * Top of content hook.
					 *
					 * @hooked woocommerce_breadcrumb - 10
					 */
					do_action( 'shop_isle_content_top' );
					?>

					<?php
					while ( have_posts() ) :
						the_post();
?>

						<?php
						do_action( 'shop_isle_page_before' );
						?>

						<?php get_template_part( 'content', 'page' ); ?>

						<?php
						/**
						 * Bottom of content hook.
						 *
						 * @hooked shop_isle_display_comments - 10
						 */
						do_action( 'shop_isle_page_after' );
						?>

					<?php endwhile; ?>

				</div>

			</div> <!-- .row -->

		</div>
	</section>
	<!-- Pricing end -->


<?php get_footer(); ?>
