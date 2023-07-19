<?php
/**
 * Template Name: Store Locator
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

$content = get_field('cp_store_locator_content');

$shortcode = get_field('cp_store_locator_shortcode');
if(!$shortcode) {
	$shortcode = '[gmw form="1"]';
}

get_header(); ?>

	<?php while ( have_posts() ) : the_post(); ?>

		<?php get_template_part( 'template-parts/partial', 'page-banner' ); ?>

		<?php if($content) : ?>
			<div class="store-locator-content">
				<div class="container">
					<?php echo $content; ?>
				</div>
			</div>
		<?php endif; ?>

		<?php echo do_shortcode($shortcode); ?>
				
		<?php get_template_part( 'template-parts/content', 'page-builder' ); ?>
			
	<?php endwhile; ?>

<?php

get_footer();