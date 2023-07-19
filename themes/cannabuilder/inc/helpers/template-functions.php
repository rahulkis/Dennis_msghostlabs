<?php

/**
 * Get asset version based on last saved timestamp
 *
 * @param string $file_name
 * @param string $type
 * @return string
 */
function cannabuilder_asset_version($file_name, $type = 'js') {
	$paths = [
		'js' => get_template_directory() . '/dist/js/',
		'css' => get_template_directory() . '/dist/css/'
	];

	if (array_key_exists($type, $paths) && file_exists($paths[$type] . $file_name)) {
		return filemtime($paths[$type] . $file_name);
	} else {
		return '1';
	}
}


/**
 * Get asset path
 *
 * @param string $file_name
 * @param string $type
 * @return string
 */
function cannabuilder_asset_path($file_name, $type = 'js') {
	$paths = [
		'js' => get_template_directory_uri() . '/dist/js/',
		'css' => get_template_directory_uri() . '/dist/css/'
	];

	if (array_key_exists($type, $paths)) {
		return $paths[$type] . $file_name;
	} else {
		return false;
	}
}


/**
 * Create slug from string
 * @param  string $text string to slugify
 * @return string slugified string
 */
function cannabuilder_slugify($text) {
	// replace non letter or digits by -
	$text = preg_replace('~[^\pL\d]+~u', '-', $text);

	// transliterate
	$text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

	// remove unwanted characters
	$text = preg_replace('~[^-\w]+~', '', $text);

	// remove apostrophes
	$text = str_replace("’", '', $text);
	$text = str_replace("'", '', $text);
	$text = str_replace('"', '', $text);
	$text = str_replace('-8217-', '', $text);

	// trim
	$text = trim($text, '-');

	// remove duplicate -
	$text = preg_replace('~-+~', '-', $text);

	// lowercase
	$text = strtolower($text);

	if (empty($text)) {
		return 'n-a';
	}

	return $text;
}


function cp_format_image_position($horizontal, $vertical) {

	if(is_null($horizontal)) {
		$horizontal = '50%';
	}

	if(is_null($vertical)) {
		$vertical = '50%';
	}

	$horizontal = str_replace('%', '', $horizontal);
	$vertical = str_replace('%', '', $vertical);

	return 'position-' . $horizontal . '-' . $vertical;
	
}


function limit_text($text, $limit) {
	if (str_word_count($text, 0) > $limit) {
		$words = str_word_count($text, 2);
		$pos = array_keys($words);
		$text = substr($text, 0, $pos[$limit]) . '...';
	}
	return $text;
}


/**
 * Adds custom classes to the array of body classes.
 *
 * @param array $classes Classes for the body element.
 * @return array
 */
function cannabuilder_body_classes( $classes ) {
	// Adds a class of hfeed to non-singular pages.
	if ( ! is_singular() ) {
		$classes[] = 'hfeed';
	}

	return $classes;
}
add_filter( 'body_class', 'cannabuilder_body_classes' );


/**
 * Add a pingback url auto-discovery header for singularly identifiable articles.
 */
function cannabuilder_pingback_header() {
	if ( is_singular() && pings_open() ) {
		echo '<link rel="pingback" href="', esc_url( get_bloginfo( 'pingback_url' ) ), '">';
	}
}
add_action( 'wp_head', 'cannabuilder_pingback_header' );


/**
 * Converts text surrounded by double asterisks (**text**) into underlined text
 * Converts pipe characters into line breaks
 */
function cannabuilder_format_text_fields( $value, $post_id, $field ) {
	$value = preg_replace('#\*{2}(.*?)\*{2}#', '<u>$1</u>', $value);
	$value = str_replace(' | ', ' <br>', $value);
	return $value;
}

add_filter('acf/format_value/type=text', 'cannabuilder_format_text_fields', 10, 3);


function get_excerpt_trim($num_words='20', $more='...'){
	$excerpt = get_the_excerpt();
	$excerpt = wp_trim_words( $excerpt, $num_words , $more );
	return $excerpt;
}

/**
 * Shortcodes for the Policies page
 * uses values from the Policy Settings section of the customizer
 */
function cp_shortcode_client_name() { 
	return get_theme_mod( 'cp_setting_business_name', get_bloginfo('name') );
}
add_shortcode('cp_client_name', 'cp_shortcode_client_name');

function cp_shortcode_client_url() { 
	return get_theme_mod( 'cp_setting_business_url', get_site_url() );
}
add_shortcode('cp_client_url', 'cp_shortcode_client_url');

function cp_shortcode_client_email() { 
	return get_theme_mod( 'cp_setting_business_email', 'BUSINESS EMAIL' );
}
add_shortcode('cp_client_email', 'cp_shortcode_client_email');

function cp_shortcode_client_phone() { 
	return get_theme_mod( 'cp_setting_business_phone', 'BUSINESS PHONE' );
}
add_shortcode('cp_client_phone', 'cp_shortcode_client_phone');