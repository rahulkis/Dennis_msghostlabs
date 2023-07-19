<?php
/**
 * Buy One Get One Free Helper Functions
 *
 * @package WC_BOGOF
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main function for returning WC_BOGOF_Rule.
 *
 * This function should only be called after 'init' action is finished, as there might be post types that are getting
 * registered during the init action.
 *
 * @since 2.1.5
 *
 * @param mixed $rule_id Rule ID of the BOGOF rule.
 * @return WC_BOGOF_Rule|false
 */
function wc_bogof_get_rule( $rule_id ) {
	if ( ! did_action( 'woocommerce_init' ) || ! did_action( 'wc_bogof_after_register_post_type' ) ) {
		/* translators: 1: wc_bogof_get_rule 2: woocommerce_init 3: wc_bogof_after_register_post_type 4: woocommerce_after_register_post_type */
		wc_doing_it_wrong( __FUNCTION__, sprintf( __( '%1$s should not be called before the %2$s, %3$s and %4$s action have finished.', 'wc-buy-one-get-one-free' ), 'wc_bogof_get_rule', 'woocommerce_init', 'wc_bogof_after_register_post_type' ), '2.1.5' );
		return false;
	}

	$rule_id = absint( $rule_id );
	try {
		return new WC_BOGOF_Rule( $rule_id );
	} catch ( Exception $e ) {
		return false;
	}
}

/*
|--------------------------------------------------------------------------
| Utils Functions
|--------------------------------------------------------------------------
*/

/**
 * Checks if the array insterset of two arrays is no empty.
 *
 * @param array $array1 The array with master values to check.
 * @param array $array2 An array to compare values against.
 * @return bool
 */
function wc_bogof_in_array_intersect( $array1, $array2 ) {
	$intersect = array_intersect( $array1, $array2 );
	return ! empty( $intersect );
}

/**
 * Return product categories IDs.
 *
 * @param int $product_id Product ID.
 * @return array
 */
function wc_bogof_get_product_cats( $product_id ) {
	$cache_key    = WC_Cache_Helper::get_cache_prefix( 'product_' . $product_id ) . '_bogof_product_cat_' . $product_id;
	$product_cats = wp_cache_get( $cache_key, 'products' );
	if ( ! is_array( $product_cats ) ) {
		$product_cats = wp_get_post_terms( $product_id, 'product_cat', array( 'fields' => 'ids' ) );
		wp_cache_set( $cache_key, $product_cats, 'products' );
	}
	return $product_cats;
}

/**
 * Check if the product belong to category.
 *
 * @param int   $product_id Product ID.
 * @param array $term_ids Array of product categories IDs to check.
 * @return bool.
 */
function wc_bogof_product_in_category( $product_id, $term_ids ) {
	$in_category = in_array( 'all', $term_ids, true );
	if ( ! $in_category ) {
		$product_cats = wc_bogof_get_product_cats( $product_id );
		$in_category  = wc_bogof_in_array_intersect( $term_ids, $product_cats );
	}
	return $in_category;
}

/**
 * Check if the curren user has one role of ta group of roles.
 *
 * @param array $roles Array roles to check.
 * @return bool.
 */
function wc_bogof_current_user_has_role( $roles ) {
	$has_role   = false;
	$user       = wp_get_current_user();
	$user_roles = empty( $user->ID ) ? array( 'not-logged-in' ) : $user->roles;
	$has_role   = wc_bogof_in_array_intersect( $user_roles, $roles );

	return $has_role;
}

/**
 * Check if the page content has the wc_choose_your_gift shortcode.
 *
 * @param int $post_id Post ID.
 * @return bool
 */
function wc_bogof_has_choose_your_gift_shortcode( $post_id ) {
	$post          = get_post( $post_id );
	$has_shortcode = false;

	if ( $post && isset( $post->post_content ) ) {
		$has_shortcode = has_shortcode( $post->post_content, 'wc_choose_your_gift' );
	}
	return $has_shortcode;
}

/**
 * Returns an array of variable product types.
 *
 * @return array
 */
function wc_bogof_variable_types() {
	return array( 'variable', 'variable-subscription' );
}

/**
 * Returns incompatible product types.
 *
 * @return array
 */
function wc_bogof_incompatible_product_types() {
	return array( 'mix-and-match', 'composite' );
}

/**
 * Returns an array with the WC Customer emails and user ID.
 *
 * @return array
 */
function wc_bogof_user_ids() {
	$ids = array();
	if ( WC()->customer ) {
		foreach ( array( 'get_email', 'get_billing_email', 'get_id' ) as $getter ) {
			if ( is_callable( array( WC()->customer, $getter ) ) ) {
				$ids[] = WC()->customer->{$getter}();
			}
		}
	}

	return array_unique( array_filter( array_map( 'strtolower', $ids ) ) );
}

/**
 * Get the product row price per item.
 *
 * @param WC_Product $product Product object.
 * @param array      $args Optional arguments to pass product quantity and price.
 * @return float
 */
function wc_bogof_get_cart_product_price( $product, $args = array() ) {
	if ( WC()->cart->display_prices_including_tax() ) {
		$product_price = wc_get_price_including_tax( $product, $args );
	} else {
		$product_price = wc_get_price_excluding_tax( $product, $args );
	}
	return $product_price;
}

/*
|--------------------------------------------------------------------------
| Transients Functions
|--------------------------------------------------------------------------
*/

/**
 * Delete product transients.
 *
 * @param int $post_id (default: 0) The product ID.
 */
function wc_bogof_delete_product_transients( $post_id ) {
	if ( $post_id ) {
		delete_transient( 'wc_bogof_rules_' . $post_id );
	}
}
add_action( 'woocommerce_delete_product_transients', 'wc_bogof_delete_product_transients' );

/**
 * Delete used by transient on order status change.
 *
 * @param int $post_id The product ID.
 */
function wc_bogof_delete_used_by_transient( $post_id ) {
	$rule_ids = get_post_meta( $post_id, '_wc_bogof_rule_id' );

	if ( is_array( $rule_ids ) && ! empty( $rule_ids ) ) {
		foreach ( array_unique( $rule_ids ) as $rule_id ) {
			delete_transient( 'wc_bogof_uses_' . $rule_id );
		}
	}
}
add_action( 'woocommerce_order_status_changed', 'wc_bogof_delete_used_by_transient' );

/**
 * Run after clear transients action of the WooCommerce System Status Tool.
 *
 * @param array $tool Details about the tool that has been executed.
 */
function wc_bogof_clear_transients( $tool ) {
	global $wpdb;

	$id = isset( $tool['id'] ) ? $tool['id'] : false;
	if ( 'clear_transients' === $id && ! empty( $tool['success'] ) ) {

		WC_Cache_Helper::get_transient_version( 'bogof_rules', true );

		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s OR option_name LIKE %s OR option_name LIKE %s",
				'_transient_timeout_wc_bogof_uses_%',
				'_transient_wc_bogof_uses_%',
				'_transient_timeout_wc_bogof_cyg_%',
				'_transient_wc_bogof_cyg_%'
			)
		);
	}
}
add_action( 'woocommerce_system_status_tool_executed', 'wc_bogof_clear_transients' );
add_action( 'woocommerce_rest_insert_system_status_tool', 'wc_bogof_clear_transients' );

/**
 * Delete transients on BOGO rule delete.
 *
 * @param mixed $id ID of post being deleted.
 */
function wc_bogof_delete_post( $id ) {
	if ( ! $id ) {
		return;
	}

	$post_type = get_post_type( $id );

	if ( 'shop_bogof_rule' === $post_type ) {
		WC_Cache_Helper::get_transient_version( 'bogof_rules', true );
	}
}
add_action( 'delete_post', 'wc_bogof_delete_post' );
add_action( 'wp_trash_post', 'wc_bogof_delete_post' );
add_action( 'untrashed_post', 'wc_bogof_delete_post' );

/*
|--------------------------------------------------------------------------
| Compatibility Functions
|--------------------------------------------------------------------------
*/

/**
 * Skips cart item to not count to the rule.
 *
 * @param WC_BOGOF_Rule $cart_rule Cart rule.
 * @param array         $cart_item Cart item.
 * @return bool
 */
function wc_bogof_cart_item_match_skip( $cart_rule, $cart_item ) {
	// Skip bundle child items.
	return apply_filters(
		'wc_bogof_cart_item_match_skip',
		( class_exists( 'WC_Product_Woosb' ) && ! empty( $cart_item['woosb_parent_id'] ) ),
		$cart_rule,
		$cart_item
	);
}

/*
|--------------------------------------------------------------------------
| Meta Box Functions
|--------------------------------------------------------------------------
*/

/**
 * Returns the display key for the _wc_bogof_rule_id meta data.
 *
 * @param string $display_key Display key.
 * @param object $meta Meta data.
 */
function wc_bogof_order_item_display_meta_key( $display_key, $meta ) {
	if ( '_wc_bogof_rule_id' === $meta->key ) {
		$display_key = 'BOGO rule';
	}
	return $display_key;
}
add_filter( 'woocommerce_order_item_display_meta_key', 'wc_bogof_order_item_display_meta_key', 10, 2 );

/**
 * Returns the display valu for the _wc_bogof_rule_id meta data.
 *
 * @param string $display_value Display value.
 * @param object $meta Meta data.
 */
function wc_bogof_order_item_display_meta_value( $display_value, $meta ) {
	if ( '_wc_bogof_rule_id' === $meta->key ) {
		$rule = wc_bogof_get_rule( $meta->value );
		if ( $rule ) {
			$display_value = sprintf( '<a href="%s">%s</a>', admin_url( '/post.php?action=edit&post=' . $rule->get_id() ), $rule->get_title() );
		}
	}
	return $display_value;
}
add_filter( 'woocommerce_order_item_display_meta_value', 'wc_bogof_order_item_display_meta_value', 10, 2 );

/**
 * Output an enhanced select.
 *
 * @param array $field Data about the field to render.
 */
function wc_bogof_enhanced_select( $field ) {
	$field = wp_parse_args(
		$field,
		array(
			'wrapper_class'     => '',
			'class'             => 'wc-enhanced-select',
			'value'             => array(),
			'options'           => array(),
			'name'              => $field['id'],
			'label'             => '',
			'desc_tip'          => false,
			'custom_attributes' => array(),
			'multiple'          => true,
			'placeholder'       => '',
			'action'            => '',
		)
	);

	$custom_attributes = (array) $field['custom_attributes'];

	?>
	<p class="form-field<?php echo esc_attr( empty( $field['wrapper_class'] ) ? '' : ' ' . $field['wrapper_class'] ); ?>">
		<label for="<?php echo esc_attr( $field['name'] ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
		<select style="width: 50%;" id="<?php echo esc_attr( $field['id'] ); ?>" class="<?php echo esc_attr( $field['class'] ); ?>" <?php echo ( $field['multiple'] ? 'multiple="multiple"' : '' ); ?> name="<?php echo esc_attr( $field['name'] ); ?><?php echo ( $field['multiple'] ? '[]' : '' ); ?>" data-placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>" data-action="<?php echo esc_attr( $field['action'] ); ?>" <?php echo wc_implode_html_attributes( $custom_attributes ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
		<?php wc_bogof_enhanced_select_options( $field['options'], $field['value'] ); ?>
		</select>
		<?php if ( $field['desc_tip'] ) : ?>
			<?php echo wc_help_tip( $field['desc_tip'] ); ?>
		<?php endif; ?>
	</p>
	<?php
}

/**
 * Output enhanced select options.
 *
 * @param array $options Options in array.
 * @param array $values Values selected.
 */
function wc_bogof_enhanced_select_options( $options, $values ) {
	foreach ( $options as $key => $option_value ) {
		if ( is_array( $option_value ) ) {
			echo '<optgroup label="' . esc_attr( $key ) . '">';
			wc_bogof_enhanced_select_options( $option_value, $values );
			echo '</optgroup>';
		} else {
			echo '<option value="' . esc_attr( $key ) . '" ' . selected( in_array( $key, $values ), true, false ) . '>' . esc_html( $option_value ) . '</option>'; // phpcs:ignore WordPress.PHP.StrictInArray
		}
	}
}

/**
 * Output an search product select.
 *
 * @param array $field Data about the field to render.
 */
function wc_bogof_search_product_select( $field ) {
	$field['object'] = isset( $field['object'] ) ? $field['object'] : 'product';
	$field['value']  = is_array( $field['value'] ) ? $field['value'] : array( $field['value'] );
	$options         = array();

	foreach ( $field['value'] as $object_id ) {
		$object = 'shop_coupon' === $field['object'] ? ( new WC_Coupon( $object_id ) ) : wc_get_product( $object_id );
		if ( $object ) {
			$formatted_name = 'shop_coupon' === $field['object'] ? $object->get_code() : $object->get_formatted_name();

			$options[ $object->get_id() ] = rawurldecode( wp_strip_all_tags( $formatted_name ) );
		}
	}

	$field['options'] = $options;
	$field['class']   = 'wc-product-search';

	wc_bogof_enhanced_select( $field );
}

/**
 * Returns an array with the BOGO rule type in type desc pair.
 *
 * @return array
 */
function wc_bogof_rule_type_options() {
	return array(
		'buy_a_get_b'   => __( 'Buy products and get a different product(s) for free', 'wc-buy-one-get-one-free' ),
		'cheapest_free' => __( 'Buy 2 (or more) products and get the cheapest one for free', 'wc-buy-one-get-one-free' ),
		'buy_a_get_a'   => __( 'Buy a product and get the same product for free', 'wc-buy-one-get-one-free' ),
	);
}
