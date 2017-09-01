<?php
/**
 * Customizer functionality for the Blog Header.
 *
 * @package WordPress
 * @subpackage Shop Isle
 */

/**
 * Hook controls for Blog Header.
 */
function shop_isle_blog_header_customize_register( $wp_customize ) {

	/* Blog Header title */
	$wp_customize->add_setting(
		'shop_isle_blog_header_title', array(
			'default'           => __( 'Blog','shop-isle' ),
			'sanitize_callback' => 'shop_isle_sanitize_text',
			'transport'         => 'postMessage',
		)
	);
	$wp_customize->add_control(
		'shop_isle_blog_header_title', array(
			'label'             => esc_html__( 'Blog header title', 'shop-isle' ),
			'section'           => 'shop_isle_header_section',
			'priority'          => 3,
		)
	);

	/* Blog Header subtitle */
	$wp_customize->add_setting(
		'shop_isle_blog_header_subtitle', array(
			'sanitize_callback' => 'shop_isle_sanitize_text',
			'transport'         => 'postMessage',
		)
	);
	$wp_customize->add_control(
		'shop_isle_blog_header_subtitle', array(
			'label'             => esc_html__( 'Blog header subtitle', 'shop-isle' ),
			'section'           => 'shop_isle_header_section',
			'priority'          => 4,
		)
	);

}

add_action( 'customize_register', 'shop_isle_blog_header_customize_register' );
