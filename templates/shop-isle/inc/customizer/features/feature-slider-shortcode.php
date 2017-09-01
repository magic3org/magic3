<?php
/**
 * Customizer functionality for the Slider Shortcode Control.
 *
 * @package Shop Isle
 */

/**
 * Hook controls for Big Title Section to Customizer.
 */
function shop_isle_slider_shortcode_customize_register( $wp_customize ) {
	/* Slider shortcode  */
	$slider_section = $wp_customize->get_section( 'shop_isle_slider_section' );

	$wp_customize->add_setting(
		'shop_isle_homepage_slider_shortcode', array(
			'sanitize_callback' => 'shop_isle_sanitize_text',
		)
	);

	$wp_customize->add_control(
		'shop_isle_homepage_slider_shortcode', array(
			'label'           => __( 'Slider shortcode', 'shop-isle' ),
			'description'     => __( 'You can replace the homepage slider with any plugin you like, just copy the shortcode generated and paste it here.', 'shop-isle' ),
			'section'         => ! empty( $slider_section ) ? 'shop_isle_slider_section' : 'shop_isle_big_title_section',
			'active_callback' => 'is_front_page',
			'priority'        => 10,
		)
	);
}

add_action( 'customize_register', 'shop_isle_slider_shortcode_customize_register' );
