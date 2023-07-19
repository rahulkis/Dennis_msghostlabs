<?php

new \Kirki\Section(
	'cp_section_policies', [
    'title'          => esc_html__( 'Policy Settings', 'kirki' ),
	'priority'       => 9999,
]);

new \Kirki\Field\Text(
	[
	'settings' => 'cp_setting_business_name',
	'label'    => esc_html__( 'Business Name', 'kirki' ),
	'section'  => 'cp_section_policies',
	'priority' => 10,
	'default'  => get_bloginfo('name')
] );

new \Kirki\Field\Text(
	[
	'settings' => 'cp_setting_business_url',
	'label'    => esc_html__( 'Business URL', 'kirki' ),
	'section'  => 'cp_section_policies',
	'priority' => 10,
	'default'  => get_site_url()
] );

new \Kirki\Field\Text(
	[
	'settings' => 'cp_setting_business_email',
	'label'    => esc_html__( 'Business Email', 'kirki' ),
	'section'  => 'cp_section_policies',
	'priority' => 10,
	'default'  => 'BUSINESS EMAIL'
] );

new \Kirki\Field\Text(
	[
	'settings' => 'cp_setting_business_phone',
	'label'    => esc_html__( 'Business Phone', 'kirki' ),
	'section'  => 'cp_section_policies',
	'priority' => 10,
	'default'  => 'BUSINESS PHONE'
] );