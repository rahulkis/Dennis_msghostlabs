<?php
/*
Plugin Name: CannaPlanners Brands
Plugin URI: https://cannaplanners.com
Description: Registers a Brands Custom Post Type, used for the Brands Module.
Version: 1.0
Author: CannaPlanners
Author URI: https://cannaplanners.com
License: GPL2
*/

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function cp_brands_init() {
    $labels = array(
        'name'               => _x( 'Brands', 'post type general name', 'cp-brands' ),
        'singular_name'      => _x( 'Brand', 'post type singular name', 'cp-brands' ),
        'menu_name'          => _x( 'Brands', 'admin menu', 'cp-brands' ),
        'name_admin_bar'     => _x( 'Brand', 'add new on admin bar', 'cp-brands' ),
        'add_new'            => _x( 'Add New', 'brand', 'cp-brands' ),
        'add_new_item'       => __( 'Add New Brand', 'cp-brands' ),
        'new_item'           => __( 'New Brand', 'cp-brands' ),
        'edit_item'          => __( 'Edit Brand', 'cp-brands' ),
        'view_item'          => __( 'View Brand', 'cp-brands' ),
        'all_items'          => __( 'All Brands', 'cp-brands' ),
        'search_items'       => __( 'Search Brands', 'cp-brands' ),
        'parent_item_colon'  => __( 'Parent Brands:', 'cp-brands' ),
        'not_found'          => __( 'No brands found.', 'cp-brands' ),
        'not_found_in_trash' => __( 'No brands found in Trash.', 'cp-brands' )
    );

    $args = array(
        'labels'             => $labels,
        'description'        => __( 'Description.', 'cp-brands' ),
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'brand' ),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => null,
        'menu_icon'          => 'dashicons-portfolio',
        'supports'           => array( 'title', 'thumbnail', 'editor' )
    );

    register_post_type( 'cp_brand', $args );
}
add_action( 'init', 'cp_brands_init' );
