<?php

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WC_Sugester_Integration' ) ) :

/**
 * WC Sugester Integration.
 *
 * This class is the core of the plugin, as every required setting is
 * available only from this class, therefore it is used in other classes.
 *
 * Should you want to add any settings validation, follow the convention
 * for '$this->validate_api_token_field' and replace "api_token" with your field.
 * Remember to add your field to "$this->form_fields".
 *
 * @package  WC_Sugester
 * @category Integration Core
 * @author   Sugester
 *
 * @todo ONCE IT IS POSSIBLE:
 *          save data ONLY when it gets validated by integration tests
 *          and display POST settings on reload instead of previous.
 */
class WC_Sugester_Integration extends WC_Integration {

	/**
	 * Instance to be returned in self::get_instance
	 * @var object WC_Sugester_Integration instance
	 */
	private static $instance;


	/**
	 * Init and hook in the integration.
	 * @param bool $add_hooks Set it to false, if you do not want any hooks.
	 */
	public function __construct( $add_hooks = true ) {
		// Setting ID for init settings
		$this->id = 'sugester';

		// Load the settings.
		$this->init_settings();

		// Define user set variables.
		$this->c = new WC_Sugester_Container( $this );
		$this->tools = new WC_Sugester_Tools( $this->c, false );

		// Translations:
		$this->translation = & WC_Sugester_Translation::get_instance();

		// Integration info to be displayed in settings.
		$this->method_title       = $this->translation->l('plugin_info.title');
		$this->method_description = $this->translation->l('plugin_info.description');

		// Load fields
		$this->init_form_fields();

		if ( $add_hooks ) {
			// Actions.
			add_action( 'woocommerce_update_options_integration_' .  $this->id, array( $this, 'process_admin_options' ) );

			// Filters.
			add_filter( 'woocommerce_settings_api_sanitized_fields_' . $this->id, array( $this, 'sanitize_settings' ) );
		}
	}

	/**
	 * Uses translation->l function to translate the identifier.
	 *
	 * @since 1.0.0
	 * @param string $msg Translation identfiier
	 * @return string Matched translation.
	 */
	public function l($msg) {
		return $this->translation->l( $msg );
	}


	/**
	 * Returns a reference to static tools in order not to copy
	 * this class whenever this is called.
	 *
	 * @note Calling this function should be done by " & self::get_instance() "
	 * @since 1.0.0
	 * @return object This class
	 */
	public static function &get_instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self( false );
		}
		return self::$instance;
	}




	/**
	 * Initialize integration settings form fields.
	 *
	 * @since 1.0.0
	 */
	public function init_form_fields() {
		$p = 'settings.field.'; // translation prefix
		$this->form_fields = array(
			'api_token' => array(
				'title'             => $this->l($p.'api_token.title'),
				'label'             => $this->l($p.'api_token.label'),
				'description'       => $this->l($p.'api_token.description'),
				'type'              => 'text',
				'default'           => '',
				'desc_tip'          => true,
			),
		);
	}


	/**
	 * Outputs admin options
	 *
	 * @note this method overrides parent, just to output the
	 *       "Create all clients link". If unnecessary, remove it whole.
	 * @since 1.0.0
	 */
	public function admin_options() {
		echo '<h2>' . esc_html( $this->get_method_title() ) . '</h2>';
		echo wp_kses_post( wpautop( $this->get_method_description() ) );

		if ( $this->tools->is_configured() ) {
			// Show all deals
			$this->tools->generate_deals_url( null, true );
			echo '<br/><br/>';

			// Show all clients
			$this->tools->generate_clients_url( null, true );
			echo '<br/><br/>';

			// Create all clients on Sugester.
			echo '<a class="button hide-if-no-js" onclick="sugester_create_all_clients()">' .
				$this->l('settings.button.create_all_clients') .
			'</a>';

			echo '<br/><br/>';
			// Create all orders on Sugester.
			// NOTE: this will add clients as well.
			echo '<a class="button hide-if-no-js" onclick="sugester_create_all_orders()">' .
				$this->l('settings.button.create_all_orders') .
			'</a>';
		}

		echo '<div><input type="hidden" name="section" value="' . esc_attr( $this->id ) . '" /></div>';
		echo '<table class="form-table">' . $this->generate_settings_html( $this->get_form_fields(), false ) . '</table>';
	}


	/**
	 * Runs integration test on WC_Sugester_Integration_Test class
	 *
	 * @since 1.0.0
	 * @param array $settings Posted data to run integration tests on
	 * @return bool no errors?
	 */
	private function run_integration_tests( $settings ) {
		$test_container = new WC_Sugester_Container( $settings );
		$test_interface = new WC_Sugester_Integration_Test( $test_container );
		$errors = array();

		if ( $this->c->api_token !== $test_container->api_token )
		{
			$errors = $test_interface->run_integration_tests();
			foreach ( $errors as $err )
				$this->errors[] = $err;
		}
		return empty( $errors );
	}


	/**
	 * Sanitize our settings
	 * @see process_admin_options()
	 * @param array $settings POST settings from form
	 * @return array $settings to save
	 */
	public function sanitize_settings( $settings ) {
		$errors = $this->get_errors(); // PHP 5.2 compatibility

		if ( empty( $errors ) && $this->run_integration_tests( $settings ) ) {
			// Redefinition
			$this->c = new WC_Sugester_Container( $settings );
			$this->tools = new WC_Sugester_Tools( $this->c, true );
			$this->tools->update_configured( true );
			// We update statuses, and update all orders
			$this->tools->update_sugester_statuses();
		}
		else {
			$this->tools->update_configured( false );
			// WooCommerce will save data anyway, we have to perform a rollback
			// in order not to override previous settings
			$rollback_array = $this->c->as_array();
			$settings_keys = array_keys( $settings );

			foreach ( $settings_keys as $key ) {
				$settings[ $key ] = $rollback_array[ $key ];
			}
		}

		// TODO: in further version, replace display_error somehow. Rgiht now
		// TODO: test it on previous versions up to 2.2
		foreach ( $this->get_errors() as $err )
			WC_Admin_Settings::add_error( $err );

		return $settings;
	}


	/**
	 * Validate the API token
	 *
	 * @since 1.0.0
	 * @todo once it is possible to save errors ONLY when validate
	 *       then just add error. WooCommerce should have implemented it
	 *       but it did not, which is pathetic.
	 * @see validate_settings_fields()
	 * @param string $key 'api_token'
	 * @return string API_Token to be saved.
	 */
	public function validate_api_token_field( $key ) {
		// get the posted value
		$api_token = trim( $_POST[ $this->plugin_id . $this->id . '_' . $key ] );

		if ( empty($api_token) ) {
			$this->errors[] = $this->l('settings.validate.api_token.empty');
			return $this->c->api_token;
		}

		return $api_token;
	}
}

endif;