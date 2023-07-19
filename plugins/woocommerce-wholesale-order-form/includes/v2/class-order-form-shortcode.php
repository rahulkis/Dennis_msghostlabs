<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// Exit if accessed directly

if ( ! class_exists( 'Order_Form_Shortcode' ) ) {

	/**
	 * Order Form Shortcode.
	 */
	class Order_Form_Shortcode {

		/*
		|--------------------------------------------------------------------------
		| Class Properties
		|--------------------------------------------------------------------------
		 */

		/**
		 * Property that holds the single main instance of Order_Form_Shortcode.
		 *
		 * @since  1.6.6
		 * @access private
		 * @var Order_Form_Shortcode
		 */
		private static $_instance;

		/**
		 * WWOF_Product_Listing Object
		 *
		 * @since  1.6.6
		 * @access private
		 * @var WWOF_Product_Listing
		 */
		private $_wwof_product_listings;

		/*
		|--------------------------------------------------------------------------
		| Class Methods
		|--------------------------------------------------------------------------
		 */

		/**
		 * Order_Form_Shortcode constructor.
		 *
		 * @param array $dependencies Array of instance objects of all dependencies of Order_Form_Shortcode model.
		 *
		 * @access public
		 * @since  1.6.6
		 */
		public function __construct( $dependencies ) {

			$this->_wwof_product_listings = $dependencies['WWOF_Product_Listing'];

		}

		/**
		 * Ensure that only one instance of Order_Form_Shortcode is loaded or can be loaded (Singleton Pattern).
		 *
		 * @param array $dependencies Array of instance objects of all dependencies of Order_Form_Shortcode model.
		 *
		 * @return Order_Form_Shortcode
		 * @since 1.6.6
		 */
		public static function instance( $dependencies = null ) {

			if ( ! self::$_instance instanceof self ) {
				self::$_instance = new self( $dependencies );
			}

			return self::$_instance;

		}

		/**
		 * Product listing shortcode.
		 *
		 * @return string
		 * @since 1.0.0
		 * @since 1.6.6 Refactor codebase and move to its proper model
		 * @since 1.15  Added user permissions check. Enable Order Form v2.
		 * @since 2.0   Check whether to use the old form or not. V2.0 and up will not show the old form if wwof_use_legacy_order_form_filter is false.
		 */
		public function wwof_sc_product_listing( $atts ) {

			// To buffer the output
			ob_start();

			global $wc_wholesale_order_form;

			$user_has_access = $wc_wholesale_order_form->_wwof_permissions->wwof_user_has_access();

			// Order form key based on atttributes
			$form_key = maybe_serialize( $atts );

			// Extract atts
			$atts = shortcode_atts(
				array(
					'show_search' => 1,
					'categories'  => 0,
					'products'    => 0,
					'id'          => isset( $atts['id'] ) ? $atts['id'] : 0,
					'post_status' => isset( $atts['id'] ) ? get_post_status( $atts['id'] ) : '',
					'beta'        => 'false',
				),
				$atts
			);

			// Prior to 2.0
			// Display if beta shortcode is still being used
			// Even if use legacy filter is enabled
			if (
				isset( $atts['id'] ) && $atts['id'] > 0 &&
				isset( $atts['beta'] ) && $atts['beta'] == "true" &&
				get_post_type( $atts['id'] ) === 'order_form' &&
				$atts['post_status'] === 'publish' ||
				( $atts['post_status'] === 'draft' && current_user_can( 'administrator' ) )
			) {

				echo '<div class="order-form-wrapper order-form order-form-' . $atts['id'] . '" data-order-form-attr="' . htmlspecialchars(
						json_encode( $atts ),
						ENT_QUOTES,
						'UTF-8'
					) . '"></div>';

				return ob_get_clean();
			}

			// Use legacy form
			$use_legacy = apply_filters( 'wwof_use_legacy_order_form_filter', false );

			// Use Legacy Form is enabled AND
			// Not fresh install THEN
			// Use Old Form
			if ( $use_legacy && ! Order_Form_Helpers::is_fresh_install() ) {

				// Load old form
				require WWOF_VIEWS_ROOT_DIR . 'shortcodes/wwof-shortcode-product-listing.php';

				return ob_get_clean();

			}

			// Check if the user has permission
			if ( $user_has_access ) {

				// Validate the ID
				if ( isset( $atts['id'] ) && $atts['id'] > 0 ) {

					// Will show to all users if status is publish
					// Will show to admin only if status is draft
					if (
						get_post_type( $atts['id'] ) === 'order_form' &&
						$atts['post_status'] === 'publish' ||
						( $atts['post_status'] === 'draft' && current_user_can( 'administrator' ) )
					) {

						echo '<div class="order-form-wrapper order-form order-form-' . $atts['id'] . '" data-order-form-attr="' . htmlspecialchars(
								json_encode( $atts ),
								ENT_QUOTES,
								'UTF-8'
							) . '"></div>';

					} else {

						// ID is invalid
						if ( current_user_can( 'administrator' ) ) {
							echo '<div class="invalid"><h5>' . __(
									'The shortcode id for this order form is invalid.',
									'woocommerce-wholesale-order-form'
								) . '</h5></div>';
						}

					}

					return ob_get_clean();

				} else {

					// Check for migration data mapping
					$migration_data = get_option( WWOF_MIGRATION_DATA_MAPPING );

					// Display mapped order form
					if ( ! empty( $migration_data ) && ! Order_Form_Helpers::is_fresh_install() ) {

						$mapped_form = array();

						// Check if there's mapped new form for this old shortcode
						foreach ( $migration_data as $data ) {

							if ( $form_key == maybe_serialize( $data['atts'] ) ) {
								$mapped_form = $data;

								break;
							}
						}

						if ( ! empty( $mapped_form ) ) {

							$url_components = parse_url( $mapped_form['edit_form'] );
							parse_str( $url_components['query'], $params );

							$form_id     = $params['post'];
							$post_status = get_post_status( $form_id );

							// Override using mapped data
							$atts['id']          = $form_id;
							$atts['post_status'] = $post_status;

							// Validate the ID
							if ( isset( $form_id ) && get_post_type( $form_id ) === 'order_form' ) {

								// Will show to all users if status is publish
								// Will show to admin only if status is draft
								if ( $post_status === 'publish' || ( $post_status === 'draft' && current_user_can(
											'administrator'
										) ) ) {

									echo '<div class="order-form-wrapper order-form order-form-' . $form_id . '" data-order-form-attr="' . htmlspecialchars(
											json_encode( $atts ),
											ENT_QUOTES,
											'UTF-8'
										) . '"></div>';

									return ob_get_clean();

								}

							}

						}

					}

					// Fresh install and user is admin
					// We don't allow shortcode that has no id
					// No ID = Old Form then dont display
					if ( Order_Form_Helpers::is_fresh_install() && current_user_can( 'administrator' ) ) {

						echo '<div class="invalid">' . __(
								'Invalid short code. Please provide id of the form.',
								'woocommerce-wholesale-order-form'
							) . '</div>';

						// To return the buffered output
						return ob_get_clean();

					}

					// If user is admin and invalid id
					if ( current_user_can( 'administrator' ) ) {

						// Existing Installs
						// We dont support old form starting v2.0
						// They have to enable "wwof_use_legacy_order_form_filter" filter and set to true
						echo '<div class="invalid">' . __(
								'We are not supporting Order Form lower than 2.0. If you still want to use old form then please contact support.',
								'woocommerce-wholesale-order-form'
							) . '</div>';

						// To return the buffered output
						return ob_get_clean();

					}

				}

				// To return the buffered output
				return ob_get_clean();

			} else {

				ob_start();

				// User don't have permission
				$title     = trim( stripslashes( strip_tags( get_option( 'wwof_permissions_noaccess_title' ) ) ) );
				$message   = trim( stripslashes( get_option( 'wwof_permissions_noaccess_message' ) ) );
				$login_url = trim( get_option( 'wwof_permissions_noaccess_login_url' ) );

				if ( empty( $title ) ) {
					$title = __( 'Access Denied', 'woocommerce-wholesale-order-form' );
				}

				if ( empty( $message ) ) {
					$message = __(
						"You do not have permission to view wholesale product listing",
						'woocommerce-wholesale-order-form'
					);
				}

				if ( empty( $login_url ) ) {
					$login_url = wp_login_url();
				}
				?>

				<div id="wwof_access_denied">
				<h2 class="content-title"><?php echo $title; ?></h2>
				<?php echo do_shortcode( html_entity_decode( $message ) ); ?>
				<p class="login-link-container"><a class="login-link" href="<?php echo $login_url; ?>"><?php _e(
							'Login Here',
							'woocommerce-wholesale-order-form'
						); ?></a></p>
				</div><?php

				if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

					// To return the buffered output
					echo ob_get_clean();
					die();

				} else {
					return ob_get_clean();
				}

			}

		}

		/**
		 * Apply certain classes to body tag wherever page/post the shortcode [wwof_product_listing] is applied.
		 *
		 * @param $classes
		 *
		 * @return mixed
		 * @since 1.0.0
		 * @since 1.6.6 Refactor codebase and move to its proper model
		 */
		public function wwof_sc_body_classes( $classes ) {

			global $post;

			if ( isset( $post->post_content ) && has_shortcode( $post->post_content, 'wwof_product_listing' ) ) {

				$classes[] = 'wwof-woocommerce';
				$classes[] = 'woocommerce';
				$classes[] = 'woocommerce-page';

			}

			return apply_filters( 'wwof_filter_body_classes', $classes );

		}

		/**
		 * Execute model.
		 *
		 * @since  1.6.6
		 * @access public
		 */
		public function run() {

			global $wc_wholesale_order_form;

			// Un-register Short Codes
			remove_shortcode(
				'wwof_product_listing',
				array( $wc_wholesale_order_form->_wwof_shortcode, 'wwof_sc_product_listing' )
			);
			remove_filter( 'body_class', array( $wc_wholesale_order_form->_wwof_shortcode, 'wwof_sc_body_classes' ) );

			// Register shortcode. Customization will be done in this new class
			add_shortcode( 'wwof_product_listing', array( $this, 'wwof_sc_product_listing' ) );
			add_filter( 'body_class', array( $this, 'wwof_sc_body_classes' ) );

		}

	}

}
