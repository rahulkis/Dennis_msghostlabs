<?php
/**
 * Minimum Price Template
 *
 * @author      Kathy Darling
 * @package     WC_Name_Your_Price/Templates
 * @version     3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<p id="nyp-minimum-price-<?php echo esc_attr( $counter ); ?>" class="minimum-price nyp-terms">
	<?php echo wp_kses_post( WC_Name_Your_Price_Helpers::get_minimum_price_html( $nyp_product ) ); ?>
</p>

