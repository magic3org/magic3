<?php
/**
 *  This is used for wp-org and pro version from wp-org migration.
 *
 * @package ShopIsle
 */

/**
 * Frontpage manager customize register
 */
function shop_isle_frontpage_manager_customize_register( $wp_customize ) {
	$wporg_flag = get_option( 'shop_isle_wporg_flag' );
	/**
	 * Only make available if wp_org flag is defined.
	 */
	if ( isset( $wporg_flag ) && ( $wporg_flag === 'true' ) ) {
		/**
		 * Option to get the frontpage settings to the old default template if a static frontpage is selected
		 */
		$wp_customize->add_setting(
			'shop_isle_keep_old_fp_template', array(
				'sanitize_callback' => 'shop_isle_sanitize_checkbox',
			)
		);

		$wp_customize->add_control(
			'shop_isle_keep_old_fp_template', array(
				'type'     => 'checkbox',
				'label'    => esc_html__( 'Keep the old static frontpage template?', 'shop-isle' ),
				'section'  => 'static_front_page',
				'priority' => 10,
			)
		);
	}
}
add_action( 'customize_register', 'shop_isle_frontpage_manager_customize_register' );


/**
 * Filter the front page template so it's bypassed entirely if the user selects
 * to display blog posts on their homepage instead of a static page.
 */
function shop_isle_filter_front_page_template( $template ) {
	$wporg_flag = get_option( 'shop_isle_wporg_flag' );
	$shop_isle_keep_old_fp_template = get_theme_mod( 'shop_isle_keep_old_fp_template' );
	if ( ! $shop_isle_keep_old_fp_template && ( isset( $wporg_flag ) && ( $wporg_flag === 'true' ) ) ) {
		return is_home() ? '' : $template;
	} else {
		return '';
	}
}

add_filter( 'frontpage_template', 'shop_isle_filter_front_page_template' );
