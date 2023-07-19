<?php
/**
 * Interact with WooCommerce cart
 *
 * @class   WC_Name_Your_Price_Cart
 * @package WooCommerce Name Your Price/Classes
 * @since   1.0.0
 * @version  3.1.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_Name_Your_Price_Cart class.
 */
class WC_Name_Your_Price_Cart {

	/**
	 * The single instance of the class.
	 *
	 * @var WC_Name_Your_Price_Cart
	 *
	 * @since 3.0.0
	 */
	protected static $instance = null;

	/**
	 * Main class instance. Ensures only one instance of class is loaded or can be loaded.
	 *
	 * @static
	 * @return WC_Name_Your_Price_Cart
	 * @since  3.0.0
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 3.0.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cloning this object is forbidden.', 'wc_name_your_price' ), '3.0.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 3.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Unserializing instances of this class is forbidden.', 'wc_name_your_price' ), '3.0.0' );
	}

	/**
	 * __construct function.
	 *
	 * @return void
	 */
	public function __construct() {

		// Functions for cart actions - ensure they have a priority before addons (10).
		add_filter( 'woocommerce_is_purchasable', array( $this, 'is_purchasable' ), 5, 2 );
		add_filter( 'woocommerce_add_cart_item_data', array( $this, 'add_cart_item_data' ), 5, 3 );
		add_filter( 'woocommerce_get_cart_item_from_session', array( $this, 'get_cart_item_from_session' ), 11, 2 );
		add_filter( 'woocommerce_add_cart_item', array( $this, 'set_cart_item' ), 11, 1 );
		add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'validate_add_cart_item' ), 5, 6 );

		// Re-validate prices in cart.
		add_action( 'woocommerce_check_cart_items', array( $this, 'check_cart_items' ) );

	}

	/**
	 * ---------------------------------------------------------------------------------
	 * Cart Filters
	 * ---------------------------------------------------------------------------------
	 */

	/**
	 * Override woo's is_purchasable in cases of nyp products.
	 *
	 * @since 1.0
	 *
	 * @param   boolean     $is_purchasable
	 * @param   WC_Product  $product
	 * @return  boolean
	 */
	public function is_purchasable( $is_purchasable, $product ) {
		if ( ( $product->is_type( WC_Name_Your_Price_Helpers::get_simple_supported_types() ) && WC_Name_Your_Price_Helpers::is_nyp( $product ) )
			|| ( $product->is_type( WC_Name_Your_Price_Helpers::get_variable_supported_types() ) && WC_Name_Your_Price_Helpers::has_nyp( $product ) )
		) {
			$is_purchasable = true;
		}
		return $is_purchasable;
	}

	/**
	 * Redirect to the cart when editing a price "in-cart".
	 *
	 * @since   3.0.0
	 * @param  string $url
	 * @return string
	 */
	public function edit_in_cart_redirect( $url ) {
		return wc_get_cart_url();
	}


	/**
	 * Filter the displayed notice after redirecting to the cart when editing a price "in-cart".
	 *
	 * @since   3.0.0
	 * @param  string $url
	 * @return string
	 */
	public function edit_in_cart_redirect_message( $message ) {
		return __( 'Cart updated.', 'wc_name_your_price' );
	}

	/**
	 * Add cart session data.
	 *
	 * @param array $cart_item_data extra cart item data we want to pass into the item.
	 * @param int   $product_id contains the id of the product to add to the cart.
	 * @param int   $variation_id ID of the variation being added to the cart.
	 * @since 1.0
	 */
	public function add_cart_item_data( $cart_item_data, $product_id, $variation_id ) {

		// phpcs:disable WordPress.Security.NonceVerification

		// An NYP item can either be a product or variation.
		$nyp_id = $variation_id ? $variation_id : $product_id;

		$suffix  = WC_Name_Your_Price_Helpers::get_suffix( $nyp_id );
		$product = WC_Name_Your_Price_Helpers::maybe_get_product_instance( $nyp_id );

		// get_posted_price() removes the thousands separators.
		$posted_price = WC_Name_Your_Price_Helpers::get_posted_price( $product, $suffix );

		// Is this an NYP item?
		if ( WC_Name_Your_Price_Helpers::is_nyp( $nyp_id ) && $posted_price ) {

			// Updating container in cart?
			if ( isset( $_POST['update-price'] ) && isset( $_POST['_nypnonce'] ) && wp_verify_nonce( sanitize_key( $_POST['_nypnonce'] ), 'nyp-nonce' ) ) {

				$updating_cart_key = wc_clean( wp_unslash( $_POST['update-price'] ) );

				if ( WC()->cart->find_product_in_cart( $updating_cart_key ) ) {

					// Remove.
					WC()->cart->remove_cart_item( $updating_cart_key );

					// Redirect to cart.
					add_filter( 'woocommerce_add_to_cart_redirect', array( $this, 'edit_in_cart_redirect' ) );

					// Edit notice.
					add_filter( 'wc_add_to_cart_message_html', array( $this, 'edit_in_cart_redirect_message' ) );
				}
			}

			// No need to check is_nyp b/c this has already been validated by validate_add_cart_item().
			$cart_item_data['nyp'] = (float) $posted_price;
		}

		// Add the subscription billing period (the input name is nyp-period).
		$posted_period = WC_Name_Your_Price_Helpers::get_posted_period( $product, $suffix );

		if ( WC_Name_Your_Price_Helpers::is_subscription( $nyp_id ) && WC_Name_Your_Price_Helpers::is_billing_period_variable( $nyp_id ) && $posted_period && array_key_exists( $posted_period, WC_Name_Your_Price_Helpers::get_subscription_period_strings() ) ) {
			$cart_item_data['nyp_period'] = $posted_period;
		}

		return $cart_item_data;
	}

	/**
	 * Adjust the product based on cart session data.
	 *
	 * @param  array $cart_item $cart_item['data'] is product object in session
	 * @param  array $values cart item array
	 * @since 1.0
	 */
	public function get_cart_item_from_session( $cart_item, $values ) {

		// No need to check is_nyp b/c this has already been validated by validate_add_cart_item().
		if ( isset( $values['nyp'] ) ) {
			$cart_item['nyp'] = $values['nyp'];

			// Add the subscription billing period.
			if ( WC_Name_Your_Price_Helpers::is_subscription( $cart_item['data'] ) && isset( $values['nyp_period'] ) && array_key_exists( $values['nyp_period'], WC_Name_Your_Price_Helpers::get_subscription_period_strings() ) ) {
				$cart_item['nyp_period'] = $values['nyp_period'];
			}

			$cart_item = $this->set_cart_item( $cart_item );
		}

		return $cart_item;
	}

	/**
	 * Change the price of the item in the cart.
	 *
	 * @since 3.0
	 *
	 * @param  array $cart_item
	 * @return  array
	 */
	public function set_cart_item( $cart_item ) {

		// Adjust price in cart if nyp is set.
		if ( isset( $cart_item['nyp'] ) && isset( $cart_item['data'] ) ) {

			$product = $cart_item['data'];

			$product->set_price( $cart_item['nyp'] );
			$product->set_sale_price( $cart_item['nyp'] );
			$product->set_regular_price( $cart_item['nyp'] );

			// Subscription-specific price and variable billing period.
			if ( $product->is_type( array( 'subscription', 'subscription_variation' ) ) ) {

				$product->update_meta_data( '_subscription_price', $cart_item['nyp'] );

				if ( WC_Name_Your_Price_Helpers::is_billing_period_variable( $product ) && isset( $cart_item['nyp_period'] ) ) {

					// Length may need to be re-calculated. Hopefully no one is using the length but who knows.
					// v3.1.3 disables the length selector when in variable billing mode.
					$original_period = WC_Subscriptions_Product::get_period( $product );
					$original_length = WC_Subscriptions_Product::get_length( $product );

					if ( $original_length > 0 && $original_period && $cart_item['nyp_period'] !== $original_period ) {
						$factors    = WC_Name_Your_Price_Helpers::annual_price_factors();
						$new_length = $original_length * $factors[ $cart_item['nyp_period'] ] / $factors[ $original_period ];
						$product->update_meta_data( '_subscription_length', floor($new_length) );
					}

					// Set period to the chosen period.
					$product->update_meta_data( '_subscription_period', $cart_item['nyp_period'] );

					// Variable billing period is always a "per" interval.
					$product->update_meta_data( '_subscription_period_interval', 1 );

				}
			}
		}

		return $cart_item;
	}

	/**
	 * Validate an NYP product before adding to cart.
	 *
	 * @since 1.0
	 *
	 * @param  int    $product_id     - Contains the ID of the product.
	 * @param  int    $quantity       - Contains the quantity of the item.
	 * @param  int    $variation_id   - Contains the ID of the variation.
	 * @param  array  $variation      - Attribute values.
	 * @param  array  $cart_item_data - Extra cart item data we want to pass into the item.
	 * @return bool
	 */
	public function validate_add_cart_item( $passed, $product_id, $quantity, $variation_id = '', $variations = '', $cart_item_data = array() ) {

		$nyp_id  = $variation_id ? $variation_id : $product_id;
		$product = wc_get_product( $nyp_id );

		// Skip if not a NYP product - send original status back.
		if ( ! WC_Name_Your_Price_Helpers::is_nyp( $product ) ) {
			return $passed;
		}

		$suffix = WC_Name_Your_Price_Helpers::get_suffix( $nyp_id );

		// Get_posted_price() runs the price through the standardize_number() helper.
		$price = isset( $cart_item_data['nyp'] ) ? $cart_item_data['nyp'] : WC_Name_Your_Price_Helpers::get_posted_price( $product, $suffix );

		// Get the posted billing period.
		$period = isset( $cart_item_data['nyp_period'] ) ? $cart_item_data['nyp_period'] : WC_Name_Your_Price_Helpers::get_posted_period( $product, $suffix );

		return $this->validate_price( $product, $quantity, $price, $period );

	}

	/**
	 * Re-validate prices on cart load.
	 * Specifically we are looking to prevent smart/quick pay gateway buttons completing an order that is invalid.
	 */
	public function check_cart_items() {

		foreach ( WC()->cart->cart_contents as $cart_item_key => $cart_item ) {

			if ( isset( $cart_item['nyp'] ) ) {
				$period = isset( $cart_item['nyp_period'] ) ? $cart_item['nyp_period'] : '';
				$this->validate_price( $cart_item['data'], $cart_item['quantity'], $cart_item['nyp'], $period, 'cart' );
			}
		}
	}

	/**
	 * Validate an NYP product's price is valid.
	 *
	 * @since 3.0
	 *
	 * @param  mixed   $product
	 * @param  int     $quantity
	 * @param  string  $price
	 * @param  string  $period
	 * @param  string  $context
	 * @param  bool    $return_error - When true returns the string error message.
	 * @throws Exception When the entered price is not valid
	 * @return boolean|string
	 */
	public function validate_price( $product, $quantity, $price, $period = '', $context = 'add-to-cart', $return_error = false ) {
		$is_configuration_valid = true;

		try {

			// Sanity check.
			$product = WC_Name_Your_Price_Helpers::maybe_get_product_instance( $product );

			if ( ! ( $product instanceof WC_Product ) ) {
				$notice = WC_Name_Your_Price_Helpers::error_message( 'invalid-product' );
				throw new Exception( $notice );
			}

			$product_id    = $product->get_id();
			$product_title = $product->get_title();

			// Get minimum price.
			$minimum = WC_Name_Your_Price_Helpers::get_minimum_price( $product );

			// Get maximum price.
			$maximum = WC_Name_Your_Price_Helpers::get_maximum_price( $product );

			// Minimum error template.
			$error_template = WC_Name_Your_Price_Helpers::is_minimum_hidden( $product ) ? 'hide_minimum' : 'minimum';

			// Check that it is a positive numeric value.
			if ( ! is_numeric( $price ) || is_infinite( $price ) || floatval( $price ) < 0 ) {

				$notice = WC_Name_Your_Price_Helpers::error_message(
					'invalid',
					array( '%%TITLE%%' => $product_title ),
					$product,
					$context
				);

				throw new Exception( $notice );

				// Check that it is greater than minimum price for variable billing subscriptions.
			} elseif ( $minimum && $period && WC_Name_Your_Price_Helpers::is_subscription( $product ) && WC_Name_Your_Price_Helpers::is_billing_period_variable( $product ) ) {

				// Minimum billing period.
				$minimum_period = WC_Name_Your_Price_Helpers::get_minimum_billing_period( $product );

				// Annual minimum.
				$minimum_annual = WC_Name_Your_Price_Helpers::annualize_price( $minimum, $minimum_period );

				// Annual price.
				$annual_price = WC_Name_Your_Price_Helpers::annualize_price( $price, $period );

				// By standardizing the prices over the course of a year we can safely compare them.
				if ( $annual_price < $minimum_annual ) {

					$factors = WC_Name_Your_Price_Helpers::annual_price_factors();

					// If set period is in the $factors array we can calc the min price shown in the error according to entered period.
					if ( isset( $factors[ $period ] ) ) {
						$error_price  = $minimum_annual / $factors[ $period ];
						$error_period = $period;
						// Otherwise, just show the saved minimum price and period.
					} else {
						$error_price  = $minimum;
						$error_period = $minimum_period;
					}

					// The minimum is a combo of price and period.
					$minimum_error = wc_price( $error_price ) . ' / ' . $error_period;

					$notice = WC_Name_Your_Price_Helpers::error_message(
						$error_template,
						array(
							'%%TITLE%%'   => $product_title,
							'%%MINIMUM%%' => $minimum_error,
						),
						$product,
						$context
					);

					throw new Exception( $notice );

				}
				// Check that it is greater than minimum price.
			} elseif ( $minimum && floatval( $price ) < floatval( $minimum ) ) {

				$notice = WC_Name_Your_Price_Helpers::error_message(
					$error_template,
					array(
						'%%TITLE%%'   => $product_title,
						'%%MINIMUM%%' => wc_price( $minimum ),
					),
					$product,
					$context
				);

				throw new Exception( $notice );

				// Check that it is less than maximum price.
			} elseif ( $maximum && floatval( $price ) > floatval( $maximum ) ) {

				$error_template = '' !== $context ? 'maximum-' . $context : 'maximum';
				$notice         = WC_Name_Your_Price_Helpers::error_message(
					'error_template',
					array(
						'%%TITLE%%'   => $product_title,
						'%%MAXIMUM%%' => wc_price( $maximum ),
					),
					$product,
					$context
				);

				throw new Exception( $notice );

			}
		} catch ( Exception $e ) {

			$notice = $e->getMessage();

			if ( $notice ) {

				// Return the error message. Typically for use by headless applications.
				if ( $return_error ) {
					return $notice;
				}

				wc_add_notice( $notice, 'error' );
			}

			$is_configuration_valid = false;

		} finally {
			return $is_configuration_valid;
		}

	}

	/**
	 * ---------------------------------------------------------------------------------
	 * Deprecated Functions
	 * ---------------------------------------------------------------------------------
	 */

	/**
	 * Change the price of the item in the cart.
	 *
	 * @since 1.0
	 * @deprecated 3.0
	 */
	public function add_cart_item( $cart_item ) {
		wc_deprecated_function( 'add_cart_item', '3.0', 'Renamed to set_cart_item()' );
		return $this->set_cart_item( $cart_item );
	}

} // End class.
