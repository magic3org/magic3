<?php
/**
 * Customizer functionality for the 404 Page controls.
 *
 * @package WordPress
 * @subpackage Shop Isle
 */

/**
 * Hook controls for the 404 Page to Customizer.
 */
function shop_isle_404_page_customize_register( $wp_customize ) {

	/*  404 page  */

	/* Background */
	$wp_customize->add_setting(
		'shop_isle_404_background', array(
			'default'           => get_template_directory_uri() . '/assets/images/404.jpg',
			'transport'         => 'postMessage',
			'sanitize_callback' => 'esc_url',
		)
	);

	$wp_customize->add_control(
		new WP_Customize_Image_Control(
			$wp_customize, 'shop_isle_404_background', array(
				'label'    => __( 'Background image', 'shop-isle' ),
				'section'  => 'shop_isle_general_section',
				'priority' => 3,
			)
		)
	);

	/* Title */
	$wp_customize->add_setting(
		'shop_isle_404_title', array(
			'sanitize_callback' => 'shop_isle_sanitize_text',
			'default'           => __( 'Error 404', 'shop-isle' ),
			'transport'         => 'postMessage',
		)
	);

	$wp_customize->add_control(
		'shop_isle_404_title', array(
			'label'    => __( 'Title', 'shop-isle' ),
			'section'  => 'shop_isle_general_section',
			'priority' => 4,
		)
	);

	/* Text */
	$wp_customize->add_setting(
		'shop_isle_404_text', array(
			'sanitize_callback' => 'shop_isle_sanitize_text',
			'default'           => __( 'The requested URL was not found on this server.<br> That is all we know.', 'shop-isle' ),
			'transport'         => 'postMessage',
		)
	);

	$wp_customize->add_control(
		'shop_isle_404_text', array(
			'type'     => 'textarea',
			'label'    => __( 'Text', 'shop-isle' ),
			'section'  => 'shop_isle_general_section',
			'priority' => 5,
		)
	);

	/* Button link */
	$wp_customize->add_setting(
		'shop_isle_404_link', array(
			'sanitize_callback' => 'esc_url',
			'default'           => '#',
			'transport'         => 'postMessage',
		)
	);

	$wp_customize->add_control(
		'shop_isle_404_link', array(
			'label'    => __( 'Button link', 'shop-isle' ),
			'section'  => 'shop_isle_general_section',
			'priority' => 6,
		)
	);

	/* Button label */
	$wp_customize->add_setting(
		'shop_isle_404_label', array(
			'sanitize_callback' => 'shop_isle_sanitize_text',
			'default'           => __( 'Back to home page', 'shop-isle' ),
			'transport'         => 'postMessage',
		)
	);

	$wp_customize->add_control(
		'shop_isle_404_label', array(
			'label'    => __( 'Button label', 'shop-isle' ),
			'section'  => 'shop_isle_general_section',
			'priority' => 7,
		)
	);
}

add_action( 'customize_register', 'shop_isle_404_page_customize_register' );
