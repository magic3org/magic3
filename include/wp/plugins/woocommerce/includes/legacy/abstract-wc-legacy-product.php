<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Legacy Abstract Product
 *
 * Legacy and deprecated functions are here to keep the WC_Abstract_Product
 * clean.
 * This class will be removed in future versions.
 *
 * @version  3.0.0
 * @package  WooCommerce/Abstracts
 * @category Abstract Class
 * @author   WooThemes
 */
abstract class WC_Abstract_Legacy_Product extends WC_Data {

	/**
	 * Magic __isset method for backwards compatibility. Legacy properties which could be accessed directly in the past.
	 *
	 * @param  string $key Key name.
	 * @return bool
	 */
	public function __isset( $key ) {
		$valid = array(
			'id',
			'product_attributes',
			'visibility',
			'sale_price_dates_from',
			'sale_price_dates_to',
			'post',
			'download_type',
			'product_image_gallery',
			'variation_shipping_class',
			'shipping_class',
			'total_stock',
			'crosssell_ids',
			'parent',
		);
		if ( $this->is_type( 'variation' ) ) {
			$valid = array_merge( $valid, array(
				'variation_id',
				'variation_data',
				'variation_has_stock',
				'variation_shipping_class_id',
				'variation_has_sku',
				'variation_has_length',
				'variation_has_width',
				'variation_has_height',
				'variation_has_weight',
				'variation_has_tax_class',
				'variation_has_downloadable_files',
			) );
		}
		return in_array( $key, array_merge( $valid, array_keys( $this->data ) ) ) || metadata_exists( 'post', $this->get_id(), '_' . $key ) || metadata_exists( 'post', $this->get_parent_id(), '_' . $key );
	}

	/**
	 * Magic __get method for backwards compatibility. Maps legacy vars to new getters.
	 *
	 * @param  string $key Key name.
	 * @return mixed
	 */
	public function __get( $key ) {

		if ( 'post_type' === $key ) {
			return $this->post_type;
		}

		wc_doing_it_wrong( $key, __( 'Product properties should not be accessed directly.', 'woocommerce' ), '3.0' );

		switch ( $key ) {
			case 'id' :
				$value = $this->is_type( 'variation' ) ? $this->get_parent_id() : $this->get_id();
				break;
			case 'product_type' :
				$value = $this->get_type();
				break;
			case 'product_attributes' :
				$value = isset( $this->data['attributes'] ) ? $this->data['attributes'] : '';
				break;
			case 'visibility' :
				$value = $this->get_catalog_visibility();
				break;
			case 'sale_price_dates_from' :
				return $this->get_date_on_sale_from() ? $this->get_date_on_sale_from()->getTimestamp() : '';
				break;
			case 'sale_price_dates_to' :
				return $this->get_date_on_sale_to() ? $this->get_date_on_sale_to()->getTimestamp() : '';
				break;
			case 'post' :
				$value = get_post( $this->get_id() );
				break;
			case 'download_type' :
				return 'standard';
				break;
			case 'product_image_gallery' :
				$value = $this->get_gallery_image_ids();
				break;
			case 'variation_shipping_class' :
			case 'shipping_class' :
				$value = $this->get_shipping_class();
				break;
			case 'total_stock' :
				$value = $this->get_total_stock();
				break;
			case 'downloadable' :
			case 'virtual' :
			case 'manage_stock' :
			case 'featured' :
			case 'sold_individually' :
				$value = $this->{"get_$key"}() ? 'yes' : 'no';
				break;
			case 'crosssell_ids' :
				$value = $this->get_cross_sell_ids();
				break;
			case 'upsell_ids' :
				$value = $this->get_upsell_ids();
				break;
			case 'parent' :
				$value = wc_get_product( $this->get_parent_id() );
				break;
			case 'variation_id' :
				$value = $this->is_type( 'variation' ) ? $this->get_id() : '';
				break;
			case 'variation_data' :
				$value = $this->is_type( 'variation' ) ? wc_get_product_variation_attributes( $this->get_id() ) : '';
				break;
			case 'variation_has_stock' :
				$value = $this->is_type( 'variation' ) ? $this->managing_stock() : '';
				break;
			case 'variation_shipping_class_id' :
				$value = $this->is_type( 'variation' ) ? $this->get_shipping_class_id() : '';
				break;
			case 'variation_has_sku' :
			case 'variation_has_length' :
			case 'variation_has_width' :
			case 'variation_has_height' :
			case 'variation_has_weight' :
			case 'variation_has_tax_class' :
			case 'variation_has_downloadable_files' :
				$value = true; // These were deprecated in 2.2 and simply returned true in 2.6.x.
				break;
			default :
				if ( in_array( $key, array_keys( $this->data ) ) ) {
					$value = $this->{"get_$key"}();
				} else {
					$value = get_post_meta( $this->id, '_' . $key, true );
				}
				break;
		}
		return $value;
	}
}
