<?php
/**
 * Template part for displaying the Section Start module
 *
 * @link https://www.advancedcustomfields.com/resources/flexible-content/
 *
 * @package CannaBuilder
 */

// settings
$index = $args['index'];
$bg_color = get_sub_field('cp_module_setting_background_color');
$custom_bg = false;
$custom_text = false;
if($bg_color == 'custom') {
	$custom_bg = get_sub_field('cp_module_setting_background_color_custom');
	$custom_text = get_sub_field('cp_module_setting_text_color');
}

?>

<div id="<?php echo 'module-' . $index; ?>" class="module module-section module-section-start module-setting-bg-<?php echo $bg_color; ?> <?php echo 'module-' . $index; ?> style="<?php echo $custom_bg ? 'background-color:' . $custom_bg . ';' : ''; ?>">