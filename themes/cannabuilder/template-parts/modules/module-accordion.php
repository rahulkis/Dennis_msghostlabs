<?php
/**
 * Template part for displaying the accordion module
 *
 * @link https://www.advancedcustomfields.com/resources/flexible-content/
 *
 * @package CannaBuilder
 */

// index
$index = $args['index'];

// title from text field
$pretitle = get_sub_field('cp_module_part_pretitle');
$title = get_sub_field('cp_module_part_title');

// description from text field
$description = get_sub_field('cp_module_part_description');

// Background color from radio field
$bg_color = get_sub_field('cp_module_setting_background_color');

?>

<div id="<?php echo 'module-' . $index; ?>" class="module module-accordion module-setting-bg-<?php echo $bg_color; ?> <?php echo 'module-' . $index; ?> module-padded-top module-padded-bottom">
	<div class="container">

		<div class="module-part-content padded">
			<?php if ( $title ) : ?>
				<?php if ($pretitle) : ?>
					<p class="module-part-pretitle"><?php echo $pretitle; ?></p>
				<?php endif; ?>
				<h2 class="module-part-title" data-widowfix><?php echo $title; ?></h2>
			<?php endif; ?>

			<?php if ( $description ) : ?>
				<div class="module-part-description">
					<?php echo $description; ?>
				</div>
			<?php endif; ?>

		</div>

			<?php if( have_rows('cp_module_part_accordion_items') ): ?>

			<div class="js-accordion" data-accordion-prefix-classes="accordion">

				<?php while( have_rows('cp_module_part_accordion_items') ): the_row(); ?>

					 <div class="js-accordion__panel">
						<h2 class="js-accordion__header"><span><?php the_sub_field('cp_module_part_title') ?></span> <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"><path fill="#1E2F43" fill-rule="evenodd" d="M19.375 7.5H12.5V.625A.625.625 0 0 0 11.875 0h-3.75A.625.625 0 0 0 7.5.625V7.5H.625A.625.625 0 0 0 0 8.125v3.75c0 .345.28.625.625.625H7.5v6.875c0 .345.28.625.625.625h3.75c.345 0 .625-.28.625-.625V12.5h6.875c.345 0 .625-.28.625-.625v-3.75a.625.625 0 0 0-.625-.625z"/></svg></h2>
						<?php the_sub_field('cp_module_part_description'); ?>
					 </div>

				<?php endwhile; ?>

				</div>

			<?php endif; ?>
		
		
	</div>
</div><!-- /.module-accordion -->
