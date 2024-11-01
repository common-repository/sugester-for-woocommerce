<?php
/**
 * First database migration.
 * Changes the way the plugin works
 */
function woocommerce_sugester_upgrade_1_0_2() {
	global $wpdb;
	$res = true;
	$db_key = 'sugester_woocommerce_db_version';
	$db_version = get_option( $db_key );
	if ( ! empty( $db_version ) ) {
		$res = $res && delete_option( $db_key );
	}

	/*$prefix = $wpdb->prefix . 'sugester_woocommerce_';
	$clients_table = $prefix . "clients";
	$orders_table = $prefix . "orders";

	if ($wpdb->get_var("SHOW TABLES LIKE '${clients_table}'") === $clients_table) {
		// clients
		error_log("clients exists");
		$clients = $wpdb->get_results( "
			SELECT *
			FROM ${clients_table}",
			ARRAY_A
		);
		error_log(print_r($clients, true));
	}

	if ($wpdb->get_var("SHOW TABLES LIKE '${orders_table}'") === $orders_table) {
		// orders
		error_log("orders exists");
	}
	*/
	/*return ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name);


	$clients = = $wpdb->get_results( "
			SELECT *
			FROM  $table_name
			WHERE " . $field . " = " . (int) $id,
			ARRAY_A
		);*/

	return $res;
}