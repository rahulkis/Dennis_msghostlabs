<?php
/**
 * Blog page template
 *
 * This file is used for the blog page
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package CannaBuilder
 */

global $wp_query;

// check for search query
$search = $wp_query->get('s');

get_header(); ?>

<div class="container">

	<h1 class="h2 post-list-title">Search results for: <?php echo $search; ?></h1>
	
	<div class="post-list">

		<div class="post-list-content">

			<?php if ( have_posts() ) : ?>

				<div class="flex-col flex-col-2 flex-col-3" data-load-more-container>

					<?php while ( have_posts() ) : the_post(); ?>

						<?php get_template_part('template-parts/partial', 'post-card'); ?>

					<?php endwhile; ?>

				</div>

			<?php else : ?>

				<h3>No posts found for your search query.</h3>

			<?php endif; ?>

		</div>

		<div class="post-list-footer">
			<?php the_posts_pagination(); ?>
		</div>

	</div>

</div>

<?php

get_footer();