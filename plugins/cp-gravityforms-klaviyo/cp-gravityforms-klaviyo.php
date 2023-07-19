<?php
/*
Plugin Name: CannaPlanners Gravity Forms Klaviyo Add-On
Description: Integrates Gravity Forms with Klaviyo
Version: 1.0
Author: CannaPlanners
Author URI: https://cannaplanners.com
*/

require_once('includes/Klaviyo.php');

define( 'GF_KLAVIYO_API_VERSION', '2.0' );

add_action( 'gform_loaded', array( 'GF_KLAVIYO_API', 'load' ), 5 );

class GF_KLAVIYO_API {
	public static function load() {
		if ( ! method_exists( 'GFForms', 'include_feed_addon_framework' ) ) {
			return;
		}

		require_once( 'class-gfklaviyofeedaddon.php' );
		GFAddOn::register( 'GFKlaviyoAPI' );
	}
}

function gf_klaviyo_api_feed() {
	return GFKlaviyoAPI::get_instance();
}