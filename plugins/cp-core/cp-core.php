<?php
/*
Plugin Name: CannaPlanners Core
Description: This plugin adds core CannaPlanners functionality
Version: 1.1.1
Author: CannaPlanners
Author URI: https://cannaplanners.com
*/

function cp_add_support_toolbar_link( $wp_admin_bar ) {
    $args = array(
        'id'    => 'cp_support',
        'title' => '<span>Need help? Email support@cannaplanners.com</span>',
        'href'  => 'mailto:support@cannaplanners.com',
        'meta'  => array( 'class' => 'cp-support-link', 'target' => '_blank' )
    );
    $wp_admin_bar->add_node( $args );
}
add_action( 'admin_bar_menu', 'cp_add_support_toolbar_link', 999 );

add_action('admin_head', 'cp_admin_css');
function cp_admin_css() {
  echo '<style>
    
    .yith-license-notice,
    .monsterinsights-review-notice,
    .sbi-license-expired,
    .sbi-license-countdown {
        display: none !important;
    }

    .cp-support-link a {
        display: -webkit-box !important;
        display: -ms-flexbox !important;
        display: flex !important;
        -webkit-box-align: center;
            -ms-flex-align: center;
                align-items: center;
      }
    .cp-support-link a:before {
        content: "\f468";
        display: inline-block;
    }
    .cp-support-link a span {
        display: inline-block;
    }
  </style>';
}

// Only allow fields to be edited on development
add_action('init', 'cp_hide_acf_admin');
function cp_hide_acf_admin() {
	if ( wp_get_environment_type() === 'production' ) {
    	add_filter( 'acf/settings/show_admin', '__return_false' );
	}
}