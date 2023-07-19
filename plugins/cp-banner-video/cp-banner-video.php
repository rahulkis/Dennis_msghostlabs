<?php
/*
Plugin Name: CannaPlanners Banner Video
Description: Adds a video option to the home page banner
Version: 1.0.0
Author: CannaPlanners
Author URI: https://cannaplanners.com
*/

add_action('cp_before_page_banner_image', 'cp_add_banner_video');

function cp_add_banner_video() {

    $video = get_field('cp_page_banner_video');

    if(!$video) {
        return false;
    }

    echo '<video autoplay muted loop preload src="'.$video.'" class="page-banner-video cover-video"></video>';

}

if( function_exists('acf_add_local_field_group') ):

acf_add_local_field_group(array(
	'key' => 'group_606f14a52056b',
	'title' => 'Page Banner Video',
	'fields' => array(
		array(
			'key' => 'field_606f14c3dc671',
			'label' => 'Video',
			'name' => 'cp_page_banner_video',
			'type' => 'url',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'placeholder' => '',
		),
	),
	'location' => array(
		array(
			array(
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'page',
			),
		),
	),
	'menu_order' => 5,
	'position' => 'acf_after_title',
	'style' => 'default',
	'label_placement' => 'top',
	'instruction_placement' => 'label',
	'hide_on_screen' => '',
	'active' => true,
	'description' => '',
));

endif;