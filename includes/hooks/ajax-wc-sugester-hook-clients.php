<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WC_Sugester_Ajax_Clients' ) ) :

/**
 * WC Sugester Ajax Clients class.
 *
 * Used for communication between Shop server and Client.
 *
 * @package  WC_Sugester_Ajax
 * @category Ajax
 * @author   Sugester
 * @since 1.0.0
 */
class WC_Sugester_Ajax_Clients extends WC_Sugester_Hook_Core {

	/**
	 * Initializes hooks from WC_Sugester_Hook_Client_Create class.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct();

		// If fatal error is present, will not add hooks.
		if ( ! $this->db->fatal_present() && $this->tools->is_configured() ) {
			// callbacks
			add_action( 'wp_ajax_sugester_create_client', array( $this, 'create_client_callback' ) );
			add_action( 'wp_ajax_sugester_create_all_clients', array( $this, 'create_all_clients_callback' ) );
		}
	}


	/**
	 * Tries to create a client on Sugester
	 *
	 * @since 1.0.0
	 * @param int $id Customer ID
	 * @param string $source Where was this function called (to return
	 *                       appropriate text).
	 *                       Default: ''
	 * @return array Success and data from the server.
	 */
	private function try_to_create_client($id, $source = '') {
		$id = (int) $id; // cast to int to avoid problems.
		$external_client_id = $this->db->get_client_id( $id );
		if ( $external_client_id === false ) {
			$success = false;
			$data = array( 'msg' => $this->l('db.error') );
		}
		elseif ( ! empty( $external_client_id ) ) {
			$success = false;
			$data = array(
				'msg' => $this->l('client.already_created'),
				'created' => true
			);
		}
		else {
			$client = $this->tools->get_user_meta( $id );
			$response = $this->tools->create_sugester_client( $id, $client );
			$success = ! empty( $response );

			if ( ! $success )
				$data = array( 'msg' => $this->l('common.server_down') );
			else
				$data = array(
					'url'    => $this->tools->get_client_url( $response->id ),
					'rename' => $this->l("client.button.view.{$source}"),
					'msg'    => sprintf(
						$this->l('client.success'),
						$client['name']
					)
				);
		}

		return array(
			'success' => $success,
			'data'    => $data
		);
	}


	/**
	 * Creates a client on Sugester from AJAX request.
	 *
	 * @since 1.0.0
	 * @param int $client_id Client ID
	 * @param string $source, from ['users', 'user', 'order'].
	 * @return mixed Success notice and external client id.
	 */
	public function create_client_callback() {
		$client_id = (int) $_POST['client_id'];
		$source = (string) $_POST['source'];

		$data = $this->try_to_create_client( $client_id, $source );

		echo wp_send_json( $data );

		wp_die();
	}


	/**
	 * Callback function that will create take all clients frmo the database
	 * and create a Sugester Client for each
	 *
	 * @since 1.0.0
	 */
	public function create_all_clients_callback() {
		// Getting all customer ids
		$customer_ids = $this->tools->get_all_customer_ids();

		$success = 0;
		$created = 0;
		$failed = 0;

		foreach ( $customer_ids as $id ) {
			$data = $this->try_to_create_client( $id );
			if ( $data['success'] )
				$success++;
			elseif ( isset($data['data']['created']) && $data['data']['created'] )
				$created++;
			else
				$failed++;
		}

		// Callback message to be displayed
		$data = array(
			'msg'    => sprintf(
				$this->l('clients.create_all_success'), // takes 3 numbers
				$success,
				$failed,
				$created
			)
		);

		if ( $failed > 0 )
			echo wp_send_json_error( $data );
		else
			echo wp_send_json_success( $data );

		wp_die();
	}

}

$WC_Sugester_Ajax_Clients = new WC_Sugester_Ajax_Clients();

endif;