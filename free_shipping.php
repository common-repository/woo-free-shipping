<?php
/*
Plugin Name: Woocommerce Free Shipping
Description: This woocommerce based plugin helps you make shipping free for users having specific quantity of orders in their cart.
Author: Bilal Iqbal
Author URI: https://phalcosoft.com
Version: 1.0
*/

// Required plugin notice
add_action( 'admin_notices', 'irb_woocommerce_dependency' );
function irb_woocommerce_dependency() {
  if( ! is_plugin_active( 'woocommerce' ) ){
      echo '<div class="error"><p>' . __( 'Warning: This plugin requires woocommerce.', 'wc_free_shipping' ) . '</p></div>';
  }
}

// Add field to shipping settings page
add_filter( 'woocommerce_shipping_settings', 'irb_add_free_shipping_on_orders_setting' );
function irb_add_free_shipping_on_orders_setting( $settings ) {
  $updated_settings = array();
  foreach ( $settings as $section ) {
    if ( isset( $section['id'] ) && 'shipping_options' == $section['id'] && isset( $section['type'] ) && 'sectionend' == $section['type'] ) {
      $updated_settings[] = array(
        'name'     => __( 'Offer Free Shipping', 'wc_free_shipping' ),
        'desc_tip' => __( 'Make shipping free on orders greater than given quantity.', 'wc_free_shipping' ),
        'id'       => 'woocommerce_free_shipping_on_orders',
        'type'     => 'number',
        'css'      => 'min-width:300px;',
        'std'      => '1',
        'default'  => '1',
        'desc'     => __( 'Set value to -1 to disable this functionality.', 'wc_free_shipping' ),
      );
    }
    $updated_settings[] = $section;
  }
  return $updated_settings;
}

// Calcualte and remove shipping amount
add_filter( 'woocommerce_cart_ready_to_calc_shipping', 'irb_remove_shipping_calc_on_cart', 99 );
function irb_remove_shipping_calc_on_cart( $show_shipping ) {
  $qtyLimit = get_option('woocommerce_free_shipping_on_orders');
    if( !is_bool($qtyLimit) && $qtyLimit > -1 && WC()->cart->get_cart_contents_count() >= $qtyLimit) {
        return false;
    }
    return $show_shipping;
}

// Not working now will update it later if required
add_filter( 'woocommerce_package_rates', 'irb_wc_change_flat_rates_cost', 10, 2 );
function irb_wc_change_flat_rates_cost( $rates, $package ) {
  if ( isset( $rates['flat_rate'] ) ) {
    $cart_subtotal = WC()->cart->get_cart_contents_count();
    $qtyLimit = get_option('woocommerce_free_shipping_on_orders');
    if ( !is_bool($qtyLimit) && $qtyLimit > -1 && $cart_subtotal >= $qtyLimit ) {
      $rates['flat_rate']->cost = 0;
    }
  }

  return $rates;
}
