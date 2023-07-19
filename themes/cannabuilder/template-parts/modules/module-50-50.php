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
$content_type = get_sub_field('cp_module_setting_content_type');
$image_side = get_sub_field('cp_module_setting_image_side');
$bg_color = get_sub_field('cp_module_setting_background_color');
$width = get_sub_field('cp_module_setting_width');

$custom_bg = false;
$custom_text = false;
if($bg_color == 'custom') {
	$custom_bg = get_sub_field('cp_module_setting_background_color_custom');
	$custom_text = get_sub_field('cp_module_setting_text_color');
}

$padded = '';
if($width == 'contained') {
	$padded = 'module-padded-top module-padded-bottom';
}

// image
$image = get_sub_field('cp_module_part_image');
$horizontal = get_sub_field('cp_module_setting_horizontal_focus_point');
$vertical = get_sub_field('cp_module_setting_vertical_focus_point');
$image_position = cp_format_image_position($horizontal, $vertical);

// content
$pretitle = get_sub_field('cp_module_part_pretitle');
$title = get_sub_field('cp_module_part_title');
$content = get_sub_field('cp_module_part_content');
$button = get_sub_field('cp_module_part_button');
$button2 = get_sub_field('cp_module_part_button_2');
$video = get_sub_field('cp_module_part_video');

// iframe
$iframe = get_sub_field('cp_module_part_iframe');

?>

<div id="<?php echo 'module-' . $index; ?>" class="module module-50-50 module-setting-image-side-<?php echo $image_side; ?> module-setting-width-<?php echo $width; ?> module-setting-bg-<?php echo $bg_color; ?> <?php echo 'module-' . $index; ?> <?php echo $padded; ?>" style="<?php echo $custom_bg ? 'background-color:' . $custom_bg . ';' : ''; ?>">

	<?php if($width == 'contained') : ?>
		<div class="container">
	<?php endif; ?>

	<div class="flex">
		
		<?php if ( $content ) : ?>
			<div class="module-part-content">
				<div class="module-part-content-wrap">

					<?php if ($title) : ?>
						<div class="module-part-heading">
							<?php if ($pretitle) : ?>
								<p class="module-part-pretitle"><?php echo $pretitle; ?></p>
							<?php endif; ?>
							<h2 class="module-part-title"><?php echo $title; ?></h2>
						</div>
					<?php endif; ?>
					
					<div class="flush">
							
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

						<?php echo $content; ?>

						<?php if($button || $button2) : ?>

							<div class="module-part-buttons">
								<?php if ( $button ) : ?>
									<a href="<?php echo $button['url']; ?>" target="<?php echo $button['target']; ?>" class="btn-primary"><?php echo $button['title']; ?></a>
								<?php endif; ?>

								<?php if ( $button2 ) : ?>
									<a href="<?php echo $button2['url']; ?>" target="<?php echo $button2['target']; ?>" class="btn-primary-outline"><?php echo $button2['title']; ?></a>
								<?php endif; ?>
							</div>

						<?php endif; ?>

					</div>
				</div>
			</div>
		<?php endif ?>

		<div class="module-part-image">

			<?php if($content_type == 'default' || $content_type == 'video') : ?>
				<?php if ( $image ) : ?>
					<?php echo wp_get_attachment_image($image['ID'], 'full', false, [
						'class' => 'image ' . $image_position,
						'sizes' => '(min-width: 992px) 100vw'
					]); ?>
				<?php endif ?>
			<?php endif; ?>
			
			<?php if($content_type == 'video') : ?>
				<button class="btn-reset btn-play" type="button" data-a11y-dialog-show="modal-<?php echo $index; ?>"><span class="sr-only">Play video</span><svg xmlns="http://www.w3.org/2000/svg" width="30" height="43" viewBox="0 0 30 43"><path fill="#F04E38" fill-rule="evenodd" d="M30 21.5L0 43V0z"/></svg></button>
			<?php endif; ?>

			<?php if($content_type == 'iframe') : ?>
				<?php echo $iframe; ?>
			<?php endif; ?>

		</div>

		<?php if($content_type == 'video') : ?>
			
			<div class="modal modal-move" id="modal-<?php echo $index; ?>" aria-hidden="true">

				<div class="modal-backdrop" tabindex="-1" data-a11y-dialog-hide></div>

				<div class="modal-dialog" role="dialog" aria-labelledby="modal-<?php echo $index; ?>-title">
					
					<div class="modal-header">
						<button type="button" class="btn-reset" data-a11y-dialog-hide aria-label="Close this dialog window">
							<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28"><path fill="#FFF" fill-rule="evenodd" d="M24.704.565L14.077 11.193 4.541.565a1.934 1.934 0 0 0-2.734 2.733l9.536 10.629L.566 24.7A1.934 1.934 0 0 0 3.3 27.435L13.93 16.81l9.537 10.624a1.93 1.93 0 1 0 2.73-2.734l-9.54-10.624L27.434 3.3a1.93 1.93 0 1 0-2.73-2.733z"/></svg>
						</button>
					</div>
					
					<div class="modal-content" role="document">
						
						<h1 id="modal-<?php echo $index; ?>-title" class="sr-only">Watch video</h1>
						
						<div class="embed-container" data-video-embed="<?php echo htmlentities(add_iframe_attrs($video)); ?>">
						</div>

					</div>
				</div>

			</div>

		<?php endif; ?>

	</div>

	<?php if($width == 'contained') : ?>
		</div>
	<?php endif; ?>

</div><!-- /.module-50-50 -->
