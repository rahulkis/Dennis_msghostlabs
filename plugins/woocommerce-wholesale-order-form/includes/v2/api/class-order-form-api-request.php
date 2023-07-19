<?php //phpcs:ignore WordPress.Files.FileName.InvalidClassFileName

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
// Exit if accessed directly

require WWOF_PLUGIN_DIR . '/vendor/autoload.php';

use Automattic\WooCommerce\Client;
use Automattic\WooCommerce\HttpClient\HttpClientException;

if ( ! class_exists( 'WWOF_API_Request' ) ) {

    class WWOF_API_Request {

        /*
        |--------------------------------------------------------------------------
        | Class Properties
        |--------------------------------------------------------------------------
         */

        /**
         * Products Per Page
         *
         * @since  1.19
         * @access private
         */
        private $products_per_page = 10;

        /**
         * Products Per Page
         *
         * @since  1.19
         * @access private
         */
        private $categories_per_page = 100;

        /*
        |--------------------------------------------------------------------------
        | Class Methods
        |--------------------------------------------------------------------------
         */

        public function __construct() {

            // Get Products
            add_action( 'wp_ajax_nopriv_wwof_api_get_products', array( $this, 'get_products' ) );
            add_action( 'wp_ajax_wwof_api_get_products', array( $this, 'get_products' ) );

            // Get Product categories
            add_action( 'wp_ajax_nopriv_wwof_api_get_categories', array( $this, 'get_categories' ) );
            add_action( 'wp_ajax_wwof_api_get_categories', array( $this, 'get_categories' ) );

            // Regular Variations
            add_action( 'wp_ajax_nopriv_wwof_api_get_variations', array( $this, 'get_variations' ) );
            add_action( 'wp_ajax_wwof_api_get_variations', array( $this, 'get_variations' ) );

            // Wholesale Variations
            add_action(
                'wp_ajax_nopriv_wwof_api_get_wholesale_variations',
                array( $this, 'get_wholesale_variations' )
            );
            add_action( 'wp_ajax_wwof_api_get_wholesale_variations', array( $this, 'get_wholesale_variations' ) );

            // Sort by sku is not supported by WC API so we will make our own integration.
            add_filter( 'rest_product_collection_params', array( $this, 'insert_sku_collection_param' ), 10, 2 );
            add_filter( 'woocommerce_get_catalog_ordering_args', array( $this, 'add_sku_sorting' ), 10, 3 );

            // Update add to cart response for new OF inc/excl tax.
            add_filter( 'wwof_ajax_add_to_cart_response', array( $this, 'update_cart_subtotal' ) );

            // Quantity Restriction Toggle Off/On
            add_filter( 'wwof_quantity_validation', array( $this, 'toggle_quantity_restriction' ) );

            // Show variations individually
            add_filter( 'wwp_rest_wholesale_products', array( $this, 'show_variations_individually' ), 10, 2 );

            // Alter pre_get_posts API request
            add_action( 'pre_get_posts', array( $this, 'pre_get_posts_api_request' ) );

            // Order Form Search
            add_filter( 'woocommerce_rest_product_object_query', array( $this, 'order_form_search' ), 10, 2 );
        }

        /**
         * Get products. If user is wholesale customer then use wwpp api else use custom wwof api endpoint.
         *
         * @return array
         * @since 1.15
         */
        public function get_products() {

            if ( ! check_ajax_referer( 'wp_rest', 'nonce', false ) ) {
                wp_send_json_error(
                    array( 'message' => __( 'Invalid nonce', 'woocommerce-wholesale-order-form' ) ),
                    403
                );
            }

            try {

                $user_roles = Order_Form_Helpers::get_user_roles();

                // User is admin and shop managers then show all products using wc products endpoint
                // NOTE: If user is a visitor then we need to use the WWP API since we need to use the visibility functions to hide any restricted products.
                if ( ! empty( $user_roles ) &&
                    ( in_array( 'administrator', $user_roles, true ) ||
                        in_array( 'shop_manager', $user_roles, true ) )
                ) {
                    $this->get_regular_products();
                } else {

                    $wholesale_role = WWOF_API_Helpers::is_wholesale_customer();

                    if ( empty( $wholesale_role ) && isset( $_POST['wholesale_role'] ) ) {
                        $wholesale_role = $_POST['wholesale_role'];
                    }

                    $wwp_data         = Order_Form_Helpers::get_wwp_data();
                    $wwpp_data        = Order_Form_Helpers::get_wwpp_data();
                    $wwp_min_version  = Order_Form_Requirements::MIN_WWP_VERSION;
                    $wwpp_min_version = Order_Form_Requirements::MIN_WWPP_VERSION;

                    $wholesale_role = sanitize_text_field( $wholesale_role );

                    // Only WWP is active. Check min version
                    if (
                        Order_Form_Helpers::is_wwp_active() &&
                        ! Order_Form_Helpers::is_wwpp_active() &&
                        version_compare( $wwp_data['Version'], $wwp_min_version, '>=' )
                    ) {
                        return $this->get_wholesale_products( $wholesale_role );
                    }

                    // Both WWP and WWPP are active. Check min version
                    if (
                        Order_Form_Helpers::is_wwp_active() &&
                        Order_Form_Helpers::is_wwpp_active() &&
                        version_compare( $wwp_data['Version'], $wwp_min_version, '>=' ) &&
                        version_compare( $wwpp_data['Version'], $wwpp_min_version, '>=' )
                    ) {
                        return $this->get_wholesale_products( $wholesale_role );
                    }

                    return $this->get_regular_products();
                }
            } catch ( HttpClientException $e ) {

                return $this->get_regular_products();
            }

            return array();
        }

        /**
         * Get regular products using WWOF API custom endpoint.
         *
         * @return array
         * @since       2.0.2 Replace site_url() with get_home_url(). Some sites transfer their WordPress into a sub directory.
         *              Issue occurs when performing request via site_url() function coz it will add the sub dir to the url.
         *              API needs the home root url.
         *              Remove parent in the arg. Not working when show variations individually is enabled.
         *              Add custom arg is_wwof and filter_by_categories.
         * @since       1.15
         */
        public function get_regular_products() {

            check_ajax_referer( 'wp_rest', 'nonce' );

            try {

                $api_keys = Order_Form_API_KEYS::get_keys();

                $options = apply_filters(
                    'wwof_filter_get_regular_products_api_args',
                    array(
                        'version'           => 'wc/v3',
                        'query_string_auth' => is_ssl(),
                        'verify_ssl'        => false,
                        'wp_api'            => true,
                        'timeout'           => 120,
                    )
                );

                $woocommerce = new Client(
                    strtok( get_home_url(), '?' ),
                    $api_keys['consumer_key'],
                    $api_keys['consumer_secret'],
                    $options
                );

                // Search Text
                $search = $_POST['search'] ?? '';
                $search = sanitize_text_field( $search );

                // Searching
                $is_wwof_searching = $_POST['searching'] ?? '';

                // Filter by category
                $cat_obj     = isset( $_POST['category'] ) && ! empty( $_POST['category'] ) ? get_term_by(
                    'slug',
                    $_POST['category'],
                    'product_cat'
                ) : '';
                $category_id = is_a( $cat_obj, 'WP_Term' ) ? $cat_obj->term_id : '';

                // Show Variations Individually feature
                $show_variations_individually = isset( $_POST['form_settings'] ) && isset( $_POST['form_settings']['show_variations_individually'] ) ? $_POST['form_settings']['show_variations_individually'] : false;

                // Allow SKU Search
                $allow_sku_search = isset( $_POST['allow_sku_search'] ) &&
                boolval( $_POST['allow_sku_search'] ) === true ? 'yes' : 'no';

                $form_settings = $_POST['form_settings'] ?? array();

                // Show Zero Inventory
                $show_zero_inventory = isset( $_POST['form_settings'] ) &&
                isset( $_POST['form_settings']['show_zero_inventory_products'] ) &&
                ( 'true' === $_POST['form_settings']['show_zero_inventory_products'] ) ? 'yes' : 'no';

                // Include Products
                $include_products = $form_settings['include_products'] ?? array();

                // Exclude Products
                $exclude_products = $form_settings['exclude_products'] ?? array();

                // Pre-selected category or categories
                $category = $category_id ? $category_id : WWOF_API_Helpers::filtered_categories( $form_settings );

                $args = array(
                    'per_page'                     => $_POST['per_page'] ?? $this->products_per_page,
                    'search'                       => $search,
                    'category'                     => 'true' !== $show_variations_individually ? $category : '',
                    'page'                         => $_POST['page'] ?? 1,
                    'order'                        => ! empty( $_POST['sort_order'] ) ? $_POST['sort_order'] : $form_settings['sort_order'] ?? 'desc',
                    'orderby'                      => ! empty( $_POST['sort_by'] ) ? $_POST['sort_by'] : $form_settings['sort_by'] ?? 'date',
                    'status'                       => 'publish',
                    'include'                      => $include_products,
                    'exclude'                      => $exclude_products,
                    '_fields'                      => implode( ', ', $this->get_fields() ),
                    // WWP params
                    'show_categories'              => true,
                    // Only used in WWOF
                    'is_wwof'                      => true,
                    // Custom: Only used in WWOF
                    'show_variations_individually' => 'true' === $show_variations_individually ? 'yes' : 'no',
                    // Custom: Only used in WWOF
                    'allow_sku_search'             => $allow_sku_search,
                    // Custom: Only used in WWOF
                    'show_zero_inventory'          => $show_zero_inventory,
                    // Custom: Only used in WWOF
                    'filter_by_categories'         => $category,
                    // Custom: Only used in WWOF
                    'is_wwof_searching'            => $is_wwof_searching,
                    // Custom: Only used in WWOF
                );

                // Aelia Currency Switcher selected currency integration for front end
                $aelia_selected_currency = $_REQUEST['aelia_selected_currency'] ?? '';
                if ( $aelia_selected_currency ) {
                    $args['aelia_selected_currency'] = $aelia_selected_currency;
                }

                if (
                    $show_variations_individually == 'true' && ! empty( $category ) &&
                    isset( $_POST['form_settings'] ) && isset( $_POST['form_settings']['filtered_categories'] ) &&
                    ! empty( $_POST['form_settings']['filtered_categories'] )
                ) {

                    // Filter by the selected categories.
                    // Display also the variations
                    // NOTE: This is only used when "Show Variations Individually" is enabled
                    $product_ids     = WWOF_API_Helpers::get_product_and_variation_ids_from_category( $category );
                    $args['include'] = $product_ids;

                    unset( $args['category'] );
                }

                $results = $woocommerce->get( 'products', $args );

                $response       = $woocommerce->http->getResponse();
                $headers        = WWOF_API_Helpers::get_header_data( $response->getHeaders() );
                $total_pages    = $headers['total_pages'];
                $total_products = $headers['total_products'];

                wp_send_json(
                    array(
                        'status'                    => 'success',
                        'products'                  => $results,
                        'lazy_load_variations_data' => $this->lazy_load_variations_data(
                            $results,
                            '',
                            $exclude_products
                        ),
                        'settings'                  => array(),
                        'total_page'                => $total_pages,
                        'total_products'            => $total_products,
                        'cart_subtotal'             => $this->get_cart_subtotal(),
                        'cart_url'                  => wc_get_cart_url(),
                    )
                );
            } catch ( HttpClientException $e ) {

                wp_send_json(
                    array(
                        'status'                    => 'error',
                        'products'                  => array(),
                        'lazy_load_variations_data' => array(),
                        'settings'                  => array(),
                        'total_page'                => 0,
                        'total_products'            => 0,
                        'cart_subtotal'             => $this->get_cart_subtotal(),
                        'cart_url'                  => wc_get_cart_url(),
                        'message'                   => $e->getMessage(), // error
                    )
                );
            }
        }

        /**
         * Get wholesale products using WWPP API custom endpoint.
         * Note: not yet used will use this in the next phase.
         *
         * @return array
         * @since       2.0.2 Replace site_url() with get_home_url(). Some sites transfer their WordPress into a sub directory.
         *              Issue occurs when performing request via site_url() function coz it will add the sub dir to the url.
         *              API needs the home root url.
         *              Remove parent in the arg. Not working when show variations individually is enabled.
         *              Add custom arg is_wwof and filter_by_categories.
         * @since       1.15
         */
        public function get_wholesale_products( $wholesale_role ) {

            //phpcs:disable WordPress.Security.NonceVerification.Missing
            // this method is being called internally where nonce is already verified
            try {

                $api_keys = Order_Form_API_KEYS::get_keys();

                $options = apply_filters(
                    'wwof_filter_get_wholesale_products_api_args',
                    array(
                        'version'           => 'wholesale/v1',
                        'query_string_auth' => is_ssl(),
                        'verify_ssl'        => false,
                        'wp_api'            => true,
                        'timeout'           => 120,
                    )
                );

                $woocommerce = new Client(
                    strtok( get_home_url(), '?' ),
                    $api_keys['consumer_key'],
                    $api_keys['consumer_secret'],
                    $options
                );

                // Search Text
                $search = $_POST['search'] ?? '';
                $search = sanitize_text_field( $search );

                // Searching
                $is_wwof_searching = $_POST['searching'] ?? '';

                // Filter by category
                $cat_obj     = isset( $_POST['category'] ) && ! empty( $_POST['category'] ) ? get_term_by(
                    'slug',
                    $_POST['category'],
                    'product_cat'
                ) : '';
                $category_id = is_a( $cat_obj, 'WP_Term' ) ? $cat_obj->term_id : '';

                $form_settings = $_POST['form_settings'] ?? array();

                // Show Variations Individually feature
                $show_variations_individually = $form_settings['show_variations_individually'] ?? false;

                // Allow SKU Search
                $allow_sku_search = isset( $_POST['allow_sku_search'] ) &&
                true === boolval( $_POST['allow_sku_search'] ) ? 'yes' : 'no';

                // Show Zero Inventory
                $show_zero_inventory = 'true' === $form_settings['show_zero_inventory_products'] ? 'yes' : 'no';

                // Include Products
                $include_products = $this->filter_include_products();

                // Exclude Products
                $exclude_products = $form_settings['exclude_products'] ?? array();

                // Pre-selected category or categories
                $category = $category_id ? $category_id : WWOF_API_Helpers::filtered_categories( $form_settings );

                $order = isset( $_POST['sort_order'] ) && ! empty( $_POST['sort_order'] ) ? $_POST['sort_order'] : $form_settings['sort_order'];
                $order = ! empty( $order ) ? $order : 'desc';

                $order_by = isset( $_POST['sort_by'] ) && ! empty( $_POST['sort_by'] ) ? $_POST['sort_by'] : $form_settings['sort_by'];
                $order_by = ! empty( $order_by ) ? $order_by : 'date';

                $args = array(
                    'wholesale_role'               => $wholesale_role,
                    'per_page'                     => $_POST['per_page'] ?? $this->products_per_page,
                    'search'                       => $search,
                    'category'                     => 'true' !== $show_variations_individually ? $category : '',
                    'page'                         => $_POST['page'] ?? 1,
                    'order'                        => $order,
                    'orderby'                      => $order_by,
                    'status'                       => 'publish',
                    'include'                      => 'true' !== $show_variations_individually ? $include_products : array(),
                    'exclude'                      => $exclude_products,
                    '_fields'                      => implode( ', ', $this->get_fields() ),
                    // WWP params
                    'show_categories'              => true,
                    'uid'                          => isset( $_POST['uid'] ) ? intval( $_POST['uid'] ) : '',
                    'show_meta_data'               => true,
                    // Only used in WWOF
                    'is_wwof'                      => true,
                    // Custom: Only used in WWOF
                    'show_variations_individually' => 'true' === $show_variations_individually ? 'yes' : 'no',
                    // Custom: Only used in WWOF
                    'allow_sku_search'             => $allow_sku_search,
                    // Custom: Only used in WWOF
                    'show_zero_inventory'          => $show_zero_inventory,
                    // Custom: Only used in WWOF
                    'filter_by_categories'         => $category,
                    // Custom: Only used in WWOF
                    'filter_by_products'           => $include_products,
                    // Custom: Only used in WWOF
                    'is_wwof_searching'            => $is_wwof_searching,
                    // Custom: Only used in WWOF
                );

                // Aelia Currency Switcher selected currency integration for front end
                $aelia_selected_currency = isset( $_REQUEST['aelia_selected_currency'] ) ? $_REQUEST['aelia_selected_currency'] : '';
                if ( $aelia_selected_currency ) {
                    $args['aelia_selected_currency'] = $aelia_selected_currency;
                }

                if (
                    $show_variations_individually == 'true' && ! empty( $category ) &&
                    ! empty( $form_settings['filtered_categories'] )
                ) {

                    // Filter by the selected categories.
                    // Display also the variations
                    // NOTE: This is only used when "Show Variations Individually" is enabled
                    $product_ids     = WWOF_API_Helpers::get_product_and_variation_ids_from_category( $category );
                    $args['include'] = $product_ids;

                    unset( $args['category'] );
                }

                if ( ! empty( $args['orderby'] ) && 'sku' === $args['orderby'] ) {
                    add_filter(
                        'woocommerce_rest_product_object_query',
                        function ( $args, $request ) {

                            $args['orderby_meta_key'] = '_sku';
                            $args['orderby']          = 'meta_value';

                            return $args;
                        },
                        10,
                        2
                    );
                }

                $results = $woocommerce->get( 'products', $args );

                $response       = $woocommerce->http->getResponse();
                $headers        = WWOF_API_Helpers::get_header_data( $response->getHeaders() );
                $total_pages    = $headers['total_pages'];
                $total_products = $headers['total_products'];

                wp_send_json(
                    array(
                        'status'                    => 'success',
                        'products'                  => $results,
                        'lazy_load_variations_data' => $this->lazy_load_variations_data(
                            $results,
                            $wholesale_role,
                            $exclude_products
                        ),
                        'settings'                  => array(),
                        'total_page'                => $total_pages,
                        'total_products'            => $total_products,
                        'cart_subtotal'             => $this->get_cart_subtotal(),
                        'cart_url'                  => wc_get_cart_url(),
                    )
                );
            } catch ( HttpClientException $e ) {

                wp_send_json(
                    array(
                        'status'                    => 'error',
                        'products'                  => array(),
                        'lazy_load_variations_data' => array(),
                        'settings'                  => array(),
                        'total_page'                => 0,
                        'total_products'            => 0,
                        'cart_subtotal'             => $this->get_cart_subtotal(),
                        'cart_url'                  => wc_get_cart_url(),
                        'message'                   => $e->getMessage(), // error
                    )
                );
            }
            //phpcs:enable WordPress.Security.NonceVerification.Missing
        }

        /**
         * Get categories using get_terms().
         *
         * @since       2.0.2 Replace site_url() with get_home_url(). Some sites transfer their WordPress into a sub directory.
         *              Issue occurs when performing request via site_url() function coz it will add the sub dir to the url.
         *              API needs the home root url.
         * @since       1.15
         * @uses       get_terms()
         */
        public function get_categories() {

            if ( ! check_ajax_referer( 'wp_rest', 'nonce', false ) ) {
                wp_send_json_error(
                    array(
                        'status'  => 'error',
                        'message' => 'Invalid nonce',
                    ),
                    403
                );
            }

            $args = array(
                'per_page' => $this->categories_per_page,
            );

            // WWOF Product Categories Shortcode Attribute.
            if ( ! empty( $_POST['categories'] ) && is_string( $_POST['categories'] ) ) {
                if ( ! empty( $args['include'] ) ) {
                    $args['include'] = array_merge( $args['include'], explode( ',', $_POST['categories'] ) );
                } else {
                    $args['include'] = explode( ',', $_POST['categories'] );
                }
            }

            $categories = get_terms(
                wp_parse_args(
                    $args,
                    array(
                        'taxonomy'   => 'product_cat',
                        'hide_empty' => false,
                    )
                )
            );

            wp_send_json(
                array(
                    'status'     => 'success',
                    'categories' => $categories,
                )
            );
        }

        /**
         * Get product variations via WC API endpoint.
         *
         * @param array $products
         *
         * @return array
         * @since       1.15
         * @since       2.0.2 Replace site_url() with get_home_url(). Some sites transfer their WordPress into a sub directory.
         *              Issue occurs when performing request via site_url() function coz it will add the sub dir to the url.
         *              API needs the home root url.
         */
        public function get_variations() {

            if ( empty( $products ) ) {
                $products = isset( $_POST['products'] ) ? json_decode(
                    wp_json_encode( $_POST['products'] ),
                    false
                ) : array();
            }

            $variations = array();
            $api_keys   = Order_Form_API_KEYS::get_keys();

            $options = apply_filters(
                'wwof_filter_get_categories_api_args',
                array(
                    'version'           => 'wc/v3',
                    'query_string_auth' => is_ssl(),
                    'verify_ssl'        => false,
                    'wp_api'            => true,
                    'timeout'           => 120,
                )
            );

            $woocommerce = new Client(
                strtok( get_home_url(), '?' ),
                $api_keys['consumer_key'],
                $api_keys['consumer_secret'],
                $options
            );

            // Exclude Products
            $exclude_products = isset( $_POST['form_settings'] ) && isset( $_POST['form_settings']['exclude_products'] ) ? $_POST['form_settings']['exclude_products'] : array();

            // Fetch variations per variable product LIMIT 20
            if ( ! empty( $products ) ) {

                // Fetch all variations per variable product
                foreach ( $products as $product ) {

                    if ( 'variable' === $product->type ) {

                        try {

                            $args = array(
                                'orderby'  => 'menu_order',
                                'order'    => 'asc',
                                'per_page' => $this->get_variations_per_page(),
                                'exclude'  => $exclude_products,
                                '_fields'  => implode( ', ', $this->get_fields() ),
                            );

                            $results = $woocommerce->get( 'products/' . $product->id . '/variations', $args );

                            if ( $results ) {

                                foreach ( $results as $index => $variation ) {
                                    $variation_obj            = wc_get_product( $variation->id );
                                    $results[ $index ]->price = $variation_obj->get_price_html();
                                }

                                $variations[ $product->id ] = $results;
                            }
                        } catch ( HttpClientException $e ) {

                            // Prints Error: Not a wholesale product. [wholesale_rest_cannot_view]
                            // We won't log any error message here just to avoid confusion.
                            // Only use error log when debuggin issues.

                        }
                    }
                }
            } elseif ( isset( $_POST['product_id'] ) ) {

                // Lazy Loading on scroll combo variation
                try {

                    $current_page = sanitize_text_field( $_POST['current_page'] );
                    $product_id   = sanitize_text_field( $_POST['product_id'] );

                    $args = array(
                        'orderby'  => 'menu_order',
                        'order'    => 'asc',
                        'status'   => 'publish',
                        'page'     => $current_page,
                        'per_page' => $this->get_variations_per_page(),
                        'exclude'  => $exclude_products,
                        '_fields'  => implode( ', ', $this->get_fields() ),
                    );

                    $results = $woocommerce->get( 'products/' . $product_id . '/variations', $args );

                    if ( $results ) {

                        foreach ( $results as $index => $variation ) {
                            $variation_obj            = wc_get_product( $variation->id );
                            $results[ $index ]->price = $variation_obj->get_price_html();
                        }

                        $variations = $results;
                    }
                } catch ( HttpClientException $e ) {

                    // Prints Error: Not a wholesale product. [wholesale_rest_cannot_view]
                    // We won't log any error message here just to avoid confusion.
                    // Only use error log when debuggin issues.

                }
            }

            if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

                wp_send_json(
                    array(
                        'status'     => 'success',
                        'variations' => $variations,
                    )
                );
            }
        }

        /**
         * Get Wholesale Variations.
         *
         * @return array
         * @since       2.0.2 Replace site_url() with get_home_url(). Some sites transfer their WordPress into a sub directory.
         *              Issue occurs when performing request via site_url() function coz it will add the sub dir to the url.
         *              API needs the home root url.
         * @since       1.16
         */
        public function get_wholesale_variations() {

            $api_keys = Order_Form_API_KEYS::get_keys();
            $options  = apply_filters(
                'wwof_filter_get_wholesale_variations_api_args',
                array(
                    'version'           => 'wholesale/v1',
                    'query_string_auth' => is_ssl(),
                    'verify_ssl'        => false,
                    'wp_api'            => true,
                    'timeout'           => 120,
                )
            );

            $woocommerce = new Client(
                strtok( get_home_url(), '?' ),
                $api_keys['consumer_key'],
                $api_keys['consumer_secret'],
                $options
            );

            $product_id     = isset( $_POST['product_id'] ) ? sanitize_text_field( $_POST['product_id'] ) : '';
            $products       = isset( $_POST['products'] ) ? filter_var_array( $_POST['products'] ) : array();
            $wholesale_role = isset( $_POST['wholesale_role'] ) ? sanitize_text_field( $_POST['wholesale_role'] ) : '';
            $variations     = array();

            // Exclude Products
            $exclude_products = isset( $_POST['form_settings'] ) && isset( $_POST['form_settings']['exclude_products'] ) ? filter_var_array(
                $_POST['form_settings']['exclude_products']
            ) : array();

            // Fetch variations per variable product LIMIT 20
            if ( ! empty( $products ) ) {

                foreach ( $products as $product ) {

                    if ( 'variable' === $product['type'] ) {
                        try {

                            $args = array(
                                'orderby'        => 'menu_order',
                                'order'          => 'asc',
                                'wholesale_role' => $wholesale_role,
                                'uid'            => isset( $_POST['uid'] ) ? intval(
                                    $_POST['uid']
                                ) : get_current_user_id(),
                                'per_page'       => $this->get_variations_per_page(),
                                'show_meta_data' => true,
                                'exclude'        => $exclude_products,
                                '_fields'        => implode( ', ', $this->get_fields() ),
                            );

                            $results = $woocommerce->get( 'products/' . $product['id'] . '/variations', $args );

                            if ( $results ) {
                                // NOTE: Will comment this part for now. Causing speed issue.
                                // foreach ($results as $index => $variation) {
                                // $variation_obj          = wc_get_product($variation->id);
                                // $results[$index]->price = $variation_obj->get_price_html();
                                // }

                                $variations[ $product['id'] ] = $results;
                            }
                        } catch ( HttpClientException $e ) {

                            // Prints Error: Not a wholesale product. [wholesale_rest_cannot_view]
                            // We won't log any error message here just to avoid confusion.
                            // Only use error log when debuggin issues.
                            // error_log(print_r($e->getMessage(), true));

                        }
                    }
                }
            } elseif ( isset( $product_id ) ) {
                // Lazy Loading on scroll combo variation

                try {

                    $current_page = isset( $_POST['current_page'] ) ? sanitize_text_field( $_POST['current_page'] ) : 1;
                    $uid          = isset( $_POST['uid'] ) ? sanitize_text_field( $_POST['uid'] ) : get_current_user_id(
                    );

                    $args = array(
                        'orderby'        => 'menu_order',
                        'order'          => 'asc',
                        'wholesale_role' => $wholesale_role,
                        'uid'            => intval( $uid ),
                        'per_page'       => $this->get_variations_per_page(),
                        'page'           => $current_page,
                        'exclude'        => $exclude_products,
                        'show_meta_data' => true,
                        '_fields'        => implode( ', ', $this->get_fields() ),
                    );

                    $results = $woocommerce->get( 'products/' . $product_id . '/variations', $args );

                    if ( $results ) {
                        // NOTE: Will comment this part for now. Causing speed issue.
                        // foreach ($results as $index => $variation) {
                        // $variation_obj          = wc_get_product($variation->id);
                        // $results[$index]->price = $variation_obj->get_price_html();
                        // }

                        $variations = $results;
                    }
                } catch ( HttpClientException $e ) {
                    // Prints Error: Not a wholesale product. [wholesale_rest_cannot_view]
                    // We won't log any error message here just to avoid confusion.
                    // Only use error log when debuggin issues.
                    // error_log(print_r($e->getMessage(), true));
                }
            }

            if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

                wp_send_json(
                    array(
                        'status'     => 'success',
                        'variations' => $variations,
                    )
                );
            }
        }

        /**
         * Get cart subtotal.
         *
         * @return string
         * @since 1.16
         */
        public function get_cart_subtotal() {

            global $wc_wholesale_prices_premium;

            if ( isset( $_REQUEST['form_settings'] ) && ! empty( $_REQUEST['form_settings']['tax_display'] ) ) {
                $tax_display = $_REQUEST['form_settings']['tax_display'];
            } else {
                // Always use the WC setting "Display prices in the shop" if no override is set in Subtotal Tax Display component
                if ( $wc_wholesale_prices_premium ) {
                    remove_filter(
                        'option_woocommerce_tax_display_shop',
                        array( $wc_wholesale_prices_premium->wwpp_tax, 'wholesale_tax_display_shop' ),
                        10,
                        1
                    );
                }

                $tax_display = get_option( 'woocommerce_tax_display_shop' );

                if ( $wc_wholesale_prices_premium ) {
                    add_filter(
                        'option_woocommerce_tax_display_shop',
                        array( $wc_wholesale_prices_premium->wwpp_tax, 'wholesale_tax_display_shop' ),
                        10,
                        1
                    );
                }
            }

            $subtotal_pretext = isset( $_REQUEST['form_settings'] ) && ! empty( $_REQUEST['form_settings']['subtotal_pretext'] ) ? $_REQUEST['form_settings']['subtotal_pretext'] : '';
            $subtotal_suffix  = isset( $_REQUEST['form_settings'] ) && ! empty( $_REQUEST['form_settings']['subtotal_suffix'] ) ? $_REQUEST['form_settings']['subtotal_suffix'] : '';

            ob_start();

            if ( ! empty( WC()->cart ) && WC()->cart->get_cart_contents_count() ) {

                switch ( $tax_display ) {
                    case 'excl':
                        $subtotal_suffix = ! empty( $subtotal_suffix ) ? $subtotal_suffix : WC(
                        )->countries->ex_tax_or_vat();
                        echo wp_sprintf(
                            '%s %s <small> %s</small>',
                            $subtotal_pretext,
                            wc_price( WC()->cart->cart_contents_total ),
                            $subtotal_suffix
                        );
                        break;
                    case 'incl':
                        $subtotal_suffix = ! empty( $subtotal_suffix ) ? $subtotal_suffix : WC(
                        )->countries->inc_tax_or_vat();
                        echo wp_sprintf(
                            '%s %s <small> %s</small>',
                            $subtotal_pretext,
                            wc_price( WC()->cart->cart_contents_total + WC()->cart->tax_total ),
                            $subtotal_suffix
                        );
                }
            }

            return ob_get_clean();
        }

        /**
         * Set additional param to WWP API request to handle sort by sku.
         *
         * @param array  $params
         * @param string $post_type
         *
         * @return array
         * @since 1.17
         */
        public function insert_sku_collection_param( $params, $post_type ) {

            $params['orderby']['enum'][] = 'sku';

            return $params;
        }

        /**
         * Add sort by sku.
         *
         * @param array  $args
         * @param string $orderby
         * @param string $order
         *
         * @return array
         * @since 1.20
         */
        public function add_sku_sorting( $args, $orderby, $order ) {

            $orderby_value = isset( $_GET['orderby'] ) ? wc_clean( $_GET['orderby'] ) : '';

            if ( 'sku' === $orderby_value ) {
                add_filter( 'posts_clauses', array( $this, 'sku_sorting_query' ) );
            }

            return $args;
        }

        /**
         * Sort by sku query.
         *
         * @param array $args
         *
         * @return array
         * @since 1.20
         */
        public function sku_sorting_query( $args ) {

            global $wpdb;

            $order = isset( $_GET['order'] ) ? wc_clean( $_GET['order'] ) : 'ASC';

            $args['join'] .= " LEFT JOIN {$wpdb->postmeta} wwof_pm1 ON ( $wpdb->posts.ID = wwof_pm1.post_id && wwof_pm1.meta_key = '_sku' ) ";

            $args['orderby'] = " wwof_pm1.meta_value $order ";

            return $args;
        }

        /**
         * Update add to cart response for new OF inc/excl tax.
         * New OF should not be based on the old setting but the new option in subtotal component tax display option.
         *
         * @param array $args
         *
         * @return array
         * @since 1.17
         */
        public function update_cart_subtotal( $response ) {

            if ( isset( $response['status'] ) && 'success' === $response['status'] && isset( $_REQUEST['form_settings'] ) ) {
                $response['cart_subtotal_markup'] = $this->get_cart_subtotal();
            }

            return $response;
        }

        /**
         * Quantity Restriction Toggle Off/On
         *
         * @param bool $value Min Order Qty and Step Restriction. If empty or false means restricted. If true restriction is off and the product can be added to cart.
         *
         * @return bool
         * @since 1.17
         */
        public function toggle_quantity_restriction( $value ) {

            if ( isset( $_REQUEST['form_settings'] ) && isset( $_REQUEST['form_settings']['quantity_restriction'] ) ) {
                if ( $_REQUEST['form_settings']['quantity_restriction'] == 'false' ) {
                    return true;
                }
            }

            return $value;
        }

        /**
         * Lazy variations data. Set defaults.
         *
         * @param array  $products         Product Objects
         * @param string $wholesale_role   Wholesale Role
         * @param array  $exclude_products Excluded Product ID's
         *
         * @return array
         * @since 1.19
         */
        public function lazy_load_variations_data(
            $products = array(),
            $wholesale_role = '',
            $exclude_products = array()
        ) {

            $data = array();
            if ( ! empty( $products ) ) {
                foreach ( $products as $product ) {
                    if ( 'variable' === $product->type ) {
                        $totals               = WWOF_API_Helpers::get_variations_total_by_variable_id(
                            $product->id,
                            $wholesale_role,
                            $exclude_products
                        );
                        $data[ $product->id ] = array(
                            'current_page'     => 1,
                            'total_variations' => $totals,
                            'total_page'       => ceil( $totals / $this->get_variations_per_page() ),
                        );
                    }
                }
            }

            return $data;
        }

        /**
         * Toggle show variations individually.
         *
         * @param array  $wholesale_products
         * @param string $wholesale_role
         *
         * @return array
         * @since 1.19
         */
        public function show_variations_individually( $wholesale_products, $wholesale_role ) {

            if ( isset( $_REQUEST['show_variations_individually'] ) && 'yes' === $_REQUEST['show_variations_individually'] ) {
                return WWOF_API_Helpers::get_variations_to_show_individually( $wholesale_products, $wholesale_role );
            }

            return $wholesale_products;
        }

        /**
         * Hook into pre_get_posts.
         * - Show variations individually.
         * - Show zero inventory products
         *
         * @param WP_Query $query
         *
         * @since 1.20
         * @since 2.0.2 Filter the results when Include Products or Included Categories is set.
         */
        public function pre_get_posts_api_request( $query ) {

            global $wpdb, $wc_wholesale_prices_premium;

            $is_rest = apply_filters( 'wwof_rest', defined( 'REST_REQUEST' ) && REST_REQUEST );

            if ( $is_rest && isset( $_REQUEST['is_wwof'] ) ) {

                // Aeilia Integration
                add_filter(
                    'wc_aelia_cs_selected_currency',
                    function () {

                        if ( ! is_admin() && isset( $_REQUEST['aelia_selected_currency'] ) &&
                            WWOF_ACS_Integration_Helper::shop_base_currency(
                            ) != $_REQUEST['aelia_selected_currency'] ) {
                            return $_REQUEST['aelia_selected_currency'];
                        } else {
                            return WWOF_ACS_Integration_Helper::shop_base_currency();
                        }
                    },
                    99
                );

                // Show Variations Individually
                if ( isset( $_REQUEST['show_variations_individually'] ) && 'yes' === $_REQUEST['show_variations_individually'] ) {

                    // Return variations
                    $query->set( 'post_type', array( 'product', 'product_variation' ) );

                    // Exclude variable products
                    $tax_query        = is_array( $query->get( 'tax_query' ) ) ? $query->get( 'tax_query' ) : array();
                    $variable_term_id = WWOF_Product_Listing_Helper::get_variable_product_term_taxonomy_id();

                    if ( $variable_term_id ) {
                        $tax_query = array_merge(
                            $tax_query,
                            array(
                                array(
                                    'taxonomy' => 'product_type',
                                    'field'    => 'term_id',
                                    'terms'    => array( $variable_term_id ),
                                    'operator' => 'NOT IN',
                                ),
                            )
                        );
                    }

                    $query->set( 'tax_query', $tax_query );

                    // Exclude products
                    $not_in = array();
                    if ( isset( $_REQUEST['exclude'] ) ) {
                        $children     = WWOF_API_Helpers::get_variable_children( $_REQUEST['exclude'] );
                        $not_in       = array_unique( array_merge( $_REQUEST['exclude'], $children ) );
                        $post__not_in = is_array( $query->get( 'post__not_in' ) ) ? $query->get(
                            'post__not_in'
                        ) : array();
                        $post__not_in = array_unique( array_merge( $post__not_in, $not_in ) );
                    }

                    // If filter products is set then only display the selected products
                    // Filter products will have higher priority over category filter
                    // If post in has 0 then dont display anything. The 0 comes from order_form_search function where it didnt find any search results.
                    $search            = $_REQUEST['search'] ?? '';
                    $is_wwof_searching = $_REQUEST['is_wwof_searching'] ?? '';

                    if ( $is_wwof_searching === 'no' && $search === '' ) {

                        // If both Include and Category filter is set
                        // Filter the Included products that belongs to the category set
                        if ( isset( $_REQUEST['filter_by_products'] ) && ! empty( $_REQUEST['filter_by_products'] ) && isset( $_REQUEST['filter_by_categories'] ) && ! empty( $_REQUEST['filter_by_categories'] ) ) {

                            // Included Products
                            $include_products      = isset( $_REQUEST['filter_by_products'] ) && isset( $_REQUEST['filter_by_products'] ) ? $_REQUEST['filter_by_products'] : array();
                            $where_products_ids_in = '';

                            if ( ! empty( $include_products ) ) {
                                $products_ids_in       = implode( ', ', $include_products );
                                $where_products_ids_in = 'AND p.ID  IN ( ' . $products_ids_in . ' )';
                            }

                            // Category Filter
                            $filter_by_categories  = isset( $_REQUEST['filter_by_categories'] ) && isset( $_REQUEST['filter_by_categories'] ) ? $_REQUEST['filter_by_categories'] : '';
                            $where_category_ids_in = '';

                            if ( ! empty( $filter_by_categories ) ) {
                                $where_category_ids_in = 'AND tr.term_taxonomy_id IN ( ' . $filter_by_categories . ' )';
                            }

                            global $wpdb;
                            $sql = "SELECT DISTINCT p.ID
                                                            FROM $wpdb->posts p
                                                            INNER JOIN $wpdb->postmeta pm1 ON (p.ID = pm1.post_id)
                                                            INNER JOIN $wpdb->term_relationships tr ON (p.ID = tr.object_id)
                                                            WHERE p.post_type = 'product'
                                                            AND p.post_status = 'publish'
                                                            $where_products_ids_in
                                                            $where_category_ids_in";

                            $results = $wpdb->get_results( $sql );

                            if ( ! empty( $results ) ) {

                                $products_ids = array();
                                foreach ( $results as $product ) {
                                    $products_ids[] = $product->ID;
                                }

                                $variations = WWOF_API_Helpers::get_variable_children( $products_ids );
                                $query->set( 'post__in', array_unique( array_merge( $products_ids, $variations ) ) );
                            } else {
                                // Not match products under the category then return empty
                                $query->set( 'post__in', array( 0 ) );
                            }
                        } else {

                            if ( isset( $_REQUEST['filter_by_products'] ) && ! empty( $_REQUEST['filter_by_products'] ) ) {

                                $products = array_unique(
                                    array_merge(
                                        WWOF_API_Helpers::get_variable_children( $_REQUEST['filter_by_products'] ),
                                        $_REQUEST['filter_by_products']
                                    )
                                );
                                $products = array_diff( $products, $not_in );
                                $products = ! empty( $products ) ? $products : array( 0 );
                                $query->set(
                                    'post__in',
                                    apply_filters(
                                        'wwof_post__in_products_from_include_products',
                                        $products,
                                        $query
                                    )
                                );
                            } elseif ( isset( $_REQUEST['filter_by_categories'] ) && ! empty( $_REQUEST['filter_by_categories'] ) ) {

                                $products = WWOF_API_Helpers::get_product_and_variation_ids_from_category(
                                    $_REQUEST['filter_by_categories']
                                );
                                $products = array_diff( $products, $not_in );
                                $products = ! empty( $products ) ? $products : array( 0 );
                                $query->set(
                                    'post__in',
                                    apply_filters( 'wwof_post__in_products_from_category', $products, $query )
                                );
                            }
                        }
                    }
                }

                // Peform sku search
                // Only used when show variations invidually is disabled
                // We have separte function that handles that (the show variations invidually is enabled) in function order_form_search
                // if (isset($_REQUEST['search']) && isset($_REQUEST['allow_sku_search']) && $_REQUEST['allow_sku_search'] == 'yes' && $_REQUEST['show_variations_individually'] == 'no') {

                // $meta_query = is_array($query->get('meta_query')) ? $query->get('meta_query') : array();
                // $meta_query = array_merge($meta_query, array(array(
                // 'key'     => '_sku',
                // 'value'   => $_REQUEST['search'],
                // 'compare' => 'LIKE',
                // )));

                // $query->set('s', '');
                // $query->set('meta_query', $meta_query);

                // }

                // Show Zero Inventory
                if ( isset( $_REQUEST['show_zero_inventory'] ) && 'yes' !== $_REQUEST['show_zero_inventory'] ) {

                    $meta_query = is_array( $query->get( 'meta_query' ) ) ? $query->get( 'meta_query' ) : array();
                    $meta_query = array_merge(
                        $meta_query,
                        array(
                            array(
                                'key'     => '_stock_status',
                                'value'   => 'instock',
                                'compare' => '=',
                            ),
                        )
                    );

                    $query->set( 'meta_query', $meta_query );
                }

                // Exclude not supported products
                // Get Excluded IDs ( Exclude Bundle and Composite product types since we do not support these yet )
                $excluded_products1 = WWOF_Product_Listing_Helper::wwof_get_excluded_product_ids();

                // Get all products that has product visibility to hidden
                $excluded_products2 = WWOF_Product_Listing_Helper::wwof_get_excluded_hidden_products();

                // Merge excluded products ( Bundle, Composite and Hidden Products)
                $excluded_products = array_merge( $excluded_products1, $excluded_products2 );
                $post__in          = is_array( $query->get( 'post__in' ) ) ? $query->get( 'post__in' ) : array();
                $post__not_in      = is_array( $query->get( 'post__not_in' ) ) ? $query->get(
                    'post__not_in'
                ) : array();

                if ( ! empty( $post__in ) && ! empty( $excluded_products ) ) {
                    $post__in = array_diff( $post__in, $excluded_products );
                }

                $post__not_in = array_unique( array_merge( $post__not_in, $excluded_products ) );

                if ( isset( $_REQUEST['exclude'] ) ) {
                    $children     = WWOF_API_Helpers::get_variable_children( $_REQUEST['exclude'] );
                    $post__not_in = array_unique( array_merge( $post__not_in, $children ) );
                }

                $query->set( 'post__in', apply_filters( 'wwof_post__in_included_products', $post__in, $query ) );
                $query->set(
                    'post__not_in',
                    apply_filters( 'wwof_post__not_in_excluded_products', $post__not_in, $query )
                );

                do_action_ref_array( 'wwof_pre_get_posts_api_request', array( &$query ) );
            }
        }

        /**
         * Set variations per page.
         * Combo dropdown will return show 20 results per page.
         * Standard dropdown will return 100 results per page.
         *
         * @return int
         * @since 1.19
         */
        public function get_variations_per_page() {

            $variations_per_page = 100;
            $selector_type       = 'standard';

            if ( isset( $_POST['form_settings'] ) ) {

                // If selector style is not set, meaning it is the default combo style
                if (
                    ! isset( $_POST['form_settings']['variation_selector_style'] ) ||
                    (
                        isset( $_POST['form_settings']['variation_selector_style'] ) &&
                        'combo' === $_POST['form_settings']['variation_selector_style']
                    )
                ) {
                    $variations_per_page = 20;
                    $selector_type       = 'combo';
                }
            }

            return apply_filters( 'wwof_v2_variations_per_page', $variations_per_page, $selector_type );
        }

        /**
         * Perform search. Override the wp query search.
         * Reasoning is so we can have flexibility on what to search.
         * Searching by variation and returning the variable is quite difficult.
         *
         * @param array           $args    Query args
         * @param WP_REST_Request $request WP Rest Request Object
         *
         * @return array
         * @since 1.20
         */
        public function order_form_search( $args, $request ) {

            // Show Variations Individually
            $show_variations_individually = isset( $_REQUEST['show_variations_individually'] ) && $_REQUEST['show_variations_individually'] == 'yes' ? true : false;
            $allow_sku_search             = isset( $_REQUEST['allow_sku_search'] ) ? $_REQUEST['allow_sku_search'] : 'no';

            // Only perform this custmoize search if show variations individually is disabled AND
            // Allow sku search is enabled
            if ( ! $show_variations_individually && $allow_sku_search !== 'yes' ) {
                return $args;
            }

            // Either Text or Category search/filter
            if ( ! empty( $request['search'] ) || ( isset( $request['is_wwof_searching'] ) && 'yes' === $request['is_wwof_searching'] ) ) {

                global $wpdb;

                $args['s'] = '';
                $search    = $request['search'];

                // Included Products
                $include_products          = $request['filter_by_products'] ?? array();
                $where_products_ids_in     = '';
                $where_products_ids_in_sku = '';

                if ( ! empty( $include_products ) ) {

                    $variations                = WWOF_API_Helpers::get_variable_children( $include_products );
                    $products_ids_in           = array_unique( array_merge( $include_products, $variations ) );
                    $products_ids_in           = implode( ', ', $products_ids_in );
                    $where_products_ids_in     = 'AND p.ID IN ( ' . $products_ids_in . ' )';
                    $where_products_ids_in_sku = "AND ( p.ID IN ( $products_ids_in ) OR p.post_parent IN ( $products_ids_in ) )";
                }

                // Exclude Products
                $exclude_products = $request['exclude'] ?? array();

                // Category Filter
                $filter_by_categories  = $request['filter_by_categories'] ?? '';
                $where_category_ids_in = '';

                if ( ! empty( $filter_by_categories ) ) {
                    $where_category_ids_in = 'AND tr.term_taxonomy_id IN ( ' . $filter_by_categories . ' )';
                }

                // Perform regular search to Title, Content and Excerpt on the product level
                $regular_search_query = $wpdb->prepare(
                    "SELECT DISTINCT p.ID
                                                            FROM $wpdb->posts p
                                                            INNER JOIN $wpdb->postmeta pm1 ON (p.ID = pm1.post_id)
                                                            INNER JOIN $wpdb->term_relationships tr ON (p.ID = tr.object_id)
                                                            WHERE p.post_type = 'product'
                                                            AND p.post_status = 'publish'
                                                            AND (
                                                                p.post_title LIKE %s
                                                                OR p.post_content LIKE %s
                                                                OR p.post_excerpt LIKE %s
                                                            )
                                                            $where_products_ids_in
                                                            $where_category_ids_in
                                                        ",
                    '%' . $search . '%',
                    '%' . $search . '%',
                    '%' . $search . '%'
                );

                $searched_products = $wpdb->get_results( $regular_search_query );

                $post__in = array();

                // Fetch variations from parent
                if ( ! empty( $searched_products ) ) {

                    $products_ids = array();
                    foreach ( $searched_products as $product ) {
                        $products_ids[] = $product->ID;
                    }

                    // Get the variations
                    // Having 2 post type search above will result in inaccurate search since it will fetch variations that doesnt belong to a category for example.
                    if ( $show_variations_individually ) {
                        $ids              = implode( ', ', $products_ids );
                        $variations       = $wpdb->prepare(
                            "SELECT p.ID
                                                            FROM $wpdb->posts p
                                                            WHERE p.post_type = 'product_variation'
                                                            AND p.post_status = 'publish'
                                                            AND p.post_parent IN (" . $ids . ')'
                        );
                        $fetch_variations = $wpdb->get_results( $variations );

                        if ( ! empty( $fetch_variations ) ) {
                            foreach ( $fetch_variations as $product ) {
                                $products_ids[] = $product->ID;
                            }
                        }
                    }

                    $post__in = $products_ids;
                }

                // Perform search for variations
                if ( $show_variations_individually ) {

                    $products_ids = array();
                    // This is in a separate query to avoid issue when doing category/term query
                    // There's issue where it returns variations that doest belong to the category when category search is set
                    $variations_search_query = $wpdb->prepare(
                        "SELECT DISTINCT p.ID
                                                                    FROM $wpdb->posts p
                                                                    INNER JOIN $wpdb->postmeta pm1 ON (p.ID = pm1.post_id)
                                                                    INNER JOIN $wpdb->term_relationships tr ON (p.post_parent = tr.object_id)
                                                                    WHERE p.post_type = 'product_variation'
                                                                    AND p.post_status = 'publish'
                                                                    AND (
                                                                        p.post_title LIKE %s
                                                                        OR p.post_content LIKE %s
                                                                        OR p.post_excerpt LIKE %s
                                                                    )
                                                                    $where_products_ids_in
                                                                    $where_category_ids_in
                                                                ",
                        '%' . $search . '%',
                        '%' . $search . '%',
                        '%' . $search . '%'
                    );

                    $searched_variations = $wpdb->get_results( $variations_search_query );

                    if ( ! empty( $searched_variations ) ) {
                        foreach ( $searched_variations as $product ) {
                            $products_ids[] = $product->ID;
                        }
                    }

                    $post__in = array_unique( array_merge( $post__in, $products_ids ) );
                }

                // Peform sku search
                if ( ! empty( $_REQUEST['search'] ) && isset( $_REQUEST['allow_sku_search'] ) && 'yes' === $_REQUEST['allow_sku_search'] ) {

                    $search_sku_query = $wpdb->prepare(
                        "SELECT DISTINCT p.ID, p.post_parent
                                        FROM $wpdb->posts p
                                        INNER JOIN $wpdb->postmeta pm1 ON (p.ID = pm1.post_id)
                                        WHERE p.post_type IN ('product', 'product_variation')
                                        AND p.post_status = 'publish'
                                        AND pm1.meta_key = '_sku' AND pm1.meta_value LIKE %s
                                        $where_products_ids_in_sku
                                    ",
                        '%' . $_REQUEST['search'] . '%'
                    );

                    $product_with_searched_skus = $wpdb->get_results( $search_sku_query );
                    $searched_sku_products      = array();

                    if ( ! empty( $product_with_searched_skus ) ) {
                        foreach ( $product_with_searched_skus as $product ) {
                            // Show Variations Individually is enabled
                            if ( $show_variations_individually ) {
                                $searched_sku_products[] = $product->ID;
                            } else {
                                $searched_sku_products[] = ! empty( $product->post_parent ) ? $product->post_parent : $product->ID;
                            }
                        }
                    }

                    $post__in   = array_unique( array_merge( $post__in, $searched_sku_products ) );
                    $variations = WWOF_API_Helpers::get_variable_children( $post__in );
                    $post__in   = array_unique( array_merge( $post__in, $variations ) );
                }

                if ( empty( $post__in ) ) {
                    $post__in         = array( 0 );
                    $args['post__in'] = $post__in;
                }

                // Remove exluded products
                if ( ! in_array( 0, $post__in, true ) ) {

                    if ( ! empty( $exclude_products ) ) {

                        $variations           = WWOF_API_Helpers::get_variable_children( $exclude_products );
                        $exclude_products     = array_merge( $variations, $exclude_products );
                        $post__not_in         = is_array( $args['post__not_in'] ) ? $args['post__not_in'] : array();
                        $args['post__not_in'] = array_values(
                            array_unique( array_merge( $post__not_in, $exclude_products ) )
                        );

                        // Remove excluded products from post in or searched products
                        $post__in = array_diff( $post__in, $exclude_products );
                    }
                }

                $args['post__in'] = ! empty( $post__in ) ? $post__in : array( 0 ); // Return empty if no results

            }

            return $args;
        }

        /**
         * Remove product ids that has disregard discounts enabled.
         *
         * @return array
         * @since 2.0
         */
        public function filter_include_products() {

            global $wc_wholesale_prices_premium;

            $include_products = isset( $_POST['form_settings'] ) && isset( $_POST['form_settings']['include_products'] ) ? $_POST['form_settings']['include_products'] : array();

            // Disregard wholesale
            if ( $wc_wholesale_prices_premium && isset( $_POST['wholesale_role'] ) && ! empty( $_POST['wholesale_role'] ) && ! empty( $include_products ) ) {

                $disregard = $wc_wholesale_prices_premium->wwpp_query->disregard_wholesale_products(
                    $_POST['wholesale_role'],
                    $include_products
                );

                $include_products = array_values( array_diff( $include_products, $disregard ) );
            }

            return apply_filters( 'wwof_filter_include_products', $include_products );
        }

        /**
         * Return only data that is needed.
         *
         * @return array
         * @since 2.0.2 Add filter. Added description to the returned results.
         * @since 2.0
         */
        public function get_fields() {

            return apply_filters(
                'wwof_api_product_fields',
                array(
                    'id',
                    'stock_status',
                    'stock_quantity',
                    'backorders',
                    'type',
                    'price_html',
                    'wholesale_data',
                    'images',
                    'permalink',
                    'meta_data',
                    'name',
                    'sku',
                    'stock_status',
                    'description',
                    'short_description',
                    'attributes',
                    'default_attributes',
                    'categories',
                    'low_stock_amount',
                )
            );

        }
    }
}

return new WWOF_API_Request();
