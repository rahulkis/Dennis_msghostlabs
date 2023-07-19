<?php

new \Kirki\Field\Checkbox_Switch(
	[
	'settings'    => 'cp_setting_site_notice_enabled',
	'label'       => esc_html__( 'Enable Site Notice', 'kirki' ),
	'section'     => 'cp_section_site_notice',
	'default'     => '0',
	'priority'    => 10,
	'choices'     => [
		'on'  => esc_html__( 'Enable', 'kirki' ),
		'off' => esc_html__( 'Disable', 'kirki' ),
	],
] );

new \Kirki\Field\Radio_Buttonset(
	[
		'settings'    => 'cp_setting_site_notice_position',
		'label'       => esc_html__( 'Site Notice Position', 'kirki' ),
		'section'     => 'cp_section_site_notice',
		'default'     => 'top',
		'priority'    => 10,
		'choices'     => [
			'top'   => esc_html__( 'Top', 'kirki' ),
			'bottom' => esc_html__( 'Bottom', 'kirki' )
		],
	]
);

new \Kirki\Field\Editor(
	[
	'settings'    => 'cp_setting_site_notice',
	'label'       => esc_html__( 'Site Notice', 'kirki' ),
	'section'     => 'cp_section_site_notice',
	'default'     => '',
] );