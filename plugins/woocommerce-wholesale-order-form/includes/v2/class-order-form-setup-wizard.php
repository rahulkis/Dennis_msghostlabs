<?php if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Exit if accessed directly

if ( ! class_exists( 'Order_Form_Setup_Wizard' ) ) {

    class Order_Form_Setup_Wizard {

        /**
         * Cron hook that displays the notice again.
         *
         * @since 2.0
         */
        const CRON_HOOK = 'wwof_cron_display_wizard_notice';

        /*
        |--------------------------------------------------------------------------
        | Class Properties
        |--------------------------------------------------------------------------
         */

        public function __construct() {

            // Load Backend CSS and JS
            add_action( 'admin_enqueue_scripts', array( $this, 'load_back_end_styles_and_scripts' ) );

            // Order Form WC Submenu
            add_action( 'admin_menu', array( $this, 'add_setup_wizard_page' ), 99 );

            // Call admin notices
            add_action( 'admin_notices', array( $this, 'setup_wizard_admin_notices' ) );

            // Dismiss migration and setup wizard notice.
            add_action( 'wp_ajax_wwof_dismiss_setup_wizard_notice', array( $this, 'dismiss_setup_wizard_notice' ) );

            // Redirect to setup wizard for fresh installs
            // add_action('activated_plugin', array($this, 'redirect_to_setup_wizard'), 10, 2);

            // Calling the function on admin-init action hook
            add_action( 'admin_init', array( $this, 'redirect_to_setup_wizard' ) );

            // Display setup/migration wizard after 7 days since dismissed.
            add_action( self::CRON_HOOK, array( $this, 'cron_display_wizard_notice' ) );
        }

        /**
         * Load Admin or Backend Related Styles and Scripts.
         *
         * @since 2.0
         */
        public function load_back_end_styles_and_scripts( $handle ) {

            if ( get_option( WWOF_WIZARD_SETUP_DONE ) != 'yes' && $handle === 'admin_page_order-forms-setup-wizard' ) {

                global $wc_wholesale_prices;

                $wholesale_role = ! empty( $wc_wholesale_prices ) ? $wc_wholesale_prices->wwp_wholesale_roles->getUserWholesaleRole(
                ) : '';

                // Important: Must enqueue this script in order to use WP REST API via JS
                wp_enqueue_script( 'wp-api' );

                wp_localize_script(
                    'wp-api',
                    'setup_wizard_options',
                    array(
                        'ajaxurl' => admin_url( 'admin-ajax.php' ),
                        'root' => esc_url_raw( rest_url() ),
                        'nonce' => wp_create_nonce( 'wp_rest' ),
                        'security_generate_key' => wp_create_nonce( 'update-api-key' ),
                        'uid' => get_current_user_id(),
                        'description' => 'WWOF v2',
                        'migration' => ! Order_Form_Helpers::is_fresh_install(),
                        // Only do migration if not fresh install
                    )
                );

                // Setup Wizard Scripts
                $args = array(
                    'app_name' => 'setup-wizard',
                );

                Order_Form_Scripts::load_react_order_form_scripts( $args );
            }
        }

        /**
         * Add Setup / Migration Wizard Page
         *
         * @since 2.0
         */
        public function add_setup_wizard_page() {

            // Check if the Setup/Migration is already done then dont display the page content
            if ( get_option( WWOF_WIZARD_SETUP_DONE ) === 'yes' ) {
                return;
            }

            $page_title = isset( $_GET['migration'] ) && $_GET['migration'] == true ? __(
                'Order Form Setup Wizard - Migration',
                'woocommerce-wholesale-order-form'
            ) : __( 'Order Form Setup Wizard', 'woocommerce-wholesale-order-form' );

            add_submenu_page(
                $page_title,
                $page_title,
                $page_title,
                'manage_options',
                'order-forms-setup-wizard',
                array( $this, 'setup_wizard_content' )
            );
        }

        /**
         * Element wrapper for displaying react elements
         *
         * @since 2.0
         */
        public function setup_wizard_content() {

            echo '<div id="wwof-setup-wizard"></div>';
        }

        /**
         * Setup wizard notices.
         *
         * @since 2.0
         */
        public function setup_wizard_admin_notices() {

            // Check if current user is not admin or shop manager then dont show the notice
            if ( ! ( current_user_can( 'administrator' ) || ! current_user_can( 'shop_manager' ) ) ) {
                return;
            }

            if ( apply_filters( 'wwof_display_admin_wizard_notice', true ) ) {

                $screen = get_current_screen();

                // Don't show on other wc settings page other than wwof settings
                if ( isset( $_GET['page'] ) && $_GET['page'] == 'wc-settings' && isset( $_GET['tab'] ) && $_GET['tab'] !== 'wwof_settings' ) {
                    return;
                }

                // Don't display the notice if the setup is already finished
                if ( get_option( WWOF_WIZARD_SETUP_DONE ) === 'yes' ) {
                    return;
                }

                // Show in WWOF settings page
                if ( isset( $_GET['tab'] ) && $_GET['tab'] === 'wwof_settings' ) {

                    // Persistent Migration Notice in the Order Form settings tab
                    $this->persistent_wizard_notice();
                } elseif (
                    strpos( $screen->base, 'wholesale_page_' ) !== false ||
                    in_array( $screen->id, array( 'settings_page_wwc_license_settings', 'plugins' ) ) ||
                    $screen->post_type === 'product' ||
                    in_array( $screen->parent_base, array( 'woocommerce' ) )
                ) {

                    // Only display this under these conditions.
                    // Condition: - if under Wholesale pages
                    // - if products pages
                    // - if woocommerce pages ( wc, products, analytics, etc.)
                    // - if plugins page

                    if ( get_option( WWOF_DISPLAY_WIZARD_NOTICE ) == 'no' ) {
                        return;
                    }

                    // Check if WWOF is not fresh install and has Template Overrides or WPML or Addons active then dont display the notice
                    if ( ! Order_Form_Helpers::is_fresh_install() ) {

                        // Check if WWOF has Template Overrides or WPML or Addons or Aelia active then dont display the notice
                        if ( Order_Form_Helpers::has_wpml_active() || Order_Form_Helpers::has_addons_active(
                            ) || Order_Form_Helpers::has_template_overrides() ) {
                            return;
                        }

                        $this->migration_wizard_notice();
                    } else {
                        $this->setup_wizard_notice();
                    }
                }
            }
        }

        /**
         * Persistent setup wizard notice under the order form settings.
         * Displays the migration wizard notice.
         * There will be no setup wizard notice for fresh installs.
         *
         * @since 2.0
         */
        public function persistent_wizard_notice() {

            if ( ! Order_Form_Helpers::is_fresh_install() ) {
                echo '<div class="updated notice setup-wizard-notice-wrapper">';
                echo '<p><img src="' . WWOF_IMAGES_ROOT_URL . 'wholesale-suite-activation-notice-logo.png" alt=""/></p>';
                echo '<p  style="margin-bottom: 20px;">';
                echo __(
                    'We detected that you are still using old order form, please migrate to new order form as this will be phased out soon.',
                    'woocommerce-wholesale-order-form'
                );
                echo '</p>';
                echo '<a href="' . admin_url(
                        'admin.php?page=order-forms-setup-wizard&migration=true'
                    ) . '" class="button-primary woocommerce-save-button">' . __(
                        'Start Migration',
                        'woocommerce-wholesale-order-form'
                    ) . '</a>';
                echo '</div>';
            }
        }

        /**
         * New install setup wizard notice.
         *
         * @since 2.0
         */
        public function setup_wizard_notice() {

            echo '<div class="updated notice setup-wizard-notice-wrapper">';
            echo '<p><img src="' . WWOF_IMAGES_ROOT_URL . 'wholesale-suite-activation-notice-logo.png" alt=""/></p>';
            echo '<p>';
            echo __(
                'Congratulations! <b>Wholesale Order Forms</b> plugin has been successfully installed and is ready to be set up.',
                'woocommerce-wholesale-order-form'
            );
            echo '</p>';
            echo '<p style="margin-bottom: 20px;">';
            echo __(
                'Get Started quickly by clicking <b>"Start Setup"</b> and we\'ll guide you through creating your first form.',
                'woocommerce-wholesale-order-form'
            );
            echo '</p>';
            echo '<a href="' . admin_url(
                    'admin.php?page=order-forms-setup-wizard'
                ) . '" class="button-primary woocommerce-save-button" style="margin-right: 10px;">' . __(
                    'Start Setup',
                    'woocommerce-wholesale-order-form'
                ) . '</a>';
            echo '<a href="#" class="dismiss-setup-wizard-notice">' . __(
                    'I\'ll Do this Later',
                    'woocommerce-wholesale-order-form'
                ) . '</a>';
            echo '</div>';
        }

        /**
         * Migration Notice. Show notice for existing users.
         *
         * @since 2.0
         */
        public function migration_wizard_notice() {

            echo '<div class="updated notice setup-wizard-notice-wrapper">';
            echo '<p><img src="' . WWOF_IMAGES_ROOT_URL . 'wholesale-suite-activation-notice-logo.png" alt=""/></p>';
            echo '<p>';
            echo __(
                'Congratulations! This version of <b>Wholesale Order Form</b> plugin introduces a new form builder, multiple forms, and lots of great new options making it more powerful than ever.',
                'woocommerce-wholesale-order-form'
            );
            echo '</p>';
            echo '<p style="margin-bottom: 20px;">';
            echo __(
                'You can get started quickly with the new features by migrating your old form over to the new style. If you\'re not ready yet, you can choose to do it later via the Order Form Settings area.',
                'woocommerce-wholesale-order-form'
            );
            echo '</p>';
            echo '<a href="' . admin_url(
                    'admin.php?page=order-forms-setup-wizard&migration=true'
                ) . '" class="button-primary woocommerce-save-button" style="margin-right: 10px;">' . __(
                    'Start Migration',
                    'woocommerce-wholesale-order-form'
                ) . '</a>';
            echo '<a href="#" class="dismiss-setup-wizard-notice">' . __(
                    'I\'ll Do this Later',
                    'woocommerce-wholesale-order-form'
                ) . '</a>';
            echo '</div>';
        }

        /**
         * Dismiss migration and setup wizard notice.
         * Note: This also dismisses the admin note in wc inbox.
         *
         * @since 2.0
         */
        public function dismiss_setup_wizard_notice() {

            check_ajax_referer( 'setup_wizard_nonce', 'nonce' );

            // Setup cron hook to be fired 7 days after notice is dismissed
            if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
                wp_schedule_single_event( strtotime( '+7 days' ), self::CRON_HOOK );
            }

            update_option( WWOF_DISPLAY_WIZARD_NOTICE, 'no' );
        }

        /**
         * Redirect user to order form wizard on plugin activation.
         *
         * @since 2.0
         */
        public function redirect_to_setup_wizard() {

            $redirect_user = get_option( WWOF_SETUP_WIZARD_REDIRECT );

            if ( $redirect_user && get_option( WWOF_WIZARD_SETUP_DONE ) != 'yes' ) {
                delete_option( WWOF_SETUP_WIZARD_REDIRECT );
                exit( wp_redirect( admin_url( 'admin.php?page=order-forms-setup-wizard' ) ) );
            }
        }

        /**
         * Display setup/migration wizard notice again after 7 days since it is dismissed.
         * Only show if:
         * - Wizard is not yet done
         * - If fresh install and no order form has been created
         *
         * @since 2.0
         */
        public function cron_display_wizard_notice() {

            if ( get_option( WWOF_SETTINGS_WHOLESALE_PAGE_ID ) != '' && get_option(
                    WWOF_WIZARD_SETUP_DONE
                ) != 'yes' ) {

                // Migration
                delete_option( WWOF_DISPLAY_WIZARD_NOTICE );
            } elseif ( get_option( WWOF_WIZARD_SETUP_DONE ) != 'yes' ) {

                // Fresh install
                $order_forms = new WP_Query(
                    array(
						'post_type' => 'order_form',
						'fields'    => 'ids',
                    )
                );
                $count       = $order_forms->post_count;

                if ( $count === 0 ) {
                    delete_option( WWOF_DISPLAY_WIZARD_NOTICE );
                }
            }
        }
    }
}

return new Order_Form_Setup_Wizard();
