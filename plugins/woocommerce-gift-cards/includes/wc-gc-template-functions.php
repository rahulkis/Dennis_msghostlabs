<?php
/**
 * Template Functions
 *
 * @author   SomewhereWarm <info@somewherewarm.com>
 * @package  WooCommerce Gift Cards
 * @since    1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'wc_gc_get_activity_description' ) ) {

	function wc_gc_get_activity_description( $activity_data ) {

		$description = $activity_data->get_gc_code();
		$mask        = wc_gc_mask_codes();

		switch ( $activity_data->get_type() ) {
			case 'redeemed':
				/* translators: %1$s: giftcard code */
				$description = sprintf( __( 'Added gift card code %1$s to your account', 'woocommerce-gift-cards' ), $mask ? wc_gc_mask_code( $activity_data->get_gc_code() ) : $activity_data->get_gc_code() );
				break;
			case 'refunded':
				/* translators: %1$s: giftcard code, %2$s: order link */
				$description = sprintf( __( 'Refunded to gift card code %1$s via order %2$s', 'woocommerce-gift-cards' ), $mask ? wc_gc_mask_code( $activity_data->get_gc_code() ) : $activity_data->get_gc_code(), '<a href="' . wc_get_endpoint_url( 'view-order', $activity_data->get_object_id(), wc_get_page_permalink( 'myaccount' ) ) . '">#' . $activity_data->get_object_id() . '</a>' );
				break;
			case 'used':
				/* translators: %1$s: giftcard code, %2$s: order link */
				$description = sprintf( __( 'Used gift card code %1$s to pay for order %2$s', 'woocommerce-gift-cards' ), $mask ? wc_gc_mask_code( $activity_data->get_gc_code() ) : $activity_data->get_gc_code(), '<a href="' . wc_get_endpoint_url( 'view-order', $activity_data->get_object_id(), wc_get_page_permalink( 'myaccount' ) ) . '">#' . $activity_data->get_object_id() . '</a>' );
				break;
		}

		return $description;
	}
}


if ( ! function_exists( 'wc_gc_get_emails_formatted' ) ) {

	function wc_gc_get_emails_formatted( $emails, $show = 2 ) {
		$total    = count( $emails );
		$remaning = $total - $show;

		if ( $total > $show ) {
			$value = implode( ', ', array_slice( $emails, 0, $show ) );
			if ( $remaning > 0 ) {
				$more = '';
				if ( 1 === $remaning ) {
					$more = esc_html__( 'and 1 more', 'woocommerce-gift-cards' );
				} else {
					/* translators: %s: number of emails used */
					$more = _n( 'and %s more', 'and %s others', $remaning, 'woocommerce-gift-cards' );
				}
				$value .= ' ' . sprintf( $more, $remaning );
			}
		} else {
			$value = implode( ', ', $emails );
		}

		return $value;
	}
}

if ( ! function_exists( 'wc_gc_mask_code' ) ) {

	function wc_gc_mask_code( $code ) {
		return 'XXXX-XXXX-XXXX-' . substr( $code, -4 );
	}

}
