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
add_action( 'woocommerce_subscriptions_renewal_order_created', 'wpcom_increment_renewal_order', 10, 1 );
function wpcom_increment_renewal_order($order) {
	global $wpdb, $woocommerce;

	// Send me an email if this hook works
	error_log("woocommerce_subscriptions_renewal_order_created hook fired", 1, "savvywit@gmail.com", 
  	"Subject: woocommerce_subscriptions_renewal_order_created");

	if ( ! is_object( $order ) )
		$order = new WC_Order( $order );
	// Get the values we need from the WC_Order object
	$order_id = $order->id;
	$item = $order->get_items();
	$product_id = WC_Subscriptions_Order::get_items_product_id( $item[0] );

	// These arrays contain the details for each possible product, in order. They
	// are used to change the order to the correct values
	$quarterly_product_ids = array(2949, 2945, 2767, 2793, 2795, 2796, 2798, 2799, 2800, 2805, 2806, 2815, 2816, 2817);
	$quarterly_product_names = array("One Day Renewal for Testing No Virtual Downloadable", "One Day Renewal for Testing", "0-3 MONTHS ELEPHANT (QUARTERLY)", "3-6 MONTHS HIPPO (QUARTERLY)", "6-9 MONTHS GIRAFFE (QUARTERLY)", "9-12 MONTHS PANDA (QUARTERLY)", "12-15 MONTHS ZEBRA (QUARTERLY)", "15-18 MONTHS RABBIT (QUARTERLY)", "18-21 MONTHS MONKEY (QUARTERLY)", "21-24 MONTHS FROG (QUARTERLY)", "24-27 MONTHS KANGAROO (QUARTERLY)", "27-30 MONTHS BEAR (QUARTERLY)", "30-33 MONTHS TIGER (QUARTERLY)", "33-36 MONTHS CROCODILE (QUARTERLY)");
	// Not sure yet if SKUs are needed here
	$quarterly_product_skus = array("0-3-elephant-q", "3-6-hippo-q", "6-9-giraffe-q", "9-12-panda-q", "12-15-zebra-q", "15-18-rabbit-q", "18-21-monkey-q", "21-24-frog-q", "24-24-kangaroo-q", "27-30-bear-q", "30-33-tiger-q", "33-36-crocodile-q");
	$yearly_product_names = array();
	$yearly_product_ids = array();

	// Get the position of the current id, and then assign the next product
	// to $incremented_item_id and $incremented_item_name
	$product_id_index = array_search($product_id, $quarterly_product_ids);
	if($product_id_index === False)
		$product_id_index = array_search($product_id, $yearly_product_ids);
	$incremented_item_id = $quarterly_product_ids[$product_id_index + 1];
	$incremented_item_name = $quarterly_product_names[$product_id_index + 1];

	// Update woocommerce_order_items table
	$order_items_table = prefix."woocommerce_order_items";
	$wpdb->update( $order_items_table,
		array( 'order_item_name' => $incremented_item_name ),
		array( 'order_id' => $order_id )
	);

	// Update woocommerce_order_itemmeta table
	$order_row = $wpdb->get_row("SELECT * FROM $order_items_table WHERE order_id = $order_id", ARRAY_A);
	$order_item_id = $order_row['order_item_id'];
	$order_itemmeta_table = prefix."woocommerce_order_itemmeta";
	$wpdb->update( $order_itemmeta_table,
		array( 'meta_value' => $incremented_item_id ),
		array( 'meta_key' => '_product_id',
		       'order_item_id' => $order_item_id )
	);
}
?>
