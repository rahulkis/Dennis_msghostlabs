/* global wc_admin_bogof_meta_boxes_params, woocommerce_admin */
;( function( $ ) {

	if ( 'undefined' === typeof wc_admin_bogof_meta_boxes_params ) {
		return;
	}

	/**
	 * Rule metabox actions
	 */
	var wc_meta_boxes_bogof_actions = {

		init: function() {
			$('#_type')
				.on( 'change', this.on_type_change)
				.on( 'change', function() {
					$('#_buy_product_ids').val(null).trigger('change');
				} )
				.on('change', this.on_free_qty_keyup );
			$('#_applies_to').on('change', this.on_applies_to_change);
			$('#_action').on('change', this.on_action_change);

			$('#_free_quantity')
				.on('keyup mouseup', this.on_free_qty_keyup)
				.on('blur', this.remove_error_tips);
			$('#_min_quantity').on('change', this.on_free_qty_keyup);

			this.on_type_change();
			this.on_applies_to_change();
			this.on_action_change();
		},

		add_error_tip: function( element, error_type ) {
			var offset = element.position();

			if ( element.parent().find( '.wc_bogof_error_tip' ).length === 0 ) {
				element.after( '<div class="wc_bogof_error_tip ' + error_type + '">' + wc_admin_bogof_meta_boxes_params[error_type] + '</div>' );
				element.parent().find( '.wc_bogof_error_tip' )
					.css( 'left', offset.left + element.width() - ( element.width() / 2 ) - ( $( '.wc_bogof_error_tip' ).width() / 2 ) )
					.css( 'top', offset.top + element.height() )
					.fadeIn( '100' );
			}
		},

		remove_error_tip: function( element, error_type ) {
			element.parent().find( '.wc_bogof_error_tip.' + error_type ).fadeOut( '100', function() { $( this ).remove(); } );
		},

		remove_error_tips: function() {
			$(this).parent().find( '.wc_bogof_error_tip').fadeOut( '100', function() { $( this ).remove(); } );
		},

		on_type_change: function() {
			$('#_action').closest('div.options_group').toggle( $('#_type').val() === 'buy_a_get_b' );
			$('#_individual').closest('div.options_group').toggle( $('#_type').val() === 'buy_a_get_b' && $('#_applies_to').val() === 'category' );
			if ( $('#_type').val() !== 'buy_a_get_b' ) {
				$('#_buy_product_ids').data( 'action', 'wc_bogof_json_search_free_products' );
				$('#_buy_product_ids').data( 'exclude', wc_admin_bogof_meta_boxes_params.incompatible_types );
			} else {
				$('#_buy_product_ids').data( 'action', '' );
				$('#_buy_product_ids').data( 'exclude', '' );
			}
		},

		on_applies_to_change: function() {
			$('p._buy_product_ids_field').toggle( $('#_applies_to').val() === 'product' );
			$('p._buy_category_ids_field').toggle( $('#_applies_to').val() === 'category' );
			$('#_individual').closest('div.options_group').toggle( $('#_type').val() === 'buy_a_get_b' && $('#_applies_to').val() === 'category' );
		},

		on_action_change: function() {
			var show_class = 'show_if_' + $('#_action').val();

			$('p.action_objects_fields').hide();
			$('p.action_objects_fields.' + show_class).show();
		},

		on_free_qty_keyup: function() {
			if ( $('#_type').val() !== 'cheapest_free' ) {
				wc_meta_boxes_bogof_actions.remove_error_tip( $('#_free_quantity'), 'i18n_free_less_than_min_error' );
			} else {
				var min_quantity  = parseInt( $('#_min_quantity').val(), 10 );
				var free_quantity = parseInt( $('#_free_quantity').val(), 10 );

				if ( free_quantity >= min_quantity ) {
					wc_meta_boxes_bogof_actions.add_error_tip( $('#_free_quantity'), 'i18n_free_less_than_min_error' );
				} else {
					wc_meta_boxes_bogof_actions.remove_error_tip( $('#_free_quantity'), 'i18n_free_less_than_min_error' );
				}
			}
		}
	};
	wc_meta_boxes_bogof_actions.init();

	// Toggle rule on/off.
	$( 'tr.type-shop_bogof_rule' ).on( 'click', '.wc-bogof-rule-toggle-enabled', function( e ) {
		e.preventDefault();
		var $link   = $( this ),
			$toggle = $link.find( '.woocommerce-input-toggle' );

		var data = {
			action:  'wc_bogof_toggle_rule_enabled',
			security: wc_admin_bogof_meta_boxes_params.nonces.rule_toggle,
			rule_id:  $link.data( 'rule_id' )
		};

		$toggle.addClass( 'woocommerce-input-toggle--loading' );

		$.ajax( {
			url:      woocommerce_admin.ajax_url,
			data:     data,
			dataType : 'json',
			type     : 'POST',
			success:  function( response ) {
				if ( true === response.data ) {
					$toggle.removeClass( 'woocommerce-input-toggle--enabled, woocommerce-input-toggle--disabled' );
					$toggle.addClass( 'woocommerce-input-toggle--enabled' );
					$toggle.removeClass( 'woocommerce-input-toggle--loading' );
				} else if ( false === response.data ) {
					$toggle.removeClass( 'woocommerce-input-toggle--enabled, woocommerce-input-toggle--disabled' );
					$toggle.addClass( 'woocommerce-input-toggle--disabled' );
					$toggle.removeClass( 'woocommerce-input-toggle--loading' );
				}
			}
		} );

		return false;
	});
})( jQuery );