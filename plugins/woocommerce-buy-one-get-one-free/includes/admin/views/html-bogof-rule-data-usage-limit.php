<?php
/**
 * Buy One Get One rule usage limit data panel.
 *
 * @package WC_BOGOF
 */

defined( 'ABSPATH' ) || exit;
?>
<div id="usage_limit_bogof_rule_data" class="panel woocommerce_options_panel">
	<div class="options_group">
	<?php
	woocommerce_wp_text_input(
		array(
			'id'                => '_cart_limit',
			'label'             => __( 'Free items limit', 'wc-buy-one-get-one-free' ),
			'description'       => __( 'The maximum number of free items the user can get when buy multiples of "Buy quantity". Leave blank for unlimited free items.', 'wc-buy-one-get-one-free' ),
			'desc_tip'          => true,
			'type'              => 'number',
			'placeholder'       => esc_attr__( 'Unlimited free items', 'wc-buy-one-get-one-free' ),
			'custom_attributes' => array(
				'step' => 1,
				'min'  => 0,
			),
		)
	);

	woocommerce_wp_text_input(
		array(
			'id'                => '_usage_limit_per_user',
			'label'             => __( 'Usage limit per user', 'wc-buy-one-get-one-free' ),
			'description'       => __( 'How many times this rule can be used by an individual user. Leave blank for unlimited.', 'wc-buy-one-get-one-free' ),
			'desc_tip'          => true,
			'type'              => 'number',
			'placeholder'       => esc_attr__( 'Unlimited usage', 'wc-buy-one-get-one-free' ),
			'custom_attributes' => array(
				'step' => 1,
				'min'  => 0,
			),
		)
	);

	?>
	</div>
</div>
