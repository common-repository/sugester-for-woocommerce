<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WC_Sugester_Translation' ) ) :

/**
 * WC Sugester Translation.
 *
 * Class that will provide translations to javascript files
 * identified by a shortcode.
 *
 * To use translations, you'll have to define $this->l function that will run
 * this class's l function.
 * Use & WC_Sugesterr_Translation::get_instance() to get translations.
 *
 * @todo Kompilator YAML -> PHP? w ten sposob bedziemy miec tlumaczenia w YAMLu
 * @package  WC_Sugester_Miscellaneous
 * @category Miscellaneous
 * @author   Sugester
 */
class WC_Sugester_Translation {

	/**
	 * Instance to be returned in $this->get_instance()
	 *
	 * Contains WC_Sugester_Translation instance
	 *
	 * @since 1.0.0
	 * @var object WC_Sugester_Translation instance
	 */
	private static $instance;


	/**
	 * Translations that will be used by Sugester Plugin.
	 *
	 * This has to be initialized by init().
	 *
	 * @since 1.0.0
	 * @var array Translations
	 */
	private static $translations;


	/**
	 * Returns a reference to static translation in order not to copy
	 * this class whenever this is called.
	 *
	 * @note Calling this function should be done by " & self::get_instance() "
	 * @since 1.0.0
	 * @return object This class
	 */
	public static function &get_instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();
			self::init();
		}
		return self::$instance;
	}


	/**
	 * Applies translations to self::$translations.
	 *
	 * It has to be run before using $this->l.
	 *
	 * @since 1.0.0
	 */
	public static function init() {
		self::$translations = array(
			'plugin_info' => array(
				'title' => __('Sugester', 'sugester-for-woocommerce'),
				'description' => __('Sugester for WooCommerce is an integration plugin that will integrate your WooCommerce shop with Sugester.', 'sugester-for-woocommerce'),
			),

			'common' => array(
				'server_down' => __('Sugester servers are down. Please try again later.', 'sugester-for-woocommerce'),
				'is_not_configured' => __('Sugester for WooCommerce: Plugin is not configured. Configure it at: WooCommerce -> Settings -> Integration -> Sugester', 'sugester-for-woocommerce'),
			),

			'container' => array(
				'error' => array(
					'constructor' => array(
						'unknown_type' => __('Container constructor was called with unknown type: %s', 'sugester-for-woocommerce'),
					)
				)
			),


			'db' => array(
				'error' => array(
					'' => __('Sugester database error.', 'sugester-for-woocommerce'),
					'prefix' => __('Sugester:', 'sugester-for-woocommerce'),
					'fatal' => __('Fatal database error is present.', 'sugester-for-woocommerce'),
					'table_not_exists' => __("Table '%s' does not exist in the database. This means that it was removed manually. Inform Sugester to solve this problem.", 'sugester-for-woocommerce'),
				),
			),

			'settings' => array(
				'button' => array(
					'create_all_clients' => __('Create all customers on your Sugester account', 'sugester-for-woocommerce'),
					'create_all_orders' => __('Create all orders on your Sugester account', 'sugester-for-woocommerce'),
				),
				'validate' => array(
					'api_token' => array(
						'empty' => __('Your Sugester API Token is not set. You can get your API Token at “https://your_account.sugester.com/app/account/api”. Should you have any issues, please contact us.', 'sugester-for-woocommerce'),
					),
				),
				'field' => array(
					'api_token' => array(
						'title' => __('API Token', 'sugester-for-woocommerce'),
						'label' => __('Your Sugester account API Token', 'sugester-for-woocommerce'),
						'description' => __('Enter with your API Key. You can find this in "User Profile" drop-down (top right corner) > API Keys.', 'sugester-for-woocommerce'),
					),
				),
				'test' => array(
					'integration' => array(
						'error' => array(
							'empty_response' => __('Sugester server has returned an empty response. This probably means that our servers are down at this moment and we are doing our best to fix this issue. Please, try again later or contact us.', 'sugester-for-woocommerce' ),
							'is_everything_correct' => __('Are you sure that everything is set up correctly?', 'sugester-for-woocommerce'),
						),
						'connect' => array(
							'' => __('Connection Test 1 (checking the API Token) has failed.', 'sugester-for-woocommerce'),
						),
						'client' => array(
							'create' => array(
								'' => __('Connection Test 2 (creating a test client on your Sugester account) has failed.', 'sugester-for-woocommerce'),
								'empty_id' => __("Sugester server has returned a response. However, created client's id is empty. Please inform us about this issue immediately.", 'sugester-for-woocommerce'),
							),
							'modify' => array(
								'' => __('Connection Test 3 (modifying created client on Sugester) has failed.', 'sugester-for-woocommerce'),
								'different_data_response' => __('Sugester server has returned data different from what was sent by WooCommerce. This should not have happened. Please, inform us about this issue immediately.', 'sugester-for-woocommerce'),
							),
							'delete' => array(
								'' => __('Connection Test 4 (deleting created client on Sugester) has failed.', 'sugester-for-woocommerce'),
								'unknown_response' => __('Sugester server has returned a response, but it is different from expected one. Please, inform us about this issue immediately.', 'sugester-for-woocommerce'),
							),
						),
					),
				),
			),


			'client' => array(
				'' => __('Client', 'sugester-for-woocommerce'),
				'already_created' => __('This customer was already created.', 'sugester-for-woocommerce'),
				'success' => __("Successfully created customer '%s'.", 'sugester-for-woocommerce'),
				'error' => array(
					'empty_response' => __("Empty response: '%s'", 'sugester-for-woocommerce'),
					'create' => __("Failed to create client '%s' on Sugester.", 'sugester-for-woocommerce'),
				),
				'button' => array(
					'view' => array(
						'user' => __('Show user on Sugester', 'sugester-for-woocommerce'),
						'users' => __('Sugester', 'sugester-for-woocommerce'),
						'order' => __('Show user on Sugester', 'sugester-for-woocommerce'),
					),
					'add' => array(
						'user' => __('This user is not on Sugester yet. Press here to create one.', 'sugester-for-woocommerce'),
						'users' => __('Sugester+', 'sugester-for-woocommerce'),
						'order' => __('Create this user on Sugester', 'sugester-for-woocommerce'),
					),
				),
			),

			'clients' => array(
				'button' => array(
					'show_all' => __('Show all clients on Sugester', 'sugester-for-woocommerce'),
				),
				'create_all_success' => __('Successfully created %1$d customers.<br/>Failed to create %2$d customers.<br/>There were %3$d customers created before this operation.', 'sugester-for-woocommerce'),
			),

			// todo: zamienic deal na order?
			'deal' => array(
				'error' => array(
					'create' => __("Failed to create order '#%s' on Sugester.", 'sugester-for-woocommerce'),
				),
				'button' => array(
					'view' => array(
						'' => __('Show this order on Sugester.', 'sugester-for-woocommerce'),
					),
					'add' => array(
						'' => __('Generate deal from this order.', 'sugester-for-woocommerce'),
					),
				),
			),
			'deals' => array(
				'' => __('Deals', 'sugester-for-woocommerce'),
				'button' => array(
					'show_all' => __('Show all orders on Sugester', 'sugester-for-woocommerce'),
					'show_by_client' => __('Show orders created by this client.', 'sugester-for-woocommerce'),
					'client_not_created' => __('This client was not created yet. Please create it.', 'sugester-for-woocommerce'),
				),
				'create_all_success' => __('Successfully created %1$d deals.<br/>Failed to create %2$d deals.<br/>There were %3$d deals created before this operation.', 'sugester-for-woocommerce'),
			),

			'order' => array(
				'' => __('Order', 'sugester-for-woocommerce'),
				'id' => __('Order #%d', 'sugester-for-woocommerce'),
				'already_created' => __('This order was already created.', 'sugester-for-woocommerce'),
				'success' => __("Successfully created order '%s'.", 'sugester-for-woocommerce'),
				'guest' => __('*Guest*', 'sugester-for-woocommerce'),
				'created_by_guest' => __('This order was created by a Guest', 'sugester-for-woocommerce'),
			),

			'orders' => array(
				'create_all_success' => __('Successfully created %1$d orders.<br/>Failed to create %2$d orders.<br/>There were %3$d orders created before this operation.', 'sugester-for-woocommerce'),
			),
		);
	}

	/**
	 * Returns the translations for javascript files.
	 *
	 * @since 1.0.0
	 * @return array Translations.
	 */
	public static function &get_translations_js() {
		/**
		 * Contains all translations that will be passed to javascript files.
		 * To search where it is used, find SUGESTER_T.{$key}
		 *
		 * @todo cannot declare it as static, do something about it
		 * @since 1.0.0
		 * @var array
		 */
		$translations = array(
			'display_in_progress' => __( 'Process is already in progress.', 'sugester-for-woocommerce'),
			'display_started'     => __( 'Process started.', 'sugester-for-woocommerce' ),
			'display_error'       => __( 'There was an error. Contact Sugester to resolve this issue.', 'sugester-for-woocommerce' ),
		);

		return $translations;
	}


	/**
	 * Returns translation of an identifier that is specified in translations.
	 *
	 * @since 1.0.0
	 * @return string Translated string.
	 */
	public function l( $msg ) {
		$path = explode( '.', $msg );
		$code = "self::\$translations['" . implode( "']['", $path ) . "']";

		eval( "\$exists = ! empty({$code});" );

		if ( $exists ) {
			eval( "\$type = gettype({$code});" );
			eval( "\$tmp = {$code};" );
			if ( $type === "string" )
				$translation = $tmp;
			elseif ( $type === "array" ) {
				if ( !empty( $tmp[''] ) ) // default translation
					$translation = $tmp[''];
				else
					$translation = "default_translation_missing: '{$msg}'.";
			}
			else {
				$translation = "path_evaluates_to_empty: '{$msg}'";
			}
		}
		else
			$translation = "translation_missing: '{$msg}'";

		return $translation;
	}

}

endif;