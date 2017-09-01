<?php
/**
 * Template functions used for the site comments.
 *
 * @package WordPress
 * @subpackage Shop Isle
 */

if ( ! function_exists( 'shop_isle_display_comments' ) ) {
	/**
	 * Display comments
	 *
	 * @since  1.0.0
	 */
	function shop_isle_display_comments() {
		// If comments are open or we have at least one comment, load up the comment template
		if ( comments_open() || '0' != get_comments_number() ) :
			comments_template();
		endif;
	}
}

if ( ! function_exists( 'shop_isle_comment' ) ) {
	/**
	 * Comment template
	 *
	 * @since 1.0.0
	 */
	function shop_isle_comment( $comment, $args, $depth ) {
		if ( 'div' == $args['style'] ) {
			$tag = 'div';
			$add_below = 'comment';
		} else {
			$tag = 'li';
			$add_below = 'div-comment';
		}
		?>
		<<?php echo esc_attr( $tag ); ?> <?php comment_class( empty( $args['has_children'] ) ? '' : 'parent' ); ?> id="comment-<?php comment_ID(); ?>">
		<div class="comment-body">
			<div class="comment-meta commentmetadata">
				<div class="comment-author vcard">
					<?php
					echo get_avatar( $comment, 128 );
					/* translators: s: Comment author link */
					printf( __( '<cite class="fn">%s</cite>', 'shop-isle' ), get_comment_author_link() );
					?>
				</div>
				<?php if ( '0' == $comment->comment_approved ) : ?>
					<em class="comment-awaiting-moderation"><?php _e( 'Your comment is awaiting moderation.', 'shop-isle' ); ?></em>
					<br />
				<?php endif; ?>
			</div>
			<?php if ( 'div' != $args['style'] ) : ?>
				<div id="div-comment-<?php comment_ID(); ?>" class="comment-content">
			<?php endif; ?>

			<?php comment_text(); ?>

			<div class="comments-bottom-wrap">
				<a href="<?php echo esc_url( htmlspecialchars( get_comment_link( $comment->comment_ID ) ) ); ?>" class="comment-date">
					<?php echo '<time>' . get_comment_date() . '</time>'; ?>
				</a>
				<div class="reply">
					 &nbsp; - &nbsp;
					<?php
					comment_reply_link(
						array_merge(
							$args, array(
								'add_below' => $add_below,
								'depth' => $depth,
								'max_depth' => $args['max_depth'],
							)
						)
					);
						?>
					<?php edit_comment_link( __( 'Edit', 'shop-isle' ), '  ', '' ); ?>
				</div>
			</div>

		</div>
		<?php if ( 'div' != $args['style'] ) : ?>
			</div>
		<?php endif; ?>
	<?php
	}
}// End if().
