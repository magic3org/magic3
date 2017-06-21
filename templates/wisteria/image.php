<?php
/**
 * The template for displaying image attachments
 *
 * @package Wisteria
 */

get_header(); ?>

	<div class="container">
		<div class="row">

			<section id="primary" class="content-area <?php wisteria_layout_class( 'content' ); ?>">
				<main id="main" class="site-main">

					<?php while ( have_posts() ) : the_post(); ?>

						<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

							<?php
							// Retrieve attachment metadata.
							$metadata = wp_get_attachment_metadata();
							?>

							<div class="entry-header-wrapper">
								<header class="entry-header">
									<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
								</header><!-- .entry-header -->

								<div class="entry-meta entry-meta-header-after">
									<ul>
										<li>
											<span class="posted-on">
												<time class="entry-date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
													<?php echo esc_html( get_the_date() ); ?>
												</time>
											</span>
										</li>
										<?php if ( $post->post_parent ) : ?>
										<li>
											<span class="parent-post-link">
												<a href="<?php echo esc_url( get_permalink( $post->post_parent ) ); ?>" rel="gallery">
													<?php echo esc_html( get_the_title( $post->post_parent ) ); ?>
												</a>
											</span>
										</li>
										<?php endif; ?>
										<li>
											<span class="full-size-link">
												<a href="<?php echo esc_url( wp_get_attachment_url() ); ?>">
													<?php echo esc_html( $metadata['width'] ); ?> &times; <?php echo esc_html( $metadata['height'] ); ?>
												</a>
											</span>
										</li>
									</ul>
								</div><!-- .entry-meta -->
							</div><!-- .entry-header-wrapper -->

							<div class="entry-content">
								<div class="entry-attachment">
									<div class="attachment">
										<?php wisteria_the_attached_image(); ?>
									</div><!-- .attachment -->

									<?php if ( has_excerpt() ) : ?>
									<div class="entry-caption">
										<?php the_excerpt(); ?>
									</div><!-- .entry-caption -->
									<?php endif; ?>
								</div><!-- .entry-attachment -->

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
								<?php edit_post_link( esc_html__( 'Edit', 'wisteria' ), '<span class="edit-link">', '</span>' ); ?>
							</footer><!-- .entry-meta -->
							<?php endif; ?>

						</article><!-- #post-## -->

						<nav id="image-navigation" class="navigation image-navigation">
							<div class="nav-links">
								<div class="previous-image nav-previous"><?php previous_image_link( false, esc_html__( 'Previous Image', 'wisteria' ) ); ?></div>
								<div class="next-image nav-next"><?php next_image_link( false, esc_html__( 'Next Image', 'wisteria' ) ); ?></div>
							</div><!-- .nav-links -->
						</nav><!-- #image-navigation -->

						<?php
							// If comments are open or we have at least one comment, load up the comment template
							if ( comments_open() || '0' != get_comments_number() ) :
								comments_template();
							endif;
						?>

					<?php endwhile; // end of the loop. ?>

				</main><!-- #main -->
			</section><!-- #primary -->

			<?php get_sidebar(); ?>

		</div><!-- .row -->
	</div><!-- .container -->

<?php get_footer(); ?>
