<?php if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly

if (!class_exists('WWLC_Admin_Menu')) {

    /*
    |--------------------------------------------------------------------------
    | This class is about adding "Lead Capture" submenu under "Wholesale" top level menu.
    | For now it only redirects to settings page.
    | When the version 2 comes this will open its own page just like "Order Form"
    |--------------------------------------------------------------------------
     */
    class WWLC_Admin_Menu
    {

        /*
        |--------------------------------------------------------------------------
        | Class Properties
        |--------------------------------------------------------------------------
         */

        /**
         * Property that holds the single main instance of WWLC_Admin_Menu.
         *
         * @since 1.17.2
         * @access private
         * @var WWLC_Admin_Menu
         */
        private static $_instance;

        /*
        |--------------------------------------------------------------------------
        | Class Methods
        |--------------------------------------------------------------------------
         */

        /**
         * WWLC_Admin_Menu constructor.
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWLC_Admin_Menu model.
         *
         * @since 1.17.2
         */
        public function __construct()
        {
        }

        /**
         * Singleton Pattern.
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWLC_Admin_Menu model.
         *
         * @since 1.17.2
         * @return WWLC_Admin_Menu
         */
        public static function instance($dependencies = null)
        {

            if (!self::$_instance instanceof self) {
                self::$_instance = new self($dependencies);
            }

            return self::$_instance;

        }

        /*
        |--------------------------------------------------------------------------
        | Add CPT Admin Menu Page
        |--------------------------------------------------------------------------
         */

        /**
         * Add "Lead Capture" submenu into Wholesale top level menu.
         *
         * @since 1.17.2
         * @access public
         */
        public function add_lead_capture_submenu()
        {

            add_submenu_page(
                'wholesale-suite',
                __('Lead Capture', 'woocommerce-wholesale-prices'),
                __('Lead Capture', 'woocommerce-wholesale-prices'),
                apply_filters('wwp_can_access_admin_menu_cap', 'manage_options'),
                'wwp-lead-capture-page',
                array($this, 'lead_capture_settings'),
                3
            );

        }

        /**
         * Redirect Wholesale > Lead Capture submenu to Lead Capture settings page
         *
         * @since 1.17.2
         * @access public
         */
        public function lead_capture_settings()
        {
            wp_redirect(admin_url('admin.php?page=wc-settings&tab=wwlc_settings'));
            exit;
        }

        /**
         * Execute model.
         *
         * @since 1.17.2
         * @access public
         */
        public function run()
        {

            // Order Form WC Submenu
            add_action('admin_menu', array($this, 'add_lead_capture_submenu'), 99);

        }

    }

}
