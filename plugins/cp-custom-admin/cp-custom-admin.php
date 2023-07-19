<?php
/*
Plugin Name: CannaPlanners Custom Admin
Plugin URI: https://cannaplanners.com
Version: 1.0
Author: CannaPlanners
*/

function my_admin_theme_style() {
    wp_enqueue_style('my-admin-theme', plugins_url('cp-admin-style.css?v=2.0.7', __FILE__));
}
add_action('admin_enqueue_scripts', 'my_admin_theme_style');
add_action('login_enqueue_scripts', 'my_admin_theme_style');

?>