<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Model that houses the logic of updating the plugin.
 *
 * @since 1.17
 */
class WWPP_WWS_Update_Manager {

    /**
     * Class Properties
     */

    /**
     * Property that holds the single main instance of WWPP_WWS_Update_Manager.
     *
     * @since 1.17
     * @access private
     * @var WWPP_WWS_Update_Manager
     */
    private static $_instance;

    /**
     * Class Methods
     */

    /**
     * Class constructor.
     *
     * @since 1.17
     * @access public
     */
    public function __construct() {}

    /**
     * Ensure that only one instance of this class is loaded or can be loaded ( Singleton Pattern ).
     *
     * @since 1.17
     * @access public
     *
     * @return WWPP_WWS_Update_Manager
     */
    public static function instance() {
        if ( ! self::$_instance instanceof self ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Hijack the WordPress 'set_site_transient' function for 'update_plugins' transient.
     * So now we don't have our own cron to check for updates, we just rely on when WordPress check updates for plugins and themes,
     * and if WordPress does then sets the 'update_plugins' transient, then we hijack it and check for our own plugin update.
     *
     * @since 1.17
     * @access public
     *
     * @param object $update_plugins Update plugins data.
     */
    public function update_check( $update_plugins ) {

        /**
         * Function wp_update_plugins calls set_site_transient( 'update_plugins' , ... ) twice, yes twice
         * so we need to make sure we are on the last call before checking plugin updates
         * the last call will have the checked object parameter
         *
         * @since 1.26.5 Added flag condition to forcefully run update check via settings.
         */
        if ( isset( $update_plugins->checked ) ) {
            $this->ping_for_new_version( false );
        }

        /**
         * We try to inject plugin update data if it has any
         * This is to fix the issue about plugin info appearing/disappearing
         * when update page in WordPress is refreshed
         */
        $result = $this->inject_plugin_update(); // Inject new update data if there are any.

        $installed_version = is_multisite() ? get_site_option( WWPP_OPTION_INSTALLED_VERSION ) : get_option( WWPP_OPTION_INSTALLED_VERSION );

        // If the plugin is up to date then put the plugin in no update.
        if ( $result && isset( $result['value'] ) && version_compare( $result['value']->new_version, $installed_version, '==' ) ) {
            unset( $update_plugins->response[ $result['key'] ] );
            $update_plugins->no_update[ $result['key'] ] = $result['value'];
            return $update_plugins;
        }

        if ( $result && isset( $update_plugins->response ) && is_array( $update_plugins->response ) && ! array_key_exists( $result['key'], $update_plugins->response ) ) {
            $update_plugins->response[ $result['key'] ] = $result['value'];
        }

        return $update_plugins;
    }

    /**
     * Ping plugin for new version. Ping static file first, if indeed new version is available, trigger update data request.
     *
     * @since 1.17
     * @since 1.26.5 We added new parameter $force. This will serve as a flag if we are going to "forcefully" fetch the latest update data from the server.
     *
     * @param bool $force Flag to determine whether to "forcefully" fetch the latest update data from the server.
     * @access public
     */
    public function ping_for_new_version( $force = false ) {

        $license_activated = is_multisite() ? get_site_option( WWPP_LICENSE_ACTIVATED ) : get_option( WWPP_LICENSE_ACTIVATED );

        if ( 'yes' !== $license_activated ) {
            if ( is_multisite() ) {
                delete_site_option( WWPP_UPDATE_DATA );
            } else {
                delete_option( WWPP_UPDATE_DATA );
            }

            return;
        }

        $retrieving_update_data = is_multisite() ? get_site_option( WWPP_RETRIEVING_UPDATE_DATA ) : get_option( WWPP_RETRIEVING_UPDATE_DATA );
        if ( 'yes' === $retrieving_update_data ) {
            return;
        }

        /**
         * Only attempt to get the existing saved update data when the operation is not forced.
         * Else, if it is forced, we ignore the existing update data if any
         * and forcefully fetch the latest update data from our server.
         *
         * @since 1.26.5
         */
        if ( ! $force ) {
            $update_data = is_multisite() ? get_site_option( WWPP_UPDATE_DATA ) : get_option( WWPP_UPDATE_DATA );
        } else {
            $update_data = null;
        }

        /**
         * Even if the update data is still valid, we still go ahead and do static json file ping.
         * The reason is on WooCommerce 3.3.x , it seems WooCommerce do not regenerate the download url every time you change the downloadable zip file on WooCommerce store.
         * The side effect is, the download url is still valid, points to the latest zip file, but the update info could be outdated coz we check that if the download url
         * is still valid, we don't check for update info, and since the download url will always be valid even after subsequent release of the plugin coz WooCommerce is reusing the url now
         * then there will be a case our update info is outdated. So that is why we still need to check the static json file, even if update info is still valid.
         */

        $option = apply_filters(
            'wwpp_plugin_new_version_ping_options',
            array(
				'timeout' => 10, // Seconds.
				'headers' => array( 'Accept' => 'application/json' ),
            )
        );

        $response = wp_remote_retrieve_body( wp_remote_get( apply_filters( 'wwpp_plugin_new_version_ping_url', WWPP_STATIC_PING_FILE ), $option ) );

        if ( ! empty( $response ) ) {
            $response = json_decode( $response );

            if ( ! empty( $response ) && property_exists( $response, 'version' ) ) {
                $installed_version = is_multisite() ? get_site_option( WWPP_OPTION_INSTALLED_VERSION ) : get_option( WWPP_OPTION_INSTALLED_VERSION );

                if ( ( ! $update_data && version_compare( $response->version, $installed_version, '>' ) ) ||
                    ( $update_data && version_compare( $response->version, $update_data->latest_version, '>' ) ) ) {

                    if ( is_multisite() ) {
                        update_site_option( WWPP_RETRIEVING_UPDATE_DATA, 'yes' );
                    } else {
                        update_option( WWPP_RETRIEVING_UPDATE_DATA, 'yes' );
                    }

                    // Fetch software product update data.
                    if ( is_multisite() ) {
                        $this->_fetch_software_product_update_data( get_site_option( WWPP_OPTION_LICENSE_EMAIL ), get_site_option( WWPP_OPTION_LICENSE_KEY ), home_url() );
                    } else {
                        $this->_fetch_software_product_update_data( get_option( WWPP_OPTION_LICENSE_EMAIL ), get_option( WWPP_OPTION_LICENSE_KEY ), home_url() );
                    }

                    if ( is_multisite() ) {
                        delete_site_option( WWPP_RETRIEVING_UPDATE_DATA );
                    } else {
                        delete_option( WWPP_RETRIEVING_UPDATE_DATA );
                    }
                }
            }
        }
    }

    /**
     * Fetch software product update data.
     *
     * @since 1.17
     * @access public
     *
     * @param string $activation_email Activation email.
     * @param string $license_key      License key.
     * @param string $site_url         Site url.
     */
    private function _fetch_software_product_update_data( $activation_email, $license_key, $site_url ) {

        $update_check_url = add_query_arg(
            array(
				'activation_email' => rawurlencode( $activation_email ),
				'license_key'      => $license_key,
				'site_url'         => $site_url,
				'software_key'     => 'WWPP',
				'multisite'        => is_multisite() ? 1 : 0,
            ),
            apply_filters( 'wwpp_software_product_update_data_url', WWPP_UPDATE_DATA_URL )
        );

        $option = apply_filters(
            'wwpp_software_product_update_data_options',
            array(
				'timeout' => 30, // Seconds.
				'headers' => array( 'Accept' => 'application/json' ),
            )
        );

        $result = wp_remote_retrieve_body( wp_remote_get( $update_check_url, $option ) );

        if ( ! empty( $result ) ) {
            $result = json_decode( $result );

            if ( ! empty( $result ) && 'success' === $result->status && ! empty( $result->software_update_data ) ) {
                if ( is_multisite() ) {
                    update_site_option( WWPP_UPDATE_DATA, $result->software_update_data );
                } else {
                    update_option( WWPP_UPDATE_DATA, $result->software_update_data );
                }
            } else {
                if ( is_multisite() ) {
                    delete_site_option( WWPP_UPDATE_DATA );
                } else {
                    delete_option( WWPP_UPDATE_DATA );
                }

                if ( ! empty( $result ) && 'fail' === $result->status &&
                    isset( $result->error_key ) &&
                    in_array( $result->error_key, array( 'invalid_license', 'expired_license' ), true ) ) {

                    // Invalid License.
                    if ( is_multisite() ) {
                        update_site_option( WWPP_LICENSE_ACTIVATED, 'no' );
                    } else {
                        update_option( WWPP_LICENSE_ACTIVATED, 'no' );
                    }

                    // Check if license is expired.
                    if ( 'expired_license' === $result->error_key ) {
                        if ( is_multisite() ) {
                            update_site_option( WWPP_LICENSE_EXPIRED, $result->expiration_timestamp );
                        } else {
                            update_option( WWPP_LICENSE_EXPIRED, $result->expiration_timestamp );
                        }
                    } else {
                        if ( is_multisite() ) {
                            delete_site_option( WWPP_LICENSE_EXPIRED );
                        } else {
                            delete_option( WWPP_LICENSE_EXPIRED );
                        }
                    }
                }
            }

            // Fire post product update data hook.
            do_action( 'wwpp_software_product_update_data', $result, $activation_email, $license_key );
        }
    }

    /**
     * Inject plugin update info to plugin update details page.
     * Note, this is only triggered when there is a new update and the "View version <new version here> details" link is clicked.
     * In short, the pure purpose for this is to provide details and info the update info popup.
     *
     * @since 1.17
     * @access public
     *
     * @param false|object|array $result The result object or array. Default false.
     * @param string             $action The type of information being requested from the Plugin Install API.
     * @param object             $args   Plugin API arguments.
     * @return array Plugin update info.
     */
    public function inject_plugin_update_info( $result, $action, $args ) {

        $license_activated = is_multisite() ? get_site_option( WWPP_LICENSE_ACTIVATED ) : get_option( WWPP_LICENSE_ACTIVATED );

        if ( 'yes' === $license_activated && 'plugin_information' === $action && isset( $args->slug ) && 'woocommerce-wholesale-prices-premium' === $args->slug ) {
            $software_update_data = is_multisite() ? get_site_option( WWPP_UPDATE_DATA ) : get_option( WWPP_UPDATE_DATA );

            if ( $software_update_data ) {

                // Create update info object.
                $update_info                       = new \StdClass();
                $update_info->name                 = 'WooCommerce Wholesale Prices Premium';
                $update_info->slug                 = 'woocommerce-wholesale-prices-premium';
                $update_info->version              = $software_update_data->latest_version;
                $update_info->tested               = $software_update_data->tested_up_to;
                $update_info->last_updated         = $software_update_data->last_updated;
                $update_info->homepage             = $software_update_data->home_page;
                $update_info->author               = sprintf( '<a href="%s" target="_blank">%s</a>', $software_update_data->author_url, $software_update_data->author );
                $update_info->download_link        = $software_update_data->download_url;
                $update_info->{'update-supported'} = true;
                $update_info->sections             = array(
                    'description'  => $software_update_data->description,
                    'installation' => $software_update_data->installation,
                    'changelog'    => $software_update_data->changelog,
                    'support'      => $software_update_data->support,
                );

                $update_info->icons = array(
                    '1x'      => 'https://ps.w.org/woocommerce-wholesale-prices/assets/wwpp-icon-128x128.jpg',
                    '2x'      => 'https://ps.w.org/woocommerce-wholesale-prices/assets/wwpp-icon-256x256.jpg',
                    'default' => 'https://ps.w.org/woocommerce-wholesale-prices/assets/wwpp-icon-256x256.jpg',
                );

                $update_info->banners = array(
                    'low'  => 'https://ps.w.org/woocommerce-wholesale-prices/assets/wwpp-banner-772x250.jpg',
                    'high' => 'https://ps.w.org/woocommerce-wholesale-prices/assets/wwpp-banner-1544x500.jpg',
                );

                return $update_info;
            }
        }

        return $result;
    }

    /**
     * When WordPress fetches 'update_plugins' transient (which holds data regarding plugins that have updates), we
     * inject our plugin update data in, if we have any. This data is saved in the WWPP_UPDATE_DATA option. It is
     * important we don't delete this option until the plugin has successfully updated. We do this because we are
     * hooking on transient read. So if the option gets deleted on first transient read, then subsequent reads will not
     * include our plugin update data.
     *
     * This function also checks the validity of the update url to cover the edge case where we may have stored the
     * update data locally as an option, then later on the update data became invalid. So as a safety mechanism we check
     * if the update url is still valid and if not we remove the locally stored update data.
     *
     * @since 1.17 Refactor codebase to adapt being called on set_site_transient function. We don't need to check for
     * software update data validity as its already been checked on ping_for_new_version and this function is
     * immediately called right after that.
     * @access public
     * @return array Filtered plugin updates data.
     */
    public function inject_plugin_update() {
        $license_activated = is_multisite() ? get_site_option( WWPP_LICENSE_ACTIVATED ) : get_option( WWPP_LICENSE_ACTIVATED );
        if ( 'yes' !== $license_activated ) {
            return false;
        }

        $software_update_data = is_multisite() ? get_site_option( WWPP_UPDATE_DATA ) : get_option( WWPP_UPDATE_DATA );

        if ( $software_update_data ) {

            // Create update info object.
            $update                       = new \stdClass();
            $update->name                 = 'WooCommerce Wholesale Prices Premium';
            $update->id                   = $software_update_data->download_id;
            $update->slug                 = 'woocommerce-wholesale-prices-premium';
            $update->plugin               = WWPP_PLUGIN_BASE_NAME;
            $update->new_version          = $software_update_data->latest_version;
            $update->url                  = WWPP_PLUGIN_SITE_URL;
            $update->package              = $software_update_data->download_url;
            $update->tested               = $software_update_data->tested_up_to;
            $update->{'update-supported'} = true;
            $update->update               = false;
            $update->icons                = array(
                '1x'      => 'https://ps.w.org/woocommerce-wholesale-prices/assets/wwpp-icon-128x128.jpg',
                '2x'      => 'https://ps.w.org/woocommerce-wholesale-prices/assets/wwpp-icon-256x256.jpg',
                'default' => 'https://ps.w.org/woocommerce-wholesale-prices/assets/wwpp-icon-256x256.jpg',
            );

            $update->banners = array(
                '1x'      => 'https://ps.w.org/woocommerce-wholesale-prices/assets/wwpp-banner-772x250.jpg',
                '2x'      => 'https://ps.w.org/woocommerce-wholesale-prices/assets/wwpp-banner-1544x500.jpg',
                'default' => 'https://ps.w.org/woocommerce-wholesale-prices/assets/wwpp-banner-1544x500.jpg',
            );

            return array(
                'key'   => WWPP_PLUGIN_BASE_NAME,
                'value' => $update,
            );

        }

        return false;
    }

    /**
     * Force fetch the latest update data from our server.
     *
     * @since 1.26.5
     * @access public
     */
    public function wwpp_force_fetch_update_data() {

        if ( ( ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) ||
            ! wp_verify_nonce( $_REQUEST['nonce'], 'wwpp_force_fetch_update_data_nonce' ) ||
            ! current_user_can( 'manage_woocommerce' ) ) {
            @header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) ); // phpcs:ignore
            echo wp_json_encode(
                array(
                    'status'  => 'error',
                    'message' => 'Security check failure.',
                )
            );

            wp_die();
        }

        /**
         * Force check and fetch the update data of the plugin.
         * Will save the update data into the WWPP_UPDATE_DATA option.
         */
        $this->ping_for_new_version( true ); // Force check.

        /**
         * Get the update data from the WWPP_UPDATE_DATA option.
         * Returned data is pre-formatted.
         */
        $update_data       = $this->inject_plugin_update(); // Inject new update data if there are any.
        $installed_version = is_multisite() ? get_site_option( WWPP_OPTION_INSTALLED_VERSION ) : get_option( WWPP_OPTION_INSTALLED_VERSION );

        /**
         * Get wp update transient data.
         * Automatically unserializes the returned value.
         */
        $update_transient = is_multisite() ? get_site_option( '_site_transient_update_plugins', false ) : get_option( '_site_transient_update_plugins', false );

        // If the plugin is up to date then put the plugin in no update.
        if ( $update_data && isset( $update_data['value'] ) && version_compare( $update_data['value']->new_version, $installed_version, '==' ) ) {

            unset( $update_transient->response[ $update_data['key'] ] );
            $update_transient->no_update[ $update_data['key'] ] = $update_data['value'];

        } elseif ( $update_data && $update_transient && isset( $update_transient->response ) && is_array( $update_transient->response ) ) {

            // Inject into the wp update data our latest plugin update data.
            $update_transient->response[ $update_data['key'] ] = $update_data['value'];

        }

        // Update wp update data transient.
        if ( is_multisite() ) {
            update_site_option( '_site_transient_update_plugins', $update_transient );
        } else {
            update_option( '_site_transient_update_plugins', $update_transient );
        }

        @header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) ); // phpcs:ignore
        echo wp_json_encode( array( 'status' => 'success' ) );
        wp_die();
    }

    /**
     * Execute Model.
     *
     * @since 1.17
     * @access public
     */
    public function run() {
        add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'update_check' ) );
        add_filter( 'plugins_api', array( $this, 'inject_plugin_update_info' ), 10, 3 );
        add_action( 'wp_ajax_wwpp_force_fetch_update_data', array( $this, 'wwpp_force_fetch_update_data' ) );
    }
}
