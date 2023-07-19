<?php
/**
 * WC_GC_Shortcodes class
 *
 * @author   SomewhereWarm <info@somewherewarm.com>
 * @package  WooCommerce Gift Cards
 * @since    1.5.1
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * WooCommerce Gift Cards Shortcodes class.
 *
 * @version 1.5.1
 */
class WC_GC_Shortcodes {

	/**
	 * Init shortcodes.
	 */
	public static function init() {
		$shortcodes = array(
			'woocommerce_my_account_giftcards' => array( __CLASS__, 'my_account_giftcards' )
		);

		foreach ( $shortcodes as $shortcode => $function ) {
			add_shortcode( apply_filters( "{$shortcode}_shortcode_tag", $shortcode ), $function );
		}
	}

	/**
	 * Shortcode Wrapper.
	 *
	 * @param  mixed  $function
	 * @param  array  $atts
	 * @param  array  $wrapper
	 * @return string
	 */
	public static function shortcode_wrapper( $function, $atts = array(), $wrapper = array() ) {

		$wrapper = wp_parse_args( $wrapper, array(
			'class'  => 'woocommerce',
			'before' => null,
			'after'  => null
		) );

		ob_start();

		echo empty( $wrapper[ 'before' ] ) ? '<div class="' . esc_attr( $wrapper[ 'class' ] ) . '">' : $wrapper[ 'before' ];
		call_user_func( $function, $atts );
		echo empty( $wrapper[ 'after' ] ) ? '</div>' : $wrapper[ 'after' ];

		return ob_get_clean();
	}

	/**
	 * My account giftcards page shortcode.
	 *
	 * @param  array  $atts
	 * @return string
	 */
	public static function my_account_giftcards( $atts ) {
		return self::shortcode_wrapper( array( WC_GC()->account, 'render_page' ), 1, $atts );
	}
}
