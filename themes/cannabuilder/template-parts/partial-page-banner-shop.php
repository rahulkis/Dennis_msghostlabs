<?php
/**
 * Template part for displaying page banner image
 *
 *
 * @package CannaBuilder
 */

$page_id = get_option( 'woocommerce_shop_page_id' );

// remove the banner if user selects that option
$disabled = get_field('cp_banner_disabled', $page_id);
if($disabled) {
	return;
}

// remove banner image if user selects that options
$image_status = get_theme_mod( 'cp_setting_interior_banner_bg_image', 'on' );

// image
$horizontal = get_field('horizontal_focus_point', $page_id);
$vertical = get_field('vertical_focus_point', $page_id);
$image_position = cp_format_image_position($horizontal, $vertical);

$title = get_field('cp_banner_title', $page_id);
if(!$title) {
	$title = woocommerce_page_title( false );
}

$subtitle = get_field('cp_banner_subtitle', $page_id);

$image = get_the_post_thumbnail($page_id, 'full', [
	'class' => 'cover-image page-banner-image ' . $image_position
]);

if(is_product_category() || is_product_tag()) {

	$image_status = get_theme_mod( 'cp_setting_product_cat_banner_bg_image', 'on' );

	$term = get_queried_object();
	$title = single_cat_title('', false);
	$cat_image = get_field('cp_product_cat_featured_image', $term);
	if($cat_image) {
		$image = cannabuilder_acf_responsive_image($cat_image, 'full', [], [
			'class' => 'cover-image page-banner-image position-50-50'
		]);
	}
}

?>

<div class="page-banner cover page-banner-text-align-<?php echo $text_align; ?> page-banner-image-<?php echo $image_status; ?>">

	<?php do_action('cp_before_page_full'); ?>

	<?php if($image_status == 'on') : ?>

		<?php if ( $image ) : ?>
			<?php echo $image; ?>
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

			<div class="page-heading-content flush">

				<?php if(is_product_category() || is_product_tag()) : ?>
					<?php do_action( 'woocommerce_archive_description' ); ?>
				<?php else : ?>
					<?php if($subtitle) : ?>
						<p class="page-heading-subtitle"><?php echo $subtitle; ?></p>
					<?php endif; ?>
				<?php endif; ?>

			</div>

		</div><!-- /.content-wrap -->

	</div>

</div><!-- /.page-banner -->