<?php
/**
 * Buy One Get One Free WPML compatibilty.
 *
 * @package WC_BOGOF
 */

defined( 'ABSPATH' ) || exit;

/**
 * WC_BOGOF_WPML Class
 */
class WC_BOGOF_WPML {

	/**
	 * Init hooks
	 */
	public static function init() {
		add_action( 'save_post', array( __CLASS__, 'synchronize_bogof_data' ), PHP_INT_MAX, 2 );
		add_action( 'icl_make_duplicate', array( __CLASS__, 'icl_make_duplicate' ), PHP_INT_MAX, 4 );
		add_action( 'wc_bogof_after_ajax_toggle_enabled', array( __CLASS__, 'after_ajax_toggle_enabled' ), 10, 2 );
		add_action( 'pre_get_posts', array( __CLASS__, 'pre_get_posts' ) );
		add_filter( 'option_wc_bogof_cyg_page_id', array( __CLASS__, 'choose_your_gift_page_id' ) );
	}

	/**
	 * Synchronize BOGO data between languages.
	 *
	 * @param int    $post_id Post ID.
	 * @param object $post Post object.
	 */
	public static function synchronize_bogof_data( $post_id, $post ) {
		global $sitepress;

		// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals

		if ( 'shop_bogof_rule' !== get_post_type( $post_id ) || ( ! empty( $_POST['icl_ajx_action'] ) && 'make_duplicates' === $_POST['icl_ajx_action'] ) ) {
			return;
		}

		// Dont' save meta boxes for revisions or autosaves.
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || is_int( wp_is_post_revision( $post ) ) || is_int( wp_is_post_autosave( $post ) ) ) {
			return;
		}

		// Check the nonce.
		if ( empty( $_POST['woocommerce_meta_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['woocommerce_meta_nonce'] ), 'woocommerce_save_data' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			return;
		}

		// Check the post being saved == the $post_id to prevent triggering this call for other save_post events.
		if ( empty( $_POST['post_ID'] ) || absint( $_POST['post_ID'] ) !== $post_id ) {
			return;
		}

		remove_action( 'save_post', array( __CLASS__, 'synchronize_bogof_data' ), PHP_INT_MAX );

		$original_post_id = apply_filters( 'wpml_object_id', $post_id, 'shop_bogof_rule', true, $sitepress->get_default_language() );

		if ( absint( $original_post_id ) === absint( $post_id ) ) {

			// Sync between languages.
			$langs = $sitepress->get_active_languages();
			foreach ( $langs as $lang => $lang_details ) {
				if ( $lang === $sitepress->get_default_language() ) {
					continue;
				}
				$tr_post_id = apply_filters( 'wpml_object_id', $post_id, 'shop_bogof_rule', false, $lang );

				if ( empty( $tr_post_id ) && is_callable( array( $sitepress, 'make_duplicate' ) ) ) {
						$tr_post_id = $sitepress->make_duplicate( $post_id, $lang );
				}
				if ( ! empty( $tr_post_id ) ) {
					self::sync_data( $post_id, $tr_post_id, $lang );
				}
			}
		}

		add_action( 'save_post', array( __CLASS__, 'synchronize_bogof_data' ), PHP_INT_MAX );

		// phpcs:enable

	}

	/**
	 * After duplicate sync.
	 *
	 * @param int    $master_post_id Master post ID.
	 * @param string $lang Language code.
	 * @param array  $postarr Post data.
	 * @param int    $id Translate ID.
	 */
	public static function icl_make_duplicate( $master_post_id, $lang, $postarr, $id ) {
		global $sitepress;

		if ( 'shop_bogof_rule' !== get_post_type( $master_post_id ) ) {
			$master_post_id = apply_filters( 'wpml_object_id', $post_id, 'shop_bogof_rule', true, $sitepress->get_default_language() ); // phpcs:disable WordPress.NamingConventions.PrefixAllGlobals

			self::sync_data( $master_post_id, $id, $lang );
		}
	}

	/**
	 * Sync the product data between languages.
	 *
	 * @param int    $original_id Original ID.
	 * @param int    $tr_id Translate ID.
	 * @param string $lang Language.
	 */
	public static function sync_data( $original_id, $tr_id, $lang ) {
		if ( $original_id === $tr_id ) {
			return;
		}
		$rule    = new WC_BOGOF_Rule( $original_id );
		$tr_rule = new WC_BOGOF_Rule( $tr_id );

		if ( $rule->get_free_product_id() ) {
			$type          = get_post_type( $rule->get_free_product_id() );
			$tr_product_id = apply_filters( 'wpml_object_id', $rule->get_free_product_id(), $type, false, $lang ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
			$tr_rule->set_free_product_id( $tr_product_id );
		}

		foreach ( array( '_buy_product_ids', '_free_product_ids', '_exclude_product_ids', '_buy_category_ids', '_free_category_ids' ) as $prop ) {
			$getter         = 'get' . $prop;
			$setter         = 'set' . $prop;
			$product_ids    = $rule->{$getter}();
			$tr_product_ids = array();
			foreach ( $product_ids as $product_id ) {
				$type = in_array( $prop, array( '_buy_category_ids', '_free_category_ids' ), true ) ? 'product_cat' : get_post_type( $product_id );

				if ( 'all' === $product_id && 'product_cat' === $type ) {
					$tr_product_ids[] = 'all';
				} else {
					$tr_product_ids[] = apply_filters( 'wpml_object_id', $product_id, $type, false, $lang ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
				}
			}
			$tr_rule->{$setter}( $tr_product_ids );
		}

		$tr_rule->save();
	}

	/**
	 * Sync rule enabled on the AJAX action.
	 *
	 * @param int  $rule_id BOGOF Rule ID.
	 * @param bool $enabled True of False.
	 */
	public static function after_ajax_toggle_enabled( $rule_id, $enabled ) {
		global $sitepress;

		$langs = is_callable( array( $sitepress, 'get_active_languages' ) ) ? $sitepress->get_active_languages() : array();

		foreach ( $langs as $lang => $lang_details ) {
			if ( $lang === $sitepress->get_default_language() ) {
				continue;
			}
			$tr_post_id = apply_filters( 'wpml_object_id', $rule_id, 'shop_bogof_rule', false, $lang );

			if ( ! empty( $tr_post_id ) ) {

				$rule = new WC_BOGOF_Rule( absint( $tr_post_id ) );

				if ( $rule->get_id() ) {
					$rule->set_enabled( $enabled );
					$rule->save();
				}
			}
		}
	}

	/**
	 * Checks the environment for compatibility problems.
	 *
	 * @return array
	 */
	public static function get_admin_notices() {
		global $woocommerce_wpml;

		$messages = array();
		if ( ! empty( $woocommerce_wpml->settings['cart_sync']['lang_switch'] ) ) {
			// Translators: HTML tags.
			$messages[] = sprintf( __( '%1$sBuy One Get One Free%2$s requires you set the option %3$sSwitching languages when there are items in the cart%4$s to %1$sPrompt for a confirmation and reset the cart%2$s.', 'wc-buy-one-get-one-free' ), '<strong>', '</strong>', '<a href="' . admin_url( 'admin.php?page=wpml-wcml&tab=settings' ) . '">', '</a>' );
		}
		return $messages;
	}

	/**
	 * Supress filters is required by WPML to filter post by language.
	 *
	 * @param WP_Query $query WP_Query object.
	 */
	public static function pre_get_posts( $query ) {
		if ( ! $query->is_main_query() && 'shop_bogof_rule' === $query->get( 'post_type' ) ) {
			$query->set( 'suppress_filters', false );
		}
	}

	/**
	 * Retruns the choose your gift page ID for the current language.
	 *
	 * @param string $value Option value.
	 */
	public static function choose_your_gift_page_id( $value ) {
		return apply_filters( 'wpml_object_id', $value, 'page', false );
	}

}
