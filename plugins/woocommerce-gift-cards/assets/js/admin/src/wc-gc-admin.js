/* global wc_gc_admin_params, woocommerce_admin_meta_boxes */
;( function( $, window ) {

	// Main.
	$( function() {

		// Date Picker
		$( document.body ).on( 'wc-init-datepickers', function() {
			$( '.date-picker-field, .date-picker' ).datepicker( {
				dateFormat: 'yy-mm-dd',
				numberOfMonths: 1,
				showButtonPanel: true
			} );
		} ).trigger( 'wc-init-datepickers' );

		// Warning.
		$( '.woocommerce-gc-giftcards #delete-action' ).on( 'click', function( e ) {
			if ( ! window.confirm( wc_gc_admin_params.i18n_wc_delete_card_warning ) ) {
				e.preventDefault();
				return false;
			}
		} );

		$( '#giftcards-table #doaction' ).on( 'click', function( e ) {

			var value = $( '#bulk-action-selector-top' ).val();

			if ( value === 'delete' && ! window.confirm( wc_gc_admin_params.i18n_wc_bulk_delete_card_warning ) ) {
				e.preventDefault();
				return false;
			}
		} );

		$( '#giftcards-table #doaction2' ).on( 'click', function( e ) {

			var value = $( '#bulk-action-selector-bottom' ).val();

			if ( value === 'delete' && ! window.confirm( wc_gc_admin_params.i18n_wc_bulk_delete_card_warning ) ) {
				e.preventDefault();
				return false;
			}
		} );

		// Enclosed message controller.
		var handle_message_replacement = ( function() {

			var $parent = $( '.wc-gc-message-mask' );
			if ( ! $parent.length ) {
				return false;
			}

			var editing      = false,
				$placeholder = $parent.find( '.wc-gc-message-mask_placeholder' ),
				$cancel      = $parent.find( '#wc_gc_replace_message_cancel' ).hide(),
				$textarea    = $parent.find( 'textarea' ),
				$form        = $( '#edit-gift-card-form' );

			var toggle_placeholder = function() {
				if ( editing ) {
					$placeholder.hide();
				} else {
					$placeholder.fadeIn( 'fast' );
				}
			};

			var toggle_textarea = function() {
				if ( ! editing ) {
					$textarea.fadeIn( 'fast' );
					$cancel.fadeIn( 'fast' );
					editing = true;
				} else {
					editing = false;
					$textarea.hide();
					$cancel.hide();
				}
			};

			var hook = function() {

				$parent.on( 'click', '#wc_gc_replace_message_action, #wc_gc_replace_message_cancel', function( e ) {
					e.preventDefault();

					toggle_textarea();
					toggle_placeholder();

					return false;
				} );

				$form.on( 'submit', function( e ) {

					if ( editing ) {
						if ( ! window.confirm( wc_gc_admin_params.i18n_wc_edit_message_warning ) ) {
							e.preventDefault();
							return false;
						}
					}

					return true;
				} );
			};

			return {
				hook: hook
			};

		} )();

		// Hook in.
		if ( handle_message_replacement ) {
			handle_message_replacement.hook();
		}

		/**
		 * Order Items Panel
		 */
		 var wc_gc_meta_boxes_order_items = {

			$order_items_wrapper: $( '#woocommerce-order-items' ),

			view: false,

			init: function() {

				if ( ! this.$order_items_wrapper.length ) {
					return;
				}

				// Hook events.
				this.$order_items_wrapper

					// Manual redeem.
					.on( 'click', 'button.add-gift-card', this.add_gift_card.bind( this ) )
					.on( 'click', 'a.delete-gift-card-item', this.delete_gift_card.bind( this ) )

					// Manual configure.
					.on( 'click', 'button.configure_gift_card', { action: 'configure' }, this.clicked_edit_button.bind( this ) )
					.on( 'click', 'button.edit_gift_card', { action: 'edit' }, this.clicked_edit_button.bind( this ) );
			},

			reloaded_items: function() {

				if ( 'yes' === wc_gc_admin_params.is_wc_version_gte_3_4 ) {
					this.$order_items_wrapper.trigger( 'wc_order_items_reloaded' );
				} else {
					this.core.init_tiptip();
					this.core.stupidtable.init();
				}
			},

			block: function( $target, params ) {
				if ( ! $target || $target === 'undefined' ) {
					$target = this.$order_items_wrapper;
				}

				var defaults = {
						message: null,
						overlayCSS: {
							background: '#fff',
							opacity:    0.6
						}
					};

				var opts = $.extend( {}, defaults, params || {} );

				$target.block( opts );

			},

			unblock: function( $target ) {
				if ( ! $target || $target === 'undefined' ) {
					$target = this.$order_items_wrapper;
				}

				$target.unblock();
			},

			clicked_edit_button: function( event ) {

				var WCGCBackboneModal = $.WCBackboneModal.View.extend( {
					addButton: this.clicked_done_button.bind( this )
				} );

				var $item   = $( event.target ).closest( 'tr.item' ),
					item_id = $item.attr( 'data-order_item_id' );

				this.view = new WCGCBackboneModal( {
					target: 'wc-modal-edit-gift-card',
					string: {
						action: 'configure' === event.data.action ? wc_gc_admin_params.i18n_configure_order_item : wc_gc_admin_params.i18n_edit_order_item,
						item_id: item_id
					}
				} );

				this.populate_form.call( this );

				return false;
			},

			clicked_done_button: function( event ) {

				this.block( this.view.$el.find( '.wc-backbone-modal-content' ) );

				var data = $.extend( {}, this.get_taxable_address(), {
					action:    'wc_gc_edit_order_item_gift_card_in_order',
					item_id:   this.view._string.item_id,
					fields:    this.view.$el.find( 'input, select, textarea' ).serialize(),
					dataType:  'json',
					order_id:  woocommerce_admin_meta_boxes.post_id,
					security:  wc_gc_admin_params.edit_gift_card_order_item_nonce
				} );

				$.post( woocommerce_admin_meta_boxes.ajax_url, data, function( response ) {

					if ( response.result && 'success' === response.result ) {

						this.$order_items_wrapper.find( '.inside' ).empty();
						this.$order_items_wrapper.find( '.inside' ).append( response.html );

						this.reloaded_items();

						if ( 'yes' === wc_gc_admin_params.is_wc_version_gte_3_6 ) {

							// Update notes.
							if ( response.notes_html ) {
								$( 'ul.order_notes' ).empty();
								$( 'ul.order_notes' ).append( $( response.notes_html ).find( 'li' ) );
							}
						}

						this.unblock( this.view.$el.find( '.wc-backbone-modal-content' ) );

						// Make it look like something changed.
						this.block();
						setTimeout( function() {
							this.unblock();
						}.bind( this ), 250 );

						this.view.closeButton( event );

					} else {
						window.alert( response.error ? response.error.replace( /&quot;/g, '\"' ) : wc_gc_admin_params.i18n_validation_error );
						this.unblock( this.view.$el.find( '.wc-backbone-modal-content' ) );
					}

				}.bind( this ) );
			},

			populate_form: function() {

				this.block( this.view.$el.find( '.wc-backbone-modal-content' ) );

				var data = {
					action:    'wc_gc_configure_order_item_gift_card',
					item_id:   this.view._string.item_id,
					dataType:  'json',
					order_id:  woocommerce_admin_meta_boxes.post_id,
					security:  wc_gc_admin_params.edit_gift_card_order_item_nonce
				};

				$.post( woocommerce_admin_meta_boxes.ajax_url, data, function( response ) {

					if ( response.result && 'success' === response.result ) {

						var $form = this.view.$el.find( 'form' );

						$form.html( response.html );
						$form.wc_gc_datepickers();

						var $code_container = $form.find( '.wc-gc-edit-code' ),
							$code_checkbox  = $code_container.find( 'input[type="checkbox"]' ),
							$code_field     = $code_container.find( '.wc-gc-field' );

						if ( ! $code_checkbox.is( ':checked' ) ) {
							$code_field.show();
						}

						$code_checkbox.on( 'change', function( e ) {

							var $checkbox = $( this ),
								is_checked = $checkbox.is( ':checked' );

							if ( is_checked ) {
								$code_field.hide();
							} else {
								$code_field.show();
							}

						} );

						this.unblock( this.view.$el.find( '.wc-backbone-modal-content' ) );

					} else {
						window.alert( wc_gc_admin_params.i18n_form_error );
						this.unblock( this.view.$el.find( '.wc-backbone-modal-content' ) );
						this.view.$el.find( '.modal-close' ).trigger( 'click' );
					}

				}.bind( this ) );
			},

			add_gift_card: function( e ) {
				var value = window.prompt( wc_gc_admin_params.i18n_wc_add_order_item_prompt );

				if ( null !== value || '' !== value ) {
					this.block();

					var user_id    = $( '#customer_user' ).val();
					var user_email = $( '#_billing_email' ).val();

					var data = $.extend( {}, this.get_taxable_address(), {
						action     : 'wc_gc_add_order_item_gift_card',
						dataType   : 'json',
						order_id   : woocommerce_admin_meta_boxes.post_id,
						security   : woocommerce_admin_meta_boxes.order_item_nonce,
						giftcard   : value,
						user_id    : user_id,
						user_email : user_email
					} );

					$.ajax( {
						url:     woocommerce_admin_meta_boxes.ajax_url,
						data:    data,
						type:    'POST',
						success: function( response ) {

							if ( response.result && 'success' === response.result ) {
								this.$order_items_wrapper.find( '.inside' ).empty();
								this.$order_items_wrapper.find( '.inside' ).append( response.html );

								if ( 'yes' === wc_gc_admin_params.is_wc_version_gte_3_6 ) {

									// Update notes.
									if ( response.notes_html ) {
										$( 'ul.order_notes' ).empty();
										$( 'ul.order_notes' ).append( $( response.notes_html ).find( 'li' ) );
									}
								}

								this.reloaded_items();
								this.unblock();

							} else {
								window.alert( response.data.error );
							}

							this.unblock();

						}.bind( this )
					} );
				}

				e.preventDefault();
				return false;
			},

			delete_gift_card: function( e ) {

				// Get the container.
				var $item         = $( e.target ).closest( 'tr.item, tr.fee, tr.shipping' ),
					gc_code       = $item.attr( 'data-gc_code' ),
					order_item_id = $item.attr( 'data-order_item_id' );

				var answer = window.confirm( wc_gc_admin_params.i18n_wc_delete_order_item_warning.replace( '%%code%%', gc_code ) );

				if ( answer ) {

					this.block();

					var data = $.extend( {}, this.get_taxable_address(), {
						order_id      : woocommerce_admin_meta_boxes.post_id,
						order_item_ids: order_item_id,
						action        : 'wc_gc_remove_order_item_gift_card',
						security      : woocommerce_admin_meta_boxes.order_item_nonce
					} );

					// Check if items have changed, if so pass them through so we can save them before deleting.
					if ( 'true' === $( 'button.cancel-action' ).attr( 'data-reload' ) ) {
						data.items = $( 'table.woocommerce_order_items :input[name], .wc-order-totals-items :input[name]' ).serialize();
					}

					$.ajax( {
						url:     woocommerce_admin_meta_boxes.ajax_url,
						data:    data,
						type:    'POST',
						success: function( response ) {

							if ( response.result && 'success' === response.result ) {
								this.$order_items_wrapper.find( '.inside' ).empty();
								this.$order_items_wrapper.find( '.inside' ).append( response.html );

								if ( 'yes' === wc_gc_admin_params.is_wc_version_gte_3_6 ) {

									// Update notes.
									if ( response.notes_html ) {
										$( 'ul.order_notes' ).empty();
										$( 'ul.order_notes' ).append( $( response.notes_html ).find( 'li' ) );
									}
								}

								this.reloaded_items();
								this.unblock();
							} else {
								window.alert( response.data.error );
							}

							this.unblock();

						}.bind( this )
					});
				}

				e.preventDefault();
				return false;
			},

			get_taxable_address: function() {
				var country          = '';
				var state            = '';
				var postcode         = '';
				var city             = '';

				if ( 'shipping' === woocommerce_admin_meta_boxes.tax_based_on ) {
					country  = $( '#_shipping_country' ).val();
					state    = $( '#_shipping_state' ).val();
					postcode = $( '#_shipping_postcode' ).val();
					city     = $( '#_shipping_city' ).val();
				}

				if ( 'billing' === woocommerce_admin_meta_boxes.tax_based_on || ! country ) {
					country  = $( '#_billing_country' ).val();
					state    = $( '#_billing_state' ).val();
					postcode = $( '#_billing_postcode' ).val();
					city     = $( '#_billing_city' ).val();
				}

				return {
					country:  country,
					state:    state,
					postcode: postcode,
					city:     city
				};
			},

			core: {

				init_tiptip: function() {
					$( '#tiptip_holder' ).removeAttr( 'style' );
					$( '#tiptip_arrow' ).removeAttr( 'style' );
					$( '.tips' ).tipTip({
						'attribute': 'data-tip',
						'fadeIn': 50,
						'fadeOut': 50,
						'delay': 200
					});
				},

				stupidtable: {

					init: function() {
						$( '.woocommerce_order_items' ).stupidtable();
						$( '.woocommerce_order_items' ).on( 'aftertablesort', this.add_arrows );
					},

					add_arrows: function( event, data ) {
						var th    = $( this ).find( 'th' );
						var arrow = data.direction === 'asc' ? '&uarr;' : '&darr;';
						var index = data.column;
						th.find( '.wc-arrow' ).remove();
						th.eq( index ).append( '<span class="wc-arrow">' + arrow + '</span>' );
					}
				},
			}
		};

		wc_gc_meta_boxes_order_items.init();

		function handle_show_if_giftcard_containers( set, $elements ) {

			if ( false !== set ) {
				set = true;
			}

			if ( ! $elements ) {
				$elements = $product_data.find( '.show_if_giftcard' );
			}

			$elements.each( function( index ) {

				var $el                 = $( this ),
					$simple_container   = $el.find( '.show_if_giftcard_simple' ),
					$variable_container = $el.find( '.show_if_giftcard_variable' );



				set ? $el.show() : $el.hide();

				if ( 'simple' === product_type ) {

					set ? $variable_container.hide() : $variable_container.show();

					if ( $simple_container.length ) {
						set ? $simple_container.show() : $simple_container.hide();
					}
				}


				if ( 'variable' === product_type ) {

					set ? $simple_container.hide() : $simple_container.show();

					if ( $variable_container.length ) {
						set ? $variable_container.show() : $variable_container.hide();
					}
				}

			} );
		}

		// Gift Card checkbox callbacks.
		function giftcard_checkbox_on_cb( on_load ) {

			handle_show_if_giftcard_containers();

			// Global Virtual.
			$virtual_checkbox.prop( 'checked', 'checked' ).change();
			$virtual_container.attr( 'class', 'tips' ); // Remove every show_if & hide_if classes.
			$virtual_container.hide();

			if ( ! on_load || 'undefined' == on_load ) {
				// Taxes.
				$tax_status_select.val( 'none' );
			}

			// Variation Virtual.
			if ( $variable_product_options.length ) {
				$variable_product_options.find( '.variable_is_virtual' ).prop( 'checked', 'checked' ).each( function() {
					var $this = $( this );
					$this.change();
					$this.parent().hide();
				} );
			}
		}

		function giftcard_checkbox_off_cb( on_load ) {

			handle_show_if_giftcard_containers( false );

			// Global Virtual.
			$virtual_container.attr( 'class', virtual_classes.join( ' ' ) );
			$virtual_checkbox.prop( 'checked', virtual_value_cache ? 'checked' : null ).change();

			if ( -1 !== $.inArray( 'show_if_' + product_type, virtual_classes ) ) {
				$virtual_container.show();
			}

			if ( ! on_load || 'undefined' == on_load ) {
				// Restore Taxes.
				$tax_status_select.val( tax_status );
			}

			// Variation Virtual.
			if ( $variable_product_options.length ) {
				$variable_product_options.find( '.variable_is_virtual' ).each( function() {
					$( this ).parent().show();
				} );
			}
		}

		var $product_data = $( 'div#woocommerce-product-data' );
		if ( $product_data.length ) {

			var $virtual_checkbox         = $product_data.find( '#_virtual' ),
				$virtual_container        = $virtual_checkbox.parent(),
				virtual_value_cache       = $virtual_checkbox.is( ':checked' ),
				virtual_classes           = $virtual_container.attr( 'class' ).split( /\s+/ ),

				// Product Data type and taxes.
				$product_type_select      = $product_data.find( '#product-type' ),
				product_type              = $product_type_select.val(),
				$tax_status_select        = $product_data.find( '#_tax_status' ),
				tax_status                = $tax_status_select.val(),

				// Variable virtual checkboxes.
				$variable_product_options = $product_data.find( '#variable_product_options' ),
				// Giftcard Checkbox.
				$giftcard_checkbox        = $product_data.find( '#_gift_card' ),
				$giftcard_container       = $giftcard_checkbox.parent(),
				giftcard_classes          = $giftcard_container.attr( 'class' ).split( /\s+/ );

			// Initial load.
			if ( $giftcard_checkbox.is( ':checked' ) ) {
				giftcard_checkbox_on_cb( true );
			} else {
				giftcard_checkbox_off_cb( true );
			}

			// Variations loaded callback.
			$product_data.on( 'woocommerce_variations_loaded woocommerce_variations_added', function() {

				if ( $giftcard_checkbox.is( ':checked' ) ) {
					$variable_product_options.find( '.variable_is_virtual' ).prop( 'checked', 'checked' ).each( function() {
						var $this = $( this );
						$this.change();
						$this.parent().hide();
					} );

				} else {
					handle_show_if_giftcard_containers( false );
				}

				init_image_select();
			} );

			// Gift Card checkbox changed callback.
			$giftcard_checkbox.on( 'change', function() {
				if ( $( this ).is( ':checked' ) ) {
					giftcard_checkbox_on_cb();
				} else {
					giftcard_checkbox_off_cb();
				}
			} );

			// Product type select changed callback.
			$product_type_select.on( 'change', function() {

				product_type = $( this ).val();

				if ( -1 === $.inArray( 'show_if_' + product_type, giftcard_classes ) ) {

					$giftcard_container.hide();
					handle_show_if_giftcard_containers( false );

				} else {

					if ( $giftcard_checkbox.is( ':checked' ) ) {
						handle_show_if_giftcard_containers();
					} else {
						handle_show_if_giftcard_containers( false );
					}
				}

			} );

			// Gift card template Default.
			function init_image_select() {

				var $image_select_container = $product_data.find( '.template_default_image_container' );

				$image_select_container.each( function( index ) {

					var $container               = $( this ),
						$default_use_image_field = $container.find( '#_gift_card_template_default_use_image' ),
						$custom_field            = $container.find( '.gift_card_template_default_custom_image' ),
						$select_image            = $container.find( '.wc_gc_field_select_image' ),
						$remove_image            = $container.find( '.wc_gc_field_remove_image' );

					// Image type setting.
					if ( 'custom' === $default_use_image_field.val() ) {
						$custom_field.show();
					}

					$default_use_image_field.on( 'change', function() {
						if ( 'custom' === $default_use_image_field.val() ) {
							$custom_field.show();
						} else {
							$custom_field.hide();
						}
					} );

					// Remove Image.
					$remove_image.on( 'click', function( e ) {

						var $button         = $( this ),
							$option_wrapper = $button.closest( '.wc_gc_select_image' ),
							$upload_button  = $option_wrapper.find( '.wc_gc_field_select_image' );

						e.preventDefault();

						$upload_button.removeClass( 'has_image' );
						$button.removeClass( 'has_image' );
						$option_wrapper.find( 'input' ).val( '' ).change();
						$upload_button.find( 'img' ).eq( 0 ).attr( 'src', wc_gc_admin_params.wc_placeholder_img_src );
					} );

					var image_frame = null;

					$select_image.on( 'click', function( e ) {

						e.preventDefault();

						var $button = $( this );

						// If the media frame already exists, reopen it.
						if ( image_frame ) {

							image_frame.open();

						} else {

							// Create the media frame.
							image_frame = wp.media( {

								// Set the title of the modal.
								title: wc_gc_admin_params.i18n_select_image_frame_title,
								button: {
									text: wc_gc_admin_params.i18n_select_image_frame_button
								},
								states: [
									new wp.media.controller.Library( {
										title: wc_gc_admin_params.i18n_select_image_frame_title,
										filterable: 'all'
									} )
								]
							} );

							// When an image is selected, run a callback.
							image_frame.on( 'select', function () {

								var attachment = image_frame.state().get( 'selection' ).first().toJSON(),
									url        = attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;

								$button.addClass( 'has_image' );
								$button.closest( '.wc_gc_select_image' ).find( '.wc_gc_field_remove_image' ).addClass( 'has_image' );
								$button.find( 'input' ).val( attachment.id ).change();
								$button.find( 'img' ).eq( 0 ).attr( 'src', url );
							} );

							// Finally, open the modal.
							image_frame.open();
						}

					} );

				} );

			}

			// First load init.
			init_image_select();

		} // If $product_data

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
				$timestamp_input  = $container.find( 'input[name="wc_gc_giftcard_delivery"]' );

			// Init datepicker.
			$datepicker.datepicker( {
				beforeShow: function( input, el ) {
					$('#ui-datepicker-div').removeClass( 'wc_gc_datepicker' );
					$('#ui-datepicker-div').addClass( 'wc_gc_datepicker' );
				},
				minDate: '+1D'
			} );

			// Fill hidden inputs with selected date if any.
			var currentDate = $datepicker.datepicker( 'getDate' );
			if ( null !== currentDate && typeof currentDate.getTime === 'function' ) {

				// Append current time.
				var now = new Date();
				currentDate.setHours( now.getHours(), now.getMinutes() );
				$timestamp_input.val( currentDate.getTime() / 1000 );
			}

			// On Change.
			$datepicker.on( 'change', function() {

				var selectedDate = $datepicker.datepicker( 'getDate' );
				if ( null !== selectedDate && typeof selectedDate.getTime === 'function' ) {

					// Append current time.
					var now = new Date();
					selectedDate.setHours( now.getHours(), now.getMinutes() );
					$timestamp_input.val( selectedDate.getTime() / 1000 );

				} else {
					$timestamp_input.val( '' );
				}

			} );

		} );
	};

} )( jQuery, window );
