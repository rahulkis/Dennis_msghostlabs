<?php

/**
 * ACF Theme Options Page
 */

if( function_exists('acf_add_options_sub_page') ) {
    acf_add_options_sub_page(array(
        'page_title'  => __('Fallback Images'),
        'menu_title'  => __('Fallback Images'),
        'parent_slug' => 'themes.php',
    ));
}