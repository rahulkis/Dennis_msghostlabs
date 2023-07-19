<?php
/**
 * Plugin Name: CannaPlanners - Pop-ups
 * Description: Adds a basic pop-up builder to the site.
 * Version: 1.0.0
 * Author: CannaPlanners
 * Author URI: https://cannaplanners.com
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include 'cp-popups-post-type.php';

add_action('wp_footer', 'cp_add_popups_to_footer');
function cp_add_popups_to_footer() {

	$args = [
		'post_type' => 'pop-up',
		'numberposts' => -1
	];

	$the_query = new WP_Query( $args );
 
	if ( $the_query->have_posts() ) {
		while ( $the_query->have_posts() ) {
			$the_query->the_post();
			include 'cp-popup-template.php';
		}
	}
	wp_reset_postdata();

}