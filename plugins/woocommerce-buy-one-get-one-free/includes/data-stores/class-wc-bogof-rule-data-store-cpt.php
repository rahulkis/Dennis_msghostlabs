<?php
/**
 * Class WC_BOGOF_Rule_Data_Store_CPT file.
 *
 * @package WC_BOGOF
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC BOGOF Rule Data Store: Custom Post Type.
 */
class WC_BOGOF_Rule_Data_Store_CPT extends WC_Data_Store_WP implements WC_Object_Data_Store_Interface {

	/**
	 * Data stored in meta keys, but not considered "meta" for a rule.
	 *
	 * @var array
	 */
	protected $internal_meta_keys = array(
		'_enabled',
		'_type',
		'_applies_to',
		'_buy_product_ids',
		'_buy_category_ids',
		'_min_quantity',
		'_action',
		'_free_product_id',
		'_free_product_ids',
		'_free_category_ids',
		'_individual',
		'_free_quantity',
		'_cart_limit',
		'_usage_limit_per_user',
		'_coupon_ids',
		'_exclude_product_ids',
		'_allowed_user_roles',
		'_minimum_amount',
		'_start_date',
		'_end_date',
		'_edit_lock',
		'_edit_last',
		'_wp_old_date',
		'_buy_objects_ids',
	);

	/**
	 * Method to create a new BOGOF rule in the database.
	 *
	 * @param WC_BOGOF_rule $rule BOGOF rule object.
	 */
	public function create( &$rule ) {
		$rule->set_date_created( current_time( 'timestamp', true ) );

		$rule_id = wp_insert_post(
			array(
				'post_type'     => 'shop_bogof_rule',
				'post_status'   => 'publish',
				'post_author'   => get_current_user_id(),
				'post_title'    => $rule->get_title( 'edit' ),
				'post_content'  => '',
				'post_date'     => gmdate( 'Y-m-d H:i:s', $rule->get_date_created()->getOffsetTimestamp() ),
				'post_date_gmt' => gmdate( 'Y-m-d H:i:s', $rule->get_date_created()->getTimestamp() ),
			),
			true
		);

		if ( $rule_id ) {
			$rule->set_id( $rule_id );
			$this->update_post_meta( $rule );
			$rule->save_meta_data();
			$rule->apply_changes();
		}
	}

	/**
	 * Updates a rule in the database.
	 *
	 * @param WC_BOGOF_rule $rule BOGOF rule object.
	 */
	public function update( &$rule ) {
		$rule->save_meta_data();
		$changes = $rule->get_changes();

		if ( array_intersect( array( 'title', 'date_created', 'date_modified' ), array_keys( $changes ) ) ) {
			$post_data = array(
				'post_title'        => $rule->get_title( 'edit' ),
				'post_date'         => gmdate( 'Y-m-d H:i:s', $rule->get_date_created( 'edit' )->getOffsetTimestamp() ),
				'post_date_gmt'     => gmdate( 'Y-m-d H:i:s', $rule->get_date_created( 'edit' )->getTimestamp() ),
				'post_modified'     => isset( $changes['date_modified'] ) ? gmdate( 'Y-m-d H:i:s', $rule->get_date_modified( 'edit' )->getOffsetTimestamp() ) : current_time( 'mysql' ),
				'post_modified_gmt' => isset( $changes['date_modified'] ) ? gmdate( 'Y-m-d H:i:s', $rule->get_date_modified( 'edit' )->getTimestamp() ) : current_time( 'mysql', 1 ),
			);

			/**
			 * When updating this object, to prevent infinite loops, use $wpdb
			 * to update data, since wp_update_post spawns more calls to the
			 * save_post action.
			 *
			 * This ensures hooks are fired by either WP itself (admin screen save),
			 * or an update purely from CRUD.
			 */
			if ( doing_action( 'save_post' ) ) {
				$GLOBALS['wpdb']->update( $GLOBALS['wpdb']->posts, $post_data, array( 'ID' => $rule->get_id() ) );
				clean_post_cache( $rule->get_id() );
			} else {
				wp_update_post( array_merge( array( 'ID' => $rule->get_id() ), $post_data ) );
			}
			$rule->read_meta_data( true ); // Refresh internal meta data, in case things were hooked into `save_post` or another WP hook.
		}
		$this->update_post_meta( $rule );
		$rule->apply_changes();
	}

	/**
	 * Deletes a rule from the database.
	 *
	 * @param WC_BOGOF_rule $rule BOGOF rule object.
	 * @param array         $args Array of args to pass to the delete method.
	 */
	public function delete( &$rule, $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'force_delete' => false,
			)
		);

		$id = $rule->get_id();

		if ( ! $id ) {
			return;
		}

		if ( $args['force_delete'] ) {
			wp_delete_post( $id );
			$rule->set_id( 0 );
		} else {
			wp_trash_post( $id );
		}
	}

	/**
	 * Method to read a rule.
	 *
	 * @param WC_BOGOF_rule $rule BOGOF rule object.
	 *
	 * @throws Exception If invalid rule.
	 */
	public function read( &$rule ) {
		$rule->set_defaults();
		$post_object = get_post( $rule->get_id() );

		if ( ! $rule->get_id() || ! $post_object || 'shop_bogof_rule' !== $post_object->post_type ) {
			throw new Exception( __( 'Invalid BOGO rule.', 'wc-buy-one-get-one-free' ) );
		}
		$rule->set_props(
			array(
				'title'         => $post_object->post_title,
				'date_created'  => 0 < $post_object->post_date_gmt ? wc_string_to_timestamp( $post_object->post_date_gmt ) : null,
				'date_modified' => 0 < $post_object->post_modified_gmt ? wc_string_to_timestamp( $post_object->post_modified_gmt ) : null,
			)
		);
		$this->read_properties( $rule );
		$rule->read_meta_data();
		$rule->set_object_read( true );
	}

	/**
	 * Reads rule properties from meta data
	 *
	 * @param WC_BOGOF_rule $rule BOGOF rule object.
	 */
	protected function read_properties( &$rule ) {
		$post_meta_values = get_post_meta( $rule->get_id() );
		$set_props        = array();

		foreach ( $post_meta_values as $meta_key => $meta_value ) {
			if ( in_array( $meta_key, array( '_title', '_date_created', '_date_modified', '_edit_lock', '_edit_last', '_wp_old_date', '_buy_objects_ids' ), true ) ) {
				continue;
			}
			$prop  = substr( $meta_key, 1 );
			$value = isset( $meta_value[0] ) ? $meta_value[0] : null;

			$set_props[ $prop ] = maybe_unserialize( $value ); // get_post_meta only unserializes single values.
		}

		$rule->set_props( $set_props );
	}

	/**
	 * Helper method that updates all the post meta for a rule based on it's settings in the WC_BOGOF_rule class.
	 *
	 * @param WC_BOGOF_rule $rule BOGOF rule object.
	 */
	protected function update_post_meta( &$rule ) {
		$updated_props     = array();
		$meta_key_to_props = array();

		foreach ( $this->internal_meta_keys as $meta_key ) {
			if ( in_array( $meta_key, array( '_title', '_date_created', '_date_modified', '_edit_lock', '_edit_last', '_wp_old_date', '_buy_objects_ids' ), true ) ) {
				continue;
			}
			$meta_key_to_props[ $meta_key ] = substr( $meta_key, 1 );
		}

		$props_to_update = $this->get_props_to_update( $rule, $meta_key_to_props );

		foreach ( $props_to_update as $meta_key => $prop ) {
			$value = $rule->{"get_$prop"}( 'edit' );
			$value = is_string( $value ) ? wp_slash( $value ) : $value;
			switch ( $prop ) {
				case 'enabled':
				case 'individual':
					$value = wc_bool_to_string( $value );
					break;
				case 'start_date':
				case 'end_date':
					$value = $value ? $value->getTimestamp() : '';
					break;
			}

			$updated = update_post_meta( $rule->get_id(), $meta_key, $value );

			if ( $updated ) {
				$updated_props[] = $prop;
			}
		}

		$this->validate_updated_properties( $rule, $updated_props );
		$this->update_buy_object_meta_key( $rule, $updated_props );
		$this->clear_caches( $rule->get_id() );
	}

	/**
	 * Validate updated properties after updating meta data.
	 *
	 * @param WC_BOGOF_rule $rule Rule object.
	 * @param array         $updated_props Update properties.
	 */
	protected function validate_updated_properties( &$rule, $updated_props ) {
		if ( wc_bogof_in_array_intersect( array( 'min_quantity', 'free_quantity' ), $updated_props ) && 'cheapest_free' === $rule->get_type() ) {
			if ( $rule->get_free_quantity() >= $rule->get_min_quantity() ) {
				$free_quantity = $rule->get_min_quantity() - 1 > 0 ? $rule->get_min_quantity() - 1 : 0;
				update_post_meta( $rule->get_id(), '_free_quantity', $free_quantity );
			}
		}
	}

	/**
	 * Update buy_object_ids meta key.
	 *
	 * @param WC_BOGOF_rule $rule Rule object.
	 * @param array         $updated_props Update properties.
	 */
	protected function update_buy_object_meta_key( &$rule, $updated_props ) {
		if ( array_intersect( array( 'buy_product_ids', 'buy_category_ids', 'applies_to' ), $updated_props ) ) {
			if ( 'product' === $rule->get_applies_to() ) {
				$value = ',' . implode( ',', $rule->get_buy_product_ids() ) . ',';
			} else {
				$value = ',' . implode( ',', $rule->get_buy_category_ids() ) . ',';
			}
			update_post_meta( $rule->get_id(), '_buy_objects_ids', $value );
		}
	}

	/**
	 * Clear any caches.
	 *
	 * @param int $rule_id Rule ID.
	 */
	protected function clear_caches( $rule_id ) {
		if ( version_compare( WC_VERSION, '3.9', '>=' ) ) {
			WC_Cache_Helper::invalidate_cache_group( 'bogof_rule_' . $rule_id );
		} else {
			WC_Cache_Helper::incr_cache_prefix( 'bogof_rule_' . $rule_id );
		}
		WC_Cache_Helper::get_transient_version( 'bogof_rules', true );
	}

	/**
	 * Is a free product?
	 *
	 * @param int           $product_id Product ID.
	 * @param WC_BOGOF_rule $rule Rule object.
	 * @return bool
	 */
	public function is_free_product( $product_id, $rule ) {

		$cache_key   = WC_Cache_Helper::get_cache_prefix( 'bogof_rule_' . $rule->get_id() ) . $rule->get_id() . '_' . WC_Cache_Helper::get_cache_prefix( 'product_' . $product_id ) . $product_id . '_is_free_product';
		$cache_value = wp_cache_get( $cache_key, 'wc_bogof' );

		if ( false !== $cache_value ) {
			return 'yes' === $cache_value;
		}

		$is_free = false;

		if ( $rule->is_action( 'choose_from_category' ) ) {
			$is_free = wc_bogof_product_in_category( $product_id, $rule->get_free_category_ids() );
		} else {
			$is_free = in_array( $product_id, $rule->get_free_product_ids() ); // phpcs:ignore WordPress.PHP.StrictInArray
		}

		if ( ! $is_free && 'product_variation' === get_post_type( $product_id ) ) {
			$is_free = $this->is_free_product( wp_get_post_parent_id( $product_id ), $rule );
		}

		wp_cache_set( $cache_key, ( $is_free ? 'yes' : 'no' ), 'wc_bogof' );

		return $is_free;
	}

	/**
	 * Is a buy product?
	 *
	 * @param int           $product_id Product ID.
	 * @param WC_BOGOF_rule $rule Rule object.
	 * @return bool
	 */
	public function is_buy_product( $product_id, $rule ) {

		$cache_key   = WC_Cache_Helper::get_cache_prefix( 'bogof_rule_' . $rule->get_id() ) . $rule->get_id() . '_' . WC_Cache_Helper::get_cache_prefix( 'product_' . $product_id ) . $product_id . '_is_buy_product';
		$cache_value = wp_cache_get( $cache_key, 'wc_bogof' );

		if ( false !== $cache_value ) {
			return 'yes' === $cache_value;
		}

		$is_buy = false;

		if ( 'product' === $rule->get_applies_to() ) {
			$is_buy = in_array( $product_id, $rule->get_buy_product_ids() ); // phpcs:ignore WordPress.PHP.StrictInArray
		} else {
			$is_buy = wc_bogof_product_in_category( $product_id, $rule->get_buy_category_ids() );
		}

		if ( ! $is_buy && 'product_variation' === get_post_type( $product_id ) ) {
			$is_buy = $this->is_buy_product( wp_get_post_parent_id( $product_id ), $rule );
		}

		wp_cache_set( $cache_key, ( $is_buy ? 'yes' : 'no' ), 'wc_bogof' );

		return $is_buy;
	}

	/**
	 * Returns the coupon codes of a rule.
	 *
	 * @param WC_BOGOF_rule $rule Rule object.
	 * @return array
	 */
	public function get_coupon_codes( &$rule ) {
		$cache_key = WC_Cache_Helper::get_cache_prefix( 'bogof_rule_' . $rule->get_id() ) . $rule->get_id() . '_coupon_codes';
		$codes     = wp_cache_get( $cache_key, 'wc_bogof' );

		if ( false !== $codes && is_array( $codes ) ) {
			return $codes;
		}

		$codes = array();
		$ids   = $rule->get_coupon_ids();
		if ( ! empty( $ids ) ) {
			$posts = get_posts(
				array(
					'post_type'      => 'shop_coupon',
					'post_status'    => 'publish',
					'posts_per_page' => -1,
					'post__in'       => $ids,
				)
			);

			foreach ( $posts as $post ) {
				$codes[] = function_exists( 'wc_format_coupon_code' ) ? wc_format_coupon_code( $post->post_title ) : $post->post_title;
			}
		}

		wp_cache_set( $cache_key, $codes, 'wc_bogof' );

		return $codes;
	}

	/**
	 * Return the rules that applies to an array of object ids.
	 *
	 * @param string $applies_to Applies to metakey value (product|category).
	 * @param array  $buy_objects_ids Array of object ids.
	 */
	protected function get_rules_applies_to( $applies_to, $buy_objects_ids ) {

		$cache_key = 'get_rules_applies_to_' . md5(
			wp_json_encode(
				array(
					'_applies_to'      => $applies_to,
					'_buy_objects_ids' => $buy_objects_ids,
					'version'          => WC_Cache_Helper::get_transient_version( 'bogof_rules' ),
				)
			)
		);

		$ids = wp_cache_get( $cache_key, 'wc_bogof' );

		if ( false !== $ids && is_array( $ids ) ) {
			return $ids;
		}

		$meta_query = array(
			'relation' => 'OR',
		);
		foreach ( $buy_objects_ids as $objects_id ) {
			$meta_query[] = array(
				'key'     => '_buy_objects_ids',
				'value'   => ',' . $objects_id . ',',
				'compare' => 'LIKE',
				'type'    => 'CHAR',
			);
		}

		$ids = get_posts(
			array(
				'post_type'      => 'shop_bogof_rule',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
				'fields'         => 'ids',
				'meta_query'     => array(
					'relation' => 'AND',
					array(
						'key'     => '_applies_to',
						'value'   => $applies_to,
						'compare' => '=',
					),
					array(
						$meta_query,
					),
				),
			)
		);

		wp_cache_set( $cache_key, $ids, 'wc_bogof' );

		return $ids;
	}

	/**
	 * Returns data stored in a transient.
	 *
	 * @param string $transient_name Transient name.
	 * @param string $version transient version.
	 * @return array|bool
	 */
	protected function get_transient_data( $transient_name, $version = false ) {
		$data = get_transient( $transient_name );

		if ( false === $data || empty( $data['version'] ) ) {
			return false;
		}
		$version = false === $version ? WC_Cache_Helper::get_transient_version( 'bogof_rules' ) : $version;

		if ( $version === $data['version'] && isset( $data['data'] ) ) {
			$data = $data['data'];
		} else {
			$data = false;
		}

		return $data;
	}

	/**
	 * Strores data in a transient.
	 *
	 * @param string $transient_name Transient name.
	 * @param mixed  $data Data to store.
	 * @param string $version transient version.
	 */
	protected function set_transient_data( $transient_name, $data, $version = false ) {
		$data = array(
			'version' => false === $version ? WC_Cache_Helper::get_transient_version( 'bogof_rules' ) : $version,
			'data'    => $data,
		);
		set_transient( $transient_name, $data, 30 * DAY_IN_SECONDS );
	}

	/**
	 * Get a lists of rules by a product ID.
	 *
	 * @param int $product_id Product ID.
	 * @return array
	 */
	public function get_rules_by_product( $product_id ) {
		$cache_key = 'wc_bogof_rules_' . $product_id;
		$ids       = $this->get_transient_data( $cache_key );

		if ( false === $ids || ! is_array( $ids ) ) {

			$product_ids = array( $product_id );
			if ( 'product_variation' === get_post_type( $product_id ) ) {
				$product_ids = array_merge( $product_ids, array( wp_get_post_parent_id( $product_id ) ) );
			}

			$ids = $this->get_rules_applies_to( 'product', $product_ids );

			$cat_ids = array( 'all' );
			foreach ( $product_ids as $p_id ) {
				$cat_ids = array_unique( array_filter( array_merge( $cat_ids, wc_bogof_get_product_cats( $p_id ) ) ) );
			}

			if ( ! empty( $cat_ids ) ) {
				$ids = array_unique( array_filter( array_merge( $ids, $this->get_rules_applies_to( 'category', $cat_ids ) ) ) );
			}

			$this->set_transient_data( $cache_key, $ids );
		}

		$rules = array();

		foreach ( $ids as $rule_id ) {
			$rule = new WC_BOGOF_Rule( $rule_id );
			if ( in_array( $product_id, $rule->get_exclude_product_ids() ) ) { // phpcs:ignore WordPress.PHP.StrictInArray
				continue;
			}
			$rules[ $rule_id ] = $rule;
		}
		return $rules;
	}

	/**
	 * Increase usage count for current rule.
	 *
	 * @param WC_BOGOF_rule $rule Rule object.
	 * @param WC_Order      $order Order object.
	 */
	public function increase_usage_count( $rule, $order ) {
		$order->add_meta_data( '_wc_bogof_rule_id', $rule->get_id() );

		delete_transient( 'wc_bogof_uses_' . $rule->get_id() );
	}

	/**
	 * Returns the number of times a user used a rule.
	 *
	 * @param string        $used_by Either user ID or billing email.
	 * @param WC_BOGOF_rule $rule Rule object.
	 */
	public function get_used_by_count( $used_by, $rule ) {
		$cache_key    = 'wc_bogof_uses_' . $rule->get_id();
		$data         = get_transient( $cache_key );
		$used_by      = array_filter( array_unique( array_map( 'strtolower', ( is_array( $used_by ) ? $used_by : array( $used_by ) ) ) ) );
		$used_by_s    = "'" . implode( "','", array_map( 'sanitize_text_field', $used_by ) ) . "'";
		$used_by_hash = md5( $used_by_s );

		if ( ! is_array( $data ) ) {
			$data = array();
		}

		if ( ! isset( $data[ $used_by_hash ] ) || ! is_numeric( $data[ $used_by_hash ] ) ) {

			global $wpdb;

			$sql  = "SELECT count( posts.ID ) FROM {$wpdb->posts} posts ";
			$sql .= "INNER JOIN {$wpdb->postmeta} postmeta ON postmeta.post_id= posts.ID ";
			$sql .= "LEFT JOIN {$wpdb->postmeta} billing_email ON billing_email.post_id = posts.ID and billing_email.meta_key = '_billing_email' ";
			$sql .= "LEFT JOIN {$wpdb->postmeta} customer_user ON customer_user.post_id = posts.ID and customer_user.meta_key = '_customer_user' ";
			$sql .= 'WHERE postmeta.meta_key = \'_wc_bogof_rule_id\' AND postmeta.meta_value = %d ';
			$sql .= "AND posts.post_status IN ( 'wc-completed', 'wc-processing', 'wc-on-hold' ) ";
			$sql .= 'AND ( billing_email.meta_value IN (' . $used_by_s . ') OR customer_user.meta_value IN (' . $used_by_s . ') )';

			$count = $wpdb->get_var( $wpdb->prepare( $sql, $rule->get_id() ) ); //phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

			$data[ $used_by_hash ] = $count;

			set_transient( $cache_key, $data, 30 * DAY_IN_SECONDS );
		}

		return absint( $data[ $used_by_hash ] );
	}
}
