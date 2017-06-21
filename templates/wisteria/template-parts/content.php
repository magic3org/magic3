<?php
/**
 * The default template for displaying content
 *
 * @package Wisteria
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<div class="post-content-wrapper">

		<?php wisteria_post_thumbnail(); ?>

		<div class="entry-data-wrapper">
			<div class="entry-header-wrapper entry-header-wrapper-archive">
				<?php if ( 'post' == get_post_type() ) : // For Posts ?>
				<div class="entry-meta entry-meta-header-before">
					<ul>
						<li><?php wisteria_post_first_category(); ?></li>
						<?php wisteria_post_format( '<li>', '</li>' ); ?>
						<?php if ( is_sticky() && is_home() && ! is_paged() ) : ?>
						<li>
							<span class="post-label post-label-featured">
								<span class="screen-reader-text"><?php esc_html_e( 'Featured', 'wisteria' ); ?></span>
							</span>
						</li>
						<?php endif; ?>
					</ul>
				</div><!-- .entry-meta -->
				<?php endif; ?>

				<header class="entry-header">
					<?php the_title( '<h1 class="entry-title"><a href="' . esc_url( wisteria_get_link_url() ) . '" rel="bookmark">', '</a></h1>' ); ?>
				</header><!-- .entry-header -->

				<?php if ( 'post' == get_post_type() ) : // For Posts ?>
				<div class="entry-meta entry-meta-header-after">
					<ul>
						<li><?php wisteria_posted_on(); ?></li>
						<li><?php wisteria_posted_by(); ?></li>
					</ul>
				</div><!-- .entry-meta -->
				<?php endif; ?>
			</div><!-- .entry-header-wrapper -->

			<?php if ( wisteria_has_excerpt() ) : ?>
			<div class="entry-summary">
				<?php the_excerpt(); ?>
			</div><!-- .entry-summary -->
			<?php endif; ?>
		</div><!-- .entry-data-wrapper -->

	</div><!-- .post-content-wrapper -->
</article><!-- #post-## -->
