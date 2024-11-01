<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WC_Sugester_Hook_Orders' ) ) :

/**
 * WC Sugester Hook Orders class.
 *
 * Used for communication wordpress <-> sugester.
 * Each hook has a one corresponding function.
 *
 * @package  WC_Sugester_Hooks
 * @category Integration Hooks
 * @author   Sugester
 * @since 1.0.0
 */
class WC_Sugester_Hook_Orders extends WC_Sugester_Hook_Core {

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
			add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'create_new_order' ), 10, 2 );


			add_action( 'woocommerce_order_status_changed', array( $this, 'change_order_status' ), 10, 4 );
		}
	}


	/**
	 * Creates new order from $order_id.
	 *
	 * Registered to 'woocommerce_new_order' hook.
	 *
	 * @since 1.0.0
	 * @param int $order_id Order ID
	 */
	public function create_new_order($order_id, $posted) {
		$this->tools->create_deal_from_order( $order_id );
	}


	/**
	 * Changes order status on sugester
	 *
	 * @since 1.0.6
	 * @param int $order_id
	 * @param string $from Previous status
	 * @param string $to   New status
	 * @param WC_Order $order WC_Order instance
	 */
	public function change_order_status($order_id, $from, $to, $order) {
		$statuses = WC_Sugester_Database::get_statuses();
		$sugester_status_id = $statuses[ 'wc-' . $to ];
		if ( empty( $sugester_status_id ) ) {
			return false;
		}
		$sugester_deal_id = $this->db->get_order_id( $order_id );
		$url = $this->tools->get_deal_url_json( $sugester_deal_id );
		$data = array(
			'api_token' => $this->c->api_token,
			'deal' => array(
				'status_id' => $sugester_status_id,
			),
		);
		$response = $this->tools->make_request( $url, 'PUT', $data );
	}

}

$WC_Sugester_Hook_Orders = new WC_Sugester_Hook_Orders();

endif;