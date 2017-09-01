<?php
/**
 * Template functions used for the site footer.
 *
 * @package WordPress
 * @subpackage Shop Isle
 */

if ( ! function_exists( 'shop_isle_footer_widgets' ) ) {
	/**
	 * Display the footer widgets
	 *
	 * @since  1.0.0
	 * @return void
	 */
	function shop_isle_footer_widgets() {
		?>
		<!-- Widgets start -->

	<?php if ( is_active_sidebar( 'sidebar-footer-area-1' ) || is_active_sidebar( 'sidebar-footer-area-2' ) || is_active_sidebar( 'sidebar-footer-area-3' ) || is_active_sidebar( 'sidebar-footer-area-4' ) ) : ?>

		<div class="module-small bg-dark shop_isle_footer_sidebar">
			<div class="container">
				<div class="row">

					<?php if ( is_active_sidebar( 'sidebar-footer-area-1' ) ) : ?>
						<div class="col-sm-6 col-md-3 footer-sidebar-wrap">
							<?php dynamic_sidebar( 'sidebar-footer-area-1' ); ?>
						</div>
					<?php endif; ?>
					<!-- Widgets end -->

					<?php if ( is_active_sidebar( 'sidebar-footer-area-2' ) ) : ?>
						<div class="col-sm-6 col-md-3 footer-sidebar-wrap">
							<?php dynamic_sidebar( 'sidebar-footer-area-2' ); ?>
						</div>
					<?php endif; ?>
					<!-- Widgets end -->

					<?php if ( is_active_sidebar( 'sidebar-footer-area-3' ) ) : ?>
						<div class="col-sm-6 col-md-3 footer-sidebar-wrap">
							<?php dynamic_sidebar( 'sidebar-footer-area-3' ); ?>
						</div>
					<?php endif; ?>
					<!-- Widgets end -->


					<?php if ( is_active_sidebar( 'sidebar-footer-area-4' ) ) : ?>
						<div class="col-sm-6 col-md-3 footer-sidebar-wrap">
							<?php dynamic_sidebar( 'sidebar-footer-area-4' ); ?>
						</div>
					<?php endif; ?>
					<!-- Widgets end -->

				</div><!-- .row -->
			</div>
		</div>

	<?php endif; ?>

		<?php
	}
}// End if().

if ( ! function_exists( 'shop_isle_footer_copyright_and_socials' ) ) {
	/**
	 * Display the theme copyright and socials
	 *
	 * @since  1.0.0
	 * @return void
	 */
	function shop_isle_footer_copyright_and_socials() {

		?>
		<!-- Footer start -->
		<footer class="footer bg-dark">
			<!-- Divider -->
			<hr class="divider-d">
			<!-- Divider -->
			<div class="container">

				<div class="row">

					<?php
					/* Copyright */
					$shop_isle_copyright = apply_filters( 'shop_isle_footer_copyright_filter', get_theme_mod( 'shop_isle_copyright' ) );
					echo '<div class="col-sm-6">';
					if ( ! empty( $shop_isle_copyright ) ) :
						echo '<p class="copyright font-alt">' . $shop_isle_copyright . '</p>';
						endif;

						$shop_isle_site_info_hide = apply_filters( 'shop_isle_footer_socials_filter', get_theme_mod( 'shop_isle_site_info_hide' ) );
					if ( isset( $shop_isle_site_info_hide ) && $shop_isle_site_info_hide != 1 ) {
						echo apply_filters( 'shop_isle_site_info','<p class="shop-isle-poweredby-box"><a class="shop-isle-poweredby" href="http://themeisle.com/themes/shop-isle/" rel="nofollow">ShopIsle </a>' . __( 'powered by','shop-isle' ) . '<a class="shop-isle-poweredby" href="http://wordpress.org/" rel="nofollow"> WordPress</a></p>' );
					}
					echo '</div>';

					/* Socials icons */

					$shop_isle_socials = get_theme_mod( 'shop_isle_socials' );

					if ( ! empty( $shop_isle_socials ) ) :

						$shop_isle_socials_decoded = json_decode( $shop_isle_socials );

						if ( ! empty( $shop_isle_socials_decoded ) ) :

							echo '<div class="col-sm-6">';

								echo '<div class="footer-social-links">';

							foreach ( $shop_isle_socials_decoded as $shop_isle_social ) :

								$icon_value = ! empty( $shop_isle_social->icon_value ) ? apply_filters( 'shop_isle_translate_single_string', $shop_isle_social->icon_value, 'Footer socials' ) : '';
								$link = ! empty( $shop_isle_social->link ) ? apply_filters( 'shop_isle_translate_single_string', $shop_isle_social->link, 'Footer socials' ) : '';

								if ( ! empty( $icon_value ) && ! empty( $link ) ) {
									echo '<a href="' . esc_url( $link ) . '"><span class="' . esc_attr( $icon_value ) . '"></span></a>';
								}
									endforeach;

								echo '</div>';

							echo '</div>';

						endif;

					endif;
					?>
				</div><!-- .row -->

			</div>
		</footer>
		<!-- Footer end -->
		<?php
	}
}// End if().


if ( ! function_exists( 'shop_isle_footer_wrap_open' ) ) {
	/**
	 * Display the theme copyright and socials
	 *
	 * @since  1.0.0
	 * @return void
	 */
	function shop_isle_footer_wrap_open() {
		echo '</div><div class="bottom-page-wrap">';
	}
}


if ( ! function_exists( 'shop_isle_footer_wrap_close' ) ) {
	/**
	 * Display the theme copyright and socials
	 *
	 * @since  1.0.0
	 * @return void
	 */
	function shop_isle_footer_wrap_close() {
		echo '</div><!-- .bottom-page-wrap -->';
	}
}
