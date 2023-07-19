<?php
/**
 * Template Name: Linktree
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site may use a
 * different template.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package CannaBuilder
 */

get_header('landing'); ?>

	<?php while ( have_posts() ) : the_post(); ?>

		<div class="linktree-content">

			<?php
				$logo = get_field('cp_linktree_logo');
				$title = get_field('cp_linktree_title');
				$bg_color = get_field('cp_linktree_background_color');
			?>

			<?php if($bg_color) : ?>
				<style>
					body {
						background-color: <?php echo $bg_color; ?>;
					}
				</style>
			<?php endif; ?>

			<?php if($logo) : ?>
				<img src="<?php echo $logo['sizes']['medium']; ?>" alt="<?php echo $logo['alt']; ?>" class="linktree-logo">
			<?php endif; ?>

			<?php if($title) : ?>
				<h1><?php echo $title; ?></h1>
			<?php endif; ?>

			<?php if( have_rows('cp_linktree_links') ): ?>

				<?php while( have_rows('cp_linktree_links') ): the_row(); ?>

					<?php $link = get_sub_field('cp_linktree_link'); ?>

					<a class="linktree-link" href="<?php echo $link['url']; ?>"><?php echo $link['title']; ?></a>

				<?php endwhile; ?>
			<?php endif; ?>

		</div>
			
	<?php endwhile; ?>

<?php

get_footer('landing');