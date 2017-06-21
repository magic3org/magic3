<?php
/**
 * The template used for displaying page content in page.php
 *
 * @package Wisteria
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<div class="entry-header-wrapper">
		<header class="entry-header">
			<?php the_title( '<h1 class="entry-title entry-title-single">', '</h1>' ); ?>
		</header><!-- .entry-header -->
	</div><!-- .entry-header-wrapper -->

	<div class="entry-content">
		<?php the_content(); ?>
		<?php
			wp_link_pages( array(
				'before'      => '<div class="page-links"><span class="page-links-title">' . esc_html__( 'Pages:', 'wisteria' ) . '</span>',
				'after'       => '</div>',
				'link_before' => '<span>',
				'link_after'  => '</span>',
			) );
		?>
	</div><!-- .entry-content -->

	<?php if ( '' != get_edit_post_link() ) : ?>
	<footer class="entry-meta entry-meta-footer">
		<?php wisteria_entry_footer(); ?>
	</footer><!-- .entry-meta -->
	<?php endif; ?>
</article><!-- #post-## -->
