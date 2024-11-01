<?php
/**
 * Plugin Name: 	Sugester for WooCommerce
 * Plugin URI:  	http://sugester.com/
 * Description: 	Integrates Sugester with WooCommerce
 * Version:     	1.0.9
 * Author:      	Sugester
 * Author URI:  	http://sugester.com/
 * Text Domain: 	sugester-for-woocommerce
 * Domain Path: 	/languages
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WC_Sugester' ) ) :

define ( 'SUGESTER_WC_DEBUG', false );
define ( 'SUGESTER_WC_DEBUG_LOG', false);


/**
 * Main class of the plugin
 */
class WC_Sugester {

	/**
	 * Plugin version
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $version = '1.0.9';


	/**
	 * Instance to be returned in $this->get_instance()
	 *
	 * Contains WC_Sugester class instance
	 *
	 * @since 1.0.2
	 * @var object WC_Sugester instance
	 */
	private static $instance;


	/**
	 * Returns a reference to static database in order not to copy
	 * this class whenever this is called.
	 *
	 * @note Calling this function should be done by " & self::get_instance() "
	 * @since 1.0.2
	 * @return object This class
	 */
	public static function &get_instance() {
		if ( empty( self::$instance ) )
			self::$instance = new self();
		return self::$instance;
	}



	/**
	* Construct the plugin.
	*
	* @since 1.0.0
	*/
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'init' ) );
	}


	/**
	 * Add a new integration to WooCommerce.
	 *
	 * This plugin uses WC_Integration class by extending it and inheriting
	 * almost all required functions in WC_Sugester_Integration class
	 *
	 * @since 1.0.0
	 */
	public function add_integration( $integrations ) {
		$integrations[] = 'WC_Sugester_Integration';
		return $integrations;
	}


	/**
	 * Includes integration files and register the integration
	 *
	 * 1. Database class gets loaded to check if all required tables exist.
	 * 2. The core Integration classes, along with integration tests.
	 * 3. Hooks: they trigger database constructor.
	 *
	 * @since 1.0.0
	 */
	public function register_integration() {
		// Include database class
		include( 'includes/functions.php' );
		$this->include_database();
		$this->include_core();
		$this->include_miscellaneous();
		$this->include_hooks();

		// Register the integration.
		add_filter( 'woocommerce_integrations', array( $this, 'add_integration' ) );

		// Add javascript and css
		add_action( 'admin_enqueue_scripts', array( $this, 'include_css' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'include_js' ) );

		/**
		 * Runs upgrades if necessary
		 * @since 1.0.7
		 * @note To avoid bugs, it should be called after all post types
		 *       get registered by woocommerce, therefore we have to run it
		 *       after the registration
		 */
		add_action(
			'woocommerce_after_register_post_type',
			array( 'WC_Sugester_Database', 'try_upgrade' )
		);
	}


	/**
	 * Includes all classes for maintaining database.
	 *
	 * @note This method exists for future development.
	 *       In case someone decides to increase the size of database
	 *       classes greatly.
	 * @since 1.0.0
	 */
	private function include_database() {
		$p = 'includes/database/';
		include_once( $p . 'class-wc-sugester-database.php' );
	}


	/**
	 * Includes all core classes.
	 *
	 * @note All of these files declare classes.
	 *       They do not declare any variables.
	 * @since 1.0.0
	 */
	private function include_core() {
		$p = 'includes/core/';
		include_once( $p . 'class-wc-sugester-container.php' );
		include_once( $p . 'class-wc-sugester-tools.php' );
		include_once( $p . 'class-wc-sugester-integration-test.php' );
		include_once( $p . 'class-wc-sugester-integration.php' );
	}


	/**
	 * Includes all various classes, like translation.
	 *
	 * @since 1.0.0
	 */
	private function include_miscellaneous() {
		$p = 'includes/miscellaneous/';
		include_once( $p . 'class-wc-sugester-translation.php' );
	}


	/**
	 * Includes all classes with hooks
	 *
	 * @since 1.0.0
	 */
	private function include_hooks() {
		$p = 'includes/hooks/';
		include_once( $p . 'class-wc-sugester-hook-core.php' );
		include_once( $p . 'class-wc-sugester-hook-clients.php' );
		include_once( $p . 'class-wc-sugester-hook-orders.php' );

		// ajax functions
		include_once( $p . 'ajax-wc-sugester-hook-clients.php' );
		include_once( $p . 'ajax-wc-sugester-hook-orders.php' );

		// display hooks
		include_once( $p . 'class-wc-sugester-hook-display-order.php' );
		include_once( $p . 'class-wc-sugester-hook-display-user.php' );
		include_once( $p . 'class-wc-sugester-hook-display-users.php' );
	}


	/**
	 * Include all CSS files
	 *
	 * In order to add style, add line to $styles.
	 *
	 * @since 1.0.0
	 */
	public function include_css() {
		$styles = array(
			// IDENTIFIER   | FILENAME      | DEPENDENCIES
			'toastr' => array( 'toastr.min', array() ),
		);
		$css_url = plugins_url( '/assets/css/', __FILE__ );
		foreach ( array_keys($styles) as $id ) {
			$identifier = "sugester_woocommerce_{$id}";
			wp_register_style(
				$identifier,                           // IDENTIFIER
				$css_url . $styles[ $id ][0] . '.css', // FILENAME
				$styles[ $id ][1],                     // DEPENDENCIES
				'1.0.9'                          // STYLE VERSION
			);
			wp_enqueue_style( $identifier );
		}
	}


	/**
	 * Include all javascript files.
	 *
	 * In order to add script, add line to $scripts along with dependencies.
	 *
	 * @since 1.0.0
	 */
	public function include_js() {
		$scripts = array(
			// IDENTIFIER             | FILENAME        | DEPENDENCIES
			'toastr'         => array( 'toastr.min',    array( 'jquery' ) ),
			'core'           => array( 'core',          array() ),
			'ajax_clients'   => array( 'ajax_clients',  array( 'jquery' ) ),
			'ajax_orders'    => array( 'ajax_orders',   array( 'jquery' ) ),
		);
		$js_url = plugins_url( '/assets/js/', __FILE__ );
		$translations = & WC_Sugester_Translation::get_translations_js();

		foreach ( array_keys($scripts) as $id ) {
			$identifier = "sugester_woocommerce_{$id}";
			wp_register_script(
				$identifier,                           // IDENTIFIER
				$js_url . $scripts[ $id ][0] . '.js',  // FILENAME
				$scripts[ $id ][1],                    // DEPENDENCIES
				'1.0.9'                          // SCRIPT VERSION
			);

			wp_localize_script( $identifier, 'SUGESTER_T', $translations );

			wp_enqueue_script( $identifier );
		}
	}


	/**
	 * Load Localisation files.
	 *
	 * @todo document
	 */
	public function load_plugin_textdomain() {
		$domain = 'sugester-for-woocommerce';
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		// wp-content/languages/sugester-for-woocommerce/sugester-for-woocommerce-xy_XY.mo
		load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );

		// wp-content/plugins/sugester-for-woocommerce/languages/sugester-for-woocommerce-xy_XY.mo
		load_plugin_textdomain( $domain, FALSE, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}


	/**
	 * Initialize the plugin.
	 * @since 1.0.0
	 */
	public function init() {
		// Checks if WooCommerce is installed.
		if ( ! $this->meets_prerequisites() )
			return;

		// Setup localization
		$this->load_plugin_textdomain();

		// Register the integration
		$this->register_integration();
	}


	/**
	 * Checks if it is ok to run the Sugester plugin
	 *
	 * @return boolean true if it meets the prerequisites, otherwise false
	 */
	public function meets_prerequisites() {
		// Check if woocommerce plugin is active, by checking if
		// the woocommerce class that we will extend exists
		if ( ! class_exists( 'WC_Integration' )) {
			add_action( 'admin_notices', array( $this, 'woocommerce_missing_notice' ) );
			return false;
		}

		// Checks if woocommerce version is at least 3.0.0:
		global $woocommerce;
		if ( version_compare( WC_VERSION, '3.0.0', '<')) {
			add_action( 'admin_notices', array( $this, 'woocommerce_outdated_version' ) );
			return false;
		}

		return true;
	}


	/**
	 * WooCommerce not installed notice
	 *
	 * Added to 'admin_notices' hook
	 * @since 1.0.0
	 */
	public function woocommerce_missing_notice() {
		$class = 'notice error';
		// Out of TRANSLATIONS scope, so we have to translate it directly
		$message = __(
			'Sugester for WooCommerce plugin requires the WooCommerce plugin.',
			'sugester-for-woocommerce'
		);

		printf( '<div class="%s"><p>%s</p></div>', $class, $message );
	}

	/**
	 * WooCommerce is outdated. Should be at least >= 3.0.0
	 *
	 * Added to 'admin_notices' hook
	 * @since 1.0.7
	 */
	public function woocommerce_outdated_version() {
		$class = 'notice error';
		// Out of TRANSLATIONS scope, so we have to translate it directly
		$message = __(
			'Sugester for WooCommerce plugin requires WooCommerce version 3.0.0 or later. ' .
			'Please update WooCommerce in order for this plugin to work.',
			'sugester-for-woocommerce'
		);

		printf( '<div class="%s"><p>%s</p></div>', $class, $message );
	}

}

$WC_Sugester = new WC_Sugester();

endif;