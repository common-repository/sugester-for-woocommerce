<?php
/**
 * Removes account domain from the database
 * @since 1.0.4
 * Changes the way the plugin works
 */
function woocommerce_sugester_upgrade_1_0_4() {
	global $wpdb;
	$res = true;
	delete_option( 'sugester_woocommerce_db_version' );
	delete_option( 'sugester_woocommerce_is_configured' );

	$settings = get_option( 'woocommerce_sugester_settings' );
	if ( !empty( $settings['account_domain'] ) && !empty( $settings['api_token']) ) {
		// Attempt to append account domain prefix to api_token
		$api_token      = $settings['api_token'];
		$account_domain = $settings['account_domain'];
		if ( strpos($account_domain, '.sugester.') !== false ) {
			$account_domain = explode('.', $account_domain);
			if ( ! empty( $account_domain[0] ) && strpos( $api_token, '/' ) === false ) {
				// We append the prefix to api_token
				$settings['api_token'] = $api_token . '/' . $account_domain[0];
			}
		}
	}

	// We remove account domain from the settings
	unset($settings['account_domain']);

	if ( empty( $settings ) ) {
		// If record was not found in the database
		$settings = array();
	}
	$upgrade_container = new WC_Sugester_Container( $settings );
	$integration_test_interface = new WC_Sugester_Integration_Test( $upgrade_container );
	$errors = $integration_test_interface->run_integration_tests();

	update_option( 'woocommerce_sugester_is_configured', empty( $errors ) );
	update_option( 'woocommerce_sugester_settings',      empty( $errors ) ? $settings : array() );

	return true;
}