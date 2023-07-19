<?php
/**
 * Template part for displaying a gallery
 *
 * @link https://www.advancedcustomfields.com/resources/flexible-content/
 *
 * @package CannaBuilder
 */

// index
$index = $args['index'];

//Text
$title = get_sub_field('cp_module_part_title');
$description = get_sub_field('cp_module_part_description');

//Gallery Type
$gallery_type = get_sub_field('cp_module_setting_gallery_type');

//Gallery Images
$gallery_images = get_sub_field('cp_module_part_gallery');

// Background color from radio field
$bg_color = get_sub_field('cp_module_setting_background_color');

?>

<div id="<?php echo 'module-' . $index; ?>" class="module module-gallery module-padded-top module-padded-bottom module-setting-bg-<?php echo $bg_color; ?> <?php echo $gallery_type; ?> <?php echo 'module-' . $index; ?>">

	
	<?php if ($title) : ?>
		<div class="module-part-heading flush padded">
			<?php if ($title) : ?>
				<h2 class="module-part-title" data-widowfix><?php echo $title; ?></h2>
			<?php endif; ?>

			<?php if ($description) : ?>
				<?php echo $description; ?>
			<?php endif; ?>

		</div>
	<?php endif; ?>

	<?php if($gallery_type == 'grid' || $gallery_type == 'masonry') : ?>

		<?php if ($gallery_images ) : ?>
			<div class="container">
				<div class="gallery clearfix" itemscope itemtype="http://schema.org/ImageGallery">

					<?php foreach ($gallery_images as $gallery_image): ?>
						<figure class="gallery-image" itemprop="associatedMedia" itemscope itemtype="http://schema.org/ImageObject">
							<a href="<?php echo $gallery_image['sizes']['large']; ?>" itemprop="contentUrl" data-size="<?php echo $gallery_image['sizes']['large-width'] . 'x' . $gallery_image['sizes']['large-height'] ?>">
								
								<?php if($gallery_type == 'grid') : ?>
									<?php echo wp_get_attachment_image($gallery_image['ID'], 'medium', false, ['sizes' => '(min-width: 992px) 560px, (min-width: 768px) 33.3vw, 300px']); ?>
								<?php else : ?>
									<?php echo wp_get_attachment_image($gallery_image['ID'], 'medium_large', false, ['sizes' => '(min-width: 992px) 33vw, 50vw']); ?>
								<?php endif; ?>

							</a>
							<?php if ( $gallery_image['caption'] ) : ?>
								<figcaption itemprop="caption description"><?php echo $gallery_image['caption']; ?></figcaption>
							<?php endif ?>
						</figure><!-- /.gallery-image -->
					<?php endforeach ?>
				</div><!-- /.gallery -->
			</div>
		<?php endif ?>

	<?php endif; ?>

	<?php if($gallery_type == 'logos') : ?>

		<?php if( have_rows('cp_module_part_logos') ): ?>

			<div class="container">
				
				<div class="module-part-logos flex">
					
					<?php while( have_rows('cp_module_part_logos') ): the_row(); ?>

						<?php
							$logo = get_sub_field('cp_module_part_logo');
							$url = get_sub_field('cp_module_part_url');
						?>

						<div class="flex-item">
							<?php if($url) : ?>
								<a href="<?php echo $url; ?>" target="_blank">
							<?php endif; ?>

							<?php if($logo) : ?>
								<img src="<?php echo $logo['url']; ?>" alt="<?php echo $logo['alt']; ?>">
							<?php endif; ?>

							<?php if($url) : ?>
								</a>
							<?php endif; ?>
						</div>

					<?php endwhile; ?>

				</div>

			</div>

		<?php endif; ?>

	<?php endif; ?>

</div>