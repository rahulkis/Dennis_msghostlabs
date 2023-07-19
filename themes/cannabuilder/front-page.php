<?php
/**
 * The template for displaying the front page
 *
 * This is the template that displays the front page by default.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package CannaBuilder
 */

get_header(); ?>

	<?php while ( have_posts() ) : the_post(); ?>

		<?php get_template_part( 'template-parts/partial', 'page-banner' ); ?>
				
		<?php get_template_part( 'template-parts/content', 'page-builder' ); ?>
			
	<?php endwhile; ?>

<?php

get_footer();