<?php
/*
Plugin Name: CannaPlanners - Wholesale
Description: Custom wholesale functionality
Version: 1.0.0
Author: CannaPlanners
Author URI: https://cannaplanners.com
*/

function cp_logged_in_shortcode( $atts, $content = null ) {
    if(is_user_logged_in()) {
        return apply_filters('the_content', $content);
    }
    return '';
}
add_shortcode('logged_in', 'cp_logged_in_shortcode');

function cp_logged_out_shortcode( $atts, $content = null ) {
    if(!is_user_logged_in()) {
        return apply_filters('the_content', $content);
    }
    return '';
}
add_shortcode('logged_out', 'cp_logged_out_shortcode');

// Change the country list of the registration form
add_filter( 'woocommerce_form_field_args', 'my_wwlc_set_country');
function my_wwlc_set_country($args){
    // Set the country list
    if($args['id'] == 'wwlc_country'){
        $args['options'] = array(
            'US' => 'United States'
        );
    }
    // Return the modified list
    return $args;
}

// Change the default country on WooCommerce Wholesale Lead Capture's registration
function wwsSetRegistrationDefaultCountry() {
    if ( is_page( 'wholesale-registration-page' ) ) {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function () {
                jQuery('select#wwlc_country').val('US');
                jQuery('select#wwlc_country').trigger('change');
            });
        </script>
        <?php
    }
}
add_action( 'wp_footer', 'wwsSetRegistrationDefaultCountry', 99 );