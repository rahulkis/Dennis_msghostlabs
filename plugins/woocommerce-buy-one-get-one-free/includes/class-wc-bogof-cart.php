<?php
/**
 * WooCommerce Buy One Get One Free cart actions.
 *
 * @package WC_BOGOF
 */

defined( 'ABSPATH' ) || exit;

/**
 * WC_BOGOF_Cart Class
 */
class WC_BOGOF_Cart {

	/**
	 * Cart rules
	 *
	 * @var array
	 */
	private static $cart_rules = array();

	/**
	 * Cart item cart rules references.
	 *
	 * @var array
	 */
	private static $cart_rules_ref = array();

	/**
	 * Calculate cart subtotal.
	 *
	 * @var WC_BOGOF_Cart_Totals
	 */
	private static $cart_totals = null;

	/**
	 * Init hooks
	 */
	public static function init() {
		add_action( 'woocommerce_cart_loaded_from_session', array( __CLASS__, 'cart_loaded_from_session' ), 20 );
		add_action( 'woocommerce_cart_loaded_from_session', array( __CLASS__, 'validate_free_items' ), 30 );
		add_action( 'template_redirect', array( __CLASS__, 'refresh_cart_rules_items' ), 30 );
		add_action( 'woocommerce_add_to_cart', array( __CLASS__, 'add_to_cart' ), 5 );
		add_action( 'woocommerce_cart_item_removed', array( __CLASS__, 'cart_item_removed' ), 5 );
		add_action( 'woocommerce_after_cart_item_quantity_update', array( __CLASS__, 'cart_item_set_quantity' ), 5 );
		add_action( 'woocommerce_cart_item_restored', array( __CLASS__, 'cart_item_restored' ) );
		add_action( 'woocommerce_after_checkout_validation', array( __CLASS__, 'checkout_validation' ) );
		add_action( 'woocommerce_checkout_create_order', array( __CLASS__, 'checkout_create_order' ) );
		add_action( 'woocommerce_checkout_create_order_line_item', array( __CLASS__, 'checkout_create_order_line_item' ), 10, 3 );
		add_filter( 'woocommerce_add_cart_item', array( __CLASS__, 'add_cart_item' ), 100, 3 );
		add_filter( 'woocommerce_get_cart_item_from_session', array( __CLASS__, 'get_cart_item_from_session' ), 100, 3 );

		// Handle product price.
		add_filter( 'woocommerce_product_get_price', array( __CLASS__, 'get_free_product_price' ), 9999, 2 );
		add_filter( 'woocommerce_product_variation_get_price', array( __CLASS__, 'get_free_product_price' ), 9999, 2 );
		add_filter( 'woocommerce_product_get_sale_price', array( __CLASS__, 'get_free_product_price' ), 9999, 2 );
		add_filter( 'woocommerce_product_variation_get_sale_price', array( __CLASS__, 'get_free_product_price' ), 9999, 2 );

		// Display add to cart messages.
		add_filter( 'woocommerce_add_to_cart_redirect', array( __CLASS__, 'add_to_cart_messages' ) );
		add_filter( 'woocommerce_update_cart_action_cart_updated', array( __CLASS__, 'add_to_cart_messages' ) );

		// Coupons.
		add_action( 'woocommerce_applied_coupon', array( __CLASS__, 'cart_update' ), 5 );
		add_action( 'woocommerce_removed_coupon', array( __CLASS__, 'cart_update' ), 5 );
		add_filter( 'woocommerce_coupon_is_valid', array( __CLASS__, 'coupon_is_valid' ), 100, 2 );

		// Cart totals.
		add_action( 'wc_bogof_after_set_cart_item_discount', array( __CLASS__, 'calculate_items_subtotal' ) );

		// Deprecated.
		if ( version_compare( WC_VERSION, '3.7', '<' ) ) {
			add_action( 'woocommerce_before_cart_item_quantity_zero', array( __CLASS__, 'before_cart_item_quantity_zero' ) );
		}
	}

	/**
	 * Cart loaded from session.
	 */
	public static function cart_loaded_from_session() {
		if ( did_action( 'wc_bogof_cart_rules_loaded' ) ) {
			// Only do it once.
			return;
		}
		self::load_cart_rules();
		self::cart_rules_actions();

		do_action( 'wc_bogof_cart_rules_loaded' );
	}

	/**
	 * Refresh the cart rules items on load.
	 *
	 * @todo Do only when the rules have changed.
	 */
	public static function refresh_cart_rules_items() {
		if ( ! did_action( 'wc_bogof_cart_rules_loaded' ) || did_action( 'wc_bogof_after_cart_rules_update' ) || is_ajax() ) {
			return;
		}
		self::cart_update();
	}

	/**
	 * Add a cart rule to the cart rules array.
	 *
	 * @param WC_BOGOF_Rule $rule BOGOF rule.
	 * @param string        $cart_item_key Cart item key.
	 * @param bool          $init_hooks Init cart rule hooks? default=false.
	 */
	private static function add_cart_rule( $rule, $cart_item_key, $init_hooks = false ) {

		if ( $rule->is_enabled() && $rule->is_available_for_current_user_role() && self::check_usage_limit( $rule ) ) {

			$cart_item = WC()->cart->get_cart_item( $cart_item_key );

			if ( 'buy_a_get_a' === $rule->get_type() ) {
				$cart_rule = new WC_BOGOF_Cart_Rule_Buy_A_Get_A( $rule, $cart_item['data']->get_id() );
			} elseif ( 'cheapest_free' === $rule->get_type() ) {
				$cart_rule = new WC_BOGOF_Cart_Rule_Cheapest_Free( $rule );
			} elseif ( $rule->is_individual() ) {
				$cart_rule = new WC_BOGOF_Cart_Rule_Individual( $rule, $cart_item['data']->get_id() );
			} else {
				$cart_rule = new WC_BOGOF_Cart_Rule( $rule );
			}

			if ( ! isset( self::$cart_rules[ $cart_rule->get_id() ] ) ) {
				self::$cart_rules[ $cart_rule->get_id() ] = $cart_rule;
				if ( $init_hooks ) {
					self::$cart_rules[ $cart_rule->get_id() ]->init_hooks();
				}
			}

			if ( ! isset( self::$cart_rules_ref[ $cart_item_key ] ) ) {
				self::$cart_rules_ref[ $cart_item_key ] = array();
			}
			self::$cart_rules_ref[ $cart_item_key ][] = $cart_rule->get_id();
		}
	}

	/**
	 * Check the usage limit.
	 *
	 * @param int $rule WC_BOGOF_Rule object.
	 * @return bool
	 */
	private static function check_usage_limit( $rule ) {
		$check = true;
		if ( $rule->get_usage_limit_per_user() > 0 ) {
			$total_uses = $rule->get_used_by_count( wc_bogof_user_ids() ) + self::get_rule_count( $rule->get_id() );
			$check      = $total_uses < $rule->get_usage_limit_per_user();
		}
		return $check;
	}

	/**
	 * Load available rules.
	 */
	private static function load_cart_rules() {
		self::$cart_rules = array();

		$cart_contents = WC()->cart->get_cart_contents();
		$data_store    = WC_Data_Store::load( 'bogof-rule' );

		foreach ( $cart_contents as $cart_item_key => $cart_item ) {
			if ( self::is_free_item( $cart_item ) || empty( $cart_item['data'] ) || ! is_callable( array( $cart_item['data'], 'get_id' ) ) ) {
				continue;
			}

			$rules = $data_store->get_rules_by_product( $cart_item['data']->get_id() );
			foreach ( $rules as $rule ) {
				self::add_cart_rule( $rule, $cart_item_key );
			}
		}
	}

	/**
	 * Validate the free items.
	 */
	public static function validate_free_items() {
		$cart_contents = WC()->cart->get_cart_contents();
		foreach ( $cart_contents as $key => $cart_item ) {
			if ( self::is_free_item( $cart_item ) && ! self::is_valid_free_item( $cart_item ) ) {
				unset( WC()->cart->cart_contents[ $key ] );

			} elseif ( self::is_valid_discount( $cart_item ) ) {
				// Check the rules.
				foreach ( $cart_item['data']->_bogof_discount->get_rules() as $cart_rule_id => $quantity ) {
					if ( ! isset( self::$cart_rules[ $cart_rule_id ] ) ) {
						WC()->cart->cart_contents[ $key ]['data']->_bogof_discount->remove_free_quantity( $cart_rule_id );
					}
				}
				if ( ! WC()->cart->cart_contents[ $key ]['data']->_bogof_discount->has_discount() ) {
					unset( WC()->cart->cart_contents[ $key ]['data']->_bogof_discount );
					unset( WC()->cart->cart_contents[ $key ]['_bogof_discount'] );
				}
			}
		}
	}

	/**
	 * Add the hooks of the rules.
	 */
	private static function cart_rules_actions() {
		foreach ( self::$cart_rules as $cart_rule ) {
			$cart_rule->init_hooks();
		}
	}

	/**
	 * Add to cart action.
	 *
	 * @param string $cart_item_key Cart item key.
	 */
	public static function add_to_cart( $cart_item_key ) {
		$cart_item = WC()->cart->get_cart_item( $cart_item_key );

		if ( empty( $cart_item ) ) {
			return;
		}

		if ( self::is_valid_free_item( $cart_item ) ) {

			// Check the qty of the free item.
			self::check_cart_free_item_qty( $cart_item_key, $cart_item );

			// Refresh rule totals.
			$rule = self::get_cart_rule( $cart_item['_bogof_free_item'] );
			$rule->clear_totals();

		} elseif ( ! empty( $cart_item['data'] ) && is_callable( array( $cart_item['data'], 'get_id' ) ) ) {
			// Add new rules.
			$data_store = WC_Data_Store::load( 'bogof-rule' );
			$rules      = $data_store->get_rules_by_product( $cart_item['data']->get_id() );
			foreach ( $rules as $rule ) {
				self::add_cart_rule( $rule, $cart_item_key, true );
			}

			// Refresh cart rules.
			self::cart_update();
		}

	}

	/**
	 * Remove the cart of the item from the removed cart rules array
	 *
	 * @param string $cart_item_key Cart item key.
	 */
	private static function restore_cart_rule( $cart_item_key ) {
		$removed_rules = WC()->session->get( 'wc_bogof_removed_rules', array() );
		if ( ! empty( $removed_rules ) ) {
			$cart_rules = self::get_cart_rules( $cart_item_key );
			foreach ( $cart_rules as $cart_rule ) {
				if ( isset( $removed_rules[ $cart_rule->get_id() ] ) ) {
					unset( $removed_rules[ $cart_rule->get_id() ] );
				}
			}
			if ( 0 < count( $removed_rules ) ) {
				WC()->session->set( 'wc_bogof_removed_rules', $removed_rules );
			} else {
				unset( WC()->session->wc_bogof_removed_rules );
			}
		}
	}

	/**
	 * Update free items qty on item removed.
	 *
	 * @param string $cart_item_key Cart item key.
	 */
	public static function cart_item_removed( $cart_item_key ) {
		$cart_contents = WC()->cart->get_removed_cart_contents();
		$cart_item     = $cart_contents[ $cart_item_key ];

		if ( self::is_free_item( $cart_item ) ) {
			// Add the rule to the removed rules array.
			$cart_rule_id                   = $cart_item['_bogof_free_item'];
			$removed_rules                  = WC()->session->get( 'wc_bogof_removed_rules', array() );
			$removed_rules[ $cart_rule_id ] = 1;
			WC()->session->set( 'wc_bogof_removed_rules', $removed_rules );
			return;
		}

		// Restore the cart rule.
		self::restore_cart_rule( $cart_item_key );

		// Refresh cart rules.
		self::cart_update();
	}

	/**
	 * Update free items qty on item qty updated.
	 *
	 * @param string $cart_item_key Cart item key.
	 */
	public static function cart_item_set_quantity( $cart_item_key ) {
		$cart_item = WC()->cart->get_cart_item( $cart_item_key );

		if ( empty( $cart_item ) || self::is_free_item( $cart_item ) ) {
			return;
		}

		// Restore the cart rule.
		self::restore_cart_rule( $cart_item_key );

		// Refresh cart rules.
		self::cart_update();
	}

	/**
	 * Unset free item flag after item restored
	 *
	 * @param string $cart_item_key Cart item key.
	 */
	public static function cart_item_restored( $cart_item_key ) {
		$cart_contents = WC()->cart->get_cart_contents();
		$cart_item     = isset( $cart_contents[ $cart_item_key ] ) ? $cart_contents[ $cart_item_key ] : false;

		if ( $cart_item && self::is_free_item( $cart_item ) ) {
			unset( WC()->cart->cart_contents[ $cart_item_key ]['_bogof_free_item'] );
		}
	}

	/**
	 * Check the usage limit of each rule in the cart.
	 *
	 * @param array $posted Post data.
	 */
	public static function checkout_validation( $posted ) {
		$rules_count = array();

		foreach ( self::get_cart_rules() as $cart_rule_id => $cart_rule ) {

			$rule    = $cart_rule->get_rule();
			$rule_id = $rule->get_id();

			if ( $rule->get_usage_limit_per_user() > 0 && self::cart_rule_in_use( $cart_rule_id ) ) {

				$rules_count[ $rule_id ] = isset( $rules_count[ $rule_id ] ) ? $rules_count[ $rule_id ] + 1 : 1;

				$customer_ids   = wc_bogof_user_ids();
				$customer_ids[] = empty( $posted['billing_email'] ) ? false : strtolower( sanitize_email( $posted['billing_email'] ) );

				$total_uses = $rule->get_used_by_count( $customer_ids ) + $rules_count[ $rule_id ];

				if ( $total_uses > $rule->get_usage_limit_per_user() ) {
					self::remove_free_items( $cart_rule_id );
					self::remove_discount( $cart_rule_id );

					WC()->session->set( 'refresh_totals', true );

					wc_add_notice( 'You reached the usage limit of the offer.', 'error' );
				}
			}
		}
	}

	/**
	 * Add the rule Id as order metadata.
	 *
	 * @param WC_Order $order Order object.
	 */
	public static function checkout_create_order( $order ) {
		foreach ( self::get_cart_rules() as $cart_rule_id => $cart_rule ) {
			if ( self::cart_rule_in_use( $cart_rule_id ) ) {
				$cart_rule->get_rule()->increase_usage_count( $order );
			}
		}
	}

	/**
	 * Add the rule ID as item meta.
	 *
	 * @param  WC_Order_Item_Product $item          Order item data.
	 * @param  string                $cart_item_key Cart item key.
	 * @param  array                 $values        Order item values.
	 */
	public static function checkout_create_order_line_item( $item, $cart_item_key, $values ) {
		$rule_ids  = array();
		$cart_item = WC()->cart->get_cart_item( $cart_item_key );

		if ( self::is_valid_free_item( $cart_item ) ) {
			$cart_rule = self::get_cart_rule( $cart_item['_bogof_free_item'] );
			if ( $cart_rule ) {
				$rule_ids[] = $cart_rule->get_rule_id();
			}
		} elseif ( self::is_valid_discount( $cart_item ) ) {
			foreach ( array_keys( $cart_item['data']->_bogof_discount->get_rules() ) as $cart_rule_id ) {
				$cart_rule = self::get_cart_rule( $cart_rule_id );
				if ( $cart_rule ) {
					$rule_ids[] = $cart_rule->get_rule_id();
				}
			}
		}

		foreach ( array_unique( $rule_ids ) as $rule_id ) {
			$item->add_meta_data( '_wc_bogof_rule_id', $rule_id );
		}
	}

	/**
	 * Sort cart rule array.
	 *
	 * @param WC_BOGOF_Cart_Rule $a Cart rule to compare.
	 * @param WC_BOGOF_Cart_Rule $b Cart rule to compare.
	 * @return int
	 */
	private static function sort_cart_rules( $a, $b ) {
		$count = array(
			'a' => 0,
			'b' => 0,
		);
		$cmp   = 0;

		if ( 'cheapest_free' === $a->get_rule()->get_type() && 'cheapest_free' !== $b->get_rule()->get_type() ) {
			$cmp = -1;
		} elseif ( 'cheapest_free' !== $a->get_rule()->get_type() && 'cheapest_free' === $b->get_rule()->get_type() ) {
			$cmp = 1;
		} else {
			// Order by number of items cover.
			foreach ( array( 'a', 'b' ) as $rule ) {
				$id = ${$rule}->get_id();
				foreach ( self::$cart_rules_ref as $rule_ref ) {
					if ( in_array( $id, $rule_ref, true ) ) {
						$count[ $rule ]++;
					}
				}
			}

			if ( $count['a'] === $count['b'] ) {
				// Order by buy quantity.
				foreach ( array( 'a', 'b' ) as $rule ) {
					$count[ $rule ] += ${$rule}->get_rule()->get_min_quantity();
				}
			}

			if ( $count['a'] === $count['b'] ) {
				$cmp = 0;
			} else {
				$cmp = $count['a'] < $count['b'] ? -1 : 1;
			}
		}
		return $cmp;
	}

	/**
	 * Compare two cart rules by ID.
	 *
	 * @param WC_BOGOF_Cart_Rule $a Cart rule to compare.
	 * @param WC_BOGOF_Cart_Rule $b Cart rule to compare.
	 * @return int
	 */
	private static function compare_cart_rules( $a, $b ) {
		$cmp = 0;
		if ( $a->get_id() !== $b->get_id() ) {
			$cmp = $a->get_id() < $b->get_id() ? -1 : 1;
		}
		return $cmp;
	}

	/**
	 * Refresh cart rules quantities and discounts.
	 */
	public static function cart_update() {
		// Remove actions that also run cart_update.
		remove_action( 'woocommerce_add_to_cart', array( __CLASS__, 'add_to_cart' ), 5 );
		remove_action( 'woocommerce_cart_item_removed', array( __CLASS__, 'cart_item_removed' ), 5 );
		remove_action( 'woocommerce_after_cart_item_quantity_update', array( __CLASS__, 'cart_item_set_quantity' ), 5 );
		// Do not calcule totals after add or remove free items.
		remove_action( 'woocommerce_cart_item_removed', array( WC()->cart, 'calculate_totals' ), 20, 0 );
		remove_action( 'woocommerce_add_to_cart', array( WC()->cart, 'calculate_totals' ), 20, 0 );

		$cart_rules = self::get_cart_rules();

		// Sort rules.
		usort( $cart_rules, array( __CLASS__, 'sort_cart_rules' ) );

		// Remove all discounts.
		self::remove_discount( wc_list_pluck( $cart_rules, 'get_id' ) );

		// Get removed cart rules.
		$removed_rules = WC()->session->get( 'wc_bogof_removed_rules', array() );

		// Cart rules refresh.
		foreach ( $cart_rules as $cart_rule ) {
			if ( is_callable( array( $cart_rule, 'update_free_items_qty' ) ) ) {

				$add_to_cart = empty( $removed_rules[ $cart_rule->get_id() ] );

				// Update free items.
				$cart_rule->update_free_items_qty( $add_to_cart );
			}
		}

		// Add the actions.
		add_action( 'woocommerce_add_to_cart', array( __CLASS__, 'add_to_cart' ), 5 );
		add_action( 'woocommerce_cart_item_removed', array( __CLASS__, 'cart_item_removed' ), 5 );
		add_action( 'woocommerce_after_cart_item_quantity_update', array( __CLASS__, 'cart_item_set_quantity' ), 5 );
		add_action( 'woocommerce_cart_item_removed', array( WC()->cart, 'calculate_totals' ), 20, 0 );
		add_action( 'woocommerce_add_to_cart', array( WC()->cart, 'calculate_totals' ), 20, 0 );

		// Cart rules updated.
		do_action( 'wc_bogof_after_cart_rules_update' );
	}

	/**
	 * Verify the qty of the free item is not over the allowed.
	 *
	 * @param string $cart_item_key Cart item key.
	 * @param array  $cart_item_data Cart item data.
	 */
	private static function check_cart_free_item_qty( $cart_item_key, $cart_item_data ) {
		$cart_rule = self::get_cart_rule( $cart_item_data['_bogof_free_item'] );
		$quantity  = self::get_free_quantity( $cart_rule->get_id() );

		if ( $cart_rule && $quantity && $quantity > $cart_rule->get_max_free_quantity() ) {
			$product_quantity = $cart_item_data['quantity'];
			$extra_quantity   = $quantity - $cart_rule->get_max_free_quantity();

			// Set the max value.
			WC()->cart->cart_contents[ $cart_item_key ]['quantity'] = $product_quantity - $extra_quantity;

			// Add to cart the rest.
			self::add_to_cart_from_item( $cart_item_data, $extra_quantity );
		}
	}

	/**
	 * Update product with the flags before add to cart.
	 *
	 * Prevent overwriting in woocommerce_add_cart_item hook.
	 *
	 * @param array $cart_item_data Cart item data.
	 * @return array
	 */
	public static function add_cart_item( $cart_item_data ) {
		if ( self::is_valid_free_item( $cart_item_data ) && isset( $cart_item_data['data'] ) ) {
			self::set_free_price( $cart_item_data['data'], $cart_item_data['_bogof_free_item'] );
		}
		return $cart_item_data;
	}

	/**
	 * Update product with the flags.
	 *
	 * @param array  $session_data Session data.
	 * @param array  $values Values.
	 * @param string $key Item key.
	 * @return array
	 */
	public static function get_cart_item_from_session( $session_data, $values, $key ) {
		if ( isset( $values['_bogof_free_item'] ) ) {
			$session_data = self::set_cart_item_free( $session_data, $values['_bogof_free_item'] );
		} elseif ( isset( $values['_bogof_discount'] ) && is_array( $values['_bogof_discount'] ) ) {
			$session_data['data']->_bogof_discount = new WC_BOGOF_Cart_Item_Discount( $session_data, $values['_bogof_discount'] );
		}
		return $session_data;
	}

	/**
	 * Return the zero price for free product in the cart.
	 *
	 * @param mixed      $price Product price.
	 * @param WC_Product $product Product instance.
	 */
	public static function get_free_product_price( $price, $product ) {
		if ( ! empty( $product->_bogof_free_item ) && isset( self::$cart_rules[ $product->_bogof_free_item ] ) ) {
			$price = 0;
		} elseif ( self::is_valid_discount( $product ) && $product->_bogof_discount->has_discount() ) {
			$price = $product->_bogof_discount->get_sale_price();
		}
		return $price;
	}

	/**
	 * Add the free item added messages.
	 *
	 * @param mixed $value Value to return. Filter call function.
	 */
	public static function add_to_cart_messages( $value ) {
		foreach ( self::get_cart_rules() as $cart_rule ) {
			$cart_rule->add_messages();
		}
		return $value;
	}

	/**
	 * Disable the usage of coupons is there is a free item in the cart.
	 *
	 * @param bool      $is_valid Is valid?.
	 * @param WC_Coupon $coupon Coupon object.
	 * @return bool
	 */
	public static function coupon_is_valid( $is_valid, $coupon ) {
		if ( 'yes' !== get_option( 'wc_bogof_disable_coupons', 'no' ) || ! $is_valid ) {
			return $is_valid;
		}

		// Checks the coupon is in a cart rule.
		foreach ( self::get_cart_rules() as $cart_rule ) {
			if ( in_array( $coupon->get_id(), $cart_rule->get_rule()->get_coupon_ids() ) ) { // phpcs:ignore WordPress.PHP.StrictInArray
				return true;
			}
		}

		// If there is a free item coupon is invalid.
		foreach ( WC()->cart->get_cart_contents() as $cart_item ) {
			if ( self::is_valid_free_item( $cart_item ) || self::is_valid_discount( $cart_item ) ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Before cart item quantity zero.
	 *
	 * @deprecated Since WC3.7
	 * @param string $cart_item_key Cart item key.
	 */
	public static function before_cart_item_quantity_zero( $cart_item_key ) {
		$cart_item = isset( WC()->cart->cart_contents[ $cart_item_key ] ) ? WC()->cart->cart_contents[ $cart_item_key ] : false;

		if ( $cart_item && ! self::is_free_item( $cart_item ) ) {

			// Set the qty to zero before the cart rules update.
			WC()->cart->cart_contents[ $cart_item_key ]['quantity'] = 0;

			self::cart_update();
		}
	}

	/**
	 * Calculate items subtotal after adding a discount.
	 */
	public static function calculate_items_subtotal() {
		if ( ! empty( self::$cart_totals ) ) {
			self::$cart_totals->calculate_items_subtotal();
		}
	}

	/**
	 * ---------------------------------------------
	 * Helper functions.
	 * ---------------------------------------------
	 */

	/**
	 * Return the cart subtotal.
	 *
	 * @since 2.1.0
	 * @return float
	 */
	public static function cart_subtotal() {
		if ( empty( self::$cart_totals ) ) {
			self::$cart_totals = new WC_BOGOF_Cart_Totals();
		}
		return self::$cart_totals->get_subtotal();
	}

	/**
	 * Add to cart a product from a cart item data array.
	 *
	 * @since 2.1.0
	 * @param array $cart_item_data Cart item data.
	 * @param int   $qty Contains the quantity of the item to add.
	 */
	public static function add_to_cart_from_item( $cart_item_data, $qty ) {
		// Ignore cart_item_data when the function is trigger from the "add to cart" form. Prevent issues with Product add-ons.
		$item_data = isset( $_REQUEST['add-to-cart'] ) && is_numeric( wp_unslash( $_REQUEST['add-to-cart'] ) ) ? array_intersect_key( $cart_item_data, array( 'wc_bogof_cart_rule' => '' ) ) : $cart_item_data; // phpcs:ignore WordPress.Security.NonceVerification

		$product_id   = isset( $cart_item_data['product_id'] ) ? $cart_item_data['product_id'] : 0;
		$variation_id = isset( $cart_item_data['variation_id'] ) ? $cart_item_data['variation_id'] : 0;
		$variation    = isset( $cart_item_data['variation'] ) ? $cart_item_data['variation'] : array();

		unset( $item_data['key'] );
		unset( $item_data['product_id'] );
		unset( $item_data['variation_id'] );
		unset( $item_data['variation'] );
		unset( $item_data['quantity'] );
		unset( $item_data['data'] );
		unset( $item_data['data_hash'] );
		unset( $item_data['_bogof_free_item'] );
		unset( $item_data['_bogof_discount'] );

		return WC()->cart->add_to_cart( $product_id, $qty, $variation_id, $variation, $item_data );
	}

	/**
	 * Returns the cart rules.
	 *
	 * @param string $cart_item_key Cart item key or false to all cart rules.
	 * @return array
	 */
	public static function get_cart_rules( $cart_item_key = false ) {
		if ( ! $cart_item_key ) {
			return self::$cart_rules;
		}
		$cart_rules = array();
		if ( ! empty( self::$cart_rules_ref[ $cart_item_key ] ) ) {
			foreach ( array_unique( self::$cart_rules_ref[ $cart_item_key ] ) as $cart_rule_id ) {
				$cart_rules[] = self::get_cart_rule( $cart_rule_id );
			}
		}
		return array_filter( $cart_rules );
	}

	/**
	 * Returns a cart rule by ID.
	 *
	 * @param string $cart_rule_id Cart rule ID.
	 * @return array
	 */
	public static function get_cart_rule( $cart_rule_id ) {
		return isset( self::$cart_rules[ $cart_rule_id ] ) ? self::$cart_rules[ $cart_rule_id ] : false;
	}

	/**
	 * Set a free cart item.
	 *
	 * @param array $cart_item_data Cart item data.
	 * @param mixed $cart_rule_id Rule ID.
	 * @return array
	 */
	public static function set_cart_item_free( $cart_item_data, $cart_rule_id ) {
		$cart_item_data['_bogof_free_item'] = $cart_rule_id;

		if ( isset( $cart_item_data['data'] ) ) {
			self::set_free_price( $cart_item_data['data'], $cart_rule_id );
		}
		return $cart_item_data;
	}

	/**
	 * Set product price to zero.
	 *
	 * @param WC_Product $product Product object.
	 * @param string     $cart_rule_id Cart rule ID.
	 */
	private static function set_free_price( &$product, $cart_rule_id ) {
		$product->set_price( 0 );
		$product->set_sale_price( 0 );
		$product->_bogof_free_item = $cart_rule_id;
	}

	/**
	 * Set an discount price.
	 *
	 * @param string $cart_item_key Cart item key.
	 * @param mixed  $cart_rule_id Rule ID.
	 * @param int    $free_qty Free quantity.
	 * @return array
	 */
	public static function set_cart_item_discount( $cart_item_key, $cart_rule_id, $free_qty ) {
		$cart_item_data = WC()->cart->cart_contents[ $cart_item_key ];

		if ( ! isset( $cart_item_data['data'] ) || ! is_object( $cart_item_data['data'] ) ) {
			return;
		}

		$_product = &$cart_item_data['data'];

		if ( ! isset( $_product->_bogof_discount ) || ! is_a( $_product->_bogof_discount, 'WC_BOGOF_Cart_Item_Discount' ) ) {
			$_product->_bogof_discount = new WC_BOGOF_Cart_Item_Discount( $cart_item_data );
		}

		$_product->_bogof_discount->set_free_quantity( $cart_rule_id, $free_qty );

		WC()->cart->cart_contents[ $cart_item_key ]['_bogof_discount'] = $_product->_bogof_discount->get_rules(); // Do not store objects in the session.

		do_action( 'wc_bogof_after_set_cart_item_discount', $cart_item_data, $_product->_bogof_discount );
	}

	/**
	 * Is a free cart item?.
	 *
	 * @param array $cart_item_data Cart item data.
	 * @return bool
	 */
	public static function is_free_item( $cart_item_data ) {
		return isset( $cart_item_data['_bogof_free_item'] );
	}

	/**
	 * Is a valid free cart item?.
	 *
	 * @param array $cart_item_data Cart item data.
	 * @return bool
	 */
	public static function is_valid_free_item( $cart_item_data ) {
		return isset( $cart_item_data['_bogof_free_item'] ) && isset( self::$cart_rules[ $cart_item_data['_bogof_free_item'] ] );
	}

	/**
	 * Is a valid offer?.
	 *
	 * @param array|object $data Data to check.
	 * @return bool
	 */
	public static function is_valid_discount( $data ) {
		$discount = false;
		if ( is_array( $data ) && isset( $data['data'] ) && is_object( $data['data'] ) && isset( $data['data']->_bogof_discount ) ) {
			$discount = $data['data']->_bogof_discount;
		} elseif ( is_object( $data ) && isset( $data->_bogof_discount ) ) {
			$discount = $data->_bogof_discount;
		}
		return $discount && is_a( $discount, 'WC_BOGOF_Cart_Item_Discount' ) && $discount->has_cart_rule( array_keys( self::get_cart_rules() ) );
	}

	/**
	 * Returns the free items in the cart of a rule.
	 *
	 * @param mixed $cart_rule_id Rule ID.
	 * @return array
	 */
	public static function get_free_items( $cart_rule_id ) {
		$items         = array();
		$cart_contents = WC()->cart->get_cart_contents();
		foreach ( $cart_contents as $key => $cart_item ) {
			if ( self::is_valid_free_item( $cart_item ) && $cart_rule_id === $cart_item['_bogof_free_item'] ) {
				$items[ $key ] = $cart_item;
			}
		}
		return $items;
	}

	/**
	 * Returns the number of free items in the cart of a rule.
	 *
	 * @param mixed $cart_rule_id Cart rule ID.
	 * @return int
	 */
	public static function get_free_quantity( $cart_rule_id ) {
		$qty           = 0;
		$cart_contents = self::get_free_items( $cart_rule_id );
		foreach ( $cart_contents as $cart_item ) {
			$qty += $cart_item['quantity'];
		}
		return $qty;
	}

	/**
	 * Removes the free items of a cart rule.
	 *
	 * @param mixed $cart_rule_id Cart rule ID.
	 */
	public static function remove_free_items( $cart_rule_id ) {
		$free_items = self::get_free_items( $cart_rule_id );
		foreach ( array_keys( $free_items ) as $cart_item_key ) {
			unset( WC()->cart->cart_contents[ $cart_item_key ] );
		}
	}

	/**
	 * Removes the discount of a cart rule.
	 *
	 * @param string|array $cart_rule_id Cart rule ID or array of IDs.
	 */
	public static function remove_discount( $cart_rule_id ) {
		$cart_contents = WC()->cart->get_cart_contents();
		$cart_rule_ids = is_array( $cart_rule_id ) ? $cart_rule_id : array( $cart_rule_id );

		foreach ( self::$cart_rules_ref as $item_key => $cart_rules_ids ) {
			if ( isset( $cart_contents[ $item_key ] ) && self::is_valid_discount( $cart_contents[ $item_key ] ) && wc_bogof_in_array_intersect( $cart_rule_ids, $cart_rules_ids ) ) {
				foreach ( $cart_rule_ids as $id ) {
					WC()->cart->cart_contents[ $item_key ]['data']->_bogof_discount->remove_free_quantity( $id );
				}
				WC()->cart->cart_contents[ $item_key ]['_bogof_discount'] = WC()->cart->cart_contents[ $item_key ]['data']->_bogof_discount->get_rules();
			}
		}
	}

	/**
	 * Returns the number of items available for free in the shop.
	 *
	 * @return int
	 */
	public static function get_shop_free_quantity() {
		$qty = 0;
		foreach ( self::$cart_rules as $rule ) {
			$qty += $rule->get_shop_free_quantity();
		}
		return $qty;
	}

	/**
	 * Returns the free available qty for a product.
	 *
	 * @param WC_Product $product Product instance.
	 * @return int
	 */
	public static function get_product_shop_free_quantity( $product ) {
		$qty = 0;
		foreach ( self::get_cart_rules() as $cart_rule ) {
			if ( $cart_rule->is_shop_avilable_free_product( $product ) ) {
				$qty += $cart_rule->get_shop_free_quantity();
			}
		}
		return $qty;
	}

	/**
	 * Returns the hash based on cart contents and bogo rules.
	 *
	 * @return string hash for cart content
	 */
	public static function get_hash() {
		if ( ! isset( WC()->cart ) ) {
			return false;
		}
		$pieces = array();
		foreach ( self::$cart_rules as $rule_id => $rule ) {
			$pieces[ $rule_id ] = $rule->get_free_products_in();
		}
		return md5( wp_json_encode( $pieces ) );
	}

	/**
	 * Is there a free item in the cart that that belongs to the "cart rule"?
	 *
	 * @param string $cart_rule_id Cart rule ID.
	 * @return boolean
	 */
	public static function cart_rule_has_free_item( $cart_rule_id ) {
		return count( self::get_free_items( $cart_rule_id ) ) > 0;
	}

	/**
	 * Is there a discount in the cart that that belongs to the "cart rule"?
	 *
	 * @param string $cart_rule_id Cart rule ID.
	 * @return boolean
	 */
	public static function cart_rule_has_discount( $cart_rule_id ) {
		$has_discount  = false;
		$cart_contents = WC()->cart->get_cart_contents();
		foreach ( $cart_contents as $cart_item ) {
			if ( self::is_valid_discount( $cart_item ) && $cart_item['data']->_bogof_discount->has_cart_rule( $cart_rule_id ) ) {
				$has_discount = true;
				break;
			}
		}
		return $has_discount;
	}

	/**
	 * Returns is the cart rule is in use.
	 *
	 * @param string $cart_rule_id Cart rule ID.
	 * @return boolean
	 */
	public static function cart_rule_in_use( $cart_rule_id ) {
		return self::cart_rule_has_free_item( $cart_rule_id ) || self::cart_rule_has_discount( $cart_rule_id );
	}

	/**
	 * Returns the number of times a rule is used in the cart.
	 *
	 * @param int $rule_id Rule ID.
	 * @return int
	 */
	public static function get_rule_count( $rule_id ) {
		$count = 0;
		foreach ( self::get_cart_rules() as $cart_rule_id => $cart_rule ) {
			if ( $cart_rule->get_rule_id() === $rule_id && self::cart_rule_in_use( $cart_rule_id ) ) {
				$count++;
			}
		}
		return $count++;
	}
}
