/**
 * Script for validating the prices before adding to cart.
 *
 * @package WooCommerce Name Your Price/Scripts
 */

/* global woocommerce_nyp_params */

/**----------------------------------------------------------------*/
/*  Global utility variables + functions.                          */
/*-----------------------------------------------------------------*/

// Format the price with accounting.js.
function woocommerce_nyp_format_price( price, currency_symbol, format ) {

	if ( typeof currency_symbol === 'undefined' ) {
		currency_symbol = '';
	}

	if ( typeof format === 'undefined' ) {
		format = false;
	}

	var currency_format = format ? woocommerce_nyp_params.currency_format : '%v';

	return accounting.formatMoney(
		price,
		{
			symbol : currency_symbol,
			decimal : woocommerce_nyp_params.currency_format_decimal_sep,
			thousand: woocommerce_nyp_params.currency_format_thousand_sep,
			precision : woocommerce_nyp_params.currency_format_num_decimals,
			format: currency_format
		}
	).trim();

}

// Get absolute value of price and turn price into float decimal.
function woocommerce_nyp_unformat_price( price ) {
	return Math.abs( parseFloat( accounting.unformat( price, woocommerce_nyp_params.currency_format_decimal_sep ) ) );
}

/**
 * Container script object getter.
 */
jQuery.fn.wc_nyp_get_script_object = function() {

	var $el = jQuery( this );

	if ( typeof( $el.data( 'wc_nyp_script_obj' ) ) !== 'undefined' ) {
		return $el.data( 'wc_nyp_script_obj' );
	}

	return false;
};

/*-----------------------------------------------------------------*/
/*  Encapsulation.                                                 */
/*-----------------------------------------------------------------*/

( function( $ ) {

	/**
	 * Main form object.
	 */
	var nypForm = function( $cart ) {

		if ( nyp_script_object = $cart.wc_nyp_get_script_object() ) {
			return nyp_script_object;
		}

		this.$el            = $cart;
		this.$add_to_cart   = $cart.find( '.single_add_to_cart_button' );

		// If the button isn't found by class, find it by type=submit.
		if ( ! this.$add_to_cart.length ) {
			this.$add_to_cart   = $cart.find( ':submit' );
		}

		this.$addons_totals = this.$el.find( '#product-addons-total' );

		this.show_addons_totals = false;
		this.nypProducts        = [];
		this.update_nyp_timer   = false;

		this.$el.trigger( 'wc-nyp-initializing', [ this ] );

		// Methods.
		this.updateForm = this.updateForm.bind( this );

		// Events.
		this.$add_to_cart.on( 'click', { nypForm: this }, this.onSubmit );
		this.$el.on( 'wc-nyp-initialized', { nypForm: this }, this.updateForm )
		this.$el.on( 'wc-nyp-updated', { nypForm: this }, this.updateForm );

		this.initIntegrations();

		this.$el.data( 'wc_nyp_script_obj', this );

		// Initialize an update immediately.
		this.$el.trigger( 'wc-nyp-initialized', [ this ] );

	}

	/**
	 * Get all child item objects.
	 */
	nypForm.prototype.getProducts = function() {

		var form = this;

		this.$el.find( '.nyp' ).each(
			function( index ) {
				var $nyp          = $( this ),
				nyp_script_object = $nyp.wc_nyp_get_script_object();

				// Initialize any objects that don't yet exist.
				if ( ! nyp_script_object ) {
					  nyp_script_object = new nypProduct( $nyp );
				}
				form.nypProducts[ index ] = nyp_script_object;
			}
		);

		return form.nypProducts;

	};

	/**
	 * Initialize integrations.
	 */
	nypForm.prototype.initIntegrations = function() {

		if ( this.$el.hasClass( 'variations_form' ) ) {
			new WC_NYP_Variations_Integration( this );
		}

		if ( this.$el.hasClass( 'grouped_form' ) ) {
			new WC_NYP_Grouped_Integration( this );
		}

		if ( $( '#woo_pp_ec_button_product' ).length ) {
			new WC_NYP_PPEC_Integration( this );
		}

	}

	/**
	 * Schedules an update of the form  when an NYP price is changed
	 */
	nypForm.prototype.updateForm = function( e, triggered_by ) {

		var form = e.data.nypForm;

		clearTimeout( form.update_nyp_timer );

		form.update_nyp_timer = setTimeout(
			function() {
				form.updateFormTask( triggered_by );
			},
			10
		);
	};

	/**
	 * Update the form.
	 */
	nypForm.prototype.updateFormTask = function( triggeredBy ) {

		var current_price = false;
		var attr_name     = false;
		var nypProducts   = this.getProducts();

		// If triggered by form update, only get a single instance. Unsure how this will work with Bundles/Grouped.
		if ( 'undefined' === typeof triggeredBy && 'undefined' !== typeof nypProducts && nypProducts.length ) {
			triggeredBy = nypProducts.shift();
		}

		if ( 'undefined' !== typeof triggeredBy && 'undefined' !== typeof triggeredBy.$price_input ) {
			attr_name     = triggeredBy.$price_input.attr( 'name' );
			current_price = triggeredBy.user_price;

			// Always add the price to the button as data for AJAX add to cart.
			this.$add_to_cart.data( attr_name, current_price );

			// Update Addons.
			this.$addons_totals.data( 'price', current_price );
			this.$el.trigger( 'woocommerce-product-addons-update' );

		}

		// Change button status.
		if ( this.isValid() ) {
			this.$add_to_cart.removeClass( 'nyp-disabled' );
			this.$el.trigger( 'wc-nyp-valid', [ this ] );
		} else {
			this.$add_to_cart.addClass( 'nyp-disabled' );
			this.$el.trigger( 'wc-nyp-invalid', [ this ] );
		}

	};

	/**
	 * Validate on submit.
	 */
	nypForm.prototype.onSubmit = function( e ) {
		var form = e.data.nypForm;

		if ( ! form.isValid( 'submit' ) ) {
			e.preventDefault();
			e.stopImmediatePropagation();
			return false;
		}

	};

	/**
	 * Are all NYP fields valid?
	 */
	nypForm.prototype.isValid = function( event_type ) {

		var valid = true;

		this.getProducts().forEach(
			function (nypProduct) {

				// Revalidate on submit.
				if ( 'submit' === event_type ) {
					nypProduct.$el.trigger( 'wc-nyp-update' );
				}

				if ( ! nypProduct.isValid() ) {
					valid = false;
					return true;
				}

			}
		);

		return valid;
	};

	/**
	 * Shuts down events, actions and filters managed by this script object.
	 */
	nypForm.prototype.shutdown = function() {
		this.$el.find( '*' ).off();
	};

	/*-----------------------------------------------------------------*/
	/*  nypProduct object                                              */
	/*-----------------------------------------------------------------*/

	var nypProduct = function( $nyp ) {

		if ( nyp_script_object = $nyp.wc_nyp_get_script_object() ) {
			return nyp_script_object;
		}

		var self = this;

		// Objects.
		self.$el                 = $nyp;
		self.$cart               = $nyp.closest( '.cart' );
		self.$form               = $nyp.closest( '.cart' ).not( '.product, [data-bundled_item_id]' );
		self.$error              = $nyp.find( '.woocommerce-nyp-message' );
		self.$error_content      = self.$error.find( 'ul.woocommerce-error' );
		self.$label              = $nyp.find( 'label' );
		self.$screen_reader      = $nyp.find( '.screen-reader-text' );
		self.$price_input        = $nyp.find( '.nyp-input' );
		self.$period_input       = $nyp.find( '.nyp-period' );
		self.$minimum 			 = $nyp.find( '.minimum-price' );
		self.$subscription_terms = $nyp.find( '.subscription-details' );

		// Variables.
		self.form           = self.$form.wc_nyp_get_script_object();
		self.min_price      = parseFloat( $nyp.data( 'min-price' ) );
		self.max_price      = parseFloat( $nyp.data( 'max-price' ) );
		self.annual_minimum = parseFloat( $nyp.data( 'annual-minimum' ) );
		self.raw_price      = self.$price_input.val();
		self.user_price     = woocommerce_nyp_unformat_price( self.raw_price );
		self.user_period    = self.$period_input.val();
		self.error_messages = [];
		self.optional       = false;
		self.initialized    = false;

		// Methods.
		self.onUpdate = self.onUpdate.bind( self );
		self.validate = self.validate.bind( self );

		// Events.
		this.$el.on( 'change', '.nyp-input, .nyp-period', { nypProduct: this }, this.onChange );
		this.$el.on( 'keypress', '.nyp-input, .nyp-period', { nypProduct: this }, this.onKeypress );
		this.$el.on( 'woocommerce-nyp-update', { nypProduct: this }, this.onUpdate ); // For backcompat only, please use wc-nyp-update instead.
		this.$el.on( 'wc-nyp-update', { nypProduct: this }, this.onUpdate );

		// Store reference in the DOM.
		self.$el.data( 'wc_nyp_script_obj', self );

		// Trigger immediately.
		self.$el.trigger( 'wc-nyp-update', [ self ] );

	};

	/**
	 * Relay change event to the custom update event.
	 */
	nypProduct.prototype.onChange = function( e ) {
		e.data.nypProduct.$el.trigger( 'wc-nyp-update', [ e.data.nypProduct ] );
	};

	/**
	 * Prevent submit on pressing Enter key.
	 */
	nypProduct.prototype.onKeypress = function( e ) {
		if ( 'Enter' === e.key ) {
			e.preventDefault();
			e.data.nypProduct.$el.trigger( 'wc-nyp-update', [ e.data.nypProduct ] );
		}
	};

	/**
	 * Handle update.
	 */
	nypProduct.prototype.onUpdate = function( e, args ) {

		var self = this;

		// Force revalidation.
		if ( 'undefined' !== typeof args && args.hasOwnProperty( 'force' ) && true === args.force ) {
			this.initialized = false;
		}

		// Current values.
		this.raw_price   = this.$price_input.val().trim() ? this.$price_input.val().trim() : '';
		this.user_price  = woocommerce_nyp_unformat_price( this.raw_price );
		this.user_period = this.$period_input.val();

		// Maybe auto-format the input.
		if ( '' !== this.raw_price ) {
			this.$price_input.val( woocommerce_nyp_format_price( this.user_price ) );
		}

		// Validate this!
		this.validate();

		// Always add price to NYP div for compatibility.
		this.$el.data( 'price', this.user_price );
		this.$el.data( 'period', this.user_period );

		if ( this.isValid() ) {

			// Remove error state class.
			this.$el.removeClass( 'nyp-error' );

			// Remove error messages.
			this.$error.slideUp();

			this.$el.trigger( 'wc-nyp-valid-item', [ this ] );

		} else {

			var $messages = $( '<ul/>' );
			var messages  = this.getErrorMessages();

			if ( messages.length > 0 ) {
				$.each(
					messages,
					function( i, message ) {
						$messages.append( $( '<li/>' ).html( message ) );
					}
				);
			}

			this.$error_content.html( $messages.html() );

			this.$el.trigger( 'wc-nyp-invalid-item', [ this ] );

		}

		if ( this.isInitialized() && ! this.isValid() ) {

			this.$el.addClass( 'nyp-error' );

			this.$error.slideDown(
				function() {
					self.$price_input.trigger( 'focus' ).trigger( 'select' );
				}
			);

		}

		// Backcompat triggers.
		this.$cart.trigger( 'woocommerce-nyp-updated-item' ); // Used by Product Bundles.
		$( 'body' ).trigger( 'woocommerce-nyp-updated' );

		// New trigger.
		this.$el.trigger( 'wc-nyp-updated', [ this ] );

		// Mark the product as initialized.
		this.initialized = true;

	};

	/**
	 * Validate all the prices.
	 */
	nypProduct.prototype.validate = function() {

		// Skip validate if the price has not changed.
		if ( ! this.priceChanged() ) {
			return true;
		}

		// Reset validation messages.
		this.resetMessages();
		this.$el.data( 'nyp-valid', true );

		// Skip validation for optional products, ex: grouped/bundled.
		if ( this.isOptional() ) {
			return true;
		}

		// Not optional, so let's check the prices.

		// Begin building the error message.
		var error_message = this.$el.data( 'hide-minimum' ) ? this.$el.data( 'hide-minimum-error' ) : this.$el.data( 'minimum-error' );
		var error_tag     = "%%MINIMUM%%";
		var error_price   = ''; // This will hold the formatted price for the error message.

		// If has variable billing period AND a minimum then we need to annulalize min price for comparison.
		if ( this.annual_minimum > 0 ) {

			// Calculate the price over the course of a year for comparison.
			form_annulualized_price = this.user_price * woocommerce_nyp_params.annual_price_factors[this.user_period];

			// If the calculated annual price is less than the annual minimum.
			if ( form_annulualized_price < this.annual_minimum ) {

				var min_price     = this.annual_minimum / woocommerce_nyp_params.annual_price_factors[this.user_period];
				var period_string = this.$period_input.find( 'option[value="' + this.user_period + '"]' ).text();

				error_price = woocommerce_nyp_params.i18n_subscription_string.replace( '%price', woocommerce_nyp_format_price( min_price, woocommerce_nyp_params.currency_format_symbol, true ) ).replace( '%period', period_string );
				this.addErrorMessage( error_message.replace( error_tag, error_price ) );

			}

			// Otherwise a regular product or subscription with non-variable periods, compare price directly.
		} else if ( this.min_price && this.user_price < this.min_price ) {

			error_price = woocommerce_nyp_format_price( this.min_price, woocommerce_nyp_params.currency_format_symbol, true );
			this.addErrorMessage( error_message.replace( error_tag, error_price ) );

			// Check maximum price.
		} else if ( this.max_price && this.user_price > this.max_price ) {

			error_message = this.$el.data( 'maximum-error' );
			error_tag     = "%%MAXIMUM%%";
			error_price   = woocommerce_nyp_format_price( this.max_price, woocommerce_nyp_params.currency_format_symbol, true );
			this.addErrorMessage( error_message.replace( error_tag, error_price ) );

			// Check empty input.
		} else if ( '' === this.raw_price ) {

			error_message = this.$el.data( 'empty-error' );
			this.addErrorMessage( error_message.replace( error_tag, error_price ) );

		}

		if ( ! this.isValid() ) {
			this.$el.data( 'nyp-valid', false );
		}

	};

	/**
	 * Has this price changed?
	 */
	nypProduct.prototype.priceChanged = function() {
		$changed = true;

		if ( ! this.$el.is( ':visible' ) ) {
			$changed = false;
		} else if ( this.isInitialized() && this.raw_price === this.user_price && this.user_price === this.$el.data( 'price' ) && this.user_period === this.$el.data( 'period' ) ) {
			$changed = false;
		}

		return $changed;
	};

	/**
	 * Is this price valid?
	 */
	nypProduct.prototype.isValid = function() {
		return ! this.$el.is( ':visible' ) || this.isOptional() || ! this.error_messages.length;
	};

	/**
	 * Is this product optional?
	 */
	nypProduct.prototype.isOptional = function() {
		return this.$el.data( 'optional' ) === 'yes' && this.$el.data( 'optional_status' ) === false;
	}

	/**
	 * Is this product initialized?
	 */
	nypProduct.prototype.isInitialized = function() {
		return this.initialized;
	}

	/**
	 * Add validation message.
	 */
	nypProduct.prototype.addErrorMessage = function( message ) {
		this.error_messages.push( message.toString() );
	};

	/**
	 * Get validation messages.
	 */
	nypProduct.prototype.getErrorMessages = function( type ) {
		return this.error_messages;

	};

	/**
	 * Reset messages on update start.
	 */
	nypProduct.prototype.resetMessages = function() {
		this.error_messages = [];
	};

	/**
	 * Reset messages on update start.
	 */
	nypProduct.prototype.resetMessages = function() {
		this.error_messages = [];
	};

	/**
	 * Get the user price.
	 */
	nypProduct.prototype.getPrice = function() {
		return this.user_price;
	};

	/**
	 * Get the user period.
	 */
	nypProduct.prototype.getPeriod = function() {
		return this.user_period;
	};

	/*-----------------------------------------------------------------*/
	/*  Integrations .                                                 */
	/*-----------------------------------------------------------------*/

	/**
	 * Variable Product Integration.
	 */
	function WC_NYP_Variations_Integration( form ) {

		var self = this;

		// Assume in a variable product there's only 1 NYP field.
		var nyp = form.getProducts().shift();

		// The add to cart text.
		var default_add_to_cart_text = form.$add_to_cart.html();

		// Init.
		this.integrate = function() {
			form.$el.on( 'found_variation', self.onFoundVariation );
			form.$el.on( 'reset_image', self.resetVariations );
			form.$el.on( 'click', '.reset_variations', self.resetVariations );
		}

		// When variation is found, decide if it is NYP or not.
		this.onFoundVariation = function( event, variation ) {

			// Hide any existing error message.
			nyp.$error.slideUp();

			// If NYP show the price input and tweak the data attributes.
			if ( typeof variation.is_nyp != undefined && variation.is_nyp == true ) {

				// Switch add to cart button text if variation is NYP.
				form.$add_to_cart.html( variation.add_to_cart_text );

				// Get the prices out of data attributes.
				var display_price = typeof variation.display_price !== 'undefined' && variation.display_price ? variation.display_price : '';

				// Set the NYP attributes for JS validation.
				nyp.min_price = typeof variation.minimum_price !== 'undefined' && variation.minimum_price ? parseFloat( variation.minimum_price ) : '';
				nyp.max_price = typeof variation.maximum_price !== 'undefined' && variation.maximum_price ? parseFloat( variation.maximum_price ) : '';

				// Maybe auto-format the input.
				if ( '' !== display_price.trim() ) {
					nyp.$price_input.val( woocommerce_nyp_format_price( display_price ) );
				} else {
					nyp.$price_input.val( '' );
				}

				// Maybe switch the label.
				if ( nyp.$label.length ) {

					var label = 'undefined' !== variation.price_label ? variation.price_label : '';

					if ( label ) {
						nyp.$label.html( label ).show();
					} else {
						nyp.$label.empty().hide();
					}
				}

				// Maybe show minimum price html.
				if ( nyp.$minimum.length ) {

					var minimum_price_html = 'undefined' !== variation.minimum_price_html ? variation.minimum_price_html : '';

					if ( minimum_price_html ) {
						nyp.$minimum.html( minimum_price_html ).show();
					} else {
						nyp.$minimum.empty().hide();
					}
				}

				// Show the input.
				nyp.$el.slideDown();

				// Toggle minimum error message between explicit and obscure.
				nyp.$el.data( 'hide-minimum', variation.hide_minimum );

				// Trigger update.
				nyp.initialized = false;
				nyp.$el.trigger( 'wc-nyp-update' );

				// If not NYP, hide the price input.
			} else {

				self.resetVariations();

			}

		}

		// Hide NYP errors when attributes are reset.
		this.resetVariations = function() {
			form.$add_to_cart.html( default_add_to_cart_text ).removeClass( 'nyp-disabled' );
			nyp.$el.slideUp().removeClass( 'nyp-error' );
			nyp.initialized = false;
			nyp.$error_content.empty();
			nyp.$price_input.val( '' );
		}

		// Lights on.
		this.integrate();

	}

	/**
	 * Grouped Product Integration.
	 */
	function WC_NYP_Grouped_Integration( form ) {

		var self = this;

		// Init.
		this.integrate = function() {

			// Handle status of optional grouped products.
			form.$el.on( 'change', '.qty, .wc-grouped-product-add-to-cart-checkbox', self.onStatusChange );
			form.$el.find( '.qty, .wc-grouped-product-add-to-cart-checkbox' ).trigger( 'change' );

		}

		// Handle optional status changes.
		this.onStatusChange = function() {

			var $nyp = $( this ).closest( 'tr' ).find( '.nyp' );

			if ( $nyp.length ) {

				var selected = $( this ).is( ':checkbox' ) ? $( this ).is( ':checked' ) : $( this ).val() > 0;

				if ( selected ) {
					$nyp.data( 'optional_status', true );
				} else {
					$nyp.data( 'optional_status', false );
				}
				$nyp.trigger( 'wc-nyp-update', [ { 'force': true } ] );
			}

		}

		// Lights on.
		this.integrate();

	}

	/**
	 * PayPal Express Checkout Integration.
	 */
	function WC_NYP_PPEC_Integration( form ) {

		var self = this;

		// Init.
		this.integrate = function() {
			form.$el.on( 'wc-nyp-valid', self.enable );
			form.$el.on( 'wc-nyp-invalid', self.disable );
			$( document ).on( 'wc_ppec_validate_product_form', self.validate );
		}

		// Enable PayPal buttons.
		this.enable = function( e ) {
			$( '#woo_pp_ec_button_product' ).trigger( 'enable' );
		}

		// Disable PayPal buttons.
		this.disable = function( e ) {
			$( '#woo_pp_ec_button_product' ).trigger( 'disable' );
		}

		// Extra validation for NYP items.
		this.validate = function( e, is_valid, $form ) {

			var nyp_script_object = $form.wc_nyp_get_script_object();

			if ( 'object' === typeof nyp_script_object ) {
				is_valid = nyp_script_object.isValid();
			}

			return is_valid;

		}

		// Lights on.
		this.integrate();

	}

	/*-----------------------------------------------------------------*/
	/*  Initialization.                                                */
	/*-----------------------------------------------------------------*/

	jQuery( function( $ ) {

		/**
		 * Script initialization on '.cart' elements.
		 */
		$.fn.wc_nyp_form = function() {

			var $cart        = $( this ),
			nyp_script_object = $cart.wc_nyp_get_script_object();

			if ( ! $cart.hasClass( 'cart' ) ) {
				return false;
			}

			// If the script object already exists, then we need to shut it down first before re-initializing.
			if ( nyp_script_object) {
				$cart.data( 'wc_nyp_script_obj' ).shutdown();
			}

			// Launch the form object.
			new nypForm( $cart );

			return this;

		};

		/**
		* Initialize NYP scripts.
		*/
		$( 'form.cart' ).each(
			function() {
				$( this ).wc_nyp_form();
			}
		);

		new nypForm( $( 'form.cart' ) );

		/*-----------------------------------------------------------------*/
		/*  Compatibility .                                                */
		/*-----------------------------------------------------------------*/

		/**
		 * QuickView compatibility.
		 */
		$( 'body' ).on(
			'quick-view-displayed',
			function() {

				$( 'form.cart' ).each(
					function() {
						$( this ).wc_nyp_form();
					}
				);

			}
		);

		/*
			* One Page Checkout compatibility.
			*/
		$( '.wcopc .cart' ).each(
			function() {
				$( this ).wc_nyp_form();
			}
		);

		$( 'body' ).on(
			'opc_add_remove_product',
			function ( event, data, e, selectors ) {

				if ( 'undefined' !== typeof e ) {

					  var $triggeredBy = $( e.currentTarget );

					  var nyp_script_object = $triggeredBy.closest( '.cart' ).find( '.nyp' ).wc_nyp_get_script_object();

					if ( nyp_script_object ) {

						nyp_script_object.$el.trigger( 'wc-nyp-update' );

						var qty = parseFloat( data.quantity );

						if ( qty > 0 && ! nyp_script_object.isValid() ) {

							  // Reset input quantity to quantity in cart.
							if ( $triggeredBy.prop( 'type' ) === 'number' ) {
								$triggeredBy.val( $triggeredBy.data( 'cart_quantity' ) );
							}
							// Prevent OPC from firing AJAX.
							data.invalid = true;

						} else if ( qty === 0 ) {

							 // Remove error state class.
							 nyp_script_object.$el.removeClass( 'nyp-error' );

							 // Remove error messages.
							 nyp_script_object.$error.slideUp();

							 // Reset input to original value.
							 var original_price = nyp_script_object.$el.data( 'initial-price' );
							if ( $.trim( original_price ) !== '' ) {
								nyp_script_object.$price_input.val( woocommerce_nyp_format_price( original_price ) );
							} else {
								nyp_script_object.$price_input.val( '' );
							}

						}

					}
				}

				return data;

			}
		);

		/**
		 * Run when a Composite component is re-loaded.
		 */
		$( 'body .component' ).on(
			'wc-composite-component-loaded',
			function() {

				var $nyp = $( this ).find( '.nyp' );

				if ( $nyp.length ) {
					  var nyp_script_object = new nypProduct( $nyp );
				} else {
					 // Update the form.
					 $( this ).trigger( 'wc-nyp-updated' );
				}

			}
		);

	} );

} )( jQuery );
