<?php
/**
 * Wisteria Theme Customizer
 *
 * @package Wisteria
 */

/**
 * Add postMessage support for site title and description for the Theme Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function wisteria_customize_register ( $wp_customize ) {

	$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
	$wp_customize->get_setting( 'background_color' )->transport = 'postMessage';
	$wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';

	// Theme Options Panel
	$wp_customize->add_panel( 'wisteria_theme_options', array(
	    'title'     => esc_html__( 'Theme Options', 'wisteria' ),
	    'priority'  => 1,
	) );

	// General Options Section
	$wp_customize->add_section( 'wisteria_general_options', array (
		'title'     => esc_html__( 'General Options', 'wisteria' ),
		'panel'     => 'wisteria_theme_options',
		'priority'  => 1,
	) );

	// Main Sidebar Position
	$wp_customize->add_setting ( 'wisteria_sidebar_position', array (
		'default'           => wisteria_default( 'wisteria_sidebar_position' ),
		'sanitize_callback' => 'wisteria_sanitize_select',
	) );

	$wp_customize->add_control ( 'wisteria_sidebar_position', array (
		'label'    => esc_html__( 'Main Sidebar Position (if active)', 'wisteria' ),
		'section'  => 'wisteria_general_options',
		'priority' => 1,
		'type'     => 'select',
		'choices'  => array(
			'right' => esc_html__( 'Right', 'wisteria'),
			'left'  => esc_html__( 'Left',  'wisteria'),
		),
	) );

	/**
	 * Footer Section
	 */
	$wp_customize->add_section( 'wisteria_footer_options', array (
		'title'       => esc_html__( 'Footer Options', 'wisteria' ),
		'panel'       => 'wisteria_theme_options',
		'priority'    => 2,
		'description' => esc_html__( 'Personalize the footer settings of your theme.', 'wisteria' ),
	) );

	// Copyright Control
	$wp_customize->add_setting ( 'wisteria_copyright', array (
		'default'           => wisteria_default( 'wisteria_copyright' ),
		'transport'         => 'postMessage',
		'sanitize_callback' => 'wp_kses_post',
	) );

	$wp_customize->add_control ( 'wisteria_copyright', array (
		'label'    => esc_html__( 'Copyright', 'wisteria' ),
		'section'  => 'wisteria_footer_options',
		'priority' => 1,
		'type'     => 'textarea',
	) );

	// Credit Control
	$wp_customize->add_setting ( 'wisteria_credit', array (
		'default'           => wisteria_default( 'wisteria_credit' ),
		'transport'         => 'postMessage',
		'sanitize_callback' => 'wisteria_sanitize_checkbox',
	) );

	$wp_customize->add_control ( 'wisteria_credit', array (
		'label'    => esc_html__( 'Display Designer Credit', 'wisteria' ),
		'section'  => 'wisteria_footer_options',
		'priority' => 2,
		'type'     => 'checkbox',
	) );

	// Theme Support Section
	$wp_customize->add_section( 'wisteria_support', array(
		'title'       => esc_html__( 'Theme Support', 'wisteria' ),
		'description' => esc_html__( 'Thanks for your interest in Wisteria! If you have any questions or run into any trouble, please visit us the following links. We will get you fixed up!', 'wisteria' ),
		'panel'       => 'wisteria_theme_options',
		'priority'    => 3,
	) );

	// Theme
	$wp_customize->add_setting ( 'wisteria_theme_about', array(
		'default' => '',
	) );

	$wp_customize->add_control(
		new Wisteria_Button_Control(
			$wp_customize,
			'wisteria_theme_about',
			array(
				'label'         => esc_html__( 'Wisteria Theme', 'wisteria' ),
				'section'       => 'wisteria_support',
				'type'          => 'button',
				'button_tag'    => 'a',
				'button_class'  => 'button button-primary',
				'button_href'   => 'https://wpfriendship.com/wisteria/',
				'button_target' => '_blank',
			)
		)
	);

	// Support
	$wp_customize->add_setting ( 'wisteria_theme_support', array(
		'default' => '',
	) );

	$wp_customize->add_control(
		new Wisteria_Button_Control(
			$wp_customize,
			'wisteria_theme_support',
			array(
				'label'         => esc_html__( 'Wisteria Support', 'wisteria' ),
				'section'       => 'wisteria_support',
				'type'          => 'button',
				'button_tag'    => 'a',
				'button_class'  => 'button button-primary',
				'button_href'   => 'https://wpfriendship.com/contact/',
				'button_target' => '_blank',
			)
		)
	);
}
add_action( 'customize_register', 'wisteria_customize_register' );

/**
 * Button Control Class
 */
if ( class_exists( 'WP_Customize_Control' ) ) {

	class Wisteria_Button_Control extends WP_Customize_Control {
		/**
		 * @access public
		 * @var string
		 */
		public $type = 'button';

		/**
		 * HTML tag to render button object.
		 *
		 * @var  string
		 */
		protected $button_tag = 'button';

		/**
		 * Class to render button object.
		 *
		 * @var  string
		 */
		protected $button_class = 'button button-primary';

		/**
		 * Link for <a> based button.
		 *
		 * @var  string
		 */
		protected $button_href = 'javascript:void(0)';

		/**
		 * Target for <a> based button.
		 *
		 * @var  string
		 */
		protected $button_target = '';

		/**
		 * Click event handler.
		 *
		 * @var  string
		 */
		protected $button_onclick = '';

		/**
		 * ID attribute for HTML tab.
		 *
		 * @var  string
		 */
		protected $button_tag_id = '';

		/**
		 * Render the control's content.
		 */
		public function render_content() {
		?>
			<span class="center">
				<?php
				// Print open tag
				echo '<' . esc_html( $this->button_tag );

				// button class
				if ( ! empty( $this->button_class ) ) {
					echo ' class="' . esc_attr( $this->button_class ) . '"';
				}

				// button or href
				if ( 'button' == $this->button_tag ) {
					echo ' type="button"';
				} else {
					echo ' href="' . esc_url( $this->button_href ) . '"' . ( empty( $this->button_tag ) ? '' : ' target="' . esc_attr( $this->button_target ) . '"' );
				}

				// onClick Event
				if ( ! empty( $this->button_onclick ) ) {
					echo ' onclick="' . esc_js( $this->button_onclick ) . '"';
				}

				// ID
				if ( ! empty( $this->button_tag_id ) ) {
					echo ' id="' . esc_attr( $this->button_tag_id ) . '"';
				}

				echo '>';

				// Print text inside tag
				echo esc_html( $this->label );

				// Print close tag
				echo '</' . esc_html( $this->button_tag ) . '>';
				?>
			</span>
		<?php
		}
	}

}

/**
 * Sanitize Select.
 *
 * @param string $input Slug to sanitize.
 * @param WP_Customize_Setting $setting Setting instance.
 * @return string Sanitized slug if it is a valid choice; otherwise, the setting default.
 */
function wisteria_sanitize_select( $input, $setting ) {

	// Ensure input is a slug.
	$input = sanitize_key( $input );

	// Get list of choices from the control associated with the setting.
	$choices = $setting->manager->get_control( $setting->id )->choices;

	// If the input is a valid key, return it; otherwise, return the default.
	return ( array_key_exists( $input, $choices ) ? $input : $setting->default );
}

/**
 * Sanitize the checkbox.
 *
 * @param bool $checked Whether the checkbox is checked.
 * @return bool Whether the checkbox is checked.
 */
function wisteria_sanitize_checkbox( $checked ) {
	return ( ( isset( $checked ) && true === $checked ) ? true : false );
}

/**
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
 */
function wisteria_customize_preview_js() {
	wp_enqueue_script( 'wisteria_customizer', get_template_directory_uri() . '/js/customizer.js', array( 'customize-preview' ), '20140120', true );
}
add_action( 'customize_preview_init', 'wisteria_customize_preview_js' );
