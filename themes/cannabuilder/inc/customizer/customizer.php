<?php

if(class_exists('Kirki')) {

	new \Kirki\Panel(
		'cp_panel_design',
		[
			'priority'    => 10,
			'title'       => esc_html__( 'Design Settings', 'kirki' ),
		]
	);

	require 'sections/design/header.php';
	require 'sections/design/logo.php';
	require 'sections/design/colors.php';
	require 'sections/design/fonts.php';
	require 'sections/design/buttons.php';
	require 'sections/design/images.php';
	require 'sections/design/banners.php';
	require 'sections/design/blog.php';
	require 'sections/shop.php';
	require 'sections/policies.php';
	require 'sections/coming-soon.php';

	new \Kirki\Section(
		'cp_section_site_notice', [
		'title'          => esc_html__( 'Site Notice', 'kirki' ),
		'priority'    => 10
	]);

	require 'sections/site-notice.php';

	new \Kirki\Panel(
		'cp_panel_footer',
		[
			'priority'    => 10,
			'title'       => esc_html__( 'Footer Settings', 'kirki' ),
		]
	);

	require 'sections/footer.php';
}