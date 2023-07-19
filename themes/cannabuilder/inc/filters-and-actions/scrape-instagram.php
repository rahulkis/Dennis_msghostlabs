<?php
/**
 * Function for loading more posts with ajax
 *
 *
 * @link https://github.com/usminteractive/usmms/wiki/Useful-Snippets#load-more-posts-with-ajax
 *
 * @package CannaBuilder
 */

function cp_scrape_instagram() {

	$username = $_POST['username'];

	// change this name to clear the transient cache
	$transient_name = 'cp_instagram_feed';

	// expiration time
	$transient_expiration = 3 * HOUR_IN_SECONDS;

	// Do we have this information in our transients already?
	$transient = get_transient( $transient_name );
	
	// Yep! Just return it and we're done.
	if( ! empty( $transient ) ) {
	
		// The function will return here every time after the first time it is run, until the transient expires.
		wp_send_json_success($transient);

	// Nope!    We gotta make a call.
	} else {

		$insta_source = file_get_contents('http://instagram.com/'.$username);
		$shards = explode('window._sharedData = ', $insta_source);
		$insta_json = explode(';</script>', $shards[1]); 
		$insta_array = json_decode($insta_json[0], TRUE);

		set_transient( $transient_name, $insta_array, $transient_expiration );

		wp_send_json_success( $insta_array );
	}
	
}
add_action( 'wp_ajax_cp_scrape_instagram', 'cp_scrape_instagram' );
add_action( 'wp_ajax_nopriv_cp_scrape_instagram', 'cp_scrape_instagram' );