<?php

new \Kirki\Section(
	'cp_section_blog', [
    'title'          => esc_html__( 'Blog', 'kirki' ),
    'panel'          => 'cp_panel_design',
]);

new \Kirki\Field\Checkbox_Switch(
	[
	'settings'    => 'cp_setting_blog_sidebar',
	'label'       => esc_html__( 'Sidebar', 'kirki' ),
	'description' => esc_html__( 'Show the sidebar on the blog page', 'kirki' ),
	'section'     => 'cp_section_blog',
	'default'     => '0',
	'priority'    => 10,
	'choices'     => [
		'on'  => esc_html__( 'Enable', 'kirki' ),
		'off' => esc_html__( 'Disable', 'kirki' ),
	],
] );

new \Kirki\Field\Radio(
	[
	'settings'    => 'cp_setting_blog_layout',
	'label'       => esc_html__( 'Blog Layout', 'kirki' ),
	'section'     => 'cp_section_blog',
	'default'     => 'columns',
	'priority'    => 10,
	'choices'     => [
		'columns'   => esc_html__( 'Column Layout', 'kirki' ),
		'list' => esc_html__( 'List Layout', 'kirki' ),
	],
] );


new \Kirki\Field\Checkbox_Switch(
	[
	'settings'    => 'cp_setting_blog_featured_image',
	'label'       => esc_html__( 'Featured Image', 'kirki' ),
	'description' => esc_html__( 'Show the featured image on the blog detail page', 'kirki' ),
	'section'     => 'cp_section_blog',
	'default'     => '1',
	'priority'    => 10,
	'choices'     => [
		'on'  => esc_html__( 'Enable', 'kirki' ),
		'off' => esc_html__( 'Disable', 'kirki' ),
	],
] );

new \Kirki\Field\Checkbox_Switch(
	[
	'settings'    => 'cp_setting_blog_post_date',
	'label'       => esc_html__( 'Post Date', 'kirki' ),
	'description' => esc_html__( 'Show the post date on the blog list and blog post pages', 'kirki' ),
	'section'     => 'cp_section_blog',
	'default'     => '1',
	'priority'    => 10,
	'choices'     => [
		'on'  => esc_html__( 'Enable', 'kirki' ),
		'off' => esc_html__( 'Disable', 'kirki' ),
	],
] );