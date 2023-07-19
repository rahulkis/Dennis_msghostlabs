;( function( $ ) {

	if ( typeof wc_bogof_single_product_params === 'undefined' ) {
		return false;
	}

	if ( $( 'input[name^="wc_bogof_cart_rule"]' ).length < 1 && typeof wc_bogof_single_product_params.cart_rules !== 'undefined' && $( 'form.cart' ).length > 0 ) {
		// Append the fields when there are not in the form.
		var $form = $( 'form.cart' ).first();
		wc_bogof_single_product_params.cart_rules.forEach( function( id ){
			$('<input>').attr({
				type: 'hidden',
				name: 'wc_bogof_cart_rule[]',
				value: id
			}).appendTo($form);
		} );
	}

	if ( $( 'form.cart .ajax_add_to_cart[data-product_id]' ).length > 0 ) {
		// Add the form data to the ajax add to cart.
		$( 'form.cart .ajax_add_to_cart[data-product_id]' ).each( function( index, button ) {
			var ids = [];
			$(button).closest( 'form.cart' ).find( 'input[name^="wc_bogof_cart_rule"]' ).each( function( index, input ) {
				ids.push( $(input).val() );
			} );

			// Add the ids to the button data.
			if ( ids.length > 0 ) {
				$(button).data( 'wc_bogof_cart_rule', ids );
				$(button).data( 'wc_bogof_single_product', 1 );
			}
		} );
	} else if ( typeof wc_add_to_cart_params !== 'undefined' ) {
		var ids = [];
		$( 'form.cart' ).first().find( 'input[name^="wc_bogof_cart_rule"]' ).each( function( index, input ) {
			ids.push( 'wc_bogof_cart_rule[]=' + encodeURIComponent( $(input).val() ) );
		} );
		if ( ids.length > 0 ) {
			ids.push( 'wc_bogof_single_product=1' );
			var queryStr= ids.join( '&' );

			['ajax_url', 'wc_ajax_url'].forEach( function ( prop ){
				if ( typeof wc_add_to_cart_params[ prop ] !== 'undefined' ) {
					wc_add_to_cart_params[ prop ] += wc_add_to_cart_params[ prop ].indexOf( '?' ) < 0 ? ( '?' + queryStr ) : ( '&' + queryStr );
				}
			});
		}
	}

	// Redirect on added to cart.
	$( document.body ).on( 'added_to_cart', function( e, fragments ) {
		if ( fragments && 'undefined' !== typeof fragments.wc_choose_your_gift_data ) {
			var data = fragments.wc_choose_your_gift_data;
			if ( 'undefined' !== typeof data.cart_redirect && 'yes' === data.cart_redirect ) {
				window.location = wc_bogof_single_product_params.cart_url;
				return;
			}
		}
	} );

})( jQuery );