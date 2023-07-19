<?php
/**
 * Admin edit-Gift Card meta box html
 *
 * @version 1.3.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?><div class="woocommerce_gc_giftcard_form">

	<?php if ( $product->is_sold_individually() ) : ?>
		<div class="wc_gc_field wc_gc_giftcard_to">
			<label for="wc_gc_giftcard_to"><?php esc_html_e( 'Recipient', 'woocommerce-gift-cards' ); ?></label>
			<input type="text" name="wc_gc_giftcard_to" placeholder="<?php esc_attr_e( 'Enter recipient e-mail', 'woocommerce-gift-cards' ); ?>" value="<?php echo esc_attr( $to ) ?>" />
		</div>
	<?php else : ?>
		<div class="wc_gc_field wc_gc_giftcard_to_multiple">
			<label for="wc_gc_giftcard_to_multiple"><?php esc_html_e( 'Recipient(s)', 'woocommerce-gift-cards' ); ?></label>
			<input type="text" name="wc_gc_giftcard_to_multiple" placeholder="<?php echo sprintf( esc_attr__( 'Enter an e-mail for each recipient, separating e-mails by comma (%s)', 'woocommerce-gift-cards' ), wc_gc_get_emails_delimiter() ); ?>" value="<?php echo esc_attr( $to ) ?>" />
		</div>
	<?php endif; ?>

	<div class="wc_gc_field wc_gc_giftcard_from">
		<label for="wc_gc_giftcard_from"><?php esc_html_e( 'Sender', 'woocommerce-gift-cards' ); ?></label>
		<input type="text" name="wc_gc_giftcard_from" placeholder="<?php esc_attr_e( 'Enter sender\'s name', 'woocommerce-gift-cards' ); ?>" value="<?php echo esc_attr( $from ) ?>" />
	</div>

	<div class="wc_gc_field wc_gc_giftcard_message">
		<label for="wc_gc_giftcard_message"><?php esc_html_e( 'Message', 'woocommerce-gift-cards' ); ?></label>
		<textarea rows="3" name="wc_gc_giftcard_message" placeholder="<?php esc_attr_e( 'Add a message (optional)', 'woocommerce-gift-cards' ); ?>"><?php echo esc_html( $message ) ?></textarea>
	</div>

	<div class="wc_gc_field wc_gc_giftcard_delivery">
		<label for="wc_gc_giftcard_delivery"><?php esc_html_e( 'Delivery Date', 'woocommerce-gift-cards' ); ?></label>
		<input autocomplete="off" type="text" class="datepicker" placeholder="<?php esc_attr_e( 'Now', 'woocommerce-gift-cards' ); ?>" value="<?php echo esc_attr( $deliver_date ); ?>" />
		<input autocomplete="off" type="hidden" name="wc_gc_giftcard_delivery" />
		<input autocomplete="off" type="hidden" name="_wc_gc_giftcard_delivery_gmt_offset" value="<?php echo (float) get_option( 'gmt_offset' ) ?>" />
	</div>

	<div class="wc-gc-edit-code">

		<label><input type="checkbox" name="wc_gc_giftcard_code_random"<?php echo empty( $code ) ? ' checked="checked"' : '' ?>><?php esc_html_e( 'Generate a random code', 'woocommerce-gift-cards' ) ?></label>

		<div class="wc-gc-field wc_gc_giftcard_code">
			<label for="wc_gc_giftcard_code"><?php esc_html_e( 'Code', 'woocommerce-gift-cards' ); ?></label>
			<input type="text" placeholder="<?php esc_attr_e( 'XXXX-XXXX-XXXX-XXXX', 'woocommerce-gift-cards' ); ?>" name="wc_gc_giftcard_code" autocomplete="off" value="<?php echo esc_attr( $code ) ?>" />
		</div>

	</div>

</div>
