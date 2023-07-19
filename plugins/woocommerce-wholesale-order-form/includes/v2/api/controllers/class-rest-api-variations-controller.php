<?php if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
// Exit if accessed directly

if ( ! class_exists( 'Variations_API_Controller' ) ) {

    class Variations_API_Controller extends WP_REST_Controller {

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
        protected $rest_base = 'variations';

        /**
         * Variations_API_Controller constructor.
         *
         * @since  2.0.4
         * @access public
         */
        public function __construct() {

            // Fires when preparing to serve an API request.
            add_action( 'rest_api_init', array( $this, 'register_routes' ) );

        }

        /**
         * Register the routes for the objects of the controller.
         *
         * @since 2.0.4
         */
        public function register_routes() {

            register_rest_route(
                $this->namespace,
                '/' . $this->rest_base,
                array(
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => array( $this, 'get_items' ),
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
         * @since 2.0.4
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

            return false;

        }

        /**
         * Checks the type of request.
         * Theres 3 types:
         * - get_variations: First to be called, get all variable variations via sql but without wholesale data.
         * - get_variations_wholesale_data: Second to be called, fetches all wholesale variation data based on the auto selected variation ids.
         * - get_variation_wholesale_data: Triggered when changing variation dropdown.
         *
         * @param WP_REST_Request $request Full data about the request.
         *
         * @return WP_Error|WP_REST_Response
         * @since 2.0.4
         */
        public function get_items( $request ) {

            try {

                switch ( $request['type'] ) {

                    case 'get_variation_wholesale_data':
                        return new WP_REST_Response( $this->get_variation_wholesale_data( $request ), 200 );

                        break;

                    case 'get_variations_wholesale_data':
                        return new WP_REST_Response( $this->get_variations_wholesale_data( $request ), 200 );

                        break;

                    case 'get_variations':
                        return new WP_REST_Response( $this->get_variations( $request ), 200 );

                        break;

                    default:
                        break;

                }
            } catch ( HttpClientException $e ) {

                return new WP_REST_Response(
                    array(
                        'status'  => 'error',
                        'message' => $e->getMessage(),
                    ),
                    200
                );

            }

        }

        /**
         * Get the variation wholesale data.
         * This is triggered when changing variation in the standard or combo dropdown.
         * After this, it will re-update the price column for appropriate wholesale price.
         *
         * @param WP_REST_Request $request Full data about the request.
         *
         * @return WP_Error|WP_REST_Response
         * @since 2.0.4
         */
        public function get_variation_wholesale_data( $request ) {

            global $wc_wholesale_prices;

            try {

                $product_id = sanitize_text_field( $request['variationID'] );
                $product    = wc_get_product( $product_id );

                // Note: This only triggers for this case
                // Avoid getting duplicate wholesale price. Basically the code already detected that the user that is logged-in as a wholesale customer so the filter woocommerce_get_price_html is triggered twice for this scenario.
                // The remove filter will allow us to get the regular price before getting the wholesale price.
                // If you trace the function we are using the api get_wwp_meta_data
                // If you check the code:
                // $meta_data['price_html'] = $wwp_wholesale_prices_instance->wholesale_price_html_filter($product->get_price_html(), $product, array($wholesale_role));
                // This code $product->get_price_html() will already have wholesale price in it.
                // We dont need wholesale price here yet since we are calling wholesale_price_html_filter.
                // We are adding it back since others might be using the filter.
                // Example output of issue:
                // $20.000
                // Wholesale Price: $15.000
                // Wholesale Price: $15.000
                remove_filter(
                    'woocommerce_get_price_html',
                    array( $wc_wholesale_prices->wwp_wholesale_prices, 'wholesale_price_html_filter' ),
                    10,
                    2
                );
                $wholesale_data = $wc_wholesale_prices->wwp_rest_api->wwp_rest_api_wholesale_products_controller->get_wwp_meta_data(
                    $product,
                    $request
                );
                add_filter(
                    'woocommerce_get_price_html',
                    array( $wc_wholesale_prices->wwp_wholesale_prices, 'wholesale_price_html_filter' ),
                    10,
                    2
                );

                return array(
                    'status'         => 'success',
                    'wholesale_data' => $wholesale_data,
                );
            } catch ( HttpClientException $e ) {

                return new WP_REST_Response(
                    array(
                        'status'  => 'error',
                        'message' => $e->getMessage(),
                    ),
                    200
                );

            }

        }

        /**
         * Get the auto selected variations wholesale data.
         * Gather all the auto selected variation ids and fetch the variation wholesale data in one request.
         * After this, it will re-update the price column for appropriate wholesale price.
         *
         * @param WP_REST_Request $request Full data about the request.
         *
         * @return WP_Error|WP_REST_Response
         * @since 2.0.4
         */
        public function get_variations_wholesale_data( $request ) {

            global $wc_wholesale_prices;

            try {

                $variations = $request['variations'];
                $data       = array();

                if ( ! empty( $variations ) ) {
                    foreach ( $variations as $variation ) {

                        $product_data = json_decode( $variation );

                        if ( $product_data ) {

                            $product = wc_get_product( $product_data[0]->variationId );

                            // Note: This only triggers for this case
                            // Avoid getting duplicate wholesale price. Basically the code already detected that the user that is logged-in as a wholesale customer so the filter woocommerce_get_price_html is triggered twice for this scenario.
                            // The remove filter will allow us to get the regular price before getting the wholesale price.
                            // If you trace the function we are using the api get_wwp_meta_data
                            // If you check the code:
                            // $meta_data['price_html'] = $wwp_wholesale_prices_instance->wholesale_price_html_filter($product->get_price_html(), $product, array($wholesale_role));
                            // This code $product->get_price_html() will already have wholesale price in it.
                            // We dont need wholesale price here yet since we are calling wholesale_price_html_filter.
                            // We are adding it back since others might be using the filter.
                            // Example output of issue:
                            // $20.000
                            // Wholesale Price: $15.000
                            // Wholesale Price: $15.000
                            remove_filter(
                                'woocommerce_get_price_html',
                                array(
                                    $wc_wholesale_prices->wwp_wholesale_prices,
                                    'wholesale_price_html_filter',
                                ),
                                10,
                                2
                            );
                            $data[ $product_data[0]->productId ] = array(
                                'variationId'    => $product_data[0]->variationId,
                                'wholesale_data' => $wc_wholesale_prices->wwp_rest_api->wwp_rest_api_wholesale_products_controller->get_wwp_meta_data(
                                    $product,
                                    $request
                                ),
                            );
                            add_filter(
                                'woocommerce_get_price_html',
                                array(
                                    $wc_wholesale_prices->wwp_wholesale_prices,
                                    'wholesale_price_html_filter',
                                ),
                                10,
                                2
                            );

                        }
					}
                }

                return array(
                    'status' => 'success',
                    'data'   => $data,
                );
            } catch ( HttpClientException $e ) {

                return new WP_REST_Response(
                    array(
                        'status'  => 'error',
                        'message' => $e->getMessage(),
                    ),
                    200
                );

            }

        }

        /**
         * Get the products variations.
         * This is triggered after fetching the products. All variable ids are gathered and perform another get variations request.
         * Note: No wholesale data is returned on this stage yet since the main cause of delay is the getting of wholesale data.
         *       Grabbing of wholesale data is performed after all dropdown is populated and variations are auto selected.
         *
         * @param WP_REST_Request $request Full data about the request.
         *
         * @return array
         * @since 2.0.4
         */
        public function get_variations( $request ) {

            global $wpdb, $wc_wholesale_prices;

            $ids = array();

            // The variable ids to fetch for variations
            // Example unserialized data:
            // stdClass Object
            // (
            // [8383] => stdClass Object
            // (
            // [fetching] => 1
            // [id] => 8383
            // [type] => variable
            // )
            // )
            $products = json_decode( $request['products'] );

            // The order form settings
            // Example unserialized data:
            // stdClass Object
            // (
            // [variation_selector_style] => standard
            // [selected_category] =>
            // [filtered_categories] => Array
            // (
            // )
            // [tax_display] =>
            // [excluded_categories] => Array
            // (
            // )
            // [subtotal_pretext] =>
            // [subtotal_suffix] =>
            // [quantity_restriction] =>
            // [products_per_page] => 10
            // [show_zero_inventory_products] =>
            // [show_variations_individually] =>
            // [lazy_loading] =>
            // [sort_by] => date
            // [sort_order] => desc
            // [exclude_products] => Array
            // (
            // )
            // [include_products] => Array
            // (
            // [0] => 8383
            // )
            // )
            $form_settings = json_decode( $request['form_settings'] );

            // Exclude products
            $exclude_products_condition = '';
            if ( $form_settings instanceof stdClass && property_exists(
                    $form_settings,
                    'exclude_products'
                ) && ! empty( $form_settings->exclude_products ) ) {
                $exclude_products           = implode( ', ', $form_settings->exclude_products );
                $exclude_products_condition = 'AND ID NOT IN (' . $exclude_products . ')';
            }

            // Get products ids to fetch the variations
            foreach ( $products as $key => $data ) {
                $ids[] = $key;
            }

            $ids        = implode( ',', $ids );
            $variations = array();

            if ( ! empty( $ids ) ) {

                $is_general_discount_set                = WWOF_Functions::is_wwpp_active(
                ) ? WWPP_API_Helpers::has_wholesale_general_discount( $request['wholesale_customer'] ) : false;
                $only_show_wholesale_products           = get_option(
                    'wwpp_settings_only_show_wholesale_products_to_wholesale_users'
                ) === 'yes' ? true : false;
                $only_show_wholesale_products_condition = '';

                // If only show is set and no general discount then perform check on product and category level
                $wholesale_role = sanitize_text_field( $request['wholesale_role'] );
                if ( $only_show_wholesale_products && ! $is_general_discount_set && ! empty( $wholesale_role ) ) {
                    $ids = $wpdb->prepare(
                        "SELECT DISTINCT p2.post_parent
                                FROM $wpdb->posts p2
                                INNER JOIN $wpdb->postmeta pm2 ON (p2.ID = pm2.post_id)
                                WHERE (
                                        ( pm2.meta_key = %s AND pm2.meta_value > 0  )
                                        OR
                                        ( pm2.meta_key = %s AND pm2.meta_value != '' )
                                        or
                                        ( pm2.meta_key = %s AND pm2.meta_value = 'yes' )
                                    )
                                AND p2.post_parent IN ($ids)",
                        $wholesale_role . '_wholesale_price',
                        $wholesale_role . '_variations_with_wholesale_price',
                        $wholesale_role . '_have_wholesale_price_set_by_product_cat'
                    );
                }

                // Perform 1 query to fetch all variable variations
                $children = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT DISTINCT p.ID, p.post_parent
                                                    FROM $wpdb->posts p
                                                    INNER JOIN $wpdb->postmeta pm1 ON (p.ID = pm1.post_id)
                                                    WHERE p.post_parent IN ($ids)
                                                    AND p.post_status = 'publish'
                                                    $exclude_products_condition
                                                    ORDER BY p.menu_order ASC"
                    ),
                    ARRAY_A
                );

                // Avoid getting wholesale price. It will cause slow response time.
                // We will ge the wholesale data once the variation is auto selected or has changed.
                // We will only get the base regular price html
                // $product->get_price_html() will return the wholesale price + regular which we dont need for this case.
                remove_filter(
                    'woocommerce_get_price_html',
                    array( $wc_wholesale_prices->wwp_wholesale_prices, 'wholesale_price_html_filter' ),
                    10,
                    2
                );

                /**
                 * Integration with WWPP backorder overrides when displaying product variations.
                 * The $product->get_backorders() call below is not filtered at this point by WWPP so we need to detect
                 * if the setting is turned on and if so, manually add the filters to turn the product is in stock
                 * to 'true' and backorders availability to 'notify' so that the form will react and show the
                 * appropriate value in the In Stock column, Quantity, and Add To Cart
                 */
                global $wc_wholesale_prices_premium;

                /**
                 * Check if global variable is not null. To avoid errors when calling methods from null object.
                 */
                if ( $wc_wholesale_prices_premium ) {
                    $wc_wholesale_prices_premium->wwpp_rest_api->wwpp_rest_api_wholesale_products_controller->rest_allow_backorders(
                        $request,
                        'product_variation'
                    );
                }

                // Loop through variations (variable children)
                foreach ( $children as $child ) {

                    $post_parent = $child['post_parent'];

                    $product = wc_get_product( $child['ID'] );

                    $variations[ $post_parent ][] = array(
                        'id'             => $child['ID'],
                        'attributes'     => $this->get_attributes( $product ),
                        'backorders'     => $product->get_backorders(),
                        'description'    => wpautop( do_shortcode( $product->get_description() ) ),
                        'meta_data'      => array(),
                        'permalink'      => $product->get_permalink(),
                        'sku'            => $product->get_sku(),
                        'stock_quantity' => $product->get_stock_quantity(),
                        'stock_status'   => $product->get_stock_status(),
                        'price_html'     => $product->get_price_html(),
                    );

                }

                // Re-add the function to get the wholesale price html
                add_filter(
                    'woocommerce_get_price_html',
                    array( $wc_wholesale_prices->wwp_wholesale_prices, 'wholesale_price_html_filter' ),
                    10,
                    2
                );

            }

            return array(
                'status'     => 'success',
                'variations' => $variations,
            );

        }

        /**
         * Get the attributes for a product or product variation.
         *
         * @param WC_Product|WC_Product_Variation $product Product instance.
         *
         * @return array
         * @since 2.0.4
         */
        protected function get_attributes( $product ) {

            $attributes = array();

            if ( $product->is_type( 'variation' ) ) {

                // Variation attributes.
                foreach ( $product->get_variation_attributes() as $attribute_name => $attribute ) {
                    $name = str_replace( 'attribute_', '', $attribute_name );

                    if ( ! $attribute ) {
                        continue;
                    }

                    // Taxonomy-based attributes are prefixed with `pa_`, otherwise simply `attribute_`.
                    if ( 0 === strpos( $attribute_name, 'attribute_pa_' ) ) {
                        $option_term  = get_term_by( 'slug', $attribute, $name );
                        $attributes[] = array(
                            'id'     => wc_attribute_taxonomy_id_by_name( $name ),
                            'name'   => $this->get_attribute_taxonomy_label( $name ),
                            'option' => $option_term && ! is_wp_error( $option_term ) ? $option_term->name : $attribute,
                        );
                    } else {
                        $attributes[] = array(
                            'id'     => 0,
                            'name'   => $name,
                            'option' => $attribute,
                        );
                    }
                }
            } else {
                foreach ( $product->get_attributes() as $attribute ) {
                    if ( $attribute['is_taxonomy'] ) {
                        $attributes[] = array(
                            'id'        => wc_attribute_taxonomy_id_by_name( $attribute['name'] ),
                            'name'      => $this->get_attribute_taxonomy_label( $attribute['name'] ),
                            'position'  => (int) $attribute['position'],
                            'visible'   => (bool) $attribute['is_visible'],
                            'variation' => (bool) $attribute['is_variation'],
                            'options'   => $this->get_attribute_options( $product->get_id(), $attribute ),
                        );
                    } else {
                        $attributes[] = array(
                            'id'        => 0,
                            'name'      => $attribute['name'],
                            'position'  => (int) $attribute['position'],
                            'visible'   => (bool) $attribute['is_visible'],
                            'variation' => (bool) $attribute['is_variation'],
                            'options'   => $this->get_attribute_options( $product->get_id(), $attribute ),
                        );
                    }
                }
            }

            return $attributes;
        }

        /**
         * Get attribute taxonomy label.
         *
         * @param string $name Taxonomy name.
         *
         * @return string
         * @since 2.0.4
         */
        protected function get_attribute_taxonomy_label( $name ) {

            $tax    = get_taxonomy( $name );
            $labels = get_taxonomy_labels( $tax );

            return $labels->singular_name;
        }

        /**
         * Get attribute options.
         *
         * @param int   $product_id Product ID.
         * @param array $attribute  Attribute data.
         *
         * @return array
         * @since 2.0.4
         */
        protected function get_attribute_options( $product_id, $attribute ) {

            if ( isset( $attribute['is_taxonomy'] ) && $attribute['is_taxonomy'] ) {
                return wc_get_product_terms( $product_id, $attribute['name'], array( 'fields' => 'names' ) );
            } elseif ( isset( $attribute['value'] ) ) {
                return array_map( 'trim', explode( '|', $attribute['value'] ) );
            }

            return array();
        }

    }

}

return new Variations_API_Controller();
