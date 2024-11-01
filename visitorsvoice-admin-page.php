<?php

	$api_authorized = get_option( 'visitorsvoice_api_authorized' );
	$fixed_suggestions_expire = get_option( 'visitorsvoice_fixed_suggestions_expire' );
	$days_before_check_for_suggestion = get_option( 'visitorsvoice_days_before_check_for_suggestion' );
	
	if( $api_authorized ) {
	$visitorsvoiceplugin = VisitorsVoicePlugin::get_instance();
		if($visitorsvoiceplugin->check_configured()) {
			include( 'visitorsvoice-configuration-page.php' );
		} else {
			include( 'visitorsvoice-controls-page.php' );			
		}
	} else {
		include( 'visitorsvoice-authorize-page.php' );
	}