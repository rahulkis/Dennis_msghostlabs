<?php
/**
 * Buy One Get One Free rule class
 *
 * @package WC_BOGOF
 */

defined( 'ABSPATH' ) || exit;

/**
 * WC_BOGOF_Rule Class
 */
class WC_BOGOF_Rule extends WC_Data {

	/**
	 * This is the name of this object type.
	 *
	 * @var string
	 */
	protected $object_type = 'bogof_rule';

	/**
	 * Stores data.
	 *
	 * @var array
	 */
	protected $data = array(
		'title'                => '',
		'date_created'         => null,
		'date_modified'        => null,
		'enabled'              => true,
		'type'                 => '',
		'applies_to'           => '',
		'buy_product_ids'      => array(),
		'buy_category_ids'     => array(),
		'individual'           => false,
		'min_quantity'         => '',
		'action'               => '',
		'free_product_id'      => '',
		'free_product_ids'     => array(),
		'free_category_ids'    => array(),
		'free_quantity'        => '',
		'cart_limit'           => '',
		'usage_limit_per_user' => '',
		'coupon_ids'           => array(),
		'exclude_product_ids'  => array(),
		'allowed_user_roles'   => array(),
		'minimum_amount'       => '',
		'start_date'           => null,
		'end_date'             => null,
	);

	/**
	 * Rule constructor. Loads rule data.
	 *
	 * @param int|WC_BOGOF_Rule|object $data WC_BOGOF_Rule to init.
	 */
	public function __construct( $data = 0 ) {
		parent::__construct();

		if ( is_numeric( $data ) && $data > 0 ) {
			$this->set_id( $data );
		} elseif ( $data instanceof self ) {
			$this->set_id( absint( $data->get_id() ) );
		} elseif ( ! empty( $data->ID ) ) {
			$this->set_id( absint( $data->ID ) );
		} else {
			$this->set_object_read( true );
		}

		$this->data_store = WC_Data_Store::load( 'bogof-rule' );
		if ( $this->get_id() > 0 ) {
			$this->data_store->read( $this );
		}
	}

	/*
	|--------------------------------------------------------------------------
	| Getters
	|--------------------------------------------------------------------------
	|
	*/

	/**
	 * Get rule title.
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 * @return string
	 */
	public function get_title( $context = 'view' ) {
		return $this->get_prop( 'title', $context );
	}

	/**
	 * Get rule created date.
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 * @return WC_DateTime|NULL object if the date is set or null if there is no date.
	 */
	public function get_date_created( $context = 'view' ) {
		return $this->get_prop( 'date_created', $context );
	}

	/**
	 * Get rule modified date.
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 * @return WC_DateTime|NULL object if the date is set or null if there is no date.
	 */
	public function get_date_modified( $context = 'view' ) {
		return $this->get_prop( 'date_modified', $context );
	}

	/**
	 * Get enabled.
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 * @return bool
	 */
	public function get_enabled( $context = 'view' ) {
		return $this->get_prop( 'enabled', $context );
	}

	/**
	 * Returns the type.
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 * @return string
	 */
	public function get_type( $context = 'view' ) {
		return $this->get_prop( 'type', $context );
	}

	/**
	 * Returns the applies to.
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 * @return string
	 */
	public function get_applies_to( $context = 'view' ) {
		return $this->get_prop( 'applies_to', $context );
	}

	/**
	 * Returns the buy product ids.
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 * @return array
	 */
	public function get_buy_product_ids( $context = 'view' ) {
		return $this->get_prop( 'buy_product_ids', $context );
	}

	/**
	 * Returns the buy category ids.
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 * @return array
	 */
	public function get_buy_category_ids( $context = 'view' ) {
		return $this->get_prop( 'buy_category_ids', $context );
	}

	/**
	 * Get individual.
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 * @return bool
	 */
	public function get_individual( $context = 'view' ) {
		return $this->get_prop( 'individual', $context );
	}

	/**
	 * Returns the min quantity.
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 * @return string
	 */
	public function get_min_quantity( $context = 'view' ) {
		return $this->get_prop( 'min_quantity', $context );
	}

	/**
	 * Returns the action.
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 * @return string
	 */
	public function get_action( $context = 'view' ) {
		return $this->get_prop( 'action', $context );
	}

	/**
	 * Returns the free product id.
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 * @return string
	 */
	public function get_free_product_id( $context = 'view' ) {
		return $this->get_prop( 'free_product_id', $context );
	}

	/**
	 * Returns the free product ids.
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 * @return array
	 */
	public function get_free_product_ids( $context = 'view' ) {
		return $this->is_action( 'add_to_cart' ) ? array( $this->get_free_product_id() ) : $this->get_prop( 'free_product_ids', $context );
	}

	/**
	 * Returns the free category ids.
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 * @return array
	 */
	public function get_free_category_ids( $context = 'view' ) {
		return $this->get_prop( 'free_category_ids', $context );
	}

	/**
	 * Returns the free quantity.
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 * @return string
	 */
	public function get_free_quantity( $context = 'view' ) {
		return $this->get_prop( 'free_quantity', $context );
	}

	/**
	 * Returns the cart limit.
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 * @return string
	 */
	public function get_cart_limit( $context = 'view' ) {
		return $this->get_prop( 'cart_limit', $context );
	}

	/**
	 * Returns the usage limit per user.
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 * @return string
	 */
	public function get_usage_limit_per_user( $context = 'view' ) {
		return $this->get_prop( 'usage_limit_per_user', $context );
	}

	/**
	 * Returns the coupon ids.
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 * @return array
	 */
	public function get_coupon_ids( $context = 'view' ) {
		return $this->get_prop( 'coupon_ids', $context );
	}

	/**
	 * Returns the coupon codes of a rule.
	 *
	 * @return array
	 */
	public function get_coupon_codes() {
		return $this->data_store->get_coupon_codes( $this );
	}

	/**
	 * Returns the exclude product ids.
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 * @return array
	 */
	public function get_exclude_product_ids( $context = 'view' ) {
		return $this->get_prop( 'exclude_product_ids', $context );
	}

	/**
	 * Returns the allowed user roles.
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 * @return array
	 */
	public function get_allowed_user_roles( $context = 'view' ) {
		return $this->get_prop( 'allowed_user_roles', $context );
	}

	/**
	 * Get minimum spend amount.
	 *
	 * @since  2.1.0
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return float
	 */
	public function get_minimum_amount( $context = 'view' ) {
		return $this->get_prop( 'minimum_amount', $context );
	}

	/**
	 * Set the minimum spend amount.
	 *
	 * @since 2.1.0
	 * @param float $amount Minium amount.
	 */
	public function set_minimum_amount( $amount ) {
		$this->set_prop( 'minimum_amount', wc_format_decimal( $amount ) );
	}

	/**
	 * Returns the start date.
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 * @return WC_DateTime|NULL
	 */
	public function get_start_date( $context = 'view' ) {
		return $this->get_prop( 'start_date', $context );
	}

	/**
	 * Returns the end date.
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 * @return WC_DateTime|NULL
	 */
	public function get_end_date( $context = 'view' ) {
		return $this->get_prop( 'end_date', $context );
	}


	/*
	|--------------------------------------------------------------------------
	| Setters
	|--------------------------------------------------------------------------
	|
	*/

	/**
	 * Set rule title.
	 *
	 * @param string $title Rule name.
	 */
	public function set_title( $title ) {
		$this->set_prop( 'title', $title );
	}

	/**
	 * Set date_created
	 *
	 * @param string|integer|null $date UTC timestamp, or ISO 8601 DateTime. If the DateTime string has no timezone or offset, WordPress site timezone will be assumed. Null if there is no date.
	 */
	public function set_date_created( $date ) {
		$this->set_date_prop( 'date_created', $date );
	}

	/**
	 * Set date_modified
	 *
	 * @param string|integer|null $date UTC timestamp, or ISO 8601 DateTime. If the DateTime string has no timezone or offset, WordPress site timezone will be assumed. Null if there is no date.
	 */
	public function set_date_modified( $date ) {
		$this->set_date_prop( 'date_modified', $date );
	}

	/**
	 * Set if the rule is enabled.
	 *
	 * @param bool|string $enabled Whether product is virtual or not.
	 */
	public function set_enabled( $enabled ) {
		$this->set_prop( 'enabled', wc_string_to_bool( $enabled ) );
	}

	/**
	 * Set the type.
	 *
	 * @param string $type Rule type.
	 */
	public function set_type( $type ) {
		$this->set_prop( 'type', $type );
	}

	/**
	 * Set the applies to.
	 *
	 * @param string $applies_to Applies to.
	 */
	public function set_applies_to( $applies_to ) {
		$this->set_prop( 'applies_to', $applies_to );
	}

	/**
	 * Set the buy product ids.
	 *
	 * @param array $buy_product_ids The product that the rule applies to.
	 */
	public function set_buy_product_ids( $buy_product_ids ) {
		$this->set_prop( 'buy_product_ids', array_filter( array_map( 'intval', (array) $buy_product_ids ) ) );
	}

	/**
	 * Set the buy category ids.
	 *
	 * @param array $buy_category_ids The product that the rule applies to.
	 */
	public function set_buy_category_ids( $buy_category_ids ) {
		$this->set_prop( 'buy_category_ids', array_filter( (array) $buy_category_ids ) );
	}

	/**
	 * The rule the rule should be applied individually to each product of the category.
	 *
	 * @param bool|string $individual Whether product is virtual or not.
	 */
	public function set_individual( $individual ) {
		$this->set_prop( 'individual', wc_string_to_bool( $individual ) );
	}

	/**
	 * Set the min quantity.
	 *
	 * @param int|string $min_quantity Min quantity the customer must buy to get the gift.
	 */
	public function set_min_quantity( $min_quantity ) {
		$this->set_prop( 'min_quantity', '' === $min_quantity ? '' : absint( $min_quantity ) );
	}

	/**
	 * Set the action.
	 *
	 * @param string $action The action trigger after the customer buys the min qty.
	 */
	public function set_action( $action ) {
		$this->set_prop( 'action', $action );
	}

	/**
	 * Set the free product id.
	 *
	 * @param int|string $free_product_id Product to add to cart.
	 */
	public function set_free_product_id( $free_product_id ) {
		$this->set_prop( 'free_product_id', '' === $free_product_id ? '' : absint( $free_product_id ) );
	}

	/**
	 * Set the free product ids.
	 *
	 * @param array $free_product_ids List of products from the customer can choosee for free.
	 */
	public function set_free_product_ids( $free_product_ids ) {
		$this->set_prop( 'free_product_ids', array_filter( array_map( 'intval', (array) $free_product_ids ) ) );
	}

	/**
	 * Set the free category ids.
	 *
	 * @param array $free_category_ids List of categories from the customer can choosee for free.
	 */
	public function set_free_category_ids( $free_category_ids ) {
		$this->set_prop( 'free_category_ids', array_filter( (array) $free_category_ids ) );
	}

	/**
	 * Set the free quantity.
	 *
	 * @param int|string $free_quantity Free qty.
	 */
	public function set_free_quantity( $free_quantity ) {
		$this->set_prop( 'free_quantity', '' === $free_quantity ? '' : absint( $free_quantity ) );
	}

	/**
	 * Set the cart limit.
	 *
	 * @param int|string $cart_limit Free items limit in the cart.
	 */
	public function set_cart_limit( $cart_limit ) {
		$this->set_prop( 'cart_limit', '' === $cart_limit ? '' : absint( $cart_limit ) );
	}

	/**
	 * Set the usage limit per user.
	 *
	 * @param string $usage_limit_per_user Limit of free items the user can get.
	 */
	public function set_usage_limit_per_user( $usage_limit_per_user ) {
		$this->set_prop( 'usage_limit_per_user', '' === $usage_limit_per_user ? '' : absint( $usage_limit_per_user ) );
	}

	/**
	 * Set the coupon ids.
	 *
	 * @param array $coupon_ids Coupons that enable the rule.
	 */
	public function set_coupon_ids( $coupon_ids ) {
		$this->set_prop( 'coupon_ids', array_filter( array_map( 'intval', (array) $coupon_ids ) ) );
	}

	/**
	 * Set the exclude product ids.
	 *
	 * @param array $exclude_product_ids Products that the rule will not be applied to.
	 */
	public function set_exclude_product_ids( $exclude_product_ids ) {
		$this->set_prop( 'exclude_product_ids', array_filter( array_map( 'intval', (array) $exclude_product_ids ) ) );
	}

	/**
	 * Set the allowed user roles.
	 *
	 * @param array $allowed_user_roles User roles that the rule will be available.
	 */
	public function set_allowed_user_roles( $allowed_user_roles ) {
		$this->set_prop( 'allowed_user_roles', array_filter( (array) $allowed_user_roles ) );
	}

	/**
	 * Set the start date.
	 *
	 * @param string|integer|null $date UTC timestamp, or ISO 8601 DateTime. The deal will begin at 00:00 of this date.
	 */
	public function set_start_date( $date ) {
		$this->set_date_prop( 'start_date', $date );
	}

	/**
	 * Set the end date.
	 *
	 * @param string|integer|null $date UTC timestamp, or ISO 8601 DateTime. The deal will end at 23:59 of this date.
	 */
	public function set_end_date( $date ) {
		$this->set_date_prop( 'end_date', $date );
	}

	/*
	|--------------------------------------------------------------------------
	| No CRUD Getters
	|--------------------------------------------------------------------------
	*/

	/**
	 * Returns the number of times a user used a rule.
	 *
	 * @param string|array $used_by Either user ID or billing email.
	 */
	public function get_used_by_count( $used_by ) {
		return $this->data_store->get_used_by_count( $used_by, $this );
	}

	/*
	|--------------------------------------------------------------------------
	| Conditionals
	|--------------------------------------------------------------------------
	*/

	/**
	 * Checks if a rule enabled.
	 *
	 * @return bool
	 */
	public function is_enabled() {
		$enabled = $this->get_enabled();
		if ( $enabled && $this->get_end_date() && $this->get_end_date()->getTimestamp() < current_time( 'timestamp', true ) ) {
			$enabled = false;
		}
		if ( $enabled && $this->get_start_date() && $this->get_start_date()->getTimestamp() > current_time( 'timestamp', true ) ) {
			$enabled = false;
		}
		return $enabled;
	}

	/**
	 * Checks the rule action.
	 *
	 * @param string $action Action to check.
	 * @return bool
	 */
	public function is_action( $action ) {
		$is_action = false;
		switch ( $action ) {
			case 'add_to_cart':
				$is_action = 'buy_a_get_a' === $this->get_type() || $action === $this->get_action();
				break;
			default:
				$is_action = $action === $this->get_action();
				break;
		}
		return $is_action;
	}

	/**
	 * Should the rule be applied individually?
	 *
	 * @return bool
	 */
	public function is_individual() {
		return 'buy_a_get_a' === $this->get_type() || ( 'category' === $this->get_applies_to() && $this->get_individual() );
	}

	/**
	 * Is a free product?
	 *
	 * @param int $product_id Product ID.
	 * @return bool
	 */
	public function is_free_product( $product_id ) {
		return $this->data_store->is_free_product( $product_id, $this );
	}

	/**
	 * Is a buy product?
	 *
	 * @param int $product_id Product ID.
	 * @return bool
	 */
	public function is_buy_product( $product_id ) {
		return $this->data_store->is_buy_product( $product_id, $this );
	}

	/**
	 * Is a exclude product?
	 *
	 * @since 2.0.5
	 * @param int $product_id Product ID.
	 * @return bool
	 */
	public function is_exclude_product( $product_id ) {
		return wc_bogof_in_array_intersect( array( $product_id, wp_get_post_parent_id( $product_id ) ), $this->get_exclude_product_ids() );
	}

	/**
	 * Is the role available for the current user.
	 *
	 * @return bool
	 */
	public function is_available_for_current_user_role() {
		$is_available = true;

		// Check user roles.
		$roles = $this->get_allowed_user_roles();
		if ( ! empty( $roles ) ) {
			$is_available = wc_bogof_current_user_has_role( $roles );
		}
		return $is_available;
	}

	/*
	|--------------------------------------------------------------------------
	| Other Actions
	|--------------------------------------------------------------------------
	*/

	/**
	 * Increase usage count for current rule.
	 *
	 * @param WC_Order $order Order object.
	 */
	public function increase_usage_count( $order ) {
		$this->data_store->increase_usage_count( $this, $order );
	}
}
