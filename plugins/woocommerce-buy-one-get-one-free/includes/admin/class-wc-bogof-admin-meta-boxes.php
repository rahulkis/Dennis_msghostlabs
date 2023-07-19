<?php
/**
 * WooCommerce Buy One Get One Free Meta Boxes
 *
 * @package WC_BOGOF
 */

defined( 'ABSPATH' ) || exit;

/**
 * WC_BOGOF_Admin_Meta_Boxes Class
 */
class WC_BOGOF_Admin_Meta_Boxes {

	/**
	 * Is meta boxes saved once?
	 *
	 * @var boolean
	 */
	private static $saved_meta_boxes = false;

	/**
	 * Init hooks
	 */
	public static function init() {
		add_filter( 'woocommerce_screen_ids', array( __CLASS__, 'screen_ids' ) );
		add_action( 'admin_menu', array( __CLASS__, 'connect_pages' ) );
		add_filter( 'postbox_classes_shop_bogof_rule_woocommerce-bogo-rule-data', array( __CLASS__, 'metabox_classes' ) );
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_boxes' ), 30 );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_scripts' ), 20 );
		add_action( 'save_post_shop_bogof_rule', array( __CLASS__, 'save' ), 10, 2 );
	}

	/**
	 * Add the BOGO rule screens to the WooCommerce screens
	 *
	 * @param array $ids Screen ids.
	 * @return array
	 */
	public static function screen_ids( $ids ) {
		$ids[] = 'shop_bogof_rule';
		$ids[] = 'edit-shop_bogof_rule';
		return $ids;
	}

	/**
	 * Connect BOGO pages for display the WooCommerce admin header.
	 */
	public static function connect_pages() {
		if ( ! function_exists( 'wc_admin_connect_page' ) ) {
			return;
		}
		wc_admin_connect_page(
			array(
				'id'        => 'woocommerce-bogo-rules',
				'screen_id' => 'edit-shop_bogof_rule',
				'title'     => __( 'BOGO rules', 'wc-buy-one-get-one-free' ),
				'path'      => add_query_arg( 'post_type', 'shop_bogof_rule', 'edit.php' ),
			)
		);

		wc_admin_connect_page(
			array(
				'id'        => 'woocommerce-add-bogo-rule',
				'parent'    => 'woocommerce-bogo-rules',
				'screen_id' => 'shop_bogof_rule-add',
				'title'     => __( 'Add new BOGO rule', 'wc-buy-one-get-one-free' ),
			)
		);

		wc_admin_connect_page(
			array(
				'id'        => 'woocommerce-edit-bogo-rule',
				'parent'    => 'woocommerce-bogo-rules',
				'screen_id' => 'shop_bogof_rule',
				'title'     => __( 'Edit BOGO rule', 'wc-buy-one-get-one-free' ),
			)
		);
	}

	/**
	 * Add the woocommerce css class to the metabox wrapper.
	 *
	 * @param array $classes Array of css clasess.
	 * @return array
	 */
	public static function metabox_classes( $classes ) {
		$classes[] = 'woocommerce';
		return $classes;
	}

	/**
	 * Add WC Meta boxes.
	 */
	public static function add_meta_boxes() {
		add_meta_box( 'woocommerce-bogo-rule-data', __( 'Rule data', 'wc-buy-one-get-one-free' ), array( __CLASS__, 'output' ), 'shop_bogof_rule', 'normal', 'high' );
	}

	/**
	 * Enqueue scripts.
	 */
	public static function admin_scripts() {
		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id : '';
		$suffix    = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		if ( in_array( $screen_id, array( 'shop_bogof_rule', 'edit-shop_bogof_rule' ), true ) ) {
			wp_register_script( 'wc-admin-bogof-meta-boxes', plugin_dir_url( WC_BOGOF_PLUGIN_FILE ) . 'assets/js/admin/meta-boxes-bogof-rule' . $suffix . '.js', array( 'wc-admin-meta-boxes' ), WC_Buy_One_Get_One_Free::$version, true );
			wp_localize_script(
				'wc-admin-bogof-meta-boxes',
				'wc_admin_bogof_meta_boxes_params',
				array(
					'i18n_free_less_than_min_error' => __( 'Please enter in a value less than the buy quantity.', 'woocommerce' ),
					'incompatible_types'            => implode( ',', wc_bogof_incompatible_product_types() ),
					'nonces'                        => array(
						'rule_toggle' => wp_create_nonce( 'wc-bogof-toggle-rule-enabled' ),
					),
				)
			);
			wp_enqueue_script( 'wc-admin-bogof-meta-boxes' );
			// Styles.
			wp_enqueue_style( 'wc-admin-bogof', plugin_dir_url( WC_BOGOF_PLUGIN_FILE ) . 'assets/css/admin.css', array(), WC_Buy_One_Get_One_Free::$version );
		}
	}

	/**
	 * Output the metabox.
	 *
	 * @param WP_Post $post WP_Post instance.
	 */
	public static function output( $post ) {
		wp_nonce_field( 'woocommerce_save_data', 'woocommerce_meta_nonce' );

		$tabs = apply_filters(
			'wc_bogof_rule_data_tabs',
			array(
				'general'           => array(
					'label'  => __( 'General', 'wc-buy-one-get-one-free' ),
					'target' => 'general_bogof_rule_data',
					'class'  => '',
				),
				'usage_limit'       => array(
					'label'  => __( 'Limits', 'wc-buy-one-get-one-free' ),
					'target' => 'usage_limit_bogof_rule_data',
					'class'  => '',
				),
				'usage_restriction' => array(
					'label'  => __( 'Usage restriction', 'wc-buy-one-get-one-free' ),
					'target' => 'usage_restriction_bogof_rule_data',
					'class'  => '',
				),
			)
		);

		$rule         = new WC_BOGOF_Rule( $post->ID );
		$product_cats = wp_list_pluck( get_terms( 'product_cat', array( 'hide_empty' => 0 ) ), 'name', 'term_id' );
		?>
		<div id="bogo_rule_options" class="panel-wrap bogo_rule_data">
			<ul class="bogo_rule_data_tabs wc-tabs" style="">
			<?php foreach ( $tabs as $key => $tab ) : ?>
				<li class="<?php echo esc_attr( $key ); ?>_options <?php echo esc_attr( $key ); ?>_tab <?php echo esc_attr( implode( ' ', (array) $tab['class'] ) ); ?>">
					<a href="#<?php echo esc_attr( $tab['target'] ); ?>">
						<span><?php echo esc_html( $tab['label'] ); ?></span>
					</a>
				</li>
			<?php endforeach; ?>
			</ul>
			<?php include dirname( __FILE__ ) . '/views/html-bogof-rule-data-general.php'; ?>
			<?php include dirname( __FILE__ ) . '/views/html-bogof-rule-data-usage-limit.php'; ?>
			<?php include dirname( __FILE__ ) . '/views/html-bogof-rule-data-usage-restriction.php'; ?>
			<?php do_action( 'wc_bogof_rule_data_panels' ); ?>
		</div>
		<style type="text/css">
			#woocommerce-bogo-rule-data .inside { margin:0; padding:0; }
			#woocommerce-bogo-rule-data .woocommerce_options_panel { float: left;width: 80%; }
			#edit-slug-box, #minor-publishing-actions { display:none }
		</style>
		<?php
	}

	/**
	 * Check if we're saving, save the post data.
	 *
	 * @param int    $post_id Post ID.
	 * @param object $post Post object.
	 */
	public static function save( $post_id, $post ) {
		$post_id = absint( $post_id );

		// $post_id and $post are required
		if ( empty( $post_id ) || empty( $post ) || self::$saved_meta_boxes ) {
			return;
		}

		// Dont' save meta boxes for revisions or autosaves.
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || is_int( wp_is_post_revision( $post ) ) || is_int( wp_is_post_autosave( $post ) ) ) {
			return;
		}

		// Check the nonce.
		if ( empty( $_POST['woocommerce_meta_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['woocommerce_meta_nonce'] ), 'woocommerce_save_data' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			return;
		}

		// Check the post being saved == the $post_id to prevent triggering this call for other save_post events.
		if ( empty( $_POST['post_ID'] ) || absint( $_POST['post_ID'] ) !== $post_id ) {
			return;
		}

		// Check user has permission to edit.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// We need this save event to run once to avoid potential endless loops. This would have been perfect:
		// remove_action( current_filter(), __METHOD__ );
		// But cannot be used due to https://github.com/woocommerce/woocommerce/issues/6485
		// When that is patched in core we can use the above.
		self::$saved_meta_boxes = true;

		self::save_bogof_rule( $post_id, wc_clean( wp_unslash( $_POST ) ) );
	}

	/**
	 * Save the BOGOF rule
	 *
	 * @param int   $post_id Post ID.
	 * @param array $postdata Data of the _POST array sanitized.
	 */
	private static function save_bogof_rule( $post_id, $postdata ) {
		// Handle dates.
		$start_date = '';
		$end_date   = '';

		// Force date from to beginning of day.
		if ( isset( $postdata['_start_date'] ) ) {
			$start_date = wc_clean( wp_unslash( $postdata['_start_date'] ) );

			if ( ! empty( $start_date ) ) {
				$start_date = date( 'Y-m-d 00:00:00', strtotime( $start_date ) );
			}
		}

		// Force date to to the end of the day.
		if ( isset( $postdata['_end_date'] ) ) {
			$end_date = wc_clean( wp_unslash( $postdata['_end_date'] ) );

			if ( ! empty( $end_date ) ) {
				$end_date = date( 'Y-m-d 23:59:59', strtotime( $end_date ) );
			}
		}

		$rule   = new WC_BOGOF_Rule( $post_id );
		$errors = $rule->set_props(
			array(
				'enabled'              => isset( $postdata['_enabled'] ),
				'type'                 => $postdata['_type'],
				'applies_to'           => $postdata['_applies_to'],
				'buy_product_ids'      => isset( $postdata['_buy_product_ids'] ) ? $postdata['_buy_product_ids'] : array(),
				'buy_category_ids'     => isset( $postdata['_buy_category_ids'] ) ? $postdata['_buy_category_ids'] : array(),
				'min_quantity'         => $postdata['_min_quantity'],
				'action'               => $postdata['_action'],
				'free_product_id'      => isset( $postdata['_free_product_id'] ) ? $postdata['_free_product_id'] : array(),
				'free_product_ids'     => isset( $postdata['_free_product_ids'] ) ? $postdata['_free_product_ids'] : array(),
				'free_category_ids'    => isset( $postdata['_free_category_ids'] ) ? $postdata['_free_category_ids'] : array(),
				'individual'           => isset( $postdata['_individual'] ),
				'free_quantity'        => $postdata['_free_quantity'],
				'cart_limit'           => $postdata['_cart_limit'],
				'usage_limit_per_user' => $postdata['_usage_limit_per_user'],
				'coupon_ids'           => isset( $postdata['_coupon_ids'] ) ? $postdata['_coupon_ids'] : array(),
				'exclude_product_ids'  => isset( $postdata['_exclude_product_ids'] ) ? $postdata['_exclude_product_ids'] : array(),
				'allowed_user_roles'   => isset( $postdata['_allowed_user_roles'] ) ? $postdata['_allowed_user_roles'] : array(),
				'minimum_amount'       => isset( $postdata['_minimum_amount'] ) ? wc_format_decimal( $postdata['_minimum_amount'] ) : '',
				'start_date'           => $start_date,
				'end_date'             => $end_date,
			)
		);

		if ( is_wp_error( $errors ) ) {
			WC_Admin_Meta_Boxes::add_error( $errors->get_error_message() );
		}

		/**
		 * Set props before save.
		 */
		do_action( 'wc_bogof_admin_process_rule_object', $rule, $postdata );

		$rule->save();
	}
}
