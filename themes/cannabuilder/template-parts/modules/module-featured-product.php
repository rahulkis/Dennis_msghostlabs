<?php
/**
 * Template part for displaying the WYSIWYG module
 *
 * @link https://www.advancedcustomfields.com/resources/flexible-content/
 *
 * @package CannaBuilder
 */

// don't show if woocommerce is inactive
if ( !class_exists( 'WooCommerce' ) ) {
	return;
}

// settings
$index = $args['index'];
$bg_color = get_sub_field('cp_module_setting_background_color');
$columns = get_sub_field('cp_module_setting_columns');
$product = get_sub_field('cp_module_setting_product');

// content
$image = get_sub_field('cp_module_part_image');
$pretitle = get_sub_field('cp_module_part_pretitle');
$title = get_sub_field('cp_module_part_title');
$text = get_sub_field('cp_module_part_text');
$button = get_sub_field('cp_module_part_button');

if($product) {
	$product = wc_get_product($product->ID);

	$review_count = $product->get_review_count();
	$rating_count = $product->get_rating_counts();

	$has_reviews = false;
	if( $review_count || $rating_count) {
		$has_reviews = true;
	}
}

$button_class = 'btn-primary';
if($bg_color == 'brand') {
	$button_class = 'btn-white-outline';
}

?>


<div id="<?php echo 'module-' . $index; ?>" class="module module-featured-product module-padded-top module-padded-bottom module-setting-bg-<?php echo $bg_color; ?> <?php echo 'module-' . $index; ?>">

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

		<div class="module-part-content padded flush">
			
			<?php if($product) : ?>

				<?php if($has_reviews) : ?>
					<div class="flex woocommerce">

						<?php if($rating_count) : ?>
							<div class="flex-item">
								<p><?php echo wc_get_rating_html( $product->get_average_rating()); ?></p>
							</div>
						<?php endif; ?>
							
						<?php if($review_count) : ?>
							<div class="flex-item">
								<p>(<?php echo $product->get_review_count(); ?> Customer Review<?php echo $review_count > 1 ? 's' : ''; ?>)</p>
							</div>
						<?php endif; ?>
					</div>
				<?php endif; ?>
				
				<p><?php echo $product->get_price_html(); ?></p>

			<?php endif; ?>

			<?php if ( $button ) : ?>
				<a href="<?php echo $button['url']; ?>" target="<?php echo $button['target']; ?>" class="<?php echo $button_class; ?>"><?php echo $button['title']; ?></a>
			<?php endif; ?>

		</div>

		<?php if ( $image ) : ?>
			<div class="module-part-image">
				<img src="<?php echo $image['url']; ?>" alt="<?php echo $image['alt']; ?>">
			</div>
		<?php endif ?>

	</div>

</div>