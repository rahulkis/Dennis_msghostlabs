<?php
/**
 * Status Report data for NYP.
 *
 * @author   Kathy Darling
 * @package  WooCommerce Name Your Price
 * @since    3.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?><table class="wc_status_table widefat" cellspacing="0" id="status">
	<thead>
		<tr>
			<th colspan="3" data-export-label="Name Your Price"><h2><?php esc_html_e( 'Name Your Price', 'wc_name_your_price' ); ?></h2></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td data-export-label="Version" colspan="2"><?php esc_html_e( 'Version', 'wc_name_your_price' ); ?>:</td>
			<td id="name-your-price-version" colspan="3"><?php echo esc_html( WC_Name_Your_Price()->version );?></td>
		</tr>
		<tr>
			<td data-export-label="Template overrides"><?php esc_html_e( 'Template overrides', 'wc_name_your_price' ); ?>:</td>
			<td class="help"><?php echo wc_help_tip( esc_html__( 'Shows any files overriding the default Name Your Price templates.', 'wc_name_your_price' ) ); ?></td>
			<td>
			<?php

			if ( ! empty( $debug_data['overrides'] ) ) {

				$total_overrides = count( $debug_data['overrides'] );

				for ( $i = 0; $i < $total_overrides; $i++ ) {

					$override = $debug_data['overrides'][ $i ];

					if ( $override['core_version'] && ( empty( $override['version'] ) || version_compare( $override['version'], $override['core_version'], '<' ) ) ) {

						$current_version = $override['version'] ? $override['version'] : '-';

						printf(
							/* Translators: %1$s: Template name, %2$s: Template version, %3$s: Core version. */
							esc_html__( '%1$s version %2$s (out of date)', 'wc_name_your_price' ),
							'<code>' . esc_html( $override['file'] ) . '</code>',
							'<strong style="color:red">' . esc_html( $current_version ) . '</strong>',
							esc_html( $override['core_version'] )
						);

					} else {
						echo '<code>' . esc_html( $override['file'] ) . '</code>';
					}

					if ( ( count( $debug_data['overrides'] ) - 1 ) !== $i ) {
						echo ', ';
					}

					echo '<br />';
				}
			} else {
				?>
					&ndash;
					<?php
			}
			?>
			</td>
		</tr>
	</tbody>
</table>
