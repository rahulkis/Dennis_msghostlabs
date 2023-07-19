<?php
/**
 * Enqueue scripts and styles.
 */
function cannabuilder_scripts() {

	global $wp_query;

	wp_enqueue_style( 'google-fonts', 'https://fonts.googleapis.com/css?family=Arvo:400,400i,700|Lato:400,700&display=swap');

	wp_enqueue_style( 'cannabuilder-style', cannabuilder_asset_path('style.css', 'css'), [], cannabuilder_asset_version('style.css', 'css') );

	wp_enqueue_script( 'cannabuilder-script', cannabuilder_asset_path('app.js'), array('jquery'), cannabuilder_asset_version('app.js'), true );

	wp_localize_script( 'cannabuilder-script', 'cp_ajax_object',
            array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}

	$page_id = cp_get_page_id();

	/*
	 * load scripts based on the modules that exist on a page
	 */
	if( class_exists('acf') ) {
		
		if( have_rows('cp_page_builder', $page_id) ):

			// loop through the rows of data
			while ( have_rows('cp_page_builder', $page_id) ) : the_row();

				if( get_row_layout() == 'cp_module_accordion' ): 
					wp_enqueue_script( 'cannabuilder-accordion-js', cannabuilder_asset_path('accordion.js'), array('jquery'), cannabuilder_asset_version('accordion.js'), true );

				elseif( get_row_layout() == 'cp_module_gallery' ): 
					wp_enqueue_style( 'photoswipe-style', cannabuilder_asset_path('plugins/photoswipe/photoswipe.css', 'css'), [], cannabuilder_asset_version('photoswipe.css', 'css'));
					wp_enqueue_script( 'cannabuilder-gallery-js', cannabuilder_asset_path('gallery.js'), array('jquery'), cannabuilder_asset_version('gallery.js'), true );
					wp_enqueue_script( 'cannabuilder-gallery-masonry-js', cannabuilder_asset_path('gallery-masonry.js'), array('jquery'), cannabuilder_asset_version('gallery-masonry.js'), true );

				elseif( get_row_layout() == 'cp_module_google_map' ): 
					wp_enqueue_script( 'cannabuilder-google-map-api', 'https://maps.googleapis.com/maps/api/js?key=AIzaSyDgkBBMgwj1a0FgJH69ALJcLel6gcv_h8s', array(), '1', true );
					wp_enqueue_script( 'cannabuilder-google-map-js', cannabuilder_asset_path('google-map.js'), array(), cannabuilder_asset_version('google-map.js'), true );

				elseif( get_row_layout() == 'cp_module_testimonial_slider' ):
					wp_enqueue_style( 'slick-style', cannabuilder_asset_path('plugins/slick-slider/slick.css', 'css'), [], cannabuilder_asset_version('plugins/slick-slider/slick.css', 'css'));
					wp_enqueue_style( 'slick-theme-style', cannabuilder_asset_path('plugins/slick-slider/slick-theme.css', 'css'), [], cannabuilder_asset_version('plugins/slick-slider/slick-theme.css', 'css'));
					wp_enqueue_script( 'cannabuilder-slider-js', cannabuilder_asset_path('slider.js'), array('jquery'), cannabuilder_asset_version('slider.js'), true );

			   	elseif( get_row_layout() == 'cp_module_tabs' ):
			   		wp_enqueue_script( 'cannabuilder-tabs-js', cannabuilder_asset_path('tabs.js'), array('jquery'), cannabuilder_asset_version('tabs.js'), true );

				endif;

			endwhile;

		endif;
	}
}
add_action( 'wp_enqueue_scripts', 'cannabuilder_scripts' );