<?php
/**
 * The Banners Section
 *
 * @package WordPress
 * @subpackage Shop Isle
 */

		/* BANNERS */


		$shop_isle_banners_hide = get_theme_mod( 'shop_isle_banners_hide' );
		$shop_isle_banners_title = get_theme_mod( 'shop_isle_banners_title' );

if ( isset( $shop_isle_banners_hide ) && $shop_isle_banners_hide != 1 ) :
	echo '<section class="module-small home-banners">';
		elseif ( is_customize_preview() ) :
			echo '<section class="module-small home-banners shop_isle_hidden_if_not_customizer">';
		endif;

		if ( ( isset( $shop_isle_banners_hide ) && $shop_isle_banners_hide != 1) || is_customize_preview() ) :

			$shop_isle_banners = get_theme_mod(
				'shop_isle_banners', json_encode(
					array(
						array(
							'image_url' => get_template_directory_uri() . '/assets/images/banner1.jpg',
							'link' => '#',
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
				)
			);

			if ( ! empty( $shop_isle_banners ) ) :

				$shop_isle_banners_decoded = json_decode( $shop_isle_banners );

				if ( ! empty( $shop_isle_banners_decoded ) ) :

					echo '<div class="container">';

					if ( ! empty( $shop_isle_banners_title ) ) {
						echo '<div class="row">';
						echo '<div class="col-sm-6 col-sm-offset-3">';
						echo '<h2 class="module-title font-alt product-banners-title">' . $shop_isle_banners_title . '</h2>';
						echo '</div>';
						echo '</div>';

					} elseif ( is_customize_preview() ) {
						echo '<div class="row">';
						echo '<div class="col-sm-6 col-sm-offset-3">';
						echo '<h2 class="module-title font-alt product-banners-title shop_isle_hidden_if_not_customizer"></h2>';
						echo '</div>';
						echo '</div>';
					}

						echo '<div class="row shop_isle_bannerss_section">';

					foreach ( $shop_isle_banners_decoded as $shop_isle_banner ) :

						$image_url = ! empty( $shop_isle_banner->image_url ) ? apply_filters( 'shop_isle_translate_single_string', $shop_isle_banner->image_url, 'Banners section' ) : '';
						$link = ! empty( $shop_isle_banner->link ) ? apply_filters( 'shop_isle_translate_single_string', $shop_isle_banner->link, 'Banners section' ) : '';

						if ( ! empty( $image_url ) ) {

							$shop_isle_alt_image = '';
							$image_id = function_exists( 'attachment_url_to_postid' ) ? attachment_url_to_postid( $image_url ) : '';
							if ( ! empty( $image_id ) && $image_id !== 0 ) {
								$shop_isle_alt_image = 'alt="' . esc_attr( get_post_meta( $image_id, '_wp_attachment_image_alt', true ) ) . '"';
							}

							echo '<div class="col-sm-4"><div class="content-box mt-0 mb-0"><div class="content-box-image">';

							if ( ! empty( $link ) ) {
								echo '<a href="' . esc_url( $link ) . '"><img src="' . esc_url( $image_url ) . '" ' . $shop_isle_alt_image . '></a>';
							} else {
								echo '<a><img src="' . esc_url( $image_url ) . '"></a>';
							}
							echo '</div></div></div>';

						}
						endforeach;

						echo '</div>';

					echo '</div>';
				endif;

			endif;
			echo '</section>';

			echo '<hr class="divider-w">';

endif;  /* END BANNERS */
