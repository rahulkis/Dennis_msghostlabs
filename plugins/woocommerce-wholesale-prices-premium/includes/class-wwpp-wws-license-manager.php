<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Wholesale Prices Premium License Manager
 */
class WWPP_WWS_License_Manager {

    /**
     * Class Properties
     */

    /**
     * Property that holds the single main instance of WWPP_WWS_License_Manager.
     *
     * @since 1.17
     * @access private
     * @var WWPP_WWS_License_Manager
     */
    private static $_instance;

    /**
     * Class Methods
     */

    /**
     * WWPP_WWS_License_Manager constructor.
     *
     * @param array $dependencies Array of instance objects of all dependencies of WWPP_WWS_License_Manager model.
     *
     * @access public
     * @since 1.17
     */
    public function __construct( $dependencies ) {}

    /**
     * Ensure that only one instance of WWPP_WWS_License_Manager is loaded or can be loaded (Singleton Pattern).
     *
     * @param array $dependencies Array of instance objects of all dependencies of WWPP_WWS_License_Manager model.
     *
     * @return WWPP_WWS_License_Manager
     * @since 1.17
     */
    public static function instance( $dependencies = null ) {
        if ( ! self::$_instance instanceof self ) {
            self::$_instance = new self( $dependencies );
        }

        return self::$_instance;
    }

    /**
     * Wholesale Suite License Settings
     */

    /**
     * Add WWPP specific WWS license settings markup.
     *
     * @since 1.0.1
     * @access public
     */
    public function wws_license_settings_page() {
        ob_start();
        require_once WWPP_PLUGIN_PATH . 'views/wws-license-settings/wwpp-view-wss-settings-page.php';
        wp_ob_end_flush_all();
    }

    /**
     * AJAX FUNCTIONS
     */

    /**
     * Save and activate Wholesale Prices Premium license details.
     *
     * @since 1.0.1
     * @since 1.11 Updated to use new license manager
     */
    public function ajax_activate_license() {
        // Make sure we're doing ajax.
        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

            // Check nonce and fail gracefully if invalid.
            if ( ! check_ajax_referer( 'wwpp_activate_license', 'ajax_nonce', false ) ) {
                @header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) ); // phpcs:ignore
                echo wp_json_encode(
                    array(
                        'status'    => 'fail',
                        'error_msg' => __(
                            'Security check failed',
                            'woocommerce-wholesale-prices-premium'
                        ),
                    )
                );
                wp_die();
            }

            // Check passed in values and fail gracefully if invalid.
            if ( ! isset( $_REQUEST['license_email'] ) || ! isset( $_REQUEST['license_key'] ) ) {
                @header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) ); // phpcs:ignore
                echo wp_json_encode(
                    array(
                        'status'    => 'fail',
                        'error_msg' => __(
                            'Required parameters not supplied',
                            'woocommerce-wholesale-prices-premium'
                        ),
                    )
                );
                wp_die();
            }

            if ( isset( $_REQUEST['license_email'] ) && isset( $_REQUEST['license_key'] ) ) {
                $activation_email = trim( sanitize_email( $_REQUEST['license_email'] ) );
                $license_key      = trim( sanitize_text_field( $_REQUEST['license_key'] ) );
            } else {
                $activation_email = is_multisite() ? get_site_option( WWPP_OPTION_LICENSE_EMAIL ) : get_option( WWPP_OPTION_LICENSE_EMAIL );
                $license_key      = is_multisite() ? get_site_option( WWPP_OPTION_LICENSE_KEY ) : get_option( WWPP_OPTION_LICENSE_KEY );
            }

            $activation_url = add_query_arg(
                array(
                    'activation_email' => rawurlencode( $activation_email ),
                    'license_key'      => $license_key,
                    'site_url'         => home_url(),
                    'software_key'     => 'WWPP',
                    'multisite'        => is_multisite() ? 1 : 0,
                ),
                apply_filters( 'wwpp_license_activation_url', WWPP_LICENSE_ACTIVATION_URL )
            );

            // Store data even if not valid license.
            if ( is_multisite() ) {

                update_site_option( WWPP_OPTION_LICENSE_EMAIL, $activation_email );
                update_site_option( WWPP_OPTION_LICENSE_KEY, $license_key );

            } else {

                update_option( WWPP_OPTION_LICENSE_EMAIL, $activation_email );
                update_option( WWPP_OPTION_LICENSE_KEY, $license_key );

            }

            $option = apply_filters(
                'wwpp_license_activation_options',
                array(
                    'timeout' => 10, // Seconds.
                    'headers' => array( 'Accept' => 'application/json' ),
                )
            );

            $result = wp_remote_retrieve_body( wp_remote_get( $activation_url, $option ) );

            // Activation point failed to send a response.
            if ( empty( $result ) ) {

                if ( is_multisite() ) {
                    delete_site_option( WWPP_LICENSE_EXPIRED );
                } else {
                    delete_option( WWPP_LICENSE_EXPIRED );
                }

                $response = array(
                    'status'    => 'fail',
                    'error_msg' => __( 'Failed to activate license. Failed to connect to activation access point. Please contact plugin support.', 'woocommerce-wholesale-prices-premium' ),
                );

            } else {

                // Received a response from the activation point.
                $result = json_decode( $result );

                if ( empty( $result ) || ! property_exists( $result, 'status' ) ) {
                    if ( is_multisite() ) {
                        delete_site_option( WWPP_LICENSE_EXPIRED );
                    } else {
                        delete_option( WWPP_LICENSE_EXPIRED );
                    }

                    $response = array(
                        'status'    => 'fail',
                        'error_msg' => __( 'Failed to activate license. Activation access point returned invalid response. Please contact plugin support.', 'woocommerce-wholesale-prices-premium' ),
                    );
                } else {
                    if ( 'success' === $result->status ) {
                        if ( is_multisite() ) {
                            delete_site_option( WWPP_LICENSE_EXPIRED );
                            update_site_option( WWPP_LICENSE_ACTIVATED, 'yes' );
                        } else {
                            delete_option( WWPP_LICENSE_EXPIRED );
                            update_option( WWPP_LICENSE_ACTIVATED, 'yes' );
                        }

                        $response = array(
                            'status'      => $result->status,
                            'success_msg' => $result->success_msg,
                        );
                    } else {
                        if ( is_multisite() ) {
                            update_site_option( WWPP_LICENSE_ACTIVATED, 'no' );
                        } else {
                            update_option( WWPP_LICENSE_ACTIVATED, 'no' );
                        }

                        $response = array(
                            'status'    => $result->status,
                            'error_msg' => $result->error_msg,
                        );

                        // Remove any locally stored update data if there are any.
                        $wp_site_transient = get_site_transient( 'update_plugins' );

                        if ( $wp_site_transient ) {
                            $wwpp_plugin_basename = WWPP_PLUGIN_BASE_NAME;

                            if ( isset( $wp_site_transient->checked ) && is_array( $wp_site_transient->checked ) && array_key_exists( $wwpp_plugin_basename, $wp_site_transient->checked ) ) {
                                unset( $wp_site_transient->checked[ $wwpp_plugin_basename ] );
                            }

                            if ( isset( $wp_site_transient->response ) && is_array( $wp_site_transient->response ) && array_key_exists( $wwpp_plugin_basename, $wp_site_transient->response ) ) {
                                unset( $wp_site_transient->response[ $wwpp_plugin_basename ] );
                            }

                            set_site_transient( 'update_plugins', $wp_site_transient );
                            wp_update_plugins();
                        }

                        // Check if this license is expired.
                        if ( property_exists( $result, 'expiration_timestamp' ) ) {

                            // Always store in GMT in database.
                            $response['expired_date'] = gmdate( 'Y-m-d', $result->expiration_timestamp );

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
            }

            // Fire post activation attempt hook.
            do_action( 'wwpp_ajax_activate_license', $response, $activation_email, $license_key );

            // Return AJAX response.
            @header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) ); // phpcs:ignore
            echo wp_json_encode( $response );
            wp_die();
        }
    }

    /**
     * AJAX dismiss activate notice.
     *
     * @since 1.11
     * @access public
     */
    public function ajax_dismiss_activate_notice() {
        // Check this is an AJAX operation and that user is able to manage WC settings.
        if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX || ! current_user_can( 'manage_woocommerce' ) ) {
            $response = array(
                'status'    => 'fail',
                'error_msg' => __( 'Invalid AJAX Operation', 'woocommerce-wholesale-prices-premium' ),
            );
        } else {

            // Deactivate the license notice.
            if ( is_multisite() ) {
                update_site_option( WWPP_ACTIVATE_LICENSE_NOTICE, 'yes' );
            } else {
                update_option( WWPP_ACTIVATE_LICENSE_NOTICE, 'yes' );
            }

            $response = array( 'status' => 'success' );
        }

        // Return AJAX response.
        @header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) ); // phpcs:ignore
        echo wp_json_encode( $response );
        wp_die();
    }

    /**
     * Admin Notice
     */

    /**
     * Activate license notice.
     *
     * @since 1.11
     * @since 1.28 The WWPP license activation notice should only be shown for admin user
     * @access public
     */
    public function activate_license_notice() {
        $license_activated    = is_multisite() ? get_site_option( WWPP_LICENSE_ACTIVATED ) : get_option( WWPP_LICENSE_ACTIVATED );
        $license_notice_muted = is_multisite() ? get_site_option( WWPP_ACTIVATE_LICENSE_NOTICE ) : get_option( WWPP_ACTIVATE_LICENSE_NOTICE );

        if ( current_user_can( 'manage_woocommerce' ) && 'yes' !== $license_activated && 'yes' !== $license_notice_muted ) {
            if ( is_multisite() ) {
                $wwpp_license_settings_url = get_site_url() . '/wp-admin/network/admin.php?page=wws-ms-license-settings&tab=wwpp';
            } else {
                $wwpp_license_settings_url = get_site_url() . '/wp-admin/options-general.php?page=wwc_license_settings&tab=wwpp';

                if ( method_exists( 'WWP_Helper_Functions', 'is_wwp_v2' ) && WWP_Helper_Functions::is_wwp_v2() ) {
                    $wwpp_license_settings_url = get_site_url() . '/wp-admin/admin.php?page=wwc_license_settings&tab=wwpp';
                }
            }
            ?>

            <div class="notice notice-error is-dismissible wwpp-activate-license-notice">
                <p class="wwpp-activate-license-notice" style="font-size: 16px;">
                    <?php
                    printf(
                        /* Translators: %1$s is <a> tag html, %2$s is closing </a>, %3$s is opening <b> tag, %4$s is closing </b> tag */
                        esc_html__( 'Please %1$sactivate%2$s your copy of %3$sWooCommerce Wholesale Prices Premium%4$s to get the latest updates and have access to support.', 'woocommerce-wholesale-prices-premium' ),
                        '<b><a href="' . esc_url( $wwpp_license_settings_url ) . '">',
                        '</a></b>',
                        '<b>',
                        '</b>'
                    );
                    ?>
                </p>
            </div>

            <script type="text/javascript">
                jQuery( document ).ready( function( $ ) {
                    $( '.wwpp-activate-license-notice' ).on( 'click' , '.notice-dismiss' , function() {
                        $.post( window.ajaxurl, { action : 'wwpp_slmw_dismiss_activate_notice' } );
                    } );
                } );
            </script>

        <?php
        }
    }

    /**
     * Remove WWP license upsell content.
     *
     * @param array $wwp_license The WWP_WWS_License_Manager instance.
     * @since 1.27.11
     * @access public
     */
    public function remove_wwpp_license_upsell_content( $wwp_license ) {
        remove_action( 'wws_action_license_settings_wwpp', array( $wwp_license, 'wwpp_license_content' ) );
    }

    /**
     * Execute model.
     *
     * @since 1.11
     * @access public
     */
    public function run() {
        // Register AJAX endpoints.
        add_action( 'wp_ajax_wwpp_activate_license', array( $this, 'ajax_activate_license' ) );
        add_action( 'wp_ajax_wwpp_slmw_dismiss_activate_notice', array( $this, 'ajax_dismiss_activate_notice' ) );

        // Remove WWP license upsell when WWPP is active.
        add_action( 'wwp_license_tab_and_contents', array( $this, 'remove_wwpp_license_upsell_content' ) );

        if ( is_multisite() ) {
            // Network admin notice.
            add_action( 'network_admin_notices', array( $this, 'activate_license_notice' ) );

            // Access license page if wwp and wwpp are network active and accesing via the main blog url. Subsites will be blocked.
            if ( is_plugin_active_for_network( 'woocommerce-wholesale-prices/woocommerce-wholesale-prices.bootstrap.php' ) &&
                is_plugin_active_for_network( 'woocommerce-wholesale-prices-premium/woocommerce-wholesale-prices-premium.bootstrap.php' ) &&
                get_current_blog_id() === 1 ) {

                // Add WWS License Settings Page (WWPP).
                add_action( 'wws_action_license_settings_wwpp', array( $this, 'wws_license_settings_page' ) );

            }
        } else {
            // Add WWS License Settings Page (WWPP).
            add_action( 'wws_action_license_settings_wwpp', array( $this, 'wws_license_settings_page' ) );

            // Admin Notice.
            add_action( 'admin_notices', array( $this, 'activate_license_notice' ) );
        }
    }
}
