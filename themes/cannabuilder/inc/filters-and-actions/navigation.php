<?php

/**
 * If menu item has image associated with it use it as the menu item
 */
function cannabuilder_menu_item_image( $items, $args ) {

	foreach( $items as &$item ) {
		$image = get_field('menu_item_image', $item);

		if ($image) {
			$item->title = '<img src="' . $image['url'] . '" alt="' . $image['alt'] . '" />';
            $item->classes[] = 'has-image';
		}

        if (in_array('menu-item-has-children', $item->classes)) {
            $item->title = '<span>'. $item->title . '</span><svg xmlns="http://www.w3.org/2000/svg" width="19" height="11" viewBox="0 0 19 11"><path fill="#FFF" d="M9.7405 10.3922l8.8378-9.0152a.5467.5467 0 000-.765.5211.5211 0 00-.7499 0l-8.461 8.6308L.9067.612a.5211.5211 0 00-.75 0 .5497.5497 0 00-.157.3805c0 .1362.051.2764.157.3805l8.8379 9.0152a.52.52 0 00.746.004z"/></svg>';
        }
	}

	return $items;
}

add_filter('wp_nav_menu_objects', 'cannabuilder_menu_item_image', 10, 2);

/**
 * Add data attributes to make bootstrap dropdown.js work
 */
function cannabuilder_dropdown_atts( $atts, $item, $args ) {
    if (in_array('menu-item-has-children', $item->classes)) {
        $atts['aria-haspopup'] = 'true';
        $atts['aria-expanded'] = 'false';
        $atts['role'] = 'button';
        $atts['id'] = 'dropdown-' . $item->ID;

        if (!array_key_exists('class', $atts)) {
            $atts['class'] = 'site-nav-menu-dropdown';
        } else {
            $atts['class'] .= ' site-nav-menu-dropdown';
        }
    }

    $button = get_field('menu_item_button', $item);

    if ($button) {
        if (!array_key_exists('class', $atts)) {
            $atts['class'] = $button;
        } else {
            $atts['class'] .= ' ' . $button;
        }
    }

    return $atts;
}

add_filter( 'nav_menu_link_attributes', 'cannabuilder_dropdown_atts', 10, 3 );
