<?php
/**
 * Customizer functionality for the Banners Section.
 *
 * @package WordPress
 * @subpackage Shop Isle
 */

/**
 * Hook controls for Banners Section to Customizer.
 */
function shop_isle_banners_controls_customize_register( $wp_customize ) {

	/* Banners section */

	$wp_customize->add_section(
		'shop_isle_banners_section', array(
			'title'    => __( 'Banners section', 'shop-isle' ),
			'priority' => apply_filters( 'shop_isle_section_priority', 15, 'shop_isle_banners_section' ),
		)
	);

	/* Hide banner */
	$wp_customize->add_setting(
		'shop_isle_banners_hide', array(
			'transport'         => 'postMessage',
			'sanitize_callback' => 'shop_isle_sanitize_text',
		)
	);

	$wp_customize->add_control(
		'shop_isle_banners_hide',
		array(
			'type'        => 'checkbox',
			'label'       => __( 'Hide banners section?', 'shop-isle' ),
			'section'     => 'shop_isle_banners_section',
			'priority'    => 1,
		)
	);

	$wp_customize->add_setting(
		'shop_isle_banners_title', array(
			'transport'         => 'postMessage',
			'sanitize_callback' => 'shop_isle_sanitize_text',
		)
	);

	$wp_customize->add_control(
		'shop_isle_banners_title', array(
			'label'    => __( 'Section title', 'shop-isle' ),
			'section'  => 'shop_isle_banners_section',
			'priority' => 2,
		)
	);

	/* Banner */
	$wp_customize->add_setting(
		'shop_isle_banners', array(
			'transport'         => 'postMessage',
			'sanitize_callback' => 'shop_isle_sanitize_repeater',
			'default'           => json_encode(
				array(
					array(
						'image_url' => get_template_directory_uri() . '/assets/images/banner1.jpg',
						'link'      => '#',
					),
					array(
						'image_url' => get_template_directory_uri() . '/assets/images/banner2.jpg',
						'link' => '#',
					),
					array(
						'image_url' => get_template_directory_uri() . '/assets/images/banner3.jpg',
						'link' => '#',
					),
				)
			),
		)
	);
	$wp_customize->add_control(
		new Shop_Isle_Repeater_Controler(
			$wp_customize, 'shop_isle_banners', array(
				'label'                         => __( 'Add new banner', 'shop-isle' ),
				'section'                       => 'shop_isle_banners_section',
				'active_callback'               => 'is_front_page',
				'priority'                      => 3,
				'shop_isle_image_control'       => true,
				'shop_isle_link_control'        => true,
				'shop_isle_text_control'        => false,
				'shop_isle_subtext_control'     => false,
				'shop_isle_label_control'       => false,
				'shop_isle_icon_control'        => false,
				'shop_isle_description_control' => false,
				'shop_isle_box_label'           => __( 'Banner', 'shop-isle' ),
				'shop_isle_box_add_label'       => __( 'Add new banner', 'shop-isle' ),
			)
		)
	);

	$wp_customize->get_section( 'shop_isle_banners_section' )->panel = 'shop_isle_front_page_sections';

}

add_action( 'customize_register', 'shop_isle_banners_controls_customize_register' );
