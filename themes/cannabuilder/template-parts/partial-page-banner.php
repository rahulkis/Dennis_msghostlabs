<?php
/**
 * Template part for displaying page banner image
 *
 *
 * @package CannaBuilder
 */

global $wp_query;

// check for search query
$search = $wp_query->get('s');

// set up our page ID
global $post;
$page_id = $post->ID;

// change page_id if we are on the blog or category pages
if(is_home() || is_category() || is_tag()) {
	$page_id = get_option('page_for_posts');
}

// remove the banner if user selects that option
$disabled = get_field('cp_banner_disabled', $page_id);
if($disabled) {
	return;
}

// remove banner image if user selects that options
$image_status = get_theme_mod( 'cp_setting_interior_banner_bg_image', 'on' );

// force image status to be on for front page
if(is_front_page()) {
	$image_status = 'on';
}

// image
$horizontal = get_field('horizontal_focus_point', $page_id);
$vertical = get_field('vertical_focus_point', $page_id);
$image_position = cp_format_image_position($horizontal, $vertical);

// content
$title = get_field('cp_banner_title', $page_id);
$subtitle = get_field('cp_banner_subtitle', $page_id);
$button = get_field('cp_banner_button', $page_id);
$button_2 = get_field('cp_banner_button_2', $page_id);
$button_3 = get_field('cp_banner_button_3', $page_id);

// $buttons = [
// 	get_field('cp_banner_button', $page_id),
// 	get_field('cp_banner_button_2', $page_id),
// 	get_field('cp_banner_button_3', $page_id)
// ];

// use default page title if the override is not set
if(!$title) {
	$title = get_the_title($page_id);
}

// home banner text align
$text_align = get_theme_mod( 'cp_setting_home_banner_text_align', '' );
if(!$text_align) {
	$text_align = 'center';
}

// button style
$button_style = get_theme_mod( 'cp_setting_home_banner_button_style', '' );
if(!$button_style) {
	$button_style = 'btn-primary';
}

// button 2 style
$button_2_style = get_theme_mod( 'cp_setting_home_banner_button_2_style', '' );
if(!$button_2_style) {
	$button_2_style = 'btn-primary-outline';
}

// search title
if($search) {
	$title = 'Search results for: ' . $search;
}

// category or tag title
if (is_category() || is_tag()) {
	$title = single_term_title('', false);
}

?>

<div class="page-banner cover page-banner-text-align-<?php echo $text_align; ?>">

	<?php do_action('cp_before_page_full'); ?>

	<?php if($image_status == 'on') : ?>

		<?php do_action('cp_before_page_banner_image'); ?>

		<?php if ( has_post_thumbnail($page_id) ) : ?>
			<?php echo get_the_post_thumbnail($page_id, 'full', [
				'class' => 'cover-image page-banner-image ' . $image_position,
			]); ?>
		<?php else : ?>
			<?php
				$default_thumbnails = get_field('theme_settings_default_featured_images', 'option');
				$random_image = $default_thumbnails[rand(0, count($default_thumbnails) - 1)];
				echo cannabuilder_acf_responsive_image($random_image, 'full', [], [
					'class' => 'cover-image page-banner-image position-50-50'
				]);
			?>
		<?php endif; ?>

	<?php endif; ?>

	<div class="container">
		<div class="page-banner-content flush cover-content">

			<h1 class="page-banner-title"><?php echo $title; ?></h1>

			<?php if ( $subtitle ) : ?>
				<p class="page-banner-subtitle"><?php echo $subtitle; ?></p>
			<?php endif ?>

			<?php if ( $button || $button_2 || $button_3 ) : ?>
				<div class="page-banner-buttons">
					<?php if ( $button ) : ?>
						<a href="<?php echo $button['url']; ?>" target="<?php echo $button['target']; ?>" class="<?php echo $button_style; ?> page-banner-button-1"><?php echo $button['title']; ?></a>
					<?php endif; ?>

					<?php if ( $button_2 ) : ?>
						<a href="<?php echo $button_2['url']; ?>" target="<?php echo $button_2['target']; ?>" class="<?php echo $button_2_style; ?> page-banner-button-2"><?php echo $button_2['title']; ?></a>
					<?php endif; ?>

					<?php if ( $button_3 ) : ?>
						<a href="<?php echo $button_3['url']; ?>" target="<?php echo $button_3['target']; ?>" class="<?php echo $button_2_style; ?> page-banner-button-2"><?php echo $button_3['title']; ?></a>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div><!-- /.content-wrap -->
	</div>
	
	<?php if(is_front_page()) : ?>
		<a href="#module-1" class="page-banner-scroll"><span>Scroll</span><br><svg xmlns="http://www.w3.org/2000/svg" width="15" height="23" viewBox="0 0 15 23"><path fill="#FFF" d="M14.7744 15.488c.3008-.3134.3008-.8349 0-1.1592-.2906-.3133-.7743-.3133-1.0643 0l-5.4514 5.8775V.8115C8.2581.3594 7.9247 0 7.5054 0c-.4193 0-.7635.3594-.7635.8115v19.3948l-5.4412-5.8775c-.3008-.3133-.7852-.3133-1.0751 0-.3008.3243-.3008.8465 0 1.1591l6.742 7.2689c.2906.3243.7742.3243 1.0642 0l6.7426-7.2689z"/></svg></a>
	<?php endif; ?>

</div><!-- /.page-banner -->
