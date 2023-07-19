<?php
/**
 * Plugin Name: CannaPlanners - Team Member Post Type
 * Description: Registers a Team Member post type for use with the team module
 * Version: 1.0.0
 * Author: CannaPlanners
 * Author URI: https://cannaplanners.com
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'cp_team_post_type' );
/**
 * Register a Team Member post type.
 *
 */
function cp_team_post_type() {

	$labels = array(
		'name'               => _x( 'Team Members', 'post type general name', 'your-plugin-textdomain' ),
		'singular_name'      => _x( 'Team Member', 'post type singular name', 'your-plugin-textdomain' ),
		'menu_name'          => _x( 'Team Members', 'admin menu', 'your-plugin-textdomain' ),
		'name_admin_bar'     => _x( 'Team Member', 'add new on admin bar', 'your-plugin-textdomain' ),
		'add_new'            => _x( 'Add New', 'Team Member', 'your-plugin-textdomain' ),
		'add_new_item'       => __( 'Add New Team Member', 'your-plugin-textdomain' ),
		'new_item'           => __( 'New Team Member', 'your-plugin-textdomain' ),
		'edit_item'          => __( 'Edit Team Member', 'your-plugin-textdomain' ),
		'view_item'          => __( 'View Team Member', 'your-plugin-textdomain' ),
		'all_items'          => __( 'All Team Members', 'your-plugin-textdomain' ),
		'search_items'       => __( 'Search Team Members', 'your-plugin-textdomain' ),
		'parent_item_colon'  => __( 'Parent Team Members:', 'your-plugin-textdomain' ),
		'not_found'          => __( 'No Team Members found.', 'your-plugin-textdomain' ),
		'not_found_in_trash' => __( 'No Team Members found in Trash.', 'your-plugin-textdomain' )
	);

	$args = array(
		'labels'             => $labels,
        'description'        => __( 'Description.', 'your-plugin-textdomain' ),
		'public'             => false,
		'publicly_queryable' => false,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'Team Member' ),
		'capability_type'    => 'post',
		'has_archive'        => false,
		'hierarchical'       => false,
		'menu_position'      => null,
		'supports'           => array( 'title', 'thumbnail'),
		'menu_icon'			 => 'dashicons-groups'
	);

	register_post_type( 'Team Member', $args );
}