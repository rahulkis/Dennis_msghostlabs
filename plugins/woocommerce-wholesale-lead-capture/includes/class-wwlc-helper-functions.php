<?php if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly

if (!class_exists('WWLC_Helper_Functions')) {

    /**
     * Model that house various generic plugin helper functions.
     *
     * @since 1.17.1
     */
    final class WWLC_Helper_Functions
    {

        /**
         * Check if WWP is v2.0.
         *
         * @since 1.17.1
         * @access public
         *
         * @return boolean
         */
        public static function is_wwp_v2()
        {

            if (self::is_plugin_active('woocommerce-wholesale-prices/woocommerce-wholesale-prices.bootstrap.php')) {

                if (!function_exists('get_plugin_data')) {
                    require_once ABSPATH . '/wp-admin/includes/plugin.php';
                }

                $wwp_data = get_plugin_data(WWP_PLUGIN_PATH . 'woocommerce-wholesale-prices.bootstrap.php');

                if (version_compare($wwp_data['Version'], '2', '>=')) {
                    return true;
                }

            }

            return false;

        }

        /**
         * Utility function that determines if a plugin is active or not.
         *
         * @since 1.17.1
         * @access public
         *
         * @param string $plugin_basename Plugin base name. Ex. woocommerce/woocommerce.php
         * @return boolean True if active, false otherwise.
         */
        public static function is_plugin_active($plugin_basename)
        {

            // Makes sure the plugin is defined before trying to use it
            if (!function_exists('is_plugin_active')) {
                include_once ABSPATH . 'wp-admin/includes/plugin.php';
            }

            return is_plugin_active($plugin_basename);
        }

    }

}
