<?php

if ( ( !defined('ABSPATH')) ) exit;

if ( ! class_exists( 'WC_Sugester_Database' ) ) :

/**
 * WC Sugester Database class.
 *
 * Class responsible for server-database management.
 *
 * Contains methods for adding/removing keys from database.
 * This allows the developer for easier maintenance of the code.
 *
 * @package  WC_Sugester_Database
 * @category Integration Database
 * @author   Sugester
 * @since 1.0.0
 */
class WC_Sugester_Database {

	/**
	 * Instance to be returned in $this->get_instance()
	 *
	 * Contains WC_Sugester_Database instance
	 *
	 * @since 1.0.0
	 * @var object WC_Sugester_Database instance
	 */
	private static $instance;


	/**
	 * Database tables.
	 * @since 1.0.0
	 * @var array
	 */
	private static $table_names = array(
		'clients', 'orders'
	);


	/**
	 * Bool indicating database failure like table not existing
	 * @since 1.0.0
	 * @var bool
	 */
	private static $fatal = false;


	/**
	 * Constructor
	 *
	 * If version differs from installed one, will run function
	 * to create tables.
	 *
	 * @param bool $with_upgrade Should system try to perform an upgrade?
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->tools       = & WC_Sugester_Tools::get_instance();
		$this->c           = & $this->tools->c;
		$this->translation = & WC_Sugester_Translation::get_instance();
	}

	/**
	 * Uses translation->l function to translate the identifier.
	 *
	 * @since 1.0.0
	 * @param string $msg Translation identfiier
	 * @return string Matched translation.
	 */
	public function l( $msg ) {
		return $this->translation->l( $msg );
	}


	/**
	 * Returns a reference to static database in order not to copy
	 * this class whenever this is called.
	 *
	 * @note Calling this function should be done by " & self::get_instance() "
	 * @since 1.0.0
	 * @return object This class
	 */
	public static function &get_instance() {
		if ( empty( self::$instance ) ) {
			// We do not want to run upgrade
			self::$instance = new self( false );
		}

		return self::$instance;
	}


	/**
	 * Tries to upgrade system if required
	 * @since 1.0.5
	 */
	public static function try_upgrade() {
		$version_key = 'woocommerce_sugester_version';
		$sugester = & WC_Sugester::get_instance();
		$installed_version = get_option( $version_key );
		$new_version = $sugester->version;

		if ( $installed_version === false ) {
			/**
			 * @since 1.0.3
			 * First time install version
			 */
			self::create_tables();
			update_option( $version_key, $new_version );
			$installed_version = "1.0.0";
		}

		if ( version_compare( $installed_version, $new_version, '<' ) ) {
			include_once('class-wc-sugester-upgrades.php');
			WC_Sugester_Upgrades::upgrade($installed_version, $new_version);
			update_option( $version_key, $new_version );
		}
	}


	/**
	 * Checks for any errors in database and returns them to caller.
	 *
	 * @since 1.0.0
	 * @return array Generated errors
	 */
	public function database_errors() {
		$errors = array();
		foreach ( self::$table_names as $name ) {
			$table_name = $this->prefix( $name );

			if ( ! $this->table_exists( $table_name ) ) {
				self::$fatal = true;
				$errors[] = sprintf(
					$this->l('db.error.prefix').' '.$this->l('db.error.table_not_exists'),
					$table_name
				);
			}
		}
		return $errors;
	}


	/**
	 * Returns whether fatal error is present or not
	 *
	 * @todo: zmienic nazwe
	 * @since 1.0.0
	 * @return bool Is fatal error present?
	 */
	public function fatal_present() {
		return self::$fatal;
	}


	/**
	 * Provides a database table name
	 *
	 * @since 1.0.0
	 * @param string $name Required table after prefix
	 * @return string wp_sugester_$name
	 */
	private static function prefix($name = '') {
		global $wpdb;
		return $wpdb->prefix . 'sugester_woocommerce_' . $name;
	}



	/**
	 * Returns clients table
	 *
	 * @since 1.0.0
	 * @see $this->prefix
	 * @return string wp_sugester_clients
	 */
	public static function get_clients_table() {
		return self::prefix('clients');
	}


	/**
	 * Returns orders table
	 *
	 * @since 1.0.0
	 * @see $this->prefix
	 * @return string wp_sugester_orders
	 */
	public static function get_orders_table() {
		return self::prefix('orders');
	}


	/**
	 * Checks whether table named $table_name exist in the database or not
	 *
	 * @since 1.0.0
	 * @param string $table_name Table to check if exists
	 * @return bool true if table exists else false
	 */
	public function table_exists($table_name) {
		global $wpdb;
		return ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name);
	}


	/**
	 * Creates tables used by Sugester plugin
	 *
	 * Uses dbDelta provided by WordPress to create/update database with ease.
	 *
	 * @since 1.0.0
	 */
	private static function create_tables() {
		global $wpdb;

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$charset_collate = $wpdb->get_charset_collate();

		// CLIENTS
		$table_name = self::get_clients_table();
		$sql = "CREATE TABLE $table_name (
			client_id           INT NOT NULL,
			external_client_id  INT NOT NULL,
			PRIMARY KEY(client_id),
			UNIQUE (client_id)
		) $charset_collate;";
		dbDelta( $sql );

		// ORDERS
		$table_name = self::get_orders_table();
		$sql = "CREATE TABLE $table_name (
			order_id           INT NOT NULL,
			external_order_id  INT NOT NULL,
			PRIMARY KEY(order_id),
			UNIQUE (order_id)
		) $charset_collate;";
		dbDelta( $sql );
	}


	/**
	 * Inserts fields id->external_id relationship to database.
	 *
	 * @note This will override external_id that was previously set on pair.
	 *
	 * @since 1.0.0
	 * @todo update if exists
	 * @param int $id WooCommerce client ID
	 * @param int $external_id Sugester Client ID
	 * @param string $table_name Table name to insert into
	 * @param string $field First field, ID from Wordpress
	 * @param string $external_field External field from Sugester
	 * @return bool true if success else false
	 */
	private function insert_field($id, $external_id, $table_name, $field, $external_field) {
		if ( self::$fatal )
			return false;

		if ( empty( $id ) || empty( $external_id ) )
			return false;

		global $wpdb;
		$wpdb->query( $wpdb->prepare(
				"INSERT INTO {$table_name}
				( {$field}, {$external_field} )
				VALUES ( %d, %d )
				ON DUPLICATE KEY UPDATE {$external_field} = %d",
				(int) $id,
				(int) $external_id,
				(int) $external_id // to update
			)
		);

		return true;
	}


	/**
	 * Inserts clients id->external_id relationship to database.
	 *
	 * @note Used in API create client on Sugester request
	 * @since 1.0.0
	 * @param int $id WooCommerce client ID
	 * @param int $external_id Sugester Client ID
	 * @return bool true if success else false
	 */
	public function insert_client($id, $external_id) {
		return $this->insert_field(
			$id,
			$external_id,
			$this->get_clients_table(),
			'client_id',
			'external_client_id'
		);
	}


	/**
	 * Inserts order id->external_id relationship to database.
	 *
	 * @since 1.0.0
	 * @param int $id WooCommerce client ID
	 * @param int $external_id Sugester Client ID
	 * @return bool true if success else false
	 */
	public function insert_order($id, $external_id) {
		return $this->insert_field(
			$id,
			$external_id,
			$this->get_orders_table(),
			'order_id',
			'external_order_id'
		);
	}


	/**
	 * Returns Sugester Client ID registered to the database
	 *
	 * @note Used for API PUT/GET requests
	 * @see $this->insert_client
	 * @see $this->get_external_id
	 * @since 1.0.0
	 * @param int $id WooCommerce FIELD ID
	 * @param string $table_name Table name to search in
	 * @param string $field Field name
	 * @param string $external_field External field name.
	 * @return mixed Sugester Client ID
	 *            - false: database fatal error
	 *            - NULL:  no record
	 *            - 0:     there was an error with creation.
	 *            - >0:    correct Sugester Client ID
	 */
	private function get_external_id($id, $table_name, $field, $external_field) {
		if ( self::$fatal )
			return false;

		global $wpdb;
		$result = $wpdb->get_results( "
			SELECT " . $external_field . "
			FROM " . $table_name . "
			WHERE " . $field . " = " . (int) $id,
			ARRAY_A
		);

		return ( empty( $result ) ? null : (int) $result[0][$external_field] );
	}


	/**
	 * Returns Sugester Client ID registered to the database
	 *
	 * @note Used for API PUT/GET requests
	 * @see $this->insert_client
	 * @see $this->get_external_id
	 * @since 1.0.0
	 * @param int $id WooCommerce Client ID
	 * @return mixed Sugester Client ID
	 *            - false: database fatal error
	 *            - NULL:  no record
	 *            - 0:     there was an error with creation.
	 *            - >0:    correct Sugester Client ID
	 */
	public function get_client_id($id) {
		return ( $this->get_external_id(
			$id,
			$this->get_clients_table(),
			'client_id',
			'external_client_id'
		) );
	}

	public function remove_order($id) {
		global $wpdb;
		return $wpdb->delete(
			$this->get_orders_table(),
			array('order_id' => $id)
		);
	}


	/**
	 * Returns Sugester Client ID registered to the database
	 *
	 * @note Used for API PUT/GET requests
	 * @see $this->insert_order
	 * @see $this->get_external_id
	 * @since 1.0.0
	 * @param int $id WooCommerce Order ID
	 * @return mixed Sugester Deal ID
	 *            - false: database fatal error
	 *            - NULL:  no record
	 *            - 0:     there was an error with creation.
	 *            - >0:    correct Sugester Client ID
	 */
	public function get_order_id($id) {
		return ( $this->get_external_id(
			$id,
			$this->get_orders_table(),
			'order_id',
			'external_order_id'
		) );
	}


	/**
	 * Returns current statuses from the database
	 * @since 2.2.5
	 */
	public static function get_statuses() {
		$statuses = get_option( 'woocommerce_sugester_statuses' );
		return ( empty( $statuses ) ? array() : $statuses );
	}

	public static function get_all_orders() {
		global $wpdb;
		$orders_table = $wpdb->prefix . 'sugester_woocommerce_orders';
		$orders = $wpdb->get_results( "SELECT * FROM " . $orders_table, ARRAY_A );

		return $orders;
	}
}

endif;