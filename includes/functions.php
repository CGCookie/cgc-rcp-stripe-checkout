<?php 

function cgc_rcp_process_checkout() {

	if ( isset( $_POST['stripeToken'] ) && 'stripe-checkout' == $_POST['source'] ){

		$cgc_member = new CGC_RCP_Member();
		$cgc_member->process_signup();

	}

}
add_action( 'init', 'cgc_rcp_process_checkout' );