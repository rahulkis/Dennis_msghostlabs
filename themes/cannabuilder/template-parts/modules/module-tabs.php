<?php
/**
 * Template part for displaying the full tabs module
 *
 * @link https://www.advancedcustomfields.com/resources/flexible-content/
 *
 * @package CannaBuilder
 */

// index
$index = $args['index'];

// Background color from radio field
$bg_color = get_sub_field('cp_module_setting_background_color');

// title from text field
$pretitle = get_sub_field('cp_module_part_pretitle');
$title = get_sub_field('cp_module_part_title');

// description from text field
$description = get_sub_field('cp_module_part_description');

$tabs = get_sub_field('cp_module_part_tabs');

?>

<div id="<?php echo 'module-' . $index; ?>" class="module module-tabs module-padded-top module-padded-bottom module-setting-bg-<?php echo $bg_color; ?> <?php echo 'module-' . $index; ?> ">

	<div class="container">

		<div class="module-part-content padded">

			<div class="module-part-text">
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

			<div class="module-part-content-wrap">

				<div class="tabs">
					
					<div class="tabs-dropdown">

						<button class="tabs-dropdown-toggle" aria-haspopup="true" aria-expanded="false" data-toggle="dropdown">
							<span class="active"><?php echo $tabs[0]['title']; ?></span>
							<img src="<?php echo get_template_directory_uri() . '/dist/img/modules/tabs/mobile-dropdown-chevron.svg'; ?>" alt="Mobile Dropdown" class="chevron" />
						</button>

						<?php $i = 0; ?>
						<ul class="tabs-nav" role="tablist">
						    <?php while ( have_rows('cp_module_part_tabs') ) : the_row(); ?>
								<?php $title = get_sub_field('title'); ?>
								<li class="tabs-nav-item <?php echo ($i == 0 ? 'active' : ''); ?>">
									<a href="<?php echo '#tab-' . $index . '-' . cannabuilder_slugify($title); ?>" data-toggle="tab" role="tab" data-smooth-scroll="noscroll" aria-controls="<?php echo cannabuilder_slugify($title); ?>" class="tabs-nav-item-link"><?php echo $title; ?></a>
								</li><!-- /.nav-item -->
								<?php $i++; ?>
							<?php endwhile; ?>
						</ul><!-- /.nav-tabs -->

					</div>

					<?php if ( have_rows('cp_module_part_tabs') ) : ?>
						<?php $i = 0; ?>
						<div class="tabs-content">
						    <?php while ( have_rows('cp_module_part_tabs') ) : the_row(); ?>
								<?php
									$title = get_sub_field('title');
									$content = get_sub_field('content');
								?>
								<div class="tabs-content-pane <?php echo ($i == 0 ? 'active' : ''); ?>" id="<?php echo 'tab-' . $index . '-' . cannabuilder_slugify($title); ?>" role="tabpanel">
									<?php echo $content; ?>
								</div><!-- /.tab-pane -->
								<?php $i++; ?>
							<?php endwhile; ?>
						</div><!-- /.tab-content -->
					<?php endif; ?>

				</div>

			</div>
		</div>

	</div>
	
</div><!-- /.module-tabs -->
