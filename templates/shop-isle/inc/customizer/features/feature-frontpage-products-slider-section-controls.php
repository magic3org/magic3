<?php
/**
 * Customizer functionality for the Products Slider Section.
 *
 * @package WordPress
 * @subpackage Shop Isle
 */

/**
 * Hook controls for Products Slider Section to Customizer.
 */
function shop_isle_products_slider_controls_customize_register( $wp_customize ) {

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
				) . '">%s</a>',
				esc_html__( 'WooCommerce', 'shop-isle' )
			)
		) . '</p></div>';
	}

	/*  Products slider section */

	$wp_customize->add_section(
		'shop_isle_products_slider_section', array(
			'title'       => __( 'Products slider section', 'shop-isle' ),
			'description' => $shop_isle_require_woo,
			'priority'    => apply_filters( 'shop_isle_section_priority', 35, 'shop_isle_products_slider_section' ),
		)
	);

	/* Hide products slider on frontpage */
	$wp_customize->add_setting(
		'shop_isle_products_slider_hide', array(
			'default'           => false,
			'sanitize_callback' => 'shop_isle_sanitize_checkbox',
		)
	);

	$wp_customize->add_control(
		'shop_isle_products_slider_hide',
		array(
			'type'     => 'checkbox',
			'label'    => __( 'Hide products slider section on frontpage?', 'shop-isle' ),
			'section'  => 'shop_isle_products_slider_section',
			'priority' => 1,
		)
	);

	/* Hide products slider on single product page */
	$wp_customize->add_setting(
		'shop_isle_products_slider_single_hide', array(
			'transport'         => 'postMessage',
			'sanitize_callback' => 'shop_isle_sanitize_text',
		)
	);

	$wp_customize->add_control(
		'shop_isle_products_slider_single_hide',
		array(
			'type'     => 'checkbox',
			'label'    => __( 'Hide products slider section on single product page?', 'shop-isle' ),
			'section'  => 'shop_isle_products_slider_section',
			'priority' => 2,
		)
	);

	/* Title */
	$wp_customize->add_setting(
		'shop_isle_products_slider_title', array(
			'transport'         => 'postMessage',
			'sanitize_callback' => 'shop_isle_sanitize_text',
			'default'           => __( 'Exclusive products', 'shop-isle' ),
		)
	);

	$wp_customize->add_control(
		'shop_isle_products_slider_title', array(
			'label'    => __( 'Section title', 'shop-isle' ),
			'section'  => 'shop_isle_products_slider_section',
			'priority' => 3,
		)
	);

	/* Subtitle */
	$wp_customize->add_setting(
		'shop_isle_products_slider_subtitle', array(
			'transport'         => 'postMessage',
			'sanitize_callback' => 'shop_isle_sanitize_text',
			'default'           => __( 'Special category of products', 'shop-isle' ),
		)
	);

	$wp_customize->add_control(
		'shop_isle_products_slider_subtitle', array(
			'label'    => __( 'Section subtitle', 'shop-isle' ),
			'section'  => 'shop_isle_products_slider_section',
			'priority' => 4,
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
		'shop_isle_products_slider_category', array(
			'sanitize_callback' => 'shop_isle_sanitize_text',
		)
	);
	$wp_customize->add_control(
		'shop_isle_products_slider_category',
		array(
			'type'        => 'select',
			'label'       => __( 'Products category', 'shop-isle' ),
			'section'     => 'shop_isle_products_slider_section',
			'choices'     => $shop_isle_prod_categories_array,
			'priority'    => 5,
			'description' => __( 'If no category is selected , WooCommerce products from the first category found are displaying.', 'shop-isle' ),
		)
	);

	$wp_customize->get_section( 'shop_isle_products_slider_section' )->panel = 'shop_isle_front_page_sections';

}

add_action( 'customize_register', 'shop_isle_products_slider_controls_customize_register' );
