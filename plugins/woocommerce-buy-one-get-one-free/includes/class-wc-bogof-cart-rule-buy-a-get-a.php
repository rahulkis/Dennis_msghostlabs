<?php
/**
 * Buy One Get One Free Cart Rule Buy A Get A. Handles BOGO rule actions.
 *
 * @package WC_BOGOF
 */

defined( 'ABSPATH' ) || exit;

/**
 * WC_BOGOF_Cart_Rule_Buy_A_Get_A Class
 */
class WC_BOGOF_Cart_Rule_Buy_A_Get_A extends WC_BOGOF_Cart_Rule_Individual {

	/**
	 * Unique ID to handle the add to cart action.
	 *
	 * @var string
	 */
	protected $uniqid;

	/**
	 * Constructor.
	 *
	 * @param WC_BOGOF_Rule $rule BOGOF rule.
	 * @param int           $product_id Product ID.
	 */
	public function __construct( $rule, $product_id ) {
		parent::__construct( $rule, $product_id );
		$this->support_choose_your_gift = false;
	}

	/**
	 * Add the free product to the cart.
	 *
	 * @param int $qty The quantity of the item to add.
	 */
	protected function add_free_product_to_cart( $qty ) {
		$cart_item_data = false;
		$cart_item_key  = false;

		foreach ( WC()->cart->get_cart_contents() as $cart_item_key => $cart_item ) {
			if ( $this->cart_item_match( $cart_item ) ) {
				$cart_item_data = $cart_item;
				break;
			}
		}

		if ( false !== $cart_item_data ) {

			$this->uniqid = uniqid( $this->get_id() );

			$cart_item_data['product_id']         = isset( $cart_item_data['product_id'] ) ? $cart_item_data['product_id'] : $this->product_id;
			$cart_item_data['wc_bogof_cart_rule'] = array( $this->uniqid );

			$cart_item_key = WC_BOGOF_Cart::add_to_cart_from_item( $cart_item_data, $qty );
		}
		return $cart_item_key;
	}

	/**
	 * Update the cart item data.
	 *
	 * @param array $cart_item_data Cart item data.
	 * @param int   $product_id The product ID.
	 * @param int   $variation_id The variation ID.
	 */
	public function add_cart_item( $cart_item_data, $product_id, $variation_id ) {

		if ( WC_BOGOF_Cart::is_free_item( $cart_item_data ) || empty( $cart_item_data['wc_bogof_cart_rule'] ) || ! is_array( $cart_item_data['wc_bogof_cart_rule'] ) || ! in_array( $this->uniqid, $cart_item_data['wc_bogof_cart_rule'], true ) ) {
			return $cart_item_data;
		}

		$product_id = $variation_id ? $variation_id : $product_id;

		if ( $product_id === $this->product_id ) {
			// Set as a free item.
			$cart_item_data = WC_BOGOF_Cart::set_cart_item_free( $cart_item_data, $this->get_id() );
		}
		return $cart_item_data;
	}


}
