<?php
/**
 * Extras functions for page builders
 *
 * @package Shop Isle
 * @author Themeisle
 */

/**
 * Header for page builder blank template
 */
function shop_isle_no_content_get_header() {
	?>
	<!DOCTYPE html>
	<html <?php language_attributes(); ?> <?php shop_isle_html_tag_schema(); ?>>
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
		<link rel="profile" href="http://gmpg.org/xfn/11">
		<?php wp_head(); ?>
	</head>

	<body <?php body_class(); ?>>
	<?php
	do_action( 'shop_isle_page_builder_content_body_before' );
}

/**
 * Footer for page builder blank template
 */
function shop_isle_no_content_get_footer() {
	do_action( 'shop_isle_page_builder_content_body_after' );
	wp_footer();
	?>
	</body>
	</html>
	<?php
}

/**
 * Add header and footer support for beaver.
 *
 * @access public
 */
function shop_isle_header_footer_render() {
	if ( ! class_exists( 'FLThemeBuilderLayoutData' ) ) {
		return;
	}
	// Get the header ID.
	$header_ids = FLThemeBuilderLayoutData::get_current_page_header_ids();
	// If we have a header, remove the theme header and hook in Theme Builder's.
	if ( ! empty( $header_ids ) ) {
		remove_action( 'shop_isle_header', 'shop_isle_primary_navigation', 50 );
		add_action( 'shop_isle_header', 'FLThemeBuilderLayoutRenderer::render_header', 50 );
	}
	// Get the footer ID.
	$footer_ids = FLThemeBuilderLayoutData::get_current_page_footer_ids();
	// If we have a footer, remove the theme footer and hook in Theme Builder's.
	if ( ! empty( $footer_ids ) ) {
		remove_action( 'shop_isle_footer', 'shop_isle_footer_wrap_open',                    5 );
		remove_action( 'shop_isle_footer', 'shop_isle_footer_widgets',                      10 );
		remove_action( 'shop_isle_footer', 'shop_isle_footer_copyright_and_socials',        20 );
		remove_action( 'shop_isle_footer', 'shop_isle_footer_wrap_close',                   30 );

		add_action( 'shop_isle_footer', 'FLThemeBuilderLayoutRenderer::render_footer' );
	}
}
add_action( 'wp', 'shop_isle_header_footer_render' );
/**
 * Add theme support for header and footer.
 *
 * @access public
 */
function shop_isle_header_footer_support() {
	add_theme_support( 'fl-theme-builder-headers' );
	add_theme_support( 'fl-theme-builder-footers' );
}
add_action( 'after_setup_theme', 'shop_isle_header_footer_support' );
