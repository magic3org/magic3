<?php
/**
 * Template Name: Page Builder Blank
 *
 * The template for the page builder blank.
 *
 * @package Shop Isle
 * @author Themeisle
 */ ?>

<?php
shop_isle_no_content_get_header();
do_action( 'shop_isle_page_builder_blank_before_content' );
if ( have_posts() ) :
	while ( have_posts() ) :
		the_post();
		get_template_part( 'content', 'pagebuilder' );
	endwhile;
endif;
do_action( 'shop_isle_page_builder_blank_after_content' );
shop_isle_no_content_get_footer();
