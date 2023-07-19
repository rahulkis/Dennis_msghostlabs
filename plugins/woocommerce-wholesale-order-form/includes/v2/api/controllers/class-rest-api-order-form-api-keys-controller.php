<?php if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly

if (!class_exists('Order_Form_API_KEYS')) {

    /**
     * Model that houses the logic of Order Form Settngs REST API.
     *
     * @since 1.20
     */
    class Order_Form_API_KEYS extends WP_REST_Controller
    {

        /**
         * Endpoint namespace.
         *
         * @since 1.20
         * @var string
         */
        protected $namespace = 'wwof/v1';

        /**
         * Route base.
         *
         * @since 1.20
         * @var string
         */
        protected $rest_base = 'api-keys';

        /**
         * Order_Form_API_KEYS constructor.
         *
         * @since 1.20
         * @access public
         */
        public function __construct()
        {

            // Register API Routes.
            add_action("rest_api_init", array($this, "register_routes"));

        }

        /**
         * Register API Keys Endpoints.
         *
         * @since 1.20
         * @access public
         *
         * @return void
         */
        public function register_routes()
        {

            register_rest_route(
                $this->namespace,
                '/' . $this->rest_base,
                array(
                    array(
                        'methods'             => WP_REST_Server::READABLE,
                        'callback'            => array($this, 'get_key'),
                        'permission_callback' => array($this, 'permissions_check'),
                    ),
                    array(
                        'methods'             => WP_REST_Server::CREATABLE,
                        'callback'            => array($this, 'create_api_key'),
                        'permission_callback' => array($this, 'permissions_check'),
                    ),
                    array(
                        'methods'             => WP_REST_Server::DELETABLE,
                        'callback'            => array($this, 'revoke_key'),
                        'permission_callback' => array($this, 'permissions_check'),
                    ),
                    'schema' => null,
                )
            );

        }

        /**
         * Get API Keys.
         *
         * @since 1.20
         * @param WP_REST_Request $request Full details about the request.
         * @return WP_Error|WP_REST_Response
         */
        public function get_key($request)
        {

            if (self::is_api_key_valid()) {

                return rest_ensure_response(self::get_keys());

            } else {

                $data = array(
                    'status'  => 'fail',
                    'message' => __('Invalid API keys.', 'woocommerce-wholesale-order-form'),
                );

                rest_ensure_response($data);

            }

        }

        /**
         * Create new API Keys.
         * Note: Most of the code is copied in class-wc-ajax.php under functions update_api_key.
         * Decided to create our own function so that we can manage the behavior if we want to.
         *
         * @since 1.20
         * @param WP_REST_Request $request Full details about the request.
         * @return WP_Error|WP_REST_Response
         */
        public function create_api_key($request)
        {

            global $wpdb;

            try {

                $description = 'WWOF v2';
                $permissions = 'read';
                $user_id     = isset($request['uid']) ? $request['uid'] : get_current_user_id();

                $consumer_key    = 'ck_' . wc_rand_hash();
                $consumer_secret = 'cs_' . wc_rand_hash();

                $data = array(
                    'user_id'         => $user_id,
                    'description'     => $description,
                    'permissions'     => $permissions,
                    'consumer_key'    => wc_api_hash($consumer_key),
                    'consumer_secret' => $consumer_secret,
                    'truncated_key'   => substr($consumer_key, -7),
                );

                $wpdb->insert(
                    $wpdb->prefix . 'woocommerce_api_keys',
                    $data,
                    array(
                        '%d',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                    )
                );

                $key_id                      = $wpdb->insert_id;
                $response                    = $data;
                $response['consumer_key']    = $consumer_key;
                $response['consumer_secret'] = $consumer_secret;
                $response['message']         = __('API Key generated successfully. Make sure to copy your new keys now as the secret key will be hidden once you leave this page.', 'woocommerce');
                $response['revoke_url']      = '<a style="color: #a00; text-decoration: none;" href="' . esc_url(wp_nonce_url(add_query_arg(array('revoke-key' => $key_id), admin_url('admin.php?page=wc-settings&tab=advanced&section=keys')), 'revoke')) . '">' . __('Revoke key', 'woocommerce') . '</a>';

                // Add Consumer key and secret
                update_option('wwof_v2_consumer_key', sanitize_text_field($consumer_key));
                update_option('wwof_v2_consumer_secret', sanitize_text_field($consumer_secret));

            } catch (Exception $e) {
                wp_send_json_error(array('message' => $e->getMessage()));
            }

            wp_send_json_success($response);

        }

        /**
         * Delete API Keys.
         *
         * @since 1.20
         * @param WP_REST_Request $request Full details about the request.
         * @return WP_Error|WP_REST_Response
         */
        public function revoke_key($request)
        {

            global $wpdb;

            if (isset($request['secret'])) { // WPCS: input var okay, CSRF ok.
                $secret = sanitize_text_field($request['secret']);
                $result = $wpdb->get_results($wpdb->prepare("SELECT key_id, user_id FROM {$wpdb->prefix}woocommerce_api_keys WHERE consumer_secret = %s", $secret));

                if (!empty($result)) {
                    $key_id  = $result[0]->key_id;
                    $user_id = $result[0]->user_id;

                    if ($key_id && $user_id && (current_user_can('edit_user') || get_current_user_id() === $user_id)) {
                        if ($this->remove_key($key_id)) {
                            $data = array(
                                'status'  => 'success',
                                'message' => __('Successfully revoked API Key.', 'woocommerce-wholesale-order-form'),
                            );
                        }
                    }
                }

            }

            if (!isset($data)) {
                $data = array(
                    'status'  => 'fail',
                    'message' => __('You do not have permission to revoke this API Key', 'woocommerce-wholesale-order-form'),
                );
            }

            $response = rest_ensure_response($data);

            return $response;

        }

        /**
         * Check whether the user has permission perform the request.
         *
         * @since 1.20
         * @param  WP_REST_Request
         * @return WP_Error|boolean
         */
        public function permissions_check($request)
        {

            // Bypass user checking and for testing API via postman
            if (defined('WWOF_DEV') && WWOF_DEV) {
                return true;
            }

            // Grant permission if admin or shop manager
            if (current_user_can('administrator') || current_user_can('manage_woocommerce')) {
                return true;

            }

            return new WP_Error('rest_cannot_view', __('Invalid Request.', 'woocommerce-wholesale-order-form'), array('status' => rest_authorization_required_code()));

        }

        /**
         * WC API Keys.
         *
         * @since 1.20
         * @return array
         */
        public static function get_keys()
        {
            return array(
                'consumer_key'    => get_option('wwof_v2_consumer_key'),
                'consumer_secret' => get_option('wwof_v2_consumer_secret'),
            );
        }

        /**
         * Remove key.
         *
         * @param  int $key_id API Key ID.
         * @since 1.20
         * @return bool
         */
        private function remove_key($key_id)
        {
            global $wpdb;

            $delete = $wpdb->delete($wpdb->prefix . 'woocommerce_api_keys', array('key_id' => $key_id), array('%d'));

            return $delete;
        }

        /**
         * Validate API Keys saved in WWOF.
         *
         * @since 1.20
         * @return bool
         */
        public static function is_api_key_valid()
        {
            global $wpdb;

            $api_keys = self::get_keys();

            if (strlen($api_keys['consumer_key']) != 43 || strlen($api_keys['consumer_secret']) != 43) {
                return false;
            }

            if (!empty($api_keys['consumer_key']) && !empty($api_keys['consumer_secret'])) {

                // Check if consumer secret exist in db
                $tuncated_key = $wpdb->get_var($wpdb->prepare("SELECT truncated_key FROM {$wpdb->prefix}woocommerce_api_keys WHERE consumer_secret = %s", $api_keys['consumer_secret']));

                // Check if consumer key match with the save option
                if (strpos($api_keys['consumer_key'], $tuncated_key) !== false) {
                    return true;
                }

            }

            return false;

        }

    }

}

return new Order_Form_API_KEYS();
