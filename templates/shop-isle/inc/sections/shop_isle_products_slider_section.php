<?php
/**
 * Front page Product Slider Section
 *
 * @package WordPress
 * @subpackage Shop Isle
 */

$shop_isle_products_slider_hide = get_theme_mod( 'shop_isle_products_slider_hide', false );
if ( ! empty( $shop_isle_products_slider_hide ) && (bool) $shop_isle_products_slider_hide === true ) {
	return;
}

echo '<section class="home-product-slider">';


echo '<div class="container">';


if ( current_user_can( 'edit_theme_options' ) ) {
	$shop_isle_products_slider_title = get_theme_mod( 'shop_isle_products_slider_title', __( 'Exclusive products', 'shop-isle' ) );
	if ( ! class_exists( 'WC_Product' ) ) {
		$shop_isle_products_slider_subtitle = get_theme_mod( 'shop_isle_products_slider_subtitle', __( 'For this section to work, you first need to install the WooCommerce plugin , create some products, and select a product category in Customize -> Frontpage sections -> Products slider section', 'shop-isle' ) );
	} else {
		$shop_isle_products_slider_subtitle = get_theme_mod( 'shop_isle_products_slider_subtitle' );
	}
} else {
	$shop_isle_products_slider_title    = get_theme_mod( 'shop_isle_products_slider_title' );
	$shop_isle_products_slider_subtitle = get_theme_mod( 'shop_isle_products_slider_subtitle' );
}


if ( ! empty( $shop_isle_products_slider_title ) || ! empty( $shop_isle_products_slider_subtitle ) ) :
	echo '<div class="row">';
	echo '<div class="col-sm-6 col-sm-offset-3">';
	if ( ! empty( $shop_isle_products_slider_title ) ) :
		echo '<h2 class="module-title font-alt home-prod-title">' . $shop_isle_products_slider_title . '</h2>';
	endif;
	if ( ! empty( $shop_isle_products_slider_subtitle ) ) :
		echo '<div class="module-subtitle font-serif home-prod-subtitle">' . $shop_isle_products_slider_subtitle . '</div>';
	endif;
	echo '</div>';
	echo '</div><!-- .row -->';
endif;

$shop_isle_products_slider_category = get_theme_mod( 'shop_isle_products_slider_category' );


$tax_query_item  = array();
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
	'post_type'      => 'product',
	'posts_per_page' => 10,
);

if ( ! empty( $shop_isle_products_slider_category ) && ( $shop_isle_products_slider_category != '-' ) ) {
	$shop_isle_products_slider_args['tax_query'] = array(
		array(
			'taxonomy' => 'product_cat',
			'field'    => 'term_id',
			'terms'    => $shop_isle_products_slider_category,
		),
	);
}

if ( ! empty( $tax_query_item ) ) {
	$shop_isle_products_slider_args['tax_query']['relation'] = 'AND';
	$shop_isle_products_slider_args['tax_query']             = array_merge( $shop_isle_products_slider_args['tax_query'], $tax_query_item );
}

if ( ! empty( $meta_query_item ) ) {
	$shop_isle_products_slider_args['meta_query'] = $meta_query_item;
}



if ( ! empty( $shop_isle_products_slider_category ) && ( $shop_isle_products_slider_category != '-' ) ) :

	$shop_isle_products_slider_loop = new WP_Query( $shop_isle_products_slider_args );

	if ( $shop_isle_products_slider_loop->have_posts() ) :

		$rtl_slider = apply_filters( 'shop_isle_products_slider_section_rtl', 'false' );
		$number_of_items = apply_filters( 'shop_isle_products_slider_section_items', 5 );
		$pagination = apply_filters( 'shop_isle_products_slider_section_pagination', 'false' );
		$navigation = apply_filters( 'shop_isle_products_slider_section_navigation', 'false' );

		echo '<div class="row">';

		echo '<div class="owl-carousel text-center" data-items="' . esc_attr( $number_of_items ) . '" data-pagination="' . esc_attr( $pagination ) . '" data-navigation="' . esc_attr( $navigation ) . '" data-rtl="' . esc_attr( $rtl_slider ) . '" >';

		while ( $shop_isle_products_slider_loop->have_posts() ) :

			$shop_isle_products_slider_loop->the_post();
			global $product;
			echo '<div class="owl-item">';
			echo '<div class="col-sm-12">';
			echo '<div class="ex-product">';
			if ( function_exists( 'woocommerce_get_product_thumbnail' ) ) :
				echo '<a href="' . esc_url( get_permalink() ) . '">' . woocommerce_get_product_thumbnail() . '</a>';
			endif;
			echo '<h4 class="shop-item-title font-alt"><a href="' . esc_url( get_permalink() ) . '">' . get_the_title() . '</a></h4>';
			$rating_html = '';
			if ( function_exists( 'method_exists' ) && ( function_exists( 'wc_get_rating_html' ) ) && method_exists( $product, 'get_average_rating' ) ) {
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
			if ( function_exists( 'method_exists' ) && method_exists( $product, 'is_on_sale' ) ) {
				if ( $product->is_on_sale() ) {
					if ( function_exists( 'woocommerce_show_product_sale_flash' ) ) {
						woocommerce_show_product_sale_flash();
					}
				}
			}
			if ( function_exists( 'method_exists' ) && method_exists( $product, 'managing_stock' ) && method_exists( $product, 'is_in_stock' ) ) {
				if ( ! $product->managing_stock() && ! $product->is_in_stock() ) {
					echo '<span class="onsale stock out-of-stock">' . esc_html__( 'Out of Stock', 'shop-isle' ) . '</span>';
				}
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

		$rtl_slider = apply_filters( 'shop_isle_products_slider_section_rtl', 'false' );
		$number_of_items = apply_filters( 'shop_isle_products_slider_section_items', 5 );
		$pagination = apply_filters( 'shop_isle_products_slider_section_pagination', 'false' );
		$navigation = apply_filters( 'shop_isle_products_slider_section_navigation', 'false' );
		echo '<div class="row">';

		echo '<div class="owl-carousel text-center" data-items="' . esc_attr( $number_of_items ) . '" data-pagination="' . esc_attr( $pagination ) . '" data-navigation="' . esc_attr( $navigation ) . '" data-rtl="' . esc_attr( $rtl_slider ) . '">';

		while ( $shop_isle_products_slider_loop->have_posts() ) :

			$shop_isle_products_slider_loop->the_post();
			global $product;
			echo '<div class="owl-item">';
			echo '<div class="col-sm-12">';
			echo '<div class="ex-product">';
			if ( function_exists( 'woocommerce_get_product_thumbnail' ) ) :
				echo '<a href="' . esc_url( get_permalink() ) . '">' . woocommerce_get_product_thumbnail() . '</a>';
			endif;
			echo '<h4 class="shop-item-title font-alt"><a href="' . esc_url( get_permalink() ) . '">' . get_the_title() . '</a></h4>';

			$rating_html = '';
			if ( function_exists( 'method_exists' ) && ( function_exists( 'wc_get_rating_html' ) ) && method_exists( $product, 'get_average_rating' ) ) {
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
			if ( function_exists( 'method_exists' ) && method_exists( $product, 'is_on_sale' ) ) {
				if ( $product->is_on_sale() ) {
					if ( function_exists( 'woocommerce_show_product_sale_flash' ) ) {
						woocommerce_show_product_sale_flash();
					}
				}
			}
			if ( function_exists( 'method_exists' ) && method_exists( $product, 'managing_stock' ) && method_exists( $product, 'is_in_stock' ) ) {
				if ( ! $product->managing_stock() && ! $product->is_in_stock() ) {
					echo '<span class="onsale stock out-of-stock">' . esc_html__( 'Out of Stock', 'shop-isle' ) . '</span>';
				}
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




