<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'WWP_Order' ) ) {

    /**
     * Model that houses the logic of integrating with WooCommerce orders.
     * Be it be adding additional data/meta to orders or order items, etc..
     *
     * @since 1.3.0
     */
    class WWP_Order {

        /**
         * Class Properties
         */

        /**
         * Property that holds the single main instance of WWP_Order.
         *
         * @since 1.3.0
         * @access private
         * @var WWP_Order
         */
        private static $_instance;

        /**
         * Model that houses the logic of retrieving information relating to wholesale role/s of a user.
         *
         * @since 1.3.0
         * @access private
         * @var WWP_Wholesale_Roles
         */
        private $_wwp_wholesale_roles;

        /**
         *  Class Methods
         */

        /**
         * WWP_Order constructor.
         *
         * @since 1.3.0
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWP_Order model.
         */
        public function __construct( $dependencies ) {
            $this->_wwp_wholesale_roles = $dependencies['WWP_Wholesale_Roles'];
        }

        /**
         * Ensure that only one instance of WWP_Order is loaded or can be loaded (Singleton Pattern).
         *
         * @since 1.3.0
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWP_Order model.
         * @return WWP_Order
         */
        public static function instance( $dependencies ) {
            if ( ! self::$_instance instanceof self ) {
                self::$_instance = new self( $dependencies );
            }

            return self::$_instance;
        }

        /**
         * Add order meta.
         *
         * @since 1.3.0
         * @access public
         *
         * @param int   $order_id    Order id.
         * @param array $posted_data Posted data.
         */
        public function wwp_add_order_meta( $order_id, $posted_data ) {
            $user_wholesale_role = $this->_wwp_wholesale_roles->getUserWholesaleRole();

            if ( ! empty( $user_wholesale_role ) ) {

                update_post_meta( $order_id, 'wwp_wholesale_role', $user_wholesale_role[0] );

                do_action( '_wwp_add_order_meta', $order_id, $posted_data, $user_wholesale_role );

            }
        }

        /**
         * Attach order item meta for new orders since WWP 1.3.0 for more accurate reporting in the future versions of WWPP.
         * Replaces the wwp_add_order_item_meta function of WWP 1.3.0.
         *
         * @since 1.3.1
         * @since 2.0.2 Add support to add order item meta using item object when the item ID is not available.
         * @since 2.1.5 Remove legacy code related to pre-WC 3.0.
         * @access public
         *
         * @param Object $item          Order item object.
         * @param string $cart_item_key Cart item unique hash key.
         * @param array  $values        Cart item data.
         * @param Object $order         Order object.
         */
        public function add_order_item_meta( $item, $cart_item_key, $values, $order ) {
            $user_wholesale_role = $this->_wwp_wholesale_roles->getUserWholesaleRole();

            if ( isset( $values['data']->wwp_data ) && isset( $values['data']->wwp_data['wholesale_role'] ) &&
                ! empty( $user_wholesale_role ) && $user_wholesale_role[0] === $values['data']->wwp_data['wholesale_role'] ) {

                if ( isset( $values['data']->wwp_data['wholesale_priced'] ) ) {
                    $item->update_meta_data( '_wwp_wholesale_priced', $values['data']->wwp_data['wholesale_priced'] );
                }

                if ( isset( $values['data']->wwp_data['wholesale_role'] ) ) {
                    $item->update_meta_data( '_wwp_wholesale_role', $values['data']->wwp_data['wholesale_role'] );
                }
            }

            do_action( 'wwp_add_order_item_meta', $item, $cart_item_key, $values, $order );
        }

        /**
         * ############################################################################################################
         * Move the Order type filtering feature from WWPP to WWP
         *
         * @since 1.15.0
         * ##########################################################################################################*/

        /**
         * Add custom column to order listing page.
         *
         * @since 1.0.0
         * @since 1.14.0 Refactor codebase and move to its own model.
         * @access public
         *
         * @param array $columns Orders cpt listing columns.
         * @return array Filtered orders cpt listing columns.
         */
        public function add_orders_listing_custom_column( $columns ) {
            $arrayKeys = array_keys( $columns );
            $lastIndex = $arrayKeys[ count( $arrayKeys ) - 1 ];
            $lastValue = $columns[ $lastIndex ];
            array_pop( $columns );

            $columns['wwpp_order_type'] = __( 'Order Type', 'woocommerce-wholesale-prices' );

            $columns[ $lastIndex ] = $lastValue;

            return $columns;
        }

        /**
         * Add content to the custom column on order listing page.
         *
         * @since 1.0.0
         * @since 1.14.0 Refactor codebase and move to its own model.
         * @access public
         *
         * @param string $column  Current column key.
         * @param int    $post_id Current post id.
         */
        public function add_orders_listing_custom_column_content( $column, $post_id ) {
            if ( 'wwpp_order_type' === $column ) {

                $order_type = get_post_meta( $post_id, '_wwpp_order_type', true );

                if ( '' === $order_type || null === $order_type || false === $order_type || 'retail' === $order_type ) {

                    esc_html_e( 'Retail', 'woocommerce-wholesale-prices' );

                } elseif ( 'wholesale' === $order_type ) {

                    $all_registered_wholesale_roles = maybe_unserialize( get_option( WWP_OPTIONS_REGISTERED_CUSTOM_ROLES ) );
                    if ( ! is_array( $all_registered_wholesale_roles ) ) {
                        $all_registered_wholesale_roles = array();
                    }

                    $wholesale_order_type = get_post_meta( $post_id, '_wwpp_wholesale_order_type', true );

                    if ( isset( $all_registered_wholesale_roles[ $wholesale_order_type ] ) && ! empty( $all_registered_wholesale_roles[ $wholesale_order_type ]['roleName'] ) ) {
                        // translators: %1$s: wholesale role name.
                        echo esc_html( sprintf( __( 'Wholesale (%1$s)', 'woocommerce-wholesale-prices' ), $all_registered_wholesale_roles[ $wholesale_order_type ]['roleName'] ) );

                        if ( ! WWP_Helper_Functions::is_wwpp_active() && 'wholesale_customer' !== $wholesale_order_type ) {
                            echo wp_kses_post( wc_help_tip( __( 'This wholesale role is exclusive only for Wholesale Prices Premium. Make sure the plugin is active.', 'woocommerce-wholesale-prices' ) ) );
                        }
                    }
                }
            }
        }

        /**
         * Attach custom meta to orders ( the order type metadata ) to be used later for filtering orders by order type
         * on the order listing page.
         *
         * @since 1.0.0
         * @since 1.14.0 Refactor codebase and move to its own model.
         * @access public
         *
         * @param int $order_id Order id.
         */
        public function add_order_type_meta_to_wc_orders( $order_id ) {
            $all_registered_wholesale_roles = $this->_wwp_wholesale_roles->getAllRegisteredWholesaleRoles();
            $current_order                  = new WC_Order( $order_id );
            $current_order_wp_user          = get_userdata( $current_order->get_user_id() );
            $current_order_user_roles       = array();

            if ( $current_order_wp_user ) {
                $current_order_user_roles = $current_order_wp_user->roles;
            }

            if ( ! is_array( $current_order_user_roles ) ) {
                $current_order_user_roles = array();
            }

            $all_registered_wholesale_roles_keys = array();
            foreach ( $all_registered_wholesale_roles as $roleKey => $role ) {
                $all_registered_wholesale_roles_keys[] = $roleKey;
            }

            $orderUserWholesaleRole = array_values( array_intersect( $current_order_user_roles, $all_registered_wholesale_roles_keys ) );

            if ( isset( $orderUserWholesaleRole[0] ) ) {

                update_post_meta( $order_id, '_wwpp_order_type', 'wholesale' );
                update_post_meta( $order_id, '_wwpp_wholesale_order_type', $orderUserWholesaleRole[0] );

            } else {

                update_post_meta( $order_id, '_wwpp_order_type', 'retail' );
                update_post_meta( $order_id, '_wwpp_wholesale_order_type', '' );

            }
        }

        /**
         * Add custom filter on order listing page ( order type filter ).
         *
         * @since 1.0.0
         * @since 1.14.0 Refactor codebase and move to its own model.
         * @access public
         */
        public function add_wholesale_role_order_listing_filter() {
            global $typenow;

            $all_registered_wholesale_roles = $this->_wwp_wholesale_roles->getAllRegisteredWholesaleRoles();

            if ( 'shop_order' === $typenow ) {

                ob_start();

                // Filter By Wholesale Role.
                $wwpp_fbwr = null;

                if ( isset( $_GET['wwpp_fbwr'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                    $wwpp_fbwr = $_GET['wwpp_fbwr']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                }

                $all_registered_wholesale_roles = array( 'all_wholesale_orders' => array( 'roleName' => __( 'All Wholesale Orders', 'woocommerce-wholesale-prices' ) ) ) + $all_registered_wholesale_roles;
                $all_registered_wholesale_roles = array( 'all_retail_orders' => array( 'roleName' => __( 'All Retail Orders', 'woocommerce-wholesale-prices' ) ) ) + $all_registered_wholesale_roles;
                $all_registered_wholesale_roles = array( 'all_order_types' => array( 'roleName' => __( 'Show all order types', 'woocommerce-wholesale-prices' ) ) ) + $all_registered_wholesale_roles;
                ?>

                <select name="wwpp_fbwr" id="filter-by-wholesale-role" class="chosen_select">
                    <?php foreach ( $all_registered_wholesale_roles as $roleKey => $role ) : ?>
                        <option value="<?php echo esc_attr( $roleKey ); ?>" <?php echo ( $roleKey === $wwpp_fbwr ) ? 'selected' : ''; ?>>
                            <?php echo esc_html( $role['roleName'] ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <?php
                echo ob_get_clean(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            }
        }

        /**
         * Add functionality to the custom filter added on order listing page ( order type filter ).
         *
         * @since 1.0.0
         * @since 1.14.0 Refactor codebase and move to its own model.
         * @access public
         *
         * @param WP_Query $query WP_Query object.
         */
        public function wholesale_role_order_listing_filter( $query ) {
            global $pagenow;
            $wholesale_filter = null;

            if ( isset( $_GET['wwpp_fbwr'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                $wholesale_filter = trim( $_GET['wwpp_fbwr'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            }

            if ( 'edit.php' === $pagenow && isset( $query->query_vars['post_type'] ) && 'shop_order' === $query->query_vars['post_type'] && ! is_null( $wholesale_filter ) ) {

                switch ( $wholesale_filter ) {

                    case 'all_order_types':
                        // Do nothing.
                        break;

                    case 'all_retail_orders':
                        $query->set(
                            'meta_query',
                            array(
                                'relation' => 'OR',
                                array(
                                    'key'     => '_wwpp_order_type',
                                    'value'   => array( 'retail' ),
                                    'compare' => 'IN',
                                ),
                                array(
                                    'key'     => '_wwpp_order_type',
                                    'value'   => 'gebbirish', // Pre WP 3.9 bug, must set string for NOT EXISTS to work.
                                    'compare' => 'NOT EXISTS',
                                ),
                            )
                        );

                        break;

                    case 'all_wholesale_orders':
                        $query->query_vars['meta_key']   = '_wwpp_order_type';
                        $query->query_vars['meta_value'] = 'wholesale';

                        break;

                    default:
                        $query->query_vars['meta_key']   = '_wwpp_wholesale_order_type';
                        $query->query_vars['meta_value'] = $wholesale_filter;

                        break;

                }
            }

            return $query;
        }

        /**
         * Execute model.
         *
         * @since 1.3.0
         * @access public
         */
        public function run() {
            add_action( 'woocommerce_checkout_order_processed', array( $this, 'wwp_add_order_meta' ), 10, 2 );

            // Attach order item meta for new orders.
            add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'add_order_item_meta' ), 10, 4 );

            // Execute add_filter for Order Filtering if WWPP is not active and WWPP Class does not exists, else execute WWPP's Order Filtering.
            if ( is_plugin_inactive( 'woocommerce-wholesale-prices-premium/woocommerce-wholesale-prices-premium.bootstrap.php' ) && ! class_exists( 'WooCommerceWholeSalePricesPremium' ) ) {

                add_filter( 'manage_edit-shop_order_columns', array( $this, 'add_orders_listing_custom_column' ), 15, 1 );
                add_action( 'manage_shop_order_posts_custom_column', array( $this, 'add_orders_listing_custom_column_content' ), 10, 2 );

                add_action( 'woocommerce_checkout_order_processed', array( $this, 'add_order_type_meta_to_wc_orders' ), 10, 2 );
                add_action( 'restrict_manage_posts', array( $this, 'add_wholesale_role_order_listing_filter' ), 10, 1 );
                add_filter( 'parse_query', array( $this, 'wholesale_role_order_listing_filter' ), 10, 1 );

            }
        }

    }

}
