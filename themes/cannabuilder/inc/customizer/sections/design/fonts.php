<?php

new \Kirki\Section(
	'cp_section_fonts', [
    'title'          => esc_html__( 'Fonts', 'kirki' ),
    'panel'          => 'cp_panel_design',
]);

new \Kirki\Field\Typography(
	[
	'settings'    => 'cp_setting_heading_font',
	'label'       => esc_html__( 'Heading Font', 'kirki' ),
	'section'     => 'cp_section_fonts',
	'default'     => [
		'font-family'    => 'Lato',
		'variant'        => 'regular',
		'text-transform' => 'none',
		'line-height' => '1.2',
		'color' => '',
		'letter-spacing' => '',
	],
	'priority'    => 10,
	'transport'   => 'auto',
	'output'      => [
		[
			'element' => 'h1,h2,h3,h4,h5,h6,.accordion__header,.module-tabs .tabs-nav .tabs-nav-item-link,.wcpf-field-color-list .wcpf-heading-label, .wcpf-field-text-list .wcpf-heading-label, .wcpf-field-box-list .wcpf-heading-label, .wcpf-field-checkbox-list .wcpf-heading-label, .wcpf-field-radio-list .wcpf-heading-label, .wcpf-field-drop-down-list .wcpf-heading-label, .wcpf-field-price-slider .wcpf-heading-label, .cart_totals th, .woocommerce form .form-row label, .gfield_label, .gmw-form-wrapper .gmw-form-field-wrapper label, .shop_table tbody tr td:first-child, table:not(.gfield_list) tfoot th, table:not(.gfield_list) thead th',
		],
	],
] );

new \Kirki\Field\Typography(
	[
	'settings'    => 'cp_setting_paragraph_font',
	'label'       => esc_html__( 'Paragraph Font', 'kirki' ),
	'section'     => 'cp_section_fonts',
	'default'     => [
		'font-family'    => 'Arvo',
		'variant'        => 'regular',
		'color' => '',
		'letter-spacing' => ''
	],
	'priority'    => 20,
	'transport'   => 'auto',
	'output'      => [
		[
			'element' => 'body,p,ol,ul,figcaption,select,.woocommerce-breadcrumb,.product_meta,#review_form,.module-part-pretitle,.wcpf-field-checkbox-list .wcpf-checkbox-list .wcpf-checkbox-item .wcpf-checkbox-label, input[type="text"], input[type="email"], input[type="url"], input[type="tel"], input[type="number"], input[type="password"], input[type="search"], textarea, .gmw-form-wrapper input[type=text], .gmw-form-wrapper input[type=number], .gmw-form-wrapper select, .gmw-form-wrapper textarea',
		],
	],
] );

new \Kirki\Field\Typography(
	[
	'settings'    => 'cp_setting_nav_font',
	'label'       => esc_html__( 'Navigation Font', 'kirki' ),
	'section'     => 'cp_section_fonts',
	'default'     => [
		'font-family'    => 'Lato',
		'variant'        => 'regular',
		'text-transform' => 'uppercase',
		'font-size'		 => '14px'
	],
	'priority'    => 30,
	'transport'   => 'auto',
	'output'      => [
		[
			'element' => '.site-nav-menu>li>a, .site-nav-menu>li>ul>li>a',
		],
	],
] );

new \Kirki\Field\Typography(
	[
	'settings'    => 'cp_setting_button_font',
	'label'       => esc_html__( 'Button Font', 'kirki' ),
	'section'     => 'cp_section_fonts',
	'default'     => [
		'font-family'    => 'Lato',
		'variant'        => 'regular',
		'text-transform' => 'uppercase',
		'font-size' => '14px'
	],
	'priority'    => 30,
	'transport'   => 'auto',
	'output'      => [
		[
			'element' => 'button,.btn-primary, div.gmw-form-wrapper .gmw-submit, div.gmw-form-wrapper input[type=submit], #add_payment_method .wc-proceed-to-checkout a.checkout-button, .woocommerce-cart .wc-proceed-to-checkout a.checkout-button, .woocommerce-checkout .wc-proceed-to-checkout a.checkout-button, .gform_button, .gform_page_footer .gform_next_button, .gform_page_footer .gform_previous_button, .gform_page_footer .gform_button, .btn-primary-outline, .btn-accent-1, .gform_fileupload_multifile .gform_drop_area .gform_button_select_files, .btn-accent-2, .btn-accent-3, .btn-accent-4, .btn-white, .btn-white-outline, .btn-black-ghost, .woocommerce .products ul li.product .button, .woocommerce ul.products li.product .button, .wcpf-field-button .wcpf-button,#wc_gc_cart_redeem_send,.woocommerce #respond input#submit, .woocommerce a.button, .woocommerce button.button, .woocommerce input.button',
		],
	],
] );

// h1 font sizes

new \Kirki\Field\Number(
	[
	'settings'    => 'cp_font_size_h1_mobile',
	'label'       => esc_html__( 'H1 Size Mobile', 'kirki' ),
	'section'     => 'cp_section_fonts',
	'priority'    => 40,
	'default'     => 38,
	'output'      => [
		[
			'element' => 'h1, .page-banner-title',
			'property' => 'font-size',
			'units' => 'px'
		],
	],
] );

new \Kirki\Field\Number(
	[
	'settings'    => 'cp_font_size_h1_desktop',
	'label'       => esc_html__( 'H1 Size Desktop', 'kirki' ),
	'section'     => 'cp_section_fonts',
	'priority'    => 40,
	'default'     => 72,
	'output'      => [
		[
			'element' => 'h1, .page-banner-title',
			'property' => 'font-size',
			'units' => 'px',
			'media_query' => '@media (min-width: 992px)'
		],
	],
] );

// h2 font sizes

new \Kirki\Field\Number(
	[
	'settings'    => 'cp_font_size_h2_mobile',
	'label'       => esc_html__( 'H2 Size Mobile', 'kirki' ),
	'section'     => 'cp_section_fonts',
	'priority'    => 40,
	'default'     => 22,
	'output'      => [
		[
			'element' => 'h2, .module-part-title, .module h1, h1.product_title',
			'property' => 'font-size',
			'units' => 'px'
		],
	],
] );

new \Kirki\Field\Number(
	[
	'settings'    => 'cp_font_size_h2_desktop',
	'label'       => esc_html__( 'H2 Size Desktop', 'kirki' ),
	'section'     => 'cp_section_fonts',
	'priority'    => 40,
	'default'     => 52,
	'output'      => [
		[
			'element' => 'h2, .module-part-title, .module h1, h1.product_title',
			'property' => 'font-size',
			'units' => 'px',
			'media_query' => '@media (min-width: 992px)'
		],
	],
] );

// h3 font sizes

new \Kirki\Field\Number(
	[
	'settings'    => 'cp_font_size_h3_mobile',
	'label'       => esc_html__( 'H3 Size Mobile', 'kirki' ),
	'section'     => 'cp_section_fonts',
	'priority'    => 40,
	'default'     => 18,
	'output'      => [
		[
			'element' => 'h3, .woocommerce ul.products li.product .woocommerce-loop-category__title, .woocommerce ul.products li.product .woocommerce-loop-product__title, .woocommerce ul.products li.product h3, .wc-tab h2, .related.products h2',
			'property' => 'font-size',
			'units' => 'px'
		],
	],
] );

new \Kirki\Field\Number(
	[
	'settings'    => 'cp_font_size_h3_desktop',
	'label'       => esc_html__( 'H3 Size Desktop', 'kirki' ),
	'section'     => 'cp_section_fonts',
	'priority'    => 40,
	'default'     => 22,
	'output'      => [
		[
			'element' => 'h3, .woocommerce ul.products li.product .woocommerce-loop-category__title, .woocommerce ul.products li.product .woocommerce-loop-product__title, .woocommerce ul.products li.product h3, .wc-tab h2, .related.products h2',
			'property' => 'font-size',
			'units' => 'px',
			'media_query' => '@media (min-width: 992px)'
		],
	],
] );

// h4 font sizes

new \Kirki\Field\Number(
	[
	'settings'    => 'cp_font_size_h4_mobile',
	'label'       => esc_html__( 'H4 Size Mobile', 'kirki' ),
	'section'     => 'cp_section_fonts',
	'priority'    => 40,
	'default'     => 16,
	'output'      => [
		[
			'element' => 'h4',
			'property' => 'font-size',
			'units' => 'px'
		],
	],
] );

new \Kirki\Field\Number(
	[
	'settings'    => 'cp_font_size_h4_desktop',
	'label'       => esc_html__( 'H4 Size Desktop', 'kirki' ),
	'section'     => 'cp_section_fonts',
	'priority'    => 40,
	'default'     => 18,
	'output'      => [
		[
			'element' => 'h4',
			'property' => 'font-size',
			'units' => 'px',
			'media_query' => '@media (min-width: 992px)'
		],
	],
] );