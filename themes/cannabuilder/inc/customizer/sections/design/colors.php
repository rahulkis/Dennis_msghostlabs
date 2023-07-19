<?php

new \Kirki\Section(
	'cp_section_colors', [
    'title'          => esc_html__( 'Colors', 'kirki' ),
    'panel'          => 'cp_panel_design',
]);

new \Kirki\Field\Color(
	[
	'settings'    => 'cp_setting_primary_color',
	'label'       => __( 'Primary Color', 'kirki' ),
	'section'     => 'cp_section_colors',
	'default'     => '',
	'choices'     => [
		'alpha' => true,
	],
	'output'      => [
		[
			'element'  => 'body .woocommerce-store-notice, .site-notice, body p.demo_store, .site-nav-cart-total, .module.module-setting-bg-brand, .btn-play:hover, .btn-play:active, .btn-play:focus, .module-cta-grid .cta:hover:after, .module-cta-grid .cta:active:after, .module-cta-grid .cta:focus:after, .btn-primary, div.gmw-form-wrapper .gmw-submit, div.gmw-form-wrapper input[type=submit], #add_payment_method .wc-proceed-to-checkout a.checkout-button, .woocommerce-cart .wc-proceed-to-checkout a.checkout-button, .woocommerce-checkout .wc-proceed-to-checkout a.checkout-button, .gform_button, .gform_page_footer .gform_next_button, .gform_page_footer .gform_previous_button, .gform_page_footer .gform_button, .woocommerce #respond input#submit.alt, .woocommerce a.button.alt, .woocommerce button.button.alt, .woocommerce input.button.alt, .woocommerce #respond input#submit.alt.disabled, .woocommerce #respond input#submit.alt.disabled:hover, .woocommerce #respond input#submit.alt:disabled, .woocommerce #respond input#submit.alt:disabled:hover, .woocommerce #respond input#submit.alt:disabled[disabled], .woocommerce #respond input#submit.alt:disabled[disabled]:hover, .woocommerce a.button.alt.disabled, .woocommerce a.button.alt.disabled:hover, .woocommerce a.button.alt:disabled, .woocommerce a.button.alt:disabled:hover, .woocommerce a.button.alt:disabled[disabled], .woocommerce a.button.alt:disabled[disabled]:hover, .woocommerce button.button.alt.disabled, .woocommerce button.button.alt.disabled:hover, .woocommerce button.button.alt:disabled, .woocommerce button.button.alt:disabled:hover, .woocommerce button.button.alt:disabled[disabled], .woocommerce button.button.alt:disabled[disabled]:hover, .woocommerce input.button.alt.disabled, .woocommerce input.button.alt.disabled:hover, .woocommerce input.button.alt:disabled, .woocommerce input.button.alt:disabled:hover, .woocommerce input.button.alt:disabled[disabled], .woocommerce input.button.alt:disabled[disabled]:hover, .pagination .nav-links a.current, .pagination .nav-links span.current, .woocommerce nav.woocommerce-pagination ul.page-numbers li a.current, .woocommerce nav.woocommerce-pagination ul.page-numbers li span.current, .post-password-form input[type=submit], .age-gate-submit-yes, .module-testimonial-slider .swiper-horizontal > .swiper-pagination-bullets .swiper-pagination-bullet-active, .module-testimonial-slider .swiper-pagination-bullets.swiper-pagination-horizontal .swiper-pagination-bullet-active, .module-testimonial-slider .swiper-pagination-custom .swiper-pagination-bullet-active, .module-testimonial-slider .swiper-pagination-fraction .swiper-pagination-bullet-active, #wc_gc_cart_redeem_send, .button-primary',
			'property' => 'background-color',
		],
		[
			'element' => '.btn-play svg path',
			'property' => 'fill'
		],
		[
			'element' => '.module-tabs .tabs-nav .tabs-nav-item.active',
			'property' => 'border-color',
			'media_query' => '@media (min-width: 992px)'
		],
		[
			'element' => '.btn-primary-outline, .age-gate-submit-no, .wcpf-field-checkbox-list .wcpf-checkbox-list .wcpf-checkbox-item.checked>.wcpf-checkbox-item-inner>.wcpf-checkbox-label .wcpf-input-container:after',
			'property' => 'border-color'
		],
		[
			'element' => '.site-nav-menu > li > a:hover, .site-nav-menu > li > a:active, .site-nav-menu > li > a:focus, .transparent-header:not(.is-scrolled) .site-nav-menu>li>a:active, .transparent-header:not(.is-scrolled) .site-nav-menu>li>a:focus, .transparent-header:not(.is-scrolled) .site-nav-menu>li>a:hover',
			'property' => 'color',
			'media_query' => '@media (min-width: 1100px)'
		],
		[
			'element' => '.nav-dark .site-nav-menu>li>a:active, .nav-dark .site-nav-menu>li>a:focus, .nav-dark .site-nav-menu>li>a:hover',
			'property' => 'color',
			'media_query' => '@media (max-width: 1099px)'
		],
		[
			'element' => 'a, .accordion__header:hover h3, .accordion__header:active h3, .accordion__header:focus h3, .module-team .module-part-team .team-card:hover h3, .module-team .module-part-team .team-card:active h3, .module-team .module-part-team .team-card:focus h3, .post-card:hover .post-card-title, .post-card:active .post-card-title, .post-card:focus .post-card-title, .site-nav-menu > li:hover > a, .site-nav-menu > li:active > a, .site-nav-menu > li:focus > a, .woocommerce .products ul li.product .woocommerce-loop-product__link:hover h2, .woocommerce .products ul li.product .woocommerce-loop-product__link:active h2, .woocommerce .products ul li.product .woocommerce-loop-product__link:focus h2, .woocommerce ul.products li.product .woocommerce-loop-product__link:hover h2, .woocommerce ul.products li.product .woocommerce-loop-product__link:active h2, .woocommerce ul.products li.product .woocommerce-loop-product__link:focus h2, .woocommerce-MyAccount-navigation ul .is-active a, .woocommerce-MyAccount-navigation ul a:hover, .woocommerce-MyAccount-navigation ul a:active, .woocommerce-MyAccount-navigation ul a:focus, .age-gate-submit-no, .accordion__header[aria-expanded=true], .btn-white:hover, .btn-white:active, .btn-white:focus, .module.module-setting-bg-dark .gform_button:hover, .module.module-setting-bg-dark .gform_button:active, .module.module-setting-bg-dark .gform_button:focus, .wc-block-product-categories-list li a:hover, .wc-block-product-categories-list li a:active, .wc-block-product-categories-list li a:focus',
			'property' => 'color'
		]
	]
] );

new \Kirki\Field\Color(
	[
	'settings'    => 'cp_setting_primary_hover_color',
	'label'       => __( 'Primary Hover Color', 'kirki' ),
	'section'     => 'cp_section_colors',
	'default'     => '',
	'choices'     => [
		'alpha' => true,
	],
	'output'      => [
		[
			'element'  => '.btn-primary:hover, div.gmw-form-wrapper .gmw-submit:hover, div.gmw-form-wrapper input:hover[type=submit], #add_payment_method .wc-proceed-to-checkout a.checkout-button:hover, .woocommerce-cart .wc-proceed-to-checkout a.checkout-button:hover, .woocommerce-checkout .wc-proceed-to-checkout a.checkout-button:hover, .gform_button:hover, .gform_page_footer .gform_next_button:hover, .gform_page_footer .gform_previous_button:hover, .btn-primary:active, div.gmw-form-wrapper .gmw-submit:active, div.gmw-form-wrapper input:active[type=submit], #add_payment_method .wc-proceed-to-checkout a.checkout-button:active, .woocommerce-cart .wc-proceed-to-checkout a.checkout-button:active, .woocommerce-checkout .wc-proceed-to-checkout a.checkout-button:active, .gform_button:active, .gform_page_footer .gform_next_button:active, .gform_page_footer .gform_previous_button:active, .btn-primary:focus, div.gmw-form-wrapper .gmw-submit:focus, div.gmw-form-wrapper input:focus[type=submit], #add_payment_method .wc-proceed-to-checkout a.checkout-button:focus, .woocommerce-cart .wc-proceed-to-checkout a.checkout-button:focus, .woocommerce-checkout .wc-proceed-to-checkout a.checkout-button:focus, .gform_button:focus, .gform_page_footer .gform_next_button:focus, .gform_page_footer .gform_previous_button:focus, .woocommerce #respond input#submit.alt:hover, .woocommerce #respond input#submit.alt:active, .woocommerce #respond input#submit.alt:focus, .woocommerce a.button.alt:hover, .woocommerce a.button.alt:active, .woocommerce a.button.alt:focus, .woocommerce button.button.alt:hover, .woocommerce button.button.alt:active, .woocommerce button.button.alt:focus, .woocommerce input.button.alt:hover, .woocommerce input.button.alt:active, .woocommerce input.button.alt:focus, .post-password-form input[type=submit]:active, .post-password-form input[type=submit]:hover, .post-password-form input[type=submit]:focus, .age-gate-submit-yes:active, .age-gate-submit-yes:hover, .age-gate-submit-yes:focus, .age-gate-submit-no:active, .age-gate-submit-no:hover, .age-gate-submit-no:focus, #wc_gc_cart_redeem_send:hover, #wc_gc_cart_redeem_send:active, #wc_gc_cart_redeem_send:focus, .button-primary:active, .button-primary:hover, .button-primary:focus',
			'property' => 'background-color',
		],
		[
			'element' => 'a:hover, a:active, a:focus',
			'property' => 'color'
		],
		[
			'element' => '.btn-primary-outline:hover, .btn-primary-outline:active, .btn-primary-outline:focus',
			'property' => 'background-color'
		],
		[
			'element' => '.btn-primary-outline:hover, .btn-primary-outline:active, .btn-primary-outline:focus, .age-gate-submit-no:active, .age-gate-submit-no:hover, .age-gate-submit-no:focus',
			'property' => 'border-color'
		]
	]
] );

new \Kirki\Field\Color(
	[
	'settings'    => 'cp_setting_black',
	'label'       => __( 'Black', 'kirki' ),
	'section'     => 'cp_section_colors',
	'default'     => '',
	'choices'     => [
		'alpha' => true,
	],
	'output'      => [
		[
			'element'  => '.site-footer, .site-attribution, .module.module-setting-bg-dark, .btn-black-ghost:active, .btn-black-ghost:focus, .btn-black-ghost:hover, .woocommerce .products ul li.product .button:active, .woocommerce .products ul li.product .button:focus, .woocommerce .products ul li.product .button:hover, .woocommerce ul.products li.product .button:active, .woocommerce ul.products li.product .button:focus, .woocommerce ul.products li.product .button:hover',
			'property' => 'background-color',
		],
		[
			'element'  => 'h1, h2, h3, h4, h5, h6, .module-part-pretitle, .module-featured-product.module-setting-bg-brand .module-part-content .woocommerce .star-rating, .accordion__header, .module-tabs .tabs-nav .tabs-nav-item.active a, .btn-black-ghost, .woocommerce .products ul li.product .button, .woocommerce ul.products li.product .button, .page-banner-image-off .page-banner-title, .page-banner-image-off .page-banner-content .page-banner-subtitle, .page-banner-image-off .page-banner-content .term-description p, .wc-block-product-categories-list li a',
			'property' => 'color',
		],
		[
			'element'  => '.btn-black-ghost, .woocommerce .products ul li.product .button, .woocommerce ul.products li.product .button',
			'property' => 'border-color',
		],
	]
] );

new \Kirki\Field\Color(
	[
	'settings'    => 'cp_setting_header_background_color',
	'label'       => __( 'Header Background Color', 'kirki' ),
	'section'     => 'cp_section_colors',
	'default'     => '#ffffff',
	'output'      => [
		[
			'element'  => '.site-header-wrap',
			'property' => 'background-color',
		]
	]
] );

new \Kirki\Field\Color(
	[
	'settings'    => 'cp_setting_nav_link_color',
	'label'       => __( 'Navigation Link Color', 'kirki' ),
	'section'     => 'cp_section_colors',
	'output'      => [
		[
			'element'  => '.site-nav-menu > li > a',
			'property' => 'color',
		],
		[
			'element'  => '.site-nav-actions path',
			'property' => 'fill',
		],
		[
			'element'  => '.site-nav-toggle-bar',
			'property' => 'background-color',
		],
	]
] );

new \Kirki\Field\Color(
	[
	'settings'    => 'cp_setting_nav_link_hover_color',
	'label'       => __( 'Navigation Link Hover Color', 'kirki' ),
	'section'     => 'cp_section_colors',
	'output'      => [
		[
			'element'  => '.site-nav-menu>li>a:active, .site-nav-menu>li>a:focus, .site-nav-menu>li>a:hover',
			'property' => 'color',
		],
		[
			'element' => '.site-nav-menu>li>a:active, .site-nav-menu>li>a:focus, .site-nav-menu>li>a:hover',
			'property' => 'color',
			'media_query' => '@media (min-width: 1100px)'
		],
		[
			'element'  => '.site-nav-actions a:active path, .site-nav-actions a:focus path, .site-nav-actions a:hover path, .site-nav-actions button:active path, .site-nav-actions button:focus path, .site-nav-actions button:hover path',
			'property' => 'fill',
		]
	]
] );

new \Kirki\Field\Color(
	[
	'settings'    => 'cp_setting_button_color',
	'label'       => __( 'Button Color', 'kirki' ),
	'section'     => 'cp_section_colors',
	'output'      => [
		[
			'element'  => '.btn-primary, .gform_button, div.gmw-form-wrapper .gmw-submit, div.gmw-form-wrapper input[type=submit]',
			'property' => 'color',
		]
	]
] );

new \Kirki\Field\Color(
	[
	'settings'    => 'cp_setting_button_hover_color',
	'label'       => __( 'Button Hover Color', 'kirki' ),
	'section'     => 'cp_section_colors',
	'output'      => [
		[
			'element'  => '.btn-primary:active, .btn-primary:focus, .btn-primary:hover, .gform_button:active, .gform_button:focus, .gform_button:hover, div.gmw-form-wrapper .gmw-submit, div.gmw-form-wrapper input[type=submit]:active, div.gmw-form-wrapper .gmw-submit, div.gmw-form-wrapper input[type=submit]:focus, div.gmw-form-wrapper .gmw-submit, div.gmw-form-wrapper input[type=submit]:hover',
			'property' => 'color',
		]
	]
] );

new \Kirki\Field\Color(
	[
	'settings'    => 'cp_setting_store_notice_background',
	'label'       => __( 'Store Notice Background', 'kirki' ),
	'description' => 'Defaults to the Primary Color',
	'section'     => 'cp_section_colors',
	'default'     => '',
	'choices'     => [
		'alpha' => true,
	],
	'output'      => [
		[
			'element'  => 'body .woocommerce-store-notice, body p.demo_store, .site-notice',
			'property' => 'background-color',
		]
	]
] );

new \Kirki\Field\Color(
	[
	'settings'    => 'cp_setting_store_notice_text',
	'label'       => __( 'Store Notice Text Color', 'kirki' ),
	'section'     => 'cp_section_colors',
	'default'     => '',
	'choices'     => [
		'alpha' => true,
	],
	'output'      => [
		[
			'element'  => 'body .woocommerce-store-notice, body p.demo_store, .woocommerce-store-notice a, p.demo_store a, .site-notice p, .site-notice p a',
			'property' => 'color',
		]
	]
] );

new \Kirki\Field\Color(
	[
	'settings'    => 'cp_setting_home_banner_overlay',
	'label'       => __( 'Home Banner Overlay', 'kirki' ),
	'section'     => 'cp_section_colors',
	'default'     => '',
	'choices'     => [
		'alpha' => true,
	],
	'output'      => [
		[
			'element'  => '.home .page-banner.cover:after',
			'property' => 'background-color',
		]
	]
] );

new \Kirki\Field\Color(
	[
	'settings'    => 'cp_setting_interior_banner_overlay',
	'label'       => __( 'Interior Banner Overlay', 'kirki' ),
	'section'     => 'cp_section_colors',
	'default'     => '',
	'choices'     => [
		'alpha' => true,
	],
	'output'      => [
		[
			'element'  => 'body:not(.home) .page-banner.cover:after',
			'property' => 'background-color',
		]
	]
] );

new \Kirki\Field\Color(
	[
	'settings'    => 'cp_setting_interior_banner_title_color',
	'label'       => __( 'Banner Title Color', 'kirki' ),
	'section'     => 'cp_section_colors',
	'default'     => '',
	'choices'     => [
		'alpha' => true,
	],
	'output'      => [
		[
			'element'  => '.page-banner .page-banner-title',
			'property' => 'color',
		]
	]
] );

new \Kirki\Field\Color(
	[
	'settings'    => 'cp_setting_interior_banner_subtitle_color',
	'label'       => __( 'Banner Subtitle Color', 'kirki' ),
	'section'     => 'cp_section_colors',
	'default'     => '',
	'choices'     => [
		'alpha' => true,
	],
	'output'      => [
		[
			'element'  => '.page-banner .page-banner-subtitle',
			'property' => 'color',
		]
	]
] );

new \Kirki\Field\Color(
	[
	'settings'    => 'cp_setting_home_banner_title_color',
	'label'       => __( 'Home Banner Title Color', 'kirki' ),
	'section'     => 'cp_section_colors',
	'default'     => '',
	'choices'     => [
		'alpha' => true,
	],
	'output'      => [
		[
			'element'  => '.home .page-banner .page-banner-title',
			'property' => 'color',
		]
	]
] );

new \Kirki\Field\Color(
	[
	'settings'    => 'cp_setting_home_banner_subtitle_color',
	'label'       => __( 'Home Banner Subtitle Color', 'kirki' ),
	'section'     => 'cp_section_colors',
	'default'     => '',
	'choices'     => [
		'alpha' => true,
	],
	'output'      => [
		[
			'element'  => '.home .page-banner .page-banner-subtitle',
			'property' => 'color',
		]
	]
] );

new \Kirki\Field\Color(
	[
	'settings'    => 'cp_setting_footer_background',
	'label'       => __( 'Footer Background', 'kirki' ),
	'description' => 'Defaults to the Black color',
	'section'     => 'cp_section_colors',
	'default'     => '',
	'choices'     => [
		'alpha' => true,
	],
	'output'      => [
		[
			'element'  => '.site-footer, .site-attribution',
			'property' => 'background-color',
		]
	]
] );

new \Kirki\Field\Color(
	[
	'settings'    => 'cp_setting_footer_border',
	'label'       => __( 'Footer Border Color', 'kirki' ),
	'section'     => 'cp_section_colors',
	'default'     => '',
	'choices'     => [
		'alpha' => true,
	],
	'output'      => [
		[
			'element'  => '.site-footer',
			'property' => 'border-color',
		]
	]
] );

new \Kirki\Field\Color(
	[
	'settings'    => 'cp_setting_footer_heading_color',
	'label'       => __( 'Footer Heading Color', 'kirki' ),
	'section'     => 'cp_section_colors',
	'default'     => '',
	'choices'     => [
		'alpha' => true,
	],
	'output'      => [
		[
			'element'  => '.site-footer .footer-item h4, .site-footer-menu li a:hover, .site-footer-menu li a:focus, .site-footer-menu li a:active',
			'property' => 'color',
		]
	]
] );

new \Kirki\Field\Color(
	[
	'settings'    => 'cp_setting_footer_paragraph_color',
	'label'       => __( 'Footer Paragraph Color', 'kirki' ),
	'section'     => 'cp_section_colors',
	'default'     => '',
	'choices'     => [
		'alpha' => true,
	],
	'output'      => [
		[
			'element'  => '.site-footer .footer-item p, .site-footer .footer-item p a, .site-footer .footer-item ul a, .site-attribution p, .site-footer-menu li a, .site-footer-disclaimer p a, .site-policies-menu li a',
			'property' => 'color',
		],
		[
			'element' => '.footer-social-links li a',
			'property' => 'border-color'
		],
		[
			'element' => '.footer-social-links li a path',
			'property' => 'fill'
		]
	]
] );

new \Kirki\Field\Color(
	[
	'settings'    => 'cp_setting_footer_logo_color',
	'label'       => __( 'CP Logo Color', 'kirki' ),
	'section'     => 'cp_section_colors',
	'default'     => '',
	'choices'     => [
		'alpha' => true,
	],
	'output'      => [
		[
			'element' => '.site-by svg path',
			'property' => 'fill'
		]
	]
] );