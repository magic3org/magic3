<?php
/**
 * Customizer functionality for the Contact Page controls.
 *
 * @package WordPress
 * @subpackage Shop Isle
 */

/**
 * Hook controls for the Contact Page to Customizer.
 */
function shop_isle_contact_page_customize_register( $wp_customize ) {

	require_once( trailingslashit( get_template_directory() ) . 'inc/customizer/class/class-shopisle-contact-page-instructions.php' );

	/*  Contact page  */
	$wp_customize->add_section(
		'shop_isle_contact_page_section', array(
			'title'    => __( 'Contact page', 'shop-isle' ),
			'priority' => 99,
		)
	);

	/* Contact Form  */
	$wp_customize->add_setting(
		'shop_isle_contact_page_form_shortcode', array(
			'sanitize_callback' => 'shop_isle_sanitize_text',
		)
	);

	$wp_customize->add_control(
		'shop_isle_contact_page_form_shortcode', array(
			'label'           => __( 'Contact form shortcode', 'shop-isle' ),
			'description'     => sprintf(
				/* translators: 1: Link to Pirate Forms Plugin. */
				 __( 'Create a form, copy the shortcode generated and paste it here. We recommend %1$s but you can use any plugin you like.', 'shop-isle' ),
				sprintf(
					/* translators: 1: 'Simple Contact Form Plugin - PirateForms' */
					 '<a href="https://wordpress.org/plugins/pirate-forms/" target="_blank">%s</a>',
					'Simple Contact Form Plugin - PirateForms'
				)
			),
			'section'         => 'shop_isle_contact_page_section',
			'active_callback' => 'shop_isle_is_contact_page',
			'priority'        => 1,
		)
	);

	/* Map ShortCode  */
	$wp_customize->add_setting(
		'shop_isle_contact_page_map_shortcode', array(
			'sanitize_callback' => 'shop_isle_sanitize_text',
		)
	);

	$wp_customize->add_control(
		'shop_isle_contact_page_map_shortcode', array(
			'label'           => __( 'Map shortcode', 'shop-isle' ),
			'description'     => __( 'To use this section please install <a href="https://wordpress.org/plugins/intergeo-maps/">Intergeo Maps</a> plugin then use it to create a map and paste here the shortcode generated', 'shop-isle' ),
			'section'         => 'shop_isle_contact_page_section',
			'active_callback' => 'shop_isle_is_contact_page',
			'priority'        => 2,
		)
	);

	/*  Contact page - instructions for users when not on Contact page  */

	$wp_customize->add_section(
		'shop_isle_contact_page_instructions', array(
			'title'    => __( 'Contact page', 'shop-isle' ),
			'priority' => 99,
		)
	);

	$wp_customize->add_setting(
		'shop_isle_contact_page_instructions', array(
			'sanitize_callback' => 'shop_isle_sanitize_text',
		)
	);

	$wp_customize->add_control(
		new ShopIsle_Contact_Page_Instructions(
			$wp_customize, 'shop_isle_contact_page_instructions', array(
				'section'         => 'shop_isle_contact_page_instructions',
				'active_callback' => 'shop_isle_is_not_contact_page',
			)
		)
	);
}

add_action( 'customize_register', 'shop_isle_contact_page_customize_register' );

/**
 * Check if is contact page.
 *
 * @return bool
 */
function shop_isle_is_contact_page() {
	return is_page_template( 'template-contact.php' );
};

/**
 * Check if is not contact page.
 *
 * @return bool
 */
function shop_isle_is_not_contact_page() {
	return ! is_page_template( 'template-contact.php' );
};
