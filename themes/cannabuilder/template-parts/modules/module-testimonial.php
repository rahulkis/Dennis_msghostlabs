<?php
/**
 * Template part for displaying the testimonial module
 *
 * @link https://www.advancedcustomfields.com/resources/flexible-content/
 *
 * @package CannaBuilder
 */

// settings
$index = $args['index'];
$bg_color = get_sub_field('cp_module_setting_background_color');

// image
$image = get_sub_field('cp_module_part_image');
$horizontal = get_sub_field('cp_module_setting_horizontal_focus_point');
$vertical = get_sub_field('cp_module_setting_vertical_focus_point');
$image_position = cp_format_image_position($horizontal, $vertical);

if($image) {
	$bg_color = 'dark';
}

// content
$quote = get_sub_field('cp_module_part_quote');
$author_name = get_sub_field('cp_module_part_author_name');
$author_title = get_sub_field('cp_module_part_author_title');

?>

<div id="<?php echo 'module-' . $index; ?>" class="module module-testimonial module-padded-top module-padded-bottom module-setting-bg-<?php echo $bg_color; ?> <?php echo 'module-' . $index; ?> <?php echo $image ? 'cover' : ''; ?>">
	
	<?php if ( $image ) : ?>
		<?php echo cannabuilder_acf_responsive_image($image, 'full', [], [
			'class' => 'cover-image ' . $image_position
		]); ?>
	<?php endif; ?>

	<div class="cover-content">
		<div class="container">
			<div class="module-part-content padded">

				<blockquote>

					<?php if ( $quote ) : ?>
						<p><?php echo $quote; ?></p>
					<?php endif; ?>

					<?php if ( $author_name ) : ?>
						<footer>
							<cite>
								<?php echo $author_name; ?>
								<?php if ( $author_title ) : ?>
									<span><?php echo $author_title; ?></span>
								<?php endif; ?>
							</cite>
						</footer>
					<?php endif; ?>
					
				</blockquote>

			</div>
		</div>
	</div>

</div><!-- /.module-full-width-cta -->
