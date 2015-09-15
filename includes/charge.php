<?php 

// require_once dirname( __FILE__ ) . "/config.php";

function cgc_rcp_stripe_checkout_charge() {

	global $rcp_options;

	if( isset( $rcp_options['sandbox'] ) ) {
		$secret = $rcp_options['stripe_test_secret'];
	} else {
		$secret = $rcp_options['stripe_live_secret'];
	}

	\Stripe\Stripe::setApiKey( $secret );


	if ( isset( $_POST['stripeToken'] ) ){

		$user_id = get_current_user_id();
		$member = new RCP_Member( $user_id );

		$token  = $_POST['stripeToken'];
		$email  = $_POST['stripeEmail'];

		$price  = rcp_get_subscription_price( 3 ) * 100;
		$subscription = rcp_get_subscription_details( 3 );
		$plan_id = strtolower( str_replace( ' ', '', rcp_get_subscription_name( 3 ) ) );

		$customer = \Stripe\Customer::create(array(
		  'email' => $email,
		  'card'  => $token
		));

		$charge = \Stripe\Charge::create(array(
		  'customer' => $customer->id,
		  'amount'   => $price,
		  'currency' => 'usd'
		));

		$customer->updateSubscription( array( 'plan' => $plan_id ) );

		$member->set_payment_profile_id( $customer->id );
		$member->set_status( 'active' );
		$member->set_recurring( $yes = true );

		// echo '<pre>'; 
		// print_r( $_POST ); 
		// echo '</pre>'; exit;


	}

}
add_action( 'init', 'cgc_rcp_stripe_checkout_charge' );