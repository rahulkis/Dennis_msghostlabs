/* global wc_gc_params */
( function( $, document ) {

	$( function() {

		/**
		 * Gets a url for a given AJAX endpoint.
		 *
		 * @param {String} endpoint The AJAX Endpoint
		 * @return {String} The URL to use for the request
		 */
		var get_url = function( endpoint ) {

			return wc_gc_params.wc_ajax_url.toString().replace(
				'%%endpoint%%',
				endpoint
			);
		};

		/**
		 * Check if a node is blocked for processing.
		 *
		 * @param {JQuery Object} $node
		 * @return {bool} True if the DOM Element is UI Blocked, false if not.
		 */
		var is_blocked = function( $node ) {
			return $node.is( '.processing' ) || $node.parents( '.processing' ).length;
		};

		/**
		 * Block a node visually for processing.
		 *
		 * @param {JQuery Object} $node
		 */
		var block = function( $node ) {
			if ( ! is_blocked( $node ) ) {

				$node.addClass( 'processing' ).block( {
					message: null,
					overlayCSS: {
						background: '#fff',
						opacity: 0.6
					}
				} );
			}
		};

		/**
		 * Unblock a node after processing is complete.
		 *
		 * @param {JQuery Object} $node
		 */
		var unblock = function( $node ) {
			$node.removeClass( 'processing' ).unblock();
		};

		/**
		 * Update the .cart_totals div with a string of html.
		 *
		 * @param {String} html_str The HTML string with which to replace the div.
		 */
		var update_cart_totals_div = function( html_str ) {
			$( '.cart_totals' ).replaceWith( html_str );
			$( document.body ).trigger( 'updated_cart_totals' );
		};


		/**
		 * Toggle use balance checkbox.
		 */
		$( 'div.cart-collaterals' ).on( 'change', '#use_gift_card_balance', function() {

			var $el               = $( this ),
				use               = $el.is( ':checked' ),
				wc_gc_use_balance = use === true ? 'on' : 'off';

			block( $( 'div.cart_totals' ) );

			$.ajax( {
				type:     'post',
				url:      get_url( 'toggle_balance_usage' ),
				data:     'wc_gc_use_balance=' + wc_gc_use_balance + '&security=' + wc_gc_params.security_update_use_balance_nonce,
				dataType: 'html',
				success:  function( response ) {
					unblock( $( 'div.cart_totals' ) );
					update_cart_totals_div( response );
				}
			} );

		} );

		/**
		 * Remove gift card from cart buttons.
		 */
		$( 'div.cart-collaterals' ).on( 'click', '.wc_gc_remove_gift_card', function( e ) {
			e.preventDefault();

			var $el         = $( this ),
				giftcard_id = $el.data( 'giftcard' );

			block( $( 'div.cart_totals' ) );

			$.ajax( {
				type:     'post',
				url:      get_url( 'remove_gift_card_from_session' ),
				data:     'wc_gc_cart_id=' + giftcard_id + '&render_cart_fragments=1&security=' + wc_gc_params.security_remove_card_nonce,
				dataType: 'html',
				success:  function( response ) {
					unblock( $( 'div.cart_totals' ) );
					update_cart_totals_div( response );
				}
			} );

			return false;
		} );

		/**
		 * Remove gift card from checkout buttons.
		 */
		$( '#order_review' ).on( 'click', '.wc_gc_remove_gift_card', function( e ) {
			e.preventDefault();

			var $el           = $( this ),
			    giftcard_id   = $el.data( 'giftcard' );

			if ( giftcard_id ) {

				var $remove_input = $( '<input />' );
				// Add props.
				$remove_input.prop( 'type', 'hidden' );
				$remove_input.prop( 'class', 'wc_gc_cart_remove_giftcards' );
				$remove_input.prop( 'name', 'wc_gc_cart_remove_giftcards' );

				// Add id to be removed.
				$remove_input.appendTo( '#order_review' );
				$remove_input.val( giftcard_id );
				$( document.body ).trigger( 'update_checkout' );

				// Clear input for sanity.
				setTimeout( function() {
					$remove_input.remove();
				}, 500 );
			}

			return false;
		} );

		/**
		 * Checkout form handler.
		 */
		$( '#order_review' ).on( 'click', '#wc_gc_cart_redeem_send', function( e ) {
			e.preventDefault();

			var code = $( '#wc_gc_cart_code' ).val();

			if ( code ) {
				$( document.body ).trigger( 'update_checkout' );
			}
		} );

		/**
		 * Cart form handler.
		 */
		$( 'div.cart-collaterals' ).on( 'click', '#wc_gc_cart_redeem_send', function( e ) {
			e.preventDefault();

			var code = $( '#wc_gc_cart_code' ).val();

			if ( ! code ) {
				return;
			}

			block( $( 'div.cart_totals' ) );

			$.ajax( {
				type:     'post',
				url:      get_url( 'apply_gift_card_to_session' ),
				data:     'wc_gc_cart_code=' + code + '&security=' + wc_gc_params.security_redeem_card_nonce,
				dataType: 'html',
				success:  function( response ) {
					unblock( $( 'div.cart_totals' ) );
					update_cart_totals_div( response );
				}
			} );

			return false;
		} );

		// Init datepickers.
		$( '.woocommerce_gc_giftcard_form' ).wc_gc_datepickers();

	} );

	/**
	 * Gift card Datepicker extend.
	 */
	$.fn.wc_gc_datepickers = function() {

		var $datepickers = $( this ).find( '.datepicker' );

		$datepickers.each( function( index ) {

			// Cache local instances.
			var $datepicker       = $( this ),
				$container        = $datepicker.parent(),
				$timestamp_input  = $container.find( 'input[name="wc_gc_giftcard_delivery"]' ),
				$offset_gmt_input = $container.find( 'input[name="_wc_gc_giftcard_delivery_gmt_offset"]' );

			// Make Template backwards compatible.
			if ( ! $offset_gmt_input.length ) {
				$offset_gmt_input = $( '<input/>' );
				$offset_gmt_input.attr( 'type', 'hidden' );
				$offset_gmt_input.attr( 'name', '_wc_gc_giftcard_delivery_gmt_offset' );
				$container.append( $offset_gmt_input );
			}

			// Fill GMT offset.
			var now           = new Date(),
				gmt_offset    = parseFloat( wc_gc_params.gmt_offset, 10 ),
				client_offset = now.getTimezoneOffset() / 60,
				datepicker_min_date;

			var diff = client_offset - gmt_offset;

			if ( 'default' === wc_gc_params.date_input_timezone_reference ) {

				$offset_gmt_input.val( client_offset );
				datepicker_min_date = '+1D';

			} else if ( 'store' === wc_gc_params.date_input_timezone_reference ) {

				var hours_now  = now.getHours() + now.getMinutes() / 60,
					day_factor = hours_now + diff;

				$offset_gmt_input.val( gmt_offset );

				if ( day_factor >= 24 ) {
					datepicker_min_date = '+' + ( Math.floor( day_factor / 24 ) + 1 ) + 'D';
				} else if ( day_factor <= 0 ) {
					datepicker_min_date = 0;
				} else {
					datepicker_min_date = '+1D';
				}
			}

			// Init datepicker.
			$datepicker.datepicker( {
				beforeShow: function( input, el ) {
					$('#ui-datepicker-div').removeClass( 'wc_gc_datepicker' );
					$('#ui-datepicker-div').addClass( 'wc_gc_datepicker' );
				},
				minDate: datepicker_min_date
			} );

			// Fill hidden inputs with selected date if any.
			var currentDate = $datepicker.datepicker( 'getDate' );
			if ( null !== currentDate && typeof currentDate.getTime === 'function' ) {

				// Append current time.
				currentDate.setHours( now.getHours(), now.getMinutes() );

				if ( 'store' === wc_gc_params.date_input_timezone_reference ) {
					currentDate = addMinutes( currentDate, -1 * client_offset * 60 );
					currentDate = addMinutes( currentDate, gmt_offset * 60 );
				}

				$timestamp_input.val( currentDate.getTime() / 1000 );
			}

			// On Change.
			$datepicker.on( 'change', function() {

				var selectedDate = $datepicker.datepicker( 'getDate' );
				if ( null !== selectedDate && typeof selectedDate.getTime === 'function' ) {

					// Append current time.
					var now = new Date();
					selectedDate.setHours( now.getHours(), now.getMinutes() );

					if ( 'store' === wc_gc_params.date_input_timezone_reference ) {
						selectedDate = addMinutes( selectedDate, -1 * client_offset * 60 );
						selectedDate = addMinutes( selectedDate, gmt_offset * 60 );
					}

					$timestamp_input.val( selectedDate.getTime() / 1000 );

				} else {
					$timestamp_input.val( '' );
				}

			} );

		} );
	};

	/**
	 * Function to add minutes on a given Date object.
	 *
	 * @param {Date} date The Date object to be converted.
	 * @param {integer} minutes Number of minutes.
	 */
	function addMinutes( date, minutes ) {
		return new Date( date.getTime() + minutes * 60000 );
	}

} )( jQuery, document );

