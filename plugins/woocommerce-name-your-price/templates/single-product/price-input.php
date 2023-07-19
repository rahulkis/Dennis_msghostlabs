<?php
/**
 * Single Product Price Input
 *
 * @author      Kathy Darling
 * @package     WC_Name_Your_Price/Templates
 * @version     3.1.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<div class="nyp" <?php echo WC_Name_Your_Price_Helpers::get_data_attributes( $nyp_product, $suffix ); ?> > <?php // phpcs:ignore WordPress.Security.EscapeOutput ?>

	<?php do_action( 'wc_nyp_before_price_input', $nyp_product, $suffix ); ?>

		<label for="<?php echo esc_attr( $input_id ); ?>"><?php echo wp_kses_post( $input_label ); ?></label>
		<input
			type="text"
			id="<?php echo esc_attr( $input_id ); ?>"
			class="<?php echo esc_attr( implode( ' ', (array) $classes ) ); ?>"
			name="<?php echo esc_attr( $input_name ); ?>"
			value="<?php echo esc_attr( $input_value ); ?>"
			title="<?php echo esc_attr( strip_tags( $input_label ) ); ?>"
			placeholder="<?php echo esc_attr( $placeholder ); ?>"

			<?php
			if ( ! empty( $custom_attributes ) && is_array( $custom_attributes ) ) {
				foreach ( $custom_attributes as $key => $value ) {
					printf( '%s="%s" ', esc_attr( $key ), esc_attr( $value ) );
				}
			}
			?>
		/>

		<input type="hidden" name="update-price" value="<?php echo esc_attr( $updating_cart_key ); ?>" />
		<input type="hidden" name="_nypnonce" value="<?php echo esc_attr( $_nypnonce ); ?>" />	

	<?php do_action( 'wc_nyp_after_price_input', $nyp_product, $suffix ); ?>

</div>

		
