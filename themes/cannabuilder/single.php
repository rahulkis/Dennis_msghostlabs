<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package CannaBuilder
 */

$cats = get_the_category();
if($cats) {
	$cat = $cats[0];
}

$featured_image = get_theme_mod( 'cp_setting_blog_featured_image', true );

$post_date = get_theme_mod( 'cp_setting_blog_post_date', true );

get_header(); ?>

	<?php while ( have_posts() ) : the_post(); ?>

	<div class="container">
		
		<div class="post-detail">
			
			<div class="post-detail-header">
				<h1><?php the_title(); ?></h1>

				<?php if($featured_image) : ?>
					<div class="post-detail-featured-image">
						<?php the_post_thumbnail(); ?>
					</div>
				<?php endif; ?>

				<?php if($post_date) : ?>
					<p>Posted on <?php the_date('F jS, Y'); ?>
						<?php if($cats) : ?>
							to <a href="<?php echo get_term_link($cat->term_id); ?>"><?php echo $cat->name; ?></a> by <?php the_author_posts_link(); ?>
						<?php endif; ?>
					</p>
				<?php endif; ?>

			</div>

			<div class="post-detail-content flush">
				<?php the_content(); ?>
			</div>

			<div class="post-detail-footer">
				<div class="flex">
					<div class="flex-item">
						<h3>Like it? Share it!</h3>
					</div>
					<div class="flex-item">
						<?php get_template_part('template-parts/partial', 'social-share'); ?>
					</div>
				</div>
			</div>
		</div>

	</div>

	<?php get_template_part('template-parts/partial', 'related-posts'); ?>
		
	<?php endwhile; ?>

<?php

get_footer();