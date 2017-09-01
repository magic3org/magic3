<?php
/**
 * Template functions used for posts.
 *
 * @package WordPress
 * @subpackage Shop Isle
 */

if ( ! function_exists( 'shop_isle_post_header' ) ) {
	/**
	 * Display the post header with a link to the single post
	 *
	 * @since 1.0.0
	 */
	function shop_isle_post_header() {
	?>
		<div class="post-header font-alt">
			<h2 class="post-title">
				<a href="<?php echo esc_url( get_permalink() ); ?>" rel="bookmark"><?php the_title(); ?></a>
			</h2>
		</div>

		<?php
	}
}

if ( ! function_exists( 'shop_isle_post_content' ) ) {
	/**
	 * Display the post content with a link to the single post
	 *
	 * @since 1.0.0
	 */
	function shop_isle_post_content() {
		?>
		<div class="post-entry" itemprop="articleBody">
		<?php
		the_content(
			sprintf(
				/* translators: s: post title */
				__( 'Continue reading %s', 'shop-isle' ),
				'<span class="screen-reader-text">' . get_the_title() . '</span>'
			)
		);

		wp_link_pages(
			array(
				'before' => '<div class="page-links">' . __( 'Pages:', 'shop-isle' ),
				'after'  => '</div>',
			)
		);
		?>
		</div><!-- .entry-content -->

		<?php
			$tags_list = get_the_tag_list( '', esc_html__( ', ', 'shop-isle' ) );
		if ( $tags_list ) {
			printf(
				/* translators: s: post title */
				'<span class="tags-links">' . esc_html__( 'Tagged %1$s', 'shop-isle' ) . '</span>',
				$tags_list
			);
		}
		?>

		<?php
	}
} // End if().

if ( ! function_exists( 'shop_isle_post_meta' ) ) {
	/**
	 * Display the post meta
	 *
	 * @since 1.0.0
	 */
	function shop_isle_post_meta() {
	?>
		<div class="post-header font-alt">
			<div class="post-meta"><?php shop_isle_posted_on(); ?></div>
		</div>
	<?php
	}
}

if ( ! function_exists( 'shop_isle_paging_nav' ) ) {
	/**
	 * Display navigation to next/previous set of posts when applicable.
	 */
	function shop_isle_paging_nav() {
		echo '<div class="clear"></div>';
		?>
		<nav class="navigation paging-navigation" role="navigation">
			<h1 class="screen-reader-text"><?php _e( 'Posts navigation', 'shop-isle' ); ?></h1>
			<div class="nav-links">
				<?php if ( get_next_posts_link() ) : ?>
					<div class="nav-previous"><?php next_posts_link( __( '<span class="meta-nav">&larr;</span> Older posts', 'shop-isle' ) ); ?></div>
				<?php endif; ?>
				<?php if ( get_previous_posts_link() ) : ?>
					<div class="nav-next"><?php previous_posts_link( __( 'Newer posts <span class="meta-nav">&rarr;</span>', 'shop-isle' ) ); ?></div>
				<?php endif; ?>
			</div><!-- .nav-links -->
		</nav><!-- .navigation -->
		<?php
	}
}

if ( ! function_exists( 'shop_isle_post_nav' ) ) {
	/**
	 * Display navigation to next/previous post when applicable.
	 */
	function shop_isle_post_nav() {
		$args = array(
			'next_text' => '%title &nbsp;<span class="meta-nav">&rarr;</span>',
			'prev_text' => '<span class="meta-nav">&larr;</span>&nbsp;%title',
		);
		the_post_navigation( $args );
	}
}

if ( ! function_exists( 'shop_isle_posted_on' ) ) {
	/**
	 * Prints HTML with meta information for the current post-date/time and author.
	 */
	function shop_isle_posted_on() {
		$shop_isle_post_author = get_the_author();

		if ( ! empty( $shop_isle_post_author ) ) :
			echo __( 'By ','shop-isle' ) . '<a href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '">' . esc_html( get_the_author() ) . '</a> | ';
		endif;

		$time_string = '<time class="entry-date published updated" datetime="%1$s" itemprop="datePublished">%2$s</time>';

		$time_string = sprintf(
			$time_string,
			esc_attr( get_the_date( 'c' ) ),
			esc_html( get_the_date() ),
			esc_attr( get_the_modified_date( 'c' ) ),
			esc_html( get_the_modified_date() )
		);

		if ( ! empty( $time_string ) ) :
			echo '<a href="' . esc_url( get_day_link( get_post_time( 'Y' ), get_post_time( 'm' ), get_post_time( 'j' ) ) ) . '" rel="bookmark">' . $time_string . '</a> | ';
		endif;

		$shop_isle_num_comments = get_comments_number();

		if ( $shop_isle_num_comments == 0 ) {
			$shop_isle_comments = __( 'No Comments', 'shop-isle' );
		} elseif ( $shop_isle_num_comments > 1 ) {
			$shop_isle_comments = $shop_isle_num_comments . __( ' Comments','shop-isle' );
		} else {
			$shop_isle_comments = __( '1 Comment','shop-isle' );
		}
		if ( ! empty( $shop_isle_comments ) ) :
			echo '<a href="' . esc_url( get_comments_link() ) . '">' . esc_html( $shop_isle_comments ) . '</a> | ';
		endif;

		$shop_isle_categories = get_the_category();
		$separator = ', ';
		$shop_isleoutput = '';
		if ( $shop_isle_categories ) {
			foreach ( $shop_isle_categories as $shop_isle_category ) {
				$shop_isleoutput .= '<a href="' . esc_url( get_category_link( $shop_isle_category->term_id ) ) . '" title="' . esc_attr(
					sprintf(
						/* translators: s: category name */
						 __( 'View all posts in %s', 'shop-isle' ), $shop_isle_category->name
					)
				) . '">' . esc_html( $shop_isle_category->cat_name ) . '</a>' . $separator;
			}
			echo trim( $shop_isleoutput, $separator );
		}

	}
}// End if().
