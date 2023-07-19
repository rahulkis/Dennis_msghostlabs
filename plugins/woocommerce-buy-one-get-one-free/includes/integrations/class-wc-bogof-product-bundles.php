<?php
/**
 * Buy One Get One Free - Product Bundles by SomewhereWarm
 *
 * @see https://woocommerce.com/products/product-bundles/
 * @since 2.1.0
 * @package WC_BOGOF
 */

defined( 'ABSPATH' ) || exit;

/**
 * WC_BOGOF_Product_Bundles Class
 */
class WC_BOGOF_Product_Bundles {

	/**
	 * Retrun the minimun version required.
	 */
	public static function min_version_required() {
		return '6.3.0';
	}

	/**
	 * Returns the extension name.
	 */
	public static function extension_name() {
		return 'Product Bundles';
	}

	/**
	 * Checks the minimum version required.
	 */
	public static function check_min_version() {
		return defined( 'WC_PB_VERSION' ) ? version_compare( WC_PB_VERSION, static::min_version_required(), '>=' ) : false;
	}

	/**
	 * Init hooks
	 */
	public static function init() {
		add_action( 'wc_bogof_after_set_cart_item_discount', array( __CLASS__, 'cart_item_discount_init' ), 10, 2 );
		add_filter( 'wc_bogof_cart_item_discount_sale_price', array( __CLASS__, 'cart_item_discount_sale_price' ), 10, 2 );
		add_filter( 'wc_bogof_discount_line_subtotal_prefix', array( __CLASS__, 'discount_line_subtotal_prefix' ), 10, 2 );
		add_filter( 'woocommerce_cart_item_subtotal', array( __CLASS__, 'cart_item_subtotal' ), 9999, 2 );
		add_filter( 'wc_bogof_cart_item_match_skip', array( __CLASS__, 'cart_item_match_skip' ), 10, 3 );
		add_filter( 'wc_bogof_rule_data_tabs', array( __CLASS__, 'rule_data_tabs' ) );
		add_action( 'wc_bogof_rule_data_panels', array( __CLASS__, 'rule_data_panels' ) );
		add_action( 'wc_bogof_admin_process_rule_object', array( __CLASS__, 'admin_process_rule_object' ), 10, 2 );
	}

	/**
	 * Recalculate the bundle base price.
	 *
	 * @param array                       $cart_item Cart item data.
	 * @param WC_BOGOF_Cart_Item_Discount $cart_discount The discount object.
	 */
	public static function cart_item_discount_init( $cart_item, $cart_discount ) {
		if ( wc_pb_is_bundle_container_cart_item( $cart_item ) && WC_Product_Bundle::group_mode_has( $cart_item['data']->get_group_mode(), 'aggregated_prices' ) && ! $cart_discount->get_extra_data( 'is_bundle' ) ) {
			// Calculate the price based on bundles items.
			$bundle_price  = $cart_discount->get_base_price();
			$base_price    = $bundle_price;
			$items_price   = 0;
			$bundled_items = wc_pb_get_bundled_cart_items( $cart_item, WC()->cart->cart_contents );

			foreach ( $bundled_items as $bundled_item ) {
				$base_price  += $bundled_item['data']->get_price() * absint( $bundled_item['quantity'] ) / absint( $cart_item['quantity'] );
				$items_price += $bundled_item['data']->get_price() * $bundled_item['quantity'];
			}

			$cart_discount->set_base_price( $base_price );
			$cart_discount->add_extra_data( 'is_bundle', true );
			$cart_discount->add_extra_data( 'bundle_price', $bundle_price );
			$cart_discount->add_extra_data( 'bundle_items_price', $items_price );
		}
	}

	/**
	 * Return the sale price for bundle.
	 *
	 * @param float                       $sale_price Discount sale price.
	 * @param WC_BOGOF_Cart_Item_Discount $cart_discount The discount object.
	 */
	public static function cart_item_discount_sale_price( $sale_price, $cart_discount ) {
		if ( $cart_discount->get_extra_data( 'is_bundle' ) ) {

			$bundle_price       = floatval( $cart_discount->get_extra_data( 'bundle_price' ) ) * $cart_discount->get_cart_quantity();
			$bundle_items_price = floatval( $cart_discount->get_extra_data( 'bundle_items_price' ) );

			$final_price = $bundle_price + $bundle_items_price - $cart_discount->get_discount();
			$sale_price  = ( $final_price - $bundle_items_price ) / $cart_discount->get_cart_quantity();

		}
		return $sale_price;
	}

	/**
	 * Removes the discount prefix for bundle items.
	 *
	 * @param string $prefix Discount prefix.
	 * @param string $cart_subtotal Subtotal to display after discount.
	 */
	public static function discount_line_subtotal_prefix( $prefix, $cart_subtotal ) {
		if ( false !== strpos( $cart_subtotal, 'bundled_table_item_subtotal' ) ) {
			$prefix = '';
		}
		return $prefix;
	}

	/**
	 * Cart item subtotal. Recalculate the subtotal for Bundle containers.
	 *
	 * @param string $cart_subtotal Subtotal to display.
	 * @param array  $cart_item Cart item.
	 */
	public static function cart_item_subtotal( $cart_subtotal, $cart_item ) {
		if ( WC_BOGOF_Cart::is_valid_discount( $cart_item ) && $cart_item['data']->_bogof_discount->get_extra_data( 'is_bundle' ) ) {
			$bundle_price  = wc_bogof_get_cart_product_price( $cart_item['data'], array( 'qty' => $cart_item['quantity'] ) );
			$bundled_items = wc_pb_get_bundled_cart_items( $cart_item, WC()->cart->cart_contents );

			foreach ( $bundled_items as $bundled_item ) {
				$bundle_price += wc_bogof_get_cart_product_price( $bundled_item['data'], array( 'qty' => $bundled_item['quantity'] ) );
			}
			$cart_subtotal = WC_PB_Display::instance()->format_subtotal( $cart_item['data'], $bundle_price );

		}
		return $cart_subtotal;
	}

	/**
	 * Skip bundle contaniner or bundle items on the rule match function.
	 *
	 * @param bool               $skip True or false.
	 * @param WC_BOGOF_Cart_Rule $cart_rule Cart rule object.
	 * @param array              $cart_item Cart item.
	 * @return bool
	 */
	public static function cart_item_match_skip( $skip, $cart_rule, $cart_item ) {
		$applies_to = $cart_rule->get_rule()->get_meta( '_pb_applies_to' );

		return $skip || ( 'child' !== $applies_to && ! empty( $cart_item['bundled_item_id'] ) ) || ( 'child' === $applies_to && ! empty( $cart_item['bundled_items'] ) );
	}

	/**
	 * Add Product Bundles for the BOGOF Rule meta-box tabs.
	 *
	 * @param array $tabs Array of tabs.
	 */
	public static function rule_data_tabs( $tabs ) {
		$tabs['wc_bundles'] = array(
			'label'  => __( 'Bundled products', 'wc-buy-one-get-one-free' ),
			'target' => 'bundled_products_bogof_rule_data',
			'class'  => '',
		);

		return $tabs;
	}

	/**
	 * Output the data panel.
	 */
	public static function rule_data_panels() {
		?>
		<div id="bundled_products_bogof_rule_data" class="panel woocommerce_options_panel">
			<div class="options_group">
			<?php
				woocommerce_wp_select(
					array(
						'id'          => '_pb_applies_to',
						'label'       => __( 'Applies rule to', 'wc-buy-one-get-one-free' ),
						'description' => __( 'Controls to which element of the product bundle the rule applies', 'wc-buy-one-get-one-free' ),
						'desc_tip'    => true,
						'options'     => array(
							''      => __( 'Parent line item (default)', 'wc-buy-one-get-one-free' ),
							'child' => __( 'Child line items (each of the bundled products)', 'wc-buy-one-get-one-free' ),
						),
					)
				);
			?>
			</div>
		</div>
		<?php
	}

	/**
	 * Set metadata before save.
	 *
	 * @param WC_BOGOF_Rule $rule rule object.
	 * @param array         $postdata Data of the _POST array sanitized.
	 */
	public static function admin_process_rule_object( $rule, $postdata ) {
		$applies_to = empty( $postdata['_pb_applies_to'] ) ? '' : $postdata['_pb_applies_to'];
		$rule->update_meta_data( '_pb_applies_to', $applies_to );
	}
}
