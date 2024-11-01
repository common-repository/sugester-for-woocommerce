<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WC_Sugester_Hook_Display_Users' ) ) :

/**
 * WC Sugester Hook Display Users class.
 *
 * Displays data on "Users" page.
 *
 * @package  WC_Sugester_Hooks
 * @category Display
 * @author   Sugester
 * @since 1.0.0
 */
class WC_Sugester_Hook_Display_Users extends WC_Sugester_Hook_Core {

	/**
	 * Initializes hooks from WC_Sugester_Hook_Users class.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct();

		// If fatal error is present, will not add hooks.
		if ( ! $this->db->fatal_present() && $this->tools->is_configured() ) {
			// Filters.
			add_filter( 'user_row_actions', array( $this, 'display_sugester_action' ), 10, 2 );
		}
	}


	/**
	 * Adds Sugester action to current row (user row)
	 *
	 * @since 1.0.0
	 * @param array $actions Row user actions.
	 * @param object $user_object WP_User object
	 * @return array Actions with our action.
	 */
	public function display_sugester_action( $actions, $user_object ) {
		if ( in_array( 'customer', $user_object->roles ) ) {
			// if user is a customer
			// TODO: get some method from WooCommerce to determine whether user is a customer or not.
			$actions['sugester'] = $this->tools->generate_sugester_url( $user_object->ID, 'users', '' );
		}

		return $actions;
	}

}

$WC_Sugester_Hook_Display_Users = new WC_Sugester_Hook_Display_Users();

endif;