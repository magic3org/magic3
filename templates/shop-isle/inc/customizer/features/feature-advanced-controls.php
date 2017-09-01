<?php
/**
 * Customizer functionality for the Advanced Options section.
 *
 * @package WordPress
 * @subpackage Shop Isle
 */

/**
 * Hook controls for Advanced Options section to Customizer.
 */
function shop_isle_advanced_customize_register( $wp_customize ) {

	/*  ADVANCED OPTIONS  */

	$wp_customize->add_section(
		'shop_isle_general_section', array(
			'title'    => __( 'Advanced options', 'shop-isle' ),
			'priority' => 55,
		)
	);

	/* Disable preloader */
	$wp_customize->add_setting(
		'shop_isle_disable_preloader', array(
			'sanitize_callback' => 'shop_isle_sanitize_text',
			'transport'         => 'postMessage',
		)
	);

	$wp_customize->add_control(
		'shop_isle_disable_preloader', array(
			'type'        => 'checkbox',
			'label'       => __( 'Disable preloader?', 'shop-isle' ),
			'section'     => 'shop_isle_general_section',
			'priority'    => 1,
		)
	);

	/* Body font size */
	$wp_customize->add_setting(
		'shop_isle_font_size', array(
			'sanitize_callback' => 'shop_isle_sanitize_text',
			'default' => '13px',
		)
	);

	$wp_customize->add_control(
		'shop_isle_font_size',
		array(
			'type'     => 'select',
			'label'    => 'Select font size:',
			'section'  => 'shop_isle_general_section',
			'choices'  => array(
				'12px' => '12px',
				'13px' => '13px',
				'14px' => '14px',
				'15px' => '15px',
				'16px' => '16px',
			),
			'priority' => 2,
		)
	);
}

add_action( 'customize_register', 'shop_isle_advanced_customize_register' );
