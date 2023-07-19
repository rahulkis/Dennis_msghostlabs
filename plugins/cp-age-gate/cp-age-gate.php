<?php
/*
Plugin Name: CannaPlanners Age Gate
Description: Age gate and optional location selector for CannaPlanners websites
Version: 1.0.0
Author: CannaPlanners
Author URI: https://cannaplanners.com
*/

$cp_age_gate_plugin_version = '1.0.0';

// load template
add_action('wp_footer', 'cp_age_gate_template');
function cp_age_gate_template() {
    include 'includes/cp-age-gate-template.php';
}

// enqueue scripts
function cp_age_gate_scripts() {   
    wp_enqueue_script( 'cp-age-gate', plugin_dir_url( __FILE__ ) . 'scripts/cp-age-gate.js', [], $cp_age_gate_plugin_version );
    wp_enqueue_style( 'cp-age-gate', plugin_dir_url( __FILE__ ) . 'styles/cp-age-gate.css', [], $cp_age_gate_plugin_version );
}
add_action('wp_enqueue_scripts', 'cp_age_gate_scripts');

// add options pages and settings
include 'includes/cp-age-gate-customizer.php';