<?php
/**
 * Buy One Get One Free - All Products For Subscriptions by SomewhereWarm
 *
 * @see https://woocommerce.com/es-es/products/all-products-for-woocommerce-subscriptions/
 * @since 1.3.7
 * @package WC_BOGOF
 */

defined( 'ABSPATH' ) || exit;

/**
 * WC_BOGOF_WCS_ATT Class
 */
class WC_BOGOF_WCS_ATT {

	/**
	 * Retrun the minimun version required.
	 */
	public static function min_version_required() {
		return '2.1.5';
	}

	/**
	 * Returns the extension name.
	 */
	public static function extension_name() {
		return 'All Products for Subscriptions';
	}

	/**
	 * Checks the minimum version required.
	 */
	public static function check_min_version() {
		return version_compare( WCS_ATT::VERSION, static::min_version_required(), '>=' );
	}

	/**
	 * Init hooks
	 */
	public static function init() {
		add_filter( 'wcsatt_cart_item_options', array( __CLASS__, 'cart_item_options' ), 100, 3 );
		add_filter( 'woocommerce_get_cart_item_from_session', array( __CLASS__, 'get_cart_item_from_session' ), 110 );
	}

	/**
	 * Disable the subscriptions options for the free product.
	 *
	 * @param array $options Subscriptions options.
	 * @param array $subscription_schemes Subscription schemes.
	 * @param array $cart_item Cart item data.
	 * @return array
	 */
	public static function cart_item_options( $options, $subscription_schemes, $cart_item ) {
		if ( WC_BOGOF_Cart::is_free_item( $cart_item ) ) {
			$options = array( $options[0] );
		}
		return $options;
	}

	/**
	 * Set free product
	 *
	 * @param array $session_data Session data.
	 * @return array
	 */
	public static function get_cart_item_from_session( $session_data ) {
		if ( WC_BOGOF_Cart::is_free_item( $session_data ) ) {
			unset( $session_data['wcsatt_data'] );
		}
		return $session_data;
	}
}
