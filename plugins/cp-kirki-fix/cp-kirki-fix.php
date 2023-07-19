<?php
/**
 * Plugin Name: CannaPlanners - Kirki Admin Fix
 * Description: Fix the CSS in admin for Kirki
 * Version: 1.0.0
 * Author: CannaPlanners
 * Author URI: https://cannaplanners.com
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function cp_kirki_fix_admin_css() {
  ?>
  <style>
    .control-section-kirki-default, .control-section-kirki-outer {
      min-height: 0 !important;
    }
  </style>
  <?php
}
add_action( 'customize_controls_print_styles', 'cp_kirki_fix_admin_css', 999 );