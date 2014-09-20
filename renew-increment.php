<?php
/*
Plugin Name: WPCom Increment Renewal Order
Plugin URI: http://wpcommunity.com
Description: Updates customer's category for future shipments
Version: 1.0
Author: Rich Dudka
Author URI: http://wpcommunity.com
License: Private Client
*/
add_filter( 'woocommerce_subscriptions_renewal_order_created', 'wpcom_increment_renewal_order', 10, 1 );
function wpcom_increment_renewal_order( $order_id ) {
global $wpdb, $woocommerce;

$order_items_table = prefix."woocommerce_order_items";
$order_itemmeta_table = prefix."woocommerce_order_itemmeta";
$test_item_name = "testing renewal updating";

$wpdb->update( $order_items_table,
array( 'order_item_name' => $test_item_name ),
array( 'order_id' => $order_id )
);
}
?>