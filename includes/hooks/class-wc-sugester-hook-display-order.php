<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WC_Sugester_Hook_Display_Order' ) ) :

/**
 * WC Sugester Hook Display Order class.
 *
 * @package  WC_Sugester_Hooks
 * @category Display
 * @author   Sugester
 * @since 1.0.0
 */
class WC_Sugester_Hook_Display_Order extends WC_Sugester_Hook_Core {

	/**
	 * Initializes hooks from WC_Sugester_Hook_Client_Create class.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct();

		// If fatal error is present, will not add hooks.
		if ( ! $this->db->fatal_present() && $this->tools->is_configured() ) {
			// Actions
			add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ));
		}
	}


	/**
	 * Adds meta box to View Order page.
	 *
	 * @since 1.0.0
	 */
	public function add_meta_box() {
		add_meta_box(
			'sugester_order',
			$this->l('plugin_info.title'),
			array( $this, 'fill_meta_box' ),
			'shop_order',
			'side',
			'core',
			10
		);
	}


	/**
	 * Fills meta box with data
	 * @since 1.0.0
	 */
	public function fill_meta_box() {
		global $post;
		$order = new WC_Order( $post->ID );
		$user_id = $order->get_user_id();

		if ( empty( $user_id ) ) {
			echo '<strong>'.$this->l('order.guest').'</strong><br/>';
		}
		else {
			$this->tools->generate_deals_by_client_url( $user_id, null, true );
			echo "<br/><br/>";
			$this->tools->generate_sugester_url( $user_id, 'order', null, true );
			echo "<br/><br/>"; // TODO: zmienic separator w tym miejscu
		}
		$this->tools->generate_deal_button( $post->ID, true );
	}

}

$WC_Sugester_Hook_Display_Order = new WC_Sugester_Hook_Display_Order();

endif;