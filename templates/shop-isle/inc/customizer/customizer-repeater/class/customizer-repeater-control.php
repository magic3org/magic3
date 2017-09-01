<?php
if ( ! class_exists( 'WP_Customize_Control' ) ) {
	return null;
}

class Shop_Isle_Repeater_Controler extends WP_Customize_Control {

	public $id;
	private $boxtitle = array();
	private $shop_isle_image_control = false;
	private $shop_isle_icon_control = false;
	private $shop_isle_title_control = false;
	private $shop_isle_subtext_control = false;
	private $shop_isle_text_control = false;
	private $shop_isle_link_control = false;
	private $shop_isle_label_control = false;
	private $shop_isle_shortcode_control = false;
	private $shop_isle_description_control = false;
	private $shop_isle_repeater_control = false;

	/*Class constructor*/
	public function __construct( $manager, $id, $args = array() ) {
		parent::__construct( $manager, $id, $args );
		/*Get options from customizer.php*/
		$this->boxtitle   = __( 'Customizer Repeater','shop-isle' );
		if ( ! empty( $this->label ) ) {
			$this->boxtitle = $this->label;
		}

		if ( ! empty( $args['shop_isle_image_control'] ) ) {
			$this->shop_isle_image_control = $args['shop_isle_image_control'];
		}

		if ( ! empty( $args['shop_isle_icon_control'] ) ) {
			$this->shop_isle_icon_control = $args['shop_isle_icon_control'];
		}

		if ( ! empty( $args['shop_isle_title_control'] ) ) {
			$this->shop_isle_title_control = $args['shop_isle_title_control'];
		}

		if ( ! empty( $args['shop_isle_subtext_control'] ) ) {
			$this->shop_isle_subtext_control = $args['shop_isle_subtext_control'];
		}

		if ( ! empty( $args['shop_isle_text_control'] ) ) {
			$this->shop_isle_text_control = $args['shop_isle_text_control'];
		}

		if ( ! empty( $args['shop_isle_link_control'] ) ) {
			$this->shop_isle_link_control = $args['shop_isle_link_control'];
		}

		if ( ! empty( $args['shop_isle_label_control'] ) ) {
			$this->shop_isle_label_control = $args['shop_isle_label_control'];
		}

		if ( ! empty( $args['shop_isle_shortcode_control'] ) ) {
			$this->shop_isle_shortcode_control = $args['shop_isle_shortcode_control'];
		}

		if ( ! empty( $args['shop_isle_description_control'] ) ) {
			$this->shop_isle_description_control = $args['shop_isle_description_control'];
		}

		if ( ! empty( $args['shop_isle_repeater_control'] ) ) {
			$this->shop_isle_repeater_control = $args['shop_isle_repeater_control'];
		}

		if ( ! empty( $args['id'] ) ) {
			$this->id = $args['id'];
		}
	}

	/*Enqueue resources for the control*/
	public function enqueue() {

		wp_enqueue_style( 'eleganticons-style', get_template_directory_uri() . '/inc/customizer/customizer-repeater/css/eleganticons.css','1.0.0' );

		wp_enqueue_style( 'customizer-repeater-admin-stylesheet', get_template_directory_uri() . '/inc/customizer/customizer-repeater/css/admin-style.css','1.0.0' );

		wp_enqueue_script( 'customizer-repeater-script', get_template_directory_uri() . '/inc/customizer/customizer-repeater/js/customizer_repeater.js', array( 'jquery', 'jquery-ui-draggable' ), '1.0.1', true );

		wp_enqueue_script( 'customizer-repeater-iconpicker', get_template_directory_uri() . '/inc/customizer/customizer-repeater/js/iconpicker.min.js', array( 'jquery' ), '1.0.0', true );

		wp_enqueue_script( 'customizer-repeater-iconpicker-control', get_template_directory_uri() . '/inc/customizer/customizer-repeater/js/iconpicker-control.js', array( 'jquery' ), '1.0.0', true );

		wp_enqueue_style( 'customizer-repeater-iconpicker-style', get_template_directory_uri() . '/inc/customizer/customizer-repeater/css/iconpicker.min.css' );
	}

	public function render_content() {

		/*Get default options*/
		$this_default = json_decode( $this->setting->default );

		/*Get values (json format)*/
		$values = $this->value();

		/*Decode values*/
		$json = json_decode( $values );

		if ( ! is_array( $json ) ) {
			$json = array( $values );
		} ?>

		<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
		<div class="customizer-repeater-general-control-repeater customizer-repeater-general-control-droppable">
			<?php
			if ( ( count( $json ) == 1 && '' === $json[0] ) || empty( $json ) ) {
				if ( ! empty( $this_default ) ) {
					$this->iterate_array( $this_default ); ?>
					<input type="hidden"
					       id="customizer-repeater-<?php echo $this->id; ?>-colector" <?php $this->link(); ?>
					       class="customizer-repeater-colector"
					       value="<?php echo esc_textarea( json_encode( $this_default ) ); ?>"/>
					<?php
				} else {
					$this->iterate_array(); ?>
					<input type="hidden"
					       id="customizer-repeater-<?php echo $this->id; ?>-colector" <?php $this->link(); ?>
					       class="customizer-repeater-colector"/>
					<?php
				}
			} else {
				$this->iterate_array( $json ); ?>
				<input type="hidden" id="customizer-repeater-<?php echo $this->id; ?>-colector" <?php $this->link(); ?>
				       class="customizer-repeater-colector" value="<?php echo esc_textarea( $this->value() ); ?>"/>
				<?php
			} ?>
			</div>
		<button type="button" class="button add_field customizer-repeater-new-field">
			<?php esc_html_e( 'Add new field', 'shop-isle' ); ?>
		</button>
		<?php
	}

	private function iterate_array( $array = array() ) {
		/*Counter that helps checking if the box is first and should have the delete button disabled*/
		$it = 0;
		if ( ! empty( $array ) ) {
			foreach ( $array as $icon ) { ?>
				<div class="customizer-repeater-general-control-repeater-container customizer-repeater-draggable">
					<div class="customizer-repeater-customize-control-title">
						<?php echo esc_html( $this->boxtitle ); ?>
					</div>
					<div class="customizer-repeater-box-content-hidden">
						<?php
						$choice = $image_url = $icon_value = $title = $subtext = $text = $link = $shortcode = $description = $repeater = $label = '';
						if ( ! empty( $icon->choice ) ) {
							$choice = $icon->choice;
						}
						if ( ! empty( $icon->image_url ) ) {
							$image_url = $icon->image_url;
						}
						if ( ! empty( $icon->icon_value ) ) {
							$icon_value = $icon->icon_value;
						}
						if ( ! empty( $icon->title ) ) {
							$title = $icon->title;
						}
						if ( ! empty( $icon->subtext ) ) {
							$subtext = $icon->subtext;
						}
						if ( ! empty( $icon->text ) ) {
							$text = $icon->text;
						}
						if ( ! empty( $icon->link ) ) {
							$link = $icon->link;
						}
						if ( ! empty( $icon->label ) ) {
							$label = $icon->label;
						}
						if ( ! empty( $icon->shortcode ) ) {
							$shortcode = $icon->shortcode;
						}
						if ( ! empty( $icon->description ) ) {
							$description = $icon->description;
						}
						if ( ! empty( $icon->social_repeater ) ) {
							$repeater = $icon->social_repeater;
						}

						if ( $this->shop_isle_image_control == true && $this->shop_isle_icon_control == true ) {
							$this->icon_type_choice( $choice );
						}
						if ( $this->shop_isle_image_control == true ) {
							$this->image_control( $image_url, $choice );
						}
						if ( $this->shop_isle_icon_control == true ) {
							$this->icon_picker_control( $icon_value, $choice );
						}
						if ( $this->shop_isle_title_control == true ) {
							$this->input_control(array(
								'label' => __( 'Title','shop-isle' ),
								'class' => 'customizer-repeater-title-control',
							), $title);
						}
						if ( $this->shop_isle_text_control == true ) {
							$this->input_control(array(
								'label' => __( 'Title','shop-isle' ),
								'class' => 'customizer-repeater-text-control',
							), $text);
						}
						if ( $this->shop_isle_subtext_control == true ) {
							$this->input_control(array(
								'label' => __( 'Subtitle','shop-isle' ),
								'class' => 'customizer-repeater-subtext-control',
							), $subtext);
						}
						if ( $this->shop_isle_link_control ) {
							$this->input_control(array(
								'label' => __( 'Button link','shop-isle' ),
								'class' => 'customizer-repeater-link-control',
								'sanitize_callback' => 'esc_url',
							), $link);
						}
						if ( $this->shop_isle_label_control ) {
							$this->input_control(array(
								'label' => __( 'Button Label','shop-isle' ),
								'class' => 'customizer-repeater-label-control',
								'sanitize_callback' => 'esc_html',
							), $label);
						}
						if ( $this->shop_isle_shortcode_control == true ) {
							$this->input_control(array(
								'label' => __( 'Shortcode','shop-isle' ),
								'class' => 'customizer-repeater-shortcode-control',
							), $shortcode);
						}
						if ( $this->shop_isle_description_control == true ) {
							$this->input_control(array(
								'label' => __( 'Description','shop-isle' ),
								'class' => 'customizer-repeater-description-control',
							), $description);
						}
						if ( $this->shop_isle_repeater_control == true ) {
							$this->repeater_control( $repeater );
						} ?>

						<input type="hidden" class="social-repeater-box-id" value="<?php if ( ! empty( $this->id ) ) {
							echo esc_attr( $this->id );
} ?>">
						<button type="button" class="social-repeater-general-control-remove-field button" <?php if ( $it == 0 ) {
							echo 'style="display:none;"';
} ?>>
							<?php esc_html_e( 'Delete field', 'shop-isle' ); ?>
						</button>

					</div>
				</div>

				<?php
				$it++;
			}
		} else { ?>
			<div class="customizer-repeater-general-control-repeater-container">
				<div class="customizer-repeater-customize-control-title">
					<?php echo esc_html( $this->boxtitle ); ?>
				</div>
				<div class="customizer-repeater-box-content-hidden">
					<?php
					if ( $this->shop_isle_image_control == true && $this->shop_isle_icon_control == true ) {
						$this->icon_type_choice();
					}
					if ( $this->shop_isle_image_control == true ) {
						$this->image_control();
					}
					if ( $this->shop_isle_icon_control == true ) {
						$this->icon_picker_control();
					}
					if ( $this->shop_isle_title_control == true ) {
						$this->input_control( array(
							'label' => __( 'Title', 'shop-isle' ),
							'class' => 'customizer-repeater-title-control',
						) );
					}
					if ( $this->shop_isle_text_control == true ) {
						$this->input_control( array(
							'label' => __( 'Title', 'shop-isle' ),
							'class' => 'customizer-repeater-text-control',
							'type'  => 'text',
						) );
					}
					if ( $this->shop_isle_subtext_control == true ) {
						$this->input_control( array(
							'label' => __( 'Subtitle', 'shop-isle' ),
							'class' => 'customizer-repeater-subtext-control',
						) );
					}
					if ( $this->shop_isle_link_control == true ) {
						$this->input_control( array(
							'label' => __( 'Button link', 'shop-isle' ),
							'class' => 'customizer-repeater-link-control',
						) );
					}
					if ( $this->shop_isle_label_control == true ) {
						$this->input_control( array(
							'label' => __( 'Button Label', 'shop-isle' ),
							'class' => 'customizer-repeater-link-control',
						) );
					}
					if ( $this->shop_isle_shortcode_control == true ) {
						$this->input_control( array(
							'label' => __( 'Shortcode', 'shop-isle' ),
							'class' => 'customizer-repeater-shortcode-control',
						) );
					}
					if ( $this->shop_isle_description_control == true ) {
						$this->input_control( array(
							'label' => __( 'Description', 'shop-isle' ),
							'class' => 'customizer-repeater-description-control',
						) );
					}
					if ( $this->shop_isle_repeater_control == true ) {
						$this->repeater_control();
					} ?>
					<input type="hidden" class="social-repeater-box-id">
					<button type="button" class="social-repeater-general-control-remove-field button" style="display:none;">
						<?php esc_html_e( 'Delete field', 'shop-isle' ); ?>
					</button>
				</div>
			</div>
			<?php
		}
	}

	private function input_control( $options, $value = '' ) {
	?>
		<span class="customize-control-title"><?php echo $options['label']; ?></span>
		<?php
		if ( ! empty( $options['type'] ) && $options['type'] === 'textarea' ) { ?>
			<textarea class="<?php echo esc_attr( $options['class'] ); ?>" placeholder="<?php echo $options['label']; ?>"><?php echo ( ! empty( $options['sanitize_callback'] ) ?  call_user_func_array( $options['sanitize_callback'], array( $value ) ) : esc_attr( $value ) ); ?></textarea>
			<?php
		} else { ?>
			<input type="text" value="<?php echo ( ! empty( $options['sanitize_callback'] ) ?  call_user_func_array( $options['sanitize_callback'], array( $value ) ) : esc_attr( $value ) ); ?>" class="<?php echo esc_attr( $options['class'] ); ?>" placeholder="<?php echo $options['label']; ?>"/>
			<?php
		}
	}

	private function icon_picker_control( $value = '', $show = '' ) {
	?>
		<div class="social-repeater-general-control-icon" <?php if ( $show === 'shop_isle_image' || $show === 'shop_isle_none' ) { echo 'style="display:none;"'; } ?>>
			<span class="customize-control-title">
				<?php esc_html_e( 'Icon','shop-isle' ); ?>
			</span>
			<span class="description customize-control-description">
				<?php
				echo sprintf(
	                __( 'Some icons may not be displayed here. You can see the list of icons %1$s', 'shop-isle' ),
	                sprintf( '<a href="https://www.elegantthemes.com/blog/resources/elegant-icon-font" rel="nofollow" target="_blank">%s</a>', esc_html__( 'here', 'shop-isle' ) )
				); ?>
			</span>
			<div class="input-group icp-container">
				<input data-placement="bottomRight" class="icp icp-auto" value="<?php if ( ! empty( $value ) ) { echo esc_attr( $value );} ?>" type="text">
				<span class="input-group-addon"></span>
			</div>
		</div>
		<?php
	}

	private function image_control( $value = '', $show = '' ) {
	?>
		<div class="customizer-repeater-image-control" <?php if ( $show === 'shop_isle_icon' || $show === 'shop_isle_none' ) { echo 'style="display:none;"'; } ?>>
			<span class="customize-control-title">
				<?php esc_html_e( 'Image','shop-isle' )?>
			</span>
			<input type="text" class="widefat custom-media-url" value="<?php echo esc_attr( $value ); ?>">
			<input type="button" class="button button-primary customizer-repeater-custom-media-button" value="<?php esc_html_e( 'Upload Image','shop-isle' ); ?>" />
		</div>
		<?php
	}

	private function icon_type_choice( $value = 'shop_isle_icon' ) {
	?>
		<span class="customize-control-title">
			<?php esc_html_e( 'Image type','shop-isle' );?>
		</span>
		<select class="customizer-repeater-image-choice">
			<option value="shop_isle_icon" <?php selected( $value,'shop_isle_icon' );?>><?php esc_html_e( 'Icon','shop-isle' ); ?></option>
			<option value="shop_isle_image" <?php selected( $value,'shop_isle_image' );?>><?php esc_html_e( 'Image','shop-isle' ); ?></option>
			<option value="shop_isle_none" <?php selected( $value,'shop_isle_none' );?>><?php esc_html_e( 'None','shop-isle' ); ?></option>
		</select>
		<?php
	}

	private function repeater_control( $value = '' ) {
		$social_repeater = array();
		$show_del        = 0; ?>
		<span class="customize-control-title"><?php esc_html_e( 'Social icons', 'shop-isle' ); ?></span>
		<?php
		if ( ! empty( $value ) ) {
			$social_repeater = json_decode( html_entity_decode( $value ), true );
		}
		if ( ( count( $social_repeater ) == 1 && '' === $social_repeater[0] ) || empty( $social_repeater ) ) { ?>
			<div class="customizer-repeater-social-repeater">
				<div class="customizer-repeater-social-repeater-container">
					<div class="customizer-repeater-rc input-group icp-container">
						<input data-placement="bottomRight" class="icp icp-auto" value="<?php if ( ! empty( $value ) ) { echo esc_attr( $value ); } ?>" type="text">
						<span class="input-group-addon"></span>
					</div>

					<input type="text" class="customizer-repeater-social-repeater-link"
					       placeholder="<?php esc_html_e( 'Button link', 'shop-isle' ); ?>">
					<input type="hidden" class="customizer-repeater-social-repeater-id" value="">
				</div>
				<input type="hidden" id="social-repeater-socials-repeater-colector" class="social-repeater-socials-repeater-colector" value=""/>
			</div>
			<button class="social-repeater-add-social-item"><?php esc_html_e( 'Add icon', 'shop-isle' ); ?></button>
			<?php
		} else { ?>
			<div class="customizer-repeater-social-repeater">
				<?php
				foreach ( $social_repeater as $social_icon ) {
					$show_del ++; ?>
					<div class="customizer-repeater-social-repeater-container">
						<div class="customizer-repeater-rc input-group icp-container">
							<input data-placement="bottomRight" class="icp icp-auto" value="<?php if ( ! empty( $social_icon['icon'] ) ) { echo esc_attr( $social_icon['icon'] ); } ?>" type="text">
							<span class="input-group-addon"></span>
						</div>
						<input type="text" class="customizer-repeater-social-repeater-link"
						       placeholder="<?php esc_html_e( 'Button link', 'shop-isle' ); ?>"
						       value="<?php if ( ! empty( $social_icon['link'] ) ) {
									echo esc_url( $social_icon['link'] );
} ?>">
						<input type="hidden" class="customizer-repeater-social-repeater-id"
						       value="<?php if ( ! empty( $social_icon['id'] ) ) {
									echo esc_attr( $social_icon['id'] );
} ?>">
					</div>
					<?php
				} ?>
				<input type="hidden" id="social-repeater-socials-repeater-colector"
				       class="social-repeater-socials-repeater-colector"
				       value="<?php echo esc_textarea( html_entity_decode( $value ) ); ?>" />
			</div>
			<button class="social-repeater-add-social-item"><?php esc_html_e( 'Add icon', 'shop-isle' ); ?></button>
			<?php
		}
	}
}
