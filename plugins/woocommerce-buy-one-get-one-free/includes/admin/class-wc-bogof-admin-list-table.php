<?php
/**
 * List tables: BOGOF rules.
 *
 * @package  WC_BOGOF
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'WC_BOGOF_Admin_List_Table', false ) ) {
	return;
}

if ( ! class_exists( 'WC_Admin_List_Table', false ) ) {
	include_once WC_ABSPATH . 'includes/admin/list-tables/abstract-class-wc-admin-list-table.php';
}

/**
 * WC_Admin_List_Table_Coupons Class.
 */
class WC_BOGOF_Admin_List_Table extends WC_Admin_List_Table {

	/**
	 * Post type.
	 *
	 * @var string
	 */
	protected $list_table_type = 'shop_bogof_rule';

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct();
		add_filter( 'disable_months_dropdown', '__return_true' );
		add_action( 'wp_footer', array( $this, 'enqueue_js' ) );
	}

	/**
	 * Render blank state.
	 */
	protected function render_blank_state() {
		echo '<div class="woocommerce-BlankState">';
		echo '<h2 class="woocommerce-BlankState-message">' . esc_html__( 'The Buy One Get One rules is a great way to offer rewards to your customers. They will appear here once created.', 'wc-buy-one-get-one-free' ) . '</h2>';
		echo '<a class="woocommerce-BlankState-cta button-primary button" href="' . esc_url( admin_url( 'post-new.php?post_type=shop_bogof_rule' ) ) . '">' . esc_html__( 'Create your first BOGO rule', 'wc-buy-one-get-one-free' ) . '</a>';
		echo '</div>';
	}

	/**
	 * Define primary column.
	 *
	 * @return string
	 */
	protected function get_primary_column() {
		return 'title';
	}

	/**
	 * Get row actions to show in the list table.
	 *
	 * @param array   $actions Array of actions.
	 * @param WP_Post $post Current post object.
	 * @return array
	 */
	protected function get_row_actions( $actions, $post ) {
		unset( $actions['inline hide-if-no-js'] );
		return $actions;
	}

	/**
	 * Define which columns to show on this screen.
	 *
	 * @param array $columns Existing columns.
	 * @return array
	 */
	public function define_columns( $columns ) {
		$show_columns                  = array();
		$show_columns['cb']            = $columns['cb'];
		$show_columns['name']          = __( 'Name', 'wc-buy-one-get-one-free' );
		$show_columns['enabled']       = __( 'Enabled', 'wc-buy-one-get-one-free' );
		$show_columns['type']          = __( 'Type', 'wc-buy-one-get-one-free' );
		$show_columns['applies_to']    = __( 'Applies To', 'wc-buy-one-get-one-free' );
		$show_columns['applies_to']    = __( 'Applies To', 'wc-buy-one-get-one-free' );
		$show_columns['min_quantity']  = __( 'Buy quantity', 'wc-buy-one-get-one-free' );
		$show_columns['free_quantity'] = __( 'Get free quantity', 'wc-buy-one-get-one-free' );
		$show_columns['cart_limit']    = __( 'Free items limit', 'wc-buy-one-get-one-free' );

		return $show_columns;
	}

	/**
	 * Pre-fetch any data for the row each column has access to it. the_coupon global is there for bw compat.
	 *
	 * @param int $post_id Post ID being shown.
	 */
	protected function prepare_row_data( $post_id ) {
		global $the_bogof_rule;

		if ( empty( $this->object ) || $this->object->get_id() !== $post_id ) {
			$this->object   = new WC_BOGOF_Rule( $post_id );
			$the_bogof_rule = $this->object; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
		}
	}

	/**
	 * Render columm: name.
	 */
	protected function render_name_column() {
		global $post;

		$edit_link = get_edit_post_link( $this->object->get_id() );
		$title     = $this->object->get_title();

		echo '<strong><a href="' . esc_url( $edit_link ) . '">' . esc_html( $title ) . '</a>';
		_post_states( $post );
		echo '</strong>';
	}

	/**
	 * Render columm: type.
	 */
	protected function render_type_column() {
		$options = wc_bogof_rule_type_options();
		$desc    = isset( $options[ $this->object->get_type() ] ) ? $options[ $this->object->get_type() ] : '';
		echo esc_html( $desc );
	}

	/**
	 * Render columm: applies_to.
	 */
	protected function render_applies_to_column() {
		switch ( $this->object->get_applies_to() ) {
			case 'product':
				esc_html_e( 'Specific product(s)', 'wc-buy-one-get-one-free' );
				break;
			default:
				esc_html_e( 'Product category(s)', 'wc-buy-one-get-one-free' );
				break;
		}
		echo '<br/>';
		echo wp_kses_post( $this->object_list( ', ', esc_html__( 'and', 'wc-buy-one-get-one-free' ) . ' ' ) );
	}

	/**
	 * Generate list of objects for the condition.
	 *
	 * @param string $glue Glue to implode.
	 * @param string $last_prefix Prefix of the last element.
	 */
	protected function object_list( $glue = '', $last_prefix = '' ) {
		$type   = $this->object->get_applies_to();
		$names  = array();
		$hellip = array();
		$ids    = 'product' === $type ? $this->object->get_buy_product_ids() : $this->object->get_buy_category_ids();
		$count  = 0;

		foreach ( $ids as $id ) {
			$name   = false;
			$object = 'product' === $type ? wc_get_product( $id ) : get_term( $id );

			if ( $object ) {
				$name = 'product' === $type ? $object->get_name() : $object->name;

			} elseif ( 'product' !== $type && 'all' === $id ) {
				$name = __( 'All Products', 'wc-buy-one-get-one-free' );
			}

			if ( $name ) {
				$count++;
				if ( $count > 3 ) {
					$hellip[] = $name;
				} else {
					$names[] = $name;
				}
			}
		}

		// Display only 3 elements.
		if ( count( $hellip ) ) {
			$names[] = '<span class="tips" data-tip="' . implode( ', ', $hellip ) . '">&hellip;</span>';
		}

		if ( $last_prefix && count( $names ) > 1 ) {
			$names[ count( $names ) - 1 ] = $last_prefix . $names[ count( $names ) - 1 ];
		}

		return '<strong>' . implode( $glue, $names ) . '</strong>';
	}

	/**
	 * Render columm: min_quantity.
	 */
	protected function render_min_quantity_column() {
		echo esc_html( $this->object->get_min_quantity() );
	}

	/**
	 * Render columm: min_quantity.
	 */
	protected function render_free_quantity_column() {
		echo esc_html( $this->object->get_free_quantity() );
	}

	/**
	 * Render columm: cart limit.
	 */
	protected function render_cart_limit_column() {
		$limit = '' === $this->object->get_cart_limit() ? '&infin;' : $this->object->get_cart_limit();
		echo esc_html( $limit );
	}

	/**
	 * Render columm: enabled.
	 */
	protected function render_enabled_column() {
		echo '<a class="wc-bogof-rule-toggle-enabled" href="#" data-rule_id="' . esc_attr( $this->object->get_id() ) . '">';
		if ( $this->object->get_enabled() ) {
			/* Translators: %s Payment gateway name. */
			echo '<span class="woocommerce-input-toggle woocommerce-input-toggle--enabled" aria-label="' . esc_attr( sprintf( __( 'The "%s" rule is currently enabled', 'wc-buy-one-get-one-free' ), $this->object->get_title() ) ) . '">' . esc_attr__( 'Yes', 'wc-buy-one-get-one-free' ) . '</span>';
		} else {
			/* Translators: %s Payment gateway name. */
			echo '<span class="woocommerce-input-toggle woocommerce-input-toggle--disabled" aria-label="' . esc_attr( sprintf( __( 'The "%s" rule is currently disabled', 'wc-buy-one-get-one-free' ), $this->object->get_title() ) ) . '">' . esc_attr__( 'No', 'wc-buy-one-get-one-free' ) . '</span>';
		}
		echo '</a>';
	}

}
