<?php
/*
Plugin Name: CannaPlanners Email Settings
Version: 1.0.0
Author: CannaPlanners
Author URI: https://cannaplanners.com
*/

function cp_change_from_address() {
    return 'no-reply@canplanmail.com';
}
add_filter( 'wp_mail_from', 'cp_change_from_address' );