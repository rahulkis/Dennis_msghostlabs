<?php

add_theme_support('woocommerce');


// update thumbnail sizes
add_filter( 'woocommerce_get_image_size_gallery_thumbnail', function( $size ) {
	return array(
		'width' => 300,
		'height' => 300,
		'crop' => 0,
	);
} );

add_action('woocommerce_before_main_content', 'cp_add_banner_to_shop_page');
function cp_add_banner_to_shop_page() {
	if(is_shop() || is_product_category() || is_product_tag()) {
		get_template_part('template-parts/partial', 'page-banner-shop');
	}
}


add_filter( 'woocommerce_breadcrumb_defaults', 'cp_woocommerce_breadcrumbs' );
function cp_woocommerce_breadcrumbs($defaults) {
	$defaults['wrap_before'] = '<nav class="woocommerce-breadcrumb"><div class="container">';
	$defaults['wrap_after'] = '</div></nav>';
	return $defaults;
}

add_action('woocommerce_before_single_product_summary', 'cp_open_wrap_on_product_detail', 1);
function cp_open_wrap_on_product_detail() {
	echo '<div class="product-detail"><div class="container clearfix">';
}

add_action('woocommerce_after_single_product_summary', 'cp_close_wrap_on_product_detail', 1);
function cp_close_wrap_on_product_detail() {
	echo '</div></div>';
}

add_action('woocommerce_after_single_product_summary', 'cp_open_wrap_on_product_detail_footer', 2);
function cp_open_wrap_on_product_detail_footer() {
	echo '<div class="product-detail-footer"><div class="container">';
}

add_action('woocommerce_after_single_product_summary', 'cp_close_wrap_on_product_detail_footer', 9999);
function cp_close_wrap_on_product_detail_footer() {
	echo '</div></div>';
}

function get_cart_count() {

	if(class_exists('WooCommerce')) {
		echo WC()->cart->get_cart_contents_count();
	} else {
		echo '';
	}

	wp_die();

}
add_action( 'wp_ajax_get_cart_count', 'get_cart_count' );
add_action( 'wp_ajax_nopriv_get_cart_count', 'get_cart_count' );

remove_action('woocommerce_sidebar', 'woocommerce_get_sidebar');

add_action('woocommerce_before_shop_loop', function() {
	echo '<div class="product-list-wrap"><div class="product-list">';
});
add_action('woocommerce_after_shop_loop', function() {
	echo '</div>';
	if ( true == get_theme_mod( 'cp_setting_shop_sidebar', true ) ) {
		echo '<div class="product-list-sidebar">';
	}
});

function cp_woocommerce_get_sidebar() {
	if ( true == get_theme_mod( 'cp_setting_shop_sidebar', true ) ) {
		echo '<button class="btn-default product-list-filter-toggle">Show Filters</button>';
		woocommerce_get_sidebar();
	}
}

add_action('woocommerce_after_shop_loop', 'cp_woocommerce_get_sidebar');

add_action('woocommerce_after_shop_loop', function() {
	echo '</div>';
	if ( true == get_theme_mod( 'cp_setting_shop_sidebar', true ) ) {
		echo '</div>';
	}
});

add_theme_support( 'wc-product-gallery-lightbox' );
add_theme_support( 'wc-product-gallery-slider' );

add_action('woocommerce_after_main_content', 'cp_add_page_builder_to_shop');
function cp_add_page_builder_to_shop() {
	get_template_part('template-parts/content', 'page-builder');
}

add_filter( 'wc_shipment_tracking_get_providers', 'custom_shipment_tracking' );

function custom_shipment_tracking( $providers ) {

    $us_providers = $providers['United States'];
    $providers = [];
    $providers['United States'] = $us_providers;

    unset($providers['United States']['FedEx Sameday']);
    unset($providers['United States']['OnTrac']);
    unset($providers['United States']['DHL US']);

    return $providers;
}

add_filter( 'woocommerce_shipment_tracking_default_provider', 'custom_woocommerce_shipment_tracking_default_provider' );
function custom_woocommerce_shipment_tracking_default_provider( $provider ) {
	$provider = 'USPS';
	return $provider;
}

// move store notice html to header
if(class_exists('WooCommerce')) {
	remove_action( 'wp_footer', 'woocommerce_demo_store' );
	add_action('cp_before_header_wrap', 'woocommerce_demo_store');
}


add_filter( 'wc_product_sku_enabled', 'cp_woocommerce_show_sku' );
function cp_woocommerce_show_sku() {

	if(is_admin()) {
		return true;
	}

	$show = get_theme_mod( 'cp_setting_product_detail_show_sku', true );
	return $show;
}

add_action( 'wp_head', 'cp_woocommerce_show_product_detail_categories' );
function cp_woocommerce_show_product_detail_categories() {
	$show = get_theme_mod( 'cp_setting_product_detail_show_categories', true );
	
	if(!$show) {
		echo '<style> .product_meta .posted_in {display: none !important;} </style>';
	}

}

add_action( 'wp_head', 'cp_woocommerce_show_product_detail_tags' );
function cp_woocommerce_show_product_detail_tags() {
	$show = get_theme_mod( 'cp_setting_product_detail_show_tags', true );
	
	if(!$show) {
		echo '<style> .product_meta .tagged_as {display: none !important;} </style>';
	}

}

// optionally hide tabs on product detail page
add_filter( 'woocommerce_product_tabs', 'cp_filter_product_detail_tabs', 9999 );
function cp_filter_product_detail_tabs( $tabs ) {

	$additional = get_theme_mod( 'cp_setting_product_detail_show_additional_info', true);

	if(!$additional) {
		unset( $tabs['additional_information'] );
	}

	return $tabs;
}

// wholesale tax exempt
function cp_zero_rate_for_custom_user_role( $tax_class, $product ) {
    // Getting the current user 
    $current_user = wp_get_current_user();
    if($current_user->ID) {
    	$current_user_data = get_userdata($current_user->ID);
    	if ( in_array( 'wholesale', $current_user_data->roles ) ) {
    	    $tax_class = 'Zero Rate';
    	}
    }

    return $tax_class;
}
add_filter( 'woocommerce_product_get_tax_class', 'cp_zero_rate_for_custom_user_role', 10, 2 );
add_filter( 'woocommerce_product_variation_get_tax_class', 'cp_zero_rate_for_custom_user_role', 10, 2 );

add_filter( 'woocommerce_email_styles', 'cp_adjust_woocomm_email_logo_width', 9999, 2 );
function cp_adjust_woocomm_email_logo_width( $css, $email ) { 
	$css .= '
	#template_header_image img { max-width:300px;margin-bottom:10px; }
	';
	return $css;
}