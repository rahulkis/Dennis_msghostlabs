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

// content
$title = get_sub_field('cp_module_part_title');
$pretitle = get_sub_field('cp_module_part_pretitle');
$text = get_sub_field('cp_module_part_text');
$button = get_sub_field('cp_module_part_button');
$products = get_sub_field('cp_module_part_products');

$product_ids = '';
foreach ($products as $product) {
	$product_ids .= $product->ID . ',';
}

$product_count = 'four';
if(count($products) < 4) {
	$product_count = 'three';
}

?>


<div id="<?php echo 'module-' . $index; ?>" class="module module-product-grid module-padded-top module-padded-bottom module-setting-bg-<?php echo $bg_color; ?> <?php echo 'module-' . $index; ?>">

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
		
		<?php if ( $text ) : ?>
			<div class="module-part-content flush padded">
				<?php echo $text; ?>
			</div>
		<?php endif; ?>

		<?php if($product_ids) : ?>
			<div class="module-part-product-list <?php echo $product_count; ?>">
				<?php echo do_shortcode('[products columns="4" ids="'.$product_ids.'" ]'); ?>
			</div>
		<?php endif; ?>

		<?php if ( $button ) : ?>
			<div style="text-align:center;">
				<a href="<?php echo $button['url']; ?>" target="<?php echo $button['target']; ?>" class="btn-primary"><?php echo $button['title']; ?></a>
			</div>
		<?php endif; ?>

	</div>

</div>