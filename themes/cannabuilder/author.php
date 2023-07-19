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

$sidebar = get_theme_mod('cp_setting_blog_sidebar', false);
$layout = get_theme_mod('cp_setting_blog_layout', 'columns');
$post_list_columns = '';
$show_excerpt = true;
if ($layout == 'columns') {
    $post_list_columns = 'flex-col flex-col-2 flex-col-3';
    $show_excerpt = false;
}

$author = $wp_query->get_queried_object();

$author_id = get_the_author_meta('ID');
$author_job_title = get_field('cp_author_job_title', 'user_' . $author_id);
$author_description = get_field('cp_author_description', 'user_' . $author_id);
$author_quote = get_field('cp_author_quote', 'user_' . $author_id);
$author_image = get_field('cp_author_image', 'user_' . $author_id);
$linkedin = get_the_author_meta('linkedin');

get_header(); ?>

<div class="author-header">
    <div class="container">
        <h1><?php echo $author->display_name; ?></h1>

        <?php if ($author_job_title || $linkedin) : ?>
            <div class="author-sub-header">
                <?php if ($author_job_title) : ?>
                    <div class="author-job-title">
                        <?php echo $author_job_title; ?>
                    </div>
                <?php endif; ?>
                <?php if ($linkedin) : ?>
                    <a class="author-link" href="<?php echo $linkedin; ?>" target="_blank"><?php get_template_part('template-parts/icons/icon', 'linkedin'); ?> <span>Linkedin</span></a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($author_quote) : ?>
            <div class="author-quote">

                <div class="author-quote-image">
                    <?php if ($author_image) : ?>
                        <?php echo wp_get_attachment_image($author_image['ID'], 'medium_large', false, [
                            'class' => 'position-50-50',
                        ]); ?>
                    <?php endif; ?>
                </div>

                <div class="author-quote-text">
                    <div class="author-quote-text-content flush">
                        <?php echo $author_quote; ?>
                    </div>
                    <div class="author-quote-title flush">
                        <p><strong><?php echo $author->display_name; ?></strong></p>
                        <?php if ($author_job_title) : ?>
                            <p><?php echo $author_job_title; ?></p>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($author_description) : ?>
    <div class="author-description">
        <div class="container">
            <div class="flex">
                <h3>About</h3>
                <div class="flush">
                    <?php echo $author_description; ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="container">

    <div class="post-list">

        <div class="post-list-content-wrap">

            <div class="post-list-content">

                <?php if (have_posts()) : ?>

                    <div class="post-list-content-header flush">
                        <h4>Articles</h4>
                        <p><?php echo $wp_query->found_posts; ?> Articles by <?php echo $author->display_name; ?></p>
                    </div>

                    <div class="post-layout-<?php echo $layout; ?> <?php echo $post_list_columns; ?>" data-load-more-container>

                        <?php while (have_posts()) : the_post(); ?>

                            <?php get_template_part('template-parts/partial', 'post-card', ['show_excerpt' => $show_excerpt]); ?>

                        <?php endwhile; ?>

                    </div>

                <?php else : ?>

                    <h3>No posts found.</h3>

                <?php endif; ?>

            </div>

            <?php if ($sidebar) : ?>
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

<?php get_template_part('template-parts/content', 'page-builder'); ?>

<?php

get_footer();
