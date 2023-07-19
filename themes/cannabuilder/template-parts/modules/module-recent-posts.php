<?php

/**
 * Template part for displaying the WYSIWYG module
 *
 * @link https://www.advancedcustomfields.com/resources/flexible-content/
 *
 * @package CannaBuilder
 */

// settings
$index = $args['index'];
$bg_color = get_sub_field('cp_module_setting_background_color');
$post_count = get_sub_field('cp_module_setting_post_count');
$post_category = get_sub_field('cp_module_setting_post_category');

if (!$post_count) {
	$post_count = 3;
}

$query_args = [
	'post_type' => 'post',
	'posts_per_page' => $post_count
];

if ($post_category) {
	$query_args['cat'] = $post_category->term_id;
}

$recent_posts_query = new WP_Query($query_args);

// content
$title = get_sub_field('cp_module_part_title');
$pretitle = get_sub_field('cp_module_part_pretitle');
$text = get_sub_field('cp_module_part_text');
$button = get_sub_field('cp_module_part_button');

?>

<?php if ($recent_posts_query->have_posts()) : ?>

	<div id="<?php echo 'module-' . $index; ?>" class="module module-recent-posts module-padded-top module-padded-bottom module-setting-bg-<?php echo $bg_color; ?> <?php echo 'module-' . $index; ?>">

		<div class="container">

			<?php if ($title) : ?>
				<div class="module-part-heading padded">
					<?php if ($pretitle) : ?>
						<p class="module-part-pretitle"><?php echo $pretitle; ?></p>
					<?php endif; ?>
					<h2 class="module-part-title">
						<span data-widowfix><?php echo $title; ?></span>
					</h2>
				</div>
			<?php endif; ?>

			<?php if ($text) : ?>
				<div class="module-part-content flush padded">
					<?php echo $text; ?>
				</div>
			<?php endif; ?>

			<!-- the loop -->
			<div class="flex-col flex-col-3">
				<?php while ($recent_posts_query->have_posts()) : $recent_posts_query->the_post(); ?>
					<?php get_template_part('template-parts/partial', 'post-card'); ?>
				<?php endwhile; ?>
			</div>
			<!-- end of the loop -->

			<?php if ($button) : ?>
				<div class="module-part-footer">
					<a href="<?php echo $button['url']; ?>" target="<?php echo $button['target']; ?>" class="btn-primary"><?php echo $button['title']; ?></a>
				</div>
			<?php endif; ?>

		</div>

	</div>

	<?php wp_reset_postdata(); ?>

<?php endif; ?>