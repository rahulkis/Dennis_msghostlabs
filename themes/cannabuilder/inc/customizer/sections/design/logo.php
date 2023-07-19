<?php

new \Kirki\Section(
	'cp_section_logo', [
    'title'          => esc_html__( 'Logo', 'kirki' ),
    'panel'          => 'cp_panel_design',
]);

new \Kirki\Field\Image(
	[
	'settings'    => 'cp_setting_logo_header',
	'label'       => esc_html__( 'Header Logo', 'kirki' ),
	'section'     => 'cp_section_logo',
	'default'     => '',
	'choices'     => [
		'save_as' => 'array',
	],
] );

new \Kirki\Field\Image(
	[
	'settings'    => 'cp_setting_logo_header_scrolled',
	'label'       => esc_html__( 'Header Logo - Scrolled', 'kirki' ),
	'section'     => 'cp_section_logo',
	'default'     => '',
	'choices'     => [
		'save_as' => 'array',
	],
	'active_callback' => [
		[
			'setting'  => 'cp_setting_header_background',
			'operator' => '==',
			'value'    => 'transparent',
		]
	]
] );

new \Kirki\Field\Number(
	[
	'settings'    => 'cp_setting_logo_header_width_mobile',
	'label'       => esc_html__( 'Logo Width Mobile', 'kirki' ),
	'section'     => 'cp_section_logo',
	'default'     => 180,
	'output'      => [
		[
			'element' => '.site-branding',
			'property' => 'width',
			'units' => 'px'
		],
	],
] );

new \Kirki\Field\Number(
	[
	'settings'    => 'cp_setting_logo_header_width_mobile_scrolled',
	'label'       => esc_html__( 'Logo Width Mobile - Scrolled', 'kirki' ),
	'section'     => 'cp_section_logo',
	'default'     => 120,
	'output'      => [
		[
			'element' => '.is-scrolled .site-branding',
			'property' => 'width',
			'units' => 'px'
		],
	],
] );

new \Kirki\Field\Number(
	[
	'settings'    => 'cp_setting_logo_header_width_desktop',
	'label'       => esc_html__( 'Logo Width Desktop', 'kirki' ),
	'section'     => 'cp_section_logo',
	'default'     => 240,
	'output'      => [
		[
			'element' => '.site-branding',
			'property' => 'width',
			'units' => 'px',
			'media_query' => '@media (min-width: 1100px)'
		],
	],
] );

new \Kirki\Field\Number(
	[
	'settings'    => 'cp_setting_logo_header_width_desktop_scrolled',
	'label'       => esc_html__( 'Logo Width Desktop - Scrolled', 'kirki' ),
	'section'     => 'cp_section_logo',
	'default'     => 180,
	'output'      => [
		[
			'element' => '.is-scrolled .site-branding',
			'property' => 'width',
			'units' => 'px',
			'media_query' => '@media (min-width: 1100px)'
		],
	],
] );

new \Kirki\Field\Image(
	[
	'settings'    => 'cp_setting_logo_footer',
	'label'       => esc_html__( 'Footer Logo', 'kirki' ),
	'section'     => 'cp_section_logo',
	'default'     => '',
	'choices'     => [
		'save_as' => 'array',
	],
] );

new \Kirki\Field\Number(
	[
	'settings'    => 'cp_setting_logo_footer_width',
	'label'       => esc_html__( 'Footer Logo Width', 'kirki' ),
	'section'     => 'cp_section_logo',
	'default'     => 240,
	'output'      => [
		[
			'element' => '.footer-item-logo img',
			'property' => 'width',
			'units' => 'px'
		]
	]
] );