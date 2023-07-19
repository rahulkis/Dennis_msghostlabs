<?php
/**
 * WC_GC_Email_Template_Default class
 *
 * @author   SomewhereWarm <info@somewherewarm.com>
 * @package  WooCommerce Gift Cards
 * @since    1.2.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default email template.
 *
 * @class    WC_GC_Email_Template_Default
 * @version  1.5.3
 */
class WC_GC_Email_Template_Default extends WC_GC_Email_Template {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id             = 'default';
		$this->template_html  = 'emails/gift-card-received.php';
	}

	/**
	 * Get template args.
	 *
	 * @param  WC_Email  $email
	 * @return array
	 */
	public function get_args( $email ) {

		$template_args = array();

		// Get the product instance.
		$order_item     = new WC_Order_Item_Product( $email->get_gift_card()->get_order_item_id() );
		$product        = $order_item->get_product();

		$include_header = false;

		if ( is_a( $product, 'WC_Product' ) ) {
			$use_image = $product->get_meta( '_gift_card_template_default_use_image', true );

			// Backwards compatibility default.
			if ( empty( $use_image ) ) {
				$use_image = 'product';
			}

			if ( 'product' === $use_image ) {

				$image_id = $product->get_image_id();
				if ( $image_id ) {
					$image_src  = wp_get_attachment_image_src( $image_id, apply_filters( 'wooocommerce_gc_email_gift_card_image_size', 'woocommerce_single', $product, $email ) );
				}

			} elseif ( 'custom' === $use_image ) {

				$image_id = $product->get_meta( '_gift_card_template_default_custom_image', true );
				if ( $image_id ) {
					$image_src = wp_get_attachment_image_src( $image_id, apply_filters( 'wooocommerce_gc_email_gift_card_custom_image_size', 'large', $product, $email ) );
				}
			}

			if ( ! empty( $image_src ) ) {

				$include_header                = true;

				// Design args.
				$template_args[ 'image_src' ]  = $image_src[ 0 ];
				$template_args[ 'height' ]     = 200;
				$template_args[ 'position_X' ] = '50%';
				$template_args[ 'position_Y' ] = '50%';
			}
		}

		$template_args[ 'include_header' ] = $include_header;
		$template_args[ 'render_image' ]   = 'element';

		return $template_args;
	}

	/**
	 * Get admin product fields.
	 *
	 * @param  WC_Product $product
	 * @param  int        $loop
	 * @return string
	 */
	public function get_admin_product_fields_html( $product, $index = false ) {

		?><div class="template_default_image_container<?php echo $index !== false ? '' : ' show_if_giftcard_simple'; ?>"><?php

			// Use Image field.
			$use_image  = ! empty( $product->get_meta( '_gift_card_template_default_use_image', true ) ) ? $product->get_meta( '_gift_card_template_default_use_image', true ) : 'product';
			$input_name = $index !== false ? '_gift_card_template_default_use_variation_image[' . $index . ']' : '_gift_card_template_default_use_image';

			woocommerce_wp_select( array(
				'id'            => '_gift_card_template_default_use_image',
				'label'         => __( 'Recipient email image', 'woocommerce-gift-cards' ),
				'value'         => $use_image,
				'wrapper_class' => $index !== false ? 'form-row form-row-full' : '',
				'name'          => $input_name,
				'options'       => array(
					'none'    => __( 'None', 'woocommerce-gift-cards' ),
					'product' => __( 'Use product', 'woocommerce-gift-cards' ),
					'custom'  => __( 'Upload custom', 'woocommerce-gift-cards' )
				),
				'description'   => __( 'Choose an image to display in &quot;Gift Card Received&quot; emails sent to gift card recipients.', 'woocommerce-gift-cards' ),
				'desc_tip'      => 'true',
			) );

			// Image upload field.
			$image_id   = $product->get_meta( '_gift_card_template_default_custom_image', true );
			$image      = $image_id ? wp_get_attachment_thumb_url( $image_id ) : '';
			$input_name = $index !== false ? '_gift_card_template_default_custom_variation_image[' . $index . ']' : '_gift_card_template_default_custom_image';

			?><p class="form-field gift_card_template_default_custom_image wc_gc_select_image">
				<label></label>
				<a href="#" class="wc_gc_field_select_image <?php echo $image_id ? 'has_image': ''; ?>"><span class="prompt"><?php echo __( 'Select image', 'woocommerce-gift-cards' ); ?></span><img src="<?php if ( ! empty( $image ) ) echo esc_attr( $image ); else echo esc_attr( wc_placeholder_img_src() ); ?>" /><input type="hidden" name="<?php echo $input_name; ?>" class="image" value="<?php echo $image_id; ?>" /></a>
				<a href="#" class="wc_gc_field_remove_image <?php echo $image_id ? 'has_image': ''; ?>"><?php echo __( 'Remove image', 'woocommerce-gift-cards' ); ?></a>
			</p><?php

		?></div><?php
	}

	/**
	 * Process product fields.
	 *
	 * @throws  Exception
	 *
	 * @param  WC_Product $product
	 * @return void
	 */
	public function process_product_data( &$product ) {

		if ( isset( $_POST[ '_gift_card_template_default_use_image' ] ) ) {
			$product->update_meta_data( '_gift_card_template_default_use_image', wc_clean( $_POST[ '_gift_card_template_default_use_image' ] ) );

			if ( isset( $_POST[ '_gift_card_template_default_custom_image' ] ) && 'custom' === $_POST[ '_gift_card_template_default_use_image' ] ) {
				if ( ! empty( $_POST[ '_gift_card_template_default_custom_image' ] ) ) {
					$product->update_meta_data( '_gift_card_template_default_custom_image', absint( $_POST[ '_gift_card_template_default_custom_image' ] ) );
				} else {
					$product->update_meta_data( '_gift_card_template_default_use_image', 'product' );
					throw new Exception( __( 'The specified "Recipient email image" was invalid. The option has been set to use the product image instead.', 'woocommerce-gift-cards' ) );
				}

			}
		}
	}

	/**
	 * Process product variation fields.
	 *
	 * @throws  Exception
	 *
	 * @param  WC_Product $product
	 * @return void
	 */
	public function process_variation_product_data( &$product, $index ) {

		if ( isset( $_POST[ '_gift_card_template_default_use_variation_image' ][ $index ] ) ) {
			$product->update_meta_data( '_gift_card_template_default_use_image', wc_clean( $_POST[ '_gift_card_template_default_use_variation_image' ][ $index ] ) );

			if ( isset( $_POST[ '_gift_card_template_default_custom_variation_image' ][ $index ] ) && 'custom' === $_POST[ '_gift_card_template_default_use_variation_image' ][ $index ] ) {
				if ( ! empty( $_POST[ '_gift_card_template_default_custom_variation_image' ][ $index ] ) ) {
					$product->update_meta_data( '_gift_card_template_default_custom_image', absint( $_POST[ '_gift_card_template_default_custom_variation_image' ][ $index ] ) );
				} else {
					$product->update_meta_data( '_gift_card_template_default_use_image', 'product' );
					throw new Exception( sprintf( __( 'The specified "Recipient email image" for variation #%d was invalid. The option has been set to use the product image instead.', 'woocommerce-gift-cards' ), $product->get_id() ) );
				}
			}
		}

	}

	/**
	 * Style Giftcard template.
	 *
	 * @param  string    $css
	 * @param  WC_Email  $email
	 * @return string
	 */
	public function add_stylesheets( $css, $email = null ) {
		// Hint: $email param is not added until WC 3.6.
		if ( ( is_null( $email ) || 'gift_card_received' !== $email->id ) && WC_GC_Core_Compatibility::is_wc_version_gte( '3.6' ) ) {
			return $css;
		}

		// Load colors.
		$bg        = get_option( 'woocommerce_email_background_color' );
		$bg_text   = wc_light_or_dark( $bg, '#636363', '#ffffff' );
		$code_text = wc_light_or_dark( $bg, '#6f6f6f', '#aaaaaa' );
		$body      = get_option( 'woocommerce_email_body_background_color' );
		$base      = get_option( 'woocommerce_email_base_color' );
		$base_text = wc_light_or_dark( $base, '#202020', '#ffffff' );
		$text      = get_option( 'woocommerce_email_text_color' );

		ob_start();
		?>
		#header_wrapper h1 {
			line-height: 1em !important;
		}
		#giftcard__container {
			margin-bottom: 20px;
			color: <?php echo esc_attr( $bg_text ); ?>;
		}
		#giftcard__body {
			margin-bottom: 20px;
		}
		#giftcard__message {
			padding: 10px 0 10px 15px;
			font-size: 15px;
			line-height: 19px;
			border-left: 5px solid <?php echo esc_attr( $bg ); ?>;
			margin-bottom: 28px;
		}
		#giftcard__card-header {
			background-color: <?php echo esc_attr( $base ); ?>;
			margin-top: -20px;
			margin-left: -20px;
			margin-right: -20px;
			margin-bottom: 20px;
			background-size: cover;
		}
		#giftcard__card-image {
			margin-bottom: 20px;
		}
		#giftcard__card-image img {
			margin-right: 0;
		}
		#giftcard__card {
			padding: 20px 20px;
			text-align: center;
			background: <?php echo esc_attr( $bg ); ?>;
			font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;
		}
		#giftcard__card-amount {
			font-size: 42px;
			display: block;
			line-height: 42px;
			font-weight: bold;
			color: <?php echo esc_attr( $base ); ?>;
			padding: 3px 0;
			margin-bottom: 20px;
		}
		#giftcard__separator {
			color: <?php echo esc_attr( $text ); ?>;
			opacity: 0.7;
			display: block;
			margin-top: 10px;
			margin-bottom: 10px;
		}
		#giftcard__card-code {
			color: <?php echo esc_attr( $code_text ); ?>;
			font-weight: bold;
			margin-top: 4px;
			font-size: 16px;
			line-height: 16px;
			border: 1px solid <?php echo esc_attr( $code_text ); ?>;
			padding-top: 5px;
			padding-bottom: 5px;
			padding-left: 10px;
			padding-right: 10px;
		}
		#giftcard__action-button {
			text-decoration: none;
			display: inline-block;
			background: <?php echo esc_attr( $base ); ?>;
			color: <?php echo esc_attr( $base_text ); ?>;
			border: 10px solid <?php echo esc_attr( $base ); ?>;
		}
		#giftcard__action-button.shop-action {
			text-transform: uppercase;
		}
		#giftcard__expiration {
			text-transform: uppercase;
			margin-top: 20px;
			font-size: 0.8em;
		}
		<?php
		$css .= ob_get_clean();

		return $css;
	}
}
