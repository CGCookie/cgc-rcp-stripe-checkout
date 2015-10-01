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
		$user_data = get_userdata( $user_id );
		
		if( empty( $_POST['stripeToken'] ) ) {
			wp_die( 'Missing Stripe token, please try again or contact support if the issue persists.' );
		}

		$token   = $_POST['stripeToken'];
		$email   = $_POST['stripeEmail'];

		$plan_id = $_POST['subscription_id'];
		$price   = $_POST['price'];
		$base_price = $price;

		$plan_name = strtolower( str_replace( ' ', '', rcp_get_subscription_name( $plan_id ) ) );;

		$discount       = isset( $_POST['rcp_discount'] ) ? sanitize_text_field( $_POST['rcp_discount'] ) : '';
		$discount_valid = false;
		$subscription   = rcp_get_subscription_details( $plan_id );
		$expiration     = rcp_get_subscription_length( $plan_id );

		$currency     = strtolower( $rcp_options['currency'] );

		$redirect = rcp_get_current_url();

		$subscription_data = array(
			'price'             => $price,
			'discount'          => $base_price - $price,
			'discount_code'     => $discount,
			'fee' 			    => ! empty( $subscription->fee ) ? number_format( $subscription->fee, 2 ) : 0,
			'length' 			=> $expiration->duration,
			'length_unit' 		=> strtolower( $expiration->duration_unit ),
			'subscription_id'   => $subscription->id,
			'subscription_name' => $subscription->name,
			'key' 				=> '',
			'user_id' 			=> $user_data->id,
			'user_name' 		=> $user_data->user_login,
			'user_email' 		=> $user_data->user_email,
			'currency' 			=> $rcp_options['currency'],
			'auto_renew' 		=> true,
			'return_url' 		=> $redirect,
			'new_user' 			=> false,
			'post_data' 		=> $_POST
		);

		// Update the user's plan
		update_user_meta( $user_id, 'rcp_subscription_level', $plan_id );

		// send all of the subscription data off for processing by the gateway
		rcp_send_to_gateway( 'stripe', apply_filters( 'rcp_subscription_data', $subscription_data ) );

	}

}
