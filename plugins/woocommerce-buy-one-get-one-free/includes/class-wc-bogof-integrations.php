<?php
/**
 * Buy One Get One Free All Products compatibility integrations.
 *
 * @package  WC_BOGOF
 */

defined( 'ABSPATH' ) || exit;

/**
 * WC_BOGOF_WCS_ATT Class
 */
class WC_BOGOF_Integrations {

	/**
	 * Admin messages.
	 *
	 * @var array.
	 */
	private static $messages;

	/**
	 * Init integrations
	 */
	public static function init() {

		$integrations = array(
			'woocommerce_wpml' => 'WC_BOGOF_WPML',
			'WCS_ATT'          => 'WC_BOGOF_WCS_ATT',
			'Polylang'         => 'WC_BOGOF_Polylang',
			'WC_Bundles'       => 'WC_BOGOF_Product_Bundles',
		);

		self::$messages = array();

		foreach ( $integrations as $required_class => $classname ) {
			if ( class_exists( $required_class ) ) {
				self::add_integration( $classname );
			}
		}

		if ( is_admin() && ! empty( self::$messages ) ) {
			add_action( 'admin_notices', array( __CLASS__, 'display_notices' ) );
		}
	}

	/**
	 * Add an integration.
	 *
	 * @param string $classname Integration class name.
	 */
	private static function add_integration( $classname ) {
		$filename = dirname( __FILE__ ) . '/integrations/class-' . strtolower( str_replace( '_', '-', $classname ) ) . '.php';
		include_once $filename;

		if ( is_callable( array( $classname, 'check_min_version' ) ) && ! $classname::check_min_version() ) {
			$extension        = $classname::extension_name();
			$required_version = $classname::min_version_required();
			// Translators: 1,2: HTML tags, 2: extension name, 3: min version required.
			self::$messages[] = sprintf( __( 'The installed version of %1$s%3$s%2$s is not supported by %1$sBuy One Get One Free%2$s. Please update %1$s%3$s%2$s to version %1$s%4$s%2$s or higher.', 'wc-buy-one-get-one-free' ), '<strong>', '</strong>', $extension, $required_version );
		}

		if ( is_callable( array( $classname, 'check_environment' ) ) ) {
			self::$messages = array_merge( self::$messages, $classname::check_environment() );
		}

		if ( empty( self::$messages ) ) {
			$classname::init();

			if ( is_callable( array( $classname, 'get_admin_notices' ) ) ) {
				self::$messages = array_merge( self::$messages, $classname::get_admin_notices() );
			}
		}
	}

	/**
	 * Display errors notice.
	 */
	public static function display_notices() {

		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id : '';

		if ( ( in_array( $screen_id, wc_get_screen_ids(), true ) || 'plugins' === $screen_id ) ) {
			foreach ( self::$messages as $message ) {
				echo '<div class="updated woocommerce-message"><p>' . wp_kses_post( $message ) . '</p></div>';
			}
		}
	}
}
