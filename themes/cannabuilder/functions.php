<?php
/**
 * CannaBuilder functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package CannaBuilder
 */

function cp_theme_settings() {
	global $cp;
	$cp = [
		'logo-position' => 'left',  // options: left, center
		'nav-layout'    => 'overlay', // options: overlay, offcanvas
		'nav-color'     => 'dark',    // options: dark, light
	];
}
add_action( 'init', 'cp_theme_settings' );

if ( ! function_exists( 'cannabuilder_setup' ) ) :
/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */


function cannabuilder_setup() {

	// Add default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );

	/*
	 * Let WordPress manage the document title.
	 * By adding theme support, we declare that this theme does not use a
	 * hard-coded <title> tag in the document head, and expect WordPress to
	 * provide it for us.
	 */
	add_theme_support( 'title-tag' );

	/*
	 * Enable support for Post Thumbnails on posts and pages.
	 *
	 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
	 */
	add_theme_support( 'post-thumbnails' );

	// This theme uses wp_nav_menu() in one location.
	register_nav_menus( array(
		'primary' => esc_html__( 'Primary', 'cannabuilder' ),
		'primary-left' => esc_html__( 'Primary Left', 'cannabuilder' ),
		'primary-right' => esc_html__( 'Primary Right', 'cannabuilder' ),
		'quick-links-1' => esc_html__( 'Quick Links 1', 'cannabuilder' ),
		'quick-links-2' => esc_html__( 'Quick Links 2', 'cannabuilder' ),
		'quick-links-3' => esc_html__( 'Quick Links 3', 'cannabuilder' ),
		'footer' => esc_html__( 'Policies', 'cannabuilder' ),
	) );

	/*
	 * Switch default core markup for search form, comment form, and comments
	 * to output valid HTML5.
	 */
	add_theme_support( 'html5', array(
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
	) );

}
endif;

add_action( 'after_setup_theme', 'cannabuilder_setup' );


/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function cp_widgets_init() {

	register_sidebar( array(
		'name'		  => esc_html__( 'Shop Sidebar', 'cp' ),
		'id'			=> 'sidebar-1',
		'description'   => esc_html__( 'Add widgets here.', 'cp' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );

	register_sidebar( array(
		'name'		  => esc_html__( 'Blog Archive Sidebar', 'cp' ),
		'id'			=> 'sidebar-blog',
		'description'   => esc_html__( 'Add widgets here.', 'cp' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );

	register_sidebar( array(
		'name'		  => esc_html__( 'Blog Post Sidebar', 'cp' ),
		'id'			=> 'sidebar-blog-post',
		'description'   => esc_html__( 'Add widgets here.', 'cp' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );
}
add_action( 'widgets_init', 'cp_widgets_init' );


/**
 * Enqueue scripts and styles.
 */
require get_template_directory() . '/inc/enqueue-scripts.php';

/**
 * WooCommerce support
 */
require get_template_directory() . '/inc/woocommerce.php';


/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/helpers/responsive-images.php';
require get_template_directory() . '/inc/helpers/template-functions.php';
require get_template_directory() . '/inc/helpers/disable-emojis.php';
require get_template_directory() . '/inc/helpers/transparent-header.php';
require get_template_directory() . '/inc/helpers/social-share.php';
require get_template_directory() . '/inc/customizer/customizer.php';


/**
 * Custom Filters and Actions
 */
require get_template_directory() . '/inc/filters-and-actions/navigation.php';
require get_template_directory() . '/inc/filters-and-actions/gravity-forms.php';
require get_template_directory() . '/inc/filters-and-actions/tiny-mce.php';
require get_template_directory() . '/inc/filters-and-actions/scrape-instagram.php';
require get_template_directory() . '/inc/filters-and-actions/search.php';
require get_template_directory() . '/inc/filters-and-actions/acf.php';

/**
 * Page Builder functions
 */
require get_template_directory() . '/inc/page-builder/options-pages.php';
require get_template_directory() . '/inc/page-builder/helpers.php';


/**
 * Blog functions
 */
require get_template_directory() . '/inc/blog/load-more-posts.php';
require get_template_directory() . '/inc/blog/pre-get-posts.php';


/**
 * Image Sizes
 */
require get_template_directory() . '/inc/image-sizes.php';


/**
 * Admin-specific CSS
 */
require get_template_directory() . '/inc/admin-styles.php';
add_filter('acf/prepare_field/name=cp_page_builder', function($field) {

	foreach ($field['layouts'] as $key => $layout) {

		// remove woocommerce modules if the plugin isn't active
		if ( !class_exists( 'WooCommerce' ) ) {
			if( $layout['name'] == 'cp_module_featured_product' || $layout['name'] == 'cp_module_product_grid') {
				unset($field['layouts'][$key]);
			}
		}

		// remove instagram module if plugin isn't active
		if(!class_exists('SB_Instagram_Feed')) {
			if( $layout['name'] == 'cp_module_instagram' ) {
				unset($field['layouts'][$key]);
			}
		}

		// remove team module if plugin isn't active
		if(!post_type_exists('teammember')) {
			if( $layout['name'] == 'cp_module_team' ) {
				unset($field['layouts'][$key]);
			}
		}

		// remove brands module if plugin isn't active
		if(!post_type_exists('cp_brand')) {
			if( $layout['name'] == 'cp_module_brands' ) {
				unset($field['layouts'][$key]);
			}
		}

	}

	return $field;

});

add_filter('age_gate_logo', 'cp_filter_age_gate_logo', 10, 2);
function cp_filter_age_gate_logo($logo, $settings) {

	if($settings) {
		return $logo;
	}

	$custom_logo = get_theme_mod( 'cp_setting_logo_header', '' );
	if($custom_logo && $custom_logo['url']) {
		$logo = '<img class="age-gate-logo" src="'.$custom_logo['url'].'" alt="'.get_bloginfo('name').'" />';
	}
	return $logo;
}

add_action('cp_before_header_wrap', 'cp_site_notice');
function cp_site_notice() {
	get_template_part('template-parts/partial', 'site-notice');
}

add_filter( 'template_include', 'cp_coming_soon_template', 99 );
function cp_coming_soon_template( $template ) {

	if ( true == get_theme_mod( 'cp_setting_coming_soon_mode' ) ) {

		$coming_soon = locate_template( array( 'coming-soon.php' ) );
		$preview = get_theme_mod( 'cp_setting_coming_soon_preview' );

		// show coming soon except if logged in
		// or show coming soon if preview option is enabled
		if ( ('' != $coming_soon && !is_user_logged_in()) || $preview) {
			return $coming_soon ;
		}
	}

	return $template;
}