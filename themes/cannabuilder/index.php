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

// id of blog page
$my_blog_id = get_option('page_for_posts');

// check for search query
$search = $wp_query->get('s');

// get active category name
$default_category_button_text = 'All Categories';
$active_category_button_text = $default_category_button_text;

// if we are on a category page,
// get the current category info
$term_slug = '';
if(is_category()) {
	$active_category_button_text = single_cat_title('', false);

	// get current category slug
	$term_slug = $wp_query->get_queried_object()->slug;
}

$sidebar = get_theme_mod( 'cp_setting_blog_sidebar', false );
$layout = get_theme_mod( 'cp_setting_blog_layout', 'columns' );
$post_list_columns = '';
$show_excerpt = true;
if($layout == 'columns') {
	$post_list_columns = 'flex-col flex-col-2 flex-col-3';
	$show_excerpt = false;
}

get_header(); ?>

<?php get_template_part('template-parts/partial', 'page-banner'); ?>

<div class="container">
	
	<div class="post-list">
		
		<div class="post-list-header">

			<?php if( have_rows('cp_category_list', $my_blog_id) ): ?>

				<div class="post-list-filter">

					<button type="button" class="post-list-filter-toggle dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						<span><?php echo $active_category_button_text; ?></span> <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="16" height="10" viewBox="0 0 16 10"><defs><path id="fkq0a" d="M1314.16 925.85l6.95-8.48a.59.59 0 0 0 0-.72c-.16-.2-.42-.2-.59 0l-6.65 8.12-6.65-8.12c-.17-.2-.43-.2-.6 0a.57.57 0 0 0-.12.36c0 .12.04.26.13.35l6.95 8.49c.16.2.42.2.58 0z"/></defs><g><g transform="translate(-1306 -916)"><use fill="#1e2f43" xlink:href="#fkq0a"/></g></g></svg>
					</button>

					<ul class="post-list-filter-dropdown dropdown-menu">

						<li><a href="<?php echo get_permalink($my_blog_id); ?>"><?php echo $default_category_button_text; ?></a></li>

						<?php while( have_rows('cp_category_list', $my_blog_id) ): the_row(); 

							$cat = get_sub_field('cp_category');
							if(!$cat) {
								break;
							}

							?>

							<li><a href="<?php echo get_term_link($cat->term_id); ?>"><?php echo $cat->name; ?></a></li>

						<?php endwhile; ?>

					</ul>

				</div>

			<?php endif; ?>
			
		</div>

		<div class="post-list-content-wrap">
			
			<div class="post-list-content">

				<?php if ( have_posts() ) : ?>

					<div class="post-layout-<?php echo $layout; ?> <?php echo $post_list_columns; ?>" data-load-more-container>

						<?php while ( have_posts() ) : the_post(); ?>

							<?php get_template_part('template-parts/partial', 'post-card', ['show_excerpt' => $show_excerpt]); ?>

						<?php endwhile; ?>

					</div>

				<?php else : ?>

					<h3>No posts found.</h3>

				<?php endif; ?>

			</div>

			<?php if($sidebar) : ?>
				<div class="post-list-sidebar">
					<?php get_sidebar('blog'); ?>
				</div>
			<?php endif; ?>

		</div>

		<div class="post-list-footer">
			<?php the_posts_pagination(); ?>
		</div>

	</div>

</div>

<?php get_template_part( 'template-parts/content', 'page-builder' ); ?>

<?php

get_footer();