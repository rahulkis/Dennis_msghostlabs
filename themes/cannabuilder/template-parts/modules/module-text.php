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
$columns = get_sub_field('cp_module_setting_columns');

$custom_bg = false;
$custom_text = false;
if($bg_color == 'custom') {
	$custom_bg = get_sub_field('cp_module_setting_background_color_custom');
	$custom_text = get_sub_field('cp_module_setting_text_color');
}

// content
$title = get_sub_field('cp_module_part_title');
$pretitle = get_sub_field('cp_module_part_pretitle');
$text = get_sub_field('cp_module_part_text');

?>


<div id="<?php echo 'module-' . $index; ?>" class="module module-text module-padded-top module-padded-bottom module-setting-bg-<?php echo $bg_color; ?> module-setting-columns-<?php echo $columns; ?> <?php echo 'module-' . $index; ?>" style="<?php echo $custom_bg ? 'background-color:' . $custom_bg . ';' : ''; ?>">

	<div class="container">

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
		
		<?php if ( $text ) : ?>
			<div class="module-part-content flush padded">
				<?php echo $text; ?>
			</div>
		<?php endif; ?>

	</div>

</div>