<?php
/**
 * Gift Cards Functions
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
 * Get activity types.
 *
 * @return array
 */
function wc_gc_get_activity_types() {
	return array(
		'issued'   => __( 'Issue', 'woocommerce-gift-cards' ),
		'redeemed' => __( 'Redeem', 'woocommerce-gift-cards' ),
		'used'     => __( 'Debit', 'woocommerce-gift-cards' ),
		'refunded' => __( 'Credit', 'woocommerce-gift-cards' ),
	);
}

/**
 * Get activity type label.
 *
 * @param  string $slug
 * @return string
 */
function wc_gc_get_activity_type_label( $slug ) {

	$types = wc_gc_get_activity_types();

	if ( ! in_array( $slug, array_keys( $types ) ) ) {
		return '-';
	}

	return $types[ $slug ];
}

/**
 * Get Product types that can be used as Gift Cards.
 *
 * @return array
 */
function wc_gc_get_product_types_allowed() {
	return (array) apply_filters( 'woocommerce_gc_product_types_allowed', array(
		'simple',
		'variable'
	) );
}

/**
 * Generates a unique 19 character Gift Card code.
 *
 * @return string
 */
function wc_gc_generate_gift_card_code() {

	// http://stackoverflow.com/questions/3521621/php-code-for-generating-decent-looking-coupon-codes-mix-of-alphabets-and-number
	$code = strtoupper( substr( base_convert( sha1( uniqid( mt_rand() ) ), 16, 36 ), 0, 16 ) );
	$code = sprintf ( '%s-%s-%s-%s',
		substr ( $code, 0, 4 ),
		substr ( $code, 4, 4 ),
		substr ( $code, 8, 4 ),
		substr ( $code, 12, 4 )
	);

	$code = apply_filters( 'wc_gc_gift_card_code', $code );
	return $code;
}

/**
 * Generates a unique Gift Card hash.
 *
 * @since 1.0.4
 *
 * @param  string  $input
 * @param  string  $action
 * @return string
 */
function wc_gc_gift_card_hash( $input, $action ) {

	$output         = '';
	$encrypt_method = "AES-256-CBC";
	// Caution: Do not touch secret_key.
	$secret_key     = 'secret_wc_gc_key';
	// Caution: Do not touch secret_iv.
	$secret_iv      = 'secret_wc_gc_iv';

	// Hash it.
	$key = hash( 'sha256', $secret_key );
	// Tip: Encrypt method AES-256-CBC expects 16 bytes Initialization Vector.
	$iv  = substr( hash( 'sha256', $secret_iv ), 0, 16 );

	if ( 'encrypt' === $action ) {
		$output = openssl_encrypt( $input, $encrypt_method, $key, 0, $iv );
		$output = base64_encode( $output );
	} elseif ( 'decrypt' === $action ) {
		$output = openssl_decrypt( base64_decode( $input ), $encrypt_method, $key, 0, $iv );
	}

	return $output;
}

/**
 * Boolean whether or not to mask the gc codes.
 *
 * @param  string $context
 * @return bool
 */
function wc_gc_mask_codes( $context = '' ) {

	$mask = true;
	$user = wp_get_current_user();

	if ( 'admin' === $context && is_a( $user, 'WP_User' ) && wc_gc_is_site_admin() ) {
		$mask = false;
	}

	if ( 'account' === $context ) {
		$mask = true;
	}

	if ( 'checkout' === $context ) {
		$mask = true;
	}

	return apply_filters( 'woocommerce_gc_mask_codes', $mask, $context, $user );
}

/**
 * Boolean whether or not to mask the gc messages.
 *
 * @since  1.1.0
 *
 * @return bool
 */
function wc_gc_mask_messages() {

	$mask = true;
	$user = wp_get_current_user();

	if ( is_a( $user, 'WP_User' ) && wc_gc_is_site_admin() ) {
		$mask = false;
	}

	if ( ! is_admin() ) {
		$mask = false;
	}

	return apply_filters( 'woocommerce_gc_mask_message', $mask, $user );
}

/**
 * Front-End components.
 *
 * @return bool
 */
function wc_gc_is_ui_disabled() {
	return ! apply_filters( 'woocommerce_gc_disable_ui', false );
}

/**
 * Store-wide redeeming.
 *
 * @return bool
 */
function wc_gc_is_redeeming_enabled() {
	return apply_filters( 'woocommerce_gc_is_redeeming_enabled', true );
}

/**
 * Parse delimiter-seperated emails string.
 *
 * @param  string $email_string
 * @return bool
 */
function wc_gc_parse_email_string( $email_string ) {
	$max_recipients = absint( apply_filters( 'woocommerce_gc_max_recipients_number', 100 ) );
	$regex          = sprintf( '/\s*%s\s*/', preg_quote( wc_gc_get_emails_delimiter() ) );
	$value          = (array) preg_split( $regex, $email_string, $max_recipients, PREG_SPLIT_NO_EMPTY );

	return $value;
}

/**
 * Get emails string delimiter.
 *
 * @since  1.1.5
 *
 * @return string
 */
function wc_gc_get_emails_delimiter() {
	return apply_filters( 'woocommerce_gc_emails_delimiter', ',' );
}

/**
 * Is site admin helper.
 *
 * @return bool
 */
function wc_gc_is_site_admin() {
	return in_array( 'administrator', wp_get_current_user()->roles );
}

/**
 * Get formatted screen id.
 *
 * @since 1.5.2
 *
 * @param  string $key
 * @return string
 */
function wc_gc_get_formatted_screen_id( $screen_id ) {

	$prefix = sanitize_title( __( 'WooCommerce', 'woocommerce' ) );
	if ( 0 === strpos( $screen_id, 'woocommerce_' ) ) {
		$screen_id = str_replace( 'woocommerce_', $prefix . '_', $screen_id );
	}

	return $screen_id;
}

/**
 * Is a unix timestamp.
 *
 * @since 1.2.0
 *
 * @return bool
 */
function wc_gc_is_unix_timestamp( $stamp ) {
	return is_numeric( $stamp ) && (int) $stamp == $stamp && $stamp > 0;
}

/**
 * Takes a timestamp in GMT and converts it to store's timezone.
 *
 * @since 1.3.0
 *
 * @param  int   $timestamp
 * @param  float $offset
 * @return int
 */
function wc_gc_convert_timestamp_to_gmt_offset( $timestamp, $gmt_offset = null ) {

	$store_timestamp = new DateTime();
	$store_timestamp->setTimestamp( $timestamp );

	// Get the Store's offset.
	if ( is_null( $gmt_offset ) ) {
		$gmt_offset = wc_gc_get_gmt_offset();
	}

	$store_timestamp->modify( $gmt_offset * 60 . ' minutes' );

	return $store_timestamp->getTimestamp();
}

/**
 * Get the store's GMT offset.
 *
 * @since  1.3.0
 *
 * @return float
 */
function wc_gc_get_gmt_offset() {
	return (float) get_option( 'gmt_offset' );
}

/**
 * Get the delivery timestamp type.
 *
 * @since  1.3.0
 *
 * @return string
 */
function wc_gc_get_date_input_timezone_reference() {

	/**
	 * `woocommerce_gc_date_input_timezone_reference` filter.
	 *
	 * How should the delivery time be set. Available options are:
	 *
	 * @param  string  $reference  {
	 *
	 *     - 'store'  : Show UI with Store's Timezone and keep the date to the Store's timezone.
	 *                  Users select the time based on Store's clock. Foreign visitors need to be warned about this.
	 *
	 *     - 'default': Show UI with Clients's Timezone and convert the date to the Store's timezone.
	 *                  Works best when users are sending gift cards in the same timezone (Default)
	 * }
	 *
	 * @return string
	 */
	return apply_filters( 'woocommerce_gc_date_input_timezone_reference', 'default' );
}
