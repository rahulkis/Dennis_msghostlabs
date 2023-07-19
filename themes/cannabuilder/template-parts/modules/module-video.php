<?php
/**
 * Template part for displaying the video module
 *
 * @link https://www.advancedcustomfields.com/resources/flexible-content/
 *
 * @package CannaBuilder
 */

// index
$index = $args['index'];
$width = get_sub_field('cp_module_setting_width');
$padding_top = get_sub_field('cp_module_setting_padding_top');
$padding_bottom = get_sub_field('cp_module_setting_padding_bottom');
$bg_color = get_sub_field('cp_module_setting_background_color');

//Video Embed
$video_embed = get_sub_field('cp_module_part_video_embed');

// image
$image = get_sub_field('cp_module_part_image');

//Horizontal Focus Point
$horizontal = get_sub_field('cp_module_setting_horizontal_focus_point');

//Vertical Focus Point
$vertical = get_sub_field('cp_module_setting_vertical_focus_point');

// image position for object-fit
$image_position = cp_format_image_position($horizontal, $vertical);

?>

<div id="<?php echo 'module-' . $index; ?>" class="module module-video module-setting-bg-<?php echo $bg_color; ?> <?php echo $padding_top ? 'module-padded-top' : ''; ?> <?php echo $padding_bottom ? 'module-padded-bottom' : ''; ?> <?php echo 'module-' . $index; ?>">

	<?php if($width == 'contained') : ?>
		<div class="container">
	<?php endif; ?>

	<div class="module-part-content">
		<button class="btn-reset btn-play" type="button" data-a11y-dialog-show="modal-<?php echo $index; ?>"><span class="sr-only">Play video</span><svg xmlns="http://www.w3.org/2000/svg" width="30" height="43" viewBox="0 0 30 43"><path fill="#F04E38" fill-rule="evenodd" d="M30 21.5L0 43V0z"/></svg></button>
		
		<?php if ( $image ) : ?>
			<?php echo wp_get_attachment_image($image['ID'], 'full', false, ['class' => 'banner-image ' . $image_position]); ?>
		<?php endif ?>
	</div>

	<?php if($width == 'contained') : ?>
		</div>
	<?php endif; ?>

</div><!-- /.module-video -->

<div class="modal modal-move" id="modal-<?php echo $index; ?>" aria-hidden="true">

	<div class="modal-backdrop" tabindex="-1" data-a11y-dialog-hide></div>

	<div class="modal-dialog" role="dialog" aria-labelledby="modal-<?php echo $index; ?>-title">
		
		<div class="modal-header">
			<button type="button" class="btn-reset" data-a11y-dialog-hide aria-label="Close this dialog window">
				<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28"><path fill="#FFF" fill-rule="evenodd" d="M24.704.565L14.077 11.193 4.541.565a1.934 1.934 0 0 0-2.734 2.733l9.536 10.629L.566 24.7A1.934 1.934 0 0 0 3.3 27.435L13.93 16.81l9.537 10.624a1.93 1.93 0 1 0 2.73-2.734l-9.54-10.624L27.434 3.3a1.93 1.93 0 1 0-2.73-2.733z"/></svg>
			</button>
		</div>
		
		<div class="modal-content" role="document">
			
			<h1 id="modal-<?php echo $index; ?>-title" class="sr-only">Watch a video</h1>
			
			<div class="embed-container" data-video-embed="<?php echo htmlentities(add_iframe_attrs($video_embed)); ?>">
			</div>

		</div>
	</div>

</div>
