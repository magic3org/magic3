<?php
/**
 * Customizer functionality for the Video Section.
 *
 * @package WordPress
 * @subpackage Shop Isle
 */

/**
 * Hook controls for Video Section to Customizer.
 */
function shop_isle_video_controls_customize_register( $wp_customize ) {

	/* Video section */

	$wp_customize->add_section(
		'shop_isle_video_section', array(
			'title'    => __( 'Video section', 'shop-isle' ),
			'priority' => apply_filters( 'shop_isle_section_priority', 25, 'shop_isle_video_section' ),
		)
	);

	/* Hide video */
	$wp_customize->add_setting(
		'shop_isle_video_hide', array(
			'default'           => false,
			'transport'         => 'postMessage',
			'sanitize_callback' => 'shop_isle_sanitize_checkbox',
		)
	);

	$wp_customize->add_control(
		'shop_isle_video_hide', array(
			'type'        => 'checkbox',
			'label'       => __( 'Hide video section?', 'shop-isle' ),
			'section'     => 'shop_isle_video_section',
			'priority'    => 1,
		)
	);

	/* Title */
	$wp_customize->add_setting(
		'shop_isle_video_title', array(
			'sanitize_callback' => 'shop_isle_sanitize_text',
			'transport'         => 'postMessage',
		)
	);

	$wp_customize->add_control(
		'shop_isle_video_title', array(
			'label'    => __( 'Title', 'shop-isle' ),
			'section'  => 'shop_isle_video_section',
			'priority' => 2,
		)
	);

	/* Youtube link */
	$wp_customize->add_setting(
		'shop_isle_yt_link', array(
			'sanitize_callback' => 'esc_url',
		)
	);

	$wp_customize->add_control(
		'shop_isle_yt_link', array(
			'label'    => __( 'Youtube link', 'shop-isle' ),
			'section'  => 'shop_isle_video_section',
			'priority' => 3,
		)
	);

	/* Thumbnail */
	$wp_customize->add_setting(
		'shop_isle_yt_thumbnail', array(
			'sanitize_callback' => 'esc_url',
		)
	);

	$wp_customize->add_control(
		new WP_Customize_Image_Control(
			$wp_customize, 'shop_isle_yt_thumbnail', array(
				'label'       => __( 'Video thumbnail', 'shop-isle' ),
				'section'     => 'shop_isle_video_section',
				'priority'    => 4,
			)
		)
	);

	$wp_customize->get_section( 'shop_isle_video_section' )->panel = 'shop_isle_front_page_sections';

}

add_action( 'customize_register', 'shop_isle_video_controls_customize_register' );
