<?php
/**
 * Buy One Get One rule general data panel.
 *
 * @package WC_BOGOF
 */

defined( 'ABSPATH' ) || exit;

$category_label = __( 'Product categories', 'wc-buy-one-get-one-free' );

?>
<div id="general_bogof_rule_data" class="panel woocommerce_options_panel">
	<div class="options_group">
	<?php
		woocommerce_wp_checkbox(
			array(
				'id'          => '_enabled',
				'label'       => __( 'Enable/Disable ', 'wc-buy-one-get-one-free' ),
				'description' => __( 'Enable rule', 'wc-buy-one-get-one-free' ),
			)
		);

		woocommerce_wp_select(
			array(
				'id'      => '_type',
				'label'   => __( 'Deal mode', 'wc-buy-one-get-one-free' ),
				'options' => wc_bogof_rule_type_options(),
			)
		);

		woocommerce_wp_select(
			array(
				'id'      => '_applies_to',
				'label'   => __( 'Applies To', 'wc-buy-one-get-one-free' ),
				'options' => array(
					'product'  => __( 'Specific product(s)', 'wc-buy-one-get-one-free' ),
					'category' => __( 'Product category(s)', 'wc-buy-one-get-one-free' ),
				),
			)
		);

		wc_bogof_enhanced_select(
			array(
				'id'            => '_buy_category_ids',
				'wrapper_class' => '_buy_category_ids_field',
				'label'         => __( 'Categories', 'wc-buy-one-get-one-free' ),
				'options'       => array(
					'all'           => __( 'All Products', 'wc-buy-one-get-one-free' ),
					$category_label => $product_cats,
				),
				'value'         => $rule->get_buy_category_ids(),
				'placeholder'   => __( 'Choose categories&hellip;', 'wc-buy-one-get-one-free' ),
			)
		);

		wc_bogof_search_product_select(
			array(
				'id'            => '_buy_product_ids',
				'wrapper_class' => '_buy_product_ids_field',
				'label'         => __( 'Products', 'wc-buy-one-get-one-free' ),
				'value'         => $rule->get_buy_product_ids(),
				'placeholder'   => __( 'Search for a product&hellip;', 'wc-buy-one-get-one-free' ),
			)
		);
		?>
	</div>
	<div class="options_group" style="display:none;">
		<?php
		woocommerce_wp_checkbox(
			array(
				'id'          => '_individual',
				'label'       => __( 'Individual ', 'wc-buy-one-get-one-free' ),
				'description' => __( 'Check this box if the rule should be applied individually to each product of the category.', 'wc-buy-one-get-one-free' ),
			)
		);
		?>
	</div>
	<div class="options_group">
		<?php
		woocommerce_wp_text_input(
			array(
				'id'                => '_min_quantity',
				'label'             => __( 'Buy quantity', 'wc-buy-one-get-one-free' ),
				'type'              => 'number',
				'custom_attributes' => array(
					'step' => 1,
					'min'  => 0,
				),
			)
		);
		?>
	</div>
	<div class="options_group" style="display:none;">
		<?php
		woocommerce_wp_select(
			array(
				'id'      => '_action',
				'label'   => __( 'Action', 'wc-buy-one-get-one-free' ),
				'options' => array(
					'add_to_cart'          => __( 'Add a free product to the cart', 'wc-buy-one-get-one-free' ),
					'choose_from_category' => __( 'Allow customers to choose the gift(s) from a product category(s)', 'wc-buy-one-get-one-free' ),
					'choose_from_products' => __( 'Allow customers to choose the gift(s) from a lists of products', 'wc-buy-one-get-one-free' ),
				),
			)
		);

		wc_bogof_search_product_select(
			array(
				'id'                => '_free_product_id',
				'wrapper_class'     => 'action_objects_fields show_if_add_to_cart',
				'label'             => __( 'Free product', 'wc-buy-one-get-one-free' ),
				'placeholder'       => __( 'Search for a product&hellip;', 'wc-buy-one-get-one-free' ),
				'action'            => 'wc_bogof_json_search_free_products',
				'multiple'          => false,
				'value'             => $rule->get_free_product_id(),
				'custom_attributes' => array(
					'data-exclude' => implode( ',', array_merge( wc_bogof_variable_types(), wc_bogof_incompatible_product_types() ) ),
				),
			)
		);

		wc_bogof_search_product_select(
			array(
				'id'            => '_free_product_ids',
				'wrapper_class' => 'action_objects_fields show_if_choose_from_products',
				'label'         => __( 'From products', 'wc-buy-one-get-one-free' ),
				'placeholder'   => __( 'Search for a product&hellip;', 'wc-buy-one-get-one-free' ),
				'action'        => 'wc_bogof_json_search_free_products',
				'value'         => $rule->get_free_product_ids(),
			)
		);

		wc_bogof_enhanced_select(
			array(
				'id'            => '_free_category_ids',
				'label'         => __( 'From product categories', 'wc-buy-one-get-one-free' ),
				'wrapper_class' => 'action_objects_fields show_if_choose_from_category',
				'options'       => array(
					'all'           => __( 'All Products', 'wc-buy-one-get-one-free' ),
					$category_label => $product_cats,
				),
				'value'         => $rule->get_free_category_ids(),
				'placeholder'   => __( 'Choose categories&hellip;', 'wc-buy-one-get-one-free' ),
			)
		);
		?>
	</div>
	<div class="options_group">
		<?php
		woocommerce_wp_text_input(
			array(
				'id'                => '_free_quantity',
				'label'             => __( 'Get free quantity', 'wc-buy-one-get-one-free' ),
				'type'              => 'number',
				'description'       => __( 'The free items the user will get when buy multiples of "Buy quantity".', 'wc-buy-one-get-one-free' ),
				'desc_tip'          => true,
				'custom_attributes' => array(
					'step' => 1,
					'min'  => 0,
				),
			)
		);
		?>
	</div>
</div>
