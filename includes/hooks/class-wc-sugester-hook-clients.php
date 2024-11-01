<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WC_Sugester_Hook_Clients' ) ) :

/**
 * WC Sugester Hook Clients  class.
 *
 * Used for communication wordpress <-> sugester.
 * Each hook has a one corresponding function.
 *
 * @package  WC_Sugester_Hooks
 * @category Integration Hooks
 * @author   Sugester
 * @since 1.0.0
 * @todo User register WP
 */
class WC_Sugester_Hook_Clients extends WC_Sugester_Hook_Core {

	/**
	 * Initializes hooks from WC_Sugester_Hook_Client_Create class.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct();

		// If fatal error is present, will not add hooks.
		if ( ! $this->db->fatal_present() && $this->tools->is_configured() ) {
			// Actions.
			add_action( 'user_register',                         array( $this, 'create_user'     ), 10, 1 );
			//add_action( 'woocommerce_created_customer',          array( $this, 'create_customer' ), 10, 3 );
			add_action( 'woocommerce_checkout_update_user_meta', array( $this, 'update_customer' ), 10, 2 );
		}
	}


	/**
	 * Creates user on Sugester account.
	 *
	 * Assigned to 'user_register' hook.
	 *
	 * @since 1.0.2 Added condition to check if user is a customer
	 * @param int $user_id New customer's ID.
	 */
	public function create_user($user_id) {
		if ( $this->tools->is_customer( $user_id ) ) {
			$client_data = $this->tools->get_user_meta( $user_id );
			$this->tools->create_sugester_client( $user_id, $client_data, true );
		}
	}


	/**
	 * Creates customer on Sugester account.
	 *
	 * Assigned to 'woocommerce_created_customer' hook.
	 *
	 * @since 1.0.0
	 * @param int $customer_id New customer's ID.
	 * @param array $new_customer_data Data for creation form
	 * @param string $password_generated Generated password upon creation.
	 *                                   Unused by us.
	 */
	/*public function create_customer($customer_id, $new_customer_data, $password_generated) {
		sugester_log("woocommerce customer");
		$external_client_id = $this->db->get_client_id( $customer_id );
		if ( empty( $external_client_id ) ) {
			$user_login = ( empty($new_customer_data['user_login']) ? null : $new_customer_data['user_login'] );
			$user_email = ( empty($new_customer_data['user_email']) ? null : $new_customer_data['user_email'] );

			$client_data = array(
				'name'  => $user_login,
				'email' => $user_email,
			);

			$success = $this->tools->create_sugester_client( $customer_id, $client_data, true );
		}
	}*/


	/**
	 * Updates customer data on your Sugester account.
	 *
	 * Assigned to 'woocommerce_checkout_update_user_meta' hook.
	 *
	 * @param int $customer_id WooCommerce customer ID.
	 * @param array $userdata Associative array with order data.
	 * @since 1.0.0
	 */
	public function update_customer($customer_id, $userdata) {
		$external_client_id = $this->db->get_client_id( $customer_id );

		// if external client id is not set, then we had some kind of an error.
		// ie. something failed and that id is 0. We'll have to handle it later
		// so, TODO
		if ( $external_client_id ) {
			$url = $this->tools->get_client_url_json($external_client_id);
			$data = Array(
				'api_token' => $this->c->api_token,
				'client' => Array(
					'first_name'     => $userdata['billing_first_name'],
					'last_name'      => $userdata['billing_last_name'],
					'email'          => $userdata['billing_email'],
					'phone'          => $userdata['billing_phone'],
					'street'         => $userdata['billing_address_1'] . ' ' . $userdata['billing_address_2'],
					'post_code'      => $userdata['billing_postcode'],
					'city'           => $userdata['billing_city'],
					'country'        => $userdata['billing_country'],
				),
			);
			$response = $this->tools->make_request( $url, 'PUT', $data );
		}
	}

}

$WC_Sugester_Hook_Clients = new WC_Sugester_Hook_Clients();

endif;