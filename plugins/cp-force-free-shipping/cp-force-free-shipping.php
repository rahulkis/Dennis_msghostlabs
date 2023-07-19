<?php
/*
Plugin Name: CannaPlanners Force Free Shipping
Description: This plugin forces free shipping when it is available
Version: 1.0.0
Author: CannaPlanners
Author URI: https://cannaplanners.com
*/

/**
 * Hide shipping rates when free shipping is available.
 * Updated to support WooCommerce 2.6 Shipping Zones.
 *
 * @param array $rates Array of rates found for the package.
 * @return array
 */
function cp_hide_shipping_when_free_is_available( $rates ) {
    // $free = array();
    // foreach ( $rates as $rate_id => $rate ) {
    //     if ( 'free_shipping' === $rate->method_id ) {
    //         $free[ $rate_id ] = $rate;
    //         break;
    //     }
    // }
    // return ! empty( $free ) ? $free : $rates;

    $new_rates = array();

    foreach ( $rates as $rate_id => $rate ) {
    	// Only modify rates if free_shipping is present.
    	if ( 'free_shipping' === $rate->method_id ) {
    		$new_rates[ $rate_id ] = $rate;
    		break;
    	}
    }

    if ( ! empty( $new_rates ) ) {
    	//Save local pickup if it's present.
    	foreach ( $rates as $rate_id => $rate ) {
    		if ('local_pickup' === $rate->method_id ) {
    			$new_rates[ $rate_id ] = $rate;
    			break;
    		}
    	}
    	return $new_rates;
    }

    return $rates;
}
add_filter( 'woocommerce_package_rates', 'cp_hide_shipping_when_free_is_available', 100 );