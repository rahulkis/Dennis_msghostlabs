<?php
/*
Plugin Name: CannaPlanners Exclude Products from Coupons
Description: Adds option for excluding specific products from working with coupon codes
Version: 1.0.0
Author: CannaPlanners
Author URI: https://cannaplanners.com
*/

// Create and display the custom field in product general setting tab
add_action( 'woocommerce_product_options_general_product_data', 'add_custom_field_general_product_fields' );
function add_custom_field_general_product_fields(){
    global $post;

    echo '<div class="product_custom_field">';

    // Custom Product Checkbox Field
    woocommerce_wp_checkbox( array(
        'id'        => '_disabled_for_coupons',
        'label'     => __('Disabled for coupons', 'woocommerce'),
        'description' => __('Exclude this product from coupon discounts', 'woocommerce'),
        'desc_tip'  => 'true',
    ) );

    echo '</div>';;
}

// Save the custom field and update all excluded product Ids in option WP settings
add_action( 'woocommerce_process_product_meta', 'save_custom_field_general_product_fields', 10, 1 );
function save_custom_field_general_product_fields( $post_id ){

    $current_disabled = isset( $_POST['_disabled_for_coupons'] ) ? 'yes' : 'no';

    $disabled_products = get_option( '_products_disabled_for_coupons' );
    if( empty($disabled_products) ) {
        if( $current_disabled == 'yes' )
            $disabled_products = array( $post_id );
    } else {
        if( $current_disabled == 'yes' ) {
            $disabled_products[] = $post_id;
            $disabled_products = array_unique( $disabled_products );
        } else {
            if ( ( $key = array_search( $post_id, $disabled_products ) ) !== false )
                unset( $disabled_products[$key] );
        }
    }

    update_post_meta( $post_id, '_disabled_for_coupons', $current_disabled );
    update_option( '_products_disabled_for_coupons', $disabled_products );
}

// Make coupons invalid at product level
add_filter('woocommerce_coupon_is_valid_for_product', 'set_coupon_validity_for_excluded_products', 12, 4);
function set_coupon_validity_for_excluded_products($valid, $product, $coupon, $values ){

    if( ! count(get_option( '_products_disabled_for_coupons' )) > 0 ) return $valid;

    if(in_array('administrator',  wp_get_current_user()->roles)) {
        return $valid;
    }

    $disabled_products = get_option( '_products_disabled_for_coupons' );
    if( in_array( $product->get_id(), $disabled_products ) )
        $valid = false;

    return $valid;
}

// Set the product discount amount to zero
add_filter( 'woocommerce_coupon_get_discount_amount', 'zero_discount_for_excluded_products', 12, 5 );
function zero_discount_for_excluded_products($discount, $discounting_amount, $cart_item, $single, $coupon ){

    if( ! count(get_option( '_products_disabled_for_coupons' )) > 0 ) return $discount;

    if(in_array('administrator',  wp_get_current_user()->roles)) {
        return $discount;
    }

    $disabled_products = get_option( '_products_disabled_for_coupons' );
    if( in_array( $cart_item['product_id'], $disabled_products ) )
        $discount = 0;

    return $discount;
}