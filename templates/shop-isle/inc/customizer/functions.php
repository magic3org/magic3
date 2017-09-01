<?php
/**
 * Theme Customizer functions
 *
 * @package WordPress
 * @subpackage Shop Isle Lite
 */


if ( ! function_exists( 'shop_isle_customize_preview_js' ) ) {

	/**
	 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
	 *
	 * @since  1.0.0
	 */
	function shop_isle_customize_preview_js() {

		wp_enqueue_script( 'shop_isle_customizer', get_template_directory_uri() . '/js/customizer.js', array( 'customize-preview' ), '1.0.0', true );

	}
}

if ( ! function_exists( 'shop_isle_customizer_script' ) ) {

	/**
	 * Binds JS scripts for Theme Customizer.
	 *
	 * @since  1.0.0
	 */
	function shop_isle_customizer_script() {

		wp_enqueue_script( 'shop_isle_customizer_script', get_template_directory_uri() . '/js/shop_isle_customizer.js', array( 'jquery' ),'', true );

		wp_localize_script(
			'shop_isle_customizer_script', 'objectL10n', array(

				'documentation' => __( 'Documentation', 'shop-isle' ),
				'support'               => __( 'Support','shop-isle' ),

			)
		);

	}
}


if ( ! function_exists( 'shop_isle_sanitize_hex_color' ) ) {

	/**
	 * Sanitizes a hex color. Identical to core's sanitize_hex_color(), which is not available on the wp_head hook.
	 *
	 * Returns either '', a 3 or 6 digit hex color (with #), or null.
	 * For sanitizing values without a #, see sanitize_hex_color_no_hash().
	 *
	 * @since 1.0.0
	 */
	function shop_isle_sanitize_hex_color( $color ) {
		if ( '' === $color ) {
			return '';
		}

		// 3 or 6 hex digits, or the empty string.
		if ( preg_match( '|^#([A-Fa-f0-9]{3}){1,2}$|', $color ) ) {
			return $color;
		}

		return null;
	}
}


if ( ! function_exists( 'shop_isle_sanitize_choices' ) ) {

	/**
	 * Sanitizes choices (selects / radios)
	 * Checks that the input matches one of the available choices
	 *
	 * @since  1.0.0
	 */
	function shop_isle_sanitize_choices( $input, $setting ) {
		global $wp_customize;

		$control = $wp_customize->get_control( $setting->id );

		if ( array_key_exists( $input, $control->choices ) ) {
			return $input;
		} else {
			return $setting->default;
		}
	}
}


if ( ! function_exists( 'shop_isle_sanitize_text' ) ) {

	/**
	 * Sanitizes text
	 *
	 * @since  1.0.0
	 */
	function shop_isle_sanitize_text( $input ) {

		return wp_kses_post( force_balance_tags( $input ) );

	}
}

if ( ! function_exists( 'shop_isle_sanitize_array' ) ) {

	/**
	 * Sanitizes array
	 *
	 * @since  1.0.0
	 */
	function shop_isle_sanitize_array( $input ) {

		return ( is_array( $input ) ? $input : array( 'none' ) );

	}
}

if ( ! function_exists( 'shop_isle_sanitize_checkbox' ) ) {

	/**
	 * Checkbox sanitization callback example.
	 *
	 * Sanitization callback for 'checkbox' type controls. This callback sanitizes `$checked`
	 * as a boolean value, either TRUE or FALSE.
	 *
	 * @param bool $checked Whether the checkbox is checked.
	 *
	 * @return bool Whether the checkbox is checked.
	 */
	function shop_isle_sanitize_checkbox( $checked ) {
		// Boolean check.
		return ( ( isset( $checked ) && true == $checked ) ? true : false );
	}
}
