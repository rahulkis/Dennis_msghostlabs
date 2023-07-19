<?php
/**
 * Installation related functions and actions.
 *
 * @package  WC_BOGOF
 */

defined( 'ABSPATH' ) || exit;

/**
 * WC_Install Class.
 */
class WC_BOGOF_Install {

	/**
	 * Updates and callbacks that need to be run per version.
	 *
	 * @var array
	 */
	private static $db_updates = array(
		'2.0.0' => array(
			'update_db_200',
		),
	);


	/**
	 * Init hooks
	 */
	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'update_db' ), 5 );
		add_action( 'admin_init', array( __CLASS__, 'check_version' ), 10 );

		// Welcome notice.
		add_action( 'admin_init', array( __CLASS__, 'hide_welcome_notice' ) );
		add_action( 'admin_notices', array( __CLASS__, 'welcome_notice' ) );

		// Plugin row.
		add_filter( 'plugin_action_links_' . plugin_basename( WC_BOGOF_PLUGIN_FILE ), array( __CLASS__, 'plugin_action_links' ) );
		add_filter( 'plugin_row_meta', array( __CLASS__, 'plugin_row_meta' ), 10, 2 );
	}

	/**
	 * Update database to the last version.
	 */
	public static function update_db() {
		if ( empty( $_GET['update_wc_bogof_nonce'] ) ) {
			return;
		}
		check_admin_referer( 'do_update_wc_bogof', 'update_wc_bogof_nonce' );

		$current_version = self::get_install_version();
		foreach ( self::$db_updates as $version => $callbacks ) {
			if ( version_compare( $current_version, $version, '<' ) ) {
				foreach ( $callbacks as $callback ) {
					self::{$callback}();
				}
			}
		}

		update_option( 'wc_bogof_version', WC_Buy_One_Get_One_Free::$version );

		add_action( 'admin_notices', array( __CLASS__, 'database_update_complated_notice' ) );
	}

	/**
	 * Check version and run the updater is required.
	 */
	public static function check_version() {
		if ( defined( 'IFRAME_REQUEST' ) ) {
			return;
		}

		$current_version = self::get_install_version();
		$updates_count   = 0;

		if ( false !== $current_version && version_compare( $current_version, WC_Buy_One_Get_One_Free::$version, '<' ) ) {

			foreach ( self::$db_updates as $version => $callback ) {
				if ( version_compare( $current_version, $version, '<' ) ) {
					$updates_count++;
				}
			}

			if ( $updates_count > 0 ) {
				add_action( 'admin_notices', array( __CLASS__, 'database_update_required_notice' ) );
			}
		}

		if ( 0 === $updates_count ) {
			self::update_options( $current_version );
		}
	}

	/**
	 * Return the install version.
	 *
	 * @return string
	 */
	private static function get_install_version() {
		$version = get_option( 'wc_bogof_version', false );

		if ( false === $version ) {
			$deprecated_option = get_option( 'wc_bogof_category_settings', false );
			if ( false !== $deprecated_option ) {
				$version = '1.3.0';
			}

			if ( ! $version ) {
				$posts = get_posts(
					array(
						'post_type'      => 'product',
						'post_status'    => 'publish',
						'posts_per_page' => 1,
						'meta_key'       => '_bogof_enabled',
					)
				);
				if ( count( $posts ) > 0 ) {
					$version = '1.3.0';
				}
			}
		}
		return $version;
	}

	/**
	 * Update options on version changes.
	 *
	 * @param string $current_version Current version.
	 */
	private static function update_options( $current_version ) {
		if ( WC_Buy_One_Get_One_Free::$version === $current_version ) {
			// No updates are required.
			return;
		}

		if ( false !== $current_version && version_compare( $current_version, '2.1.0', '<' ) && false === get_option( 'wc_bogof_cyg_display_on', false ) ) {
			update_option( 'wc_bogof_cyg_display_on', 'custom_page' );
		}
		// Update the plugin version.
		update_option( 'wc_bogof_version', WC_Buy_One_Get_One_Free::$version );
	}

	/**
	 * Display the database update required notice.
	 */
	public static function database_update_required_notice() {
		?>
		<div class="error"><p>
		<?php
		// translators: HTML tags.
		printf( esc_html( __( '%1$sBuy One Get One Free Database Update Required%2$s We just need to update your install to the latest version.', 'wc-buy-one-get-one-free' ) ), '<strong>', '</strong> &#8211;' );
		?>
		</p><p class="submit"><a href="<?php echo esc_url( wp_nonce_url( admin_url( 'edit.php?post_type=shop_bogof_rule' ), 'do_update_wc_bogof', 'update_wc_bogof_nonce' ) ); ?>" class="wc-update-now button-primary"><?php esc_html_e( 'Run the updater', 'wc-buy-one-get-one-free' ); ?></a></p>
		</div>
		<script type="text/javascript">
			jQuery('.wc-update-now').click('click', function(){
				var answer = confirm( '<?php esc_html_e( 'It is strongly recommended that you backup your database before proceeding. Are you sure you wish to run the updater now?', 'wc-buy-one-get-one-free' ); ?>' );
				return answer;
			});
		</script>
		<?php
	}

	/**
	 * Display the database update completed notice.
	 */
	public static function database_update_complated_notice() {
		?>
		<div class="notice notice-success is-dismissible">
			<p>
				<?php esc_html_e( 'Thank you for updating to the latest version of Buy One Get One Free!', 'wc-buy-one-get-one-free' ); ?>
			</p>
			<p>
				<?php
				// translators: 1: the plugin version, 2 and 3: HTML tags.
				printf( esc_html__( 'Version %1$s brings some great new features. Take a moment to review the %2$splugin documentation%3$s.', 'wc-buy-one-get-one-free' ), esc_html( WC_Buy_One_Get_One_Free::$version ), '<a href="https://docs.woocommerce.com/document/buy-one-get-one-free/" target="_blank" rel="noopener">', '</a>' );
				?>
			</p>
		</div>
		<?php
	}

	/**
	 * Hide welcome notice.
	 */
	public static function hide_welcome_notice() {
		if ( isset( $_GET['hide-wc-bogof-welcome'] ) ) {
			check_admin_referer( 'hide-wc-bogof-welcome-notice', 'hide-wc-bogof-welcome' );

			// Delete plugin activate transient.
			delete_transient( 'bogof_plugin_activated' );
		}
	}

	/**
	 * Display welcome notice.
	 */
	public static function welcome_notice() {

		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id : '';

		if ( ( in_array( $screen_id, wc_get_screen_ids(), true ) || 'plugins' === $screen_id ) && get_transient( 'bogof_plugin_activated' ) ) {

			$nonce = wp_create_nonce( 'hide-wc-bogof-welcome-notice' );
			// translators: HTML tags.
			$message = sprintf( __( '%1$sWooCommerce Buy One Get One Free%2$s installed. You\'re ready to add your first BOGO rule.', 'wc-buy-one-get-one-free' ), '<strong>', '</strong>' );
			?>
			<div class="updated woocommerce-message">
				<a class="woocommerce-message-close notice-dismiss" href="<?php echo esc_url( add_query_arg( 'hide-wc-bogof-welcome', $nonce ) ); ?>"><?php esc_html_e( 'Dismiss', 'wc-buy-one-get-one-free' ); ?></a>
				<p><?php echo wp_kses_post( $message ); ?></p>
				<p class="submit">
					<a href="<?php echo esc_url( add_query_arg( 'hide-wc-bogof-welcome', $nonce, admin_url( 'post-new.php?post_type=shop_bogof_rule' ) ) ); ?>" class="button button-primary"><?php esc_html_e( 'Add a BOGO rule', 'wc-buy-one-get-one-free' ); ?></a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings&tab=products&section=buy-one-get-one-free' ) ); ?>" class="button"><?php esc_html_e( 'Settings', 'wc-buy-one-get-one-free' ); ?></a>
					<a href="https://docs.woocommerce.com/document/buy-one-get-one-free/" target="_blank" rel="noopener" class="button"><?php esc_html_e( 'View documentation', 'wc-buy-one-get-one-free' ); ?></a>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * Show action links on the plugin screen.
	 *
	 * @param mixed $links Plugin Action links.
	 *
	 * @return array
	 */
	public static function plugin_action_links( $links ) {
		$action_links = array(
			'settings' => '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=products&section=buy-one-get-one-free' ) . '" aria-label="' . esc_attr__( 'View plugin settings', 'wc-buy-one-get-one-free' ) . '">' . esc_html__( 'Settings', 'wc-buy-one-get-one-free' ) . '</a>',
		);

		return array_merge( $action_links, $links );
	}

	/**
	 * Show row meta on the plugin screen.
	 *
	 * @param mixed $links Plugin Row Meta.
	 * @param mixed $file  Plugin Base file.
	 *
	 * @return array
	 */
	public static function plugin_row_meta( $links, $file ) {

		if ( plugin_basename( WC_BOGOF_PLUGIN_FILE ) === $file ) {
			$row_meta = array(
				'docs' => '<a href="' . esc_url( 'https://docs.woocommerce.com/document/buy-one-get-one-free/' ) . '" aria-label="' . esc_attr__( 'View documentation', 'wc-buy-one-get-one-free' ) . '">' . esc_html__( 'Docs', 'wc-buy-one-get-one-free' ) . '</a>',
			);

			return array_merge( $links, $row_meta );
		}

		return (array) $links;
	}

	/**
	 * Create pages that the plugin relies on, storing page IDs in variables.
	 */
	public static function create_pages() {
		include_once dirname( WC_PLUGIN_FILE ) . '/includes/admin/wc-admin-functions.php';

		$page = array(
			'name'    => _x( 'choose your gift', 'Page slug', 'wc-buy-one-get-one-free' ),
			'title'   => _x( 'Choose your gift', 'Page title', 'wc-buy-one-get-one-free' ),
			'content' => '<!-- wp:shortcode -->[wc_choose_your_gift]<!-- /wp:shortcode -->',
		);

		wc_create_page( esc_sql( $page['name'] ), 'wc_bogof_cyg_page_id', $page['title'], $page['content'] );
	}

	/**
	 * Update database to version 2.0.0.
	 */
	public static function update_db_200() {
		// Import from products.
		$post_ids = get_posts(
			array(
				'fields'         => 'ids',
				'post_type'      => 'product',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'meta_key'       => '_bogof_enabled',
				'meta_value'     => 'yes',
			)
		);

		foreach ( $post_ids as $id ) {

			$rule = new WC_BOGOF_Rule();
			$rule->set_title( 'Imported from product #' . $id );
			$rule->set_enabled( true );
			$rule->set_type( 'aa' === get_post_meta( $id, '_bogof_mode', true ) ? 'buy_a_get_a' : 'buy_a_get_b' );
			$rule->set_applies_to( 'product' );
			$rule->set_min_quantity( get_post_meta( $id, '_bogof_min_quantity', true ) );
			$rule->set_action( 'add_to_cart' );
			$rule->set_free_product_id( get_post_meta( $id, '_bogof_product_id', true ) );
			$rule->set_free_quantity( get_post_meta( $id, '_bogof_free_quantity', true ) );
			$rule->set_cart_limit( get_post_meta( $id, '_bogof_free_quantity', true ) );

			$variations_rule = get_post_meta( $id, '_bogof_variations_rule', true );
			if ( 'specific' === $variations_rule ) {
				$rule->set_buy_product_ids( get_post_meta( $id, '_bogof_specific_variations', true ) );
			} else {
				$rule->set_buy_product_ids( array( $id ) );
				if ( 'except' === $variations_rule ) {
					$rule->set_exclude_product_ids( get_post_meta( $id, '_bogof_except_variations', true ) );
				}
			}
			$rule->save();
		}

		// Import from category settings.
		$category_settings = get_option( 'wc_bogof_category_settings', false );
		if ( is_array( $category_settings ) ) {
			foreach ( $category_settings as $term_id => $setting ) {
				if ( 'yes' === $setting['enabled'] ) {
					$term = get_term( $term_id, 'product_cat' );
					if ( $term ) {
						$rule = new WC_BOGOF_Rule();
						$rule->set_title( 'Imported from category #' . $term->name );
						$rule->set_enabled( true );
						$rule->set_type( 'aa' === $setting['mode'] ? 'buy_a_get_a' : 'buy_a_get_b' );
						$rule->set_applies_to( 'category' );
						$rule->set_buy_category_ids( array( $term_id ) );
						$rule->set_min_quantity( $setting['min_quantity'] );
						$rule->set_action( 'add_to_cart' );
						$rule->set_free_product_id( $setting['product_id'] );
						$rule->set_free_quantity( $setting['free_quantity'] );
						$rule->set_cart_limit( $setting['limit'] );

						if ( 'cb' !== $setting['mode'] ) {
							$rule->set_individual( true );
						}
						$rule->save();
					}
				}
			}
		}
	}
}
