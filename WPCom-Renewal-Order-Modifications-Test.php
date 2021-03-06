<?php
/*
Plugin Name: WPComm Renewal Order Modifications
Plugin URI: http://wpcommunity.com
Description: Modifies a renewal order's Product Id, Product Name, and SKU
Version: 1.0
Author: Rich Dudka
Author URI: http://wpcommunity.com
License: Private Client
.
. Uses woocommerce_subscriptions_renewal_order_items hook.
. Uses woocommerce_subscriptions_renewal_order_item_name hook.
. Uses woocommerce_subscriptions_renewal_order_meta_query hook.
. It is expected to update the tables before woocommerce creates the renewal order and sends email. 
. Custom meta_key _last_quarterly_product_id is intended to keep track of the last product shipped.
.
. Example from http://docs.woothemes.com/document/subscriptions/develop/filter-reference/
function eg_do_not_copy_meta_data( $order_meta_query, $original_order_id, $renewal_order_id, $new_order_role ) {

	$order_meta_query .= " AND `meta_key` NOT IN ('_my_extensions_meta_key', '_my_extensions_other_meta_key')";

	return $order_meta_query;
}
add_filter( 'woocommerce_subscriptions_renewal_order_meta_query', 'eg_do_not_copy_meta_data', 10, 4 ); */

add_filter( 'woocommerce_subscriptions_renewal_order_items', 'wpcom_renewal_order_items_update', 1, 5);
add_filter( 'woocommerce_subscriptions_renewal_order_item_name', 'wpcom_renewal_order_item_name_update', 1, 3);
add_filter( 'woocommerce_subscriptions_renewal_order_meta_query', 'wpcomm_renewal_meta_query_update', 1, 4);

function wpcom_renewal_order_items_update( $order_items, $original_order_id, $renewal_order_id, $product_id, $new_order_role )
{
	global $wpdb, $woocommerce;

	$postmeta_table = prefix."postmeta";

	// Array of product IDs for incrementing
	$quarterly_product_ids = array(2949, 2945, 2767, 2793, 2795, 2796, 2798, 2799, 2800, 2805, 2806, 2815, 2816, 2817);

	// Get the last product ID from the original order postmeta. If it is not there (i.e. this is the first renewal), then
	// Get the product ID from woocommerce_order_itemmeta table.
	$last_product_id = $wpdb->get_row("SELECT 'meta_value' FROM $postmeta_table WHERE 'post_id' = $original_order_id AND 'meta_key' = '_last_quarterly_product_id'");
	if( empty( $last_product_id ) )
	{
		$last_product_id = $product_id;
	}

	$product_id_index = array_search($last_product_id, $quarterly_product_ids);
	$incremented_product_id = $quarterly_product_ids[$product_id_index + 1];

	// Send me an email when this hook fires
	error_log("woocommerce_subscriptions_renewal_order_items hook fired. Original Order ID: ".$original_order_id." Renewal Order ID: ".$renewal_order_id." Old Product ID: ".$product_id." New Product ID: ".$incremented_product_id, 1, "savvywit@gmail.com", 
  	"Subject: woocommerce_subscriptions_renewal_order_meta_query");

	// $order_items['product_id'] => $incremented_product_id;
	return $order_items;
}

function wpcom_renewal_order_item_name_update( $order_item_name, $order_item, $original_order )
{
	global $wpdb, $woocommerce;

	// Table name vars
	$order_items_table = prefix."woocommerce_order_items";
	$order_itemmeta_table = prefix."woocommerce_order_itemmeta";
	$postmeta_table = prefix."postmeta";

	// Array of product IDs for incrementing
	$quarterly_product_ids = array(2949, 2945, 2767, 2793, 2795, 2796, 2798, 2799, 2800, 2805, 2806, 2815, 2816, 2817);

	// Array of product names for incrementing
	$quarterly_product_names = array("One Day Renewal for Testing No Virtual Downloadable", "One Day Renewal for Testing", "0-3 MONTHS ELEPHANT (QUARTERLY)", "3-6 MONTHS HIPPO (QUARTERLY)", "6-9 MONTHS GIRAFFE (QUARTERLY)", "9-12 MONTHS PANDA (QUARTERLY)", "12-15 MONTHS ZEBRA (QUARTERLY)", "15-18 MONTHS RABBIT (QUARTERLY)", "18-21 MONTHS MONKEY (QUARTERLY)", "21-24 MONTHS FROG (QUARTERLY)", "24-27 MONTHS KANGAROO (QUARTERLY)", "27-30 MONTHS BEAR (QUARTERLY)", "30-33 MONTHS TIGER (QUARTERLY)", "33-36 MONTHS CROCODILE (QUARTERLY)");

	// Get the order ID
	$original_order_id = $original_order->id;

	// Get the last product ID from the original order postmeta. If it is not there (i.e. this is the first renewal), then
	// Get the product ID from woocommerce_order_itemmeta table.
	$product_id = $wpdb->get_row("SELECT 'meta_value' FROM $postmeta_table WHERE 'post_id' = $original_order_id AND 'meta_key' = '_last_quarterly_product_id'");
	if( empty( $product_id ) )
	{
		$order_item_id = $wpdb->get_row("SELECT 'order_item_id' FROM $order_items_table WHERE 'order_id' = $order_id");
		$product_id = $wpdb->get_row("SELECT 'meta_value' FROM $order_itemmeta_table WHERE 'order_item_id' = $order_item_id AND 'meta_key' = '_product_id'");
	}

	$product_id_index = array_search($product_id, $quarterly_product_ids);
	$incremented_order_item_name = $quarterly_product_names[$product_id_index + 1];

	// Send me an email when this hook fires
	error_log("woocommerce_subscriptions_renewal_order_item_name hook fired. Original Order Item Name: ".$order_item_name." Incremented Order Item Name: ".$incremented_order_item_name, 1, "savvywit@gmail.com", 
  	"Subject: woocommerce_subscriptions_renewal_order_item_name");

	return $incremented_order_item_name;
}

function wpcomm_renewal_meta_query_update( $order_meta_query, $original_order_id, $renewal_order_id, $new_order_role )
{
	global $wpdb, $woocommerce;

	// Table name vars
	$order_items_table = prefix."woocommerce_order_items";
	$order_itemmeta_table = prefix."woocommerce_order_itemmeta";
	$postmeta_table = prefix."postmeta";

	// Get the last product ID from the original order postmeta. If it is not there (i.e. this is the first renewal), then
	// Get the product ID from woocommerce_order_itemmeta table.
	$product_id = $wpdb->get_row("SELECT 'meta_value' FROM $postmeta_table WHERE 'post_id' = $original_order_id AND 'meta_key' = '_last_quarterly_product_id'");
	if( empty( $product_id ) )
	{
		$order_item_id = $wpdb->get_row("SELECT 'order_item_id' FROM $order_items_table WHERE 'order_id' = $original_order_id");
		$product_id = $wpdb->get_row("SELECT 'meta_value' FROM $order_itemmeta_table WHERE 'order_item_id' = $order_item_id AND 'meta_key' = '_product_id'");
	}

	// These arrays contain the details for each possible product, in order. They
	// are used to change the order to the correct values based on index.
	$quarterly_product_ids = array(2949, 2945, 2767, 2793, 2795, 2796, 2798, 2799, 2800, 2805, 2806, 2815, 2816, 2817);
	$quarterly_product_skus = array("test sku one", "test sku two", "0-3-elephant-q", "3-6-hippo-q", "6-9-giraffe-q", "9-12-panda-q", "12-15-zebra-q", "15-18-rabbit-q", "18-21-monkey-q", "21-24-frog-q", "24-27-kangaroo-q", "27-30-bear-q", "30-33-tiger-q", "33-36-crocodile-q");
	
	// Get the position of the current id, and then assign the next product
	// to $incremented_item_id, $incremented_item_name, and $incremented_item_sku
	$product_id_index = array_search($product_id, $quarterly_product_ids);
	$incremented_item_sku = $quarterly_product_skus[$product_id_index + 1];

	// Update postmeta table
	add_post_meta($renewal_order_id, '_sku', $incremented_item_sku);

	// Send me an email when this hook fires
	error_log("woocommerce_subscriptions_renewal_order_meta_query hook fired. Original Order ID: ".$original_order_id." Renewal Order ID: ".$renewal_order_id." Old Product ID: ".$product_id." New Product ID: ".$incremented_item_id, 1, "savvywit@gmail.com", 
  	"Subject: woocommerce_subscriptions_renewal_order_meta_query");

	$incremented_product_id = $quarterly_product_ids[$product_id_index + 1];
	update_post_meta($original_order_id, '_last_quarterly_product_id', $incremented_product_id, $product_id);

	// Meta values to exclude from renewal order
	$order_meta_query .= " AND `meta_key` NOT IN ('_sku', '_last_quarterly_product_id')";

	return $order_meta_query;
}
?>
