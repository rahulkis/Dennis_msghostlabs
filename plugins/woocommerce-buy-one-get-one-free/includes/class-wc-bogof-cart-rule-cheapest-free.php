<?php
/**
 * Get the cheapest item free. Handles BOGO rule actions.
 *
 * @package WC_BOGOF
 */

defined( 'ABSPATH' ) || exit;

/**
 * WC_BOGOF_Cart_Rule_Buy_A_Get_A Class
 */
class WC_BOGOF_Cart_Rule_Cheapest_Free extends WC_BOGOF_Cart_Rule {

	/**
	 * Constructor.
	 *
	 * @param WC_BOGOF_Rule $rule BOGOF rule.
	 */
	public function __construct( $rule ) {
		parent::__construct( $rule );
		$this->support_choose_your_gift = false;
	}


	/**
	 * Returns cart items order by price.
	 */
	protected function get_cart_items() {
		$items_sorted  = array();
		$cart_contents = WC()->cart->get_cart_contents();

		foreach ( $cart_contents as $cart_item_key => $cart_item ) {
			if ( $this->cart_item_match( $cart_item ) ) {
				$items_sorted[ $cart_item_key ] = $cart_item;
			}
		}

		uasort( $items_sorted, array( $this, 'sort_by_price' ) );

		return $items_sorted;
	}

	/**
	 * Sort callback.
	 *
	 * @param array $a A element to compare.
	 * @param array $b B element to compare.
	 */
	protected function sort_by_price( $a, $b ) {
		$price_a = WC_BOGOF_Cart::is_valid_discount( $a['data'] ) ? $a['data']->_bogof_discount->get_base_price() : $a['data']->get_price();
		$price_b = WC_BOGOF_Cart::is_valid_discount( $b['data'] ) ? $b['data']->_bogof_discount->get_base_price() : $b['data']->get_price();

		if ( $price_a < $price_b ) {
			return -1;
		} elseif ( $price_a > $price_b ) {
			return 1;
		} else {
			return 0;
		}
	}

	/**
	 * Update the quantity of free items in the cart.
	 *
	 * @param bool $add_to_cart Add free items to cart?.
	 */
	public function update_free_items_qty( $add_to_cart = true ) {

		$this->clear_totals();

		$max_qty = $this->get_max_free_quantity();

		if ( $max_qty > 0 ) {

			$cart_items = $this->get_cart_items();

			foreach ( $cart_items as $cart_item_key => $cart_item ) {
				$available_qty = WC_BOGOF_Cart::is_valid_discount( $cart_item ) ? $cart_item['quantity'] - $cart_item['data']->_bogof_discount->get_free_quantity() : $cart_item['quantity'];
				$free_qty      = $max_qty < $available_qty ? $max_qty : $available_qty;
				$max_qty      -= $free_qty;

				WC_BOGOF_Cart::set_cart_item_discount( $cart_item_key, $this->get_id(), $free_qty );

				if ( 0 >= $max_qty ) {
					break;
				}
			}
		}
	}
}
