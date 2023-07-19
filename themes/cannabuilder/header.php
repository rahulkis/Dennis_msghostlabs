<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package CannaBuilder
 */

global $cp;
$nav_class = 'nav-' . $cp['nav-layout'] . ' nav-' . $cp['nav-color'];
if ( class_exists( 'WooCommerce' ) ) {
	$nav_class .= ' nav-ecommerce';
}

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>

	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<meta name="format-detection" content="telephone=no">
	
	<?php wp_head(); ?>

</head>

<body <?php body_class($nav_class); ?>>

<?php wp_body_open(); ?>

<?php if ( function_exists( 'gtm4wp_the_gtm_tag' ) ) { gtm4wp_the_gtm_tag(); } ?>

<div id="page" class="site">

	<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'cannabuilder' ); ?></a>

	<?php do_action('cp_before_header'); ?>

	<?php get_template_part('template-parts/headers/header', 'logo-' . $cp['logo-position']); ?>

	<?php do_action('cp_after_header'); ?>

	<?php do_action('cp_before_content'); ?>

	<div id="content" class="site-content">

		<div id="primary" class="content-area">
			
			<main id="main" class="site-main">