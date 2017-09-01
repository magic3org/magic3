<?php
/**
 * Customizer functionality for the Footer.
 *
 * @package WordPress
 * @subpackage Shop Isle
 */

/**
 * Hook controls for Footer to Customizer.
 */
function shop_isle_footer_customize_register( $wp_customize ) {

	/*  Footer */

	$wp_customize->add_section(
		'shop_isle_footer_section', array(
			'title'    => __( 'Footer', 'shop-isle' ),
			'priority' => 50,
		)
	);

	/* Copyright */
	$wp_customize->add_setting(
		'shop_isle_copyright', array(
			'sanitize_callback' => 'shop_isle_sanitize_text',
			'transport'         => 'postMessage',
		)
	);

	$wp_customize->add_control(
		'shop_isle_copyright', array(
			'label'    => __( 'Copyright', 'shop-isle' ),
			'section'  => 'shop_isle_footer_section',
			'priority' => 1,
		)
	);

	/* Hide site info */
	$wp_customize->add_setting(
		'shop_isle_site_info_hide', array(
			'transport' => 'postMessage',
			'sanitize_callback' => 'shop_isle_sanitize_text',
		)
	);

	$wp_customize->add_control(
		'shop_isle_site_info_hide',
		array(
			'type' => 'checkbox',
			'label' => __( 'Hide site info?','shop-isle' ),
			'section' => 'shop_isle_footer_section',
			'priority' => 2,
		)
	);

	/* socials */
	$wp_customize->add_setting(
		'shop_isle_socials', array(
			'transport'         => 'postMessage',
			'sanitize_callback' => 'shop_isle_sanitize_repeater',
		)
	);

	$wp_customize->add_control(
		new Shop_Isle_Repeater_Controler(
			$wp_customize, 'shop_isle_socials', array(
				'label'                         => __( 'Add new social', 'shop-isle' ),
				'section'                       => 'shop_isle_footer_section',
				'active_callback'               => 'is_front_page',
				'priority'                      => 3,
				'shop_isle_image_control'       => false,
				'shop_isle_link_control'        => true,
				'shop_isle_text_control'        => false,
				'shop_isle_subtext_control'     => false,
				'shop_isle_label_control'       => false,
				'shop_isle_icon_control'        => true,
				'shop_isle_description_control' => false,
				'shop_isle_box_label'           => __( 'Social', 'shop-isle' ),
				'shop_isle_box_add_label'       => __( 'Add new social', 'shop-isle' ),
			)
		)
	);
}

add_action( 'customize_register', 'shop_isle_footer_customize_register' );
