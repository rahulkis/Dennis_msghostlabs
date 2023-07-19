<?php if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
// Exit if accessed directly

if ( ! class_exists( 'Order_Form_Scripts' ) ) {

    class Order_Form_Scripts {

        /*
        |--------------------------------------------------------------------------
        | Class Properties
        |--------------------------------------------------------------------------
         */

        /**
         * Property that holds the single main instance of Order_Form_Scripts.
         *
         * @since  1.6.6
         * @access private
         * @var Order_Form_Scripts
         */
        private static $_instance;

        /*
        |--------------------------------------------------------------------------
        | Class Methods
        |--------------------------------------------------------------------------
         */

        /**
         * Ensure that only one instance of Order_Form_Scripts is loaded or can be loaded (Singleton Pattern).
         *
         * @param array $dependencies Array of instance objects of all dependencies of Order_Form_Scripts model.
         *
         * @return Order_Form_Scripts
         * @since 1.6.6
         */
        public static function instance( $dependencies = null ) {

            if ( ! self::$_instance instanceof self ) {
                self::$_instance = new self( $dependencies );
            }

            return self::$_instance;

        }

        /**
         * Load Admin or Backend Related Styles and Scripts.
         *
         * @since 1.0.0
         * @since 1.6.6 Refactor codebase and move to its proper model
         * @since 2.0.2 Add filter wwof_backend_options. Replace site_url with get_home_url. There's issue when the site is located in the subdirectory.
         *              Need to grab the root home url.
         */
        public function load_back_end_styles_and_scripts( $handle ) {

            // WWP v2 and lower = woocommerce_page_order-forms (WooCommerce > Wholesale Order Form)
            // WWP v2 and above = wholesale_page_order-forms (Wholesale > Order Form)
            if ( strpos( $handle, 'page_order-forms' ) !== false ) {

                global $wc_wholesale_prices;

                // Important: Must enqueue this script in order to use WP REST API via JS
                wp_enqueue_script( 'wp-api' );

                wp_localize_script(
                    'wp-api',
                    'WWOF_Backend_Options',
                    apply_filters(
                        'wwof_backend_options',
                        array(
                            'root'                      => esc_url_raw( rest_url() ),
                            'nonce'                     => wp_create_nonce( 'wp_rest' ),
                            'site_url'                  => strtok( get_home_url(), '?' ),
                            'ajax'                      => admin_url( 'admin-ajax.php' ),
                            'wholesale_role'            => ! empty( $wc_wholesale_prices ) ? $wc_wholesale_prices->wwp_wholesale_roles->getUserWholesaleRole(
                            ) : '',
                            'wwpp_active'               => Order_Form_Helpers::is_wwpp_active(),
                            'product_image_placeholder' => wc_placeholder_img_src(),
                            'is_rtl'                    => is_rtl(),
                        )
                    )
                );

                // React Order Form Scripts
                $paths = array(
                    'beta'     => 'yes' === get_option( 'wwof_order_form_v2_enable_order_form' ),
                    'app_name' => 'order-form-cpt',
                );

                $this->load_react_order_form_scripts( $paths );

            }

            // Display The Generate API keys under the ff location:
            // Old Setting Location - Beta
            // New Setting Location - General
            $section = filter_input( INPUT_GET, 'section', FILTER_SANITIZE_STRING );
            if ( 'wwof_settings' === filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_STRING ) &&
                ( 'wwof_settings_order_form_v2_section' === $section || empty( $section ) )
            ) {

                wp_enqueue_script(
                    'wwof_react_order_form_api_keys',
                    WWOF_JS_ROOT_URL . 'app/APIKeys.js',
                    array( 'jquery' ),
                    WooCommerce_WholeSale_Order_Form::VERSION,
                    true
                );
                wp_localize_script(
                    'wwof_react_order_form_api_keys',
                    'api_keys',
                    array(
                        'root'                  => esc_url_raw( rest_url() ),
                        'nonce'                 => wp_create_nonce( 'wp_rest' ),
                        'security_generate_key' => wp_create_nonce( 'update-api-key' ),
                        'user_id'               => get_current_user_id(),
                        'description'           => 'WWOF v2',
                        'success_message'       => __(
                            'Successfully created API key.',
                            'woocommerce-wholesale-order-form'
                        ),
                        'i18n'                  => array(
                            'success' => __( 'Success!', 'woocommerce-wholesale-order-form' ),
                            'fail'    => __( 'Fail!', 'woocommerce-wholesale-order-form' ),
                        ),
                    )
                );

            }

            if ( get_option( WWOF_WIZARD_SETUP_DONE ) !== 'yes' ) {

                // Setup wizard css
                wp_enqueue_style(
                    'wwof_setup_wizard_css',
                    WWOF_CSS_ROOT_URL . 'SetupWizard.css',
                    array(),
                    WooCommerce_WholeSale_Order_Form::VERSION,
                    'all'
                );

                wp_enqueue_script(
                    'wwof_setup_wizard_script',
                    WWOF_JS_ROOT_URL . 'app/SetupWizard.js',
                    array( 'jquery' ),
                    WooCommerce_WholeSale_Order_Form::VERSION,
                    true
                );
                wp_localize_script(
                    'wwof_setup_wizard_script',
                    'setup_wizard_notice_options',
                    array(
                        'ajax'  => admin_url( 'admin-ajax.php' ),
                        'nonce' => wp_create_nonce( 'setup_wizard_nonce' ),
                    )
                );

            }

        }

        /**
         * Load Frontend Related Styles and Scripts.
         *
         * @since 1.0.0
         * @since 1.6.6 Refactor codebase and move to its proper model
         * @since 2.0.2 Add filter wwof_backend_options. Replace site_url with get_home_url. There's issue when the site is located in the subdirectory.
         *              Need to grab the root home url.
         */
        public function load_front_end_styles_and_scripts() {

            global $post, $wc_wholesale_prices;

            $wholesale_role            = ! empty( $wc_wholesale_prices ) ? $wc_wholesale_prices->wwp_wholesale_roles->getUserWholesaleRole(
            ) : '';
            $force_load_scripts_styles = apply_filters( 'wwof_force_load_scripts_styles', false );
            $permalink_structure       = wc_get_permalink_structure();

            if ( ( $post && isset( $post->post_content ) &&
                    has_shortcode(
                        $post->post_content,
                        'wwof_product_listing'
                    ) &&
                    Order_Form_API_KEYS::is_api_key_valid() ) || true === $force_load_scripts_styles ) {

                $pattern = get_shortcode_regex();
                $beta    = false;

                // Check if shortcode 'wwof_product_listing' has beta attribute and if set
                if ( $post && preg_match_all( '/' . $pattern . '/s', $post->post_content, $matches ) ) {
                    foreach ( $matches as $match ) {
                        if ( ! empty( $match[0] ) ) {
                            preg_match( '/beta="(.*)"/', trim( $match[0] ), $attr_val );
                            if ( isset( $attr_val[1] ) ) {
                                $beta = $attr_val[1];
                            }
                        }
                    }
                }

                // Important: Must enqueue this script in order to use WP REST API via JS
                wp_enqueue_script( 'wp-api' );
                wp_localize_script(
                    'wp-api',
                    'WWOF_Frontend_Options',
                    apply_filters(
                        'wwof_frontend_options',
                        array(
                            'root'                         => esc_url_raw( rest_url() ),
                            'nonce'                        => wp_create_nonce( 'wp_rest' ),
                            'ajax'                         => admin_url( 'admin-ajax.php' ),
                            'category_base'                => $permalink_structure['category_base'] ?? '',
                            'site_url'                     => strtok( get_home_url(), '?' ),
                            'uid'                          => get_current_user_id(),
                            'wholesale_role'               => ! empty( $wholesale_role ) ? $wholesale_role[0] : '',
                            'wwpp_active'                  => Order_Form_Helpers::is_wwpp_active(),
                            'product_image_placeholder'    => wc_placeholder_img_src(),
                            'is_rtl'                       => is_rtl(),
                            'woocommerce_stock_format'     => get_option( 'woocommerce_stock_format' ),
                            'woocommerce_low_stock_amount' => get_option(
                                'woocommerce_notify_low_stock_amount'
                            ),
                            'pre_selected_variations'      => apply_filters( 'wwof_pre_selected_variations', true ),
                        )
                    )
                );

                // React Order Form Scripts
                $args = array(
                    'beta'     => $beta && 'yes' === get_option( 'wwof_order_form_v2_enable_order_form' ),
                    'app_name' => 'order-form',
                );

                $this->load_react_order_form_scripts( $args );

            }

        }

        /**
         * Load React Order Scripts.
         *
         * @since  1.15
         * @since  1.15.1 Check if the feature is turned on and if the "beta" attribute is true.
         * @access public
         */
        public static function load_react_order_form_scripts( $args ) {

            // JS Files
            $js_path = WWOF_JS_ROOT_DIR . 'app/' . $args['app_name'] . '/build/static/js';

            if ( file_exists( $js_path ) ) {

                $js_files = scandir( $js_path );

                if ( $js_files ) {
                    foreach ( $js_files as $key => $js_file ) {

                        // Get the extension using pathinfo
                        $extension = pathinfo( $js_file, PATHINFO_EXTENSION );

                        if ( 'js' === $extension ) {
                            wp_enqueue_script(
                                'wwof_react_order_form_' . $key,
                                WWOF_JS_ROOT_URL . 'app/' . $args['app_name'] . '/build/static/js/' . $js_file,
                                array( 'jquery' ),
                                WooCommerce_WholeSale_Order_Form::VERSION,
                                true
                            );
                        }
                    }
                }
            }

            // CSS Files
            $css_path = WWOF_JS_ROOT_DIR . 'app/' . $args['app_name'] . '/build/static/css';

            if ( file_exists( $css_path ) ) {

                $css_files = scandir( $css_path );

                if ( $css_files ) {

                    foreach ( $css_files as $key => $css_file ) {

                        // Get the extension using pathinfo
                        $extension = pathinfo( $css_file, PATHINFO_EXTENSION );

                        if ( 'css' === $extension ) {
                            wp_enqueue_style(
                                'wwof_react_order_form_css_' . $key,
                                WWOF_JS_ROOT_URL . 'app/' . $args['app_name'] . '/build/static/css/' . $css_file,
                                array(),
                                WooCommerce_WholeSale_Order_Form::VERSION,
                                'all'
                            );
                        }
                    }
                }
            }

        }

        /**
         * Execute model.
         *
         * @since  1.6.6
         * @access public
         */
        public function run() {

            // Load Backend CSS and JS
            add_action( 'admin_enqueue_scripts', array( $this, 'load_back_end_styles_and_scripts' ) );

            // Load Frontend CSS and JS
            add_action( 'wp_enqueue_scripts', array( $this, 'load_front_end_styles_and_scripts' ), 100 );

        }

    }

}
