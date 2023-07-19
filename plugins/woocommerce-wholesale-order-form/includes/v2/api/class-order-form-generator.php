<?php if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly

if (!class_exists('WWOF_Form_Generator')) {

    class WWOF_Form_Generator
    {

        // Addition Styles and Options per component/element
        private $styles = array();

        // The settings tab option per order form
        // Note this also includes option in per compomenet/element
        private $settings = array(
            'selected_category'            => '',
            'filtered_categories'          => array(),
            'tax_display'                  => '',
            'excluded_categories'          => '',
            'subtotal_pretext'             => '',
            'subtotal_suffix'              => '',
            'quantity_restriction'         => '',
            'products_per_page'            => '',
            'show_zero_inventory_products' => '',
            'show_variations_individually' => '',
            'lazy_loading'                 => '',
            'sort_by'                      => '',
            'sort_order'                   => '',
        );

        // The draggable elements in the sidebar
        private $form_elements = array(
            'headerElements' => array(
                'id'      => 'headerElements',
                'title'   => 'HEADER/FOOTER ELEMENTS',
                'desc'    => '',
                'itemIds' => array(
                    'add-selected-to-cart-button',
                    'cart-subtotal',
                ),

            ),
            'tableElements'  => array(
                'id'      => 'tableElements',
                'title'   => 'TABLE COLUMNS',
                'desc'    => '',
                'itemIds' => array(
                    'product-image',
                    'sku',
                    'in-stock-amount',
                    'variation-dropdown',
                    'short-description',
                    'add-to-cart-checkbox',
                    'product-meta',
                ),

            ),
        );

        // The search input element id
        private $search_input_id = '';

        // The cart subtotal element id
        private $cart_subtotal_id = '';

        // The category filter element id
        private $category_filter_id = '';

        // Elements added in the editor area
        private $editor_area = array(
            'formHeader' => array(
                'title' => 'ORDER FORM HEADER',
                'rows'  => array(),

            ),

            'formTable'  => array(
                'title'   => 'ORDER FORM TABLE',
                'itemIds' => array(
                    'product-name',
                    'price',
                    'quantity-input',
                    'add-to-cart-button',
                ),

            ),

            'formFooter' => array(
                'title' => 'ORDER FORM FOOTER',
                'rows'  => array(

                ),

            ),

        );

        private $old_data = array(
            'key'       => '',
            'post_id'   => '',
            'shortcode' => '',
            'atts'      => array(
                'show_search' => true,
                'categories'  => '',
                'products'    => '',
            ),

        );

        /**
         * Class constructor.
         *
         * @since 2.0
         */
        public function __construct($post_id, $data = array())
        {

            // Old shortcode data
            $this->old_data = array_merge($this->old_data, $data);

            // Products Per Page
            $this->settings['products_per_page'] = get_option('wwof_general_products_per_page') > 0 ? get_option('wwof_general_products_per_page') : 10;

            // Allow Zero Inventory Products
            $this->settings['show_zero_inventory_products'] = get_option('wwof_general_display_zero_products') === 'yes' ? true : false;

            // Show Variations Individually
            $this->settings['show_variations_individually'] = get_option('wwof_general_list_product_variation_individually') === 'yes' ? true : false;

            // Order Form Paging Style
            $this->settings['lazy_loading'] = get_option('wwof_general_disable_pagination') === 'yes' ? true : false;

            // Product Sorting
            $this->settings['sort_by'] = $this->sort_by();

            // Product Sort Order
            $this->settings['sort_order'] = get_option('wwof_general_sort_order') ? get_option('wwof_general_sort_order') : '';

            // Selected Category
            $this->settings['selected_category'] = get_option('wwof_general_default_product_category_search_filter') ? get_option('wwof_general_default_product_category_search_filter') : '';

            // Record the ID of the element so we can enable the options for v2
            $this->search_input_id    = $this->generate_unique_id();
            $this->cart_subtotal_id   = $this->generate_unique_id();
            $this->category_filter_id = $this->generate_unique_id();

            // Set default items in the header and footer.
            // Added Standard Elements by default.
            $this->editor_area['formHeader']['rows'][] = array(
                'rowId'   => $this->generate_unique_id(),
                'columns' => array(
                    array(
                        'colId'   => $this->search_input_id,
                        'itemIds' => array(
                            'search-input',
                        ),
                    ),

                    array(
                        'colId'   => $this->category_filter_id,
                        'itemIds' => array(
                            'category-filter',
                        ),

                    ),

                    array(
                        'colId'   => $this->generate_unique_id(),
                        'itemIds' => array(
                            'search-button',
                        ),

                    ),

                ),
            );

            $this->editor_area['formFooter']['rows'][] = array(
                'rowId'   => $this->generate_unique_id(),
                'columns' => array(
                    array(
                        'colId'   => $this->generate_unique_id(),
                        'itemIds' => array(
                            'product-count',
                        ),

                    ),

                    array(
                        'colId'   => $this->generate_unique_id(),
                        'itemIds' => array(
                            'pagination',
                        ),

                    ),

                ),

            );

            // Fires the auto generation of order form based on the old settings.
            $this->generate_form($post_id);

        }

        /**
         * Form Generator.
         *
         * @since 2.0
         */
        public function generate_form($post_id)
        {

            /*** Order Form Style ***/
            $this->order_form_style();

            /*** Display Extra Columns ***/
            $this->display_extra_columns();

            /*** Product Click Action ***/
            $this->product_click_action();

            /*** Product Thumbnail Size ***/
            $this->product_thumbnail_size();

            /***  Show Cart Subtotal ***/
            $this->show_cart_subtotal();

            /*** Hide Wholesale Quantity Based Pricing Tables ***/
            $this->hide_wholesale_quantity_based_pricing_tables();

            /***  Show Wholesale Order Requirements ***/
            $this->show_wholesale_order_requirements();

            /*** Allow Search By SKU ***/
            $this->allow_search_by_sku();

            /*** Cart Subtotal Tax ***/
            $this->cart_subtotal_tax();

            /*** Filter Product Categories ***/
            $this->filter_product_categories();

            /*** Default Category Filter ***/
            $this->default_category_filter();

            /*** Include Products ***/
            $this->include_products();

            /*** Exclude Products ***/
            $this->exclude_products();

            /*** Update Order Form Postmeta ***/
            update_post_meta($post_id, 'styles', $this->styles);
            update_post_meta($post_id, 'settings', $this->settings);
            update_post_meta($post_id, 'form_elements', $this->form_elements);
            update_post_meta($post_id, 'editor_area', $this->editor_area);

        }

        /**
         * Generate a unique 5 character string to be used as ID of elements.
         *
         * @since 2.0
         * @return string
         */
        public static function generate_unique_id()
        {
            return substr(md5(microtime()), rand(0, 26), 5);
        }

        /**
         * Get the "Product Sorting" value in the settings.
         *
         * @param string $headers WC API response header
         * @since 2.0
         * @return array
         */
        public static function sort_by()
        {
            $wwof_sort_by = get_option('wwof_general_sort_by');

            if ($wwof_sort_by == 'default' || $wwof_sort_by == "") {
                return "";
            }

            if ($wwof_sort_by == 'name') {
                return 'title';
            }

            return $wwof_sort_by;

        }

        /*
        |--------------------------------------------------------------------------------------------------------------
        | Migrate Old Settings to v2 Form
        | General Settings
        |--------------------------------------------------------------------------------------------------------------
         */

        /**
         * Migrate "Order Form Style" option
         *
         * @since 2.0
         * @return string
         */
        public function order_form_style()
        {

            $alternate_form = get_option('wwof_general_use_alternate_view_of_wholesale_page');

            if ($alternate_form == 'yes') {

                // Remove 'add-selected-to-cart-button' in header footer elements
                $this->form_elements['headerElements']['itemIds'] = array(
                    'cart-subtotal',
                );

                $sidebar_elements = array(
                    'product-image',
                    'sku',
                    'in-stock-amount',
                    'variation-dropdown',
                    'short-description',
                    'add-to-cart-button',
                    'product-meta',
                );

                // Swap 'add-to-cart-button' and 'add-to-cart-checkbox'
                $this->form_elements['tableElements']['itemIds'] = $sidebar_elements;

                $editor_table_elements = array(
                    'product-name',
                    'price',
                    'quantity-input',
                    'add-to-cart-checkbox',
                );

                $this->editor_area['formTable']['itemIds'] = $editor_table_elements;

                // Insert 'add-selected-to-cart-button' in footer
                $this->editor_area['formFooter']['rows'] = array(
                    array(
                        'rowId'   => $this->generate_unique_id(),
                        'columns' => array(
                            array(
                                'colId'   => $this->generate_unique_id(),
                                'itemIds' => array(
                                    'add-selected-to-cart-button',
                                ),
                            ),
                        ),
                    ),
                    $this->editor_area['formFooter']['rows'][0],
                );
            }

        }

        /**
         * Migrate "Display Extra Columns" option
         *
         * @since 2.0
         * @return string
         */
        public function display_extra_columns()
        {
            $sidebar_table_elements = $this->form_elements['tableElements']['itemIds'];

            // Stock Quantity
            if (get_option('wwof_general_show_product_stock_quantity') == 'yes') {

                // Remove from sidebar
                $position = array_search('in-stock-amount', $sidebar_table_elements);
                if ($position !== false) {
                    unset($this->form_elements['tableElements']['itemIds'][$position]);
                }

                $price_position = array_search('price', $this->editor_area['formTable']['itemIds']);

                // Insert in stock amount after price
                if ($price_position !== false) {
                    array_splice($this->editor_area['formTable']['itemIds'], $price_position + 1, 0, 'in-stock-amount');
                }

            }

            // Product SKU
            if (get_option('wwof_general_show_product_sku') == 'yes') {

                // Remove from sidebar
                $position = array_search('sku', $sidebar_table_elements);
                if ($position !== false) {
                    unset($this->form_elements['tableElements']['itemIds'][$position]);
                }

                $name_position = array_search('product-name', $this->editor_area['formTable']['itemIds']);

                // Insert sku after name
                if ($name_position !== false) {
                    array_splice($this->editor_area['formTable']['itemIds'], $name_position + 1, 0, 'sku');
                }

            }

            // Product Thumbnail
            if (get_option('wwof_general_show_product_thumbnail') == 'yes') {

                // Remove from sidebar
                $position = array_search('product-image', $sidebar_table_elements);
                if ($position !== false) {
                    unset($this->form_elements['tableElements']['itemIds'][$position]);
                }

                // Insert first column
                array_splice($this->editor_area['formTable']['itemIds'], 0, 0, 'product-image');

            }

            $this->form_elements['tableElements']['itemIds'] = array_values($this->form_elements['tableElements']['itemIds']);

        }

        /**
         * Migrate "Product Click Action" option
         *
         * @since 2.0
         * @return string
         */
        public function product_click_action()
        {

            $display_modal = get_option('wwof_general_display_product_details_on_popup');

            $this->styles['product-image'] = array(
                'props' => array(
                    'onClick' => $display_modal == 'yes' ? 'show-product-details' : 'navigate-to-product-page',
                ),
            );

            $this->styles['product-name'] = array(
                'props' => array(
                    'onClick'                  => $display_modal == 'yes' ? 'show-product-details' : 'navigate-to-product-page',
                    'displayVariationDropdown' => true,
                ),
            );

        }

        /**
         * Migrate "Product Thumbnail Size" option
         *
         * @since 2.0
         * @return string
         */
        public function product_thumbnail_size()
        {

            // Image Size
            $thumbnail_size = get_option('wwof_general_product_thumbnail_image_size');

            $this->styles['product-image'] = array(
                'props' => array(
                    'imageSize' => array(
                        'width'  => floatval(isset($thumbnail_size['width']) && $thumbnail_size['width'] > 0 ? $thumbnail_size['width'] : '48'),
                        'height' => floatval(isset($thumbnail_size['height']) && $thumbnail_size['height'] > 0 ? $thumbnail_size['height'] : '48'),
                    ),
                ),
            );

        }

        /**
         * Migrate "Show Cart Subtotal" option
         *
         * @since 2.0
         * @return string
         */
        public function show_cart_subtotal()
        {

            $alternate_form     = get_option('wwof_general_use_alternate_view_of_wholesale_page');
            $show_cart_subtotal = get_option('wwof_general_display_cart_subtotal');

            if ($show_cart_subtotal == 'yes') {
                $cart_subtotal_position = array_search('cart-subtotal', $this->form_elements['headerElements']['itemIds']);

                // Remove cart subtotal in sidebar
                if ($cart_subtotal_position !== false) {
                    unset($this->form_elements['headerElements']['itemIds'][$cart_subtotal_position]);
                }

                // Insert cart subtotal in the footer
                if ($alternate_form == 'yes') {
                    // If alternate view then insert in the 2nd row
                    array_splice($this->editor_area['formFooter']['rows'], 1, 0, array(
                        array(
                            'rowId'   => $this->generate_unique_id(),
                            'columns' => array(
                                array(
                                    'colId'   => $this->cart_subtotal_id,
                                    'itemIds' => array(
                                        'cart-subtotal',
                                    ),
                                ),
                            ),
                        )));
                } else {
                    // If standard then insert in the 1st row\
                    array_unshift($this->editor_area['formFooter']['rows'], array(
                        'rowId'   => $this->generate_unique_id(),
                        'columns' => array(
                            array(
                                'colId'   => $this->generate_unique_id(),
                                'itemIds' => array(
                                    'cart-subtotal',
                                ),
                            ),
                        ),
                    ));
                }

            }

        }

        /**
         * Migrate "Hide Wholesale Quantity Based Pricing Tables" option
         *
         * @since 2.0
         * @return string
         */
        public function hide_wholesale_quantity_based_pricing_tables()
        {

            $this->styles['price'] = array(
                'props' => array(
                    'showQuantityBasedPricing' => get_option('wwof_general_hide_quantity_discounts') == 'yes' ? 1 : '',
                ),
            );

        }

        /**
         * Migrate "Show Wholesale Order Requirements" option
         *
         * @since 2.0
         * @return string
         */
        public function show_wholesale_order_requirements()
        {
            $show_search = isset($this->old_data['atts']['show_search']) ? $this->old_data['atts']['show_search'] : true;

            $this->styles['formHeader'] = array(
                'props' => array(
                    'showFormHeader'   => boolval($show_search),
                    'showMinReqNotice' => get_option('wwof_display_wholesale_price_requirement') == 'yes' ? 1 : '',
                ),
            );
        }

        /**
         * Migrate "Allow Search By SKU" option
         *
         * @since 2.0
         * @return string
         */
        public function allow_search_by_sku()
        {
            $this->styles[$this->search_input_id] = array(
                'props' => array(
                    'skuSearch' => get_option('wwof_general_allow_product_sku_search') == 'yes' ? 1 : '',
                ),
            );
        }

        /**
         * Migrate "Cart Subtotal Tax" option
         *
         * @since 2.0
         * @return string
         */
        public function cart_subtotal_tax()
        {
            $this->styles[$this->cart_subtotal_id] = array(
                'props' => array(
                    'taxDisplay' => get_option('wwof_general_cart_subtotal_prices_display') != '' ? get_option('wwof_general_cart_subtotal_prices_display') : '',
                ),
            );
        }

        /*
        |--------------------------------------------------------------------------------------------------------------
        | Filters Settings
        |--------------------------------------------------------------------------------------------------------------
         */

        /**
         * Migrate "Filter Product Categories" option
         *
         * @since 2.0
         * @return string
         */
        public function filter_product_categories()
        {

            $category_shortcode_attr = isset($this->old_data['atts']['categories']) ? $this->old_data['atts']['categories'] : '';
            $category_shortcode_attr = !empty($category_shortcode_attr) ? explode(",", $category_shortcode_attr) : array();

            // Categories from shortcode attribute. Ex: [wwof_product_listing categories="15,154,75"]
            if (!empty($category_shortcode_attr)) {

                $category_slug = array();

                foreach ($category_shortcode_attr as $cat_id) {
                    $term = get_term($cat_id, 'product_cat');

                    if ($term) {
                        $category_slug[] = $term->slug;
                    }

                }
                $this->styles[$this->category_filter_id] = array(
                    'props' => array(
                        'includedCategories' => $category_slug,
                    ),
                );

                // Include Categories
                $this->settings['filtered_categories'] = $category_slug;

            } else {

                // Categories from old the settings
                $product_categories = get_option('wwof_filters_product_category_filter');

                if (!empty($product_categories)) {

                    $this->styles[$this->category_filter_id] = array(
                        'props' => array(
                            'includedCategories' => $product_categories,
                        ),
                    );

                }

                // Include Categories
                $this->settings['filtered_categories'] = $product_categories;

            }

        }

        /**
         * Migrate "Default Category Filter" option
         *
         * @since 2.0
         * @return string
         */
        public function default_category_filter()
        {

            $default_category = get_option('wwof_general_default_product_category_search_filter');

            if (!empty($default_category) && $default_category != 'none') {

                $this->styles[$this->category_filter_id] = array(
                    'props' => array(
                        'defaultCategory' => $default_category,
                    ),
                );

            }

        }

        /**
         * Migrate shortcode attribute "products" into the v2 setting.
         * Ex: [wwof_product_listing products="6542,6514,6372"]
         *
         * @since 2.0
         * @return string
         */
        public function include_products()
        {

            $include_products = isset($this->old_data['atts']['products']) ? $this->old_data['atts']['products'] : '';
            $include_products = !empty($include_products) ? explode(",", $include_products) : array();

            // Perform filtering to only save those valid product ids
            if (!empty($include_products) && is_array($include_products)) {

                $filtered = array();

                foreach ($include_products as $product_id) {

                    // Only save if id is product type and is publish
                    if (get_post_type($product_id) == 'product' && get_post_status($product_id) == 'publish') {
                        $filtered[] = $product_id;
                    }
                }

                $this->settings['include_products'] = $filtered;

            }

        }

        /**
         * Migrate "Exclude Products" option
         *
         * @since 2.0
         * @return string
         */
        public function exclude_products()
        {

            $exclude_products = get_option('wwof_filters_exclude_product_filter');

            if (!empty($exclude_products)) {

                $this->settings['exclude_products'] = $exclude_products;
            }

        }

    }

}
