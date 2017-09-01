<?php
/**
 * Jetpack Compatibility File
 * See: http://jetpack.me/
 *
 * @package WordPress
 * @subpackage Shop Isle
 */

/**
 * Add theme support for Infinite Scroll.
 * See: http://jetpack.me/support/infinite-scroll/
 */
function shop_isle_jetpack_setup() {
	add_theme_support(
		'infinite-scroll', array(
			'container' => 'shop-isle-blog-container',
			'footer'    => 'page',
		)
	);
}
add_action( 'after_setup_theme', 'shop_isle_jetpack_setup' );
