<?php
/**
 * Hide the ACF Menu for Non-Admins
 */

add_filter('acf/settings/show_admin', 'my_acf_show_admin');

function my_acf_show_admin( $show ) {
    
    return current_user_can('manage_options');
    
}
