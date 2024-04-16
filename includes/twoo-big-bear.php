<?php

//big bear
function twoo_add_big_bear_click_script() {
	if ( ! empty( get_option( 'twoo_big_bear' ) ) ) {
		echo "<script defer src='https://attr-2p.com/" . esc_attr( get_option( 'twoo_big_bear' ) ) . "/clc/1.js'></script>";
	}
}

add_action( 'wp_footer', 'twoo_add_big_bear_click_script' );


function twoo_robots_update( $output, $public ) {
	if ( ! empty( get_option( 'twoo_big_bear' ) ) ) {
		$marker = "@ 2Performant @";
		if ( strpos( $output, $marker ) === false ) {
			$text   = "\n# " . $marker . "\n";
			$text   .= "Disallow: /*2pau\n";
			$text   .= "Disallow: /*2ptt\n";
			$text   .= "Disallow: /*2ptu\n";
			$text   .= "Disallow: /*2prp\n";
			$text   .= "Disallow: /*2pdlst\n";
			$output .= $text;
		}
	}

	return $output;
}

add_filter( 'robots_txt', 'twoo_robots_update', 10, 2 );


function twoo_noindex_pages_with_big_bear_parameters() {
	if ( ! empty( get_option( 'twoo_big_bear' ) ) && ( isset( $_GET['2pau'] ) || isset( $_GET['2ptt'] ) || isset( $_GET['2ptu'] ) || isset( $_GET['2prp'] ) || isset( $_GET['2pdlst'] ) ) ) {
		echo '<meta name="robots" content="noindex">';
	}
}

add_action( 'wp_head', 'twoo_noindex_pages_with_big_bear_parameters' );

function twoo_add_order_script_to_thank_you_page( $order_id ) {
	if ( ! empty( get_option( 'twoo_big_bear' ) ) ) {
		$order = wc_get_order( $order_id );
		$items = [];
		foreach ( $order->get_items() as $item_id => $item ) {
			$product = $item->get_product();
			$items[] = [
				'quantity'   => $item->get_quantity(),
				'product_id' => $product->get_id(),
				'value'      => $product->get_price(),  // Ensure this is the price without VAT
				'name'       => $product->get_name(),
				'category'   => [ $product->get_categories() ], // This might need further refinement
				'brand'      => $product->get_attribute( 'brand' ),
			];
		}

		$tpOrder = [
			'id'            => $order->get_id(),
			'placed_at'     => $order->get_date_created()->getTimestamp(),
			'currency_code' => get_woocommerce_currency(),
			'items'         => $items
		];

		$tpOrderJson = json_encode( $tpOrder );
		echo "<script>
            var tpOrder = $tpOrderJson;
        </script>
        <script defer src='https://attr-2p.com/" . esc_attr( get_option( 'twoo_big_bear' ) ) . "/sls/1.js'></script>";
	}
}

add_action( 'woocommerce_thankyou', 'twoo_add_order_script_to_thank_you_page' );
