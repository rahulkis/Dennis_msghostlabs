<?php
/**
 * Buy One Get One Free Cart Rule Indivial. Handles BOGO rule actions.
 *
 * @package WC_BOGOF
 */

defined( 'ABSPATH' ) || exit;

/**
 * WC_BOGOF_Cart_Rule_Individual Class
 */
class WC_BOGOF_Cart_Rule_Individual extends WC_BOGOF_Cart_Rule {

	/**
	 * BOGOF rule.
	 *
	 * @var WC_BOGOF_Rule
	 */
	protected $rule;

	/**
	 * Product ID.
	 *
	 * @var int
	 */
	protected $product_id;

	/**
	 * Constructor.
	 *
	 * @param WC_BOGOF_Rule $rule BOGOF rule.
	 * @param int           $product_id Product ID.
	 */
	public function __construct( $rule, $product_id ) {
		$this->product_id = $product_id;
		parent::__construct( $rule );
	}

	/**
	 * Does the cart item match with the rule?
	 *
	 * @param array $cart_item Cart item.
	 * @return bool
	 */
	protected function cart_item_match( $cart_item ) {
		return parent::cart_item_match( $cart_item ) && $cart_item['data']->get_id() == $this->product_id; // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
	}

	/**
	 * Return the cart rule ID.
	 */
	public function get_id() {
		return $this->rule->get_id() . '-' . $this->product_id;
	}
}
