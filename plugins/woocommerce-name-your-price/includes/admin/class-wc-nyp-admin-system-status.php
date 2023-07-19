<?php
/**
 * Name Your Price System Status Class
 *
 * Adds additional information to the WooCommerce System Status.
 *
 * @author   Kathy Darling
 * @class    WC_NYP_Admin_System_Status
 * @package  WooCommerce Name Your Price/Admin
 * @since    3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * WC_NYP_Admin_System_Status class.
 */
class WC_NYP_Admin_System_Status {

	/**
	 * Attach callbacks
	 */
	public static function init() {
		// Template override scan path.
		add_filter( 'woocommerce_template_overrides_scan_paths', array( __CLASS__, 'template_scan_path' ) );

		// Show outdated templates in the system status.
		add_action( 'woocommerce_system_status_report', array( __CLASS__, 'render_system_status_items' ) );
	}

	/**
	 * Support scanning for template overrides in extension.
	 *
	 * @param  array $paths
	 * @return array
	 */
	public static function template_scan_path( $paths ) {
		$paths['WooCommerce Name Your Price'] = WC_Name_Your_Price()->plugin_path() . '/templates/';
		return $paths;
	}

	/**
	 * Add NYP debug data in the system status.
	 *
	 * @since  3.0.0
	 */
	public static function render_system_status_items() {

		$debug_data = array(
			'overrides' => self::get_template_overrides(),
		);

		include 'views/html-admin-page-status-report.php';
	}

	/**
	 * Determine which of our files have been overridden by the theme.
	 *
	 * @return array
	 */
	private static function get_template_overrides() {

		$template_path    = WC_Name_Your_Price()->plugin_path() . '/templates/';
		$templates        = WC_Admin_Status::scan_template_files( $template_path );
		$wc_template_path = trailingslashit( WC()->template_path() );
		$theme_root       = trailingslashit( get_theme_root() );

		$overridden = array();

		foreach ( $templates as $file ) {

			$found_location  = false;
			$check_locations = array(
				get_stylesheet_directory() . "/{$file}",
				get_stylesheet_directory() . "/{$wc_template_path}{$file}",
				get_template_directory() . "/{$file}",
				get_template_directory() . "/{$wc_template_path}{$file}",
			);

			foreach ( $check_locations as $location ) {
				if ( is_readable( $location ) ) {
					$found_location = $location;
					break;
				}
			}

			if ( ! empty( $found_location ) ) {

				$core_version  = WC_Admin_Status::get_file_version( $template_path . $file );
				$found_version = WC_Admin_Status::get_file_version( $found_location );
				$is_outdated   = $core_version && ( empty( $found_version ) || version_compare( $found_version, $core_version, '<' ) );

				if ( false !== strpos( $found_location, '.php' ) ) {
					$overridden[] = array(
						'file'         => str_replace( $theme_root, '', $found_location ),
						'version'      => $found_version,
						'core_version' => $core_version,
						'is_outdated'  => $is_outdated,
					);
				}
			}
		}

		return $overridden;
	}

}
WC_NYP_Admin_System_Status::init();
