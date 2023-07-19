<?php
/**
 * Template part for displaying the WYSIWYG module
 *
 * @link https://www.advancedcustomfields.com/resources/flexible-content/
 *
 * @package CannaBuilder
 */

global $post;

// settings
$index = $args['index'];
$bg_color = get_sub_field('cp_module_setting_background_color');
$columns = get_sub_field('cp_module_setting_columns');

// content
$title = get_sub_field('cp_module_part_title');
$pretitle = get_sub_field('cp_module_part_pretitle');
$team = get_sub_field('cp_module_part_team');

if(!$columns) {
	$columns = '4';
}

if($columns == '3') {
	$flex_col = 'flex-col-3';
}

if($columns == '4') {
	$flex_col = 'flex-col-2 flex-col-4';
}

?>


<div id="<?php echo 'module-' . $index; ?>" class="module module-team module-padded-top module-padded-bottom module-setting-bg-<?php echo $bg_color; ?> <?php echo 'module-' . $index; ?>">

	<div class="container">
		
		<?php if ($title) : ?>
			<div class="module-part-heading padded">
				<?php if ($pretitle) : ?>
					<p class="module-part-pretitle"><?php echo $pretitle; ?></p>
				<?php endif; ?>
				<h2 class="module-part-title">
					<span data-widowfix><?php echo $title; ?></span>
				</h2>
			</div>
		<?php endif; ?>
		
		<?php if($team) : ?>

			<div class="module-part-team <?php echo count($team) < 4 ? 'flex-col-center' : ''; ?> flex-col <?php echo $flex_col; ?>">

				<?php foreach ($team as $member) : ?>

					<?php
						$post = $member;
						setup_postdata($post);

						$horizontal = get_field('horizontal_focus_point');
						$vertical = get_field('vertical_focus_point');
						$image_position = cp_format_image_position($horizontal, $vertical);
						$title = get_the_title($post->ID);
						$job_title = get_field('cp_team_job_title');
						$desc = get_field('cp_team_desc');
						$url = get_field('cp_team_url');
					?>

					<?php if($url) : ?>
						<a href="<?php echo $url; ?>" class="btn-reset team-card flex-col-item">
					<?php else : ?>
						<?php if($desc) : ?>
							<button class="btn-reset team-card flex-col-item" type="button" data-a11y-dialog-show="modal-team-<?php echo $index; ?>-<?php echo $post->ID; ?>">
						<?php else : ?>
							<div class="team-card flex-col-item">
						<?php endif; ?>
					<?php endif; ?>
						<?php the_post_thumbnail('medium_large', ['class' => 'team-card-image ' . $image_position]); ?>
						<div class="team-card-content flush">
							<h3><?php echo $title; ?></h3>
							<?php if($job_title) : ?>
								<p><?php echo $job_title; ?></p>
							<?php endif; ?>
						</div>
					
					<?php if($url) : ?>
						</a>
					<?php else : ?>
						<?php if($desc) : ?>
							</button>
						<?php else : ?>
							</div>
						<?php endif; ?>
					<?php endif; ?>
					
					<?php if($desc && !$url) : ?>
					<div class="modal modal-team modal-move" id="modal-team-<?php echo $index; ?>-<?php echo $post->ID; ?>" aria-hidden="true" data-modal-slug-name="profile" data-modal-slug-value="<?php echo cannabuilder_slugify($title); ?>">

						<div class="modal-backdrop" tabindex="-1" data-a11y-dialog-hide></div>

						<div class="modal-dialog" role="dialog" aria-labelledby="modal-team-<?php echo $index; ?>-<?php echo $post->ID; ?>-title">
							
							<div class="modal-header">
								<button type="button" class="btn-reset" data-a11y-dialog-hide aria-label="Close this dialog window">
									<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28"><path fill="#FFF" fill-rule="evenodd" d="M24.704.565L14.077 11.193 4.541.565a1.934 1.934 0 0 0-2.734 2.733l9.536 10.629L.566 24.7A1.934 1.934 0 0 0 3.3 27.435L13.93 16.81l9.537 10.624a1.93 1.93 0 1 0 2.73-2.734l-9.54-10.624L27.434 3.3a1.93 1.93 0 1 0-2.73-2.733z"/></svg>
								</button>
							</div>

							<div class="modal-image">
								<?php the_post_thumbnail('full', ['class' => $image_position]); ?>
							</div>
							
							<div class="modal-content" role="document">
								
								<h1 id="modal-team-<?php echo $index; ?>-<?php echo $post->ID; ?>-title"><?php echo $title; ?></h1>

								<?php if($job_title) : ?>
									<p><?php echo $job_title; ?></p>
								<?php endif; ?>

								<?php echo $desc; ?>
								
							</div>
						</div>

					</div>

					<?php endif; ?>

				<?php endforeach; ?>

				<?php wp_reset_postdata(); ?>
				
			</div>
		<?php endif; ?>

	</div>

</div>