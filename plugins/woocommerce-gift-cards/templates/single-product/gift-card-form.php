<?php
/**
 * Gift Card Form
 *
 * Shows the additional form fields on the product page.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/gift-card-form.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce Gift Cards
 * @version 1.3.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_gc_before_form', $product );

?><div class="woocommerce_gc_giftcard_form">
	<?php wp_nonce_field( 'add_to_cart_giftcard', 'security' ); ?>
	<?php if ( $product->is_sold_individually() ) : ?>
		<div class="wc_gc_field wc_gc_giftcard_to">
			<label for="wc_gc_giftcard_to"><?php esc_html_e( 'To', 'woocommerce-gift-cards' ); ?></label>
			<input type="text" name="wc_gc_giftcard_to" placeholder="<?php esc_attr_e( 'Enter recipient e-mail', 'woocommerce-gift-cards' ); ?>" value="<?php echo esc_attr( $to ); ?>" />
		</div>
	<?php else : ?>
		<div class="wc_gc_field wc_gc_giftcard_to_multiple">
			<label for="wc_gc_giftcard_to_multiple"><?php esc_html_e( 'To', 'woocommerce-gift-cards' ); ?></label>
			<input type="text" name="wc_gc_giftcard_to_multiple" placeholder="<?php echo sprintf( esc_attr__( 'Enter an e-mail for each recipient, separating e-mails by comma (%s)', 'woocommerce-gift-cards' ), wc_gc_get_emails_delimiter() ); ?>" value="<?php echo esc_attr( $to ); ?>"/>
		</div>
	<?php endif; ?>

	<div class="wc_gc_field wc_gc_giftcard_from">
		<label for="wc_gc_giftcard_from"><?php esc_html_e( 'From', 'woocommerce-gift-cards' ); ?></label>
		<input type="text" name="wc_gc_giftcard_from" placeholder="<?php esc_attr_e( 'Enter your name', 'woocommerce-gift-cards' ); ?>" value="<?php echo esc_attr( $from ); ?>" />
	</div>
	<div class="wc_gc_field wc_gc_giftcard_message">
		<label for="wc_gc_giftcard_message"><?php esc_html_e( 'Message', 'woocommerce-gift-cards' ); ?></label>
		<textarea rows="3" name="wc_gc_giftcard_message" placeholder="<?php esc_attr_e( 'Add your message (optional)', 'woocommerce-gift-cards' ); ?>"><?php echo esc_html( $message ); ?></textarea>
	</div>

	<div class="wc_gc_field wc_gc_giftcard_delivery">
		<label for="wc_gc_giftcard_delivery"><?php esc_html_e( 'Delivery Date', 'woocommerce-gift-cards' ); ?></label>
		<input autocomplete="off" type="text" class="datepicker" placeholder="<?php esc_attr_e( 'Now', 'woocommerce-gift-cards' ); ?>" value="<?php echo esc_attr( $deliver_date ); ?>" />
		<input autocomplete="off" type="hidden" name="wc_gc_giftcard_delivery" />
		<input autocomplete="off" type="hidden" name="_wc_gc_giftcard_delivery_gmt_offset" />
	</div>

</div>
<?php

do_action( 'woocommerce_gc_after_form', $product );
