<?php

add_action( 'pre_get_posts', 'cp_modify_posts_per_page' );

function cp_modify_posts_per_page( $query ) {
	if ( $query->is_main_query() ) {
		if ( $query->is_home() ) {
			$blog_id = get_option('page_for_posts');
			$featured_post = get_field('cp_featured_post', $blog_id);

			if($featured_post) {
				$query->set( 'post__not_in', [$featured_post->ID] );
			}
		}
	}
}