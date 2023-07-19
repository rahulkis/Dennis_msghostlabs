<?php
/**
 * CoCart Compatibility
 *
 * @author   Kathy Darling
 * @package  WooCommerce Name Your Price/Compatibility
 * @since    3.1.0
 * @version  3.1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The Main WC_NYP_CoCart_Compatibility class
 **/
class WC_NYP_CoCart_Compatibility {

	/**
	 * WC_NYP_CoCart_Compatibility Constructor
	 */
	public static function init() {
		add_filter( 'cocart_add_to_cart_validation', array( __CLASS__, 'add_to_cart_validation' ), 10, 6 );
	}

	/**
	 * Validate an NYP product before adding to cart.
	 *
	 * @param  int    $product_id     - Contains the ID of the product.
	 * @param  int    $quantity       - Contains the quantity of the item.
	 * @param  int    $variation_id   - Contains the ID of the variation.
	 * @param  array  $variation      - Attribute values.
	 * @param  array  $cart_item_data - Extra cart item data we want to pass into the item.
	 * @return bool|WP_Error
	 */
	public static function add_to_cart_validation( $passed, $product_id, $quantity, $variation_id = '', $variations = '', $cart_item_data = array() ) {

		$nyp_id  = $variation_id ? $variation_id : $product_id;
		$product = wc_get_product( $nyp_id );

		// Skip if not a NYP product - send original status back.
		if ( ! WC_Name_Your_Price_Helpers::is_nyp( $product ) ) {
			return $passed;
		}

		$suffix = WC_Name_Your_Price_Helpers::get_suffix( $nyp_id );

		// Get_posted_price() runs the price through the standardize_number() helper.
		$price = WC_Name_Your_Price_Helpers::get_posted_price( $product, $suffix );

		// Get the posted billing period.
		$period = WC_Name_Your_Price_Helpers::get_posted_period( $product, $suffix );

		// Validate.
		$is_valid = WC_Name_Your_Price()->cart->validate_price( $product, $quantity, $price, $period, 'cocart', true );

		// Return error response.
		if ( is_string( $is_valid ) ) {
			return new WP_Error( 'cocart_cannot_add_product_to_cart', $is_valid, array( 'status' => 500 ) );
		} else {
			return boolval( $is_valid );
		}

	}

} // End class: do not remove or there will be no more guacamole for you.

WC_NYP_CoCart_Compatibility::init();
