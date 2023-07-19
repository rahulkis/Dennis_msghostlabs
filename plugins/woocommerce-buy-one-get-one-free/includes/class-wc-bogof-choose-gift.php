<?php
/**
 * Buy One Get One Free Choose your gift. Handles choose your gift actions.
 *
 * @package WC_BOGOF
 */

defined( 'ABSPATH' ) || exit;

/**
 * WC_BOGOF_Choose_Gift Class
 */
class WC_BOGOF_Choose_Gift {

	/**
	 * Flag to handle is "choose your gift page".
	 *
	 * @var string
	 */
	private static $is_choose_your_gift = false;

	/**
	 * Cart hash.
	 *
	 * @var string
	 */
	private static $bogof_cart_hash = false;

	/**
	 * The choose your gift notice has been displayed?
	 *
	 * @var string
	 */
	private static $notice_displayed = false;

	/**
	 * Init hooks
	 */
	public static function init() {
		add_action( 'wc_bogof_cart_rules_loaded', array( __CLASS__, 'choose_your_gift_page' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'load_scripts' ), 20 );
		add_action( 'woocommerce_shortcode_before_product_cat_loop', array( __CLASS__, 'choose_your_gift_notice' ), 10 );
		add_action( 'woocommerce_before_shop_loop', array( __CLASS__, 'choose_your_gift_notice' ), 10 );
		add_action( 'woocommerce_before_single_product', array( __CLASS__, 'choose_your_gift_notice' ), 15 );
		add_action( 'woocommerce_before_cart', array( __CLASS__, 'choose_your_gift_notice' ), 15 );
		add_action( 'woocommerce_before_checkout_form', array( __CLASS__, 'choose_your_gift_notice' ), 15 );
		add_action( 'woocommerce_after_cart', array( __CLASS__, 'choose_your_gift_after_cart' ), 5 );
		add_action( 'wc_ajax_bogof_update_choose_your_gift', array( __CLASS__, 'update_choose_your_gift' ) );
		add_filter( 'pre_option_woocommerce_cart_redirect_after_add', array( __CLASS__, 'cart_redirect_after_add' ) );
		add_filter( 'woocommerce_add_to_cart_fragments', array( __CLASS__, 'add_to_cart_fragments' ) );
		add_filter( 'woocommerce_add_to_cart_fragments', array( __CLASS__, 'add_to_cart_fragments_single_product' ), 11 );
		add_shortcode( 'wc_choose_your_gift', array( __CLASS__, 'choose_your_gift' ) );
	}

	/**
	 * Init the "choose your gift" page
	 */
	public static function choose_your_gift_page() {
		self::$bogof_cart_hash = false;
		$cart_hash             = self::get_hash_from_request();
		if ( $cart_hash && WC_BOGOF_Cart::get_hash() === $cart_hash ) {
			self::$bogof_cart_hash = $cart_hash;
			self::init_hooks();
		}
	}

	/**
	 * Get the cart hash from query string.
	 *
	 * @since 2.0.5
	 * @return string
	 */
	private static function get_hash_from_request() {
		$cart_hash = isset( $_REQUEST['wc_bogo_refer'] ) ? wc_clean( $_REQUEST['wc_bogo_refer'] ) : false; // phpcs:ignore WordPress.Security.NonceVerification
		if ( ! $cart_hash && defined( 'WC_DOING_AJAX' ) && WC_DOING_AJAX ) {
			$query = wp_parse_url( wp_get_referer(), PHP_URL_QUERY );
			wp_parse_str( $query, $params );

			$cart_hash = isset( $params['wc_bogo_refer'] ) ? $params['wc_bogo_refer'] : false;
		}
		return $cart_hash;
	}

	/**
	 * Adds the choose your gift page hooks.
	 */
	private static function init_hooks() {
		add_filter( 'woocommerce_add_to_cart_form_action', array( __CLASS__, 'add_to_cart_form_action' ) );
		add_filter( 'woocommerce_quantity_input_max', array( __CLASS__, 'quantity_input_max' ), 10, 2 );
	}

	/**
	 * Is choose your gift page?
	 */
	public static function is_choose_your_gift() {
		return ! empty( self::$bogof_cart_hash ) || self::$is_choose_your_gift;
	}

	/**
	 * Add the bogof parameter to the URL
	 *
	 * @param string $form_action Form action link.
	 */
	public static function add_to_cart_form_action( $form_action ) {
		global $product;
		if ( WC_BOGOF_Cart::get_product_shop_free_quantity( $product ) > 0 ) {
			$form_action = add_query_arg( 'wc_bogo_refer', WC_BOGOF_Cart::get_hash(), $form_action );
		}
		return $form_action;
	}

	/**
	 * Set the max purchase qty.
	 *
	 * @param int        $max_quantity Max purchase qty.
	 * @param WC_Product $product Product object.
	 * @return int
	 */
	public static function quantity_input_max( $max_quantity, $product ) {
		$max_free_qty = WC_BOGOF_Cart::get_product_shop_free_quantity( $product );
		if ( $max_free_qty > 0 && $max_free_qty > $max_quantity ) {
			$max_quantity = $max_free_qty;
		}
		return $max_free_qty;
	}

	/**
	 * Redirects to the cart when there are no more free items.
	 *
	 * @param string $value Option value.
	 */
	public static function cart_redirect_after_add( $value ) {
		if ( isset( $_REQUEST['wc_bogof_cart_rule'] ) && WC_BOGOF_Cart::get_shop_free_quantity() <= 0 ) { // phpcs:ignore WordPress.Security.NonceVerification
			$value = 'yes';
			add_filter( 'woocommerce_continue_shopping_redirect', array( __CLASS__, 'continue_shopping_redirect' ) );
		}
		return $value;
	}

	/**
	 * Return the shop page after add to cart from the choose your gift page.
	 *
	 * @param string $return_to Return URL.
	 * @return string
	 */
	public static function continue_shopping_redirect( $return_to ) {
		return wc_get_page_permalink( 'shop' );
	}

	/**
	 * Add to cart fragments.
	 *
	 * @param array $fragments Fragments array.
	 */
	public static function add_to_cart_fragments( $fragments ) {
		$fragments = is_array( $fragments ) ? $fragments : array();

		// phpcs:disable WordPress.Security.NonceVerification.Missing
		if ( isset( $_POST['wc_bogof_data'] ) ) {

			$postdata = wc_clean( wp_unslash( $_POST['wc_bogof_data'] ) );

			$hash    = empty( $postdata['hash'] ) ? false : $postdata['hash'];
			$is_cart = empty( $postdata['is_cart'] ) ? false : wc_string_to_bool( $postdata['is_cart'] );

			$data = array();

			if ( WC_BOGOF_Cart::get_shop_free_quantity() <= 0 ) {
				$data['cart_redirect'] = $is_cart ? 'no' : 'yes';
			} else {
				// Add to cart message.
				if ( 'yes' !== get_option( 'woocommerce_cart_redirect_after_add' ) ) {
					$product_id = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $_POST['product_id'] ) );
					$quantity   = empty( $_POST['quantity'] ) ? 1 : wc_stock_amount( wp_unslash( $_POST['quantity'] ) );
					$message    = wc_add_to_cart_message( array( $product_id => $quantity ), true, ! $is_cart );

					if ( ! $is_cart ) {
						ob_start();
						wc_print_notice( $message, 'success' );
						$data['notice'] = ob_get_clean();
					}
				}

				// Refresh the choose your gift content.
				if ( ! $is_cart && $hash && WC_BOGOF_Cart::get_hash() !== $hash ) {
					$shortcode       = new WC_BOGOF_Choose_Gift_Shortcode( $postdata );
					$data['content'] = $shortcode->get_content();
				}
			}

			$fragments['wc_choose_your_gift_data'] = $data;

		}
		// phpcs:enable

		return $fragments;
	}

	/**
	 * Add to cart fragments for single product.
	 *
	 * @param array $fragments Fragments array.
	 */
	public static function add_to_cart_fragments_single_product( $fragments ) {
		$fragments = is_array( $fragments ) ? $fragments : array();

		// phpcs:disable WordPress.Security.NonceVerification
		if ( isset( $_REQUEST['wc_bogof_single_product'] ) && ! empty( $_REQUEST['wc_bogof_cart_rule'] ) ) {
			$data = array();
			if ( WC_BOGOF_Cart::get_shop_free_quantity() <= 0 ) {
				$data['cart_redirect'] = 'yes';
			}
			$fragments['wc_choose_your_gift_data'] = $data;
		}
		// phpcs:enable

		return $fragments;
	}

	/**
	 * Refresh "choose your gift" via AJAX.
	 */
	public static function update_choose_your_gift() {
		$data     = array();
		$postdata = wc_clean( wp_unslash( $_POST ) ); // phpcs:ignore WordPress.Security.NonceVerification
		$hash     = empty( $postdata['hash'] ) ? false : $postdata['hash'];

		// Refresh the choose your gift content.
		if ( WC_BOGOF_Cart::get_hash() !== $hash ) {
			$shortcode       = new WC_BOGOF_Choose_Gift_Shortcode( $postdata );
			$data['content'] = $shortcode->get_content();
		}

		wp_send_json(
			array(
				'wc_choose_your_gift_data' => $data,
			)
		);
	}

	/**
	 * Returns the choose your gift page URL.
	 *
	 * @return string
	 */
	private static function get_link() {
		$page_link = false;
		if ( 'after_cart' === get_option( 'wc_bogof_cyg_display_on', 'after_cart' ) ) {
			if ( is_cart() ) {
				$page_link = '#wc-choose-your-gift';
			} else {
				$page_link = wc_get_cart_url() . '#wc-choose-your-gift';
			}
		} else {
			$page_id = get_option( 'wc_bogof_cyg_page_id', 0 );
			if ( absint( get_the_ID() ) === absint( $page_id ) ) {
				$page_link = '#wc-choose-your-gift';
			} elseif ( wc_bogof_has_choose_your_gift_shortcode( $page_id ) && 'publish' === get_post_status( $page_id ) ) {
				$page_link = get_permalink( $page_id );
				if ( $page_link ) {
					$page_link = add_query_arg( 'wc_bogo_refer', WC_BOGOF_Cart::get_hash(), $page_link ) . '#wc-choose-your-gift';
				}
			}
		}

		return $page_link;
	}

	/**
	 * Add a WooCommerce notice if there are avilable gifts.
	 */
	public static function choose_your_gift_notice() {
		if ( self::$notice_displayed ) {
			return;
		}

		$qty = WC_BOGOF_Cart::get_shop_free_quantity();
		if ( $qty <= 0 ) {
			return;
		}

		$page_link = self::get_link();
		if ( $page_link ) {
			$text        = get_option( 'wc_bogof_cyg_notice', false );
			$button_text = get_option( 'wc_bogof_cyg_notice_button_text', false );

			// translators: 1 free products qty, 2,3: html tags.
			$text        = empty( $text ) ? sprintf( _n( 'You can now add %1$s product for free to the cart.', 'You can now add %1$s products for free to the cart.', $qty, 'wc-buy-one-get-one-free' ), $qty ) : str_replace( '[qty]', $qty, $text );
			$button_text = empty( $button_text ) ? esc_html__( 'Choose your gift', 'wc-buy-one-get-one-free' ) : $button_text;
			$message     = sprintf( ' %s <a href="%s" tabindex="1" class="button button-choose-your-gift">%s</a>', esc_html( $text ), esc_url( $page_link ), $button_text );

			echo '<div class="woocommerce-notices-wrapper woocommerce-choose-your-gift-notice-wrapper">';
			wc_print_notice( $message, 'success' );
			echo '</div>';
		} elseif ( current_user_can( 'manage_woocommerce' ) ) {
			// translators: HTML tags.
			wc_print_notice( sprintf( __( 'The "choose your gift" page has not set! Customers will not be able to add to the cart the free product. Go to the %1$ssettings page%2$s and set a %3$spublic page%4$s that contains the [wc_choose_your_gift] shortcode. ', 'wc-buy-one-get-one-free' ), '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=products&section=buy-one-get-one-free' ) . '">', '</a>', '<strong>', '</strong>' ), 'error' );
		}

		self::$notice_displayed = true;
	}

	/**
	 * Displays the choose your gift shortcode after the cart.
	 */
	public static function choose_your_gift_after_cart() {
		if ( 'after_cart' === get_option( 'wc_bogof_cyg_display_on', 'after_cart' ) ) {
			$title = get_option( 'wc_bogof_cyg_title', false );
			$title = empty( $title ) ? __( 'Choose your gift', 'wc-buy-one-get-one-free' ) : $title;
			echo self::choose_your_gift( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				array(
					'title'      => $title,
					'no_results' => false,
				)
			);
		}
	}


	/**
	 * Register/queue frontend scripts.
	 */
	public static function load_scripts() {
		global $post;

		if ( ! did_action( 'before_woocommerce_init' ) ) {
			return;
		}

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		$deps = array( 'jquery' );
		if ( 'yes' === get_option( 'woocommerce_enable_ajax_add_to_cart' ) ) {
			$deps[] = 'wc-add-to-cart';
		}
		if ( is_cart() ) {
			$deps[] = 'wc-cart';
		}

		wp_register_script( 'wc-bogof-choose-your-gift', plugins_url( 'assets/js/frontend/choose-your-gift' . $suffix . '.js', WC_BOGOF_PLUGIN_FILE ), $deps, WC_Buy_One_Get_One_Free::$version, true );

		// Load script to adding support AJAX add to cart on the single product page.
		if ( self::is_choose_your_gift() && ( is_product() || ( ! empty( $post->post_content ) && strstr( $post->post_content, '[product_page' ) ) ) ) {
			$cart_rules = array();
			if ( ! empty( $post->ID ) ) {
				$_product = wc_get_product();
				foreach ( WC_BOGOF_Cart::get_cart_rules() as $cart_rule ) {
					if ( $cart_rule->is_shop_avilable_free_product( $_product ) ) {
						$cart_rules[] = $cart_rule->get_id();
					}
				}
			}

			wp_register_script( 'wc-bogof-single-product', plugins_url( 'assets/js/frontend/single-product' . $suffix . '.js', WC_BOGOF_PLUGIN_FILE ), array( 'jquery' ), WC_Buy_One_Get_One_Free::$version, true );
			wp_localize_script(
				'wc-bogof-single-product',
				'wc_bogof_single_product_params',
				array(
					'cart_url'   => apply_filters( 'woocommerce_add_to_cart_redirect', wc_get_cart_url(), null ),
					'cart_rules' => $cart_rules,
				)
			);
			wp_enqueue_script( 'wc-bogof-single-product' );
		}
	}

	/**
	 * Sortcode callback. Lists free available products.
	 *
	 * @param array $atts Attributes.
	 * @return string
	 */
	public static function choose_your_gift( $atts ) {
		if ( is_admin() ) {
			return;
		}

		self::$is_choose_your_gift = true;

		$content = '<div class="choose-your-gift-notice-wrapper"></div>';

		$shortcode = new WC_BOGOF_Choose_Gift_Shortcode( $atts );
		$content  .= $shortcode->get_content();

		self::$is_choose_your_gift = false;

		wp_enqueue_script( 'wc-bogof-choose-your-gift' );

		return $content;
	}

}
