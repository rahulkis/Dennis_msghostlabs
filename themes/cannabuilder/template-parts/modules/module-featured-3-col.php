<?php
/**
 * Template part for displaying the featured 3 col module
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
$image_display = get_sub_field('cp_module_setting_image_display');
$columns = get_sub_field('cp_module_setting_columns');

if(!$columns) {
	$columns = '3';
}

if($columns == '2') {
	$flex_col = 'flex-col-2';
}

if($columns == '3') {
	$flex_col = 'flex-col-3';
}

if($columns == '4') {
	$flex_col = 'flex-col-2 flex-col-4';
}

$count = 1;
?>

<?php if ( have_rows('cp_module_part_columns') ) : ?>
<div id="<?php echo 'module-' . $index; ?>" class="module module-featured-3-col module-padded-top module-padded-bottom module-setting-bg-<?php echo $bg_color; ?> module-setting-image-display-<?php echo $image_display; ?> <?php echo 'module-' . $index; ?>">

	<div class="container">

		<?php if($title || $description) : ?>
			<div class="module-part-content flush">

				<?php if ($pretitle) : ?>
					<p class="module-part-pretitle"><?php echo $pretitle; ?></p>
				<?php endif; ?>

				<?php if ( $title ) : ?>
					<h2 class="module-part-title" data-widowfix><?php echo $title; ?></h2>
				<?php endif; ?>

				<?php if ( $description ) : ?>
					<?php echo $description; ?>
				<?php endif; ?>

			</div>
		<?php endif; ?>
		
		<div class="columns flex-col <?php echo $flex_col; ?>">
			<?php while ( have_rows('cp_module_part_columns') ) : the_row(); 
				$content_type = get_sub_field('cp_module_setting_content_type');
				$title = get_sub_field('cp_module_part_title');
				$description = get_sub_field('cp_module_part_description');
				$image = get_sub_field('cp_module_part_image');
				$horizontal = get_sub_field('cp_module_setting_horizontal_focus_point');
				$vertical = get_sub_field('cp_module_setting_vertical_focus_point');
				$image_position = cp_format_image_position($horizontal, $vertical);
				$video = get_sub_field('cp_module_part_video');
				$button = get_sub_field('cp_module_part_button');
			?>
				<div class="column flex-col-item">

					<?php if($image) : ?>
						<div class="column-image">

							<?php if ( $button ) : ?>
								<a href="<?php echo $button['url']; ?>" target="<?php echo $button['target']; ?>">
							<?php endif; ?>
								<?php echo cannabuilder_acf_responsive_image($image, 'medium_large', [
									'992px' => '33.3333px'
								], [
									'class' => $image_position
								]); ?>
							<?php if ( $button ) : ?>
								</a>
							<?php endif; ?>
							
							<?php if($content_type == 'video') : ?>
								<button class="btn-reset btn-play" type="button" data-a11y-dialog-show="modal-<?php echo $index; ?>-<?php echo $count; ?>"><span class="sr-only">Play video</span><svg xmlns="http://www.w3.org/2000/svg" width="30" height="43" viewBox="0 0 30 43"><path fill="#F04E38" fill-rule="evenodd" d="M30 21.5L0 43V0z"/></svg></button>
							<?php endif; ?>
						</div>
					<?php endif; ?>

					<div class="column-content">
						<div class="column-content-header flush">
							<?php if ( $title ) : ?>
								<h3 class="column-title"><?php echo $title; ?></h3>
							<?php endif ?>
							<?php if($description) : ?>
								<?php echo $description; ?>
							<?php endif; ?>
						</div>
						
						<?php if ( $button ) : ?>
							<div class="column-content-footer">
								<a href="<?php echo $button['url']; ?>" target="<?php echo $button['target']; ?>" class="btn-primary"><?php echo $button['title']; ?></a>
							</div>
						<?php endif; ?>
					</div>
				</div><!-- /.cta -->
				
				<?php if($content_type == 'video') : ?>
					
					<div class="modal modal-move" id="modal-<?php echo $index; ?>-<?php echo $count; ?>" aria-hidden="true">

						<div class="modal-backdrop" tabindex="-1" data-a11y-dialog-hide></div>

						<div class="modal-dialog" role="dialog" aria-labelledby="modal-<?php echo $index; ?>-<?php echo $count; ?>-title">
							
							<div class="modal-header">
								<button type="button" class="btn-reset" data-a11y-dialog-hide aria-label="Close this dialog window">
									<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28"><path fill="#FFF" fill-rule="evenodd" d="M24.704.565L14.077 11.193 4.541.565a1.934 1.934 0 0 0-2.734 2.733l9.536 10.629L.566 24.7A1.934 1.934 0 0 0 3.3 27.435L13.93 16.81l9.537 10.624a1.93 1.93 0 1 0 2.73-2.734l-9.54-10.624L27.434 3.3a1.93 1.93 0 1 0-2.73-2.733z"/></svg>
								</button>
							</div>
							
							<div class="modal-content" role="document">
								
								<h1 id="modal-<?php echo $index; ?>-<?php echo $count; ?>-title" class="sr-only">Watch video</h1>
								
								<div class="embed-container" data-video-embed="<?php echo htmlentities(add_iframe_attrs($video)); ?>">
								</div>

							</div>
						</div>

					</div>

				<?php endif; ?>

				<?php $count++; ?>

			<?php endwhile; ?>
		</div><!-- /.ctas -->
		
	</div>
	
</div><!-- /.module-cta-grid -->

<?php endif; ?>
