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
$width = get_sub_field('cp_module_setting_width');
$padding_top = get_sub_field('cp_module_setting_padding_top');
$padding_bottom = get_sub_field('cp_module_setting_padding_bottom');

// content
$code = get_sub_field('cp_module_part_code');

?>


<div id="<?php echo 'module-' . $index; ?>" class="module module-code <?php echo $padding_top ? 'module-padded-top' : ''; ?> <?php echo $padding_bottom ? 'module-padded-bottom' : ''; ?> module-setting-bg-<?php echo $bg_color; ?> <?php echo 'module-' . $index; ?>">

	<?php if($width == 'contained') : ?>
		<div class="container">
	<?php endif; ?>
	
	<?php echo apply_filters('the_content', $code); ?>

	<?php if($width == 'contained') : ?>
		</div>
	<?php endif; ?>

</div>