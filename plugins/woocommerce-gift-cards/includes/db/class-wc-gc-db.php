<?php
/**
 * WC_GC_DB class
 *
 * @author   SomewhereWarm <info@somewherewarm.com>
 * @package  WooCommerce Gift Cards
 * @since    1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DB API class.
 */
class WC_GC_DB {

	/**
	 * A reference to the DB Model - @see WC_GC_Gift_Cards_DB.
	 *
	 * @var WC_GC_Gift_Cards_DB
	 */
	public $giftcards;

	/**
	 * A reference to the DB Model - @see WC_GC_Activity_DB.
	 *
	 * @var WC_GC_Activity_DB
	 */
	public $activity;

	/**
	 * Cloning is forbidden.
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Foul!', 'woocommerce-gift-cards' ), '1.0.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Foul!', 'woocommerce-gift-cards' ), '1.0.0' );
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Attach DB Models to public properties.
		$this->giftcards = new WC_GC_Gift_Cards_DB();
		$this->activity  = new WC_GC_Activity_DB();
	}
}
