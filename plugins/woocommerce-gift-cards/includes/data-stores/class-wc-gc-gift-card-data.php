<?php
/**
 * WC_GC_Gift_Card_Data class
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
 * Gift Card Data model class.
 *
 * @class    WC_GC_Gift_Card_Data
 * @version  1.0.0
 */
class WC_GC_Gift_Card_Data {

	/**
	 * Data array, with defaults.
	 *
	 * @var array
	 */
	protected $data = array(
		'id'              => 0,
		'is_active'       => 'on',
		'is_virtual'      => 'on',
		'code'            => '',
		'order_id'        => 0,
		'order_item_id'   => 0,
		'recipient'       => '',
		'redeemed_by'     => 0,
		'sender'          => '',
		'sender_email'    => 0,
		'template_id'     => 'default',
		'message'         => '',
		'balance'         => 0,
		'remaining'       => 0,
		'create_date'     => 0,
		'deliver_date'    => 0,
		'delivered'       => 'no',
		'expire_date'     => 0,
		'redeem_date'     => 0
	);

	/**
	 * Constructor.
	 *
	 * @param  int|object|array  $item  ID to load from the DB (optional) or already queried data.
	 */
	public function __construct( $giftcard = 0 ) {
		if ( $giftcard instanceof WC_GC_Gift_Card_Data ) {
			$this->set_all( $giftcard->get_data() );
		} elseif ( is_array( $giftcard ) ) {
			$this->set_all( $giftcard );
		} else {
			$this->read( $giftcard );
		}
	}

	/*---------------------------------------------------*/
	/*  Getters.                                         */
	/*---------------------------------------------------*/

	/**
	 * Returns all data for this object.
	 *
	 * @return array
	 */
	public function get_data() {
		return $this->data;
	}

	/**
	 * Get id.
	 *
	 * @return int
	 */
	public function get_id() {
		return absint( $this->data[ 'id' ] );
	}

	/**
	 * Get code.
	 *
	 * @return string
	 */
	public function get_code() {
		return $this->data[ 'code' ];
	}

	/**
	 * Get order ID.
	 *
	 * @return int
	 */
	public function get_order_id() {
		return absint( $this->data[ 'order_id' ] );
	}

	/**
	 * Get order item ID.
	 *
	 * @return int
	 */
	public function get_order_item_id() {
		return absint( $this->data[ 'order_item_id' ] );
	}

	/**
	 * Get recipient.
	 *
	 * @return string
	 */
	public function get_recipient() {
		return $this->data[ 'recipient' ];
	}

	/**
	 * Get redeemed by user.
	 *
	 * @return string
	 */
	public function get_redeemed_by() {
		return absint( $this->data[ 'redeemed_by' ] );
	}

	/**
	 * Get sender.
	 *
	 * @return string
	 */
	public function get_sender() {
		return $this->data[ 'sender' ];
	}

	/**
	 * Get sender email.
	 *
	 * @return string
	 */
	public function get_sender_email() {
		return $this->data[ 'sender_email' ];
	}

	/**
	 * Get message.
	 *
	 * @return string
	 */
	public function get_message() {
		return $this->data[ 'message' ];
	}


	/**
	 * Get balance.
	 *
	 * @return float
	 */
	public function get_initial_balance() {
		return (float) $this->data[ 'balance' ];
	}

	/**
	 * Get remaining balance.
	 *
	 * @return float
	 */
	public function get_balance() {
		return (float) $this->data[ 'remaining' ];
	}

	/**
	 * Get template id.
	 *
	 * @since 1.2.0
	 *
	 * @return string
	 */
	public function get_template_id() {
		return $this->data[ 'template_id' ];
	}

	/**
	 * Get create date.
	 *
	 * @return int
	 */
	public function get_date_created() {
		return absint( $this->data[ 'create_date' ] );
	}

	/**
	 * Get deliver date.
	 *
	 * @return int
	 */
	public function get_deliver_date() {
		return absint( $this->data[ 'deliver_date' ] );
	}

	/**
	 * Get expire date.
	 *
	 * @return int
	 */
	public function get_expire_date() {
		return absint( $this->data[ 'expire_date' ] );
	}

	/**
	 * Get redeem date.
	 *
	 * @return int
	 */
	public function get_date_redeemed() {
		return absint( $this->data[ 'redeem_date' ] );
	}


	/*---------------------------------------------------*/
	/*  Setters.                                         */
	/*---------------------------------------------------*/

	/**
	 * Set all data based on input array.
	 *
	 * @param  array  $data
	 */
	public function set_all( $data ) {
		foreach ( $data as $key => $value ) {
			// Fix some strange namings.
			if ( 'balance' === $key ) {
				$this->set_initial_balance( $value );
			} elseif ( 'is_active' === $key ) {
				$this->set_active( $value );
			} elseif ( 'is_virtual' === $key ) {
				$this->set_virtual( $value );
			} elseif ( 'remaining' === $key ) {
				$this->set_balance( $value );
			} elseif ( is_callable( array( $this, "set_$key" ) ) ) {
				$this->{"set_$key"}( $value );
			} else {
				$this->data[ $key ] = $value;
			}
		}
	}

	/**
	 * Set Deployment ID.
	 *
	 * @param  int
	 */
	public function set_id( $value ) {
		$this->data[ 'id' ] = absint( $value );
	}

	/**
	 * Set active.
	 *
	 * @param  string
	 * @return void
	 */
	public function set_active( $value ) {
		$this->data[ 'is_active' ] = 'on' === $value ? 'on' : 'off';
	}

	/**
	 * Set virtual.
	 *
	 * @param  string
	 * @return void
	 */
	public function set_virtual( $value ) {
		$this->data[ 'is_virtual' ] = 'on' === $value ? 'on' : 'off';
	}

	/**
	 * Set code.
	 *
	 * @param  strings
	 * @return void
	 */
	public function set_code( $value ) {
		$this->data[ 'code' ] = substr( $value, 0, 19 );
	}

	/**
	 * Set order ID.
	 *
	 * @param  int
	 * @return void
	 */
	public function set_order_id( $value ) {
		$this->data[ 'order_id' ] = absint( $value );
	}

	/**
	 * Set order item ID.
	 *
	 * @param  int
	 * @return void
	 */
	public function set_order_item_id( $value ) {
		$this->data[ 'order_item_id' ] = absint( $value );
	}

	/**
	 * Set recipient.
	 *
	 * @param  string
	 * @return void
	 */
	public function set_recipient( $value ) {
		$this->data[ 'recipient' ] = $value;
	}

	/**
	 * Set redeemed by.
	 *
	 * @param  string
	 * @return void
	 */
	public function set_redeemed_by( $value ) {
		$this->data[ 'redeemed_by' ] = absint( $value );
	}

	/**
	 * Set sender.
	 *
	 * @param  string
	 * @return void
	 */
	public function set_sender( $value ) {
		$this->data[ 'sender' ] = $value;
	}

	/**
	 * Set sender_email.
	 *
	 * @param  string
	 * @return void
	 */
	public function set_sender_email( $value ) {
		$this->data[ 'sender_email' ] = $value;
	}

	/**
	 * Set message.
	 *
	 * @param  string
	 * @return void
	 */
	public function set_message( $value ) {
		$this->data[ 'message' ] = $value;
	}

	/**
	 * Set balance.
	 *
	 * @param  float
	 * @return void
	 */
	public function set_initial_balance( $value ) {
		$this->data[ 'balance' ] = wc_format_decimal( $value );
	}

	/**
	 * Set remaining balance.
	 *
	 * @param  float
	 * @return void
	 */
	public function set_balance( $value ) {
		$this->data[ 'remaining' ] = wc_format_decimal( $value );
	}

	/**
	 * Set template id.
	 *
	 * @since 1.2.0
	 *
	 * @param  string
	 * @return void
	 */
	public function set_template_id( $value ) {
		$this->data[ 'template_id' ] = $value;
	}

	/**
	 * Set create date.
	 *
	 * @param  string
	 * @return void
	 */
	public function set_date_created( $value ) {
		$this->data[ 'create_date' ] = absint( $value );
	}

	/**
	 * Set expire date.
	 *
	 * @param  string
	 * @return void
	 */
	public function set_expire_date( $value ) {
		$this->data[ 'expire_date' ] = absint( $value );
	}

	/**
	 * Set deliver date.
	 *
	 * @param  string
	 * @return void
	 */
	public function set_deliver_date( $value ) {
		$this->data[ 'deliver_date' ] = absint( $value );
	}

	/**
	 * Set delivered status.
	 *
	 * @param  mixed
	 * @return void
	 */
	public function set_delivered( $value ) {
		$this->data[ 'delivered' ] = 'no' === $value ? 'no' : absint( $value );
	}

	/**
	 * Set redeem date.
	 *
	 * @param  string
	 * @return void
	 */
	public function set_date_redeemed( $value ) {
		$this->data[ 'redeem_date' ] = absint( $value );
	}

	/*---------------------------------------------------*/
	/*  CRUD.                                            */
	/*---------------------------------------------------*/

	/**
	 * Insert data into the database.
	 */
	private function create() {
		global $wpdb;

		$data = array(
			'code'          => $this->get_code(),
			'is_active'     => $this->is_active() ? 'on' : 'off',
			'is_virtual'    => $this->is_virtual() ? 'on' : 'off',
			'order_id'      => $this->get_order_id(),
			'order_item_id' => $this->get_order_item_id(),
			'recipient'     => $this->get_recipient(),
			'redeemed_by'   => $this->get_redeemed_by(),
			'sender'        => $this->get_sender(),
			'sender_email'  => $this->get_sender_email(),
			'message'       => $this->get_message(),
			'balance'       => $this->get_initial_balance(),
			'remaining'     => $this->get_balance(),
			'create_date'   => $this->get_date_created(),
			'deliver_date'  => $this->get_deliver_date(),
			'delivered'     => false === $this->is_delivered() ? 'no' : $this->is_delivered(),
			'expire_date'   => $this->get_expire_date(),
			'redeem_date'   => $this->get_date_redeemed()
		);

		$wpdb->insert( $wpdb->prefix . 'woocommerce_gc_cards', $data, array( '%s', '%s', '%s', '%d', '%d', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%d', '%d' ) );

		$this->set_id( $wpdb->insert_id );
	}

	/**
	 * Update data in the database.
	 */
	private function update() {
		global $wpdb;

		$data = array(
			'code'          => $this->get_code(),
			'is_active'     => $this->is_active() ? 'on' : 'off',
			'is_virtual'    => $this->is_virtual() ? 'on' : 'off',
			'order_id'      => $this->get_order_id(),
			'order_item_id' => $this->get_order_item_id(),
			'recipient'     => $this->get_recipient(),
			'redeemed_by'   => $this->get_redeemed_by(),
			'sender'        => $this->get_sender(),
			'sender_email'  => $this->get_sender_email(),
			'message'       => $this->get_message(),
			'balance'       => $this->get_initial_balance(),
			'remaining'     => $this->get_balance(),
			'create_date'   => $this->get_date_created(),
			'deliver_date'  => $this->get_deliver_date(),
			'delivered'     => false === $this->is_delivered() ? 'no' : $this->is_delivered(),
			'expire_date'   => $this->get_expire_date(),
			'redeem_date'   => $this->get_date_redeemed()
		);

		$updated = $wpdb->update( $wpdb->prefix . 'woocommerce_gc_cards', $data, array( 'id' => $this->get_id() ), array( '%s', '%s', '%s', '%d', '%d', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%d', '%d' ) );

		do_action( 'woocommerce_gc_update_gift_card', $this );

		return $updated;
	}

	/**
	 * Delete data from the database.
	 */
	public function delete() {

		if ( $this->get_id() ) {
			global $wpdb;

			do_action( 'woocommerce_gc_before_delete_gift_card', $this );

			// Delete and clean up.
			$wpdb->delete( $wpdb->prefix . 'woocommerce_gc_cards', array( 'id' => $this->get_id() ) );

			do_action( 'woocommerce_gc_delete_gift_card', $this );
		}
	}

	/**
	 * Save data to the database.
	 *
	 * @return int
	 */
	public function save() {

		$this->validate();

		if ( ! $this->get_id() ) {
			$this->create();
		} else {
			$this->update();
		}

		return $this->get_id();
	}

	/**
	 * Read from DB object using ID.
	 *
	 * @param  int $giftcard
	 * @return void
	 */
	public function read( $giftcard ) {
		global $wpdb;

		if ( is_numeric( $giftcard ) && ! empty( $giftcard ) ) {
			$data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}woocommerce_gc_cards WHERE id = %d LIMIT 1;", $giftcard ) );
		} elseif ( ! empty( $giftcard->id ) ) {
			$data = $giftcard;
		} else {
			$data = false;
		}

		if ( $data ) {
			$this->set_all( $data );
		}
	}

	/**
	 * Validates before saving for sanity.
	 */
	public function validate() {
		// ...
	}

	/*---------------------------------------------------*/
	/*  Utilities.                                       */
	/*---------------------------------------------------*/

	/**
	 * Is Gift Card active.
	 *
	 * @return bool
	 */
	public function is_active() {
		return 'on' === $this->data[ 'is_active' ];
	}

	/**
	 * Is Gift Card virtual.
	 *
	 * @return bool
	 */
	public function is_virtual() {
		return 'on' === $this->data[ 'is_virtual' ];
	}

	/**
	 * Is Gift Card redeemed.
	 *
	 * @return bool
	 */
	public function is_redeemed() {
		return 0 !== $this->get_date_redeemed() && 0 !== $this->get_redeemed_by();
	}

	/**
	 * Is Gift Card delivered.
	 *
	 * @return bool|int False or user id
	 */
	public function is_delivered() {
		return 'no' === $this->data[ 'delivered' ] ? false : absint( $this->data[ 'delivered' ] );
	}

	/**
	 * Is Gift Card expired.
	 *
	 * @return bool
	 */
	public function has_expired() {
		return 0 !== $this->get_expire_date() && $this->get_expire_date() <= time();
	}
}
