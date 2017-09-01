<?php
/**
 * Custom template tags used to integrate this theme with WooCommerce.
 *
 * @package WordPress
 * @subpackage Shop Isle
 */

if ( ! function_exists( 'shop_isle_cart_link' ) ) {

	/**
	 * Cart Link
	 * Displayed a link to the cart including the number of items present and the cart total.
	 *
	 * @since  1.0.0
	 */
	function shop_isle_cart_link() {
		?>
		<a class="cart-contents" href="<?php echo esc_url( esc_url( WC()->cart->get_cart_url() ) ); ?>"
		   title="<?php esc_attr_e( 'View your shopping cart', 'shop-isle' ); ?>">
			<?php
			echo wp_kses_data( WC()->cart->get_cart_subtotal() );
			?>
			<span class="count">
				<?php
				echo wp_kses_data(
					/* translators: d: number of items. */
					sprintf( _n( '%d item', '%d items', WC()->cart->get_cart_contents_count(), 'shop-isle' ), WC()->cart->get_cart_contents_count() )
				);
				?>
			</span>
		</a>
		<?php
	}
}

if ( ! function_exists( 'shop_isle_upsell_display' ) ) {

	/**
	 * Upsells
	 * Related products on single page and line above it
	 *
	 * @since   1.0.0
	 * @return  void
	 * @uses    woocommerce_upsell_display()
	 */
	function shop_isle_upsell_display() {
		echo '</div></div>';
		global $product;

		if ( function_exists( 'method_exists' ) && method_exists( $product, 'get_upsell_ids' ) ) {
			$upsells = $product->get_upsell_ids();
		} elseif ( function_exists( 'method_exists' ) && method_exists( $product, 'get_upsells' ) ) {
			$upsells = $product->get_upsells();
		}

		if ( ! empty( $upsells ) && ( count( $upsells ) > 0 ) ) {
			echo '<hr class="divider-w">';
		}
		echo '<div class="container">';
		woocommerce_upsell_display( - 1, 3 );
		$product_id = get_the_ID();

		if ( function_exists( 'wc_get_related_products' ) ) {
			$related = wc_get_related_products( $product_id );
		} elseif ( function_exists( 'method_exists' ) && method_exists( $product, 'get_related' ) ) {
			$related = $product->get_related();
		}
		if ( ! empty( $related ) && ( count( $related ) > 0 ) ) {
			echo '</div>';
			echo '<hr class="divider-w">';
			echo '<div class="container">';
		}
	}
}// End if().

/**
 * Sorting wrapper
 *
 * @since   1.4.3
 * @return  void
 */
function shop_isle_sorting_wrapper() {
	echo '<div class="row">';
	echo '<div class="col-sm-12">';
}

/**
 * Sorting wrapper close
 *
 * @since   1.4.3
 * @return  void
 */
function shop_isle_sorting_wrapper_close() {
	echo '</div>';
	echo '</div>';
}

/**
 * ShopIsle shop messages
 *
 * @since   1.4.4
 * @uses    do_shortcode
 */
function shop_isle_shop_messages() {
	if ( ! is_checkout() ) {
		echo wp_kses_post( do_shortcode( '[woocommerce_messages]' ) );
	}
}

if ( ! function_exists( 'shop_isle_woocommerce_pagination' ) ) {

	/**
	 * Pagination on shop page
	 *
	 * @since  1.0.0
	 */
	function shop_isle_woocommerce_pagination() {
		if ( woocommerce_products_will_display() ) {
			woocommerce_pagination();
		}
	}
}
