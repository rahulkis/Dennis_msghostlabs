<?php
/**
 * NYP install class.
 *
 * Updates custom db data.
 *
 * @package  WooCommerce Name Your Price/Admin
 * @since    3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * The WC_NYP_Install class.
 */
class WC_NYP_Install {

	/**
	 * Current DB Version
	 *
	 * @var string version - only change when you need to update the db content/structure.
	 * @since 3.0.0
	 */
	private static $db_version = '3.0.0';

	/**
	 * Prefix of nyp options
	 *
	 * @var wp_option prefix
	 * @since 3.0.0
	 */
	private static $db_option_prefix = 'woocommerce_nyp_';

	/**
	 * DB updates and callbacks that need to be run per version.
	 *
	 * @var array
	 */
	private static $db_updates = array(
		'3.0.0' => 'update_300_options',
	);

	/**
	 * Init.
	 *
	 * @since 3.0.0
	 */

	public static function init() {

		foreach ( self::$db_updates as $version => $callback ) {
			if ( version_compare( self::get_current_db_version(), $version, '<' ) ) {
				call_user_func( array( __CLASS__, $callback ) );
			}
		}

	}

	/**
	 * Update the suggested and min text options
	 *
	 * @since 3.0.0
	 */
	public static function update_300_options() {

		$option_prefix = self::$db_option_prefix;
		$_placeholder  = '%PRICE%';

		// Get options.
		$current_db_version = self::get_current_db_version();
		$min_text           = get_option( $option_prefix . 'minimum_text' );
		$suggested_text     = get_option( $option_prefix . 'suggested_text' );

		// Set or update minimum price text.
		if ( false !== $min_text ) {
			$min_text = is_rtl() ? $_placeholder . ' ' . $min_text : $min_text . ' ' . $_placeholder;
		} else {
			$min_text = _x( 'Minimum price: %PRICE%', 'Name your price default miinium text', 'wc_name_your_price' );
		}

		// Set or update suggested price text.
		if ( false !== $suggested_text ) {
			$suggested_text = is_rtl() ? $_placeholder . ' ' . $suggested_text : $suggested_text . ' ' . $_placeholder;
		} else {
			$suggested_text = _x( 'Suggested price: %PRICE%', 'Name your price default suggested text', 'wc_name_your_price' );
		}

		// Update options.
		update_option( $option_prefix . 'minimum_text', $min_text );
		update_option( $option_prefix . 'suggested_text', $suggested_text );
		update_option( $option_prefix . 'db_version', self::$db_version, false );

		$notice = sprintf(
			// Translators: %s is the URL to the documentation.
			__( '<strong>WooCommerce Name Your Price</strong> has been upgraded to version 3.0! Read more about <a target="_blank" href="%s">what\'s new in 3.0</a>', 'wc_name_your_price' ),
			esc_url( 'https://docs.woocommerce.com/document/name-your-price/whats-new-in-3-0/' )
		);

		WC_NYP_Admin_Notices::add_notice( $notice, 'info' );

	}

	/**
	 * Gets the NYP db version of the site
	 *
	 * @since 3.0.0
	 * @return float|int
	 */
	public static function get_current_db_version() {
		return get_option( self::$db_option_prefix . 'db_version', 0 );

	}

} // End class: do not remove or there will be no more guacamole for you.
return WC_NYP_Install::init();
