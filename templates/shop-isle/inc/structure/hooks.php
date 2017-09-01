<?php
/**
 * Shop Isle Hooks
 *
 * @package WordPress
 * @subpackage Shop Isle
 */

/**
 * General
 *
 * @see  shop_isle_setup()
 * @see  shop_isle_widgets_init()
 * @see  shop_isle_scripts()
 * @see  shop_isle_get_sidebar()
 */
add_action( 'after_setup_theme',                'shop_isle_setup' );
add_action( 'widgets_init',                     'shop_isle_widgets_init' );
add_action( 'wp_enqueue_scripts',               'shop_isle_scripts',                    10 );
add_action( 'admin_enqueue_scripts',            'shop_isle_admin_styles',               10 );
add_action( 'shop_isle_sidebar',                'shop_isle_get_sidebar',                10 );
add_action( 'shop_isle_sidebar_shop_archive',   'shop_isle_get_sidebar_shop_archive',   10 );


/**
 * Header
 *
 * @see  shop_isle_primary_navigation()
 */
add_action( 'shop_isle_header', 'shop_isle_primary_navigation',     50 );

/**
 * Footer
 *
 * @see  shop_isle_footer_widgets()
 * @see  shop_isle_footer_copyright_and_socials()
 */
add_action( 'shop_isle_footer', 'shop_isle_footer_wrap_open',                   5 );
add_action( 'shop_isle_footer', 'shop_isle_footer_widgets',                     10 );
add_action( 'shop_isle_footer', 'shop_isle_footer_copyright_and_socials',       20 );
add_action( 'shop_isle_footer', 'shop_isle_footer_wrap_close',                  30 );

/**
 * Homepage
 *
 * @see  shop_isle_homepage_content()
 * @see  shop_isle_product_categories()
 * @see  shop_isle_recent_products()
 * @see  shop_isle_featured_products()
 * @see  shop_isle_popular_products()
 * @see  shop_isle_on_sale_products()
 */
add_action( 'homepage', 'shop_isle_homepage_content',       10 );
add_action( 'homepage', 'shop_isle_product_categories',     20 );
add_action( 'homepage', 'shop_isle_recent_products',        30 );
add_action( 'homepage', 'shop_isle_featured_products',      40 );
add_action( 'homepage', 'shop_isle_popular_products',       50 );
add_action( 'homepage', 'shop_isle_on_sale_products',       60 );

/**
 * Posts
 *
 * @see  shop_isle_post_header()
 * @see  shop_isle_post_meta()
 * @see  shop_isle_post_content()
 * @see  shop_isle_paging_nav()
 * @see  shop_isle_post_nav()
 * @see  shop_isle_display_comments()
 */
add_action( 'shop_isle_loop_post',          'shop_isle_post_header',        10 );
add_action( 'shop_isle_loop_post',          'shop_isle_post_meta',          20 );
add_action( 'shop_isle_loop_post',          'shop_isle_post_content',       30 );
add_action( 'shop_isle_loop_after',         'shop_isle_paging_nav',         10 );
add_action( 'shop_isle_single_post',        'shop_isle_post_header',        10 );
add_action( 'shop_isle_single_post',        'shop_isle_post_meta',          20 );
add_action( 'shop_isle_single_post',        'shop_isle_post_content',       30 );
add_action( 'shop_isle_single_post_after',  'shop_isle_post_nav',           10 );
add_action( 'shop_isle_single_post_after',  'shop_isle_display_comments',   10 );

/**
 * Pages
 *
 * @see  shop_isle_page_content()
 * @see  shop_isle_display_comments()
 */
add_action( 'shop_isle_page',           'shop_isle_page_content',       20 );
add_action( 'shop_isle_page_after',     'shop_isle_display_comments',   10 );

/**
 * Extras
 *
 * @see  shop_isle_body_classes()
 * @see  shop_isle_page_menu_args()
 */
add_filter( 'body_class',           'shop_isle_body_classes' );
add_filter( 'wp_page_menu_args',    'shop_isle_page_menu_args' );

/**
 * Customize
 *
 * @see  shop_isle_customize_preview_js()
 * @see  shop_isle_customize_register()
 * @see  shop_isle_customizer_script()
 */
	add_action( 'customize_preview_init',               'shop_isle_customize_preview_js' );
	add_action( 'customize_register',                   'shop_isle_customize_register' );
	add_action( 'customize_controls_enqueue_scripts',   'shop_isle_customizer_script' );


/**
 * Shop page
 */
add_action( 'shop_isle_before_shop',        'shop_isle_woocommerce_breadcrumb',         10 );
add_action( 'shop_isle_before_shop',        'woocommerce_catalog_ordering',             20 );


/**
 * Define image sizes
 */
function shop_isle_woocommerce_image_dimensions() {
	global $pagenow;

	if ( ! isset( $_GET['activated'] ) || $pagenow != 'themes.php' ) {
		return;
	}
		$catalog = array(
			'width'     => '262',   // px
		'height'    => '325',   // px
		'crop'      => 1,
		);
	$single = array(
		'width'     => '555',   // px
		'height'    => '688',   // px
		'crop'      => 1,
	);
	$thumbnail = array(
		'width'     => '83',    // px
		'height'    => '103',   // px
		'crop'      => 1,
	);
	// Image sizes
	update_option( 'shop_catalog_image_size', $catalog );       // Product category thumbs
	update_option( 'shop_single_image_size', $single );         // Single product image
	update_option( 'shop_thumbnail_image_size', $thumbnail );   // Image gallery thumbs
}
add_action( 'after_switch_theme', 'shop_isle_woocommerce_image_dimensions', 1 );

/**
 * Number of thumbnails per row in product galleries
 *
 * @return int
 */
function shop_isle_thumb_cols() {
	return 6;
}
add_filter( 'woocommerce_product_thumbnails_columns', 'shop_isle_thumb_cols', 99 );

