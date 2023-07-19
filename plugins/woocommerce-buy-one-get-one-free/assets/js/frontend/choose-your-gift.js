;( function( $ ) {

	/**
	 * Object to handle choose_your_gift events.
	 */
	var choose_your_gift = {

		/**
		 * Initialize event handlers.
		 */
		init: function() {

			this.car_form_url = '';

			$( document.body )
			.on( 'click', 'a.button-choose-your-gift[href="#wc-choose-your-gift"]', this.scroll_to_choose_your_gift )
			.on( 'added_to_cart', this.update_fragments )
			.on( 'removed_from_cart', this.on_remove_from_cart )
			.on( 'updated_wc_bogof_div', this.on_updated_wc_bogof_div );

			if ( this.is_cart() ) {
				this.car_form_url = $( '.woocommerce-cart-form' ).attr( 'action' );

				$( document ).ajaxSuccess(
					this.update_bogof_div
				);
			}

			this.on_updated_wc_bogof_div();
		},

		/**
		 * Check if a node is blocked for processing.
		 *
		 * @return {bool} True if the DOM Element is UI Blocked, false if not.
		 */
		is_blocked: function() {
			return $( '#wc-choose-your-gift' ).is( '.processing' ) || $( '#wc-choose-your-gift' ).parents( '.processing' ).length;
		},

		/**
		 * Block a node visually for processing.
		 *
		 */
		block: function() {
			if ( ! choose_your_gift.is_blocked() ) {
				$( '#wc-choose-your-gift' ).addClass( 'processing' ).block( {
					message: null,
					overlayCSS: {
						background: '#fff',
						opacity: 0.6
					}
				} );
			}
		},

		/**
		 * Unblock a node after processing is complete.
		 */
		unblock: function() {
			$( '#wc-choose-your-gift' ).removeClass( 'processing' ).unblock();
		},

		/**
		 * On updated bogof div.
		 */
		on_updated_wc_bogof_div: function() {
			var parameters   = $('#wc-choose-your-gift').data('parameters');

			// Choose your gift notice.
			$('.choose-your-gift-notice').remove();
			$('.button.button-choose-your-gift').closest('.woocommerce-error, .woocommerce-message, .woocommerce-info').addClass('choose-your-gift-notice');

			// Ajax add to cart parameters.
			$('#wc-choose-your-gift[data-parameters] .ajax_add_to_cart').data( 'wc_bogof_data', parameters );

		},

		/**
		 * Replace the #wc-choose-your-gift HTML content.
		 *
		 * @param {String} html_str The HTML string with which to replace the div.
		 */
		replace_content: function( html_str ) {
			try {

				choose_your_gift.block();

				var $html    = $.parseHTML( html_str );
				var $new_div = $( '#wc-choose-your-gift', $html );

				$( '#wc-choose-your-gift' ).replaceWith( $new_div );

				$( document.body ).trigger( 'updated_wc_bogof_div' );

				choose_your_gift.unblock();

			} catch ( error ) {
				window.console.log(error);
			}
		},

		/**
		 * Is cart page?
		 */
		is_cart: function() {
			var parameters   = $('#wc-choose-your-gift').data('parameters');
			return 'undefined' !== typeof parameters.is_cart && 'yes' === parameters.is_cart && $( '.woocommerce-cart-form' ).length > 0 && $( '#wc-choose-your-gift' ).length > 0;
		},

		/**
	 	 * Scroll down to the #wc-choose-your-gift.
		 */
		scroll_to_choose_your_gift: function(e) {
			if ( $( '#wc-choose-your-gift').length < 1) {
				return;
			}

			e.preventDefault();
			$( 'html, body' ).animate({
				scrollTop: $( '#wc-choose-your-gift' ).offset().top - 100
			}, 1000 );
		},

		/**
		 * Update choose your gift after remove item event.
		 */
		on_remove_from_cart: function() {
			var data = $('#wc-choose-your-gift').data('parameters');

			choose_your_gift.block();

			$.post(
				wc_add_to_cart_params.wc_ajax_url.toString().replace( '%%endpoint%%', 'bogof_update_choose_your_gift' ),
				data,
				function( response ) {
					choose_your_gift.update_fragments( null, response );
				}
			);
		},

		/**
		 * Update fragments after add to cart events.
		 */
		update_fragments: function( e, fragments ) {
			if ( fragments && 'undefined' !== typeof fragments.wc_choose_your_gift_data ) {
				var data = fragments.wc_choose_your_gift_data;

				if ( 'undefined' !== typeof data.cart_redirect && 'yes' === data.cart_redirect ) {
					window.location = wc_add_to_cart_params.cart_url;
					return;
				}

				if ( 'undefined' !== typeof data.notice ){
					choose_your_gift.show_notice( data.notice );
				}

				if ( 'undefined' !== typeof data.content ){
					choose_your_gift.replace_content( '<div>' + data.content + '</div>' );
				}
			}
		},

		/**
		 * Shows new notices on the page.
		 *
		 * @param {Object} The Notice HTML Element in string or object form.
		 */
		show_notice: function( html_element ) {
			$target = $( '.choose-your-gift-notice-wrapper:first' );
			if ( $target.length > 0 ) {
				$target.empty();
				$target.prepend( html_element );

				if ( ! this.is_cart() ) {
					$.scroll_to_notices( $target );
				}
			}
		},

		/**
	 	 * Update the #wc-choose-your-gift div with a string of html.
		 */
		update_bogof_div: function(e, jqXHR, ajaxOptions, data) {
			if ( 'undefined' === typeof ajaxOptions.url || ajaxOptions.url.indexOf( choose_your_gift.car_form_url ) < 0 ) {
				// It's not the cart update.
				return;
			}
			choose_your_gift.replace_content( data );
		}
	};

	choose_your_gift.init();

})( jQuery );
