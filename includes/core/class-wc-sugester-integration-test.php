<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WC_Sugester_Test' ) ) :

/**
 * WC Sugester Integration Test.
 *
 * Contains integration tests to check whether API settings are correct or not
 *
 * @package  WC_Sugester_Core
 * @category Integration Core
 * @author   Sugester
 * @since 1.0.0
 * @todo Translation prefix
 */
class WC_Sugester_Integration_Test {


	/**
	 * Constructs tests class
	 *
	 * @since 1.0.0
	 * @param WC_Sugester_Container $container Container with all settings
	 */
	public function __construct( $container) {
		$this->c = $container;
		$this->tools = new WC_Sugester_Tools( $this->c, false );
		$this->errors = array();
	}


	/**
	 * Uses translation->l function to translate the identifier.
	 *
	 * @since 1.0.0
	 * @param string $msg Translation identfiier
	 * @return string Matched translation.
	 */
	public function l( $msg ) {
		return $this->tools->l( $msg );
	}


	/**
	 * Runs all required integration tests.
	 * Should any test fail, an error will be added to $this->errors
	 *
	 * @since 1.0.0
	 * @return array error messages
	 */
	public function run_integration_tests() {
		if ( empty( $this->errors ) )
			$this->test_integration_connect();

		// Testing client creation/modification/delete
		if ( empty( $this->errors ) )
			$this->test_integration_create_client();
		if ( empty( $this->errors ) )
			$this->test_integration_modify_client();
		if ( empty( $this->errors ) )
			$this->test_integration_delete_client();

		return $this->errors;
	}


	/**
	 * Performs an integration test with Sugester
	 *
	 * Should it fail, an error will be added to $this->errors
	 *
	 * @since 1.0.0
	 */
	private function test_integration_connect() {
		$url = $this->tools->get_clients_url_json();
		$data = Array(
			'api_token' => $this->c->api_token,
		);
		$response = $this->tools->make_request( $url, 'GET', $data );

		if ( is_null($response) ) {
			$msg = $this->l('settings.test.integration.connect');
			$msg .= ' ' . $this->l('settings.test.integration.error.is_everything_correct');
			$this->errors[] = $msg;
		}
	}


	/**
	 * Performs an integration test with Sugester:
	 * Tries to create a client on Sugester account and on success,
	 * will add sugester client id to $this->sugester_client_id
	 * for further tests
	 *
	 * Should it fail, an error will be added to $this->errors
	 *
	 * @see self::test_integration_modify_client
	 * @see self::test_integration_delete_client
	 * @since 1.0.0
	 */
	private function test_integration_create_client() {
		$url = $this->tools->get_clients_url_json();
		$data = Array(
			'api_token' => $this->c->api_token,
			'client' => Array(
				'name'  => 'testy_name',
				'email' => 'testy@email.dev',
			),
		);
		$response = $this->tools->make_request( $url, 'POST', $data );

		$error_prefix = $this->l('settings.test.integration.client.create');
		if ( empty($response) ) {
			// Server is down
			$error = $error_prefix . ' ';
			$error .= $this->l('settings.test.integration.error.empty_response');
			$this->errors[] = $error;
		}
		else if ( empty($response->id) ) {
			// Weird issue, but it may come in handy if a bug appears in Sugester
			$error = $error_prefix . ' ';
			$error .= $this->l('settings.test.integration.client.create.empty_id');
			$this->errors[] = $error;
		}
		else {
			// everything is ok
			// assigning client id for further tests
			$this->sugester_client_id = $response->id;
		}
	}


	/**
	 * Performs an integration test with Sugester
	 * Modifies created client.
	 *
	 * @see self::test_integration_create_client
	 * @see self::test_integration_delete_client
	 * @return bool success?
	 * @since 1.0.0
	 */
	private function test_integration_modify_client() {
		assert( ! empty( $this->sugester_client_id ));
		$url = $this->tools->get_client_url_json( $this->sugester_client_id );
		$data = Array(
			'api_token' => $this->c->api_token,
			'client' => Array(
				'first_name' => 'sugester_testy_first_name',
				'last_name' => 'sugester_testy_last_name',
			),
		);
		$response = $this->tools->make_request( $url, 'PUT', $data );

		$error_prefix = $this->l('settings.test.integration.client.modify');
		if ( empty($response) ) {
			// server is down
			$error = $error_prefix . ' ';
			$error .= $this->l('settings.test.integration.error.empty_response');
			$this->errors[] = $error;
		}
		else if ( empty($response->first_name) || empty($response->last_name) ) {
			// Server returned a response but it is different from expected.
			$error = $error_prefix . ' ';
			$error .= $this->l('settings.test.integration.client.modify.different_data_response');
			$this->errors[] = $error;
		}
	}


	/**
	 * Deletes client from Sugester Account
	 *
	 * @see $this->test_integration_create_client
	 * @see $this->test_integration_modify_client
	 * @since 1.0.0
	 */
	private function test_integration_delete_client() {
		assert( ! empty( $this->sugester_client_id ));
		$url = $this->tools->get_client_url_json( $this->sugester_client_id );
		$data = Array(
			'api_token' => $this->c->api_token,
		);
		$response = $this->tools->make_request( $url, 'DELETE', $data );

		$error_prefix = $this->l('settings.test.integration.client.delete');
		if ( empty($response) ) {
			// Server is down and failed to delete client.
			$error = $error_prefix . ' ';
			$error .= $this->l('settings.test.integration.error.empty_response');
			$this->errors[] = $error;
		}
		else if ( $response !== 'ok' ) {
			// Sugester response is different from 'ok'. Bug prevention test on Sugester
			$error = $error_prefix . ' ';
			$error .= $this->l('settings.test.integration.client.delete.unknown_response');
			$this->errors[] = $error;
		}
	}
}

endif;
