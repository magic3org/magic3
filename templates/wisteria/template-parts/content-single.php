<?php
/**
 * Template part for displaying single posts.
 *
 * @package Wisteria
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<div class="entry-header-wrapper">
		<div class="entry-meta entry-meta-header-before">
			<ul>
				<li><?php wisteria_posted_on(); ?></li>
				<li><?php wisteria_posted_by(); ?></li>
				<?php wisteria_post_format( '<li>', '</li>' ); ?>
			</ul>
		</div><!-- .entry-meta -->

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

	<footer class="entry-meta entry-meta-footer">
		<?php wisteria_entry_footer(); ?>
	</footer><!-- .entry-meta -->
</article><!-- #post-## -->
