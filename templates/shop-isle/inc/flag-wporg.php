<?php
/**
 * WordPress.org flag setup and frontpage template filter.
 *
 * @package WordPress
 * @subpackage Shop Isle
 */


/**
 * Function used for transition to PRO
 */
function shop_isle_option_used_for_pro() {
	$current_theme = get_stylesheet();

	if ( $current_theme != 'shop-isle-pro' ) {
		update_option( 'shop_isle_wporg_flag', 'true' );
	}
}

add_action( 'init', 'shop_isle_option_used_for_pro' );

/*
 * Starter Content Support
 */
add_theme_support(
	'starter-content', array(
		'posts'     => array(
			'home',
			'blog',
		),
		'nav_menus' => array(
			'primary' => array(
				'name'  => __( 'Primary Menu', 'shop-isle' ),
				'items' => array(
					'page_home',
					'page_blog',
				),
			),
		),
		'options'   => array(
			'show_on_front'  => 'page',
			'page_on_front'  => '{{home}}',
			'page_for_posts' => '{{blog}}',
		),
	)
);
