<?php
/**
 * General functions used to integrate this theme with WooCommerce.
 *
 * @package WordPress
 * @subpackage Shop Isle
 */


if ( ! function_exists( 'shop_isle_before_content' ) ) {
	/**
	 * Before Content
	 * Wraps all WooCommerce content in wrappers which match the theme markup
	 *
	 * @since   1.0.0
	 * @return  void
	 */
	function shop_isle_before_content() {
		?>
		<div class="main">
			<?php
	}
}

if ( ! function_exists( 'shop_isle_after_content' ) ) {
	/**
	 * After Content
	 * Closes the wrapping divs
	 *
	 * @since   1.0.0
	 * @return  void
	 */
	function shop_isle_after_content() {
		?>
		</div><!-- .main -->

		<?php
	}
}

if ( ! function_exists( 'shop_isle_shop_page_wrapper' ) ) {

	/**
	 * Before Shop loop
	 *
	 * @since   1.0.0
	 * @return  void
	 */
	function shop_isle_shop_page_wrapper() {
		?>
		<section class="module-small module-small-shop">
				<div class="container">

				<?php
				if ( is_shop() || is_product_tag() || is_product_category() ) :

						do_action( 'shop_isle_before_shop' );

					if ( is_active_sidebar( 'shop-isle-sidebar-shop-archive' ) ) :
					?>

							<div class="col-sm-9 shop-with-sidebar" id="shop-isle-blog-container">

						<?php endif; ?>

				<?php endif; ?>

		<?php
	}
}

/**
 * Before Product content
 *
 * @since   1.0.0
 * @return  void
 */
function shop_isle_product_page_wrapper() {
	echo '<section class="module module-super-small">
			<div class="container product-main-content">';
}

if ( ! function_exists( 'shop_isle_product_page_wrapper_end' ) ) {
	/**
	 * After Product content
	 * Closes the wrapping div and section
	 *
	 * @since   1.0.0
	 * @return  void
	 */
	function shop_isle_product_page_wrapper_end() {
		?>
			</div><!-- .container -->
		</section><!-- .module-small -->
			<?php
	}
}

if ( ! function_exists( 'shop_isle_shop_page_wrapper_end' ) ) {
	/**
	 * After Shop loop
	 * Closes the wrapping div and section
	 *
	 * @since   1.0.0
	 * @return  void
	 */
	function shop_isle_shop_page_wrapper_end() {
		?>

			<?php if ( (is_shop() || is_product_category() || is_product_tag() ) && is_active_sidebar( 'shop-isle-sidebar-shop-archive' ) ) : ?>

				</div>

				<!-- Sidebar column start -->
				<div class="col-sm-3 col-md-3 sidebar sidebar-shop">
					<?php do_action( 'shop_isle_sidebar_shop_archive' ); ?>
				</div>
				<!-- Sidebar column end -->

			<?php endif; ?>

			</div><!-- .container -->
		</section><!-- .module-small -->
		<?php
	}
}

/**
 * Default loop columns on product archives
 *
 * @return integer products per row
 * @since  1.0.0
 */
function shop_isle_loop_columns() {
	if ( is_active_sidebar( 'shop-isle-sidebar-shop-archive' ) ) {
		return apply_filters( 'shop_isle_loop_columns', 3 ); // 3 products per row
	} else {
		return apply_filters( 'shop_isle_loop_columns', 4 ); // 4 products per row
	}
}

/**
 * Add 'woocommerce-active' class to the body tag
 *
 * @param  array $classes body classes.
 *
 * @return array $classes modified to include 'woocommerce-active' class.
 */
function shop_isle_woocommerce_body_class( $classes ) {
	if ( is_woocommerce_activated() ) {
		$classes[] = 'woocommerce-active';
	}

	return $classes;
}

if ( ! function_exists( 'shop_isle_cart_link_fragment' ) ) {
	/**
	 * Cart Fragments
	 * Ensure cart contents update when products are added to the cart via AJAX
	 *
	 * @param  array $fragments Fragments to refresh via AJAX.
	 *
	 * @return array            Fragments to refresh via AJAX.
	 */
	function shop_isle_cart_link_fragment( $fragments ) {
		global $woocommerce;

		ob_start();

		shop_isle_cart_link();

		$fragments['a.cart-contents'] = ob_get_clean();

		return $fragments;
	}
}

/**
 * WooCommerce specific scripts & stylesheets
 *
 * @since 1.0.0
 */
function shop_isle_woocommerce_scripts() {

	wp_enqueue_style( 'shop-isle-woocommerce-style1', get_template_directory_uri() . '/inc/woocommerce/css/woocommerce.css', array(), 'v3' );
}

/**
 * Related Products Args
 *
 * @param  array $args related products args.
 *
 * @since 1.0.0
 * @return  array $args related products args.
 */
function shop_isle_related_products_args( $args ) {
	$args = apply_filters(
		'shop_isle_related_products_args', array(
			'posts_per_page' => 4,
			'columns'        => 4,
		)
	);

	return $args;
}

/**
 * Product gallery thumnail columns
 *
 * @return integer number of columns
 * @since  1.0.0
 */
function shop_isle_thumbnail_columns() {
	return intval( apply_filters( 'shop_isle_product_thumbnail_columns', 4 ) );
}

/**
 * Products per page
 *
 * @return integer number of products
 * @since  1.0.0
 */
function shop_isle_products_per_page() {
	return intval( apply_filters( 'shop_isle_products_per_page', 12 ) );
}

/**
 * Query WooCommerce Extension Activation.
 *
 * @var  $extension main extension class name
 * @return boolean
 */
function is_woocommerce_extension_activated( $extension = 'WC_Bookings' ) {
	return class_exists( $extension ) ? true : false;
}

/**
 * Header for shop page
 *
 * @since  1.0.0
 */
function shop_isle_header_shop_page( $page_title ) {

	$shop_isle_title = '';

	$shop_isle_header_image = get_header_image();
	if ( ! empty( $shop_isle_header_image ) ) :
		$shop_isle_title = '<section class="' . ( is_woocommerce() ? 'woocommerce-page-title ' : '' ) . 'page-header-module module bg-dark" data-background="' . $shop_isle_header_image . '">';
	else :
		$shop_isle_title = '<section class="page-header-module module bg-dark">';
	endif;

		$shop_isle_title .= '<div class="container">';

			$shop_isle_title .= '<div class="row">';

				$shop_isle_title .= '<div class="col-sm-6 col-sm-offset-3">';

	if ( ! empty( $page_title ) ) :

		$shop_isle_title .= '<h1 class="module-title font-alt">' . $page_title . '</h1>';

					endif;

					$shop_isle_shop_id = get_option( 'woocommerce_shop_page_id' );

	if ( ! empty( $shop_isle_shop_id ) ) :

		$shop_isle_page_description = get_post_meta( $shop_isle_shop_id, 'shop_isle_page_description' );

		if ( ! empty( $shop_isle_page_description[0] ) ) :
			$shop_isle_title .= '<div class="module-subtitle font-serif mb-0">' . $shop_isle_page_description[0] . '</div>';
		endif;

					endif;

				$shop_isle_title .= '</div>';

			$shop_isle_title .= '</div><!-- .row -->';

		$shop_isle_title .= '</div>';
	$shop_isle_title .= '</section>';

	return $shop_isle_title;
}

/**
 * New thumbnail size for cart page
 *
 * @since  1.0.0
 */
function shop_isle_cart_item_thumbnail( $thumb, $cart_item, $cart_item_key ) {

	if ( function_exists( 'wc_get_product' ) ) {
		$product = wc_get_product( $cart_item['product_id'] );
	} elseif ( function_exists( 'get_product' ) ) {
		$product = get_product( $cart_item['product_id'] );
	}

	if ( ! empty( $cart_item['variation'] ) ) {
		if ( ! empty( $cart_item['variation_id'] ) ) {

			if ( function_exists( 'wc_get_product' ) ) {
				$product = wc_get_product( $cart_item['variation_id'] );
			} elseif ( function_exists( 'get_product' ) ) {
				$product = get_product( $cart_item['variation_id'] );
			}
		}
	}

	return $product->get_image( 'shop_isle_cart_item_image_size' );

}

/**
 * Products slider on single page product
 *
 * @since  1.0.0
 */
function shop_isle_products_slider_on_single_page() {

	$shop_isle_products_slider_single_hide = get_theme_mod( 'shop_isle_products_slider_single_hide' );

	if ( isset( $shop_isle_products_slider_single_hide ) && $shop_isle_products_slider_single_hide != 1 ) :
		echo '<hr class="divider-w">';
		echo '<section class="module module-small-bottom aya">';
	elseif ( is_customize_preview() ) :
		echo '<hr class="divider-w">';
		echo '<section class="module module-small-bottom shop_isle_hidden_if_not_customizer">';
	endif;

	if ( ( isset( $shop_isle_products_slider_single_hide ) && $shop_isle_products_slider_single_hide != 1 ) || is_customize_preview() ) :

			echo '<div class="container">';

				$shop_isle_products_slider_title = get_theme_mod( 'shop_isle_products_slider_title',__( 'Exclusive products', 'shop-isle' ) );
				$shop_isle_products_slider_subtitle = get_theme_mod( 'shop_isle_products_slider_subtitle',__( 'Special category of products', 'shop-isle' ) );

		if ( ! empty( $shop_isle_products_slider_title ) || ! empty( $shop_isle_products_slider_subtitle ) ) :
			echo '<div class="row">';
			echo '<div class="col-sm-6 col-sm-offset-3">';
			if ( ! empty( $shop_isle_products_slider_title ) ) :
				echo '<h2 class="module-title font-alt">' . $shop_isle_products_slider_title . '</h2>';
			endif;
			if ( ! empty( $shop_isle_products_slider_subtitle ) ) :
				echo '<div class="module-subtitle font-serif">' . $shop_isle_products_slider_subtitle . '</div>';
			endif;
			echo '</div>';
			echo '</div><!-- .row -->';
				endif;

				$shop_isle_products_slider_category = get_theme_mod( 'shop_isle_products_slider_category' );

		$tax_query_item = array();
		$meta_query_item = array();
		if ( taxonomy_exists( 'product_visibility' ) ) {
			$tax_query_item = array(
				array(
					'taxonomy' => 'product_visibility',
					'field'    => 'term_id',
					'terms'    => 'exclude-from-catalog',
					'operator' => 'NOT IN',
				),
			);
		} else {
			$meta_query_item = array(
				'key'     => '_visibility',
				'value'   => 'hidden',
				'compare' => '!=',
			);
		}

		$shop_isle_products_slider_args = array(
			'post_type' => 'product',
			'posts_per_page' => 10,
		);

		if ( ! empty( $shop_isle_products_slider_category ) && ($shop_isle_products_slider_category != '-') ) {
			$shop_isle_products_slider_args['tax_query'] = array(
				array(
					'taxonomy'   => 'product_cat',
					'field'      => 'term_id',
					'terms'      => $shop_isle_products_slider_category,
				),
			);
		}

		if ( ! empty( $tax_query_item ) ) {
			$shop_isle_products_slider_args['tax_query']['relation'] = 'AND';
			$shop_isle_products_slider_args['tax_query'] = array_merge( $shop_isle_products_slider_args['tax_query'],$tax_query_item );
		}

		if ( ! empty( $meta_query_item ) ) {
			$shop_isle_products_slider_args['meta_query'] = $meta_query_item;
		}

		if ( ! empty( $shop_isle_products_slider_category ) && ($shop_isle_products_slider_category != '-') ) :

			$shop_isle_products_slider_loop = new WP_Query( $shop_isle_products_slider_args );

			if ( $shop_isle_products_slider_loop->have_posts() ) :

				$rtl_slider = apply_filters( 'shop_isle_products_slider_single_rtl', 'false' );
				$number_of_items = apply_filters( 'shop_isle_products_slider_single_items', 5 );
				$pagination = apply_filters( 'shop_isle_products_slider_single_pagination', 'false' );
				$navigation = apply_filters( 'shop_isle_products_slider_single_navigation', 'false' );

				echo '<div class="row">';

				echo '<div class="owl-carousel text-center" data-items="' . esc_attr( $number_of_items ) . '" data-pagination="' . esc_attr( $pagination ) . '" data-navigation="' . esc_attr( $navigation ) . '" data-rtl="' . esc_attr( $rtl_slider ) . '" >';

				while ( $shop_isle_products_slider_loop->have_posts() ) :

					$shop_isle_products_slider_loop->the_post();

					echo '<div class="owl-item">';
					echo '<div class="col-sm-12">';
					echo '<div class="ex-product">';
					echo '<a href="' . esc_url( get_permalink() ) . '">' . woocommerce_get_product_thumbnail() . '</a>';
					echo '<h4 class="shop-item-title font-alt"><a href="' . esc_url( get_permalink() ) . '">' . get_the_title() . '</a></h4>';
					$product = new WC_Product( get_the_ID() );

					$rating_html = '';
					if ( function_exists( 'method_exists' ) && function_exists( 'wc_get_rating_html' ) && method_exists( $product, 'get_average_rating' ) ) {
						$shop_isle_avg = $product->get_average_rating();
						if ( ! empty( $shop_isle_avg ) ) {
							$rating_html = wc_get_rating_html( $shop_isle_avg );
						}
					} elseif ( function_exists( 'method_exists' ) && method_exists( $product, 'get_rating_html' ) && method_exists( $product, 'get_average_rating' ) ) {
						$shop_isle_avg = $product->get_average_rating();
						if ( ! empty( $shop_isle_avg ) ) {
							$rating_html = $product->get_rating_html( $shop_isle_avg );
						}
					}
					if ( ! empty( $rating_html ) && get_option( 'woocommerce_enable_review_rating' ) === 'yes' ) {
						echo '<div class="product-rating-home">' . $rating_html . '</div>';
					}
					$shop_isle_price = '';
					if ( function_exists( 'method_exists' ) && method_exists( $product, 'get_price_html' ) ) {
						$shop_isle_price = $product->get_price_html();
					}
					if ( ! empty( $shop_isle_price ) ) {
						echo wp_kses_post( $shop_isle_price );
					}
					echo '</div>';
					echo '</div>';
					echo '</div>';

					endwhile;

					wp_reset_postdata();
				echo '</div>';

					echo '</div>';

			endif;

				else :

					$shop_isle_products_slider_loop = new WP_Query( $shop_isle_products_slider_args );

					if ( $shop_isle_products_slider_loop->have_posts() ) :

						$rtl_slider = apply_filters( 'shop_isle_products_slider_single_rtl', 'false' );
						$number_of_items = apply_filters( 'shop_isle_products_slider_single_items', 5 );
						$pagination = apply_filters( 'shop_isle_products_slider_single_pagination', 'false' );
						$navigation = apply_filters( 'shop_isle_products_slider_single_navigation', 'false' );

						echo '<div class="row">';

						echo '<div class="owl-carousel text-center" data-items="' . esc_attr( $number_of_items ) . '" data-pagination="' . esc_attr( $pagination ) . '" data-navigation="' . esc_attr( $navigation ) . '" data-rtl="' . esc_attr( $rtl_slider ) . '" >';

						while ( $shop_isle_products_slider_loop->have_posts() ) :

							$shop_isle_products_slider_loop->the_post();

							echo '<div class="owl-item">';
							echo '<div class="col-sm-12">';
							echo '<div class="ex-product">';
							echo '<a href="' . esc_url( get_permalink() ) . '">' . woocommerce_get_product_thumbnail() . '</a>';
							echo '<h4 class="shop-item-title font-alt"><a href="' . esc_url( get_permalink() ) . '">' . get_the_title() . '</a></h4>';
								$product = new WC_Product( get_the_ID() );
								$rating_html = '';
							if ( function_exists( 'method_exists' ) && function_exists( 'wc_get_rating_html' ) && method_exists( $product, 'get_average_rating' ) ) {
								$shop_isle_avg = $product->get_average_rating();
								if ( ! empty( $shop_isle_avg ) ) {
									$rating_html = wc_get_rating_html( $shop_isle_avg );
								}
							} elseif ( function_exists( 'method_exists' ) && method_exists( $product, 'get_rating_html' ) && method_exists( $product, 'get_average_rating' ) ) {
								$shop_isle_avg = $product->get_average_rating();
								if ( ! empty( $shop_isle_avg ) ) {
									$rating_html = $product->get_rating_html( $shop_isle_avg );
								}
							}
							if ( ! empty( $rating_html ) && get_option( 'woocommerce_enable_review_rating' ) === 'yes' ) {
								echo '<div class="product-rating-home">' . $rating_html . '</div>';
							}
							$shop_isle_price = '';
							if ( function_exists( 'method_exists' ) && method_exists( $product, 'get_price_html' ) ) {
								$shop_isle_price = $product->get_price_html();
							}
							if ( ! empty( $shop_isle_price ) ) {
								echo wp_kses_post( $shop_isle_price );
							}
							echo '</div>';
								echo '</div>';
								echo '</div>';

									endwhile;

									wp_reset_postdata();
								echo '</div>';

							echo '</div>';

					endif;

				endif;

				echo '</div>';

				echo '</section>';

	endif;
}

if ( ! function_exists( 'shop_isle_search_products_no_results_wrapper' ) ) {
	/**
	 * No results on search wrapper start.
	 */
	function shop_isle_search_products_no_results_wrapper() {

		$shop_isle_body_classes = get_body_class();

		if ( is_search() && in_array( 'woocommerce',$shop_isle_body_classes ) && in_array( 'search-no-results',$shop_isle_body_classes ) ) {
			echo '<section class="module-small module-small-shop">';
				echo '<div class="container">';
		}
	}
}

if ( ! function_exists( 'shop_isle_search_products_no_results_wrapper_end' ) ) {
	/**
	 * No results on search wrapper end.
	 */
	function shop_isle_search_products_no_results_wrapper_end() {

		$shop_isle_body_classes = get_body_class();

		if ( is_search() && in_array( 'woocommerce',$shop_isle_body_classes ) && in_array( 'search-no-results',$shop_isle_body_classes ) ) {
				echo '</div><!-- .container -->';
			echo '</section><!-- .module-small -->';
		}
	}
}

if ( ! function_exists( 'shop_isle_always_show_live_cart' ) ) {
	/**
	 *  Force WC_Widget_Cart widget to show on cart and checkout pages
	 *  Used for the live cart in header
	 */
	function shop_isle_always_show_live_cart() {
		return false;
	}
}

if ( ! function_exists( 'shop_isle_loop_product_thumbnail' ) ) {
	/**
	 * Get the product thumbnail, or the placeholder if not set.
	 */
	function shop_isle_loop_product_thumbnail() {
		global $product;
		$image_size = 'shop_catalog';

		if ( function_exists( 'method_exists' ) && method_exists( $product, 'get_gallery_image_ids' ) ) {
			$shop_isle_gallery_attachment_ids = $product->get_gallery_image_ids();
		} elseif ( function_exists( 'method_exists' ) && method_exists( $product, 'get_gallery_attachment_ids' ) ) {
			$shop_isle_gallery_attachment_ids = $product->get_gallery_attachment_ids();
		}

		if ( has_post_thumbnail() ) {
			if ( function_exists( 'wc_get_product_attachment_props' ) ) {

				$props = wc_get_product_attachment_props( get_post_thumbnail_id(), $product );
				$product_id = get_the_ID();
				echo get_the_post_thumbnail(
					$product_id, $image_size, array(
						'title'  => $props['title'],
						'alt'    => $props['alt'],
					)
				);
			}

			if ( ! empty( $shop_isle_gallery_attachment_ids[0] ) ) :
				echo wp_get_attachment_image( $shop_isle_gallery_attachment_ids[0], $image_size );
			endif;

		} elseif ( ! empty( $shop_isle_gallery_attachment_ids ) ) {

			if ( ! empty( $shop_isle_gallery_attachment_ids[0] ) ) :
				echo wp_get_attachment_image( $shop_isle_gallery_attachment_ids[0], $image_size );
			endif;

			if ( ! empty( $shop_isle_gallery_attachment_ids[1] ) ) :
				echo wp_get_attachment_image( $shop_isle_gallery_attachment_ids[1], $image_size );
			endif;

		} elseif ( function_exists( 'wc_placeholder_img_src' ) ) {

			$shop_isle_placeholder_img = wc_placeholder_img( $image_size );

			if ( ! empty( $shop_isle_placeholder_img ) ) {
				echo $shop_isle_placeholder_img;
			}
		}
	}
}// End if().


if ( ! function_exists( 'shop_isle_woocommerce_taxonomy_archive_description' ) ) {
	/**
	 * Display WooCommerce product category description on all category archive pages
	 */
	function shop_isle_woocommerce_taxonomy_archive_description() {
		if ( is_tax( array( 'product_cat', 'product_tag' ) ) && get_query_var( 'paged' ) != 0 ) {
			$description = wc_format_content( term_description() );
			if ( $description ) {
				echo '<div class="term-description">' . $description . '</div>';
			}
		}
	}
}
add_action( 'woocommerce_archive_description', 'shop_isle_woocommerce_taxonomy_archive_description' );


if ( ! function_exists( 'shop_isle_woocommerce_product_archive_description' ) ) {
	/**
	 * Display WooCommerce shop content on all shop pages
	 */
	function shop_isle_woocommerce_product_archive_description() {
		if ( is_post_type_archive( 'product' ) ) {
			$shop_page   = get_post( wc_get_page_id( 'shop' ) );
			if ( $shop_page ) {
				$description = wc_format_content( $shop_page->post_content );
				if ( $description ) {
					echo '<div class="page-description">' . $description . '</div>';
				}
			}
		}
	}
}

/**
 * Fix for Shop page set up as static frontpage problem with the sidebar
 */
function shop_isle_woocommerce_breadcrumb() {
	if ( function_exists( 'woocommerce_breadcrumb' ) ) {
		if ( is_front_page() ) {
			echo '<nav class="woocommerce-breadcrumb"><a href="' . esc_url( home_url( '/' ) ) . '">Home</a></nav>';
		} else {
			woocommerce_breadcrumb();
		}
	}
}

/**
 * Function to add Out of Stock badge on shop pages
 */
function shop_isle_outofstock_notify_on_archives() {
	global $product;
	if ( function_exists( 'method_exists' ) && method_exists( $product, 'managing_stock' ) && method_exists( $product, 'is_in_stock' ) ) {
		if ( ! $product->managing_stock() && ! $product->is_in_stock() ) {
			echo '<span class="onsale out-of-stock">' . esc_html__( 'Out of Stock','shop-isle' ) . '</span>';
		}
	}
}
