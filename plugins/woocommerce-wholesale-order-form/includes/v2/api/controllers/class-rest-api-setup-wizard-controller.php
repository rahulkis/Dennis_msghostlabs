<?php if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly

if (!class_exists('Setup_Wizard_API_Controller')) {

    class Setup_Wizard_API_Controller extends WP_REST_Controller
    {

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
        protected $rest_base = 'setup-wizard';

        /**
         * Setup_Wizard_API_Controller constructor.
         *
         * @since 2.0
         * @access public
         */
        public function __construct()
        {

            // Fires when preparing to serve an API request.
            add_action("rest_api_init", array($this, "register_routes"));

        }

        /**
         * Register the routes for the objects of the controller.
         * @since 2.0
         */
        public function register_routes()
        {

            register_rest_route($this->namespace, '/' . $this->rest_base, array(
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => array($this, 'get_items'),
                    'permission_callback' => array($this, 'permissions_check'),
                ),
                array(
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => array($this, 'setup_wizard'),
                    'permission_callback' => array($this, 'permissions_check'),
                ),
            ));

            // Setup Done
            register_rest_route($this->namespace, '/' . $this->rest_base . '/setup-done', array(
                array(
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => array($this, 'setup_done'),
                    'permission_callback' => array($this, 'permissions_check'),
                ),
            ));

        }

        /**
         * Check whether the user has permission perform the request.
         *
         * @since 2.0
         * @param  WP_REST_Request
         * @return WP_Error|boolean
         */
        public function permissions_check($request)
        {

            // Bypass user checking and for testing API via postman
            if (defined('WWOF_DEV') && WWOF_DEV) {
                return true;
            }

            if (empty(get_current_user_id())) {
                return new WP_Error('rest_customer_invalid', __('Resource does not exist.', 'woocommerce-wholesale-order-form'), array('status' => 404));
            }

            if (!user_can(get_current_user_id(), 'manage_options')) {
                return new WP_Error('rest_cannot_view', __('Sorry, you cannot list resources.', 'woocommerce-wholesale-order-form'), array('status' => rest_authorization_required_code()));
            }

            return true;

        }

        /**
         * The App texts.
         *
         * @since 2.0
         * @param WP_REST_Request $request Full data about the request.
         * @return WP_Error|WP_REST_Response
         */
        public function get_items($request)
        {
            $is_migration = isset($request['migration']) ? filter_var($request['migration'], FILTER_VALIDATE_BOOLEAN) : false;

            if ($is_migration == false) {

                // This is for fresh installs
                $first_setup_content = '<p>' . __('This wizard will guide you through setting up your first order form.', 'woocommerce-wholesale-order-form') . '</p>' .
                '<p>' . __('Here\'s a list of perks you will get with your new order forms:', 'woocommerce-wholesale-order-form') . '</p>';

                $generated_forms_table = array(
                    'shortcode' => __('Shortcode', 'woocommerce-wholesale-order-form'),
                    'edit_form' => __('Edit Form', 'woocommerce-wholesale-order-form'),
                    'view_form' => __('View Form', 'woocommerce-wholesale-order-form'),
                    'action'    => __('Action', 'woocommerce-wholesale-order-form'),
                );

                $data = array(
                    array(
                        'id'         => 'step1',
                        'title'      => __('Welcome', 'woocommerce-wholesale-order-form'),
                        'page_title' => __('Wholesale Order Form Setup Wizard', 'woocommerce-wholesale-order-form'),
                        'content'    => $first_setup_content,
                        'perks'      => array(
                            __("Easy to create multiple order forms", 'woocommerce-wholesale-order-form'),
                            __("Search entire catalog on one page", 'woocommerce-wholesale-order-form'),
                            __("Mobile & tablet friendly", 'woocommerce-wholesale-order-form'),
                            __("Simple to customize with drag & drop editing", 'woocommerce-wholesale-order-form'),
                            __("Fast to set up, you'll be done in a minute!", 'woocommerce-wholesale-order-form'),
                        ),
                    ),
                    array(
                        'id'                    => 'step3',
                        'title'                 => __('Generate Order Form', 'woocommerce-wholesale-order-form'),
                        'page_title'            => __('Generate Order Form', 'woocommerce-wholesale-order-form'),
                        'content'               => '<p>' . __('We are generating a default Wholesale Order Form for your set up according to best practices used by most stores. We\'re also creating a page with the form on it that you\'ll be able to send to your wholesale customers.', "woocommerce-wholesale-order-form") . '</p>' .
                        '<p>' . __('You can tweak the design/features of the default form or even create additional new forms via the Wholesale Order Forms page.', "woocommerce-wholesale-order-form") . '</p>' .
                        '<p>' . __('Once you see the forms in the table below, you can click to continue to finish the wizard.', "woocommerce-wholesale-order-form"),
                        'pro_tip'               => __('Pro Tip: You can add additional Order Forms.', "woocommerce-wholesale-order-form") . '</p>',
                        'generated_forms_table' => $generated_forms_table,
                    ),
                );

            } else {

                // This is for migration
                $migration_content = '<p>' . __('This wizard will guide you through migrating your old form over to the new version. All your settings will be retained.', 'woocommerce-wholesale-order-form') . '</p>' .
                '<p>' . __('Here\'s a list of perks you will get when upgrading to the new order form:', 'woocommerce-wholesale-order-form') . '</p>';

                $generated_forms_table = array(
                    'old_shortcode'  => __('Old Shortcode', 'woocommerce-wholesale-order-form'),
                    'new_shortcode'  => __('New Shortcode', 'woocommerce-wholesale-order-form'),
                    'edit_form'      => __('Edit Form', 'woocommerce-wholesale-order-form'),
                    'view_form'      => __('View Form', 'woocommerce-wholesale-order-form'),
                    'view_locations' => __('View Locations', 'woocommerce-wholesale-order-form'),
                    'action'         => __('Action', 'woocommerce-wholesale-order-form'),
                    'default'        => __('(Default)', 'woocommerce-wholesale-order-form'),
                    'tooltip'        => __('For old v1.0 shortcodes this is the form that will be shown to users on the front end.', 'woocommerce-wholesale-order-form'),
                );

                $data = array(
                    array(
                        'id'         => 'step1',
                        'title'      => __('Welcome', 'woocommerce-wholesale-order-form'),
                        'page_title' => __('Wholesale Order Form Migration Wizard', 'woocommerce-wholesale-order-form'),
                        'content'    => $migration_content,
                        'perks'      => array(
                            __("Easy to create multiple order forms", 'woocommerce-wholesale-order-form'),
                            __("Search entire catalog on one page", 'woocommerce-wholesale-order-form'),
                            __("Mobile & tablet friendly", 'woocommerce-wholesale-order-form'),
                            __("Simple to customize with drag & drop editing", 'woocommerce-wholesale-order-form'),
                            __("Fast to set up, you'll be done in a minute!", 'woocommerce-wholesale-order-form'),
                        ),
                    ),
                    array(
                        'id'                    => 'step3',
                        'title'                 => __('Generate Order Form', 'woocommerce-wholesale-order-form'),
                        'page_title'            => __('Generate Order Form', 'woocommerce-wholesale-order-form'),
                        'content'               => '<p>' .
                        __("This new version now supports multiple order forms! We're searching your posts and pages for where you used old order form so we can automatically generate a new form for each unique form.", "woocommerce-wholesale-order-form") . '</p>' .
                        __('Once you see the generated forms below, you can click <b>"Done"</b> to finish the wizard.', 'woocommerce-wholesale-order-form') . '</p>',
                        'generating_forms'      => __('Generating Order Forms. Please wait...', 'woocommerce-wholesale-order-form'),
                        'generated_forms'       => '<h3>' . __('Generated Forms:', 'woocommerce-wholesale-order-form') . '</h3>' .
                        '<p>' . __('Note: The old shortcodes will be "mapped" to the new ones so they won\'t break. You can now tweak these forms individually via the drag and drop form editor.', 'woocommerce-wholesale-order-form') .
                        '</p>',
                        'pro_tip'               => __('Pro Tip: You can add additional Order Forms.', 'woocommerce-wholesale-order-form'),
                        'generated_forms_table' => $generated_forms_table,
                    ),
                );

            }

            // Skip second step if api key is already set and valid.
            if (!Order_Form_API_KEYS::is_api_key_valid()) {
                array_splice($data, 1, 0,
                    array(
                        array(
                            'id'            => 'step2',
                            'title'         => __('Generate API Keys', 'woocommerce-wholesale-order-form'),
                            'page_title'    => __('Generate WooCommerce API Keys', 'woocommerce-wholesale-order-form'),
                            'content'       => '<p>' . __('The Wholesale Order Form uses the WooCommerce API to fetch products to show your customers.', 'woocommerce-wholesale-order-form') . '</p>' .
                            '<p>' . __('You need to allow the plugin to use the API by generating a set of API keys. This is done automatically for you when you click the button below.', 'woocommerce-wholesale-order-form') . '</p>' .
                            '<p>' . __('The access granted is only used for "reading", they can\'t be used to alter anything on your store.', 'woocommerce-wholesale-order-form') . '</p>',
                            'auto_generate' => __('Create WooCommerce API Keys', 'woocommerce-wholesale-order-form'),
                            'loading'       => __('Loading...', 'woocommerce-wholesale-order-form'),
                            'display_key'   => array(
                                'message'         => __('Successfully generated API key. Please proceed to the next step.', 'woocommerce-wholesale-order-form'),
                                'consumer_key'    => __('Consumer Key', 'woocommerce-wholesale-order-form'),
                                'consumer_secret' => __('Consumer Secret', 'woocommerce-wholesale-order-form'),
                            ),
                            'data'          => array_merge(
                                array(
                                    'is_valid' => Order_Form_API_KEYS::is_api_key_valid(),
                                ),
                                Order_Form_API_KEYS::get_keys()
                            ),

                        ),
                    )

                );
            }

            return new WP_REST_Response(
                array(
                    'steps' => $data,
                    'i18n'  => $this->fetch_i18n($request),
                ),
                200
            );
        }

        /**
         * Internationalize texts.
         *
         * @since 2.0
         * @param WP_REST_Request $request Full data about the request.
         * @return WP_Error|WP_REST_Response
         */
        public function fetch_i18n($request)
        {

            return array(
                'wws_logo'          => WWOF_IMAGES_ROOT_URL . 'logo.png',
                'need_support_text' => __('Need support? Contact us.', "woocommerce-wholesale-order-form"),
                'need_support_link' => 'https://wholesalesuiteplugin.com/support/?utm_source=wwof&utm_medium=kb&utm_campaign=wwofinstallerdonecontactus',
                'continue'          => __('Continue', 'woocommerce-wholesale-order-form'),
                'done'              => __('Done', 'woocommerce-wholesale-order-form'),
                'process_complete'  => __('Processing complete!', 'woocommerce-wholesale-order-form'),
                'process_fail'      => __('Process not done!', 'woocommerce-wholesale-order-form'),
                'pro_tip_link'      => 'https://wholesalesuiteplugin.com/kb/how-to-create-multiple-order-forms/?utm_source=wwof&utm_medium=kb&utm_campaign=wwofinstallermultipleforms',
            );

        }

        /**
         * Check if the request is for migration or fresh installs.
         *
         * @since 2.0
         * @param WP_REST_Request $request Full data about the request.
         * @return WP_Error|WP_REST_Response
         */
        public function setup_wizard($request)
        {
            $is_migration = isset($request['migration']) ? filter_var($request['migration'], FILTER_VALIDATE_BOOLEAN) : false;

            if ($is_migration) {
                return $this->migrate_old_form_to_new($request);
            } else {
                return $this->setup_default_order_form($request);
            }

        }

        /**
         * Generate default order form for fresh installs.
         *
         * @since 2.0
         * @param WP_REST_Request $request Full data about the request.
         * @return WP_Error|WP_REST_Response
         */
        public function setup_default_order_form($request)
        {

            $alternate_form = get_option('wwof_general_use_alternate_view_of_wholesale_page');
            $title          = $alternate_form == 'yes' ? __('Wholesale Ordering Alternate', 'woocommerce-wholesale-order-form') : __('Wholesale Ordering Standard', 'woocommerce-wholesale-order-form');

            // Create order form
            $post_id = wp_insert_post(array(
                'post_type'   => 'order_form',
                'post_title'  => $title,
                'post_status' => 'publish',
                'post_author' => get_current_user_id(),
            ));

            // Default Form Shortcode
            $shortcode = '[wwof_product_listing id="' . $post_id . '"]';

            // Edit Form Link
            $edit_form = admin_url("admin.php?page=order-forms&sub-page=edit&post={$post_id}");

            // View Form Link
            $page_id   = $this->generate_default_order_form_page($shortcode);
            $view_form = "";
            if ($page_id > 0) {
                $view_form = get_permalink($page_id);
            }

            // Update with shortcode as content
            wp_update_post(array(
                'ID'           => $post_id,
                'post_content' => $shortcode,
            ));

            // Set order form post meta
            new WWOF_Form_Generator($post_id);

            return wp_send_json_success(
                array(
                    array(
                        'shortcode' => $shortcode,
                        'edit_form' => $edit_form,
                        'view_form' => $view_form,
                    ),
                )

            );

        }

        /**
         * Generate a new page and add the generated order form in the content.
         *
         * @since 2.0
         * @param   $shortcode  The new v2 generated shortcode
         * @return WP_Error|bool|number
         */
        private function generate_default_order_form_page($shortcode)
        {
            $wholesale_page = array(
                'post_content' => $shortcode,
                'post_title'   => __('Wholesale Ordering', 'woocommerce-wholesale-order-form'),
                'post_status'  => 'publish',
                'post_type'    => 'page',
            );

            $page_id = wp_insert_post($wholesale_page);

            if ($page_id === 0 || is_wp_error($page_id)) {

                return false;

            } else {

                // Add an option flag after the migration is done.
                // This will be used to determin if we show the new v2 settings.
                update_option(WWOF_WIZARD_SETUP_DONE, 'yes');

                // Update wholesale page id setting
                // update_option(WWOF_SETTINGS_WHOLESALE_PAGE_ID, $page_id);

                return $page_id;

            }
        }

        /**
         * Migration from old to new form.
         *
         * @since 2.0
         * @param WP_REST_Request $request Full data about the request.
         * @return WP_Error|WP_REST_Response
         */
        public function migrate_old_form_to_new($request)
        {

            global $wpdb;

            // Find all order form shortcodes
            $sql            = "SELECT ID, post_content FROM $wpdb->posts WHERE post_type IN ('post','page') AND post_content LIKE '%[wwof_product_listing%'";
            $results        = $wpdb->get_results($sql);
            $shortcode_list = array();

            // Filter out duplicates and beta
            foreach ($results as $result) {

                preg_match_all('/' . get_shortcode_regex(array('wwof_product_listing')) . '/s', $result->post_content, $matches, PREG_SET_ORDER);

                foreach ($matches as $match) {

                    // Check if shortcode is beta or already have id in the attributes
                    $atts    = shortcode_parse_atts($match[3]);
                    $is_beta = isset($atts['beta']) && $atts['beta'] == true || isset($atts['id']) && $atts['id'] > 0 ? true : false;

                    // Check if there's duplicate shortcodes
                    $key          = substr(wp_hash($match[0]), 0, 5);
                    $is_duplicate = array_search($key, array_column($shortcode_list, 'key'));

                    // Only migrate unique shortcodes
                    // Exclude also beta shortcodes from migration
                    if ($is_duplicate === false && $is_beta === false) {
                        $default          = $match[0] == '[wwof_product_listing]' ? true : false;
                        $shortcode_list[] = array('key' => $key, 'post_id' => $result->ID, 'shortcode' => $match[0], 'atts' => $atts, 'default' => $default);
                    }

                }

            }

            // Generate unique order form
            if (!empty($shortcode_list)) {

                foreach ($shortcode_list as $key => $data) {

                    if (isset($data['post_id'])) {
                        $title = get_the_title($data['post_id']);
                    } else {
                        $alternate_form = get_option('wwof_general_use_alternate_view_of_wholesale_page');
                        $title          = $alternate_form == 'yes' ? __('Wholesale Ordering Alternate', 'woocommerce-wholesale-order-form') : __('Wholesale Ordering Standard', 'woocommerce-wholesale-order-form');
                    }

                    // Create order form
                    $post_id = wp_insert_post(array(
                        'post_type'   => 'order_form',
                        'post_title'  => $title,
                        'post_status' => 'publish',
                        'post_author' => get_current_user_id(),
                    ));

                    // New Shortcode
                    $new_shortcode                         = '[wwof_product_listing id="' . $post_id . '"]';
                    $shortcode_list[$key]['new_shortcode'] = $new_shortcode;

                    // Edit Form Link
                    $edit_form                         = admin_url("admin.php?page=order-forms&sub-page=edit&post={$post_id}");
                    $shortcode_list[$key]['edit_form'] = $edit_form;

                    // View Form Link
                    if (isset($data['post_id'])) {
                        $view_form                         = get_permalink($data['post_id']);
                        $shortcode_list[$key]['view_form'] = $view_form;
                    }

                    // Update with shortcode as content
                    wp_update_post(array(
                        'ID'           => $post_id,
                        'post_content' => $new_shortcode,
                    ));

                    // Save the old shortcode in the meta
                    if (isset($shortcode_list[$key]['shortcode'])) {
                        update_post_meta($post_id, 'old_shortcode', $shortcode_list[$key]['shortcode']);
                    }

                    // Set order form post meta
                    new WWOF_Form_Generator($post_id, $data);

                }

            }

            // Save migration data into options
            if (!empty($shortcode_list)) {
                update_option(WWOF_MIGRATION_DATA_MAPPING, $shortcode_list);
            }

            // Add an option flag after the migration is done.
            // This will be used to determin if we show the new v2 settings.
            update_option(WWOF_WIZARD_SETUP_DONE, 'yes');

            do_action('wwof_wizard_migrate', $request, $shortcode_list);

            return wp_send_json_success($shortcode_list);

        }

        /**
         * The setup is done
         *
         * @since 2.0
         * @param WP_REST_Request $request Full data about the request.
         * @return WP_Error|WP_REST_Response
         */
        public function setup_done($request)
        {

            Order_Form_DB::version_2_update();

            $data = apply_filters('wwof_migration_setup_done', array('redirect' => admin_url("admin.php?page=order-forms")));

            do_action('wwof_wizard_done', $request, $data);

            return wp_send_json_success($data);

        }

    }

}

return new Setup_Wizard_API_Controller();
