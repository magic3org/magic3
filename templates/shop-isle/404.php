<?php
/**
 * The template for displaying 404 pages (not found).
 *
 * @package WordPress
 * @subpackage Shop Isle
 */

get_header(); ?>
	<!-- Wrapper start -->
	<div class="main">

		<!-- Home start -->
		<?php

		$shop_isle_404_background = get_theme_mod( 'shop_isle_404_background', get_template_directory_uri() . '/assets/images/404.jpg' );

		if ( ! empty( $shop_isle_404_background ) ) :
			echo '<section class="home-section home-parallax home-fade home-full-height bg-dark error-page-background" data-background="' . esc_url( $shop_isle_404_background ) . '">';
		else :
			echo '<section class="home-section home-parallax home-fade home-full-height bg-dark error-page-background">';
		endif;
		?>
			<div class="hs-caption">
				<div class="caption-content">
					<?php
						/* title */
						$shop_isle_404_title = get_theme_mod( 'shop_isle_404_title',__( 'Error 404', 'shop-isle' ) );
					if ( ! empty( $shop_isle_404_title ) ) :
						echo '<div class="hs-title-size-4 font-alt mb-30 error-page-title">';
						echo shop_isle_sanitize_text( $shop_isle_404_title );
						echo '</div>';
						endif;

						/* text */
						$shop_isle_404_text = get_theme_mod( 'shop_isle_404_text','The requested URL was not found on this server.<br> That is all we know.' );
					if ( ! empty( $shop_isle_404_text ) ) :
						echo '<div class="font-alt error-page-text">';
						echo shop_isle_sanitize_text( $shop_isle_404_text );
						echo '</div>';
						endif;

						/* button */
						$shop_isle_404_link = get_theme_mod( 'shop_isle_404_link','#' );
						$shop_isle_404_label = get_theme_mod( 'shop_isle_404_label',__( 'Back to home page', 'shop-isle' ) );

					if ( ! empty( $shop_isle_404_link ) && ! empty( $shop_isle_404_label ) ) :
						echo '<div class="font-alt mt-30 error-page-button-text">';
						echo '<a href="' . esc_url( $shop_isle_404_link ) . '" class="btn btn-border-w btn-round">' . esc_html( $shop_isle_404_label ) . '</a>';
						echo '</div>';
						endif;
					?>
				</div>
			</div>

		</section >
		<!-- Home end -->

<?php get_footer(); ?>
