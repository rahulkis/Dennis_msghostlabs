<?php
/**
 * Template part for displaying a post card preview
 *
 *
 * @package CannaBuilder
 */

$blog_id = get_option('page_for_posts');
$featured_post = get_field('cp_featured_post', $blog_id);

// image
$horizontal = get_field('horizontal_focus_point', $featured_post->ID);
$vertical = get_field('vertical_focus_point', $featured_post->ID);
$image_position = cp_format_image_position($horizontal, $vertical);

?>

<a href="<?php echo get_the_permalink($featured_post->ID); ?>" class="post-card post-card-featured" data-post-card>
	
	<div class="post-card-image">
		<?php if ( has_post_thumbnail($featured_post->ID) ) : ?>
			<?php echo get_the_post_thumbnail($featured_post->ID, 'medium_large', [
				'class' => $image_position
			]); ?>
		<?php else : ?>
			<?php
				$default_thumbnails = get_field('theme_settings_default_featured_images', 'option');
				$random_image = $default_thumbnails[rand(0, count($default_thumbnails) - 1)];
				echo cannabuilder_acf_responsive_image($random_image, 'medium_large', [], [
					'class' => 'position-50-50'
				]);
			?>
		<?php endif; ?>
	</div>

	<div class="post-card-content flush">
		<h3 class="post-card-title"><?php echo get_the_title($featured_post->ID); ?></h3>
		<p class="post-card-excerpt"><?php echo limit_text(get_the_excerpt($featured_post->ID), 40); ?></p>
	</div>

</a>