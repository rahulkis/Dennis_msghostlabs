<?php

new \Kirki\Section(
	'cp_section_footer_social', [
    'title'          => esc_html__( 'Social Links', 'kirki' ),
    'panel'          => 'cp_panel_footer',
]);

new \Kirki\Field\Text(
	[
	'settings' => 'cp_setting_social_title',
	'label'    => esc_html__( 'Title', 'kirki' ),
	'section'  => 'cp_section_footer_social',
	'priority' => 10,
] );

new \Kirki\Field\Editor(
	[
	'settings'    => 'cp_setting_social_content',
	'label'       => esc_html__( 'Content', 'kirki' ),
	'section'     => 'cp_section_footer_social',
	'default'     => '',
] );

new \Kirki\Field\Text(
	[
	'settings' => 'cp_setting_social_facebook',
	'label'    => esc_html__( 'Facebook URL', 'kirki' ),
	'section'  => 'cp_section_footer_social',
	'priority' => 10,
] );

new \Kirki\Field\Text(
	[
	'settings' => 'cp_setting_social_instagram',
	'label'    => esc_html__( 'Instagram URL', 'kirki' ),
	'section'  => 'cp_section_footer_social',
	'priority' => 10,
] );

new \Kirki\Field\Text(
	[
	'settings' => 'cp_setting_social_twitter',
	'label'    => esc_html__( 'Twitter URL', 'kirki' ),
	'section'  => 'cp_section_footer_social',
	'priority' => 10,
] );

new \Kirki\Field\Text(
	[
	'settings' => 'cp_setting_social_youtube',
	'label'    => esc_html__( 'YouTube URL', 'kirki' ),
	'section'  => 'cp_section_footer_social',
	'priority' => 10,
] );

new \Kirki\Field\Text(
	[
	'settings' => 'cp_setting_social_tiktok',
	'label'    => esc_html__( 'TikTok URL', 'kirki' ),
	'section'  => 'cp_section_footer_social',
	'priority' => 10,
] );

new \Kirki\Field\Text(
	[
	'settings' => 'cp_setting_social_pinterest',
	'label'    => esc_html__( 'Pinterest URL', 'kirki' ),
	'section'  => 'cp_section_footer_social',
	'priority' => 10,
] );

new \Kirki\Field\Text(
	[
	'settings' => 'cp_setting_social_linkedin',
	'label'    => esc_html__( 'LinkedIn URL', 'kirki' ),
	'section'  => 'cp_section_footer_social',
	'priority' => 10,
] );

new \Kirki\Section(
	'cp_section_footer_area_1', [
    'title'          => esc_html__( 'Footer Area 1', 'kirki' ),
    'panel'          => 'cp_panel_footer',
]);

new \Kirki\Field\Text(
	[
	'settings' => 'cp_setting_footer_area_1_title',
	'label'    => esc_html__( 'Title', 'kirki' ),
	'section'  => 'cp_section_footer_area_1',
] );

new \Kirki\Field\Editor(
	[
	'settings'    => 'cp_setting_footer_area_1_content',
	'label'       => esc_html__( 'Content', 'kirki' ),
	'section'     => 'cp_section_footer_area_1',
	'default'     => '',
] );

new \Kirki\Section(
	'cp_section_footer_area_2', [
    'title'          => esc_html__( 'Footer Area 2', 'kirki' ),
    'panel'          => 'cp_panel_footer',
]);

new \Kirki\Field\Text(
	[
	'settings' => 'cp_setting_footer_area_2_title',
	'label'    => esc_html__( 'Title', 'kirki' ),
	'section'  => 'cp_section_footer_area_2',
] );

new \Kirki\Field\Editor(
	[
	'settings'    => 'cp_setting_footer_area_2_content',
	'label'       => esc_html__( 'Content', 'kirki' ),
	'section'     => 'cp_section_footer_area_2',
	'default'     => '',
] );

new \Kirki\Section(
	'cp_section_footer_disclaimer', [
    'title'          => esc_html__( 'Disclaimer', 'kirki' ),
    'panel'          => 'cp_panel_footer',
]);

new \Kirki\Field\Editor(
	[
	'settings'    => 'cp_setting_footer_disclaimer',
	'label'       => esc_html__( 'Disclaimer', 'kirki' ),
	'section'     => 'cp_section_footer_disclaimer',
	'default'     => '',
] );