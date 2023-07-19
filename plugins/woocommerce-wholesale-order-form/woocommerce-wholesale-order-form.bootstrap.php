<?php
/*
Plugin Name:            WooCommerce Wholesale Order Form
Plugin URI:             https://wholesalesuiteplugin.com/
Description:            WooCommerce Extension to Provide Wholesale Product Listing Functionality
Author:                 Rymera Web Co
Version:                2.1.1.1
Author URI:             http://rymera.com.au/
Text Domain:            woocommerce-wholesale-order-form
Requires at least:      5.0
Tested up to:           6.0.2
WC requires at least:   4.0
WC tested up to:        7.0
 */

require_once 'includes/class-wwof-functions.php';

// Delete code activation flag on plugin deactivate.
register_deactivation_hook(__FILE__, array(new WWOF_Functions, 'wwof_global_plugin_deactivate'));

/**
 * Check WWOF required plugins.
 * WWOF Requires the ff to be installed:
 * - WooCommerce
 * - WooCoomerce Wholesale Prices
 *
 * @since 2.0.3 We now require a minimum of WWP 2.1.3 to make WWOF working.
 *              This is needed to show the license menu and tab added in WWP 2.1.3.
 *              Coz in WWOF 2.0.3 we removed the adding of menu and tab.
 */
add_action('after_setup_theme', function () {

    $missing_required_plugins = WWOF_Functions::wwof_check_plugin_dependencies();
    $wwp_plugin_data          = get_plugin_data(WP_PLUGIN_DIR . '/woocommerce-wholesale-prices/woocommerce-wholesale-prices.bootstrap.php');

    // WooCommerce and WWP must be active
    // WWP should atleast 2.1.3 and up
    if (count($missing_required_plugins) <= 0 && version_compare($wwp_plugin_data['Version'], '2.1.3', '>=')) {

        // Include Necessary Files
        require_once 'woocommerce-wholesale-order-form.options.php';
        require_once 'woocommerce-wholesale-order-form.plugin.php';
        require_once 'includes/v2/class-order-form-helpers.php';

        // Get Instance of Main Plugin Class
        $wc_wholesale_order_form            = WooCommerce_WholeSale_Order_Form::instance();
        $GLOBALS['wc_wholesale_order_form'] = $wc_wholesale_order_form;

        /*
        |-------------------------------------------------------------------------------------------------------------------
        | Settings
        |-------------------------------------------------------------------------------------------------------------------
         */

        // Register Settings Page
        add_filter('woocommerce_get_settings_pages', array($wc_wholesale_order_form, 'wwof_plugin_settings'));

        /*
        |---------------------------------------------------------------------------------------------------------------
        | Execute WWOF
        |---------------------------------------------------------------------------------------------------------------
         */
        $wc_wholesale_order_form->run();

    } else {

        /**
         * Provide admin notice to users that a required plugin dependency of WooCommerce Wholesale Order Form plugin is missing.
         *
         * @since 1.6.3
         * @since 1.6.6 Underscore cased the function name and variables.
         * @since 2.0.3 Display a notice when WWP is lower than 2.1.3. We require the version because WWP will now be the one who will display the license menu and the license tab.
         */
        function wwof_admin_notices()
        {

            $missing_required_plugins = WWOF_Functions::wwof_check_plugin_dependencies();
            $wwp_plugin_data          = get_plugin_data(WP_PLUGIN_DIR . '/woocommerce-wholesale-prices/woocommerce-wholesale-prices.bootstrap.php');

            if (version_compare($wwp_plugin_data['Version'], '2.1.3', '<')) {

                global $current_user;

                $user_id          = $current_user->ID;
                $wwp_basename     = plugin_basename(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'woocommerce-wholesale-prices' . DIRECTORY_SEPARATOR . 'woocommerce-wholesale-prices.bootstrap.php');
                $wwp_install_text = sprintf(__('<a href="%1$s">Click here to update WooCommerce Wholesale Prices Plugin &rarr;</a>', 'woocommerce-wholesale-order-form'), wp_nonce_url('update.php?action=upgrade-plugin&plugin=' . $wwp_basename, 'upgrade-plugin_' . $wwp_basename));?>

                <div class="error">
                    <p><?php echo sprintf(__('<b>WooCommerce Wholesale Order Form</b><br/>Please ensure you have the latest version of <a href="%1$s" target="_blank">WooCommerce Wholesale Prices</a> plugin installed and activated along with the Premium extension.', 'woocommerce-wholesale-order-form'), 'http://wordpress.org/plugins/woocommerce-wholesale-prices/'); ?></p>
                    <p><?php echo $wwp_install_text; ?></p>
                </div><?php

            } else if (!empty($missing_required_plugins)) {

                $adminNoticeMsg = '';

                foreach ($missing_required_plugins as $plugin) {

                    $pluginFile = $plugin['plugin-base'];
                    $sptFile    = trailingslashit(WP_PLUGIN_DIR) . plugin_basename($pluginFile);

                    $sptInstallText = '<a href="' . wp_nonce_url('update.php?action=install-plugin&plugin=' . $plugin['plugin-key'], 'install-plugin_' . $plugin['plugin-key']) . '">' . __('Click here to install from WordPress.org repo &rarr;', 'woocommerce-wholesale-order-form') . '</a>';

                    if (file_exists($sptFile)) {
                        $sptInstallText = '<a href="' . wp_nonce_url('plugins.php?action=activate&amp;plugin=' . $pluginFile . '&amp;plugin_status=all&amp;s', 'activate-plugin_' . $pluginFile) . '" title="' . __('Activate this plugin', 'woocommerce-wholesale-order-form') . '" class="edit">' . __('Click here to activate &rarr;', 'woocommerce-wholesale-order-form') . '</a>';
                    }

                    $adminNoticeMsg .= sprintf(__('<br/>Please ensure you have the <a href="%1$s" target="_blank">%2$s</a> plugin installed and activated.<br/>', 'woocommerce-wholesale-order-form'), 'http://wordpress.org/plugins/' . $plugin['plugin-key'] . '/', str_replace('Woocommerce', 'WooCommerce', $plugin['plugin-name']));
                    $adminNoticeMsg .= $sptInstallText . '<br/>';
                }

                echo '<div class="error">';
                echo '<p>';
                echo __('<b>WooCommerce Wholesale Order Form</b> plugin missing dependency.<br/>', 'woocommerce-wholesale-order-form');
                echo $adminNoticeMsg;
                echo '</p>';
                echo '</div>';

            }

        }

        // Call admin notices
        add_action('admin_notices', 'wwof_admin_notices');

    }

});
