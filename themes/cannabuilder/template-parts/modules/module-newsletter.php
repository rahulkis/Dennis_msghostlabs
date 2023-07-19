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

// content
$title = get_sub_field('cp_module_part_title');
$desc = get_sub_field('cp_module_part_desc');
$form = get_sub_field('cp_module_part_form');

?>


<div id="<?php echo 'module-' . $index; ?>" class="module module-newsletter module-padded-top module-padded-bottom module-setting-bg-<?php echo $bg_color; ?> <?php echo 'module-' . $index; ?>">

	<div class="container">

		<div class="flex">
			
			<div class="module-part-content flush">
				<?php if ($title) : ?>
					<h2 class="module-part-title"><?php echo $title; ?></h2>
				<?php endif; ?>

				<?php if($desc) : ?>
					<p><?php echo $desc; ?></p>
				<?php endif; ?>
			</div>

			<div class="module-part-form newsletter-form">
				<?php echo $form; ?>
			</div>

		</div>

	</div>

</div>