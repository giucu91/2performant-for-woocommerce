<?php
/**
 * Twoo Performant @ Uprise
 *
 * @package       twoo-performant-uprise
 * @author        Eduard V. Doloc
 * @license       gplv3-or-later
 *
 * @wordpress-plugin
 * Plugin Name:   2Performant for WooCommerce
 * Description:  Full integration with 2 Performant for WooCommerce, supports 3rd party tracking, 1st party tracking and basic feed generation!
 * Version:       1.0.3
 * Author:        Eduard V. Doloc
 * Author URI:    https://rwky.ro
 * Text Domain:   twoo-performant-uprise
 * Domain Path:   /languages
 * License:       GPLv3 or later
 * License URI:   https://www.gnu.org/licenses/gpl-3.0.html
 * WC requires at least: 4.2
 * WC tested up to: 8.8
 * Tags: 2performant woocommerce, 2performant, 2 performant
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//feed
include_once 'twoo-feed.php';
function tp_add_settings_tab( $settings_tabs ) {
	$settings_tabs['twoo_performant_uprise'] = __( '2Performant', 'twoo-performant-uprise' );

	return $settings_tabs;
}

add_filter( 'woocommerce_settings_tabs_array', 'tp_add_settings_tab', 50 );

function tp_settings_tab() {
	woocommerce_admin_fields( get_twoo_performant_uprise_settings() );
}

add_action( 'woocommerce_settings_tabs_twoo_performant_uprise', 'tp_settings_tab' );

function get_twoo_performant_uprise_settings() {
	$feed_url = home_url( '/twoo-feed/' );
	$settings = array(
		'section_title'       => array(
			'name' => __( '2 Performant Settings', 'twoo-performant-uprise' ),
			'type' => 'title',
			'desc' => 'Here you can set all your essential settings and get your feed url! The feed url is <a href="' . $feed_url . '" target="_blank">' . $feed_url . '</a>.',
			'id'   => 'tp_section_title'
		),
		'campaign_unique'     => array(
			'name' => __( 'Campaign Unique', 'twoo-performant-uprise' ),
			'type' => 'text',
			'desc' => 'You can find the values <a href="https://businessleague.2performant.com/advertiser/attribution/iframe_tracking#installCode" target="_blank">here</a>; It is something like campaign_unique=abc1234, please input the value after =',
			'id'   => 'tp_campaign_unique'
		),
		'confirm'             => array(
			'name' => __( 'Confirm', 'twoo-performant-uprise' ),
			'type' => 'text',
			'desc' => 'You can find the values <a href="https://businessleague.2performant.com/advertiser/attribution/iframe_tracking#installCode" target="_blank">here</a>; It is something like conform=abc1234, please input the value after =',
			'id'   => 'tp_confirm'
		),
		'big_bear'            => array(
			'name' => __( 'Big Bear Attribution', 'twoo-performant-uprise' ),
			'type' => 'text',
			'desc' => 'You can find the value <a href="https://businessleague.2performant.com/advertiser/attribution/big_bear_attribution#section_0" target="_blank">here</a>; It is usually right after attr-2p.com/THIS_IS_THE_ID/clc/1.js',
			'id'   => 'tp_big_bear'
		),
		'css_classes_to_hide' => array(
			'name' => __( 'CSS Classes to Hide (optional)', 'twoo-performant-uprise' ),
			'type' => 'text',
			'desc' => __( 'Enter the CSS classes to hide elements for network traffic; CSS elements need to be separated by commas (e.g., .class-1, .class-2).', 'twoo-performant-uprise' ),
			'id'   => 'tp_css_classes_to_hide'
		),
		'section_end'         => array(
			'type' => 'sectionend',
			'id'   => 'tp_section_end'
		)
	);

	return apply_filters( 'tp_settings', $settings );
}

function tp_save_settings() {
	woocommerce_update_options( get_twoo_performant_uprise_settings() );
}

add_action( 'woocommerce_update_options_twoo_performant_uprise', 'tp_save_settings' );


function tp_add_iframe_tracking( $order_id ) {
	$order = wc_get_order( $order_id );
	$items = $order->get_items();


	$description = array();
	foreach ( $items as $item ) {
		$product       = $item->get_product();
		$product_name  = $product->get_name();
		$sku           = $product->get_sku();
		$description[] = "{$product_name} (SKU: {$sku})";
	}
	$description_string = implode( ', ', $description );
	$sale_value         = $order->get_total() - $order->get_total_tax() - $order->get_shipping_total();
	$transaction_id     = $order->get_id();

	// Echo the iframe with dynamic values
	echo "<iframe height='1' width='1' scrolling='no' marginheight='0' marginwidth='0' frameborder='0' src='https://event.2performant.com/events/salecheck?campaign_unique=" . get_option( 'tp_campaign_unique' ) . "&confirm=" . get_option( 'tp_confirm' ) . "&transaction_id={$transaction_id}&description=" . urlencode( $description_string ) . "&amount={$sale_value}'></iframe>";
}

add_action( 'woocommerce_thankyou', 'tp_add_iframe_tracking' );


function tp_enqueue_scripts() {
	if ( ! empty( get_option( 'tp_big_bear' ) ) ) {
		wp_enqueue_script( 'tp_big_bear', 'https://attr-2p.com/' . get_option( 'tp_big_bear' ) . '/clc/1.js', array(), null, true );
	}
}

add_action( 'wp_enqueue_scripts', 'tp_enqueue_scripts' );


function tp_modify_robots_txt( $output, $public ) {
	$marker = "@ 2 Performant @";
	if ( strpos( $output, $marker ) === false ) {
		$text   = "\n# " . $marker . "\n";
		$text   .= "Disallow: /*2pau\n";
		$text   .= "Disallow: /*2ptt\n";
		$text   .= "Disallow: /*2ptu\n";
		$text   .= "Disallow: /*2prp\n";
		$text   .= "Disallow: /*2pdlst\n";
		$output .= $text;
	}

	return $output;
}

add_filter( 'robots_txt', 'tp_modify_robots_txt', 10, 2 );

function tp_activate_plugin() {
	flush_rewrite_rules();
}

register_activation_hook( __FILE__, 'tp_activate_plugin' );


function tp_enqueue_hide_elements_script() {
	wp_enqueue_script( 'tp_postmessage', 'https://event.2performant.com/javascripts/postmessage.js', array( 'jquery' ), null, true );


	$css_classes_to_hide = get_option( 'tp_css_classes_to_hide', '' );
	$css_classes_to_hide = str_replace( ' ', '', $css_classes_to_hide );


    $inline_script = "
	                 <script type='text/javascript'>
	                              jQuery(document).ready(function($) {
		                              window.dp_network_url = 'event.2performant.com';
		                              window.dp_campaign_unique = '" . esc_js(get_option('tp_campaign_unique', '')) . "';
        window.dp_cookie_result = function(data){
	        if(data && data.indexOf(':click:') !== -1) {
		        $('" . esc_js($css_classes_to_hide) . "').hide();
	        } else {
		        $('" . esc_js($css_classes_to_hide) . "').show();
	        }
        };
        xtd_receive_cookie(); 
    });
    </script>";



	echo $inline_script;
}

add_action( 'wp_footer', 'tp_enqueue_hide_elements_script' );
