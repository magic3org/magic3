<?php
/**
 * Shop Isle WooCommerce Hooks
 *
 * @package WordPress
 * @subpackage Shop Isle
 */

/**
 * Styles
 *
 * @see  shop_isle_woocommerce_scripts()
 */
add_action( 'wp_enqueue_scripts',           'shop_isle_woocommerce_scripts',        20 );
add_filter( 'woocommerce_enqueue_styles',   '__return_empty_array' );

/**
 * Layout
 *
 * @see  shop_isle_before_content()
 * @see  shop_isle_after_content()
 * @see  woocommerce_breadcrumb()
 * @see  shop_isle_shop_messages()
 */

remove_action( 'woocommerce_before_main_content',   'woocommerce_breadcrumb',                   20, 0 );
remove_action( 'woocommerce_before_main_content',   'woocommerce_output_content_wrapper',       10 );
remove_action( 'woocommerce_after_main_content',    'woocommerce_output_content_wrapper_end',   10 );
remove_action( 'woocommerce_sidebar',               'woocommerce_get_sidebar',                  10 );
remove_action( 'woocommerce_before_shop_loop',      'woocommerce_result_count',                 20 );
remove_action( 'woocommerce_before_shop_loop',      'woocommerce_catalog_ordering',             30 );
remove_action( 'woocommerce_after_shop_loop',       'woocommerce_pagination',                   10 );
remove_action( 'woocommerce_archive_description',   'woocommerce_product_archive_description',  10 );





remove_action( 'woocommerce_before_shop_loop_item_title',   'woocommerce_template_loop_product_thumbnail', 10 );
add_action( 'woocommerce_before_shop_loop_item_title',   'shop_isle_loop_product_thumbnail', 20 );





add_action( 'shop_isle_before_shop',                'shop_isle_woocommerce_product_archive_description',    5 );

add_action( 'woocommerce_before_main_content',      'shop_isle_before_content',                 10 );

add_action( 'woocommerce_before_shop_loop',         'shop_isle_shop_page_wrapper',              20 );

add_action( 'shop_isle_content_top',                'shop_isle_shop_messages',                  21 );

add_action( 'woocommerce_after_shop_loop',          'shop_isle_sorting_wrapper',                23 );
add_action( 'woocommerce_after_shop_loop',          'shop_isle_woocommerce_pagination',         24 );
add_action( 'woocommerce_after_shop_loop',          'shop_isle_sorting_wrapper_close',          25 );
add_action( 'woocommerce_after_shop_loop',          'shop_isle_shop_page_wrapper_end',          40 );

add_action( 'woocommerce_after_main_content',       'shop_isle_after_content',                  50 );

add_filter( 'woocommerce_page_title', 'shop_isle_header_shop_page' );







/* WooCommerce Search Products Page - No results */
add_action( 'woocommerce_archive_description',              'shop_isle_search_products_no_results_wrapper',      10 );
add_action( 'woocommerce_after_main_content',               'shop_isle_search_products_no_results_wrapper_end',  10 );

/**
 * Products
 *
 * @see  shop_isle_upsell_display()
 */
remove_action( 'woocommerce_before_single_product',         'action_woocommerce_before_single_product', 10, 1 );
remove_action( 'woocommerce_after_single_product',          'action_woocommerce_after_single_product', 10, 1 );

add_action( 'woocommerce_before_single_product',            'shop_isle_product_page_wrapper', 10, 1 );
add_action( 'woocommerce_before_single_product',            'woocommerce_breadcrumb', 11 );
add_action( 'woocommerce_after_single_product',             'shop_isle_product_page_wrapper_end', 10, 1 );

remove_action( 'woocommerce_after_single_product_summary',  'woocommerce_upsell_display',               15 );
add_action( 'woocommerce_after_single_product_summary',     'shop_isle_upsell_display',                 15 );
remove_action( 'woocommerce_before_shop_loop_item_title',   'woocommerce_show_product_loop_sale_flash', 10 );
add_action( 'woocommerce_after_shop_loop_item_title',       'woocommerce_show_product_loop_sale_flash', 6 );
add_action( 'woocommerce_after_shop_loop_item_title',       'shop_isle_outofstock_notify_on_archives', 10 );

/* add products slider */
add_action( 'woocommerce_after_single_product',             'shop_isle_products_slider_on_single_page', 10, 0 );

/* notices */
remove_action( 'woocommerce_before_single_product',         'wc_print_notices', 10 );
add_action( 'woocommerce_before_single_product',            'wc_print_notices', 60 );

/**
 * Filters
 *
 * @see  shop_isle_woocommerce_body_class()
 * @see  shop_isle_cart_link_fragment()
 * @see  shop_isle_thumbnail_columns()
 * @see  shop_isle_related_products_args()
 * @see  shop_isle_products_per_page()
 * @see  shop_isle_loop_columns()
 */
add_filter( 'body_class',                               'shop_isle_woocommerce_body_class' );
add_filter( 'woocommerce_product_thumbnails_columns',   'shop_isle_thumbnail_columns' );
add_filter( 'woocommerce_output_related_products_args', 'shop_isle_related_products_args' );
add_filter( 'loop_shop_per_page',                       'shop_isle_products_per_page' );
add_filter( 'loop_shop_columns',                        'shop_isle_loop_columns' );

if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.3', '>=' ) ) {
	add_filter( 'woocommerce_add_to_cart_fragments', 'shop_isle_cart_link_fragment' );
} else {
	add_filter( 'add_to_cart_fragments', 'shop_isle_cart_link_fragment' );
}

/**
 * Integrations
 *
 * @see  shop_isle_woocommerce_integrations_scripts()
 */
add_action( 'wp_enqueue_scripts', 'shop_isle_woocommerce_integrations_scripts' );

/**
* Cart page
*/
add_filter( 'woocommerce_cart_item_thumbnail', 'shop_isle_cart_item_thumbnail', 10, 3 );

/* WooCommerce compare list plugin */
if ( function_exists( 'wccm_render_catalog_compare_info' ) ) {

	remove_action( 'woocommerce_before_shop_loop', 'wccm_render_catalog_compare_info' );

	add_action( 'woocommerce_before_shop_loop', 'wccm_render_catalog_compare_info', 30 );

	add_action( 'shop_isle_wccm_compare_list','wccm_render_catalog_compare_info' );
}

if ( function_exists( 'wccm_add_single_product_compare_buttton' ) ) {

	remove_action( 'woocommerce_single_product_summary', 'wccm_add_single_product_compare_buttton', 35 );

	add_action( 'woocommerce_product_meta_end', 'wccm_add_single_product_compare_buttton', 35 );
}

add_filter( 'woocommerce_widget_cart_is_hidden', 'shop_isle_always_show_live_cart', 40, 0 );

add_action( 'woocommerce_before_single_product_summary','shop_isle_outofstock_notify_on_archives',20 );
