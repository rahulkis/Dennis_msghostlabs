<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 * @package CannaBuilder
 */

get_header(); ?>

	<div class="page-banner cover">
		<?php
			$default_thumbnails = get_field('theme_settings_default_featured_images', 'option');
			$random_image = $default_thumbnails[rand(0, count($default_thumbnails) - 1)];
			echo cannabuilder_acf_responsive_image($random_image, 'full', [], [
				'class' => 'cover-image page-banner-image position-50-50'
			]);
		?>
		<div class="page-banner-content flush cover-content">
			<h1 class="page-banner-title">404</h1>
			<p class="page-banner-subtitle">Page Not Found</p>
		</div><!-- /.content-wrap -->
	</div><!-- /.page-banner -->


<?php

get_footer();