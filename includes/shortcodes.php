<?php 

function rcp_stripe_checkout_shortcode( ) {
	global $rcp_options;

	if( isset( $rcp_options['sandbox'] ) ) {
		$key = $rcp_options['stripe_test_publishable'];
	} else {
		$key = $rcp_options['stripe_live_publishable'];
	}

	$user_id      = get_current_user_id();
	$user         = get_userdata( $user_id );

	$price        = rcp_get_subscription_price( 3 ) * 100;
	$subscription = rcp_get_subscription_details( 3 );


	?>
	<form action="" method="post">
		<script src="https://checkout.stripe.com/checkout.js" class="stripe-button"
			data-key="<?php echo $key ?>"
			data-name="CG Cookie"
			data-image="https://s3.amazonaws.com/cgcookie/cgc_logo_128.png"
			data-description="Join Citizen ($<?php echo $price / 100 ?> per month)"
			data-label="Join <?php echo $subscription->name ?>"
			data-amount="<?php echo $price ?>"
			data-locale="auto"
			data-email="<?php echo $user->user_email ?>"
			data-allow-remember-me="false"
			>
		</script>
	</form>
	<?php
}
add_shortcode( 'rcp_stripe_checkout', 'rcp_stripe_checkout_shortcode' );