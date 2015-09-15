<?php 

// require_once( CGC_RCP_Stripe_Checkout_PlUGIN_DIR . 'vendor/autoload.php' );
// require_once( "../vendor/autoload.php" );

global $rcp_options;

if( isset( $rcp_options['sandbox'] ) ) {
	$secret = $rcp_options['stripe_test_secret'];
} else {
	$secret = $rcp_options['stripe_live_secret'];
}

\Stripe\Stripe::setApiKey( $secret );
