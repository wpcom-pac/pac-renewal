<?php
/*
Plugin Name: WPComm Renewal Order Items Update
Plugin URI: http://wpcommunity.com
Description: Modifies a renewal order's Product Id and SKU
Version: 1.0
Author: Rich Dudka
Author URI: http://wpcommunity.com
License: Private Client
*
* Uses woocommerce_subscriptions_renewal_order_items hook.
* Saves the new product id in the order_item. Saves the original SKU
* in postmeta meta_value '_original_sku'. Saves the new SKU in renewal order
* meta_value '_sku' and updates the original order meta_value '_sku' to
* the renewal SKU.
*
* Reasoning: Woocommerce seems to get SKUs only from the original order
* meta_value '_sku', so we have to change that for each renewal order.
* Also, the fulfillment house may need to get the SKU from the renewal order,
* and it is a good idea to have a record of the original SKU.
*/

add_filter( 'woocommerce_subscriptions_renewal_order_items', 'wpcom_renewal_order_items_update', 1, 5);

function wpcom_renewal_order_items_update( $order_items, $original_order_id, $renewal_order_id, $product_id, $new_order_role )
{
	global $wpdb, $woocommerce;

	$postmeta_table = prefix."postmeta";

	// Arrays of product IDs and SKUs for incrementing
	$quarterly_product_ids = array(2949, 2945, 2767, 2793, 2795, 2796, 2798, 2799, 2800, 2805, 2806, 2815, 2816, 2817);
	$quarterly_product_skus = array("test sku one", "test sku two", "0-3-elephant-q", "3-6-hippo-q", "6-9-giraffe-q", "9-12-panda-q", "12-15-zebra-q", "15-18-rabbit-q", "18-21-monkey-q", "21-24-frog-q", "24-27-kangaroo-q", "27-30-bear-q", "30-33-tiger-q", "33-36-crocodile-q");

	// Get the last product ID from the original order postmeta. If it is not there (i.e. this is the first renewal), then
	// use the product ID from the parameters.
	$last_product_id = $wpdb->get_row("SELECT 'meta_value' FROM $postmeta_table WHERE 'post_id' = $original_order_id AND 'meta_key' = '_last_product_shipped_id'");
	if( empty( $last_product_id ) )
	{
		$last_product_id = $product_id;
	}

	// Copy the original SKU to _original_sku, if it was not done already
	$original_sku = $wpdb->get_row("SELECT 'meta_value' FROM $postmeta_table WHERE 'post_id' = $original_order_id AND 'meta_key' = '_original_sku'");
	if( empty( $original_sku ) )
	{
		$original_sku = $wpdb->get_row("SELECT 'meta_value' FROM $postmeta_table WHERE 'post_id' = $original_order_id AND 'meta_key' = '_sku'");
		add_post_meta($original_order_id, '_original_sku', $original_sku);
	}

	$product_id_index = array_search($last_product_id, $quarterly_product_ids);
	$incremented_product_id = $quarterly_product_ids[$product_id_index + 1];
	$incremented_product_sku = $quarterly_product_skus[$product_id_index + 1];

	// Save the latest product ID for future renewals
	update_post_meta($original_order_id, '_last_product_shipped_id', $incremented_product_id, $product_id);

	// Add the new SKU to the renewal order postmeta
	add_post_meta($renewal_order_id, '_sku', $incremented_product_sku);

	// Update the original order SKU to the renewal SKU
	update_post_meta($original_order_id, '_sku', $incremented_product_sku);

	// Send me an email when this hook fires
	error_log("woocommerce_subscriptions_renewal_order_items hook fired. Original Order ID: ".$original_order_id." Renewal Order ID: ".$renewal_order_id." Old Product ID: ".$product_id." New Product ID: ".$incremented_product_id, 1, "savvywit@gmail.com", 
  	"Subject: woocommerce_subscriptions_renewal_order_items");

	$order_item = $order_items[0];
	$order_item['product_id'] = $incremented_product_id;
	$order_items[0] = $order_item;
	return $order_items;
}
?>
