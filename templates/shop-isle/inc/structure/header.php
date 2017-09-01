<?php
/**
 * Template functions used for the site header.
 *
 * @package shop-isle
 */

if ( ! function_exists( 'shop_isle_primary_navigation' ) ) {
	/**
	 * Display Primary Navigation
	 *
	 * @since  1.0.0
	 * @return void
	 */
	function shop_isle_primary_navigation() {

		?>
		<!-- Navigation start -->
		<nav class="navbar navbar-custom navbar-transparent navbar-fixed-top" role="navigation">

			<div class="container">
				<div class="header-container">

					<div class="navbar-header">
						<?php

							$shop_isle_logo = get_theme_mod( 'shop_isle_logo' );
							echo '<div class="shop_isle_header_title"><div class="shop-isle-header-title-inner">';
						if ( ! empty( $shop_isle_logo ) ) :
							echo '<a href="' . esc_url( home_url( '/' ) ) . '" class="logo-image"><img src="' . esc_url( $shop_isle_logo ) . '"></a>';
							if ( is_customize_preview() ) :
								echo '<h1 class="site-title shop_isle_hidden_if_not_customizer""><a href="' . esc_url( home_url( '/' ) ) . '" title="' . esc_attr( get_bloginfo( 'name', 'display' ) ) . '" rel="home">' . get_bloginfo( 'name' ) . '</a></h1>';
								echo '<h2 class="site-description shop_isle_hidden_if_not_customizer"><a href="' . esc_url( home_url( '/' ) ) . '" title="' . esc_attr( get_bloginfo( 'name', 'display' ) ) . '" rel="home">' . get_bloginfo( 'description' ) . '</a></h2>';
								endif;
							else :
								if ( is_customize_preview() ) :
									echo '
											<a href="' . esc_url( home_url( '/' ) ) . '" class="logo-image shop_isle_hidden_if_not_customizer">
												<img src="">
											</a>
										';
								endif;
								echo '<h1 class="site-title"><a href="' . esc_url( home_url( '/' ) ) . '" title="' . esc_attr( get_bloginfo( 'name', 'display' ) ) . '" rel="home">' . get_bloginfo( 'name' ) . '</a></h1>';
								echo '<h2 class="site-description"><a href="' . esc_url( home_url( '/' ) ) . '" title="' . esc_attr( get_bloginfo( 'name', 'display' ) ) . '" rel="home">' . get_bloginfo( 'description' ) . '</a></h2>';
							endif;
							echo '</div></div>';
						?>

						<div type="button" class="navbar-toggle" data-toggle="collapse" data-target="#custom-collapse">
							<span class="sr-only"><?php _e( 'Toggle navigation','shop-isle' ); ?></span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
						</div>
					</div>

					<div class="header-menu-wrap">
						<div class="collapse navbar-collapse" id="custom-collapse">

							<?php
							wp_nav_menu(
								array(
									'theme_location' => 'primary',
									'container' => false,
									'menu_class' => 'nav navbar-nav navbar-right',
								)
							);
								?>

						</div>
					</div>

					<?php if ( class_exists( 'WooCommerce' ) ) : ?>
						<div class="navbar-cart">

							<div class="header-search">
								<div class="glyphicon glyphicon-search header-search-button"></div>
								<div class="header-search-input">
									<form role="search" method="get" class="woocommerce-product-search" action="<?php echo esc_url( home_url( '/' ) ); ?>">
										<input type="search" class="search-field" placeholder="<?php echo esc_attr_x( 'Search Products&hellip;', 'placeholder', 'shop-isle' ); ?>" value="<?php echo get_search_query(); ?>" name="s" title="<?php echo esc_attr_x( 'Search for:', 'label', 'shop-isle' ); ?>" />
										<input type="submit" value="<?php echo esc_attr_x( 'Search', 'submit button', 'shop-isle' ); ?>" />
										<input type="hidden" name="post_type" value="product" />
									</form>
								</div>
							</div>

							<?php if ( function_exists( 'WC' ) ) : ?>
								<div class="navbar-cart-inner">
									<a href="<?php echo esc_url( WC()->cart->get_cart_url() ); ?>" title="<?php esc_attr_e( 'View your shopping cart','shop-isle' ); ?>" class="cart-contents">
										<span class="icon-basket"></span>
										<span class="cart-item-number"><?php echo esc_html( trim( WC()->cart->get_cart_contents_count() ) ); ?></span>
									</a>
									<?php apply_filters( 'shop_isle_cart_icon', '' ); ?>
								</div>
							<?php endif; ?>

						</div>
					<?php endif; ?>

				</div>
			</div>

		</nav>
		<!-- Navigation end -->
		<?php
	}
}// End if().
