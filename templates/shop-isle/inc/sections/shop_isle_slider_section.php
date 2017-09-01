<?php
/**
 * Front page Slider Section
 *
 * @package WordPress
 * @subpackage Shop Isle
 */

$shop_isle_slider_hide = get_theme_mod( 'shop_isle_slider_hide', false );
if ( ! empty( $shop_isle_slider_hide ) && (bool) $shop_isle_slider_hide === true ) {
	return;
}
$shop_isle_homepage_slider_shortcode = get_theme_mod( 'shop_isle_homepage_slider_shortcode' );

echo '<section id="home" class="home-section home-parallax home-fade ' . ( empty( $shop_isle_homepage_slider_shortcode ) ? ' home-full-height' : ' home-slider-plugin' ) . '">';

if ( ! empty( $shop_isle_homepage_slider_shortcode ) ) {
	echo do_shortcode( $shop_isle_homepage_slider_shortcode );
} else {

	$shop_isle_slider = get_theme_mod(
		'shop_isle_slider', json_encode(
			array(
				array(
					'image_url' => get_template_directory_uri() . '/assets/images/slide1.jpg',
					'link'      => '#',
					'text'      => __( 'Shop Isle', 'shop-isle' ),
					'subtext'   => __( 'WooCommerce Theme', 'shop-isle' ),
					'label'     => __( 'Read more', 'shop-isle' ),
				),
				array(
					'image_url' => get_template_directory_uri() . '/assets/images/slide2.jpg',
					'link'      => '#',
					'text'      => __( 'Shop Isle', 'shop-isle' ),
					'subtext'   => __( 'WooCommerce Theme', 'shop-isle' ),
					'label'     => __( 'Read more', 'shop-isle' ),
				),
				array(
					'image_url' => get_template_directory_uri() . '/assets/images/slide3.jpg',
					'link'      => '#',
					'text'      => __( 'Shop Isle', 'shop-isle' ),
					'subtext'   => __( 'WooCommerce Theme', 'shop-isle' ),
					'label'     => __( 'Read more', 'shop-isle' ),
				),
			)
		)
	);

	if ( ! empty( $shop_isle_slider ) ) {

		$shop_isle_slider_decoded = json_decode( $shop_isle_slider );

		if ( ! empty( $shop_isle_slider_decoded ) ) {

			echo '<div class="hero-slider">';

			echo '<ul class="slides">';

			foreach ( $shop_isle_slider_decoded as $shop_isle_slide ) {

				$image_url = ! empty( $shop_isle_slide->image_url ) ? apply_filters( 'shop_isle_translate_single_string', $shop_isle_slide->image_url, 'Slider section' ) : '';
				$text = ! empty( $shop_isle_slide->text ) ? apply_filters( 'shop_isle_translate_single_string', $shop_isle_slide->text, 'Slider section' ) : '';
				$subtext = ! empty( $shop_isle_slide->subtext ) ? apply_filters( 'shop_isle_translate_single_string', $shop_isle_slide->subtext, 'Slider section' ) : '';
				$link = ! empty( $shop_isle_slide->link ) ? apply_filters( 'shop_isle_translate_single_string', $shop_isle_slide->link, 'Slider section' ) : '';
				$label = ! empty( $shop_isle_slide->label ) ? apply_filters( 'shop_isle_translate_single_string', $shop_isle_slide->label, 'Slider section' ) : '';


				if ( ! empty( $image_url ) ) {

					echo '<li class="bg-dark-30 bg-dark" style="background-image:url(' . esc_url( $image_url ) . ')">';
					echo '<div class="hs-caption">';
					echo '<div class="caption-content">';

					if ( ! empty( $text ) ) {
						echo '<div class="hs-title-size-4 font-alt mb-30">' . wp_kses_post( $text ) . '</div>';
					}

					if ( ! empty( $subtext ) ) {
						echo '<div class="hs-title-size-1 font-alt mb-40">' . wp_kses_post( $subtext ) . '</div>';
					}

					if ( ! empty( $link ) && ! empty( $label ) ) {
						echo '<a href="' . esc_url( $link ) . '" class="section-scroll btn btn-border-w btn-round">' . wp_kses_post( $label ) . '</a>';
					}

					echo '</div>';
					echo '</div>';
					echo '</li>';

				}// End if().
			}// End foreach().

			echo '</ul>';

			echo '</div>';

		}// End if().
	}// End if().
}// End if().

echo '</section >';



