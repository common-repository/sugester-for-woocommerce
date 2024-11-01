<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WC_Sugester_Hook_Display_User' ) ) :

/**
 * WC Sugester Hook Display User class.
 *
 * Displays data on User page.
 *
 * @package  WC_Sugester_Hooks
 * @category Display
 * @author   Sugester
 * @since 1.0.0
 */
class WC_Sugester_Hook_Display_User extends WC_Sugester_Hook_Core {

	/**
	 * Initializes hooks from WC_Sugester_Hook_User class.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct();

		// If fatal error is present, will not add hooks.
		if ( ! $this->db->fatal_present() && $this->tools->is_configured() ) {
			// Actions.
			add_action( 'show_user_profile', array( $this, 'add_sugester_meta_fields' ), 1 );
			add_action( 'edit_user_profile', array( $this, 'add_sugester_meta_fields' ), 1 );
		}
	}


	/**
	 * Adds Sugester fields to Edit User page.
	 *
	 * Echoes our settings to User.
	 *
	 * @note This function is added to two hooks ( the run like xor )
	 * @since 1.0.0
	 */
	public function add_sugester_meta_fields() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		global $user_id;
		$user = new WP_User( $user_id );
		if ( in_array( 'customer', $user->roles ) ) {
			?><h2><?php echo $this->l('plugin_info.title'); ?></h2>
			<table class="form-table">
				<tr>
					<th><?php echo $this->l('client') ?></th>
					<td><?php $this->tools->generate_sugester_url( $user_id, 'user', null, true ); ?></td>
				</tr><?php #TODO: tlumaczenie?>
				<tr>
					<th><?php echo $this->l('deals'); ?>
					<td><?php $this->tools->generate_deals_by_client_url( $user_id, null, true ); ?></td>
				</tr>
			</table><?
		}
	}

}

$WC_Sugester_Hook_Display_User = new WC_Sugester_Hook_Display_User();

endif;