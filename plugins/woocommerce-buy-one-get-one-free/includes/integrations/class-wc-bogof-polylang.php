<?php
/**
 * Buy One Get One Free Polylang compatibilty.
 *
 * @package WC_BOGOF
 */

defined( 'ABSPATH' ) || exit;

/**
 * WC_BOGOF_Polylang Class
 */
class WC_BOGOF_Polylang {

	/**
	 * Init hooks
	 */
	public static function init() {
		add_action( 'pll_save_post', array( __CLASS__, 'sync_bogof_data' ), 20, 3 );
		add_filter( 'woocommerce_json_search_found_products', array( __CLASS__, 'json_search_found_products' ) );
		add_filter( 'pll_filter_query_excluded_query_vars', array( __CLASS__, 'excluded_query_vars' ), 10, 2 );
		add_filter( 'option_wc_bogof_cyg_page_id', array( __CLASS__, 'choose_your_gift_page_id' ) );
		add_filter( 'woocommerce_bogof_rule_get_free_product_ids', array( __CLASS__, 'get_free_product_ids' ) );
		add_filter( 'woocommerce_bogof_rule_get_free_product_id', array( __CLASS__, 'get_free_product_id' ) );
		add_filter( 'pllwc_translate_cart_item', array( __CLASS__, 'translate_cart_item' ) );
	}

	/**
	 * BOGO data synchronization between languages.
	 *
	 * @param int    $object_id    Id of the object being saved.
	 * @param object $obj          Not used.
	 * @param array  $translations The list of translations object ids.
	 */
	public static function sync_bogof_data( $object_id, $obj, $translations ) {
		if ( 'shop_bogof_rule' !== get_post_type( $object_id ) ) {
			return;
		}

		foreach ( $translations as $tr_lang => $tr_id ) {
			if ( absint( $tr_id ) !== absint( $object_id ) ) {
				self::sync_data( $object_id, $tr_id, $tr_lang );
			}
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
		$rule    = new WC_BOGOF_Rule( $original_id );
		$tr_rule = new WC_BOGOF_Rule( $tr_id );

		if ( $rule->get_free_product_id() ) {
			$type          = get_post_type( $rule->get_free_product_id() );
			$tr_product_id = pll_get_post( $rule->get_free_product_id(), $lang );
			$tr_rule->set_free_product_id( $tr_product_id );
		}

		foreach ( array( '_buy_product_ids', '_free_product_ids', '_exclude_product_ids', '_buy_category_ids', '_free_category_ids' ) as $prop ) {
			$getter         = 'get' . $prop;
			$setter         = 'set' . $prop;
			$product_ids    = $rule->{$getter}();
			$tr_product_ids = array();
			foreach ( $product_ids as $product_id ) {
				$type = in_array( $prop, array( '_buy_category_ids', '_free_category_ids' ), true ) ? 'product_cat' : 'product';

				if ( 'all' === $product_id && 'product_cat' === $type ) {
					$tr_product_ids[] = 'all';
				} elseif ( 'product_cat' === $type ) {
					$tr_product_ids[] = pll_get_term( $product_id, $lang );
				} else {
					$tr_product_ids[] = pll_get_post( $product_id, $lang );
				}
			}
			$tr_rule->{$setter}( $tr_product_ids );
		}

		$tr_rule->save();
	}

	/**
	 * Do not filter by language the when the query includes the meta _applies_to.
	 *
	 * @param array  $excludes Query vars excluded from the language filter.
	 * @param object $query    WP Query.
	 */
	public static function excluded_query_vars( $excludes, $query ) {
		$qvars = $query->query_vars;
		if ( isset( $qvars['post_type'] ) && 'shop_bogof_rule' === $qvars['post_type'] && isset( $qvars['meta_query'] ) && is_array( $qvars['meta_query'] ) ) {
			foreach ( $qvars['meta_query'] as $data ) {
				if ( is_array( $data ) && isset( $data['key'] ) && '_applies_to' === $data['key'] ) {
					$excludes   = is_array( $excludes ) ? $excludes : array();
					$excludes[] = 'meta_query';
					break;
				}
			}
		}
		return $excludes;
	}

	/**
	 * Filter the list of products of the json search.
	 *
	 * @param array $products Arry of products.
	 * @return array
	 */
	public static function json_search_found_products( $products ) {
		$referer  = wp_get_referer();
		$is_bogof = false !== strpos( $referer, 'post_type=shop_bogof_rule' );

		if ( ! $is_bogof && false !== strpos( $referer, 'post.php?post=' ) ) {
			// Edit post, get post ID.
			$referer = wp_parse_url( $referer, PHP_URL_QUERY );
			$pieces  = explode( '&', $referer );
			if ( isset( $pieces[0] ) ) {
				$post_id  = absint( str_replace( 'post=', '', $pieces[0] ) );
				$is_bogof = 'shop_bogof_rule' === get_post_type( ( $post_id ) );
			}
		}
		if ( $is_bogof ) {
			$current_language = pll_current_language();
			$_products        = $products;
			foreach ( $_products as $id => $product ) {
				$product_language = pll_get_post_language( $id );
				if ( $product_language && $product_language !== $current_language ) {
					unset( $products[ $id ] );
				}
			}
		}

		return $products;
	}

	/**
	 * Retruns the choose your gift page ID for the current language.
	 *
	 * @param string $value Option value.
	 */
	public static function choose_your_gift_page_id( $value ) {
		if ( ! is_admin() ) {
			$value = pll_get_post( $value );
		}
		return $value;
	}

	/**
	 * Returns translate free product IDs
	 *
	 * @param array $product_ids Product IDs to translate.
	 */
	public static function get_free_product_ids( $product_ids ) {
		$product_ids   = is_array( $product_ids ) ? $product_ids : array();
		$translate_ids = array();
		foreach ( $product_ids as $product_id ) {
			$translation_id  = pll_get_post( $product_id );
			$translate_ids[] = $translation_id ? $translation_id : $product_id;
		}
		return $translate_ids;
	}

	/**
	 * Returns translate free product ID
	 *
	 * @param int $product_id Product ID to translate.
	 */
	public static function get_free_product_id( $product_id ) {
		$translation_id = pll_get_post( $product_id );
		return $translation_id ? $translation_id : $product_id;
	}

	/**
	 * Update free product with the flags.
	 *
	 * @since 2.0.12
	 * @param array $item Session data.
	 * @return array
	 */
	public static function translate_cart_item( $item ) {
		if ( isset( $item['_bogof_free_item'] ) ) {
			// Translate the BOGOF rule.
			$translation_id           = pll_get_post( $item['_bogof_free_item'] );
			$item['_bogof_free_item'] = $translation_id ? $translation_id : $item['_bogof_free_item'];

			// Update as a free item.
			$item = WC_BOGOF_Cart::set_cart_item_free( $item, $item['_bogof_free_item'] );

		}
		return $item;
	}
}
