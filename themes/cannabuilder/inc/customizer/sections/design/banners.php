<?php

new \Kirki\Section(
	'cp_section_banners',
	[
		'title'       => esc_html__( 'Banners', 'kirki' ),
		'panel'       => 'cp_panel_design',
		'priority'    => 10,
	]
);

new \Kirki\Field\Radio(
	[
	'settings'    => 'cp_setting_home_banner_text_align',
	'label'       => esc_html__( 'Home Banner Text Align', 'kirki' ),
	'section'     => 'cp_section_banners',
	'default'     => 'center',
	'choices'     => [
		'center'   => esc_html__( 'Center', 'kirki' ),
		'left' => esc_html__( 'Left', 'kirki' ),
	],
] );

new \Kirki\Field\Radio(
	[
	'settings'    => 'cp_setting_home_banner_button_style',
	'label'       => esc_html__( 'Home Banner Button Style', 'kirki' ),
	'section'     => 'cp_section_banners',
	'default'     => 'btn-primary',
	'choices'     => [
		'btn-primary' => esc_html__( 'Primary', 'kirki' ),
		'btn-primary-outline' => esc_html__( 'Primary Outline', 'kirki' ),
		'btn-white'   => esc_html__( 'White', 'kirki' ),
		'btn-white-outline'   => esc_html__( 'White Outline', 'kirki' ),
	],
] );

new \Kirki\Field\Radio(
	[
	'settings'    => 'cp_setting_home_banner_button_2_style',
	'label'       => esc_html__( 'Home Banner Button 2 Style', 'kirki' ),
	'section'     => 'cp_section_banners',
	'default'     => 'btn-primary-outline',
	'choices'     => [
		'btn-primary' => esc_html__( 'Primary', 'kirki' ),
		'btn-primary-outline' => esc_html__( 'Primary Outline', 'kirki' ),
		'btn-white'   => esc_html__( 'White', 'kirki' ),
		'btn-white-outline'   => esc_html__( 'White Outline', 'kirki' ),
	],
] );

new \Kirki\Field\Radio(
	[
	'settings'    => 'cp_setting_interior_banner_bg_image',
	'label'       => esc_html__( 'Interior Banner Background Image', 'kirki' ),
	'section'     => 'cp_section_banners',
	'default'     => 'on',
	'choices'     => [
		'on'   => esc_html__( 'On', 'kirki' ),
		'off' => esc_html__( 'Off', 'kirki' ),
	],
] );

new \Kirki\Field\Radio(
	[
	'settings'    => 'cp_setting_product_cat_banner_bg_image',
	'label'       => esc_html__( 'Product Category Background Image', 'kirki' ),
	'section'     => 'cp_section_banners',
	'default'     => 'on',
	'choices'     => [
		'on'   => esc_html__( 'On', 'kirki' ),
		'off' => esc_html__( 'Off', 'kirki' ),
	],
] );