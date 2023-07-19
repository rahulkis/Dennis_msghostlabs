<?php
/**
 * Buy One Get One rule restriction data panel.
 *
 * @package WC_BOGOF
 */

defined( 'ABSPATH' ) || exit;

$user_roles_label = __( 'User roles', 'wc-buy-one-get-one-free' );

?>
<div id="usage_restriction_bogof_rule_data" class="panel woocommerce_options_panel">
	<div class="options_group">
		<?php
		// minimum spend.
		woocommerce_wp_text_input(
			array(
				'id'          => '_minimum_amount',
				'label'       => __( 'Minimum spend', 'wc-buy-one-get-one-free' ),
				'placeholder' => __( 'No minimum', 'wc-buy-one-get-one-free' ),
				'description' => __( 'This field allows you to set the minimum spend (subtotal minus discount) allowed to use the rule.', 'woocommerce' ),
				'data_type'   => 'price',
				'desc_tip'    => true,
				'value'       => $rule->get_minimum_amount( 'edit' ),
			)
		);
		?>
	</div>
	<div class="options_group">
		<?php
		wc_bogof_search_product_select(
			array(
				'id'          => '_coupon_ids',
				'label'       => __( 'Coupons', 'wc-buy-one-get-one-free' ),
				'placeholder' => __( 'Search for a coupon&hellip;', 'wc-buy-one-get-one-free' ),
				'action'      => 'wc_bogof_json_search_coupons',
				'object'      => 'shop_coupon',
				'value'       => $rule->get_coupon_ids(),
				'desc_tip'    => __( 'Coupons that enable the rule.', 'wc-buy-one-get-one-free' ),
			)
		);
		?>
	</div>
	<div class="options_group">
		<?php
		wc_bogof_search_product_select(
			array(
				'id'          => '_exclude_product_ids',
				'label'       => __( 'Exclude products', 'wc-buy-one-get-one-free' ),
				'placeholder' => __( 'Search for a product&hellip;', 'wc-buy-one-get-one-free' ),
				'value'       => $rule->get_exclude_product_ids(),
				'desc_tip'    => __( 'Products that the rule will not be applied to.', 'wc-buy-one-get-one-free' ),
			)
		);
		?>
	</div>
	<div class="options_group">
		<?php
		wc_bogof_enhanced_select(
			array(
				'id'          => '_allowed_user_roles',
				'label'       => __( 'Allowed user roles', 'wc-buy-one-get-one-free' ),
				'placeholder' => __( 'Choose user roles&hellip;', 'wc-buy-one-get-one-free' ),
				'desc_tip'    => __( 'User roles that the rule will be available.', 'wc-buy-one-get-one-free' ),
				'options'     => array(
					'not-logged-in'   => 'Users not logged in',
					$user_roles_label => wp_list_pluck( get_editable_roles(), 'name' ),
				),
				'value'       => $rule->get_allowed_user_roles(),
			)
		);
		?>
	</div>
	<div class="options_group">
		<?php

		$start_date_timestamp = $rule->get_start_date( 'edit' ) ? $rule->get_start_date( 'edit' )->getOffsetTimestamp() : false;
		$end_date_timestamp   = $rule->get_end_date( 'edit' ) ? $rule->get_end_date( 'edit' )->getOffsetTimestamp() : false;

		$start_date = $start_date_timestamp ? date_i18n( 'Y-m-d', $start_date_timestamp ) : '';
		$end_date   = $end_date_timestamp ? date_i18n( 'Y-m-d', $end_date_timestamp ) : '';


		woocommerce_wp_text_input(
			array(
				'id'                => '_start_date',
				'value'             => $start_date,
				'label'             => __( 'Start date', 'wc-buy-one-get-one-free' ),
				'placeholder'       => 'YYYY-MM-DD',
				'description'       => __( 'The deal will begin at 00:00 of this date.', 'wc-buy-one-get-one-free' ),
				'desc_tip'          => true,
				'class'             => 'date-picker',
				'custom_attributes' => array(
					'pattern' => apply_filters( 'woocommerce_date_input_html_pattern', '[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])' ),
				),
			)
		);
		woocommerce_wp_text_input(
			array(
				'id'                => '_end_date',
				'value'             => $end_date,
				'label'             => __( 'End date', 'wc-buy-one-get-one-free' ),
				'placeholder'       => 'YYYY-MM-DD',
				'description'       => __( 'The deal will end at 23:59 of this date.', 'wc-buy-one-get-one-free' ),
				'desc_tip'          => true,
				'class'             => 'date-picker',
				'custom_attributes' => array(
					'pattern' => apply_filters( 'woocommerce_date_input_html_pattern', '[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])' ),
				),
			)
		);
		?>
	</div>
</div>
