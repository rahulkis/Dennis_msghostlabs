<?php

/**
 * Add the "transparent-header" class to specific pages
 */

add_filter( 'body_class', 'custom_class' );
function custom_class( $classes ) {
	// set up our page ID
	global $post;
	$page_id = $post->ID;

	// change page_id if we are on the blog page
	if ( is_home() ) {
		$page_id = get_option('page_for_posts');
	}

	$header_background = get_theme_mod( 'cp_setting_header_background', 'solid' );

	// add global class if transparent is on
	if($header_background == 'transparent') {
		$classes[] = 'transparent-header-enabled';
	}

	// conditionals for pages with no banner
	if( class_exists('WooCommerce') ) {
		if(is_shop() || is_product() || is_product_category() || is_product_tag() || is_tax('product_brand')) {
			$header_background = 'solid';
		}
	}

	// check for tribe events pages
	if( class_exists('Tribe__Events__Main') ) {
		if(tribe_is_event_query()) {
			$header_background = 'solid';
		}
	}

	// no transparent header for posts
	if(is_single()) {
		$header_background = 'solid';
	}

	// no transparent header for brands
	if(is_singular('cp_brand')) {
		$header_background = 'solid';
	}

	// banner disabled
	$disabled = get_field('cp_banner_disabled', $page_id);
	if ($disabled) {
		$header_background = 'solid';
	}

	$classes[] = $header_background . '-header';
    
    return $classes;
}
