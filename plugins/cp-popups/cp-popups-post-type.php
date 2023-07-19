<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'cp_popup_post_type' );
/**
 * Register a Popup Member post type.
 *
 */
function cp_popup_post_type() {

	$labels = array(
		'name'               => _x( 'Pop-ups', 'post type general name', 'your-plugin-textdomain' ),
		'singular_name'      => _x( 'Pop-up', 'post type singular name', 'your-plugin-textdomain' ),
		'menu_name'          => _x( 'Pop-ups', 'admin menu', 'your-plugin-textdomain' ),
		'name_admin_bar'     => _x( 'Pop-up', 'add new on admin bar', 'your-plugin-textdomain' ),
		'add_new'            => _x( 'Add New', 'Pop-up', 'your-plugin-textdomain' ),
		'add_new_item'       => __( 'Add New Pop-up', 'your-plugin-textdomain' ),
		'new_item'           => __( 'New Pop-up', 'your-plugin-textdomain' ),
		'edit_item'          => __( 'Edit Pop-up', 'your-plugin-textdomain' ),
		'view_item'          => __( 'View Pop-up', 'your-plugin-textdomain' ),
		'all_items'          => __( 'All Pop-ups', 'your-plugin-textdomain' ),
		'search_items'       => __( 'Search Pop-ups', 'your-plugin-textdomain' ),
		'parent_item_colon'  => __( 'Parent Pop-ups:', 'your-plugin-textdomain' ),
		'not_found'          => __( 'No Pop-ups found.', 'your-plugin-textdomain' ),
		'not_found_in_trash' => __( 'No Pop-ups found in Trash.', 'your-plugin-textdomain' )
	);

	$args = array(
		'labels'             => $labels,
        'description'        => __( 'Description.', 'your-plugin-textdomain' ),
		'public'             => false,
		'publicly_queryable' => false,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'popup' ),
		'capability_type'    => 'post',
		'has_archive'        => false,
		'hierarchical'       => false,
		'menu_position'      => null,
		'supports'           => array( 'title', 'editor'),
		'menu_icon'			 => 'dashicons-external'
	);

	register_post_type( 'Pop-up', $args );
}