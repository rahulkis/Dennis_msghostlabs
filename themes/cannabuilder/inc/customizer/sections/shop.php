<?php

new \Kirki\Field\Checkbox_Switch(
	[
	'settings'    => 'cp_setting_shop_sidebar',
	'label'       => esc_html__( 'Shop Sidebar', 'kirki' ),
	'section'     => 'woocommerce_product_catalog',
	'default'     => '1',
	'priority'    => 10,
	'choices'     => [
		'on'  => esc_html__( 'Enable', 'kirki' ),
		'off' => esc_html__( 'Disable', 'kirki' ),
	],
] );

add_action('customize_register','cp_add_woocommerce_sections');
function cp_add_woocommerce_sections( $wp_customize ) {
	$wp_customize->add_section(
		'woocommerce_product_detail',
		array(
			'title'       => __( 'Product Detail', 'woocommerce' ),
			'priority'    => 1000,
			'panel'       => 'woocommerce',
		)
	);
}

new \Kirki\Field\Checkbox_Switch(
	[
	'settings'    => 'cp_setting_product_detail_show_sku',
	'label'       => esc_html__( 'Show SKU', 'kirki' ),
	'section'     => 'woocommerce_product_detail',
	'default'     => '1',
	'priority'    => 10,
	'choices'     => [
		'on'  => esc_html__( 'Show', 'kirki' ),
		'off' => esc_html__( 'Hide', 'kirki' ),
	],
] );

new \Kirki\Field\Checkbox_Switch(
	[
	'settings'    => 'cp_setting_product_detail_show_categories',
	'label'       => esc_html__( 'Show Categories', 'kirki' ),
	'section'     => 'woocommerce_product_detail',
	'default'     => '1',
	'priority'    => 10,
	'choices'     => [
		'on'  => esc_html__( 'Show', 'kirki' ),
		'off' => esc_html__( 'Hide', 'kirki' ),
	],
] );

new \Kirki\Field\Checkbox_Switch(
	[
	'settings'    => 'cp_setting_product_detail_show_tags',
	'label'       => esc_html__( 'Show Tags', 'kirki' ),
	'section'     => 'woocommerce_product_detail',
	'default'     => '1',
	'priority'    => 10,
	'choices'     => [
		'on'  => esc_html__( 'Show', 'kirki' ),
		'off' => esc_html__( 'Hide', 'kirki' ),
	],
] );

new \Kirki\Field\Checkbox_Switch(
	[
	'settings'    => 'cp_setting_product_detail_show_additional_info',
	'label'       => esc_html__( 'Show Additional Info Tab', 'kirki' ),
	'section'     => 'woocommerce_product_detail',
	'default'     => '1',
	'priority'    => 10,
	'choices'     => [
		'on'  => esc_html__( 'Show', 'kirki' ),
		'off' => esc_html__( 'Hide', 'kirki' ),
	],
] );