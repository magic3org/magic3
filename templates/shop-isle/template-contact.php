<?php
/**
 * The template for displaying contact page.
 *
 * Template Name: Contact page
 *
 * @package WordPress
 * @subpackage Shop Isle
 */

get_header(); ?>

<!-- Wrapper start -->
	<div class="main">

		<!-- Header section start -->
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

					<div class="col-sm-10 col-sm-offset-1">

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

					</div>

				</div><!-- .row -->

			</div>
		</section>
		<!-- Header section end -->

		<!-- Contact start -->
		
		<?php

		if ( have_posts() ) :

			while ( have_posts() ) :
				the_post();

				get_template_part( 'content', 'contact' );

			endwhile;

		endif;

		get_footer(); ?>
