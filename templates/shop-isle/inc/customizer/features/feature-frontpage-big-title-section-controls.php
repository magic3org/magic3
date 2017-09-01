<?php
/**
 * Customizer functionality for the Slider Section.
 *
 * @package WordPress
 * @subpackage Shop Isle
 */

/**
 * Hook controls for Big Title Section to Customizer.
 */
function shop_isle_big_title_controls_customize_register( $wp_customize ) {

	/* Big title section */

	$wp_customize->add_section(
		'shop_isle_big_title_section' , array(
			'title'       => __( 'Big title section', 'shop-isle' ),
			'priority'    => 10,
			'panel' => 'shop_isle_front_page_sections',
		)
	);

	/* Hide big title section */
	$wp_customize->add_setting(
		'shop_isle_big_title_hide', array(
			'sanitize_callback' => 'shop_isle_sanitize_text',
			'transport' => 'postMessage',
		)
	);

	$wp_customize->add_control(
		'shop_isle_big_title_hide',
		array(
			'type' => 'checkbox',
			'label' => __( 'Hide big title section?','shop-isle' ),
			'section' => 'shop_isle_big_title_section',
			'priority'    => 1,
		)
	);

	/* Image */
	$wp_customize->add_setting(
		'shop_isle_big_title_image', array(
			'sanitize_callback' => 'esc_url_raw',
			'default' => get_template_directory_uri() . '/assets/images/slide1.jpg',
		)
	);

	$wp_customize->add_control(
		new WP_Customize_Image_Control(
			$wp_customize, 'shop_isle_big_title_image', array(
				'label' => __( 'Image', 'shop-isle' ),
				'section' => 'shop_isle_big_title_section',
				'priority' => 2,
			)
		)
	);

	/* Title */
	$wp_customize->add_setting(
		'shop_isle_big_title_title', array(
			'sanitize_callback' => 'shop_isle_sanitize_text',
			'default'    => 'Shop Isle',
		)
	);

	$wp_customize->add_control(
		'shop_isle_big_title_title', array(
			'label' => __( 'Title','shop-isle' ),
			'section'  => 'shop_isle_big_title_section',
			'priority'    => 3,
		)
	);

	/* Subtitle */
	$wp_customize->add_setting(
		'shop_isle_big_title_subtitle', array(
			'sanitize_callback' => 'shop_isle_sanitize_text',
			'default'    => __( 'WooCommerce Theme', 'shop-isle' ),
		)
	);

	$wp_customize->add_control(
		'shop_isle_big_title_subtitle', array(
			'label' => __( 'Subtitle', 'shop-isle' ),
			'section'  => 'shop_isle_big_title_section',
			'priority'    => 4,
		)
	);

	/* Button label */
	$wp_customize->add_setting(
		'shop_isle_big_title_button_label', array(
			'sanitize_callback' => 'shop_isle_sanitize_text',
			'default'    => __( 'Read more', 'shop-isle' ),
		)
	);

	$wp_customize->add_control(
		'shop_isle_big_title_button_label', array(
			'label' => __( 'Button label','shop-isle' ),
			'section'  => 'shop_isle_big_title_section',
			'priority'    => 5,
		)
	);

	/* Button link */
	$wp_customize->add_setting(
		'shop_isle_big_title_button_link', array(
			'sanitize_callback' => 'shop_isle_sanitize_text',
			'default'    => __( '#', 'shop-isle' ),
		)
	);

	$wp_customize->add_control(
		'shop_isle_big_title_button_link', array(
			'label' => __( 'Button link', 'shop-isle' ),
			'section'  => 'shop_isle_big_title_section',
			'priority'    => 6,
		)
	);

}
add_action( 'customize_register', 'shop_isle_big_title_controls_customize_register' );
