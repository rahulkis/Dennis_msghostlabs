<?php
/**
 * Buy one get one free category page.
 *
 * @package WC_BOGOF
 */

defined( 'ABSPATH' ) || exit;
?>
<tr valign="top">
	<td class="wc_buy_one_get_one_free_wrapper" colspan="2">
		<table class="wc_buy_one_get_one_free widefat striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Enabled', 'wc-buy-one-get-one-free' ); ?></th>
					<th><?php esc_html_e( 'Category', 'wc-buy-one-get-one-free' ); ?></th>
					<th><?php esc_html_e( 'Deal mode', 'wc-buy-one-get-one-free' ); ?></th>
					<th><?php esc_html_e( 'Free product', 'wc-buy-one-get-one-free' ); ?></th>
					<th><?php esc_html_e( 'Buy quantity', 'wc-buy-one-get-one-free' ); ?></th>
					<th>
						<?php esc_html_e( 'Get free quantity', 'wc-buy-one-get-one-free' ); ?>
						<?php echo wc_help_tip( __( 'The free items the user will get when buy multiples of "Buy quantity".', 'wc-buy-one-get-one-free' ) ); // WPCS: XSS ok. ?>
					</th>
					<th>
						<?php esc_html_e( 'Free items limit', 'wc-buy-one-get-one-free' ); ?>
						<?php echo wc_help_tip( __( 'The maximum number of free items the user can get when buy multiples of "Buy quantity". Leave blank for unlimited free items.', 'wc-buy-one-get-one-free' ) ); // WPCS: XSS ok. ?>
					</th>
				</tr>
			</thead>
			<tbody>
			<?php self::category_rows(); ?>
			</tbody>
		</table>
	</td>
</tr>
