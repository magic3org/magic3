<?php
/**
 * Custom functions that act independently of the theme templates
 *
 * Eventually, some of the functionality here could be replaced by core features
 *
 * @package shop-isle
 */

/**
 * Get our wp_nav_menu() fallback, wp_page_menu(), to show a home link.
 *
 * @param array $args Configuration arguments.
 * @return array
 */
function shop_isle_page_menu_args( $args ) {
	$args['show_home'] = true;
	return $args;
}

/**
 * Adds custom classes to the array of body classes.
 *
 * @param array $classes Classes for the body element.
 * @return array
 */
function shop_isle_body_classes( $classes ) {
	// Adds a class of group-blog to blogs with more than 1 published author.
	if ( is_multi_author() ) {
		$classes[] = 'group-blog';
	}

	if ( ! function_exists( 'woocommerce_breadcrumb' ) ) {
		$classes[]  = 'no-wc-breadcrumb';
	}

	/**
	 * What is this?!
	 * Take the blue pill, close this file and forget you saw the following code.
	 * Or take the red pill, filter shop_isle_make_me_cute and see how deep the rabbit hole goes...
	 */
	$cute   = apply_filters( 'shop_isle_make_me_cute', false );

	if ( true === $cute ) {
		$classes[] = 'shop-isle-cute';
	}

	return $classes;
}

if ( ! function_exists( 'is_woocommerce_activated' ) ) {

	/**
	 * Query WooCommerce activation
	 */
	function is_woocommerce_activated() {
		return class_exists( 'woocommerce' ) ? true : false;
	}
}

/**
 * Schema type
 */
function shop_isle_html_tag_schema() {
	$schema     = 'http://schema.org/';
	$type       = 'WebPage';

	// Is single post
	if ( is_singular( 'post' ) ) {
		$type   = 'Article';
	} // End if().
	elseif ( is_author() ) {
		$type   = 'ProfilePage';
	} // Is search results page
	elseif ( is_search() ) {
		$type   = 'SearchResultsPage';
	}

	echo 'itemscope="itemscope" itemtype="' . esc_attr( $schema ) . esc_attr( $type ) . '"';
}


/**
 * Add meta box for page header description - save meta box
 *
 * @since  1.0.0
 */
function shop_isle_custom_add_save( $post_id ) {
	$parent_id = wp_is_post_revision( $post_id );
	if ( $parent_id ) {
		$post_id = $parent_id;
	}
	if ( isset( $_POST['shop_isle_page_description'] ) ) {
		shop_isle_update_custom_meta( $post_id, $_POST['shop_isle_page_description'], 'shop_isle_page_description' );
	}
}

/**
 * Add meta box for page header description - update meta box
 *
 * @since  1.0.0
 */
function shop_isle_update_custom_meta( $post_id, $newvalue, $field_name ) {
	// To create new meta
	if ( ! get_post_meta( $post_id, $field_name ) ) {
		add_post_meta( $post_id, $field_name, $newvalue );
	} else {
		// or to update existing meta
		update_post_meta( $post_id, $field_name, $newvalue );
	}
}

/**
 * Filter to translate strings
 *
 * @since 2.2.18
 */
function shop_isle_translate_single_string( $original_value, $domain ) {
	if ( is_customize_preview() ) {
		$wpml_translation = $original_value;
	} else {
		$wpml_translation = apply_filters( 'wpml_translate_single_string', $original_value, $domain, $original_value );
		if ( $wpml_translation === $original_value && function_exists( 'pll__' ) ) {
			return pll__( $original_value );
		}
	}
	return $wpml_translation;
}
add_filter( 'shop_isle_translate_single_string', 'shop_isle_translate_single_string', 10, 2 );

/**
 * Helper to register pll string.
 *
 * @param String    $theme_mod Theme mod name.
 * @param bool/json $default Default value.
 * @param String    $name Name for polylang backend.
 * @since 2.2.18
 */
function shop_isle_pll_string_register_helper( $theme_mod, $default = false, $name ) {
	if ( ! function_exists( 'pll_register_string' ) ) {
		return;
	}
	$repeater_content = get_theme_mod( $theme_mod, $default );
	$repeater_content = json_decode( $repeater_content );
	if ( ! empty( $repeater_content ) ) {
		foreach ( $repeater_content as $repeater_item ) {
			foreach ( $repeater_item as $field_name => $field_value ) {
				if ( $field_name === 'social_repeater' ) {
					$social_repeater_value = json_decode( $field_value );
					if ( ! empty( $social_repeater_value ) ) {
						foreach ( $social_repeater_value as $social ) {
							foreach ( $social as $key => $value ) {
								if ( $key === 'link' ) {
									pll_register_string( 'Social link', $value, $name );
								}
								if ( $key === 'icon' ) {
									pll_register_string( 'Social icon', $value, $name );
								}
							}
						}
					}
				} else {
					if ( $field_name !== 'id' ) {
						$f_n = ucfirst( $field_name );
						pll_register_string( $f_n, $field_value, $name );
					}
				}
			}
		}
	}
}

/**
 * Features section. Register strings for translations.
 *
 * @modified 1.1.30
 * @access public
 * @since 2.2.18
 */
function shop_isle_features_register_strings() {
	$default = json_encode(
		array(
			array(
				'image_url' => get_template_directory_uri() . '/assets/images/slide1.jpg',
				'link'      => '#',
				'text'      => __( 'Shop Isle', 'shop-isle' ),
				'subtext'   => __( 'WooCommerce Theme', 'shop-isle' ),
				'label'     => __( 'Read more', 'shop-isle' ),
			),
			array(
				'image_url' => get_template_directory_uri() . '/assets/images/slide2.jpg',
				'link'      => '#',
				'text'      => __( 'Shop Isle', 'shop-isle' ),
				'subtext'   => __( 'WooCommerce Theme', 'shop-isle' ),
				'label'     => __( 'Read more', 'shop-isle' ),
			),
			array(
				'image_url' => get_template_directory_uri() . '/assets/images/slide3.jpg',
				'link'      => '#',
				'text'      => __( 'Shop Isle', 'shop-isle' ),
				'subtext'   => __( 'WooCommerce Theme', 'shop-isle' ),
				'label'     => __( 'Read more', 'shop-isle' ),
			),
		)
	);
	shop_isle_pll_string_register_helper( 'shop_isle_slider', $default, 'Slider section' );

	$default = json_encode(
		array(
			array(
				'image_url' => get_template_directory_uri() . '/assets/images/banner1.jpg',
				'link' => '#',
			),
			array(
				'image_url' => get_template_directory_uri() . '/assets/images/banner2.jpg',
				'link' => '#',
			),
			array(
				'image_url' => get_template_directory_uri() . '/assets/images/banner3.jpg',
				'link' => '#',
			),
		)
	);
	shop_isle_pll_string_register_helper( 'shop_isle_banners', $default, 'Banners section' );

	$default = json_encode(
		array(
			array(
				'icon_value' => 'icon_gift',
				'text'       => esc_html__( 'Social icons', 'shop-isle' ),
				'subtext'    => esc_html__( 'Ideas and concepts', 'shop-isle' ),
				'link'       => esc_url( '#' ),
			),
			array(
				'icon_value' => 'icon_pin',
				'text'       => esc_html__( 'WooCommerce', 'shop-isle' ),
				'subtext'    => esc_html__( 'Top Rated Products', 'shop-isle' ),
				'link'       => esc_url( '#' ),
			),
			array(
				'icon_value' => 'icon_star',
				'text'       => esc_html__( 'Highly customizable', 'shop-isle' ),
				'subtext'    => esc_html__( 'Easy to use', 'shop-isle' ),
				'link'       => esc_url( '#' ),
			),
		)
	);
	shop_isle_pll_string_register_helper( 'shop_isle_service_box', $default, 'Features section' );

	$default = false;
	shop_isle_pll_string_register_helper( 'shop_isle_shortcodes_settings', $default, 'Shortcodes section' );
	shop_isle_pll_string_register_helper( 'shop_isle_socials', $default, 'Footer socials' );

	$default = json_encode(
		array(
			array(
				'image_url' => get_template_directory_uri() . '/assets/images/team1.jpg',
				'text' => 'Eva Bean',
				'subtext' => 'Developer',
				'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit lacus, a iaculis diam.',
			),
			array(
				'image_url' => get_template_directory_uri() . '/assets/images/team2.jpg',
				'text' => 'Maria Woods',
				'subtext' => 'Designer',
				'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit lacus, a iaculis diam.',
			),
			array(
				'image_url' => get_template_directory_uri() . '/assets/images/team3.jpg',
				'text' => 'Booby Stone',
				'subtext' => 'Director',
				'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit lacus, a iaculis diam.',
			),
			array(
				'image_url' => get_template_directory_uri() . '/assets/images/team4.jpg',
				'text' => 'Anna Neaga',
				'subtext' => 'Art Director',
				'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit lacus, a iaculis diam.',
			),
		)
	);
	shop_isle_pll_string_register_helper( 'shop_isle_team_members', $default, 'Team section' );

	$default = json_encode(
		array(
			array(
				'icon_value' => 'icon_lightbulb',
				'text' => __( 'Ideas and concepts','shop-isle' ),
				'subtext' => __( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.','shop-isle' ),
			),
			array(
				'icon_value' => 'icon_tools',
				'text' => __( 'Designs & interfaces','shop-isle' ),
				'subtext' => __( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.','shop-isle' ),
			),
			array(
				'icon_value' => 'icon_cogs',
				'text' => __( 'Highly customizable','shop-isle' ),
				'subtext' => __( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.','shop-isle' ),
			),
			array(
				'icon_value' => 'icon_like',
				'text' => __( 'Easy to use','shop-isle' ),
				'subtext' => __( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.','shop-isle' ),
			),
		)
	);
	shop_isle_pll_string_register_helper( 'shop_isle_advantages', $default, 'Advantages section' );
}
add_action( 'after_setup_theme', 'shop_isle_features_register_strings', 11 );
