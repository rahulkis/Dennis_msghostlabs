<?php
/**
 * WC_GC_Admin_Gift_Cards_Page class
 *
 * @author   SomewhereWarm <info@somewherewarm.com>
 * @package  WooCommerce Gift Cards
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_GC_Admin_Gift_Cards_Page Class.
 *
 * @version 1.3.6
 */
class WC_GC_Admin_Gift_Cards_Page {

	/**
	 * Page home URL.
	 *
	 * @const PAGE_URL
	 */
	const PAGE_URL = 'admin.php?page=gc_giftcards';

	/**
	 * Save.
	 */
	public static function process() {

		if ( empty( $_POST ) ) {
			return false;
		}

		check_admin_referer( 'woocommerce-gc-edit', 'gc_edit_security' );

		$giftcard_id = isset( $_GET[ 'giftcard' ] ) ? absint( $_GET[ 'giftcard' ] ) : 0;
		if ( $giftcard_id ) {
			$giftcard = WC_GC()->db->giftcards->get( $giftcard_id );
		}

		if ( isset( $giftcard ) && $giftcard->get_id() ) {

			// Construct edit url.
			$edit_url = add_query_arg( array( 'section' => 'edit', 'giftcard' => $giftcard->get_id() ), self::PAGE_URL );

			if ( isset( $_POST[ 'save'] ) ) {

				// Posted data.
				$args = $_POST;

				// Convert to Deliver date timestamp.
				$deliver_date_day    = isset( $args[ 'deliver_date_day' ] ) ? $args[ 'deliver_date_day' ] : false;
				$deliver_date_hour   = isset( $args[ 'deliver_date_hour' ] ) ? zeroise( intval( $args[ 'deliver_date_hour' ] ), 2 ) : '00';
				$deliver_date_minute = isset( $args[ 'deliver_date_minute' ] ) ? zeroise( intval( $args[ 'deliver_date_minute' ] ), 2 ) : '00';

				if ( $deliver_date_day ) {

					$args[ 'deliver_date' ] = strtotime( $deliver_date_day . ' ' . $deliver_date_hour . ':' . $deliver_date_minute . ':00' );

					if ( $args[ 'deliver_date' ] < strtotime( 'tomorrow' ) ) {

						WC_GC_Admin_Notices::add_notice( __( 'Delivery date must be at least one day after today.', 'woocommerce-gift-cards' ), 'success', true );
						unset( $args[ 'deliver_date' ] );

					} else {

						// if diff re-schedule.
						if ( $args[ 'deliver_date' ] !== $giftcard->get_deliver_date() && false === $giftcard->is_delivered() ) {
							if ( 0 !== $giftcard->get_deliver_date() ) {
								WC_GC_Core_Compatibility::unschedule_action( 'woocommerce_gc_schedule_send_gift_card_to_customer', array( 'giftcard' => $giftcard->get_id(), 'order_id' => $giftcard->get_order_id() ), 'send_giftcards' );
							}
							// Re-schedule.
							WC_GC_Core_Compatibility::schedule_single_action( $args[ 'deliver_date' ], 'woocommerce_gc_schedule_send_gift_card_to_customer', array( 'giftcard' => $giftcard->get_id(), 'order_id' => $giftcard->get_order_id() ), 'send_giftcards' );
							/* translators: %s: Schedule date */
							WC_GC_Admin_Notices::add_notice( sprintf( __( 'Gift Card rescheduled for %s.', 'woocommerce-gift-cards' ), esc_html( date_i18n( get_option( 'date_format' ), $args[ 'deliver_date' ] ) ) ), 'success', true );
						}
					}

				} else {

					$args[ 'deliver_date' ] = 0;

					// if diff un-schedule and send now.
					if ( $args[ 'deliver_date' ] !== $giftcard->get_deliver_date() ) {
						WC_GC_Core_Compatibility::unschedule_action( 'woocommerce_gc_schedule_send_gift_card_to_customer', array( 'giftcard' => $giftcard->get_id(), 'order_id' => $giftcard->get_order_id() ), 'send_giftcards' );
						if ( false === $giftcard->is_delivered() ) {
							// Make sure GC is enabled before sending...
							$args[ 'is_active' ] = 'on';
							do_action( 'woocommerce_gc_force_send_gift_card_to_customer', $giftcard );
							WC_GC_Admin_Notices::add_notice( __( 'Gift Card sent.', 'woocommerce-gift-cards' ), 'success', true );
						}
					}

				}

				// Convert to Expire date timestamp.
				$expire_date_day    = isset( $args[ 'expire_date_day' ] ) ? $args[ 'expire_date_day' ] : false;
				$expire_date_hour   = isset( $args[ 'expire_date_hour' ] ) ? zeroise( intval( $args[ 'expire_date_hour' ] ), 2 ) : '00';
				$expire_date_minute = isset( $args[ 'expire_date_minute' ] ) ? zeroise( intval( $args[ 'expire_date_minute' ] ), 2 ) : '00';

				if ( $expire_date_day ) {
					$args[ 'expire_date' ] = strtotime( $expire_date_day . ' ' . $expire_date_hour . ':' . $expire_date_minute . ':00' );
				} else {
					$args[ 'expire_date' ] = 0;
				}

				// Escape attributes.
				if ( isset( $args[ 'sender' ] ) ) {
					$args[ 'sender' ] = sanitize_text_field( wp_unslash( wptexturize( $args[ 'sender' ] ) ) );
				}
				if ( isset( $args[ 'recipient' ] ) ) {
					$args[ 'recipient' ] = sanitize_text_field( $args[ 'recipient' ] );
				}
				if ( isset( $args[ 'message' ] ) ) {
					$args[ 'message' ] = sanitize_textarea_field( wp_unslash( wptexturize( $args[ 'message' ] ) ) );
				}

				// Remove unused args.
				unset( $args[ 'save' ] );
				unset( $args[ 'wc_gc_action' ] );
				unset( $args[ 'deliver_date_day' ] );
				unset( $args[ 'deliver_date_hour' ] );
				unset( $args[ 'deliver_date_minute' ] );
				unset( $args[ 'expire_date_day' ] );
				unset( $args[ 'expire_date_hour' ] );
				unset( $args[ 'expire_date_minute' ] );

				// Should revalidate? -- Cache expire status.
				$has_expired = $giftcard->has_expired();

				try {
					if ( WC_GC()->db->giftcards->update( $giftcard, $args ) ) {
						if ( $has_expired !== $giftcard->has_expired() ) {
							WC_GC()->account->maybe_clear_caches();
						}
						WC_GC_Admin_Notices::add_notice( __( 'Gift Card updated.', 'woocommerce-gift-cards' ), 'success', true );
					}
				} catch ( Exception $e ) {
					WC_GC_Admin_Notices::add_notice( $e->getMessage(), 'error', true );
				}
			}

			// Process action.
			if ( ! empty( $_POST[ 'wc_gc_action' ] ) ) {

				$action        = wc_clean( $_POST[ 'wc_gc_action' ] );
				$should_update = false;

				switch ( $action ) {

					case 'send_giftcard':
						if ( ! $giftcard->has_expired() && $giftcard->is_active() ) {

							// Check if is scheduled and cancel action.
							$scheduled_date = WC_GC_Core_Compatibility::next_scheduled_action( 'woocommerce_gc_schedule_send_gift_card_to_customer', array( 'giftcard' => $giftcard->get_id(), 'order_id' => $giftcard->get_order_id() ), 'send_giftcards' );
							if ( $scheduled_date ) {
								WC_GC_Core_Compatibility::unschedule_action( 'woocommerce_gc_schedule_send_gift_card_to_customer', array( 'giftcard' => $giftcard->get_id(), 'order_id' => $giftcard->get_order_id() ), 'send_giftcards' );
							}

							do_action( 'woocommerce_gc_force_send_gift_card_to_customer', $giftcard );

							WC_GC_Admin_Notices::add_notice( __( 'Gift Card sent.', 'woocommerce-gift-cards' ), 'success', true );

						} else {
							WC_GC_Admin_Notices::add_notice( __( 'Action failed. The Gift Card is disabled or has expired.', 'woocommerce-gift-cards' ), 'error', true );
						}
						break;

					case 'enable_giftcard':
						$giftcard->set_active( 'on' );
						WC_GC()->account->maybe_clear_caches();
						$should_update = true;
						break;

					case 'disable_giftcard':
						$giftcard->set_active( 'off' );
						WC_GC()->account->maybe_clear_caches();
						$should_update = true;
						break;
				}

				if ( $should_update ) {
					$giftcard->save();
				}
			}

			// Manual Redeem process.
			if ( isset( $_POST[ 'redeem' ] ) && isset( $_POST[ 'redeem_for_customer' ] ) ) {

				$user_id = absint( $_POST[ 'redeem_for_customer' ] );
				$user    = get_user_by( 'id', $user_id );

				if ( $user && $user->ID ) {

					$giftcard_object = new WC_GC_Gift_Card( $giftcard );

					try {

						if ( $giftcard_object->is_active() && ! $giftcard_object->has_expired() && $giftcard_object->redeem( $user->ID, true ) ) {
							/* translators: %s: User display name */
							WC_GC_Admin_Notices::add_notice( __( sprintf( 'Gift Card redeemed for %s.', $user->display_name ), 'woocommerce-gift-cards' ), 'success', true );
						} else {
							WC_GC_Admin_Notices::add_notice( __( 'Action failed. The Gift Card is disabled, has expired, or has already been redeemed.', 'woocommerce-gift-cards' ), 'error', true );
						}

					} catch ( Exception $e ) {
						WC_GC_Admin_Notices::add_notice( $e->getMessage(), 'error', true );
					}
				}
			}

			wp_redirect( admin_url( $edit_url ) );
			exit;
		}
	}

	/**
	 * Delete giftcard.
	 */
	public static function delete() {

		check_admin_referer( 'delete_giftcard' );

		$giftcard_id = isset( $_GET[ 'giftcard' ] ) ? absint( $_GET[ 'giftcard' ] ) : 0;
		if ( $giftcard_id ) {
			$giftcard = WC_GC()->db->giftcards->get( $giftcard_id );
		}

		if ( isset( $giftcard ) && $giftcard ) {
			$giftcard->delete();
			if ( 0 !== $giftcard->get_deliver_date() ) {
				WC_GC_Core_Compatibility::unschedule_action( 'woocommerce_gc_schedule_send_gift_card_to_customer', array( 'giftcard' => $giftcard->get_id(), 'order_id' => $giftcard->get_order_id() ), 'send_giftcards' );
			}
			WC_GC_Admin_Notices::add_notice( __( 'Gift Card deleted.', 'woocommerce-gift-cards' ), 'success', true );
		}

		wp_redirect( admin_url( self::PAGE_URL ) );
		exit;
	}

	/**
	 * Render page.
	 */
	public static function output() {

		$search = isset( $_REQUEST[ 's' ] ) ? sanitize_text_field( $_REQUEST[ 's' ] ) : '';
		$table  = new WC_GC_Gift_Cards_List_Table();
		$table->prepare_items();

		// If no giftcards check if a GC Product exists.
		$gc_product_exists = true;
		if ( 0 === $table->total_items ) {
			global $wpdb;
			$gc_product_exists = $wpdb->get_var( "SELECT COUNT(*) AS `gc_count` FROM {$wpdb->postmeta} WHERE meta_key = '_gift_card'" ) > 0;
		}

		include dirname( __FILE__ ) . '/views/html-admin-gift-cards.php';
	}

	/**
	 * Render edit page.
	 */
	public static function edit_output() {

		$giftcard_id = isset( $_GET[ 'giftcard' ] ) ? absint( $_GET[ 'giftcard' ] ) : 0;
		if ( $giftcard_id ) {
			$giftcard = WC_GC()->db->giftcards->get( $giftcard_id );
		}

		if ( ! isset( $giftcard ) || ! $giftcard ) {
			WC_GC_Admin_Notices::add_notice( __( 'Gift Card not found.', 'woocommerce-gift-cards' ), 'success', true );
			wp_redirect( admin_url( self::PAGE_URL ) );
			exit();
		}

		$activity_table = new WC_GC_Activity_List_Table( $giftcard->get_id() );
		$activity_table->prepare_items();

		include dirname( __FILE__ ) . '/views/html-admin-gift-card-edit.php';
	}

	/**
	 * Get gift card message field HTML.
	 *
	 * @since 1.0.1
	 */
	public static function get_message_field_html( $giftcard ) {

		if ( wc_gc_mask_messages() ) {
			?>
			<div class="wc-gc-message-mask">
				<label for="message">
					<?php esc_html_e( 'Message:', 'woocommerce-gift-cards' ); ?>
					<span class="wc-gc-private-tag"><?php esc_html_e( 'Private', 'woocommerce-gift-cards' ) ?></span>
					<a id="wc_gc_replace_message_cancel" href="#">&larr; <?php esc_html_e( 'Cancel', 'woocommerce-gift-cards' ); ?></a>
				</label>
				<div class="wc-gc-message-mask_placeholder">
					<p>
						<?php esc_html_e( 'This message was hidden to protect the sender & recipient’s privacy.', 'woocommerce-gift-cards' ); ?>
					</p>
					<?php if ( ! $giftcard->is_redeemed() ): ?>
						<p>
							<?php esc_html_e( 'Sender requested a change?', 'woocommerce-gift-cards' ); ?>
							<a id="wc_gc_replace_message_action" href="#"><?php esc_html_e( 'Edit this message', 'woocommerce-gift-cards' ); ?></a>
						</p>
					<?php endif; ?>
				</div>
				<textarea name="message" placeholder="<?php esc_attr_e( 'Write a new message&hellip;', 'woocommerce-gift-cards' ); ?>" rows="5"></textarea>
			</div>
			<?php
		} else {
			?><label for="message">
				<?php esc_html_e( 'Message:', 'woocommerce-gift-cards' ); ?>
			</label>
			<textarea name="message" rows="5"<?php echo $giftcard->is_redeemed() ? ' disabled' : ''; ?>><?php echo esc_html( wptexturize( $giftcard->get_message() ) ); ?></textarea><?php
		}
	}
}
