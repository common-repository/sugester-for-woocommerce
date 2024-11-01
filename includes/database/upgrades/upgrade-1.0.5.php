<?php
/**
 * Adds statuses
 * @since 1.0.5
 */
function woocommerce_sugester_upgrade_1_0_5() {
	global $wpdb;
	$res = true;

	$tools = & WC_Sugester_Tools::get_instance_with_db();
	if ( ! $tools->is_configured() ) {
		return true;
	}

	// If plugin is configured correctly then we perform some magic tricks
	$tools->update_sugester_statuses();
	$clients_table = $wpdb->prefix . 'sugester_woocommerce_clients';
	$sugester_clients = $wpdb->get_results( "SELECT * FROM " . $clients_table, ARRAY_A );

	foreach ( $sugester_clients as $client ) {
		if ( ! empty( $client[ 'external_client_id' ] ) ) {
			$usermeta = $tools->get_user_meta( $client[ 'client_id'] );
			$url = $tools->get_client_url_json( $client[ 'external_client_id' ] );
			$data = array(
				'api_token' => $tools->c->api_token,
				'client' => array(
					'post_code' => $usermeta[ 'post_code' ],
				),
			);
			$tools->make_request( $url, 'PUT', $data );
		}
	}

	return true;
}