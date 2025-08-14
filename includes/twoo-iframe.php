<?php
function twoo_add_iframe( $order_id ) {
	if ( ! $order_id ) {
		return;
	}

	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		return;
	}

	// Optional: only track once and only for paid orders.
	if ( $order->get_meta( '_twoo_tracked' ) ) {
		return;
	}
	if ( ! $order->has_status( array( 'processing', 'completed' ) ) ) {
		return;
	}

	$lines = array();
	$net   = 0.0;

	foreach ( $order->get_items( 'line_item' ) as $item ) {
		$name    = $item->get_name();
		$product = $item->get_product();
		$sku     = $product ? $product->get_sku() : '';
		$lines[] = $sku ? "{$name} (SKU: {$sku})" : $name;

		// get_total() for line items is ex-tax; perfect for affiliate "amount"
		$net += (float) $item->get_total();
	}

	$description   = implode( ', ', $lines );
	$sale_value    = round( $net, 2 );
	$transactionId = $order->get_id();

	$iframe_url = add_query_arg(
		array(
			'campaign_unique' => get_option( 'twoo_campaign_unique' ),
			'confirm'         => get_option( 'twoo_confirm' ),
			'transaction_id'  => $transactionId,
			'description'     => $description,
			'amount'          => $sale_value,
		),
		'https://event.2performant.com/events/salecheck'
	);

	echo '<iframe height="1" width="1" scrolling="no" marginheight="0" marginwidth="0" frameborder="0" src="' . esc_url( $iframe_url ) . '"></iframe>';

	$order->update_meta_data( '_twoo_tracked', time() );
	$order->save();
}
add_action( 'woocommerce_thankyou', 'twoo_add_iframe', 10, 1 );