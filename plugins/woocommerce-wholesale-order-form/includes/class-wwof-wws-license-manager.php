<?php if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly

if (!class_exists('WWOF_WWS_License_Manager')) {

    class WWOF_WWS_License_Manager
    {

        /*
        |--------------------------------------------------------------------------
        | Class Properties
        |--------------------------------------------------------------------------
         */

        /**
         * Property that holds the single main instance of WWOF_WWS_License_Manager.
         *
         * @since 1.11
         * @access private
         * @var WWOF_WWS_License_Manager
         */
        private static $_instance;

        /*
        |--------------------------------------------------------------------------
        | Class Methods
        |--------------------------------------------------------------------------
         */

        /**
         * WWOF_WWS_License_Manager constructor.
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWOF_WWS_License_Manager model.
         *
         * @access public
         * @since 1.11
         */
        public function __construct($dependencies)
        {}

        /**
         * Ensure that only one instance of WWOF_WWS_License_Manager is loaded or can be loaded (Singleton Pattern).
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWOF_WWS_License_Manager model.
         *
         * @return WWOF_WWS_License_Manager
         * @since 1.11
         */
        public static function instance($dependencies = null)
        {

            if (!self::$_instance instanceof self) {
                self::$_instance = new self($dependencies);
            }

            return self::$_instance;

        }

        /*
        |---------------------------------------------------------------------------------------------------------------
        | WooCommerce WholeSale Suite License Settings
        |---------------------------------------------------------------------------------------------------------------
         */

        /**
         * Add WWLC specific WWS license settings markup.
         *
         * @since 1.11
         * @access public
         */
        public function wwcLicenseSettingsPage()
        {

            ob_start();

            require_once WWOF_PLUGIN_DIR . 'views/wws-license-settings/view-wwof-wws-settings-page.php';

            echo ob_get_clean();

        }

        /*
        |---------------------------------------------------------------------------------------------------------------
        | AJAX
        |---------------------------------------------------------------------------------------------------------------
         */

        /**
         * Save wwof license details.
         *
         * @param null $license_details
         * @return bool
         *
         * @since 1.11 Updated to use new license manager
         */
        public function ajax_activate_license()
        {

            if (defined("DOING_AJAX") && !DOING_AJAX) {

                if (!isset($_POST['license_email']) || !isset($_POST['license_key']) || !isset($_POST['ajax_nonce'])) {

                    @header('Content-Type: application/json; charset=' . get_option('blog_charset'));
                    echo wp_json_encode(array('status' => 'fail', 'error_msg' => __('Required parameters not supplied', 'woocommerce-wholesale-order-form')));
                    wp_die();

                } elseif (!check_ajax_referer('wwof_activate_license', 'ajax_nonce', false)) {

                    @header('Content-Type: application/json; charset=' . get_option('blog_charset'));
                    echo wp_json_encode(array('status' => 'fail', 'error_msg' => __('Security check failed', 'woocommerce-wholesale-order-form')));
                    wp_die();

                }

            }

            if (isset($_POST['license_email']) && isset($_POST['license_key'])) {
                $activation_email = trim($_POST['license_email']);
                $license_key      = trim($_POST['license_key']);
            } else {
                $activation_email = is_multisite() ? get_site_option(WWOF_OPTION_LICENSE_EMAIL) : get_option(WWOF_OPTION_LICENSE_EMAIL);
                $license_key      = is_multisite() ? get_site_option(WWOF_OPTION_LICENSE_KEY) : get_option(WWOF_OPTION_LICENSE_KEY);
            }

            $activation_url = add_query_arg(array(
                'activation_email' => urlencode($activation_email),
                'license_key'      => $license_key,
                'site_url'         => home_url(),
                'software_key'     => 'WWOF',
                'multisite'        => is_multisite() ? 1 : 0,
            ), apply_filters('wwof_license_activation_url', WWOF_LICENSE_ACTIVATION_URL));

            // Store data even if not valid license
            if (is_multisite()) {

                update_site_option(WWOF_OPTION_LICENSE_EMAIL, $activation_email);
                update_site_option(WWOF_OPTION_LICENSE_KEY, $license_key);

            } else {

                update_option(WWOF_OPTION_LICENSE_EMAIL, $activation_email);
                update_option(WWOF_OPTION_LICENSE_KEY, $license_key);

            }

            $option = apply_filters('wwof_license_activation_options', array(
                'timeout' => 10, //seconds
                'headers' => array('Accept' => 'application/json'),
            ));

            $result = wp_remote_retrieve_body(wp_remote_get($activation_url, $option));

            if (empty($result)) {

                if (is_multisite()) {
                    delete_site_option(WWOF_LICENSE_EXPIRED);
                } else {
                    delete_option(WWOF_LICENSE_EXPIRED);
                }

                $response = array('status' => 'fail', 'error_msg' => __('Failed to activate license. Failed to connect to activation access point. Please contact plugin support.', 'woocommerce-wholesale-order-form'));

            } else {

                $result = json_decode($result);

                if (empty($result) || !property_exists($result, 'status')) {

                    if (is_multisite()) {
                        delete_site_option(WWOF_LICENSE_EXPIRED);
                    } else {
                        delete_option(WWOF_LICENSE_EXPIRED);
                    }

                    $response = array('status' => 'fail', 'error_msg' => __('Failed to activate license. Activation access point return invalid response. Please contact plugin support.', 'woocommerce-wholesale-order-form'));

                } else {

                    if ($result->status === 'success') {

                        if (is_multisite()) {

                            delete_site_option(WWOF_LICENSE_EXPIRED);
                            update_site_option(WWOF_LICENSE_ACTIVATED, 'yes');

                        } else {

                            delete_option(WWOF_LICENSE_EXPIRED);
                            update_option(WWOF_LICENSE_ACTIVATED, 'yes');

                        }

                        $response = array('status' => $result->status, 'success_msg' => $result->success_msg);

                    } else {

                        if (is_multisite()) {
                            update_site_option(WWOF_LICENSE_ACTIVATED, 'no');
                        } else {
                            update_option(WWOF_LICENSE_ACTIVATED, 'no');
                        }

                        $response = array('status' => $result->status, 'error_msg' => $result->error_msg);

                        // Remove any locally stored update data if there are any
                        $wp_site_transient = get_site_transient('update_plugins');

                        if ($wp_site_transient) {

                            $wwof_plugin_basename = 'woocommerce-wholesale-order-form/woocommerce-wholesale-order-form.php';

                            if (isset($wp_site_transient->checked) && is_array($wp_site_transient->checked) && array_key_exists($wwof_plugin_basename, $wp_site_transient->checked)) {
                                unset($wp_site_transient->checked[$wwof_plugin_basename]);
                            }

                            if (isset($wp_site_transient->response) && is_array($wp_site_transient->response) && array_key_exists($wwof_plugin_basename, $wp_site_transient->response)) {
                                unset($wp_site_transient->response[$wwof_plugin_basename]);
                            }

                            set_site_transient('update_plugins', $wp_site_transient);

                            wp_update_plugins();

                        }

                        // Check if this license is expired
                        if (property_exists($result, 'expiration_timestamp')) {

                            $response['expired_date'] = date('Y-m-d', $result->expiration_timestamp);

                            if (is_multisite()) {
                                update_site_option(WWOF_LICENSE_EXPIRED, $result->expiration_timestamp);
                            } else {
                                update_option(WWOF_LICENSE_EXPIRED, $result->expiration_timestamp);
                            }

                        } else {

                            if (is_multisite()) {
                                delete_site_option(WWOF_LICENSE_EXPIRED);
                            } else {
                                delete_option(WWOF_LICENSE_EXPIRED);
                            }

                        }

                    }

                }

            }

            do_action('wwof_ajax_activate_license', $response, $activation_email, $license_key);

            if (defined("DOING_AJAX") && DOING_AJAX) {
                @header('Content-Type: application/json; charset=' . get_option('blog_charset'));
                echo wp_json_encode($response);
                wp_die();
            } else {
                return $response;
            }

        }

        /**
         * AJAX dismiss activate notice.
         *
         * @since 1.11
         * @access public
         */
        public function ajax_dismiss_activate_notice()
        {

            if (!defined("DOING_AJAX") || !DOING_AJAX) {

                $response = array('status' => 'fail', 'error_msg' => __('Invalid AJAX Operation', 'woocommerce-wholesale-order-form'));

            } else {

                if (is_multisite()) {
                    update_site_option(WWOF_ACTIVATE_LICENSE_NOTICE, 'yes');
                } else {
                    update_option(WWOF_ACTIVATE_LICENSE_NOTICE, 'yes');
                }

                $response = array('status' => 'success');

            }

            @header('Content-Type: application/json; charset=' . get_option('blog_charset'));
            echo wp_json_encode($response);
            wp_die();

        }

        /*
        |---------------------------------------------------------------------------------------------------------------
        | Admin Notice
        |---------------------------------------------------------------------------------------------------------------
         */

        /**
         * Activate license notice.
         *
         * @since 1.11
         * @access public
         */
        public function activate_license_notice()
        {

            if (is_multisite()) {

                $license_activated    = get_site_option(WWOF_LICENSE_ACTIVATED);
                $license_notice_muted = get_site_option(WWOF_ACTIVATE_LICENSE_NOTICE);

            } else {

                $license_activated    = get_option(WWOF_LICENSE_ACTIVATED);
                $license_notice_muted = get_option(WWOF_ACTIVATE_LICENSE_NOTICE);

            }

            if (current_user_can('administrator') && $license_activated !== 'yes' && $license_notice_muted !== 'yes') {

                global $wp;

                if (is_multisite()) {

                    $wwof_license_settings_url = get_site_url() . "/wp-admin/network/admin.php?page=wws-ms-license-settings&tab=wwof";

                } else {

                    $wwof_license_settings_url = get_site_url() . "/wp-admin/options-general.php?page=wwc_license_settings&tab=wwof";

                    if (WWOF_Functions::is_wwp_v2()) {
                        $wwof_license_settings_url = get_site_url() . "/wp-admin/admin.php?page=wwc_license_settings&tab=wwof";
                    }

                }?>

                <div class="notice notice-error is-dismissible wwof-activate-license-notice">
                    <p class="wwof-activate-license-notice" style="font-size: 16px;">
                        <?php echo sprintf(__('Please <b><a href="%1$s">activate</a></b> your copy of <b>WooCommerce Wholesale Order Form</b> to get the latest updates and have access to support.', 'woocommerce-wholesale-order-form'), $wwof_license_settings_url); ?>
                    </p>
                </div>

                <script>
                    jQuery( document ).ready( function( $ ) {

                        $( '.wwof-activate-license-notice' ).on( 'click' , '.notice-dismiss' , function() {
                            $.post( window.ajaxurl, { action : 'wwof_slmw_dismiss_activate_notice' } );
                        } );

                    } );
                </script>

            <?php }

        }

        /**
         * Remove WWOF license upsell content.
         *
         * @param array $wwp_license_manager    The WWP_WWS_License_Manager instance
         * @since 2.0.3
         * @access public
         */
        public function remove_wwof_license_upsell_content($wwp_license_manager)
        {

            remove_action('wws_action_license_settings_wwof', array($wwp_license_manager, 'wwof_license_content'));

        }

        /*
        |--------------------------------------------------------------------------
        | Execute license manager
        |--------------------------------------------------------------------------
         */

        /**
         * Execute model.
         *
         * @since 1.11
         * @since 2.0.3 Remove adding license menu and tab. This will now be done in WWP.
         *              When WWOF is active. Remove upsell content and replace with license form.
         *
         * @access public
         */
        public function run()
        {

            // Ajax
            add_action('wp_ajax_wwof_activate_license', array($this, 'ajax_activate_license'));
            add_action('wp_ajax_wwof_slmw_dismiss_activate_notice', array($this, 'ajax_dismiss_activate_notice'));

            // Remove WWOF license Upsell when WWPP is active
            add_action('wwp_license_tab_and_contents', array($this, 'remove_wwof_license_upsell_content'));

            if (is_multisite()) {

                // Network admin notice
                add_action('network_admin_notices', array($this, 'activate_license_notice'));

                // Access license page if wwp and wwof are network active and accesing via the main blog url. Subsites will be blocked.
                if (is_plugin_active_for_network('woocommerce-wholesale-prices/woocommerce-wholesale-prices.bootstrap.php') &&
                    is_plugin_active_for_network('woocommerce-wholesale-order-form/woocommerce-wholesale-order-form.bootstrap.php') &&
                    get_current_blog_id() === 1) {

                    // Add WWS License Settings Page (WWOF)
                    add_action("wws_action_license_settings_wwof", array($this, 'wwcLicenseSettingsPage'));

                }

            } else {

                // Admin Notice
                add_action('admin_notices', array($this, 'activate_license_notice'));

                // Add WWS License Settings Page (WWOF)
                add_action("wws_action_license_settings_wwof", array($this, 'wwcLicenseSettingsPage'));

            }

        }

    }

}