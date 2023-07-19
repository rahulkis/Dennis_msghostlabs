<?php
/**
 * WC_GC_Cart class
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
 * WC_GC_Cart class.
 *
 * @version 1.5.0
 */
class WC_GC_Cart {

	/**
	 * Keep track of form notices.
	 *
	 * @var array
	 */
	private $notices;

	/**
	 * Applied Gift Cards.
	 *
	 * @var array
	 */
	private $giftcards;

	/**
	 * Keep track of various totals.
	 *
	 * @var array
	 */
	private $totals;

	/**
	 * Constructor for the cart class. Loads options and hooks in the init method.
	 */
	public function __construct() {
		add_action( 'template_redirect', array( $this, 'maybe_process_email_session' ) );

		// Alter the Cart total to include Gift Cards.
		add_action( 'woocommerce_after_calculate_totals', array( $this, 'after_calculate_totals' ), 1000 );
		add_action( 'woocommerce_cart_emptied', array( $this, 'destroy_cart_session' ) );

		// Print Gift Card related table.
		add_action( 'woocommerce_cart_totals_before_order_total', array( $this, 'print_gift_cards' ) );
		add_action( 'woocommerce_review_order_before_order_total', array( $this, 'print_gift_cards' ) );
		add_action( 'woocommerce_checkout_update_order_review', array( $this, 'update_order_review' ) );

		// Add inline cart form.
		add_action( 'woocommerce_proceed_to_checkout', array( $this, 'display_form' ), 9 );
		add_action( 'woocommerce_review_order_before_submit', array( $this, 'display_form' ), 9 );

		// Init runtime cache.
		$this->notices   = array();
		$this->giftcards = array(
			'applied' => array(
				'giftcards'    => array(),
				'total_amount' => 0.0
			),
			'account' => array(
				'giftcards'    => array(),
				'total_amount' => 0.0
			)
		);
		$this->totals    = array(
			'cart_total'        => 0,
			'remaining_total'   => 0,
			'total_for_balance' => 0,
			'available_total'   => 0
		);
	}

	/**
	 * Sets whether or not to use balance through the Checkout context.
	 *
	 * @param  string $post_data
	 * @return void
	 */
	public function update_order_review( $post_data ) {

		if ( ! wc_gc_is_ui_disabled() ) {
			return;
		}

		parse_str( $post_data, $post );

		// Use balance?
		$use = isset( $post[ 'use_gift_card_balance' ] ) && 'on' === $post[ 'use_gift_card_balance' ] ? true : false;
		if ( WC_GC()->account->use_balance() !== $use ) {
			WC_GC()->account->set_balance_usage( $use );
		}

		// Apply Gift Card via form?
		if ( ! empty( $post[ 'wc_gc_cart_code' ] ) ) {
			$this->process_gift_card_cart_form( $post );
		}

		// Remove Gift Card via checkout.
		if ( ! empty( $post[ 'wc_gc_cart_remove_giftcards' ] ) ) {

			$remove_id = absint( $post[ 'wc_gc_cart_remove_giftcards' ] );
			WC_GC()->giftcards->remove_giftcard_from_session( $remove_id );
		}

	}

	/**
	 * Calculate totals using active Gift Cards.
	 *
	 * @param  WC_Cart $cart
	 * @return void
	 */
	public function after_calculate_totals( $cart ) {

		if ( ! wc_gc_is_ui_disabled() ) {
			return;
		}

		if ( ! WC()->session->has_session() ) {
			return;
		}

		if ( property_exists( $cart, 'recurring_cart_key' ) ) {
			return;
		}

		if ( WC_GC_Compatibility::has_cart_totals_loop() ) {
			return;
		}

		if ( ! (bool) apply_filters( 'woocommerce_gc_cart_needs_calculation', true ) ) {
			return;
		}

		// Reset session.
		WC()->session->set( '_wc_gc_giftcards', null );

		// If Giftcards exists, quit.
		if ( $this->cart_contains_gift_card() ) {
			return;
		}

		$this->totals[ 'cart_total' ]      = $cart->get_total( 'edit' );
		$this->totals[ 'remaining_total' ] = $this->totals[ 'cart_total' ];

		// Giftcards via form?
		$applied_giftcards = WC_GC()->giftcards->get_applied_giftcards_from_session();
		if ( $applied_giftcards ) {

			$this->giftcards[ 'applied' ]      = WC_GC()->giftcards->cover_balance( $this->totals[ 'remaining_total' ], $applied_giftcards );
			$this->totals[ 'remaining_total' ] = $this->totals[ 'remaining_total' ] - (float) $this->giftcards[ 'applied' ][ 'total_amount' ];
		}

		// Sanity.
		$this->totals[ 'remaining_total' ] = max( 0, $this->totals[ 'remaining_total' ] );
		// Cache the remaining total to be covered by account balance.
		$this->totals[ 'total_for_balance' ] = $this->totals[ 'remaining_total' ];

		// Account balance?
		if ( wc_gc_is_redeeming_enabled() && WC_GC()->account->use_balance() ) {

			$account_giftcards = WC_GC()->account->get_active_giftcards_from_session();
			if ( $account_giftcards ) {

				$this->giftcards[ 'account' ]      = WC_GC()->giftcards->cover_balance( $this->totals[ 'remaining_total' ], $account_giftcards );
				$this->totals[ 'remaining_total' ] = $this->totals[ 'remaining_total' ] - (float) $this->giftcards[ 'account' ][ 'total_amount' ];
			}
		}

		// Change the Cart total. Taxes already included in the Gift Card amount.
		$cart->set_total( max( 0, $this->totals[ 'remaining_total' ] ) );

		// Calculate available amount.
		$this->totals[ 'available_total' ] = min( $this->totals[ 'total_for_balance' ], WC_GC()->account->get_balance() );
		// Cache calculated giftcards.
		WC()->session->set( '_wc_gc_giftcards', array_merge( $this->giftcards[ 'applied' ][ 'giftcards' ], $this->giftcards[ 'account' ][ 'giftcards' ] ) );
	}

	/**
	 * Remove applied GC from session.
	 *
	 * @return void
	 */
	public function destroy_cart_session() {
		WC()->session->set( WC_Cache_Helper::get_transient_version( 'applied_giftcards' ) . '_wc_gc_applied_giftcards', null );
		// Reset session.
		WC()->session->set( '_wc_gc_giftcards', null );
	}

	/**
	 * Print Gift Card table rows.
	 *
	 * @return void
	 */
	public function print_gift_cards() {

		if ( ! wc_gc_is_ui_disabled() ) {
			return;
		}

		wc_get_template(
			'cart/cart-gift-cards.php',
			array(
				'giftcards'        => $this->giftcards,
				'totals'           => $this->totals,
				'balance'          => WC_GC()->account->get_balance(),
				'use_balance'      => WC_GC()->account->use_balance(),
				'has_balance'      => WC_GC()->account->has_balance()
			),
			false,
			WC_GC()->get_plugin_path() . '/templates/'
		);
	}

	/**
	 * Display form to add gift card.
	 *
	 * @return void
	 */
	public function display_form() {

		if ( ! wc_gc_is_ui_disabled() ) {
			return;
		}

		if ( $this->cart_contains_gift_card() ) {
			return;
		}

		$this->display_notices();

		// Load template.
		wc_get_template(
			'cart/apply-gift-card-form.php',
			apply_filters( 'woocommerce_gc_apply_gift_card_form_template_args', array(), $this ),
			false,
			WC_GC()->get_plugin_path() . '/templates/'
		);
	}

	/**
	 * Display gift card related notices.
	 *
	 * @since  1.3.5
	 *
	 * @return void
	 */
	public function display_notices() {

		if ( ! empty( $this->notices ) ) {
			foreach ( $this->notices as $notice ) {
				if ( empty( $notice[ 'type' ] ) ) {
					$notice[ 'type' ] = 'message';
				}
				echo '<div class="woocommerce-' . esc_attr( $notice[ 'type' ] ) . '">' . esc_html( $notice[ 'text' ] ) . '</div>';
			}
		}
	}

	/**
	 * Check if cart contains giftcards.
	 *
	 * @return bool
	 */
	public function cart_contains_gift_card() {

		foreach ( WC()->cart->cart_contents as $cart_item_key => $cart_item ) {
			if ( is_a( $cart_item[ 'data' ], 'WC_Product' ) && WC_GC_Gift_Card_Product::is_gift_card( $cart_item[ 'data' ] ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Process front-end Gift Card cart form.
	 *
	 * @param  array $args
	 * @return void
	 */
	public function process_gift_card_cart_form( $args ) {

		if ( ! wc_gc_is_ui_disabled() ) {
			return;
		}

		if ( $this->cart_contains_gift_card() ) {
			$this->notices[] = array( 'text' => __( 'Gift Cards can not be purchased using other Gift Cards.', 'woocommerce-gift-cards' ), 'type' => 'info' );
			return;
		}

		if ( ! empty( $args ) && isset( $args[ 'wc_gc_cart_code' ] ) ) {

			$code = wc_clean( $args[ 'wc_gc_cart_code' ] );

			if ( empty( $code ) ) {
				$this->notices[] = array( 'text' => __( 'Please enter your Gift Card code.', 'woocommerce-gift-cards' ), 'type' => 'info' );
				return;
			} elseif ( strlen( $code ) !== 19 ) {
				$this->notices[] = array( 'text' => __( 'Gift Card code must be 19 characters.', 'woocommerce-gift-cards' ), 'type' => 'info' );
				return;
			}

			$results       = WC_GC()->db->giftcards->query( array( 'return' => 'objects', 'code' => $code, 'limit' => 1 ) );
			$giftcard_data = count( $results ) ? array_shift( $results ) : false;

			if ( $giftcard_data ) {

				$giftcard = new WC_GC_Gift_Card( $giftcard_data );

				try {

					// If logged in check if auto-redeem is on.
					if ( get_current_user_id() && apply_filters( 'woocommerce_gc_auto_redeem', false ) ) {
						$giftcard->redeem( get_current_user_id() );
					} else {
						WC_GC()->giftcards->apply_giftcard_to_session( $giftcard );
					}

					$this->notices[] = array( 'text' => __( 'Gift Card code applied successfully!', 'woocommerce-gift-cards' ), 'type' => 'message' );

				} catch ( Exception $e ) {
					$this->notices[] = array( 'text' => $e->getMessage(), 'type' => 'error' );
				}

			} else {
				$this->notices[] = array( 'text' => __( 'Gift Card not found.', 'woocommerce-gift-cards' ), 'type' => 'error' );
			}
		}
	}

	/**
	 * Process the email link for session if any.
	 *
	 * @since 1.1.0
	 *
	 * @return void
	 */
	public function maybe_process_email_session() {

		if ( isset( $_GET[ 'do_email_session' ] ) ) {

			$base_url = remove_query_arg( 'do_email_session' );

			if ( apply_filters( 'woocommerce_gc_disable_email_session', false ) ) {
				wp_safe_redirect( $base_url );
				exit;
			}

			if ( ! WC()->session->has_session() ) {
				// Generate a random customer ID.
				WC()->session->set_customer_session_cookie( true );
			}

			$hash = ! empty( $_GET[ 'do_email_session' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 'do_email_session' ] ) ) : '';
			if ( empty( $hash ) ) {
				wc_add_notice( __( 'Invalid request. Please try again&hellip;', 'woocommerce-gift-cards' ), 'error' );
				wp_safe_redirect( $base_url );
				exit;
			}

			// Try to make brute-force attacks inefficient.
			sleep( 2 );

			$giftcard_data = WC_GC()->db->giftcards->get_by_hash( $hash );
			if ( $giftcard_data ) {

				$giftcard = new WC_GC_Gift_Card( $giftcard_data );

				try {

					// If logged in check if auto-redeem is on.
					if ( get_current_user_id() && apply_filters( 'woocommerce_gc_auto_redeem', false ) ) {
						$giftcard->redeem( get_current_user_id() );
					} else {
						WC_GC()->giftcards->apply_giftcard_to_session( $giftcard );
					}

					wc_add_notice( sprintf( __( 'Gift Card `%s` applied successfully!', 'woocommerce-gift-cards' ), $giftcard_data->get_code() ), 'success' );

				} catch ( Exception $e ) {
					wc_add_notice( $e->getMessage(), 'error' );
				}

			} else {
				wc_add_notice( __( 'Gift Card not found.', 'woocommerce-gift-cards' ), 'error' );
			}

			wp_safe_redirect( $base_url );
			exit;
		}
	}
}
