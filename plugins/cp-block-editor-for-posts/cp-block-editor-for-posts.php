<?php
/*
Plugin Name: CannaPlanners Block Editor for Posts
Description: Enables the Block Editor for blog posts only
Version: 1.0.0
Author: CannaPlanners
Author URI: https://cannaplanners.com
*/

add_filter('use_block_editor_for_post_type', 'cp_enable_gutenberg', 10, 2);
function cp_enable_gutenberg($current_status, $post_type) {
    if ($post_type === 'post') return true;
    return false;
}