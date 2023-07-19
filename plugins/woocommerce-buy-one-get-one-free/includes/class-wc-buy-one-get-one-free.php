<?php
/**
 * WooCommerce Buy One Get One Free setup
 *
 * @package  WC_BOGOF
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main WooCommerce Buy One Get One Free Class
 */
class WC_Buy_One_Get_One_Free {

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	public static $version = '2.1.8';

	/**
	 * Min WooCommerce required version.
	 *
	 * @var string
	 */
	private static $min_wc_version = '3.4';

	/**
	 * Admin notices.
	 *
	 * @var array
	 */
	private static $admin_notices = array();

	/**
	 * Init plugin
	 *
	 * @since 1.0
	 */
	public static function init() {
		self::includes();

		register_activation_hook( WC_BOGOF_PLUGIN_FILE, array( __CLASS__, 'plugin_activated' ) );

		add_action( 'init', array( __CLASS__, 'load_plugin_textdomain' ) );
		add_action( 'init', array( __CLASS__, 'register_post_types' ), 20 );
		add_action( 'plugins_loaded', array( __CLASS__, 'init_plugin' ), 10 );
		add_action( 'admin_notices', array( __CLASS__, 'display_notices' ) );
		add_filter( 'post_updated_messages', array( __CLASS__, 'post_updated_messages' ), 20 );
	}

	/**
	 * Add transient to display the welcome notice
	 */
	public static function plugin_activated() {
		set_transient( 'bogof_plugin_activated', '1', DAY_IN_SECONDS );
	}

	/**
	 * Localisation
	 */
	public static function load_plugin_textdomain() {
		load_plugin_textdomain( 'wc-buy-one-get-one-free', false, dirname( plugin_basename( WC_BOGOF_PLUGIN_FILE ) ) . '/languages' );
	}

	/**
	 * Register the BOGO rule post types.
	 */
	public static function register_post_types() {
		if ( ! is_blog_installed() || post_type_exists( 'shop_bogof_rule' ) ) {
			return;
		}

		register_post_type(
			'shop_bogof_rule',
			apply_filters(
				'woocommerce_register_post_type_shop_bogof_rule',
				array(
					'labels'              => array(
						'name'                  => __( 'BOGO rules', 'wc-buy-one-get-one-free' ),
						'singular_name'         => __( 'BOGO rule', 'wc-buy-one-get-one-free' ),
						'menu_name'             => _x( 'Buy One Get One', 'Admin menu name', 'wc-buy-one-get-one-free' ),
						'add_new'               => __( 'Add BOGO rule', 'wc-buy-one-get-one-free' ),
						'add_new_item'          => __( 'Add new BOGO rule', 'wc-buy-one-get-one-free' ),
						'edit'                  => __( 'Edit', 'wc-buy-one-get-one-free' ),
						'edit_item'             => __( 'Edit BOGO rule', 'wc-buy-one-get-one-free' ),
						'new_item'              => __( 'New BOGO rule', 'wc-buy-one-get-one-free' ),
						'view_item'             => __( 'View BOGO rule', 'wc-buy-one-get-one-free' ),
						'search_items'          => __( 'Search rule', 'wc-buy-one-get-one-free' ),
						'not_found'             => __( 'No rules found', 'wc-buy-one-get-one-free' ),
						'not_found_in_trash'    => __( 'No rules found in trash', 'wc-buy-one-get-one-free' ),
						'filter_items_list'     => __( 'Filter rules', 'wc-buy-one-get-one-free' ),
						'items_list_navigation' => __( 'Rules navigation', 'wc-buy-one-get-one-free' ),
						'items_list'            => __( 'BOGO rules list', 'wc-buy-one-get-one-free' ),
					),
					'description'         => __( 'This is where you can add new BOGO rules that customers can use in your store.', 'wc-buy-one-get-one-free' ),
					'public'              => false,
					'show_ui'             => true,
					'capability_type'     => 'shop_coupon',
					'map_meta_cap'        => true,
					'publicly_queryable'  => false,
					'exclude_from_search' => true,
					'show_in_menu'        => current_user_can( 'manage_woocommerce' ) ? 'woocommerce' : true,
					'hierarchical'        => false,
					'rewrite'             => false,
					'query_var'           => false,
					'supports'            => array( 'title' ),
					'show_in_nav_menus'   => false,
					'show_in_admin_bar'   => true,
				)
			)
		);

		do_action( 'wc_bogof_after_register_post_type' );
	}

	/**
	 * Change messages when a post type is updated.
	 *
	 * @param  array $messages Array of messages.
	 * @return array
	 */
	public static function post_updated_messages( $messages ) {
		global $post;

		$messages['shop_bogof_rule'] = array(
			0  => '', // Unused. Messages start at index 1.
			1  => __( 'BOGO rule updated.', 'wc-buy-one-get-one-free' ),
			2  => __( 'Custom field updated.', 'wc-buy-one-get-one-free' ),
			3  => __( 'Custom field deleted.', 'wc-buy-one-get-one-free' ),
			4  => __( 'BOGO rule updated.', 'wc-buy-one-get-one-free' ),
			5  => __( 'Revision restored.', 'wc-buy-one-get-one-free' ),
			6  => __( 'BOGO rule updated.', 'wc-buy-one-get-one-free' ),
			7  => __( 'BOGO rule saved.', 'wc-buy-one-get-one-free' ),
			8  => __( 'BOGO rule submitted.', 'wc-buy-one-get-one-free' ),
			9  => sprintf(
				/* translators: %s: date */
				__( 'BOGO rule scheduled for: %s.', 'wc-buy-one-get-one-free' ),
				'<strong>' . date_i18n( __( 'M j, Y @ G:i', 'wc-buy-one-get-one-free' ), strtotime( $post->post_date ) ) . '</strong>'
			),
			10 => __( 'BOGO rule draft updated.', 'wc-buy-one-get-one-free' ),
			11 => __( 'BOGO rule updated and sent.', 'wc-buy-one-get-one-free' ),
		);

		return $messages;
	}

	/**
	 * Init plugin
	 */
	public static function init_plugin() {
		if ( ! self::check_environment() ) {
			return;
		}

		// WooCommerce CRUD.
		add_action( 'woocommerce_init', array( __CLASS__, 'woocommerce_init' ) );
		add_filter( 'woocommerce_data_stores', array( __CLASS__, 'add_data_store' ) );

		WC_BOGOF_Cart::init();
		WC_BOGOF_Cart_Template::init();
		WC_BOGOF_Choose_Gift::init();
		WC_BOGOF_Integrations::init();

		if ( is_admin() ) {
			WC_BOGOF_Install::init();
			WC_BOGOF_Admin_Ajax::init();
			WC_BOGOF_Admin_Meta_Boxes::init();
			WC_BOGOF_Admin_Settings::init();
			WC_BOGOF_Admin_Navigation::init();

			// Load list table class for BOGOF rule screen.
			add_action( 'current_screen', array( __CLASS__, 'setup_screen' ) );
			add_action( 'check_ajax_referer', array( __CLASS__, 'setup_screen' ) );
		}
	}

	/**
	 * Display admin notices
	 */
	public static function display_notices() {
		foreach ( self::$admin_notices as $notice ) {
			echo '<div class="error notice"><p>' . wp_kses_post( $notice ) . '</p></div>';
		}
	}

	/**
	 * Looks at the current screen and loads the correct list table handler.
	 */
	public static function setup_screen() {
		global $wc_list_table;

		$screen_id = false;

		if ( function_exists( 'get_current_screen' ) ) {
			$screen    = get_current_screen();
			$screen_id = isset( $screen, $screen->id ) ? $screen->id : '';
		}

		if ( ! empty( $_REQUEST['screen'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$screen_id = wc_clean( wp_unslash( $_REQUEST['screen'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
		}

		if ( 'edit-shop_bogof_rule' === $screen_id ) {
			include_once dirname( __FILE__ ) . '/admin/class-wc-bogof-admin-list-table.php';
			$wc_list_table = new WC_BOGOF_Admin_List_Table();
		}

		// Ensure the table handler is only loaded once. Prevents multiple loads if a plugin calls check_ajax_referer many times.
		remove_action( 'current_screen', array( __CLASS__, 'setup_screen' ) );
		remove_action( 'check_ajax_referer', array( __CLASS__, 'setup_screen' ) );
	}

	/**
	 * Include files for the WooCommerce CRUD.
	 */
	public static function woocommerce_init() {
		include_once dirname( __FILE__ ) . '/data-stores/class-wc-bogof-rule-data-store-cpt.php';
		include_once dirname( __FILE__ ) . '/class-wc-bogof-rule.php';
		include_once dirname( __FILE__ ) . '/class-wc-bogof-choose-gift-shortcode.php';
	}

	/**
	 * Add the BOGOF Rule data store.
	 *
	 * @param array $data_stores Data stores.
	 */
	public static function add_data_store( $data_stores ) {
		$data_stores['bogof-rule'] = 'WC_BOGOF_Rule_Data_Store_CPT';
		return $data_stores;
	}

	/**
	 * Include required files
	 */
	private static function includes() {
		include_once dirname( __FILE__ ) . '/wc-bogof-helper-functions.php';
		include_once dirname( __FILE__ ) . '/class-wc-bogof-choose-gift.php';
		include_once dirname( __FILE__ ) . '/class-wc-bogof-cart-rule.php';
		include_once dirname( __FILE__ ) . '/class-wc-bogof-cart-rule-individual.php';
		include_once dirname( __FILE__ ) . '/class-wc-bogof-cart-rule-buy-a-get-a.php';
		include_once dirname( __FILE__ ) . '/class-wc-bogof-cart-rule-cheapest-free.php';
		include_once dirname( __FILE__ ) . '/class-wc-bogof-cart-item-discount.php';
		include_once dirname( __FILE__ ) . '/class-wc-bogof-cart-template.php';
		include_once dirname( __FILE__ ) . '/class-wc-bogof-cart-totals.php';
		include_once dirname( __FILE__ ) . '/class-wc-bogof-cart.php';
		include_once dirname( __FILE__ ) . '/class-wc-bogof-integrations.php';
		if ( is_admin() ) {
			include_once dirname( __FILE__ ) . '/admin/class-wc-bogof-install.php';
			include_once dirname( __FILE__ ) . '/admin/class-wc-bogof-admin-ajax.php';
			include_once dirname( __FILE__ ) . '/admin/class-wc-bogof-admin-meta-boxes.php';
			include_once dirname( __FILE__ ) . '/admin/class-wc-bogof-admin-settings.php';
			include_once dirname( __FILE__ ) . '/admin/class-wc-bogof-admin-navigation.php';
		}
	}

	/**
	 * Checks the environment for compatibility problems.
	 *
	 * @return boolean
	 */
	private static function check_environment() {

		self::$admin_notices = array();

		if ( ! defined( 'WC_VERSION' ) ) {
			// translators: HTML Tags.
			self::$admin_notices[] = sprintf( __( '%1$sWooCommerce Buy One Get One Free%2$s requires WooCommerce to be activated to work.', 'wc-buy-one-get-one-free' ), '<strong>', '</strong>' );
			return false;
		}

		if ( version_compare( WC_VERSION, self::$min_wc_version, '<' ) ) {
			// translators: HTML Tags.
			self::$admin_notices[] = sprintf( __( 'WooCommerce Buy One Get One Free - The minimum WooCommerce version required for this plugin is %1$s. You are running %2$s.', 'wc-buy-one-get-one-free' ), self::$min_wc_version, WC_VERSION );
			return false;
		}

		return true;
	}
}
