<?php
if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly

if (!class_exists('WWOF_Settings')) {

    class WWOF_Settings extends WC_Settings_Page
    {

        /**
         * Constructor.
         */
        public function __construct()
        {

            $this->id    = 'wwof_settings';
            $this->label = __('Wholesale Ordering', 'woocommerce-wholesale-order-form');

            add_filter('woocommerce_settings_tabs_array', array($this, 'add_settings_page'), 30); // 30 so it is after the emails tab
            add_action('woocommerce_settings_' . $this->id, array($this, 'output'));
            add_action('woocommerce_settings_save_' . $this->id, array($this, 'save'));
            add_action('woocommerce_sections_' . $this->id, array($this, 'output_sections'));

            add_action('woocommerce_admin_field_wwof_button', array($this, 'render_wwof_button'));
            add_action('woocommerce_admin_field_wwof_editor', array($this, 'render_wwof_editor'));
            add_action('woocommerce_admin_field_wwof_help_resources', array($this, 'render_wwof_help_resources'));
            add_action('woocommerce_admin_field_wwof_image_dimension', array($this, 'render_wwof_image_dimension'));
            add_action('woocommerce_admin_field_wwof_clear_product_caching', array($this, 'render_wwof_clear_product_caching'), 10);
            add_action('woocommerce_admin_field_wwof_api_status', array($this, 'render_wwof_api_status'), 10);

            add_filter('wwof_settings_general_section_settings', array($this, 'show_hide_setting'), 10);

            if (is_main_site()) {
                add_action('woocommerce_admin_field_wwof_force_fetch_update_data_button', array($this, 'render_plugin_settings_custom_field_wwlc_force_fetch_update_data_button'), 10);
            }

        }

        /**
         * Get sections.
         *
         * @return array
         * @since 1.0.0
         */
        public function get_sections()
        {

            $sections = array(
                ''                                  => __('General', 'woocommerce-wholesale-order-form'),
                'wwof_settings_permissions_section' => __('Permissions', 'woocommerce-wholesale-order-form'),
                'wwof_settings_help_section'        => __('Help', 'woocommerce-wholesale-order-form'),
            );

            return apply_filters('woocommerce_get_sections_' . $this->id, $sections);
        }

        /**
         * Output the settings.
         *
         * @since 1.0.0
         */
        public function output()
        {

            global $current_section, $wpdb;

            $settings = $this->get_settings($current_section);
            WC_Admin_Settings::output_fields($settings);

        }

        /**
         * Save settings.
         *
         * @since 1.0.0
         */
        public function save()
        {

            global $current_section;

            $settings = $this->get_settings($current_section);

            // Filter wysiwyg content so it gets stored properly after sanitization
            if (isset($_POST['noaccess_message']) && !empty($_POST['noaccess_message'])) {

                foreach ($_POST['noaccess_message'] as $index => $content) {

                    $_POST[$index] = htmlentities(wpautop($content));

                }

            }

            WC_Admin_Settings::save_fields($settings);

        }

        /**
         * Get settings array.
         *
         * @param string $current_section
         *
         * @return mixed
         * @since 1.0.0
         */
        public function get_settings($current_section = '')
        {

            if ($current_section == 'wwof_settings_permissions_section') {

                // Permissions Section
                $settings = apply_filters('wwof_settings_permissions_section_settings', $this->_get_permissions_section_settings());

            } elseif ($current_section == 'wwof_settings_help_section') {

                // Help Section
                $settings = apply_filters('wwof_settings_help_section_settings', $this->_get_help_section_settings());

            } else {

                // General Settings
                $settings = apply_filters('wwof_settings_general_section_settings', $this->_get_general_section_settings());

            }

            return apply_filters('woocommerce_get_settings_' . $this->id, $settings, $current_section);

        }

        /*
        |--------------------------------------------------------------------------------------------------------------
        | Section Settings
        |--------------------------------------------------------------------------------------------------------------
         */

        /**
         * Get general section settings.
         *
         * @since 1.0.0
         * @since 1.3.0 Add option to show/hide quantity based discounts on wholesale order page.
         *
         * @return array
         */
        private function _get_general_section_settings()
        {

            return array(

                array(
                    'title' => __('WooCommerce API', 'woocommerce-wholesale-order-form'),
                    'type'  => 'title',
                    'desc'  => __('Create consumer key and secret in WooCommerce > Settings > Advanced > REST API', 'woocommerce-wholesale-order-form') . '<br/><br/>' .
                    __('The Wholesale Order Form uses the WooCommerce API to fetch products to show your customers. The plugin only requires "read" permission.', 'woocommerce-wholesale-order-form'),
                    'id'    => 'wwof_woocommerce_api_title',
                ),

                array(
                    'title'    => __('API Status', 'woocommerce-wholesale-order-form'),
                    'type'     => 'wwof_api_status',
                    'desc_tip' => __('Check API Status', 'woocommerce-wholesale-order-form'),
                    'id'       => 'wwof_api_status',
                ),

                array(
                    'title'    => __('Consumer Key', 'woocommerce-wholesale-order-form'),
                    'type'     => 'text',
                    'desc'     => __('WooCommerce API consumer key', 'woocommerce-wholesale-order-form'),
                    'desc_tip' => true,
                    'id'       => 'wwof_v2_consumer_key',
                ),

                array(
                    'title'    => __('Consumer Secret', 'woocommerce-wholesale-order-form'),
                    'type'     => 'password',
                    'desc'     => __('WooCommerce API Consumer Secret', 'woocommerce-wholesale-order-form'),
                    'desc_tip' => true,
                    'id'       => 'wwof_v2_consumer_secret',
                ),

                array(
                    'title'       => __('Generate API Keys', 'woocommerce-wholesale-order-form'),
                    'type'        => 'wwof_button',
                    'button_text' => __('Auto Generate', 'woocommerce-wholesale-order-form'),
                    'desc'        => '',
                    'desc_tip'    => __('This button will show up if keys are empty or invalid. Once the keys are generated it is saved directly.', 'woocommerce-wholesale-order-form'),
                    'id'          => 'wwof_generate_api_keys',
                    'class'       => 'button button-secondary',
                ),

                array(
                    'type' => 'sectionend',
                    'id'   => 'wwof_woocommerce_api_sectionend',
                ),

            );

        }

        /**
         * Get permissions section settings.
         *
         * @return array
         * @since 1.0.0
         */
        private function _get_permissions_section_settings()
        {

            // Get all user roles
            global $wp_roles;

            if (!isset($wp_roles)) {
                $wp_roles = new WP_Roles();
            }

            $allUserRoles = $wp_roles->get_names();

            return array(

                array(
                    'title' => __('Permissions Options', 'woocommerce-wholesale-order-form'),
                    'type'  => 'title',
                    'desc'  => '',
                    'id'    => 'wwof_permissions_main_title',
                ),

                array(
                    'title'             => __('User Role Filter', 'woocommerce-wholesale-order-form'),
                    'type'              => 'multiselect',
                    'desc'              => __('Only allow a given user role/s to access the wholesale page. Left blank to disable filter.', 'woocommerce-wholesale-order-form'),
                    'desc_tip'          => true,
                    'id'                => 'wwof_permissions_user_role_filter',
                    'class'             => 'chosen_select',
                    'css'               => 'min-width:300px;',
                    'custom_attributes' => array(
                        'multiple'         => 'multiple',
                        'data-placeholder' => __('Select Some User Roles...', 'woocommerce-wholesale-order-form'),
                    ),
                    'options'           => $allUserRoles,
                ),

                array(
                    'type' => 'sectionend',
                    'id'   => 'wwof_permissions_role_filter_sectionend',
                ),

                array(
                    'title' => __('Access Denied Message', 'woocommerce-wholesale-order-form'),
                    'type'  => 'title',
                    'desc'  => __('Message to display to users who do not have permission to access the wholesale order form.', 'woocommerce-wholesale-order-form'),
                    'id'    => 'wwof_permissions_noaccess_section_title',
                ),

                array(
                    'title'    => __('Title', 'woocommerce-wholesale-order-form'),
                    'type'     => 'text',
                    'desc'     => __('Defaults to <b>"Access Denied"</b> if left blank', 'woocommerce-wholesale-order-form'),
                    'desc_tip' => true,
                    'id'       => 'wwof_permissions_noaccess_title',
                    'css'      => 'min-width: 400px;',
                ),

                array(
                    'title'    => __('Message', 'woocommerce-wholesale-order-form'),
                    'type'     => 'wwof_editor',
                    'desc'     => __('Defaults to <b>"You do not have permission to view wholesale product listing"</b> if left blank', 'woocommerce-wholesale-order-form'),
                    'desc_tip' => true,
                    'id'       => 'wwof_permissions_noaccess_message',
                    'css'      => 'min-width: 400px; min-height: 100px;',
                ),

                array(
                    'title'    => __('Login URL', 'woocommerce-wholesale-order-form'),
                    'type'     => 'text',
                    'desc'     => __('URL of the login page. Uses default WordPress login URL if left blank', 'woocommerce-wholesale-order-form'),
                    'desc_tip' => true,
                    'id'       => 'wwof_permissions_noaccess_login_url',
                    'css'      => 'min-width: 400px;',
                ),

                array(
                    'type' => 'sectionend',
                    'id'   => 'wwof_permissions_sectionend',
                ),

            );

        }

        /**
         * Get help section settings.
         *
         * @return array
         * @since 1.0.0
         */
        private function _get_help_section_settings()
        {

            return array(

                array(
                    'title' => __('Help Options', 'woocommerce-wholesale-order-form'),
                    'type'  => 'title',
                    'desc'  => '',
                    'id'    => 'wwof_help_main_title',
                ),

                array(
                    'name' => '',
                    'type' => 'wwof_help_resources',
                    'desc' => '',
                    'id'   => 'wwof_help_help_resources',
                ),

                array(
                    'name' => __('Clean up plugin options on un-installation', 'woocommerce-wholesale-order-form'),
                    'type' => 'checkbox',
                    'desc' => __('If checked, removes all plugin options when this plugin is uninstalled. <b>Warning:</b> This process is irreversible.', 'woocommerce-wholesale-order-form'),
                    'id'   => 'wwof_settings_help_clean_plugin_options_on_uninstall',
                ),

                array(
                    'name' => '',
                    'type' => 'wwof_force_fetch_update_data_button',
                    'desc' => '',
                    'id'   => 'wwof_settings_force_fetch_update_data_button',
                ),

                array(
                    'type' => 'sectionend',
                    'id'   => 'wwof_help_sectionend',
                ),

            );

        }

        /*
        |--------------------------------------------------------------------------------------------------------------
        | Custom Settings Fields
        |--------------------------------------------------------------------------------------------------------------
         */

        /**
         * Render custom setting field (wwof button)
         *
         * @param $value
         * @since 1.0.0
         */
        public function render_wwof_button($value)
        {

            if ($value['id'] == 'wwof_generate_api_keys' && Order_Form_API_KEYS::is_api_key_valid() == true) {
                return;
            }

            $field = WC_Admin_Settings::get_field_description($value);

            // Change type accordingly
            $type = $value['type'];
            if ($type == 'wwof_button') {
                $type = 'button';
            }

            // Custom attribute handling
            $custom_attributes = array();

            if (!empty($value['custom_attributes']) && is_array($value['custom_attributes'])) {
                foreach ($value['custom_attributes'] as $attribute => $attribute_value) {
                    $custom_attributes[] = esc_attr($attribute) . '="' . esc_attr($attribute_value) . '"';
                }
            }

            ob_start();?>

            <tr valign="top">
                <th scope="row" class="titledesc">
                    <label for="<?php echo esc_attr($value['id']); ?>">
                        <?php echo esc_html($value['title']); ?>
                        <?php echo $field['tooltip_html'] ?>
                    </label>
                </th>
                <td class="forminp forminp-<?php echo sanitize_title($value['type']); ?>">
                    <input
                        name="<?php echo esc_attr($value['id']); ?>"
                        id="<?php echo esc_attr($value['id']); ?>"
                        type="<?php echo esc_attr($type); ?>"
                        style="<?php echo esc_attr($value['css']); ?>"
                        value="<?php echo esc_attr($value['button_text']); ?>"
                        class="<?php echo esc_attr($value['class']); ?>"
                        <?php echo implode(' ', $custom_attributes); ?>
                        />
                    <span class="spinner" style="margin-top: 3px; float: none;"></span>
                    <?php echo $value['desc']; ?>


                </td>
            </tr>
            <?php echo ob_get_clean();

        }

        /**
         * Render custom setting field (wwof editor)
         *
         * @param $value
         * @since 1.1.0
         */
        public function render_wwof_editor($value)
        {

            // Custom attribute handling
            $custom_attributes = array();

            if (!empty($value['custom_attributes']) && is_array($value['custom_attributes'])) {
                foreach ($value['custom_attributes'] as $attribute => $attribute_value) {
                    $custom_attributes[] = esc_attr($attribute) . '="' . esc_attr($attribute_value) . '"';
                }
            }

            // Description handling
            if (true === $value['desc_tip']) {

                $description = '';
                $tip         = $value['desc'];

            } elseif (!empty($value['desc_tip'])) {

                $description = $value['desc'];
                $tip         = $value['desc_tip'];

            } elseif (!empty($value['desc'])) {

                $description = $value['desc'];
                $tip         = '';

            } else {
                $description = $tip = '';
            }

            // Description handling
            $field_description = WC_Admin_Settings::get_field_description($value);

            $val = get_option('wwof_permissions_noaccess_message');
            if (!$val) {
                $val = '';
            }

            ob_start(); ?>

            <tr valign="top">
                <th scope="row" class="titledesc">
                    <label for="<?php echo esc_attr($value['id']); ?>">
                        <?php echo esc_html($value['title']); ?>
                        <?php echo $field_description['tooltip_html']; ?>
                    </label>
                </th>
                <td class="forminp forminp-<?php echo sanitize_title($value['type']); ?>">
            <?php wp_editor(html_entity_decode($val), 'wwof_permissions_noaccess_message', array('wpautop' => true, 'textarea_name' => "noaccess_message[" . $value['id'] . "]"));
            echo $description;
            ?>
                </td>
            </tr>
            <?php echo ob_get_clean();

        }

        /**
         * Render help content page.
         *
         * @param $value
         * @since 1.6.0
         */
        public function render_wwof_help_resources($value)
        {

            echo '<tr valign="top">';
            echo '<th scope="row" class="titledesc">';
            echo '<label for="">' . __('Knowledge Base', 'woocommerce-wholesale-order-form') . '</label>';
            echo '</th>';
            echo '<td class="forminp forminp-' . sanitize_title($value['type']) . '">';
            echo sprintf(__('Looking for documentation? Please see our growing <a href="%1$s" target="_blank">Knowledge Base</a>', 'woocommerce-wholesale-order-form'), "https://wholesalesuiteplugin.com/knowledge-base/?utm_source=Order%20Form%20Plugin&utm_medium=Settings&utm_campaign=Knowledge%20Base%20");
            echo '</td>';
            echo '</tr>';

        }

        /**
         * Render custom image dimension setting
         *
         * @param $value
         * @since 1.6.0
         */
        public function render_wwof_image_dimension($value)
        {

            $field_description = WC_Admin_Settings::get_field_description($value);
            $imageSize         = get_option('wwof_general_product_thumbnail_image_size');

            extract($field_description);

            $width  = isset($imageSize) && !empty($imageSize['width']) ? $imageSize['width'] : $value['default']['width'];
            $height = isset($imageSize) && !empty($imageSize['height']) ? $imageSize['height'] : $value['default']['height']; ?>

            <tr valign="top" class="<?php echo $value['class']; ?>">
                <th scope="row" class="titledesc">
                    <label for="<?php echo esc_attr($value['id']); ?>">
                        <?php echo esc_html($value['title']); ?>
                        <?php echo $tooltip_html; ?>
                    </label>
                </th>
                <td class="forminp image_width_settings">
                    <input name="<?php echo esc_attr($value['id']); ?>[width]" id="<?php echo esc_attr($value['id']); ?>-width" type="text" size="3" value="<?php echo $width; ?>" /> &times; <input name="<?php echo esc_attr($value['id']); ?>[height]" id="<?php echo esc_attr($value['id']); ?>-height" type="text" size="3" value="<?php echo $height; ?>" />px
                </td>
            </tr><?php

        }

        /**
         * Render clear product cache option
         *
         * @param $value
         * @since 1.14.1
         */
        public function render_wwof_clear_product_caching($value)
        {
            ?>

            <tr valign="top">
                <th scope="row" class="titledesc">
                    <label for=""><?php _e('Clear product cache', 'woocommerce-wholesale-order-form');?></label>
                </th>
                <td class="forminp">
                    <input type="button" name="wwof_clear_product_caching" id="wwof_clear_product_caching" class="button button-secondary" value="<?php _e('Clear Cache', 'woocommerce-wholesale-order-form');?>">
                    <span class="spinner" style="float: none; display: inline-block; visibility: hidden;"></span>
                    <p class="desc"><?php _e('Clear both the product ID and variation ID caches. Caches are automatically rebuilt and maintained by the system.', 'woocommerce-wholesale-order-form');?></p>
                </td>
            </tr><?php

        }

        /**
         * Show or hide options
         *
         * @param $value
         * @since 1.15.4
         */
        public function show_hide_setting($settings)
        {

            foreach ($settings as $key => $setting) {

                if (!is_plugin_active('woocommerce-wholesale-prices-premium/woocommerce-wholesale-prices-premium.bootstrap.php') &&
                    ($setting['id'] === 'wwof_display_wholesale_price_requirement' || $setting['id'] === 'wwof_general_hide_quantity_discounts')) {
                    unset($settings[$key]);
                }

                if (get_option('wwof_general_show_product_thumbnail') !== 'yes' &&
                    $setting['id'] === 'wwof_general_product_thumbnail_image_size') {
                    $settings[$key]['class'] = 'hide-thumbnail';
                }

            }

            return $settings;

        }

        /**
         * Render API Status
         *
         * @param array $value
         * @since 1.20
         */
        public function render_wwof_api_status($value)
        {

            $field = WC_Admin_Settings::get_field_description($value);

            $row = '<tr valign="top">';
            $row .= '<th scope="row" class="titledesc">';
            $row .= '<label for="' . esc_attr($value['id']) . '">';
            $row .= esc_html($value['title']);
            $row .= $field['tooltip_html'];
            $row .= '</label>';
            $row .= '</th>';
            $row .= '<td class="forminp">';
            $is_valid     = Order_Form_API_KEYS::is_api_key_valid();
            $show_valid   = $is_valid ? "display:block;" : "display:none;";
            $show_invalid = !$is_valid ? "display:block;" : "display:none;";
            $row .= '<div class="status valid" style="' . $show_valid . '">' . __('API keys are valid.', 'woocommerce-wholesale-order-form') . ' <span class="dashicons dashicons-yes-alt" style="color:green;"></span></div>';
            $row .= '<div class="status invalid" style="' . $show_invalid . '">' . __('Please enter a valid API keys.', 'woocommerce-wholesale-order-form') . ' <span class="dashicons dashicons-dismiss" style="color:red;"></span></div>';
            $row .= '</td>';
            $row .= '</tr>';

            echo $row;

        }

        /**
         * Refetch Update Data.
         * WooCommerce > Settings > Wholesale Ordering  > Help > Refetch Update Data
         *
         * @since 1.19.4
         * @access public
         */
        public function render_plugin_settings_custom_field_wwlc_force_fetch_update_data_button()
        {

            require_once WWOF_VIEWS_ROOT_DIR . 'custom-fields/view-wwof-force-fetch-update-data-button-custom-field.php';

        }

    }

}

return new WWOF_Settings();
