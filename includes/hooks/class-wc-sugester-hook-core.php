<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WC_Sugester_Hook_Core' ) ) :

/**
 * WC Sugester Hook Core class.
 *
 * Core class for all hook classes. Provides variables (references):
 *    $this->db    - database class
 *    $this->tools - tools class
 *    $this->c     - container class
 *
 * Each section with it's hooks has corresponding class.
 * Each hooks has one corresponding function
 *
 * @package  WC_Sugester_Hooks
 * @category Integration Hooks
 * @author   Sugester
 * @since 1.0.0
 */
class WC_Sugester_Hook_Core {

	/**
	 * @var WC_Sugester_Database
	 * @since 1.0.0
	 */
	protected $db;


	/**
	 * @var WC_Sugester_Tools
	 * @since 1.0.0
	 */
	protected $tools;


	/**
	 * @var WC_Sugester_Container
	 * @since 1.0.0
	 */
	protected $c;


	/**
	 * @var array
	 * @since 1.0.0
	 */
	private $errors = array();


	/**
	 * Initializes hooks from WC_Sugester_Hook_Core class.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// Passing database/tools/container instances by reference
		$this->db    = & WC_Sugester_Database::get_instance();
		$this->tools = & WC_Sugester_Tools::get_instance_with_db();
		$this->c     = & $this->tools->c;
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
	 * Adds any error to $this->errors and returns a boolean: is it empty.
	 *
	 * @since 1.0.0
	 * @return bool Any errors?
	 */
	public function check_for_errors() {
		if ( ! class_exists( 'WC_Sugester_Database' ) )
			$this->errors[] = __CLASS__ . " should be loaded after DB class";

		if ( ! $this->tools->is_configured() )
			$this->errors[] = $this->l('common.is_not_configured');

		foreach ( $this->db->database_errors() as $err )
			$this->errors[] = $err;

		return ! empty( $this->errors );
	}


	/**
	 * Function hooked to 'admin_notices' and will display error to the user
	 * if there is any.
	 *
	 * @since 1.0.0
	 */
	public function display_error() {
		$class = 'updated notice is-dismissible';

		// Log to PHP Logger
		sugester_log( $this->errors, false );

		if ( $this->errors ) {
			printf(
				'<div class="%s"><p>%s</p></div>',
				$class,
				reset($this->errors) // first element access
			);
			//sugester_log( $this->errors );
		}
	}

}

// Check for errors and display them.
// Written here not to check for errors in every class.
// TODO: call errors from constructor ONLY inside core class
$WC_Sugester_Hook_Core = new WC_Sugester_Hook_Core( true );
if ( $WC_Sugester_Hook_Core->check_for_errors() ) {
	add_action( 'admin_notices', array( $WC_Sugester_Hook_Core, 'display_error' ) );
}

endif;