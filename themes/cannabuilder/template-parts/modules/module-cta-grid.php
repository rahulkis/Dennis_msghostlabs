<?php
/**
 * Template part for displaying the CTA grid module
 *
 * @link https://www.advancedcustomfields.com/resources/flexible-content/
 *
 * @package CannaBuilder
 */

// index
$index = $args['index'];

// Background color from radio field
$bg_color = get_sub_field('cp_module_setting_background_color');
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

?>
<?php if ( have_rows('cp_module_part_ctas') ) : ?>
<div id="<?php echo 'module-' . $index; ?>" class="module module-cta-grid module-setting-bg-<?php echo $bg_color; ?> <?php echo 'module-' . $index; ?>">
	<div class="flex-col <?php echo $flex_col; ?>">
		<?php while ( have_rows('cp_module_part_ctas') ) : the_row(); 
			$url = get_sub_field('cp_module_part_url');
			$title = get_sub_field('cp_module_part_title');
			$description = get_sub_field('cp_module_part_description');
			$image = get_sub_field('cp_module_part_image');
			$horizontal = get_sub_field('cp_module_setting_horizontal_focus_point');
			$vertical = get_sub_field('cp_module_setting_vertical_focus_point');
			$image_position = cp_format_image_position($horizontal, $vertical);
		?>
			<a href="<?php echo $url; ?>" class="flex-col-item cta cover">
				<div class="cta-wrap">

					<?php if($image) : ?>
						<?php echo wp_get_attachment_image($image['ID'], 'medium', false, [
							'class' => 'cover-image ' . $image_position,
							'sizes' => '(min-width: 992px) 500px'
						]); ?>
					<?php endif; ?>

					<div class="cover-content cta-content flush">
						<?php if ( $title ) : ?>
							<h3 class="cta-title"><?php echo $title; ?></h3><!-- /.title -->
						<?php endif ?>
						<?php if ( $description ) : ?>
							<div class="cta-description flush">
								<p><?php echo $description; ?></p>
							</div><!-- /.description -->
						<?php endif ?>
					</div><!-- /.content-wrap -->
				</div>
			</a><!-- /.cta -->
		<?php endwhile; ?>
	</div><!-- /.ctas -->
</div><!-- /.module-cta-grid -->
<?php else : ?>

    <?php // no rows found ?>

<?php endif; ?>
