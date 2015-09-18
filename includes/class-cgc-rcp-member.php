<?php

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

		global $rcp_options;

		$user_id = get_current_user_id();
		$member  = new RCP_Member( $user_id );
		
		if( empty( $_POST['stripeToken'] ) ) {
			wp_die( 'Missing Stripe token, please try again or contact support if the issue persists.' );
		}

		$token   = $_POST['stripeToken'];
		$email   = $_POST['stripeEmail'];

		$plan_id = $_POST['subscription'];
		$price   = $_POST['price'];

		$plan_name = strtolower( str_replace( ' ', '', rcp_get_subscription_name( $plan_id ) ) );;

		$subscription = rcp_get_subscription_details( $plan_id );
		$currency     = strtolower( $rcp_options['currency'] );

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

		// Check for plan in Stripe, otherwise create it.
		try {
			$plan = \Stripe\Plan::retrieve( $plan_id );
			$plan_exists = true;
		} catch ( Exception $e ) {
			$plan_exists = false;
		}

		if ( !$plan_exists ) {
			\Stripe\Plan::create( array(
				"amount"         => $price,
				"interval"       => $subscription->duration_unit,
				"interval_count" => $subscription,
				"name"           => $subscription->name,
				"currency"       => $currency,
				"id"             => $plan_name
				)
			);

		} else {

			// Subscriber the customer to the plan in Stripe
			$customer->updateSubscription( array( 'plan' => $plan_name ) );

		}

		// Update member in RCP
		$member->set_payment_profile_id( $customer->id );
		$member->set_status( 'active' );

		// Update the expiration
		$member->set_expiration_date( rcp_calc_member_expiration( $subscription ) );

		// Update the user's plan and make recurring
		update_user_meta( $user_id, 'rcp_subscription_level', $plan_id );
		$member->set_recurring( $yes = true );

		// Gather the payment data
		$payment_data = array(
			'date'              => date( 'Y-m-d g:i:s', time() ),
			'subscription'      => $member->get_subscription_name(),
			'payment_type' 		=> 'Credit Card',
			'subscription_key' 	=> $member->get_subscription_key(),
			'amount' 			=> $price / 100,
			'user_id' 			=> $member->ID,
			// 'transaction_id'    => $invoice->id
		);

		// Insert payment for user
		$rcp_payments = new RCP_Payments();
		$rcp_payments->insert( $payment_data );

	}

}
