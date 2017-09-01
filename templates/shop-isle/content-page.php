<?php
/**
 * Template part for displaying page content in page.php.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage Shop Isle
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php

	/*
	 * @hooked shop_isle_page_content - 20
	 */
	do_action( 'shop_isle_page' );
	?>
</article><!-- #post-## -->
