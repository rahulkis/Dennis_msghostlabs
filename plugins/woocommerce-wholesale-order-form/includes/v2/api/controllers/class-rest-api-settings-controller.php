<?php if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
// Exit if accessed directly

if ( ! class_exists( 'Order_Form_Settings_API_Controller' ) ) {

    /**
     * Model that houses the logic of Order Form Settngs REST API.
     *
     * @since 1.16
     */
    class Order_Form_Settings_API_Controller extends WP_REST_Controller {

        /**
         * Endpoint namespace.
         *
         * @var string
         */
        protected $namespace = 'wwof/v1';

        /**
         * Route base.
         *
         * @var string
         */
        protected $rest_base = 'settings';

        /**
         * Order_Form_Settings_API_Controller constructor.
         *
         * @since  1.16
         * @access public
         */
        public function __construct() {

            // Fires when preparing to serve an API request.
            add_action( 'rest_api_init', array( $this, 'register_routes' ) );

        }

        /**
         * Register cpt REST API routes and endpoints.
         *
         * @return void
         * @since  1.16
         * @access public
         */
        public function register_routes() {

            // Get Order Form Settings
            register_rest_route(
                $this->namespace,
                '/' . $this->rest_base,
                array(
                    array(
                        'methods'             => WP_REST_Server::READABLE,
                        'callback'            => array( $this, 'get_items' ),
                        'permission_callback' => array( $this, 'permissions_check' ),
                    ),
                    'schema' => array( $this, 'get_public_item_schema' ),
                )
            );

            register_rest_route(
                $this->namespace,
                '/' . $this->rest_base . '/(?P<id>[\d]+)',
                array(
                    'args' => array(
                        'id' => array(
                            'description' => __( 'Unique identifier for the object.' ),
                            'type'        => 'integer',
                        ),
                    ),
                    array(
                        'methods'             => WP_REST_Server::READABLE,
                        'callback'            => array( $this, 'get_settings_data' ),
                        'permission_callback' => array( $this, 'permissions_check' ),
                    ),
                    array(
                        'methods'             => WP_REST_Server::EDITABLE,
                        'callback'            => array( $this, 'update_settings_data' ),
                        'permission_callback' => array( $this, 'permissions_check' ),
                    ),
                )
            );

        }

        /**
         * Check whether the user has permission perform the request.
         *
         * @param WP_REST_Request
         *
         * @return WP_Error|boolean
         */
        public function permissions_check( $request ) {

            // Bypass user checking and for testing API via postman
            if ( defined( 'WWOF_DEV' ) && WWOF_DEV ) {
                return true;
            }

            // Make GET request public
            if ( $request->get_method() === 'GET' ) {
                return true;
            }

            if ( empty( get_current_user_id() ) ) {
                return new WP_Error(
                    'rest_customer_invalid',
                    __( 'Resource does not exist.', 'woocommerce-wholesale-order-form' ),
                    array( 'status' => 404 )
                );
            }

            if ( ! user_can( get_current_user_id(), 'manage_options' ) ) {
                return new WP_Error(
                    'rest_cannot_view',
                    __( 'Sorry, you cannot list resources.', 'woocommerce-wholesale-order-form' ),
                    array( 'status' => rest_authorization_required_code() )
                );
            }

            return false;

        }

        /**
         * Get WWWOF Settings.
         *
         * @param WP_REST_Request
         *
         * @return WP_REST_Response
         * @since  1.16
         * @access public
         */
        public function get_items( $request ) {

            $response = rest_ensure_response( $this->set_settings() );

            return $response;

        }

        /**
         * Set WWWOF Settings.
         *
         * @since  1.16
         * @access public
         */
        public function set_settings() {

            return apply_filters( 'rest_api_wwof_settings', array(
                array(
                    'title' => __( 'Hide Form Title', 'woocommerce-wholesale-order-form' ),
                    'type'  => 'checkbox',
                    'desc'  => __(
                        'Stops the form title from being displayed before the form.',
                        'woocommerce-wholesale-order-form'
                    ),
                    'id'    => 'hide_form_title',
                ),
                array(
                    'title'       => __( 'Product Sorting', 'woocommerce-wholesale-order-form' ),
                    'type'        => 'select',
                    'placeholder' => __( 'WooCommerce Default', 'woocommerce-wholesale-order-form' ),
                    'desc'        => __(
                        'Changes how products are sorted on the form.',
                        'woocommerce-wholesale-order-form'
                    ),
                    'id'          => 'sort_by',
                    'options'     => array(
                        ''           => __( 'WooCommerce Default', 'woocommerce-wholesale-order-form' ),
                        'menu_order' => __( 'Menu Order', 'woocommerce-wholesale-order-form' ),
                        'title'      => __( 'Name', 'woocommerce-wholesale-order-form' ),
                        'date'       => __( 'Sort by Date', 'woocommerce-wholesale-order-form' ),
                        'sku'        => __( 'SKU', 'woocommerce-wholesale-order-form' ),
                    ),
                    'default'     => __( 'WooCommerce Default', 'woocommerce-wholesale-order-form' ),

                ),
                array(
                    'title'       => __( 'Product Sorting By', 'woocommerce-wholesale-order-form' ),
                    'type'        => 'select',
                    'placeholder' => __( 'WooCommerce Default', 'woocommerce-wholesale-order-form' ),
                    'desc'        => __(
                        'Changes how products are sorted on the form.',
                        'woocommerce-wholesale-order-form'
                    ),
                    'id'          => 'sort_order',
                    'options'     => array(
                        ''     => __( 'WooCommerce Default', 'woocommerce-wholesale-order-form' ),
                        'asc'  => __( 'Ascending', 'woocommerce-wholesale-order-form' ),
                        'desc' => __( 'Descending', 'woocommerce-wholesale-order-form' ),
                    ),
                    'default'     => __( 'WooCommerce default', 'woocommerce-wholesale-order-form' ),
                ),
                array(
                    'title' => __( 'Lazy Loading', 'woocommerce-wholesale-order-form' ),
                    'type'  => 'checkbox',
                    'desc'  => __(
                        'More results are loaded into the page based on the user scrolling.',
                        'woocommerce-wholesale-order-form'
                    ),
                    'note'  => __(
                        '<b>Note:</b> If pagination element is added in the editor, this will be hidden in the frontend.',
                        'woocommerce-wholesale-order-form'
                    ),
                    'id'    => 'lazy_loading',
                ),
                array(
                    'title' => __( 'Show Variations Individually', 'woocommerce-wholesale-order-form' ),
                    'type'  => 'checkbox',
                    'desc'  => __(
                        'Enabling this setting will list down each product variation individually and have its own row in the wholesale order form.',
                        'woocommerce-wholesale-order-form'
                    ),
                    'id'    => 'show_variations_individually',
                ),
                array(
                    'title' => __( 'Zero Inventory', 'woocommerce-wholesale-order-form' ),
                    'type'  => 'checkbox',
                    'desc'  => __( 'Show products that have zero inventory.', 'woocommerce-wholesale-order-form' ),
                    'id'    => 'show_zero_inventory_products',
                ),
                array(
                    'title'       => __( 'Include Products', 'woocommerce-wholesale-order-form' ),
                    'type'        => 'multiselect',
                    'placeholder' => __( 'Select Products', 'woocommerce-wholesale-order-form' ),
                    'no_content'  => __( 'No Products', 'woocommerce-wholesale-order-form' ),
                    'desc'        => __(
                        'Only show this specific products in the order form. Variations are excluded in the selection.',
                        'woocommerce-wholesale-order-form'
                    ),
                    'id'          => 'include_products',
                    'options'     => $this->get_products( 'include_products' ),
                ),
                array(
                    'title'       => __( 'Exclude Products', 'woocommerce-wholesale-order-form' ),
                    'type'        => 'multiselect',
                    'placeholder' => __( 'Select Products', 'woocommerce-wholesale-order-form' ),
                    'no_content'  => __( 'No Products', 'woocommerce-wholesale-order-form' ),
                    'desc'        => __(
                        'Specify specific products to hide from the order form. Variations are included in the selection.',
                        'woocommerce-wholesale-order-form'
                    ),
                    'id'          => 'exclude_products',
                    'options'     => $this->get_products( 'exclude_products' ),
                ),

                )
            );

        }

        /**
         * Get Settings data for the specific order form.
         *
         * @param WP_REST_Request
         *
         * @return WP_REST_Response
         * @since  1.16
         * @access public
         */
        public function get_settings_data( $request ) {

            if ( get_post_type( $request['id'] ) !== 'order_form' ) {
                return new WP_Error(
                    'rest_invalid_id',
                    __( 'Invalid ID.', 'woocommerce-wholesale-order-form' ),
                    array( 'status' => 400 )
                );
            }

            $settingsData = get_post_meta( $request['id'], 'settings', true );

            return rest_ensure_response( ! empty( $settingsData ) ? $settingsData : array() );

        }

        /**
         * Update Settings data for the specific order form.
         *
         * @param WP_REST_Request
         *
         * @return WP_REST_Response|WP_Error
         * @since  1.16
         * @access public
         */
        public function update_settings_data( $request ) {

            if ( get_post_type( $request['id'] ) !== 'order_form' ) {
                return new WP_Error(
                    'rest_invalid_id',
                    __( 'Invalid ID.', 'woocommerce-wholesale-order-form' ),
                    array( 'status' => 400 )
                );
            }

            $updated = update_post_meta( $request['id'], 'settings', $request['data'] );

            if ( $updated === true ) {
                return rest_ensure_response(
                    array(
                        'status'  => 'success',
                        'message' => __( 'Settings updated successfully.', 'woocommerce-wholesale-order-form' ),
                        'data'    => $request['data'],
                    )
                );
            } else {
                return rest_ensure_response(
                    array(
                        'status'  => 'fail',
                        'message' => __( 'Update Fail.', 'woocommerce-wholesale-order-form' ),
                        'data'    => $request['data'],
                    )
                );
            }

        }

        /**
         * Get product lists. Filter products with search term.
         *
         * @param string $setting_id The setting ID.
         *
         * @return array
         * @since  2.0
         * @access public
         */
        public function get_products( $setting_id ) {

            global $wpdb;

            $post_type = "post_type = 'product'";

            if ( $setting_id == 'exclude_products' ) {
                $post_type = "post_type IN ( 'product' , 'product_variation' )";
            }

            $fetch_products = $wpdb->get_results(
                "
                                SELECT ID, post_title
                                FROM $wpdb->posts
                                WHERE post_status = 'publish'
                                AND $post_type
                                AND (
                                        post_parent IN ( SELECT ID from $wpdb->posts WHERE post_status = 'publish' AND post_type = 'product' )
                                        OR
                                        post_parent = ''
                                    )
                                "
            );

            $results = array();

            if ( ! empty( $fetch_products ) ) {
                foreach ( $fetch_products as $product ) {
                    $results[] = array(
                        'id'   => $product->ID,
                        'text' => '[ID : ' . $product->ID . '] ' . $product->post_title,
                    );
                }
			}

            return $results;

        }

    }

}

return new Order_Form_Settings_API_Controller();
