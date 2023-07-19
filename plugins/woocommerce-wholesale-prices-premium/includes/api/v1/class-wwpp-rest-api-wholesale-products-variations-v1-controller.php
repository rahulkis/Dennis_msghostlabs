<?php if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly

if (!class_exists('WWPP_REST_Wholesale_Product_Variations_V1_Controller')) {

    /**
     * Model that houses the logic of WWPP integration with WC API WPP Wholesale Products Variations.
     *
     * @since 1.18
     */
    class WWPP_REST_Wholesale_Product_Variations_V1_Controller extends WWP_REST_Wholesale_Product_Variations_V1_Controller
    {

        /*
        |--------------------------------------------------------------------------
        | Class Properties
        |--------------------------------------------------------------------------
         */

        /**
         * WWPP_API_Wholesale_Products_v1_Controller instance.
         *
         * @var object
         */
        protected $wwpp_api_wholesale_products_v1_controller;

        /*
        |--------------------------------------------------------------------------
        | Class Methods
        |--------------------------------------------------------------------------
         */

        /**
         * WWPP_REST_Wholesale_Product_Variations_V1_Controller constructor.
         *
         * @since 1.18
         * @access public
         */
        public function __construct()
        {

            global $wc_wholesale_prices_premium;

            $this->wwpp_api_wholesale_products_v1_controller = $wc_wholesale_prices_premium->wwpp_rest_api->wwpp_rest_api_wholesale_products_controller;

            // Filter the query arguments of the request.
            add_filter("wwp_rest_wholesale_{$this->post_type}_meta_query", array($this, "rest_meta_query_args"), 10, 4);

            // Fires after a single object is created or updated via the REST API.
            add_action("woocommerce_rest_insert_{$this->post_type}_object", array($this->wwpp_api_wholesale_products_v1_controller, "create_update_wholesale_product"), 10, 3);

            // Wholesale Visibilty
            add_action("wwp_before_get_item_{$this->post_type}_extra_check", array($this, "before_product_get_item_extra_check"), 10, 2);
            add_action("wwp_before_get_items_{$this->post_type}_extra_check", array($this, "before_product_get_item_extra_check"), 10, 2);

            // Allow backorders - Items
            add_filter("wwp_rest_wholesale_{$this->post_type}_query_args", array($this, "rest_allow_backorders_check_items"), 10, 3);

            // Allow backorders - Item
            add_action("wwp_before_variation_get_item", array($this, 'rest_allow_backorders_check_item'), 10, 1);

        }

        /**
         * Wholesale variation restriction/visibility.
         *
         * @param array           $args_copy    Request args copy.
         * @param array           $args         Request args orig.
         * @param WP_REST_Request $request      Request data.
         *
         * @since 1.27
         * @access public
         * @return array
         */
        public function rest_meta_query_args($meta_query, $wholesale_role, $args_copy, $request)
        {

            $meta_query = array(
                $meta_query,
                array(
                    'key'     => WWPP_PRODUCT_WHOLESALE_VISIBILITY_FILTER,
                    'value'   => array($wholesale_role, 'all'),
                    'compare' => 'IN',
                ),
            );

            return apply_filters("wwpp_rest_wholesale_{$this->post_type}_meta_query", $meta_query, $wholesale_role, $args_copy, $request);

        }

        /**
         * If the product is restricted then display an error message.
         *
         * @param array             $extra      Extra checks array. Contains is_valid and message.
         * @param WP_REST_Request   $request    WP REST Request Object
         *
         * @since 1.27
         * @return array
         */
        public function before_product_get_item_extra_check($extra, $request)
        {

            if (apply_filters("wwp_omit_extra_check_{$this->post_type}", false, $extra, $request)) {
                return $extra;
            }

            $wholesale_role = isset($request['wholesale_role']) ? sanitize_text_field($request['wholesale_role']) : '';

            // Parent Variable Visibility
            $product_id                = $request['product_id'];
            $product_visibility_filter = get_post_meta($product_id, 'wwpp_product_wholesale_visibility_filter', false);
            $parent_is_visible         = true;

            if (!in_array('all', $product_visibility_filter) && !in_array($wholesale_role, $product_visibility_filter)) {
                $parent_is_visible = false;
            }

            // Variation Visiblity
            $variation_is_visible = true;

            if (isset($request['id'])) {
                $variation_id = $request['id'];

                // Return immediately since not a product_variation.
                // Probably invalid id.
                if (get_post_type($variation_id) !== $this->post_type) {
                    return $extra;
                }

                $variation_visibility_filter = get_post_meta($variation_id, 'wwpp_product_wholesale_visibility_filter', false);

                if (!in_array('all', $variation_visibility_filter) && !in_array($wholesale_role, $variation_visibility_filter)) {
                    $variation_is_visible = false;
                }
            }

            // Category Visibility
            $product_is_restricted_in_category = WWPP_Helper_Functions::is_product_restricted_in_category($product_id, $wholesale_role);

            if (
                $product_is_restricted_in_category
                ||
                (
                    $parent_is_visible === false
                    ||
                    $variation_is_visible === false
                )
            ) {
                $extra['is_valid'] = false;
                $extra['message']  = new WP_Error('wholesale_rest_product_cannot_view', __('The product is restricted. Please provide the correct wholesale_role parameter for this product.', 'woocommerce-wholesale-prices-premium'), array('status' => 401));
            }

            return $extra;
        }

        /**
         * "Always Allow Backorders" feature when getting variation.
         *
         * @param WP_REST_Request $request      Request data.
         *
         * @since 1.27
         * @access public
         * @return array
         */
        public function rest_allow_backorders_check_item($request)
        {

            $this->wwpp_api_wholesale_products_v1_controller->rest_allow_backorders($request, $this->post_type);

        }

        /**
         * "Always Allow Backorders" feature when getting variations.
         *
         * @param array           $args_copy    Request args copy.
         * @param array           $args         Request args orig.
         * @param WP_REST_Request $request      Request data.
         *
         * @since 1.27
         * @access public
         * @return array
         */
        public function rest_allow_backorders_check_items($args_copy, $args, $request)
        {

            $this->wwpp_api_wholesale_products_v1_controller->rest_allow_backorders($request, $this->post_type);

            return $args_copy;

        }

    }

}
