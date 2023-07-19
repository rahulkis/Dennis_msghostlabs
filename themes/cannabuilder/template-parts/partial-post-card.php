<?php
/**
 * Template part for displaying a post card preview
 *
 *
 * @package CannaBuilder
 */

global $post;

// image
$horizontal = get_field('horizontal_focus_point');
$vertical = get_field('vertical_focus_point');
$image_position = cp_format_image_position($horizontal, $vertical);

$post_date = get_theme_mod( 'cp_setting_blog_post_date', true );

$show_excerpt = false;
if(isset($args['show_excerpt'])) {
	$show_excerpt = $args['show_excerpt'];
}

?>

<a href="<?php the_permalink(); ?>" class="post-card flex-col-item" data-post-card>
		
	<div class="post-card-image">
		<div class="post-card-image-wrap">
			<?php if ( has_post_thumbnail() ) : ?>
				<?php the_post_thumbnail('medium_large', [
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
	</div>

	<div class="post-card-content flush">

		<?php if($post_date) : ?>
			<p class="post-card-meta"><?php the_date(); ?></p>
		<?php endif; ?>

		<h3 class="post-card-title"><?php the_title(); ?></h3>

		<?php if($show_excerpt) : ?>
			<p><?php echo get_excerpt_trim(30); ?></p>
		<?php endif; ?>

		<?php do_action('cp_after_post_card_content', $post); ?>

		
	</div>

</a>