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
$autoplay = get_sub_field('cp_module_setting_autoplay');
$autoplay_speed = get_sub_field('cp_module_setting_autoplay_speed');

$title = get_sub_field('cp_module_part_title');
$pretitle = get_sub_field('cp_module_part_pretitle');

?>

<div id="<?php echo 'module-' . $index; ?>" class="module module-testimonial-slider module-padded-top module-padded-bottom module-setting-bg-<?php echo $bg_color; ?> <?php echo 'module-' . $index; ?>">

	<?php if (have_rows('cp_module_part_testimonials')) : ?>

		<div class="module-part-content">

			<?php if ($title) : ?>
				<div class="module-part-heading">
					<?php if ($pretitle) : ?>
						<p class="module-part-pretitle"><?php echo $pretitle; ?></p>
					<?php endif; ?>
					<h2 class="module-part-title">
						<span data-widowfix><?php echo $title; ?></span>
					</h2>
				</div>
			<?php endif; ?>

			<div class="swiper testimonial-slider" <?php echo $autoplay ? 'data-autoplay="'.$autoplay_speed.'"' : ''; ?>>

				<div class="swiper-wrapper testimonial-slider-wrapper">

					<?php while (have_rows('cp_module_part_testimonials')) : the_row(); ?>

						<?php
						// content
						$photo = get_sub_field('cp_module_part_photo');
						$author_name = get_sub_field('cp_module_part_author_name');
						$author_title = get_sub_field('cp_module_part_author_title');
						$quote = get_sub_field('cp_module_part_quote');
						?>
						<div class="swiper-slide testimonial-slide">

							<div class="slide-content testimonial">

								<blockquote>

									<?php if ($quote) : ?>
										<p><?php echo $quote; ?></p>
									<?php endif; ?>

									<?php if ($author_name) : ?>
										<footer>
											<?php if ($photo) : ?>
												<img src="<?php echo $photo['sizes']['thumbnail']; ?>" alt="photo of <?php echo $author_name; ?>">
											<?php endif; ?>
											<cite>
												<?php echo $author_name; ?>
												<?php if ($author_title) : ?>
													<span><?php echo $author_title; ?></span>
												<?php endif; ?>
											</cite>
										</footer>
									<?php endif; ?>

								</blockquote>

							</div><!-- /.slide-wrap -->

						</div><!-- /.slide -->

					<?php endwhile; ?>

				</div>

			</div><!-- /.slides -->

			<div class="swiper-pagination testimonial-slider-pagination"></div>

		</div>

	<?php endif; ?>

</div>