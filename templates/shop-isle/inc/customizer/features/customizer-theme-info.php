<?php
/**
 * Theme info customizer controls.
 *
 * @package ShopIsle
 */
/**
 * Hook Theme Info section to customizer.
 *
 * @param object $wp_customize The wp_customize object.
 */
function shopisle_theme_info_customize_register( $wp_customize ) {
	// Include theme info control class.
	require_once( get_template_directory() . '/inc/customizer/class/class-shopisle-info.php' );
	// Include upsell class.
	require_once( get_template_directory() . '/inc/customizer/customizer-upsell/class-shopisle-control-upsell.php' );

	// Add Theme Info Section.
	$wp_customize->add_section(
		'shopisle_pro_features_section', array(
			'title'    => __( 'View PRO version', 'shop-isle' ),
			'priority' => 0,
		)
	);

	// Add upsells.
	$wp_customize->add_setting(
		'shopisle_upsell_pro_features_main', array(
			'sanitize_callback' => 'esc_html',
			'default'           => '',
		)
	);

	$wp_customize->add_control(
		new Shopisle_Control_Upsell(
			$wp_customize, 'shopisle_upsell_pro_features_main', array(
				'section'      => 'shopisle_pro_features_section',
				'priority'     => 100,
				'options'      => array(
					esc_html__( 'Enhanced Cart', 'shop-isle' ),
					esc_html__( 'Get full color schemes support for your site. ', 'shop-isle' ),
					esc_html__( 'Section Reordering', 'shop-isle' ),
					esc_html__( 'Add New Sections', 'shop-isle' ),
					esc_html__( 'Map Section', 'shop-isle' ),
					esc_html__( 'Services Section', 'shop-isle' ),
					esc_html__( 'Quick View functionality', 'shop-isle' ),
					esc_html__( 'Categories Section', 'shop-isle' ),
					esc_html__( 'Support', 'shop-isle' ),
				),
				'explained_features' => array(
					esc_html( 'Allow visitors to easily mange their cart in a popup without changing the page, helping with your user experience and conversions.' ),
				),
				'button_url'   => esc_url( 'https://www.themeisle.com/themes/shop-isle-pro/' ),
				// xss ok
				'button_text'  => esc_html__( 'View PRO version', 'shop-isle' ),
			)
		)
	);

	$wp_customize->add_setting(
		'shopisle_upsell_colors', array(
			'sanitize_callback' => 'esc_html',
			'default'           => '',
		)
	);

	$wp_customize->add_control(
		new Shopisle_Control_Upsell(
			$wp_customize, 'shopisle_upsell_colors', array(
				'section'      => 'colors',
				'priority'     => 100,
				'options'      => array(
					esc_html__( 'Three New Color Schemes', 'shop-isle' ),
				),
				'button_url'   => esc_url( 'https://www.themeisle.com/themes/shop-isle-pro/' ),
				// xss ok
				'button_text'  => esc_html__( 'View PRO version', 'shop-isle' ),
			)
		)
	);

}
add_action( 'customize_register', 'shopisle_theme_info_customize_register' );
