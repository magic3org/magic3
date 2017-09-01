<?php
/**
 * Template Name: Page Builder Full Width
 *
 * The template for the page builder full-width.
 *
 * It contains header, footer and 100% content width.
 *
 * @package Shop Isle
 * @author Themeisle
 */
get_header(); ?>

<?php do_action( 'shop_isle_page_builder_full_before_content' ); ?>

	<div class="main">
		<?php
		if ( have_posts() ) :
			while ( have_posts() ) :
				the_post();
				get_template_part( 'content', 'pagebuilder' );
			endwhile;
		endif;
		?>
	</div>

<?php do_action( 'shop_isle_page_builder_full_after_content' ); ?>

<?php get_footer(); ?>
