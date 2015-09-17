<?php
/**
 * Plugin Name: CG Cookie Stripe Checkout for RCP
 * Description: Adds support for Stripe Checkout in RCP
 * Author: Jonathan Williamson
 * Author URI: http://cgcookie.com
 * Version: 0.0.1
 */

class CGC_RCP_Member {

	function __construct() {


		global $rcp_options;

		if( isset( $rcp_options['sandbox'] ) ) {
			$secret = $rcp_options['stripe_test_secret'];
		} else {
			$secret = $rcp_options['stripe_live_secret'];
		}

		\Stripe\Stripe::setApiKey( $secret );

	}

	public function process_signup() {	

		$user_id = get_current_user_id();
		$member  = new RCP_Member( $user_id );
		
		if( empty( $_POST['stripeToken'] ) ) {
			wp_die( 'Missing Stripe token, please try again or contact support if the issue persists.' );
		}

		$token   = $_POST['stripeToken'];
		$email   = $_POST['stripeEmail'];

		$price   = rcp_get_subscription_price( 3 ) * 100;
		$plan_id = strtolower( str_replace( ' ', '', rcp_get_subscription_name( 3 ) ) );
		
		$customer_id = $member->get_payment_profile_id();
		
		// Check for exisitng Customer ID, otherwise create new custoemr
		if( empty( $customer_id ) ) {

			$customer = \Stripe\Customer::create(array(
				'email' => $email,
				'card'  => $token
			));

			$customer_id = $customer->id;

		} else {

			$customer = \Stripe\Customer::retrieve( $customer_id );	
		}

		// Subscriber the customer to the plan in Stripe
		$customer->updateSubscription( array( 'plan' => $plan_id ) );

		// Update member in RCP
		$member->set_payment_profile_id( $customer->id );
		$member->set_status( 'active' );
		$member->set_recurring( $yes = true );
	}

}
