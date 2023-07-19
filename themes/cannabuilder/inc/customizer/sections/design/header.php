<?php

new \Kirki\Section(
	'cp_section_header', [
    'title'          => esc_html__( 'Header', 'kirki' ),
    'panel'          => 'cp_panel_design',
]);

new \Kirki\Field\Radio(
	[
	'settings'    => 'cp_setting_header_background',
	'label'       => esc_html__( 'Header Background', 'kirki' ),
	'section'     => 'cp_section_header',
	'default'     => 'solid',
	'choices'     => [
		'solid'   => esc_html__( 'Solid', 'kirki' ),
		'transparent' => esc_html__( 'Transparent', 'kirki' ),
		],
] );

new \Kirki\Field\Checkbox_Switch(
	[
	'settings'    => 'cp_setting_header_show_account',
	'label'       => esc_html__( 'Account Icon', 'kirki' ),
	'section'     => 'cp_section_header',
	'default'     => '1',
	'choices'     => [
		'on'  => esc_html__( 'Show', 'kirki' ),
		'off' => esc_html__( 'Hide', 'kirki' ),
	],
] );

new \Kirki\Field\Checkbox_Switch(
	[
	'settings'    => 'cp_setting_header_show_cart',
	'label'       => esc_html__( 'Cart Icon', 'kirki' ),
	'section'     => 'cp_section_header',
	'default'     => '1',
	'choices'     => [
		'on'  => esc_html__( 'Show', 'kirki' ),
		'off' => esc_html__( 'Hide', 'kirki' ),
	],
] );

new \Kirki\Field\Checkbox_Switch(
	[
	'settings'    => 'cp_setting_header_search',
	'label'       => esc_html__( 'Search Icon', 'kirki' ),
	'section'     => 'cp_section_header',
	'default'     => '0',
	'choices'     => [
		'on'  => esc_html__( 'Show', 'kirki' ),
		'off' => esc_html__( 'Hide', 'kirki' ),
	],
] );

new \Kirki\Field\Select(
	[
	'settings'    => 'cp_setting_search_post_type',
	'label'       => esc_html__( 'Search Post Type', 'kirki' ),
	'section'     => 'cp_section_header',
	'default'     => 'product',
	'placeholder' => esc_html__( 'Select an option...', 'kirki' ),
	'priority'    => 10,
	'multiple'    => 1,
	'choices'     => [
		'product' => esc_html__( 'Products', 'kirki' ),
		'post' => esc_html__( 'Blog Posts', 'kirki' ),
	],
	'active_callback' => [
		[
			'setting'  => 'cp_setting_header_search',
			'operator' => '==',
			'value'    => true,
		]
	]
] );