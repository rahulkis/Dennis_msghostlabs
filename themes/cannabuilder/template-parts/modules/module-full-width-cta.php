<?php
/**
 * Template part for displaying the full width CTA module
 *
 * @link https://www.advancedcustomfields.com/resources/flexible-content/
 *
 * @package CannaBuilder
 */

// settings
$index = $args['index'];

// image
$image = get_sub_field('cp_module_part_image');
$horizontal = get_sub_field('cp_module_setting_horizontal_focus_point');
$vertical = get_sub_field('cp_module_setting_vertical_focus_point');
$image_position = cp_format_image_position($horizontal, $vertical);

// content
$title = get_sub_field('cp_module_part_title');
$description = get_sub_field('cp_module_part_description');
$button = get_sub_field('cp_module_part_button');

$bg_color = get_sub_field('cp_module_setting_background_color');
$custom_bg = false;
$custom_text = false;
if($bg_color == 'custom') {
	$custom_bg = get_sub_field('cp_module_setting_background_color_custom');
	$custom_text = get_sub_field('cp_module_setting_text_color');
}
?>

<div id="<?php echo 'module-' . $index; ?>" class="module module-full-width-cta <?php echo 'module-' . $index; ?> cover">

	
	<?php if($custom_bg) : ?>
		<style>
			#module-<?php echo $index; ?>:after {
				background-color: <?php echo $custom_bg; ?>;
			}
		</style>
	<?php endif; ?>

	<?php if($custom_text) : ?>
		<style>
			#module-<?php echo $index; ?> h1,
			#module-<?php echo $index; ?> h2,
			#module-<?php echo $index; ?> h3,
			#module-<?php echo $index; ?> h4,
			#module-<?php echo $index; ?> h5,
			#module-<?php echo $index; ?> h6,
			#module-<?php echo $index; ?> p,
			#module-<?php echo $index; ?> ol,
			#module-<?php echo $index; ?> ul {
				color: <?php echo $custom_text; ?>;
			}
		</style>
	<?php endif; ?>

	<?php if($image) : ?>
		<?php echo wp_get_attachment_image($image['ID'], 'full', false, [
			'class' => 'cover-image ' . $image_position
		]); ?>
	<?php endif; ?>

	<div class="cover-content">
		<div class="container">
			<div class="module-part-content padded">

				<?php if ( $title ) : ?>
					<h2 class="module-part-title" data-widowfix><?php echo $title; ?></h2>
				<?php endif; ?>
				
				<div class="module-part-content-wrap">
					<?php echo $description; ?>
				</div>

				<?php if ( $button ) : ?>
					<a href="<?php echo $button['url']; ?>" target="<?php echo $button['target']; ?>" class="btn-white-outline"><?php echo $button['title']; ?></a>
				<?php endif; ?>
			</div>
		</div>
	</div>
	
</div><!-- /.module-full-width-cta -->
