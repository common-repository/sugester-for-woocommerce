<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WC_Sugester_Ajax_Orders' ) ) :

/**
 * WC Sugester Ajax Orders class.
 *
 * Used for communication between Shop server and Client.
 *
 * @package  WC_Sugester_Ajax
 * @category Ajax
 * @author   Sugester
 * @since 1.0.0
 */
class WC_Sugester_Ajax_Orders extends WC_Sugester_Hook_Core {

	/**
	 * Initializes hooks from WC_Sugester_Ajax_Orders class.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct();

		// If fatal error is present, will not add hooks.
		if ( ! $this->db->fatal_present() && $this->tools->is_configured() ) {
			// callbacks
			add_action( 'wp_ajax_sugester_create_order', array( $this, 'create_order_callback' ) );
			add_action( 'wp_ajax_sugester_create_all_orders', array( $this, 'create_all_orders_callback' ) );
		}
	}


	/**
	 * Tries to create an Order on Sugester
	 *
	 * @since 1.0.0
	 * @param int $id Order ID
	 * @return array Success and data from the server.
	 */
	private function try_to_create_order($id) {
		$external_order_id = $this->db->get_order_id( $id );
		if ( $external_order_id === false ) {
			$success = false;
			$data = array( 'msg' => $this->l('db.error') );
		}
		elseif ( ! empty( $external_order_id ) ) {
			$success = false;
			$data = array(
				'msg' => $this->l('order.already_created'),
				'created' => true
			);
		}
		else {
			assert( $external_order_id === 0 || is_null( $external_order_id ) );
			$response = $this->tools->create_deal_from_order( $id );
			$success = ! empty( $response );

			if ( $response === false ) {
				$data = array(
					'msg' => sprintf(
						$this->l('deal.error.create'),
						$this->tools->get_order_title( $id )
					)
				);
			}
			elseif ( empty($response) ) {
				$data = array( 'msg' => $this->l('common.server_down') );
			}
			else {
				$data = array(
					'url'    => $this->tools->get_deal_url( $response->id ),
					'rename' => $this->l('deal.button.view'),
					'msg'    => sprintf(
						$this->l('order.success'),
						$this->tools->get_order_title( $id )
					)
				);
			}
		}

		return array(
			'success' => $success,
			'data'    => $data
		);
	}


	/**
	 * Creates an order on Sugester from AJAX request.
	 *
	 * @since 1.0.0
	 * @param int $order_id Order ID
	 * @return mixed Success notice and external order id.
	 */
	public function create_order_callback() {
		$order_id = (int) $_POST['order_id'];
		$data = $this->try_to_create_order( $order_id );
		echo wp_send_json( $data );
		wp_die();
	}


	/**
	 * Callback function that will create all orders made by customer, on his
	 * Sugester account (as a client).
	 *
	 * @since 1.0.0
	 */
	public function create_all_orders_callback() {
		// Getting all order ids
		$order_ids = $this->tools->get_all_order_ids();

		$deal_success = 0;
		$deal_created = 0;
		$deal_failed  = 0;

		/*$client_success = 0;
		$client_created = 0;
		$client_failed = 0;*/

		foreach ( $order_ids as $id ) {
			$data = $this->try_to_create_order( $id );
			if ( $data['success'] )
				$deal_success++;
			elseif ( isset($data['data']['created']) && $data['data']['created'] )
				$deal_created++;
			else
				$deal_failed++;
		}

		// Callback message to be displayed
		$data = array(
			'msg' => sprintf(
				$this->l('deals.create_all_success'), // takes 3 numbers
				$deal_success,
				$deal_failed,
				$deal_created
			)
		);

		if ( $deal_failed > 0 )
			echo wp_send_json_error( $data );
		else
			echo wp_send_json_success( $data );

		wp_die();
	}

}

$WC_Sugester_Ajax_Orders = new WC_Sugester_Ajax_Orders();

endif;