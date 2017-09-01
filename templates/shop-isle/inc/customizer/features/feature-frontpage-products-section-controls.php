<?php
/**
 * Customizer functionality for the Products Section.
 *
 * @package WordPress
 * @subpackage Shop Isle
 */

/**
 * Hook controls for Products Section to Customizer.
 */
function shop_isle_products_controls_customize_register( $wp_customize ) {

	/* Products section */

	$shop_isle_require_woo = '';
	if ( ! class_exists( 'WooCommerce' ) ) {
		$shop_isle_require_woo = '<div class="shop-isle-require-woo"><p>' . sprintf(
			/* translators: 1: Link to WooCommerce Plugin */
				__( 'To use this section, you are required to first install the  %1$s plugin', 'shop-isle' ),
			sprintf(
				/* translators: 1: Link to WiooCommerce Plugin. 2: 'WooCommerce' */
				'<a href="' . esc_url(
					wp_nonce_url(
						self_admin_url( 'update.php?action=install-plugin&plugin=woocommerce' ),
						'install-plugin_woocommerce'
					)
				) . '">%s</a>', esc_html__( 'WooCommerce', 'shop-isle' )
			)
		) . '</p></div>';
	}

	$wp_customize->add_section(
		'shop_isle_products_section', array(
			'default'     => false,
			'title'       => __( 'Products section', 'shop-isle' ),
			'description' => $shop_isle_require_woo,
			'priority'    => apply_filters( 'shop_isle_section_priority', 20, 'shop_isle_products_section' ),
		)
	);

	/* Hide products */
	$wp_customize->add_setting(
		'shop_isle_products_hide', array(
			'sanitize_callback' => 'shop_isle_sanitize_text',
		)
	);

	$wp_customize->add_control(
		'shop_isle_products_hide', array(
			'type'     => 'checkbox',
			'label'    => __( 'Hide products section?', 'shop-isle' ),
			'section'  => 'shop_isle_products_section',
			'priority' => 1,
		)
	);

	/* Title */
	$wp_customize->add_setting(
		'shop_isle_products_title', array(
			'transport'         => 'postMessage',
			'sanitize_callback' => 'shop_isle_sanitize_text',
			'default'           => __( 'Latest products', 'shop-isle' ),
		)
	);

	$wp_customize->add_control(
		'shop_isle_products_title', array(
			'label'    => __( 'Section title', 'shop-isle' ),
			'section'  => 'shop_isle_products_section',
			'priority' => 2,
		)
	);

	/* Shortcode */
	$wp_customize->add_setting(
		'shop_isle_products_shortcode', array(
			'sanitize_callback' => 'shop_isle_sanitize_text',
		)
	);

	$wp_customize->add_control(
		'shop_isle_products_shortcode', array(
			'label'       => __( 'WooCommerce shortcode', 'shop-isle' ),
			'section'     => 'shop_isle_products_section',
			'description' => __( 'Insert a WooCommerce shortcode', 'shop-isle' ),
			'priority'    => 3,
		)
	);

	/* Category */
	$shop_isle_prod_categories_array = array(
		'-' => __( 'Select category', 'shop-isle' ),
	);

	$shop_isle_prod_categories = get_categories(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => 0,
			'title_li'   => '',
		)
	);

	if ( ! empty( $shop_isle_prod_categories ) ) :
		foreach ( $shop_isle_prod_categories as $shop_isle_prod_cat ) :

			if ( ! empty( $shop_isle_prod_cat->term_id ) && ! empty( $shop_isle_prod_cat->name ) ) :
				$shop_isle_prod_categories_array[ $shop_isle_prod_cat->term_id ] = $shop_isle_prod_cat->name;
			endif;

		endforeach;
	endif;

	$wp_customize->add_setting(
		'shop_isle_products_category', array(
			'sanitize_callback' => 'shop_isle_sanitize_text',
		)
	);
	$wp_customize->add_control(
		'shop_isle_products_category', array(
			'type'        => 'select',
			'label'       => __( 'Products category', 'shop-isle' ),
			'description' => __( 'OR pick a product category. If no shortcode or no category is selected , WooCommerce latest products are displaying.', 'shop-isle' ),
			'section'     => 'shop_isle_products_section',
			'choices'     => $shop_isle_prod_categories_array,
			'priority'    => 4,
		)
	);

	$wp_customize->get_section( 'shop_isle_products_section' )->panel = 'shop_isle_front_page_sections';

}

add_action( 'customize_register', 'shop_isle_products_controls_customize_register' );
