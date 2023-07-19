<?php
/**
 * Template part for displaying the banner image module
 *
 * @link https://www.advancedcustomfields.com/resources/flexible-content/
 *
 * @package CannaBuilder
 */

// index
$index = $args['index'];

// Banner Image
$image = get_sub_field('cp_module_part_image');

//Horizontal Focus Point
$horizontal = get_sub_field('cp_module_setting_horizontal_focus_point');

//Vertical Focus Point
$vertical = get_sub_field('cp_module_setting_vertical_focus_point');

// image position class
$image_position = cp_format_image_position($horizontal, $vertical);

//Content
$content = get_sub_field('cp_module_part_content');

//Option to show scroll down link
$show_scroll_down_link = get_sub_field('cp_module_setting_show_scroll_down_link');

?>

<div id="<?php echo 'module-' . $index; ?>" class="module module-banner-image <?php echo 'module-' . $index; echo ($show_scroll_down_link ? ' has-scroll-arrow' : ''); ?>">
	<?php if ( $image ) : ?>
		<?php echo cannabuilder_acf_responsive_image($image, 'full', [], [
			'class' => 'banner-image ' . $image_position
		]); ?>
	<?php endif ?>
	<?php if ( $content ) : ?>
		<div class="banner-content container">
			<?php echo $content; ?>
		</div><!-- /.banner-content -->
	<?php endif ?>
	<?php if ( $show_scroll_down_link ) : ?>
		<a class="scroll" href="#">
			<span>SCROLL</span>
			<img src="<?php echo get_template_directory_uri() . '/dist/img/modules/banner-image/arrow-icon.svg'; ?>" alt="Scroll Down" />
		</a>
	<?php endif ?>
</div><!-- /.module-banner-image -->
