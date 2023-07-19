<?php if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly

if (!class_exists('Order_Form_Helpers')) {

    /**
     * Model that houses plugin helper functions.
     *
     * @since 1.16
     */
    final class Order_Form_Helpers
    {

        /**
         * Check if user is a wholesale customer
         *
         * @since 1.15
         * @return bool
         */
        public function is_wholesale_customer()
        {

            if (is_plugin_active('woocommerce-wholesale-prices/woocommerce-wholesale-prices.bootstrap.php')) {

                global $wc_wholesale_prices;
                $wholesale_role = $wc_wholesale_prices->wwp_wholesale_roles->getUserWholesaleRole();

                return isset($wholesale_role[0]) ? $wholesale_role[0] : '';

            }

            return false;

        }

        /**
         * Utility function that determines if a plugin is active or not.
         *
         * @since 1.16
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

        /**
         * Get plugin data.
         *
         * @since 1.16
         * @access public
         *
         * @param string $plugin_basename Plugin basename.
         * @return array Array of data about the current woocommerce installation.
         */
        public static function get_plugin_data($plugin_basename)
        {

            if (!function_exists('get_plugin_data')) {
                require_once ABSPATH . '/wp-admin/includes/plugin.php';
            }

            if (file_exists(WP_PLUGIN_DIR . '/' . $plugin_basename)) {
                return get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin_basename);
            } else {
                return false;
            }

        }

        /**
         * Get data about the current woocommerce installation.
         *
         * @since 1.16
         * @access public
         *
         * @return array
         */
        public static function get_woocommerce_data()
        {

            return self::get_plugin_data('woocommerce/woocommerce.php');

        }

        /**
         * Get data about the current WWP installation.
         *
         * @since 1.16.6
         * @access public
         *
         * @return array
         */
        public static function get_wwp_data()
        {

            return self::get_plugin_data('woocommerce-wholesale-prices/woocommerce-wholesale-prices.bootstrap.php');

        }

        /**
         * Get data about the current WWPP installation.
         *
         * @since 1.16
         * @access public
         *
         * @return array
         */
        public static function get_wwpp_data()
        {

            return self::get_plugin_data('woocommerce-wholesale-prices-premium/woocommerce-wholesale-prices-premium.bootstrap.php');

        }

        /**
         * Check if WWP is active.
         *
         * @since 1.16.6
         * @access public
         *
         * @return boolean
         */
        public static function is_wwp_active()
        {

            return self::is_plugin_active('woocommerce-wholesale-prices/woocommerce-wholesale-prices.bootstrap.php');

        }

        /**
         * Check if WWPP is active.
         *
         * @since 1.16.6
         * @access public
         *
         * @return boolean
         */
        public static function is_wwpp_active()
        {

            return self::is_plugin_active('woocommerce-wholesale-prices-premium/woocommerce-wholesale-prices-premium.bootstrap.php');

        }

        /**
         * Get all user roles for current user.
         *
         * @since 1.21
         * @return array
         */
        public static function get_user_roles()
        {

            // The user id
            $user_id = get_current_user_id();

            // The transient name
            $transient_name = 'wwpp_user_role_' . $user_id;

            // The user roles
            $user_roles = get_transient($transient_name);

            // Check if user role transient is empty then fetch and save the roles
            if (empty($user_roles) && $user_id > 0) {
                $user = get_user_by('id', $user_id);
                set_transient($transient_name, $user->roles, HOUR_IN_SECONDS);
            }

            return $user_roles;

        }

        /**
         * Check active WWOF is new install by checking if Wholesale Order Form Page ID is set.
         *
         * @since 2.0
         * @access public
         *
         * @return boolean
         */
        public static function is_fresh_install()
        {

            // Flag that determines if the current order form is old installation
            $order_form_page = get_option(WWOF_SETTINGS_WHOLESALE_PAGE_ID);

            return $order_form_page > 0 && get_post_status($order_form_page) !== "" ? false : true;

        }

        /**
         * Check if WWOF has template overrides prior to 2.0.
         *
         * @since 2.0
         * @access public
         *
         * @return boolean
         */
        public static function has_template_overrides()
        {

            // Makes sure the plugin is defined before trying to use it
            if (!function_exists('list_files')) {
                include_once ABSPATH . 'wp-admin/includes/file.php';
            }

            $wc_templates = list_files(get_stylesheet_directory() . '/woocommerce');

            if (!empty($wc_templates)) {
                foreach ($wc_templates as $temp) {
                    // Template override is present
                    if (strpos($temp, 'wwof-product-listing') !== false) {
                        return true;
                    }
                }
            }

            // No template override
            return false;

        }

        /**
         * Check if the site has WPML active.
         *
         * @since 2.0
         * @access public
         *
         * @return boolean
         */
        public static function has_wpml_active()
        {

            return class_exists('SitePress') ? true : false;

        }

        /**
         * Check if the site has Product Addons active.
         *
         * @since 2.0
         * @access public
         *
         * @return boolean
         */
        public static function has_addons_active()
        {

            return Order_Form_Helpers::is_plugin_active('woocommerce-product-addons/woocommerce-product-addons.php');

        }

    }

}
