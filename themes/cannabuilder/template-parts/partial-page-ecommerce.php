<?php
/**
 * Template part for displaying ecommerce sections
 *
 *
 * @package CannaBuilder
 */
?>

<?php if ( class_exists( 'WooCommerce' ) ) : ?>

	<?php if(is_cart()) : ?>
		<div id="module-cart" class="module module-text module-setting-bg-white module-setting-columns-long module-padded-top module-padded-bottom">
			<div class="container">
				<div class="module-part-content">
					<?php echo do_shortcode('[woocommerce_cart]'); ?>
				</div>
			</div>
		</div>
	<?php endif; ?>

	<?php if(is_checkout()) : ?>
		<div id="module-checkout" class="module module-text module-setting-bg-white module-padded-top module-padded-bottom">
			<div class="container">
				<?php echo do_shortcode('[woocommerce_checkout]'); ?>
			</div>
		</div>
	<?php endif; ?>

	<?php if(is_account_page()) : ?>
		<div id="module-account" class="module module-text module-setting-bg-white module-padded-top module-padded-bottom">
			<div class="container">
				<?php echo do_shortcode('[woocommerce_my_account]'); ?>
			</div>
		</div>
	<?php endif; ?>
	
	

<?php endif; ?>