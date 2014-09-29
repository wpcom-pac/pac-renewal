<?php
/*
Plugin Name: WPCOM Update Renewal Order
Plugin URI: http://wpcommunity.com
Description: Updates a renewal order's Product Id, Product Name, and SKU
Version: 1.0
Author: Rich Dudka
Author URI: http://wpcommunity.com
License: Private Client
.
Uses woocommerce_subscriptions_renewal_order_created hook. Using arrays here because custom fields
in the product is not ready.
.
*/
add_action( 'woocommerce_subscriptions_renewal_order_created', 'wpcomm_update_renewal_order', 1, 4);
function wpcomm_update_renewal_order( $renewal_order, $original_order, $product_id, $new_order_role )
{
	global $wpdb, $woocommerce;

	$renewal_order_id = $renewal_order['id'];
	$original_order_id = $original_order['id'];

	// Send me an email if this hook works
	error_log("woocommerce_subscriptions_renewal_order_created hook fired. Original order ID: ".$original_order_id." Renewal order ID: ".$renewal_order_id." Product ID: ".$product_id." New order role: ".$new_order_role, 1, "savvywit@gmail.com", 
  	"Subject: woocommerce_subscriptions_renewal_order_created");
}
?>
