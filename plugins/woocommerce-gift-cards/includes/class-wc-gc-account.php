<?php
/**
 * WC_GC_Account class
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
 * Account class.
 *
 * @class    WC_GC_Account
 * @version  1.2.2
 */
class WC_GC_Account {

	/**
	 * Hook.
	 */
	public function __construct() {

		if ( ! wc_gc_is_redeeming_enabled() ) {
			return;
		}

		// Add menu item.
		add_action( 'woocommerce_account_menu_items', array( $this, 'add_navigation_item' ) );

		// Add endpoint setting.
		add_action( 'woocommerce_settings_pages', array( $this, 'add_endpoint_setting' ) );

		// Form handler.
		add_action( 'template_redirect', array( $this, 'maybe_process_email_redeem' ) );
		add_action( 'template_redirect', array( $this, 'process_redeem' ) );

		// Invalidate session.
		add_action( 'woocommerce_gc_gift_card_redeemed', array( $this, 'maybe_clear_caches' ) );
		add_action( 'woocommerce_gc_gift_card_debited', array( $this, 'maybe_clear_caches' ) );
		add_action( 'woocommerce_gc_gift_card_credited', array( $this, 'maybe_clear_caches' ) );
		add_action( 'woocommerce_gc_delete_gift_card', array( $this, 'maybe_clear_caches' ) );

		// Page.
		add_action( 'woocommerce_account_giftcards_endpoint', array( $this, 'render_page' )  );
		add_action( 'woocommerce_endpoint_giftcards_title', array( $this, 'get_endpoint_title' ), 10 , 2  );
		add_action( 'woocommerce_get_query_vars', array( $this, 'add_query_var' ) );
	}

	/**
	 * Whether or not a customer wants to use the balance.
	 *
	 * @return bool
	 */
	public function use_balance() {
		$use = WC()->session->get( '_wc_gc_use_balance', true );
		return $use;
	}

	/**
	 * Set the balance usage to on/off.
	 *
	 * @param  bool  $use
	 * @return void
	 */
	public function set_balance_usage( $use ) {
		WC()->session->set( '_wc_gc_use_balance', (bool) $use );
	}

	/**
	 * Fetches all active giftcards for customer.
	 *
	 * @param  int  $user_id
	 * @return array
	 */
	public function get_active_giftcards( $user_id = 0 ) {

		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();

			if ( ! $user_id ) {
				return array();
			}
		}

		$query_args = array(
			'return'                => 'objects',
			'redeemed_by'           => $user_id,
			'has_remaining_balance' => true,
			'is_active'             => 'on',
			'has_expired'           => false
		);

		$giftcards = WC_GC()->db->giftcards->query( $query_args );
		return $giftcards;
	}

	/**
	 * Try to fetch from session active giftcards. If not, save for later use.
	 *
	 * @return array
	 */
	public function get_active_giftcards_from_session() {

		$cache_key        = WC_Cache_Helper::get_transient_version( 'account_giftcards' ) . '_wc_gc_active_giftcards';
		$active_giftcards = WC()->session->get( $cache_key );

		if ( is_null( $active_giftcards ) ) {
			$active_giftcards = $this->get_active_giftcards();
			// Save for later.
			WC()->session->set( $cache_key, $active_giftcards );
		}

		return $active_giftcards;
	}

	/**
	 * Clear caches.
	 *
	 * @return void
	 */
	public function clear_caches() {
		$cache_key = WC_Cache_Helper::get_transient_version( 'account_giftcards' ) . '_wc_gc_active_giftcards';
		WC()->session->set( $cache_key, null );
	}

	/**
	 * Clear caches.
	 *
	 * @return void
	 */
	public function maybe_clear_caches() {
		if ( ! is_admin() ) {
			$this->clear_caches();
		} else {
			WC_Cache_Helper::get_transient_version( 'account_giftcards', true );
			WC_Cache_Helper::get_transient_version( 'applied_giftcards', true );
		}
	}

	/**
	 * Whether or not the customer has balance.
	 *
	 * @return bool
	 */
	public function has_balance() {
		return $this->get_balance() > 0;
	}

	/**
	 * Retrieve balance for customer.
	 *
	 * @param  int  $user_id
	 * @param  array|null  $giftcards
	 * @return float
	 */
	public function get_balance( $user_id = 0, $giftcards = null ) {

		$balance = 0.0;
		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return $balance;
		}

		if ( is_null( $giftcards ) ) {
			$giftcards = $this->get_active_giftcards_from_session();
		}

		foreach ( $giftcards as $giftcard_data ) {
			$balance += (float) $giftcard_data->get_balance();
		}

		return $balance;
	}

	/**
	 * Proccess the email link redeeming if any.
	 *
	 * @since 1.1.0
	 *
	 * @return void
	 */
	public function maybe_process_email_redeem() {

		if ( ! empty( $_GET[ 'do_email_redeem' ] ) ) {

			if ( ! is_user_logged_in() ) {
				return;
			}

			$hash     = ! empty( $_GET[ 'do_email_redeem' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 'do_email_redeem' ] ) ) : '';
			$base_url = remove_query_arg( 'do_email_redeem' );

			if ( ! $hash ) {
				wc_add_notice( __( 'Please enter a valid Gift Card code.', 'woocommerce-gift-cards' ), 'error' );
				wp_safe_redirect( $base_url );
				exit;
			}

			// Try to make brute-force attacks inefficient.
			sleep( 2 );

			$giftcard_data = WC_GC()->db->giftcards->get_by_hash( $hash );
			if ( $giftcard_data ) {

				$gc = new WC_GC_Gift_Card( $giftcard_data );

				try {

					$gc->redeem( get_current_user_id() );
					// Re-init cart giftcards.
					WC_GC()->cart->destroy_cart_session();
					wc_add_notice( __( 'The Gift Card has been added to your account.', 'woocommerce-gift-cards' ) );

				} catch ( Exception $e ) {
					wc_add_notice( $e->getMessage(), 'error' );
				}

			} else {
				wc_add_notice( __( 'Invalid Gift Card code.', 'woocommerce-gift-cards' ), 'error' );
			}

			wp_safe_redirect( $base_url );
			exit;
		}
	}

	/**
	 * Proccess the FE redeem form.
	 *
	 * @param  array  $items
	 * @return array
	 */
	public function process_redeem() {

		if ( ! empty( $_POST ) && ! empty( $_POST[ 'wc_gc_redeem_save' ] ) ) {

			if ( ! isset( $_REQUEST[ '_wpnonce' ] ) || ! wp_verify_nonce( wc_clean( $_REQUEST[ '_wpnonce' ] ), 'customer_redeems_gift_card' ) ) {
				wc_add_notice( __( 'We were unable to redeem your Gift Card. Please try again later, or get in touch with us for assistance.', 'woocommerce-gift-cards' ), 'error' );
				wp_safe_redirect( add_query_arg() );
				exit;
			}

			$code = ! empty( $_POST[ 'wc_gc_redeem_code' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'wc_gc_redeem_code' ] ) ) : '';

			if ( ! $code ) {
				wc_add_notice( __( 'Please enter a valid Gift Card code.', 'woocommerce-gift-cards' ), 'error' );
				wp_safe_redirect( add_query_arg( array() ) );
				exit;
			} elseif ( strlen( $code ) !== 19 ) {
				wc_add_notice( __( 'Gift Card code must be 19 characters.', 'woocommerce-gift-cards' ), 'error' );
				wp_safe_redirect( add_query_arg( array() ) );
				exit;
			}

			$gc_results = WC_GC()->db->giftcards->query( array(
				'return' => 'objects',
				'code'   => $code,
				'limit'  => 1
			) );

			if ( count( $gc_results ) > 0 ) {

				$gc_data = array_shift( $gc_results );
				$gc      = new WC_GC_Gift_Card( $gc_data );

				try {

					$gc->redeem( get_current_user_id() );
					// Re-init cart giftcards.
					WC_GC()->cart->destroy_cart_session();
					wc_add_notice( __( 'The Gift Card has been added to your account.', 'woocommerce-gift-cards' ) );

				} catch ( Exception $e ) {
					wc_add_notice( $e->getMessage(), 'error' );
				}

			} else {
				wc_add_notice( __( 'Invalid Gift Card code.', 'woocommerce-gift-cards' ), 'error' );
			}

			wp_safe_redirect( add_query_arg( array() ) );
			exit;
		}
	}


	/**
	 * Add Gift Cards navigation item.
	 *
	 * @param  array  $items
	 * @return array
	 */
	public function add_navigation_item( $items ) {

		$after_menu_position = 3;
		$giftcards_menu_item = array( 'giftcards' => __( 'Gift Cards', 'woocommerce-gift-cards' ) );
		$items               = array_slice( $items, 0, $after_menu_position, true ) + $giftcards_menu_item + array_slice( $items, $after_menu_position, count( $items ) - $after_menu_position, true );

		return $items;
	}

	/**
	 * Add Gift Cards navigation item.
	 *
	 * @since 1.2.2
	 *
	 * @param  array  $settings
	 * @return array
	 */
	public function add_endpoint_setting( $settings ) {

		// Find where is the id "account_endpoint_options" with type "sectionend".
		$counted_index = 0; // Settings array is not entirely zero-based.
		foreach ( $settings as $index => $setting ) {
			if ( isset( $setting[ 'type' ], $setting[ 'id' ] ) && 'sectionend' === $setting[ 'type' ] && 'account_endpoint_options' === $setting[ 'id' ] ) {
				$end_index = $index;
				break;
			}

			// Increase counted index.
			$counted_index++;
		}

		if ( isset( $end_index ) ) {

			$setting = array(
				'title'    => __( 'Gift cards', 'woocommerce-gift-cards' ),
				'desc'     => __( 'Endpoint for the "My account &rarr; Gift Cards" page.', 'woocommerce-gift-cards' ),
				'id'       => 'woocommerce_checkout_gc_giftcards_endpoint',
				'type'     => 'text',
				'default'  => 'giftcards',
				'desc_tip' => true,
			);

			$settings = array_slice( $settings, 0, $counted_index, true ) + array( 'giftcards' => $setting ) + array_slice( $settings, $counted_index, count( $settings ) - $counted_index, true );
		}

		return $settings;
	}

	/**
	 * Render page html.
	 *
	 * @param  int  $current_page (Optional)
	 * @return void
	 */
	public function render_page( $current_page = 1 ) {

		// Page.
		$current_page = ! empty( $current_page ) ? absint( $current_page ) : 1;
		$per_page     = 5;
		// Giftcards.
		$giftcards     = $this->get_active_giftcards_from_session();
		$balance       = $this->get_balance();
		$has_giftcards = count( $giftcards ) > 0 ? true : false;

		// Activities.
		$query_args_activity = array(
				'return'   => 'objects',
				'type'     => array( 'redeemed', 'used', 'refunded' ),
				'user_id'  => get_current_user_id(),
				'order_by' => array( 'date' => 'desc' ),
				'limit'    => $per_page,
				'offset'   => ( $current_page - 1 ) * $per_page
			);

		$activities     = WC_GC()->db->activity->query( $query_args_activity );
		$has_activities = count( $activities ) > 0 ? true : false;

		// Count total items.
		$query_args_activity[ 'count' ] = true;
		unset( $query_args_activity[ 'limit' ] );
		unset( $query_args_activity[ 'offset' ] );
		$total_items = WC_GC()->db->activity->query( $query_args_activity );
		$total_pages = ceil( $total_items / $per_page );

		wc_get_template(
			'myaccount/giftcards.php',
			array(
				'current_page'   => $current_page,
				'total_pages'    => $total_pages,
				'has_giftcards'  => $has_giftcards,
				'giftcards'      => $giftcards,
				'activities'     => $activities,
				'has_activities' => $has_activities,
				'balance'        => $balance
			),
			false,
			WC_GC()->get_plugin_path() . '/templates/'
		);
	}

	/**
	 * Get the endpoint page title.
	 *
	 * @param  string  $title
	 * @param  string  $endpoint
	 * @return string
	 */
	public function get_endpoint_title( $title, $endpoint ) {

		if ( 'giftcards' === $endpoint ) {
			$title = __( 'Gift Cards', 'woocommerce-gift-cards' );
		}

		return $title;
	}

	/**
	 * Add endpoint slug as query var.
	 *
	 * @param  array  $query_vars
	 * @return array
	 */
	public function add_query_var( $query_vars ) {
		$query_vars[ 'giftcards' ] = get_option( 'woocommerce_checkout_gc_giftcards_endpoint', 'giftcards' );
		return $query_vars;
	}
}
