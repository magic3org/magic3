<?php
/**
 * The main template file.
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage Shop Isle
 */
?>
<?php get_header(); ?>

	<!-- Wrapper start -->
	<div class="main">

	<!-- Header section start -->
<?php
$shop_isle_header_image = get_header_image();
if ( ! empty( $shop_isle_header_image ) ) :
	echo '<section class="page-module-content module bg-dark" data-background="' . esc_url( $shop_isle_header_image ) . '">';
else :
	echo '<section class="page-module-content module bg-dark">';
endif;
?>
	<div class="container">

		<div class="row">

			<div class="col-sm-6 col-sm-offset-3">

				<h1 class="module-title font-alt"><?php the_title(); ?></h1>

				<?php

				/* Header description */

				$shop_isle_shop_id = get_the_ID();

				if ( ! empty( $shop_isle_shop_id ) ) :

					$shop_isle_page_description = get_post_meta( $shop_isle_shop_id, 'shop_isle_page_description' );

					if ( ! empty( $shop_isle_page_description[0] ) ) :
						echo '<div class="module-subtitle font-serif mb-0">' . wp_kses_post( $shop_isle_page_description[0] ) . '</div>';
					endif;

				endif;
				?>

			</div><!-- .col-sm-6 col-sm-offset-3 -->

		</div><!-- .row -->

	</div><!-- .container -->

<?php
echo '</section><!-- .module -->';
?>
	<!-- Header section end -->

	<!-- Blog standar start -->
<?php
$shop_isle_posts_per_page = get_option( 'posts_per_page' ); /* number of latest posts to show */

if ( ! empty( $shop_isle_posts_per_page ) && ($shop_isle_posts_per_page > 0) ) :

	$shop_isle_query = new WP_Query(
		array(
			'post_type' => 'post',
			'posts_per_page' => $shop_isle_posts_per_page,
			'paged' => ( get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1 ),
		)
	);



	if ( have_posts() ) {

		?>
		<section class="module">
			<div class="container">

				<div class="row">

					<!-- Content column start -->
					<div class="col-sm-8" id="shop-isle-blog-container">
						<?php

						while ( $shop_isle_query->have_posts() ) {
							$shop_isle_query->the_post();

							?>
							<div id="post-<?php the_ID(); ?>" <?php post_class( 'post' ); ?> itemscope="" itemtype="http://schema.org/BlogPosting">

								<?php
								if ( has_post_thumbnail() ) {
									echo '<div class="post-thumbnail">';
									echo '<a href="' . esc_url( get_permalink() ) . '">';
									echo get_the_post_thumbnail( $post->ID, 'shop_isle_blog_image_size' );
									echo '</a>';
									echo '</div>';
								}
								?>

								<div class="post-header font-alt">
									<h2 class="post-title"><a href="<?php echo esc_url( get_permalink() ); ?>"><?php the_title(); ?></a></h2>
									<div class="post-meta">
										<?php
										shop_isle_posted_on();
										?>

									</div>
								</div>

								<div class="post-entry">
									<?php
									$shop_isleismore = strpos( $post->post_content, '<!--more-->' );
									if ( $shop_isleismore ) :
										the_content();
									else :
										the_excerpt();
									endif;
									?>
								</div>

								<div class="post-more">
									<a href="<?php echo esc_url( get_permalink() ); ?>" class="more-link"><?php esc_html_e( 'Read more','shop-isle' ); ?></a>
								</div>

							</div>
							<?php

						}// End while().

						?>

						<!-- Pagination start-->
						<div class="pagination font-alt">
							<?php next_posts_link( __( '<span class="meta-nav">&laquo;</span> Older posts', 'shop-isle' ), $shop_isle_query->max_num_pages ); ?>
							<?php previous_posts_link( __( 'Newer posts <span class="meta-nav">&raquo;</span>', 'shop-isle' ), $shop_isle_query->max_num_pages ); ?>
						</div>
						<!-- Pagination end -->
					</div>
					<!-- Content column end -->

					<!-- Sidebar column start -->
					<div class="col-sm-4 col-md-3 col-md-offset-1 sidebar">

						<?php do_action( 'shop_isle_sidebar' ); ?>

					</div>
					<!-- Sidebar column end -->

				</div><!-- .row -->

			</div>
		</section>
		<!-- Blog standar end -->

		<?php
		/* Restore original Post Data */
		wp_reset_postdata();
	}// End if().

endif;

?>

<?php get_footer(); ?>
