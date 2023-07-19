<?php

new \Kirki\Section(
	'cp_section_images', [
    'title'          => esc_html__( 'Images', 'kirki' ),
    'panel'          => 'cp_panel_design',
]);

new \Kirki\Field\Number(
	[
	'settings'    => 'cp_setting_image_border_radius',
	'label'       => esc_html__( 'Button Border Radius', 'kirki' ),
    'description' => esc_html__( 'Set the image border radius in pixels', 'kirki' ),
	'section'     => 'cp_section_images',
	'default'     => 0,
	'output'      => [
		[
			'element' => '.module-50-50.module-setting-width-contained .module-part-image img, .cta, .module-video .container .module-part-content, .column-image img, .gallery-image img, .module-gallery .gallery-image a:after',
			'property' => 'border-radius',
			'units' => 'px'
		],
	],
] );