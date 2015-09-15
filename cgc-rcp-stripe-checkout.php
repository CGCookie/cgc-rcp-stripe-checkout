<?php
/**
 * Plugin Name: CG Cookie Stripe Checkout for RCP
 * Description: Adds support for Stripe Checkout in RCP
 * Author: Jonathan Williamson
 * Author URI: http://cgcookie.com
 * Version: 0.0.1
 */


$plugin_url = WP_PLUGIN_URL . '/cgc-rcp-stripe-checkout';

class cgcStripeCheckout {

	function __construct() {

		if( class_exists( 'RCP_Member' ) ) {
			require_once dirname( __FILE__ ) . "/includes/config.php";
			require_once dirname( __FILE__ ) . "/includes/charge.php";
			require_once dirname( __FILE__ ) . "/includes/shortcodes.php";

		if( ! class_exists( 'Stripe\Stripe' ) ) {
			require_once RCP_PLUGIN_DIR . 'includes/libraries/stripe/init.php';
		}
	}
}