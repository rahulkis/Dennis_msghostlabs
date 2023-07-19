<?php
/**
 * Template part for displaying the mini slider module
 *
 * @link https://www.advancedcustomfields.com/resources/flexible-content/
 *
 * @package CannaBuilder
 */

// settings
$index = $args['index'];
$bg_color = get_sub_field('cp_module_setting_background_color');

?>

<div id="<?php echo 'module-' . $index; ?>" class="module module-mini-slider module-setting-bg-<?php echo $bg_color; ?> <?php echo 'module-' . $index; ?>">

	<?php if ( have_rows('cp_module_part_slides') ) : ?>

		<div class="slides">

		    <?php while ( have_rows('cp_module_part_slides') ) : the_row(); ?>

			<?php
				// content
				$image = get_sub_field('cp_module_part_image');
				$horizontal = get_sub_field('cp_module_setting_horizontal_focus_point');
				$vertical = get_sub_field('cp_module_setting_vertical_focus_point');
				$image_position = cp_format_image_position($horizontal, $vertical);
				$title = get_sub_field('cp_module_part_title');
				$description = get_sub_field('cp_module_part_description');
			?>
				<div class="slide">

					<div class="slide-content cover">
						<?php echo cannabuilder_acf_responsive_image($image, 'full', [], [
							'class' => 'cover-image ' . $image_position
						]); ?>

						<div class="slide-text cover-content flush">
							<?php if ($title) : ?>
								<h3 class="slide-title"><?php echo $title; ?></h3>
								<?php echo $description; ?>
							<?php endif; ?>
						</div>

					</div><!-- /.slide-wrap -->

				</div><!-- /.slide -->

			<?php endwhile; ?>

		</div><!-- /.slides -->

	<?php else : ?>
	
	    <?php // no rows found ?>
	
	<?php endif; ?>
</div><!-- /.module-mini-slider -->
