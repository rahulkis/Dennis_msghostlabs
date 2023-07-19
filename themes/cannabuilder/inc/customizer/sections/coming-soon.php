<?php

new \Kirki\Section(
	'cp_section_coming_soon', [
    'title'          => esc_html__( 'Coming Soon', 'kirki' ),
    'priority'       => 9999,
]);

new \Kirki\Field\Checkbox_Switch(
	[
	'settings'    => 'cp_setting_coming_soon_mode',
	'label'       => esc_html__( 'Coming Soon Mode', 'kirki' ),
	'section'     => 'cp_section_coming_soon',
	'default'     => '0',
	'priority'    => 10,
	'choices'     => [
		'on'  => esc_html__( 'Enable', 'kirki' ),
		'off' => esc_html__( 'Disable', 'kirki' ),
	],
] );

new \Kirki\Field\Checkbox_Switch(
	[
	'settings'    => 'cp_setting_coming_soon_preview',
	'label'       => esc_html__( 'Coming Soon Preview', 'kirki' ),
	'description' => 'Preview the coming soon template',
	'section'     => 'cp_section_coming_soon',
	'default'     => '0',
	'priority'    => 10,
	'choices'     => [
		'on'  => esc_html__( 'Enable', 'kirki' ),
		'off' => esc_html__( 'Disable', 'kirki' ),
	],
] );

new \Kirki\Field\Color(
	[
	'settings'    => 'cp_setting_coming_soon_background_color',
	'label'       => __( 'Background Color', 'kirki' ),
	'section'     => 'cp_section_coming_soon',
	'default'     => '#ffffff',
	'output'      => [
		[
			'element'  => '.template-coming-soon',
			'property' => 'background-color',
		]
	]
] );

new \Kirki\Field\Image(
	[
	'settings'    => 'cp_setting_coming_soon_background_image',
	'label'       => esc_html__( 'Background Image', 'kirki' ),
	'section'     => 'cp_section_coming_soon',
	'default'     => '',
	'choices'     => [
		'save_as' => 'array',
	],
] );

new \Kirki\Field\Color(
	[
	'settings'    => 'cp_setting_coming_soon_text_color',
	'label'       => __( 'Text Color', 'kirki' ),
	'section'     => 'cp_section_coming_soon',
	'default'     => '',
	'output'      => [
		[
			'element'  => '.coming-soon-content p, .coming-soon-content ul, .coming-soon-content ol, .coming-soon-content h1, .coming-soon-content h2, .coming-soon-content h3, .coming-soon-content h4, .coming-soon-content h5, .coming-soon-content h6',
			'property' => 'color',
		]
	]
] );

new \Kirki\Field\Color(
	[
	'settings'    => 'cp_setting_coming_soon_link_color',
	'label'       => __( 'Link Color', 'kirki' ),
	'section'     => 'cp_section_coming_soon',
	'default'     => '',
	'output'      => [
		[
			'element'  => '.coming-soon-content p a',
			'property' => 'color',
		]
	]
] );

new \Kirki\Field\Image(
	[
	'settings'    => 'cp_setting_coming_soon_logo',
	'label'       => esc_html__( 'Logo', 'kirki' ),
	'section'     => 'cp_section_coming_soon',
	'default'     => '',
	'choices'     => [
		'save_as' => 'array',
	],
] );

new \Kirki\Field\Number(
	[
	'settings'    => 'cp_setting_coming_soon_logo_width',
	'label'       => esc_html__( 'Logo Width', 'kirki' ),
	'section'     => 'cp_section_coming_soon',
	'default'     => 350,
	'output'      => [
		[
			'element' => '.coming-soon .site-branding-logo',
			'property' => 'width',
			'units' => 'px'
		],
	],
] );

new \Kirki\Field\Editor(
	[
	'settings'    => 'cp_setting_coming_soon_content',
	'label'       => esc_html__( 'Content', 'kirki' ),
	'section'     => 'cp_section_coming_soon',
	'default'     => '',
] );
