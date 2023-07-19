<?php
/**
 * Buy one get one free category page.
 *
 * @package WC_BOGOF
 */

defined( 'ABSPATH' ) || exit;
?>
<?php if ( empty( $categories ) && 0 === $parent ) : ?>
<tr><td colspan="7"><?php esc_html_e( 'You need to create product categories first', 'wc-buy-one-get-one-free' ); ?></td></tr>
<?php else : ?>
	<?php foreach ( $categories as $category ) : ?>
	<tr class="wc-bogof-category-settings" id="wc-bogof-category-settings-<?php echo esc_attr( $category->term_id ); ?>">
		<td width="1%"><!-- Enabled -->
			<a href="#" class="wc-bogof-settings-toggle-enabled">
				<?php if ( 'yes' === $settings[ $category->term_id ]->enabled ) : ?>
				<span class="woocommerce-input-toggle woocommerce-input-toggle--enabled"><?php esc_html_e( 'Yes', 'wc-buy-one-get-one-free' ); ?></span>
				<?php else : ?>
				<span class="woocommerce-input-toggle woocommerce-input-toggle--disabled"><?php esc_html_e( 'No', 'wc-buy-one-get-one-free' ); ?></span>
				<?php endif; ?>
			</a>
			<input type="hidden" name="term_id[]" value="<?php echo esc_attr( $category->term_id ); ?>" />
			<input type="hidden" name="enabled[<?php echo esc_attr( $category->term_id ); ?>]" value="<?php echo esc_attr( $settings[ $category->term_id ]->enabled ); ?>" />
		</td>
		<td><!-- Category -->
			<strong><?php echo esc_html( str_repeat( '&mdash; ', $level ) . $category->name ); ?></strong>
		</td>
		<td class="wc-bogof-category-product-mode"><!-- Deal mode -->
			<select class="wc-bogof-category-settings-mode" id="mode-<?php echo esc_attr( $category->term_id ); ?>" name="mode[<?php echo esc_attr( $category->term_id ); ?>]" class="select short">
				<option <?php selected( 'aa', $settings[ $category->term_id ]->mode ); ?> value="aa"><?php esc_html_e( 'Buy product A and get product A free', 'wc-buy-one-get-one-free' ); ?></option>
				<option <?php selected( 'ab', $settings[ $category->term_id ]->mode ); ?> value="ab"><?php esc_html_e( 'Buy product A and get product B free', 'wc-buy-one-get-one-free' ); ?></option>
				<option <?php selected( 'cb', $settings[ $category->term_id ]->mode ); ?> value="cb"><?php esc_html_e( 'Buy product of the category and get product B free', 'wc-buy-one-get-one-free' ); ?></option>
			</select>
		</td>
		<td class="wc-bogof-category-product-id"><!-- Free product -->
			<div class="wc-bogof-category-search-wrapper" style="<?php echo esc_attr( 'aa' === $settings[ $category->term_id ]->mode ? 'display: none;' : '' ); ?>">
				<select class="wc-product-search" style="width: 100%;" id="product_id-<?php echo esc_attr( $category->term_id ); ?>" name="product_id[<?php echo esc_attr( $category->term_id ); ?>]" data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'wc-buy-one-get-one-free' ); ?>" data-action="wc_bogof_search_purchasable_products">
					<?php
					$_product = wc_get_product( $settings[ $category->term_id ]->product_id );
					if ( is_object( $_product ) ) {
						echo '<option value="' . esc_attr( $settings[ $category->term_id ]->product_id ) . '"' . selected( true, true, false ) . '>' . wp_kses_post( $_product->get_formatted_name() ) . '</option>';
					}
					?>
				</select>
			</div>
			<span class="not-applicable" style="<?php echo esc_attr( 'aa' !== $settings[ $category->term_id ]->mode ? 'display: none;' : '' ); ?>">&mdash;</span>
		</td>
		<td class="wc-bogof-category-quantity"><!-- Buy quantity -->
			<input type="number" step="1" min="0" name="min_quantity[<?php echo esc_attr( $category->term_id ); ?>]" id="min_quantity-<?php echo esc_attr( $category->term_id ); ?>" value="<?php echo esc_attr( $settings[ $category->term_id ]->min_quantity ); ?>" />
		</td>
		<td class="wc-bogof-category-quantity"><!-- Get free quantity -->
			<input type="number" step="1" min="0" name="free_quantity[<?php echo esc_attr( $category->term_id ); ?>]" id="free_quantity-<?php echo esc_attr( $category->term_id ); ?>" value="<?php echo esc_attr( $settings[ $category->term_id ]->free_quantity ); ?>" />
		</td>
		<td class="wc-bogof-category-quantity"><!-- Free items limit -->
			<input type="number" step="1" min="0" name="limit[<?php echo esc_attr( $category->term_id ); ?>]" id="limit-<?php echo esc_attr( $category->term_id ); ?>" value="<?php echo esc_attr( $settings[ $category->term_id ]->limit ); ?>" placeholder="<?php echo esc_attr__( 'Unlimited', 'wc-buy-one-get-one-free' ); ?>" />
		</td>
	</tr>
	<?php self::category_rows( $category->term_id, $level + 1 ); ?>
	<?php endforeach; ?>
<?php endif; ?>
<?php


