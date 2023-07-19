<?php
/**
 * Template Name: Landing Page
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site may use a
 * different template.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package CannaBuilder
 */

get_header('landing'); ?>

	<?php while ( have_posts() ) : the_post(); ?>

		<?php get_template_part( 'template-parts/partial', 'page-banner' ); ?>

		<?php get_template_part( 'template-parts/partial', 'page-ecommerce' ); ?>
				
		<?php get_template_part( 'template-parts/content', 'page-builder' ); ?>
			
	<?php endwhile; ?>

<?php

get_footer('landing');