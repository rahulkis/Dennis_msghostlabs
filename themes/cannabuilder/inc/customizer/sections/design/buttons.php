<?php

new \Kirki\Section(
	'cp_section_buttons', [
    'title'          => esc_html__( 'Buttons', 'kirki' ),
    'panel'          => 'cp_panel_design',
]);

new \Kirki\Field\Number(
	[
	'settings'    => 'cp_setting_button_border_radius',
	'label'       => esc_html__( 'Button Border Radius', 'kirki' ),
	'section'     => 'cp_section_buttons',
	'default'     => 39,
	'output'      => [
		[
			'element' => '#add_payment_method .wc-proceed-to-checkout a.checkout-button, .age-gate-submit-no, .age-gate-submit-yes, .btn-accent-1, .btn-accent-2, .btn-accent-3, .btn-accent-4, .btn-black-ghost, .btn-primary, .btn-primary-outline, .btn-white, .btn-white-outline, .gform_button, .gform_fileupload_multifile .gform_drop_area .gform_button_select_files, .gform_page_footer .gform_button, .gform_page_footer .gform_next_button, .gform_page_footer .gform_previous_button, .post-password-form input[type=submit], .woocommerce-cart .wc-proceed-to-checkout a.checkout-button, .woocommerce-checkout .wc-proceed-to-checkout a.checkout-button, .woocommerce .products ul li.product .button, .woocommerce ul.products li.product .button, div.gmw-form-wrapper .gmw-submit, div.gmw-form-wrapper input[type=submit]',
			'property' => 'border-radius',
			'units' => 'px'
		],
	],
] );