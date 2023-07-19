<?php
/**
 * Function for loading more posts with ajax
 *
 *
 * @link https://github.com/usminteractive/usmms/wiki/Useful-Snippets#load-more-posts-with-ajax
 *
 * @package CannaBuilder
 */

function load_more_posts() {

	$offset = $_POST['offset'];
	$limit = $_POST['limit'];
	$template = $_POST['template'];
	$taxonomy = $_POST['taxonomy'];
	$term = $_POST['term'];

	$args = array(
		'post_type'			=> 'post',
		'posts_per_page'	=> $limit,
		'offset'			=> $offset,
		'post_status'		=> 'publish',
	);

	if($taxonomy && $term) {
		$args['tax_query'] = [
			[
				'taxonomy' => $taxonomy,
				'field'    => 'slug',
				'terms'    => $term
			]
		];
	}

	$loop = new WP_Query($args);

	if ( $loop->have_posts() ){
	
		while( $loop->have_posts() ){
			$loop->the_post();
			
			get_template_part('template-parts/partial', $template);

		}
		wp_reset_postdata();
	}

	wp_die();
	
}
add_action( 'wp_ajax_load_more_posts', 'load_more_posts' );
add_action( 'wp_ajax_nopriv_load_more_posts', 'load_more_posts' );