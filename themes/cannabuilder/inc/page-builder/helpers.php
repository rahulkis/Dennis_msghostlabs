<?php

/**
 * Hide default editor on pages
 */
add_action('init', 'my_rem_editor_from_post_type');
function my_rem_editor_from_post_type() {
    remove_post_type_support( 'page', 'editor' );
}

function cp_get_page_id() {
	global $post;

	if($post) {
		$page_id = $post->ID;
	}

	if(is_home()) {
		$page_id = get_option( 'page_for_posts' );
	}

	if(class_exists('WooCommerce')) {

		if(is_shop()) {
			$page_id = get_option('woocommerce_shop_page_id');
		}

		if(is_product_category() || is_product_tag()) {
			$page_id = get_queried_object();
		}
		
	}

	return $page_id;
}

/**
 * Add attributes to the ACF oEmbed iFrame
 */

function add_iframe_attrs($iframe) {
	
	// use preg_match to find iframe src
	preg_match('/src="(.+?)"/', $iframe, $matches);
	$src = $matches[1];

	// check if YouTube
	if (strpos($iframe, 'youtube') !== false) {
		$params = array(
			'autoplay'	=> 1,
			'rel' 		=> 0
		);
		$attributes = 'data-embed="youtube"';
		$iframe = str_replace('></iframe>', ' ' . $attributes . '></iframe>', $iframe);
	}

	// check if Vimeo
	if (strpos($iframe, 'vimeo') !== false) {
		$params = array(
			'autoplay'	=> 1,
		);
		$attributes = 'data-embed="vimeo"';
		$iframe = str_replace('></iframe>', ' ' . $attributes . '></iframe>', $iframe);
	}

	$new_src = add_query_arg($params, $src);

	$iframe = str_replace($src, $new_src, $iframe);

	return $iframe;
}



/**
 * Add the Title field to the page builder edit screen
 */

function cp_add_module_title_preview( $title, $field, $layout, $i ) {

	$text = get_sub_field('cp_module_part_title');

	if($layout['name'] == 'cp_module_testimonial') {
		$text = get_sub_field('cp_module_part_author_name');
	}

	if($text) {
		$title = '<strong>' . $title . ':</strong> '.$text;
	} else {
		$title = '<strong>' . $title . '</strong>';
	}
	
	// return
	return $title;
	
}

// name
add_filter('acf/fields/flexible_content/layout_title/name=cp_page_builder', 'cp_add_module_title_preview', 10, 4);



/**
 * Set Google Maps API key for backend map
 * @return [type] [description]
 */
function cannabuilder_acf_init() {
	
	acf_update_setting('google_api_key', 'AIzaSyDgkBBMgwj1a0FgJH69ALJcLel6gcv_h8s');
}

add_action('acf/init', 'cannabuilder_acf_init');