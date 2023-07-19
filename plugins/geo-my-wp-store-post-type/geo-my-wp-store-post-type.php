<?php
/**
 * Plugin Name: GEO my WP Store Post Type
 * Description: Registers a Store post type for use with the GEO My WP plugin
 * Version: 1.0.0
 * Author: CannaPlanners
 * Author URI: https://cannaplanners.com
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'cp_geo_my_wp_store_post_type' );
/**
 * Register a Store post type.
 *
 */
function cp_geo_my_wp_store_post_type() {

	$labels = array(
		'name'               => _x( 'Stores', 'post type general name', 'your-plugin-textdomain' ),
		'singular_name'      => _x( 'Store', 'post type singular name', 'your-plugin-textdomain' ),
		'menu_name'          => _x( 'Stores', 'admin menu', 'your-plugin-textdomain' ),
		'name_admin_bar'     => _x( 'Store', 'add new on admin bar', 'your-plugin-textdomain' ),
		'add_new'            => _x( 'Add New', 'store', 'your-plugin-textdomain' ),
		'add_new_item'       => __( 'Add New Store', 'your-plugin-textdomain' ),
		'new_item'           => __( 'New Store', 'your-plugin-textdomain' ),
		'edit_item'          => __( 'Edit Store', 'your-plugin-textdomain' ),
		'view_item'          => __( 'View Store', 'your-plugin-textdomain' ),
		'all_items'          => __( 'All Stores', 'your-plugin-textdomain' ),
		'search_items'       => __( 'Search Stores', 'your-plugin-textdomain' ),
		'parent_item_colon'  => __( 'Parent Stores:', 'your-plugin-textdomain' ),
		'not_found'          => __( 'No stores found.', 'your-plugin-textdomain' ),
		'not_found_in_trash' => __( 'No stores found in Trash.', 'your-plugin-textdomain' )
	);

	$args = array(
		'labels'             => $labels,
        'description'        => __( 'Description.', 'your-plugin-textdomain' ),
		'public'             => false,
		'publicly_queryable' => false,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'store' ),
		'capability_type'    => 'post',
		'has_archive'        => false,
		'hierarchical'       => false,
		'menu_position'      => null,
		'supports'           => array( 'title', 'editor', 'thumbnail')
	);

	register_post_type( 'store', $args );
}

// Register Custom Taxonomy
function cp_geo_my_wp_store_taxonomy() {

	$labels = array(
		'name'                       => _x( 'Store Categories', 'Taxonomy General Name', 'text_domain' ),
		'singular_name'              => _x( 'Store Category', 'Taxonomy Singular Name', 'text_domain' ),
		'menu_name'                  => __( 'Store Category', 'text_domain' ),
		'all_items'                  => __( 'All Store Categories', 'text_domain' ),
		'parent_item'                => __( 'Parent Store Category', 'text_domain' ),
		'parent_item_colon'          => __( 'Parent Store Category:', 'text_domain' ),
		'new_item_name'              => __( 'New Store Category Name', 'text_domain' ),
		'add_new_item'               => __( 'Add New Store Category', 'text_domain' ),
		'edit_item'                  => __( 'Edit Store Category', 'text_domain' ),
		'update_item'                => __( 'Update Store Category', 'text_domain' ),
		'view_item'                  => __( 'View Store Category', 'text_domain' ),
		'separate_items_with_commas' => __( 'Separate store categories with commas', 'text_domain' ),
		'add_or_remove_items'        => __( 'Add or remove Store Categories', 'text_domain' ),
		'choose_from_most_used'      => __( 'Choose from the most used', 'text_domain' ),
		'popular_items'              => __( 'Popular Store Categories', 'text_domain' ),
		'search_items'               => __( 'Search Store Categories', 'text_domain' ),
		'not_found'                  => __( 'Not Found', 'text_domain' ),
		'no_terms'                   => __( 'No store categories', 'text_domain' ),
		'items_list'                 => __( 'Store Categories list', 'text_domain' ),
		'items_list_navigation'      => __( 'Store Categories list navigation', 'text_domain' ),
	);
	$args = array(
		'labels'                     => $labels,
		'hierarchical'               => true,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => true,
		'show_tagcloud'              => true,
		'rewrite'                    => false,
	);
	register_taxonomy( 'store_cat', array( 'store' ), $args );

}
add_action( 'init', 'cp_geo_my_wp_store_taxonomy', 0 );