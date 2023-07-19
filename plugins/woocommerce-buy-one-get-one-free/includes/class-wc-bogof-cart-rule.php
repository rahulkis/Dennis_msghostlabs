<?php
/**
 * Buy One Get One Free Cart Rule. Handles BOGO rule actions.
 *
 * @package WC_BOGOF
 */

defined( 'ABSPATH' ) || exit;

/**
 * WC_BOGOF_Cart_Rule Class
 */
class WC_BOGOF_Cart_Rule {

	/**
	 * BOGOF rule.
	 *
	 * @var WC_BOGOF_Rule
	 */
	protected $rule;

	/**
	 * Array of cart totals.
	 *
	 * @var array
	 */
	protected $totals;

	/**
	 * Array of notices.
	 *
	 * @var array
	 */
	protected $notices;

	/**
	 * Does the cart rule support choose your gift?
	 *
	 * @var bool
	 */
	protected $support_choose_your_gift = true;


	/**
	 * Constructor.
	 *
	 * @param WC_BOGOF_Rule $rule BOGOF rule.
	 */
	public function __construct( $rule ) {
		$this->rule    = $rule;
		$this->totals  = array();
		$this->notices = array();
	}

	/**
	 * Return the cart rule ID.
	 */
	public function get_id() {
		return $this->rule->get_id();
	}

	/**
	 * Return the rule ID.
	 */
	final public function get_rule_id() {
		return $this->rule->get_id();
	}

	/**
	 * Return the rule ID.
	 */
	final public function get_rule() {
		return $this->rule;
	}

	/**
	 * Unset the totals array.
	 */
	public function clear_totals() {
		$this->totals = array();
	}

	/**
	 * Does the cart item match with the rule?
	 *
	 * @param array $cart_item Cart item.
	 * @return bool
	 */
	protected function cart_item_match( $cart_item ) {
		if ( WC_BOGOF_Cart::is_free_item( $cart_item ) || ! isset( $cart_item['data'] ) || ! is_callable( array( $cart_item['data'], 'get_id' ) ) || wc_bogof_cart_item_match_skip( $this, $cart_item ) ) {
			return false;
		}
		$match      = false;
		$product_id = $cart_item['data']->get_id();

		return $this->rule->is_buy_product( $product_id ) && ! $this->rule->is_exclude_product( $product_id );
	}

	/**
	 * Add the free product to the cart.
	 *
	 * @param int $qty The quantity of the item to add.
	 */
	protected function add_to_cart( $qty = 1 ) {
		$items = WC_BOGOF_Cart::get_free_items( $this->get_id() );

		if ( count( $items ) ) {
			// Set the qty.
			$cart_item_keys = array_keys( $items );
			$cart_item_key  = $cart_item_keys[0];

			$cart_item = WC()->cart->get_cart_item( $cart_item_key );
			if ( ! empty( $cart_item ) && isset( $cart_item['product_id'] ) && isset( $cart_item['quantity'] ) && isset( $cart_item['data'] ) ) {

				$product_data = $cart_item['data'];

				// Force quantity to 1 if sold individually and check for existing item in cart.
				if ( $product_data->is_sold_individually() ) {
					$qty = apply_filters( 'woocommerce_add_to_cart_sold_individually_quantity', 1, $qty, $cart_item['product_id'], 0, $cart_item );
				}

				$qty_added = $qty - $cart_item['quantity'];
				if ( $qty_added > 0 ) {
					// Set the quantity.
					WC()->cart->set_quantity( $cart_item_key, $qty, false );

					// Add the message.
					$this->add_free_product_to_cart_message( $cart_item['product_id'], $qty_added );
				}
			}
		} else {

			$cart_item_key = $this->add_free_product_to_cart( $qty );

			if ( $cart_item_key ) {
				$cart_item = WC()->cart->get_cart_item( $cart_item_key );

				if ( ! empty( $cart_item ) && isset( $cart_item['product_id'] ) && isset( $cart_item['quantity'] ) ) {
					$this->add_free_product_to_cart_message( $cart_item['product_id'], $cart_item['quantity'] );
				}
			} else {
				// Log the error.
				$notices = wc_get_notices();

				if ( isset( $notices['error'] ) && count( $notices['error'] ) ) {
					$error      = array_pop( $notices['error'] );
					$error_text = is_array( $error ) && isset( $error['notice'] ) ? $error['notice'] : $error;
					$logger     = wc_get_logger();
					$logger->error( sprintf( 'BOGO id: %s - Imposible to add the free product to the cart: ', $this->rule->get_id() ) . $error_text, array( 'source' => 'woocommerce-buy-one-get-one-free' ) );

					wc_set_notices( $notices );
				}
			}
		}

		$this->clear_totals();
	}

	/**
	 * Add the free product to the cart.
	 *
	 * @param int $qty The quantity of the item to add.
	 * @return string|bool $cart_item_key
	 */
	protected function add_free_product_to_cart( $qty ) {
		$cart_item_key = false;
		$product_id    = $this->rule->get_free_product_id();
		if ( $product_id ) {
			$cart_item_key = WC()->cart->add_to_cart( $product_id, $qty, 0, array(), array( 'wc_bogof_cart_rule' => array( $this->get_id() ) ) );
		}
		return $cart_item_key;
	}

	/**
	 * Add free product to cart message.
	 *
	 * @param int $product_id Product ID.
	 * @param int $qty Quantity.
	 */
	protected function add_free_product_to_cart_message( $product_id, $qty ) {
		global $wp_query;

		if ( is_ajax() && 'add_to_cart' === $wp_query->get( 'wc-ajax' ) && 'yes' !== get_option( 'woocommerce_cart_redirect_after_add' ) ) {
			return;
		}

		/* translators: %s: product name */
		$title = apply_filters( 'woocommerce_add_to_cart_qty_html', absint( $qty ) . ' &times; ', $product_id ) . apply_filters( 'woocommerce_add_to_cart_item_name_in_quotes', sprintf( _x( '&ldquo;%s&rdquo;', 'Item name in quotes', 'wc-buy-one-get-one-free' ), wp_strip_all_tags( get_the_title( $product_id ) ) ), $product_id );
		/* translators: %s: product name */
		$message = sprintf( _n( '%s has been added to your cart for free!', '%s have been added to your cart for free!', $qty, 'wc-buy-one-get-one-free' ), $title );

		// Added the notices to the array.
		$this->notices[] = apply_filters( 'wc_bogof_add_free_product_to_cart_message_html', $message, $product_id, $qty );

		if ( is_ajax() ) {
			$this->add_messages();
		}
	}

	/**
	 * Add the free product messages to the session.
	 */
	public function add_messages() {
		foreach ( $this->notices as $notice ) {
			wc_add_notice( $notice, apply_filters( 'woocommerce_add_to_cart_notice_type', 'success' ) );
		}
		$this->notices = array();
	}

	/**
	 * Check if is a shop page.
	 *
	 * @return bool
	 */
	protected function is_shop_page() {
		return did_action( 'parse_request' ) && WC_BOGOF_Choose_Gift::is_choose_your_gift();
	}

	/**
	 * Check the cart
	 *
	 * @return bool
	 */
	protected function check_cart() {
		return $this->check_cart_amount() && $this->check_cart_coupons();
	}

	/**
	 * Check the cart amount.
	 *
	 * @return bool
	 */
	protected function check_cart_amount() {
		$minimum_amount = $this->rule->get_minimum_amount();
		$minimum_amount = empty( $minimum_amount ) ? 0 : $minimum_amount;

		return WC_BOGOF_Cart::cart_subtotal() > $minimum_amount;
	}


	/**
	 * Check the cart coupons.
	 *
	 * @return bool
	 */
	protected function check_cart_coupons() {
		$coupons = $this->rule->get_coupon_codes();
		$valid   = empty( $coupons );
		if ( ! $valid ) {
			$valid = wc_bogof_in_array_intersect( $coupons, WC()->cart->get_applied_coupons() );
		}
		return $valid;
	}

	/**
	 * Checks if a cart data array contains the cart rule ID and unset the element.
	 *
	 * @param array $cart_item_data Cart item data.
	 * @return bool
	 */
	protected function check_cart_item_data( &$cart_item_data ) {
		// phpcs:disable WordPress.Security.NonceVerification
		$cart_rules = false;

		if ( isset( $cart_item_data['wc_bogof_cart_rule'] ) ) {
			$cart_rules = $cart_item_data['wc_bogof_cart_rule'];
		}

		$cart_rules = is_array( $cart_rules ) ? $cart_rules : array();

		$indexes = array_keys( $cart_rules, $this->get_id() ); // phpcs:ignore WordPress.PHP.StrictInArray

		// Check it once time.
		foreach ( $indexes as $index ) {
			unset( $cart_item_data['wc_bogof_cart_rule'][ $index ] );
		}
		// phpcs:enable
		return count( $indexes ) > 0;
	}

	/**
	 * Parse cart item data.
	 *
	 * @param array $cart_item_data Cart item data.
	 * @return array
	 */
	protected function parse_cart_item_data( $cart_item_data ) {
		$cart_item_data = is_array( $cart_item_data ) ? $cart_item_data : array();

		if ( ! isset( $cart_item_data['wc_bogof_cart_rule'] ) && isset( $_REQUEST['wc_bogof_cart_rule'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$cart_item_data['wc_bogof_cart_rule'] = wc_clean( wp_unslash( $_REQUEST['wc_bogof_cart_rule'] ) ); // phpcs:ignore WordPress.Security.NonceVerification

			if ( is_ajax() && is_string( $cart_item_data['wc_bogof_cart_rule'] ) ) {
				$cart_item_data['wc_bogof_cart_rule'] = json_decode( $cart_item_data['wc_bogof_cart_rule'] );
			}
		}

		if ( isset( $cart_item_data['wc_bogof_cart_rule'] ) && ! is_array( $cart_item_data['wc_bogof_cart_rule'] ) ) {
			$cart_item_data['wc_bogof_cart_rule'] = array();
		}

		return $cart_item_data;
	}

	/**
	 * Returns the quantity from a cart item.
	 *
	 * @param array $cart_item Cart item data.
	 * @return int
	 */
	protected function get_cart_item_quantity( $cart_item ) {
		$quantity = isset( $cart_item['quantity'] ) ? absint( $cart_item['quantity'] ) : 0;
		if ( WC_BOGOF_Cart::is_valid_discount( $cart_item ) ) {
			$quantity -= $cart_item['data']->_bogof_discount->get_free_quantity();
		}
		return 0 > $quantity ? 0 : $quantity;
	}

	/**
	 * Count numbers of products that matches the rule.
	 *
	 * @return int
	 */
	public function get_cart_quantity() {
		if ( ! isset( $this->totals['cart_quantity'] ) ) {

			$cart_quantity = 0;
			$cart_contents = WC()->cart->get_cart_contents();
			foreach ( $cart_contents as $key => $cart_item ) {
				if ( $this->cart_item_match( $cart_item ) ) {
					$cart_quantity += $this->get_cart_item_quantity( $cart_item );
				}
			}

			$this->totals['cart_quantity'] = $cart_quantity;
		}

		return $this->totals['cart_quantity'];
	}

	/**
	 * Get the quantity of the free items based on rule and on the product quantity in the cart.
	 *
	 * @return int
	 */
	public function get_max_free_quantity() {
		if ( ! isset( $this->totals['free_quantity'] ) ) {

			$cart_qty = $this->get_cart_quantity();
			$free_qty = 0;

			if ( $cart_qty >= $this->rule->get_min_quantity() && 0 < $this->rule->get_min_quantity() && 0 < $this->rule->get_free_quantity() && $this->check_cart() ) {

				$free_qty = absint( ( floor( $cart_qty / $this->rule->get_min_quantity() ) * $this->rule->get_free_quantity() ) );

				if ( $this->rule->get_cart_limit() && $free_qty > $this->rule->get_cart_limit() ) {
					$free_qty = $this->rule->get_cart_limit();
				}
			}

			$this->totals['free_quantity'] = $free_qty;
		}

		return apply_filters( 'wc_bogof_free_item_quantity', $this->totals['free_quantity'], $this->get_cart_quantity(), $this->rule, $this );
	}

	/**
	 * Returns the number of items available for free in the shop.
	 *
	 * @return int
	 */
	public function get_shop_free_quantity() {
		if ( ! isset( $this->totals['shop_free_quantity'] ) ) {
			$this->totals['shop_free_quantity'] = $this->support_choose_your_gift ? $this->get_max_free_quantity() - WC_BOGOF_Cart::get_free_quantity( $this->get_id() ) : 0;
		}
		return $this->totals['shop_free_quantity'];
	}

	/**
	 * Is the product avilable for free in the shop.
	 *
	 * @param int|WC_Product $product Product ID or Product object.
	 * @return bool
	 */
	public function is_shop_avilable_free_product( $product ) {
		$is_free = false;
		if ( $this->get_shop_free_quantity() > 0 ) {
			if ( is_numeric( $product ) ) {
				$is_free = $this->rule->is_free_product( $product );
			} elseif ( is_a( $product, 'WC_Product' ) ) {
				$is_free = $this->rule->is_free_product( $product->get_id() );
				if ( ! $is_free && 'variable' === $product->get_type() ) {
					foreach ( $product->get_children() as $child_id ) {
						$is_free = $this->rule->is_free_product( $child_id );
						if ( $is_free ) {
							break;
						}
					}
				}
			}
		}
		return $is_free;
	}

	/**
	 * Update the quantity of free items in the cart.
	 *
	 * @param bool $add_to_cart Add free items to cart?.
	 */
	public function update_free_items_qty( $add_to_cart = true ) {

		$this->clear_totals();

		$max_qty        = $this->get_max_free_quantity();
		$free_items_qty = WC_BOGOF_Cart::get_free_quantity( $this->get_id() );

		if ( $free_items_qty > $max_qty ) {

			$items    = WC_BOGOF_Cart::get_free_items( $this->get_id() );
			$over_qty = $free_items_qty - $max_qty;

			foreach ( $items as $key => $item ) {
				if ( 0 === $over_qty ) {
					break;
				}

				if ( $item['quantity'] > $over_qty ) {
					WC()->cart->set_quantity( $key, $item['quantity'] - $over_qty, false );
					$over_qty = 0;
				} else {
					WC()->cart->set_quantity( $key, 0, false );
					$over_qty -= $item['quantity'];
				}
			}
		} elseif ( $add_to_cart && $this->rule->is_action( 'add_to_cart' ) && ( $max_qty - $free_items_qty ) > 0 ) {
			$this->add_to_cart( $max_qty );
		}
	}

	/**
	 * Returns SQL string of the free avilable products to be use in a SELECT.
	 *
	 * @see WC_BOGOF_Choose_Gift::posts_where
	 * @return string
	 */
	public function get_free_products_in() {
		global $wpdb;
		$post_in = false;

		if ( $this->get_shop_free_quantity() > 0 ) {
			if ( $this->rule->is_action( 'choose_from_category' ) ) {
				if ( in_array( 'all', $this->rule->get_free_category_ids(), true ) ) {
					$post_in = '1=1';
				} else {
					$term_taxonomy_ids = implode( ',', array_map( 'absint', $this->rule->get_free_category_ids() ) );
					$post_in           = "{$wpdb->posts}.ID IN ( SELECT object_id FROM {$wpdb->term_relationships} WHERE term_taxonomy_id IN ({$term_taxonomy_ids}) )";
				}
			} else {
				$free_products = $this->rule->get_free_product_ids();
				$parents       = array();
				foreach ( $free_products as $product_id ) {
					if ( 'product_variation' === get_post_type( $product_id ) ) {
						$parents[] = wp_get_post_parent_id( $product_id );
					}
				}
				$free_products = array_merge( $free_products, $parents );

				$post_in = $wpdb->posts . '.ID IN (' . implode( ',', array_map( 'absint', $free_products ) ) . ')';
			}
		}

		return $post_in;
	}

	/**
	 * Init filter and actions.
	 */
	public function init_hooks() {
		add_filter( 'woocommerce_add_cart_item_data', array( $this, 'add_cart_item' ), 100, 3 );
		if ( $this->support_choose_your_gift ) {
			// Choose your gift actions.
			add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'before_add_to_cart_button' ) );
			add_filter( 'woocommerce_product_add_to_cart_url', array( $this, 'product_add_to_cart_url' ), 100, 2 );
			add_filter( 'woocommerce_loop_add_to_cart_args', array( $this, 'loop_add_to_cart_args' ), 100, 2 );
			add_filter( 'woocommerce_product_get_price', array( $this, 'get_free_product_price' ), 9999, 2 );
			add_filter( 'woocommerce_product_variation_get_price', array( $this, 'get_free_product_price' ), 9999, 2 );
			add_filter( 'woocommerce_product_get_sale_price', array( $this, 'get_free_product_price' ), 9999, 2 );
			add_filter( 'woocommerce_product_variation_get_sale_price', array( $this, 'get_free_product_price' ), 9999, 2 );
			add_filter( 'woocommerce_variation_prices_price', array( $this, 'get_free_product_price' ), 9999, 2 );
			add_filter( 'woocommerce_variation_prices_sale_price', array( $this, 'get_free_product_price' ), 9999, 2 );
			add_filter( 'woocommerce_get_variation_prices_hash', array( $this, 'get_variation_prices_hash' ), 100, 2 );
		}
	}

	/**
	 * Output the bogo cart rule field.
	 */
	public function before_add_to_cart_button() {
		global $product;
		global $post;

		$product = is_callable( array( $product, 'get_id' ) ) ? $product : $post->ID;

		if ( $this->is_shop_page() && $this->is_shop_avilable_free_product( $product ) ) {
			echo '<input type="hidden" name="wc_bogof_cart_rule[]" value="' . esc_attr( $this->get_id() ) . '" />';
		}
	}

	/**
	 * Appends the bogo cart rule parameter.
	 *
	 * @param string     $url Add to cart URL.
	 * @param WC_Product $product Product instance.
	 * @return string
	 */
	public function product_add_to_cart_url( $url, $product = false ) {
		if ( $this->is_shop_page() && strpos( $url, 'add-to-cart' ) && $this->is_shop_avilable_free_product( $product ) ) {
			$url = add_query_arg( 'wc_bogof_cart_rule[]', esc_attr( $this->get_id() ), $url );
		}
		return $url;
	}

	/**
	 * Add attributes to loop add to cart link.
	 *
	 * @param array      $args Array of arguments.
	 * @param WC_Product $product Product object.
	 */
	public function loop_add_to_cart_args( $args, $product ) {

		if ( $this->is_shop_page() && $this->is_shop_avilable_free_product( $product ) ) {
			$args               = is_array( $args ) ? $args : array();
			$args['attributes'] = empty( $args['attributes'] ) || ! is_array( $args['attributes'] ) ? array() : $args['attributes'];
			$data               = false;

			if ( isset( $args['attributes']['data-wc_bogof_cart_rule'] ) ) {
				$data = $args['attributes']['data-wc_bogof_cart_rule'];
				if ( is_string( $data ) ) {
					$data = json_decode( $data );
				}
			}

			$data   = is_array( $data ) ? $data : array();
			$data[] = $this->get_id();

			$args['attributes']['data-wc_bogof_cart_rule'] = wp_json_encode( $data );
		}

		return $args;
	}

	/**
	 * Return the zero price for free products.
	 *
	 * @param mixed      $price Product price.
	 * @param WC_Product $product Product instance.
	 */
	public function get_free_product_price( $price, $product ) {
		if ( $this->is_shop_page() && $this->is_shop_avilable_free_product( $product ) ) {
			$price = 0;
		}
		return $price;
	}

	/**
	 * Returns unique cache key to store variation child prices.
	 *
	 * @param array      $price_hash Unique cache key.
	 * @param WC_Product $product Product instance.
	 * @return array
	 */
	public function get_variation_prices_hash( $price_hash, $product ) {
		if ( $this->is_shop_page() ) {
			$price_hash   = is_array( $price_hash ) ? $price_hash : array( $price_hash );
			$price_hash[] = WC_BOGOF_Cart::get_hash() . $this->get_id();
		}
		return $price_hash;
	}

	/**
	 * Update the cart item data.
	 *
	 * @param array $cart_item_data Cart item data.
	 * @param int   $product_id The product ID.
	 * @param int   $variation_id The variation ID.
	 */
	public function add_cart_item( $cart_item_data, $product_id, $variation_id ) {
		$cart_item_data = $this->parse_cart_item_data( $cart_item_data );

		if ( WC_BOGOF_Cart::is_free_item( $cart_item_data ) || ! $this->check_cart_item_data( $cart_item_data ) ) { // phpcs:ignore WordPress.PHP.StrictInArray
			return $cart_item_data;
		}

		$product_id = $variation_id ? $variation_id : $product_id;
		if ( $this->is_shop_avilable_free_product( $product_id ) ) {
			// Set as a free item.
			$cart_item_data = WC_BOGOF_Cart::set_cart_item_free( $cart_item_data, $this->get_id() );
		}
		return $cart_item_data;
	}
}
