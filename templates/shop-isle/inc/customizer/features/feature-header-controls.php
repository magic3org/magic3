<?php
/**
 * Customizer functionality for the Header Section.
 *
 * @package WordPress
 * @subpackage Shop Isle
 */

/**
 * Hook controls for Header Section section to Customizer.
 */
function shop_isle_header_controls_customize_register( $wp_customize ) {

	/*  Header */

	$wp_customize->add_section(
		'shop_isle_header_section', array(
			'title'    => __( 'Header', 'shop-isle' ),
			'priority' => 40,
		)
	);

	/* Logo */
	$wp_customize->add_setting(
		'shop_isle_logo', array(
			'transport'         => 'postMessage',
			'sanitize_callback' => 'esc_url',
		)
	);

	$wp_customize->add_control(
		new WP_Customize_Image_Control(
			$wp_customize, 'shop_isle_logo', array(
				'label'    => __( 'Logo', 'shop-isle' ),
				'section'  => 'title_tagline',
				'priority' => 1,
			)
		)
	);

	$wp_customize->get_control( 'header_image' )->section  = 'shop_isle_header_section';
	$wp_customize->get_control( 'header_image' )->priority = '2';

}

add_action( 'customize_register', 'shop_isle_header_controls_customize_register' );
