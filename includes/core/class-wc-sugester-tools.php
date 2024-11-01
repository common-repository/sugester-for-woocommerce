<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WC_Sugester_Tools' ) ) :

/**
 * WC Sugester Tools.
 *
 * Provides core functions to communicate with Sugester API
 *
 * @note Constructor uses @see WC_Sugester_Container class for code readibility.
 *       So to access Integration settings key, we use "$this->c->your_key"
 *
 * @note Almost all methods that use database have an assumption,
 *       that no fatal error is present.
 *
 * @package  WC_Sugester
 * @category Integration
 * @author   Sugester
 */
class WC_Sugester_Tools {

	/**
	 * Instance to be returned in self::get_instance
	 * @var object WC_Sugester_Tools instance
	 */
	private static $instance_without_db;

	/**
	 * Instance to be returned in self::get_instance_with_db
	 * @var object WC_Sugester_Tools instance
	 */
	private static $instance_with_db;


	/**
	 * @var WC_Sugester_Database
	 * @since 1.0.0
	 */
	private $db;


	/**
	 * @var WC_Sugester_Translation
	 * @since 1.0.0
	 */
	private $translation;


	/**
	 * Key to check if configuration is correct (to display features)
	 * @var string
	 * @since 1.0.0
	 */
	private static $configured_key = 'woocommerce_sugester_is_configured';

	/**
	 * Is plugin configured correctly?
	 * @see $this->is_configured()
	 * @see $this->update_configured()
	 * @var bool
	 * @since 1.0.2
	 */
	private static $is_configured;


	/**
	 * Constructor for self.
	 *
	 * Two constructors available, both use @see WC_Sugester_Container:
	 * new self() - creates new sugester integration class and takes
	 *              it's new container
	 * new self( $container ) - copies given $container into $this->c
	 *
	 * @param WC_Sugester_Container $container Container to copy, optional
	 * @param bool $with_db Prevention against recursion in upgrades
	 * @since 1.0.0
	 */
	public function __construct( $container = null, $with_db = true ) {
		if ( ! is_null( $container ) ) {
			// constructor with parameter
			$this->c = $container;
		}
		else {
			// constructor without parameters. advanced stuff
			$integration = & WC_Sugester_Integration::get_instance();
			$this->c = $integration->c; // freshly made container
		}

		if ( $with_db === true ) {
			// Container is set, setting up database (some functions use it)
			$this->db = & WC_Sugester_Database::get_instance();
		}

		// Translations:
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
	 * Returns a reference to static tools in order not to copy
	 * this class whenever this is called.
	 *
	 * @note Calling this function should be done by " & self::get_instance() "
	 * @since 1.0.0
	 * @param bool $with_db Decide whether to include database or not
	 * @return object This class
	 */
	public static function &get_instance() {
		if ( empty( self::$instance_without_db ) ) {
			self::$instance_without_db = new self( null, false );
		}
		return self::$instance_without_db;
	}

	/**
	 * Returns a reference to static tools in order not to copy
	 * this class whenever this is called.
	 *
	 * @note Calling this function should be done by " & self::get_instance() "
	 * @since 1.0.0
	 * @param bool $with_db Decide whether to include database or not
	 * @return object This class
	 */
	public static function &get_instance_with_db() {
		if ( empty( self::$instance_with_db ) ) {
			self::$instance_with_db = new self( null, true );
		}
		return self::$instance_with_db;
	}


	/**
	 * Are Sugester settings correct?
	 *
	 * @since 1.0.0
	 * @return bool Is Sugester plugin configured?
	 */
	public function is_configured() {
		// Declared as static for perfomance reasons (1 database call)
		if ( ! isset( self::$is_configured ) )
			self::$is_configured = get_option( self::$configured_key );

		return self::$is_configured;
	}


	/**
	 * Updates configured key.
	 *
	 * @since 1.0.0
	 * @param bool $value Value to be updated.
	 * @return True if option value has changed, false if not or if update failed.
	 */
	public function update_configured( $value ) {
		$update_success = update_option( self::$configured_key, $value );
		if ( $update_success )
			self::$is_configured = get_option( self::$configured_key );

		return $update_success;
	}


	/**
	 * Curl function to make a request.
	 *
	 * Encodes $data into json, sends data to the server and asks for response.
	 * Afterwards, the response is returned to the caller ( @see $this->make_request )
	 *
	 * @param string $url URL for request
	 * @param string $method Method from ['GET', 'POST', 'PUT', 'DELETE']
	 * @param mixed $data Data to send to the server
	 * @return mixed Server response.
	 * @since 1.0.0
	 */
	private function curl($url, $method, $data){
		$data = json_encode($data);
		$cu = curl_init($url);
		curl_setopt($cu, CURLOPT_VERBOSE, 0);
		curl_setopt($cu, CURLOPT_HEADER, 0);
		curl_setopt($cu, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($cu, CURLOPT_CUSTOMREQUEST, $method);
		curl_setopt($cu, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($cu, CURLOPT_POSTFIELDS, $data);
		curl_setopt($cu, CURLOPT_HTTPHEADER, array('Accept: application/json','Content-Type: application/json'));
		$response = curl_exec($cu);
		curl_close($cu);

		$result = json_decode($response);
		return $result;
	}


	/**
	 * Uses @see $this->curl to make a request to the Sugester server.
	 *
	 * @param string $url url to the server
	 * @param string $method Method from ['GET', 'POST', 'PUT', 'DELETE']
	 * @param mixed $data Data to be send to the server.
	 * @return mixed Server response.
	 * @since 1.0.0
	 */
	public function make_request($url, $method, $data) {
		return $this->curl($url, $method, $data);
	}


	/**
	 * Returns an URL based on the given controller.
	 *
	 * @param string $controller Controller to call, ie 'invoices'
	 * @return string URL for @see $this->make_request()
	 * @since 1.0.0
	 */
	private function get_api_url($controller) {
		$account_domain = $this->get_prefix_from_token($this->c->api_token).'.sugester.com';
		$http = ( SUGESTER_WC_DEBUG ) ? 'http' : 'https';

		if ( SUGESTER_WC_DEBUG ) {
			// change domain to .dev
			$account_domain = explode('.', $account_domain);
			array_pop($account_domain);
			$account_domain = implode('.', $account_domain) . '.dev';
		}

		$url = "{$http}://{$account_domain}/app/{$controller}";
		return $url;
	}

	/**
	 * Extracts account prefix from api token
	 * API Token looks like this: "aBdDFWFwmD/john_kowalsky"
	 *
	 * @param string $api_token Sugester API Token
	 * @return string Account prefix extracted from api_token
	 * @since 1.0.4
	 */
	private function get_prefix_from_token($api_token) {
        $tmp = explode('/', $api_token);
        return array_pop($tmp);
    }

	/**
	 * Returns URL to Sugester Clients
	 *
	 * @since 1.0.0
	 * @see $this->get_api_url
	 * @return string Sugester Clients URL
	 */
	public function get_clients_url() {
		return $this->get_api_url( 'clients' );
	}


	/**
	 * Returns URL to Sugester Client based on given ID
	 *
	 * @since 1.0.0
	 * @see $this->get_api_url
	 * @param int $external_client_id Sugester Client ID
	 * @return string Sugester Clients URL
	 */
	public function get_client_url( $external_client_id ) {
		return $this->get_clients_url() . '/' . $external_client_id;
	}


	/**
	 * Returns URL to Sugester Clients as JSON format
	 *
	 * @since 1.0.0
	 * @see $this->get_clients_url
	 * @return string Sugester Clients URL JSON
	 */
	public function get_clients_url_json() {
		return $this->get_clients_url() . '.json';
	}


	/**
	 * Returns URL to Sugester Client as JSON format
	 *
	 * @since 1.0.0
	 * @see $this->get_client_url
	 * @param int $external_client_id Sugester Client ID
	 * @return string Sugester Client URL JSON
	 */
	public function get_client_url_json( $external_client_id ) {
		return $this->get_client_url( $external_client_id ) . '.json';
	}


	/**
	 * Returns URL to Sugester Deals
	 *
	 * @since 1.0.0
	 * @see $this->get_api_url
	 * @param int @client_id Client ID. Defualt: null
	 * @return string Sugester Deals URL
	 */
	public function get_deals_url( $client_id = null ) {
		$suffix = is_null($client_id) ? '' : "?client_id={$client_id}";
		return $this->get_api_url( 'deals' ) . $suffix;
	}

	/**
	 * Returns URL to Sugester Deals in JSON format
	 *
	 * @since 1.0.0
	 * @see $this->get_deals_url
	 * @return string Sugester Deals URL in JSON
	 */
	public function get_deals_url_json() {
		return $this->get_deals_url() . '.json';
	}

	/**
	 * Returns URL to Sugester Deal based on given ID
	 *
	 * @since 1.0.0
	 * @see $this->get_api_url
	 * @param int $external_deal_id Sugester Deal ID
	 * @return string Sugester Deals URL
	 */
	public function get_deal_url( $external_deal_id ) {
		return $this->get_deals_url() . '/' . $external_deal_id;
	}


	/**
	 * Returns URL to Sugester Deal as JSON format
	 *
	 * @since 1.0.0
	 * @see $this->get_deal_url
	 * @param int $external_deal_id Sugester Deal ID
	 * @return string Sugester Deal URL as JSON
	 */
	public function get_deal_url_json( $external_deal_id ) {
		return $this->get_deal_url( $external_deal_id ) . '.json';
	}

	/**
	 * Returns URL to Sugester Statuses
	 *
	 * @since 1.0.5
	 * @see $this->get_api_url
	 * @return string Sugester Statuses URL
	 */
	public function get_statuses_url() {
		return $this->get_api_url( 'statuses' );
	}

	/**
	 * Returns URL to Sugester Statuses in JSON format
	 *
	 * @since 1.0.5
	 * @see $this->get_statuses_url
	 * @return string Sugester Statuses URL in JSON format
	 */
	public function get_statuses_url_json() {
		return $this->get_statuses_url() . '.json';
	}

	/**
	 * Returns URL to Sugester Status based on given ID
	 *
	 * @since 1.0.5
	 * @see $this->get_statuses_url
	 * @param  int    $external_status_id   Sugester Status ID
	 * @return string Sugester Statuses URL in JSON format
	 */
	public function get_status_url( $external_status_id ) {
		return $this->get_statuses_url() . '/' . (int) $external_status_id;
	}

	/**
	 * Returns URL to Sugester Status based on given ID
	 *
	 * @since 1.0.5
	 * @see $this->get_statuses_url
	 * @param  int    $external_status_id   Sugester Status ID
	 * @return string Sugester Statuses URL in JSON format
	 */
	public function get_status_url_json( $external_status_id ) {
		return $this->get_status_url( $external_status_id ) . '.json';
	}


	/**
	 * Returns sugester statuses from domain
	 *
	 * @since 1.0.7
	 * @return array Statuses
	 */
	public function get_sugester_statuses() {
		$url = $this->get_statuses_url_json();
		$data = array(
			'api_token' => $this->c->api_token,
			'kind'      => 'deal', // Kind made for this integration
		);
		$response = $this->make_request($url, 'GET', $data);
		return is_array($response) ? $response : array();
	}

	/**
	 * Provides origin for API requests.
	 * @since 1.0.0
	 * @return string Origin.
	 */
	public function get_origin() {
		global $wp_version, $woocommerce;
		$wc_version = $woocommerce->version;
		return "wp-{$wp_version}|woocommerce-{$wc_version}|sugester-1.0.9";
	}


	/**
	 * Returns user meta data to caller.
	 *
	 * If billing meta is not filled, will return "shipping" email and no more.
	 *
	 * @since 1.0.0
	 * @param int $client_id Customer ID (User ID)
	 * @return array Associative array containing the meta data.
	 */
	public function get_user_meta( $client_id ) {
		if ( empty($client_id) ) {
			sugester_error( "get_user_meta: '{$client_id}' is empty!" ); // todo: translate
			return false;
		}
		$prefix = 'billing_';
		$fields = array(
			'first_name',
			'last_name',
			'url',
			'email',
			'phone',
			'address_1',
			'address_2',
			'postcode',
			'city',
			'country',
		);
		$data = array();

		// User data ( from 'wp_users' )
		$user = get_userdata( $client_id );
		/*sugester_log("=============In user meta:");
		sugester_log( $client_id, false );
		sugester_log( $user, false );
		sugester_log("=============ended user meta");*/
		$data['name']  = $user->user_login;
		$data['email'] = $user->user_email;
		$data['url']   = $user->user_url;

		// Setting the meta
		foreach ( $fields as $field ) {
			$meta = get_user_meta( $client_id, $prefix.$field, true );
			if ( ! empty( $meta ) )
				$data[ $field ] = $meta;
		}

		// modifying address
		if ( ! ( empty($data['address_1']) || empty($data['address_2']) ) )
			$data[ 'street' ] = $data['address_1'].' '.$data['address_2'];
		elseif ( ! empty( $data['address_1'] ) )
			$data[ 'street' ] = $data['address_1'];
		elseif ( ! empty( $data['address_2'] ) )
			$data[ 'street' ] = $data['address_2'];
		unset( $data['address_1'] );
		unset( $data['address_2'] );

		if ( ! ( empty($data[ 'postcode' ]) ) ) {
			// postcode is labeled as "post_code" in sugester
			$data[ 'post_code' ] = $data[ 'postcode' ];
		}
		unset( $data[ 'postcode' ] );

		return $data;
	}


	/**
	 * Checks if $client_id is WooCommerce customer
	 *
	 * @since 1.0.2
	 * @since 1.0.9 returns false if user is incorrect
	 * @param int $client_id WP_User ID
	 * @return bool is WP_User a WooCommerce customer?
	 */
	public function is_customer( $client_id ) {
		try {
			$user = new WP_User( $client_id );
			return in_array( 'customer', $user->roles );
		}
		catch (Exception $e) {
			return false;
		}
	}


	// todo: document
	public function extract_billing_data_from_order( $order_id ) {
		if ( empty( $order_id ) )
			return false;

		$o = new WC_Order( $order_id );
		return array(
			'first_name' => $o->billing_first_name,
			'last_name'  => $o->billing_last_name,
			'email'      => $o->billing_email,
			'phone'      => $o->billing_phone,
			'street'     => $o->billing_address_1.' '.$o->billing_address_2,
			'postcode'   => $o->billing_postcode,
			'city'       => $o->billing_city,
			'country'    => $o->billing_country,
		);
	}


	/**
	 * Generates and returns sugester url.
	 *
	 * @since 1.0.0
	 * @todo zmienic nazwe
	 * @param int     $id Wordpress User ID
	 * @param string  $source where to display. ['users', 'user', 'order']
	 * @param string  $class CSS Class to be displayed. Default: null
	 * @param bool    $echo Should echo. Default: false
	 * @return string sugester_url if $echo is false
	 */
	public function generate_sugester_url( $id, $source, $class = null, $echo = false ) {
		$sugester_client_id = $this->db->get_client_id( $id );
		$created = ! empty( $sugester_client_id );

		if ( is_null( $class ) )
			$class = 'button button-secondary';
		$class .= ' hide-if-no-js';

		if ( $created ) {
			$sugester_view_link = $this->get_client_url( $sugester_client_id );
			$link = '<a class="' . $class . '" href="' . $sugester_view_link . '" target="_blank">'
				. $this->l("client.button.view.{$source}") .
			'</a>';
		}
		else {
			// calls ajax function and replaces to view_link
			$link = '<a ' .
				'id="sugester_client_' . $id . '" '.
				'class="' . $class . '" ' .
				'href="javascript:void(0)"' .
				'onclick="sugester_create_client('.$id.',\''.$source.'\')">'
					. $this->l("client.button.add.{$source}") .
			'</a>';
		}

		if ( ! $echo )
			return $link;
		echo $link;
	}


	/**
	 * Generates and returns deals made by client url.
	 *
	 * @since 1.0.0
	 * @param int     $id Wordpress User ID
	 * @param string  $class CSS Class to be displayed. Default: null
	 * @param bool    $echo Should echo. Default: false
	 * @return string URL if $echo is false
	 */
	public function generate_deals_by_client_url($client_id, $class = null, $echo = false) {
		if ( is_null( $class ) )
			$class = 'button button-secondary';

		$sugester_client_id = $this->db->get_client_id( $client_id );

		if ( empty( $sugester_client_id ) ) {
			$link = '<a class="' . $class . '">'
				. $this->l('deals.button.client_not_created') .
			'</a>';
		}
		else {
			$deals_by_client_url = $this->get_deals_url( $sugester_client_id );
			$link = '<a class="' . $class . '" href="' . $deals_by_client_url . '" target="_blank">'
				. $this->l("deals.button.show_by_client") .
			'</a>';
		}

		if ( ! $echo )
			return $link;
		echo $link;
	}


	/**
	 * Generates and returns VIEW URL.
	 *
	 * @since 1.0.0
	 * @param string  $url URL to sugester View
	 * @param string  $text Text to be displayed.
	 * @param string  $class CSS Class to be displayed. Default: null
	 * @param bool    $echo Should echo. Default: false
	 * @return string URL if $echo is false
	 */
	public function generate_view_url($url, $text, $class = null, $echo = false) {
		if ( is_null( $class ) )
			$class = 'button button-secondary';

		$link = '<a class="' . $class . '" href="' . $url . '" target="_blank">'
			. $text .
		'</a>';

		if ( ! $echo )
			return $link;
		echo $link;
	}

	/**
	 * Generates and returns deals URL.
	 *
	 * @since 1.0.0
	 * @param string  $class CSS Class to be displayed. Default: null
	 * @param bool    $echo Should echo. Default: false
	 * @return string URL if $echo is false
	 */
	public function generate_deals_url($class = null, $echo = false) {
		return $this->generate_view_url(
			$this->get_deals_url(),
			$this->l("deals.button.show_all"),
			$class,
			$echo
		);
	}


	/**
	 * Generates and returns clients URL.
	 *
	 * @since 1.0.0
	 * @param string  $class CSS Class to be displayed. Default: null
	 * @param bool    $echo Should echo. Default: false
	 * @return string URL if $echo is false
	 */
	public function generate_clients_url($class = null, $echo = false) {
		return $this->generate_view_url(
			$this->get_clients_url(),
			$this->l("clients.button.show_all"),
			$class,
			$echo
		);
	}


	/**
	 * Generates and returns button to create a deal on Sugester from order.
	 *
	 * @since 1.0.0
	 * @param int $id Order id
	 * @param bool $echo Should echo. Default: false
	 * @return string generate deal button if $echo is false.
	 */
	public function generate_deal_button( $id, $echo = false ) {
		$sugester_order_id = $this->db->get_order_id( $id );
		$created = ! empty( $sugester_order_id );
		$class = 'button button-secondary hide-if-no-js';

		// todo: 100% width?, fixed width?
		if ( $created ) {
			$sugester_view_link = $this->get_deal_url( $sugester_order_id );
			$button = '<a class="'.$class.'" href="'.$sugester_view_link.'" target="_blank">' .
				$this->l('deal.button.view') .
			'</a>';
		}
		else {
			$button = '<a '.
				'id="sugester_order_' . $id . '" '.
				'class="'.$class.'" ' .
				'href="javascript:void(0)" ' .
				'onclick="sugester_create_order('.$id.')">'
					. $this->l('deal.button.add') .
			'</a>';
		}

		if ( ! $echo )
			return $button;

		echo $button;
	}


	/**
	 * Uses WP_User_Query to select all customer ids from WooCommerce.
	 *
	 * @since 1.0.0
	 * @return array Array with ids
	 */
	public function get_all_customer_ids() {
		$admin_users = new WP_User_Query(
			array(
				'role'   => 'administrator',
				'fields' => 'ID'
			)
		);
		$manager_users = new WP_User_Query(
			array(
				'role'   => 'shop_manager',
				'fields' => 'ID'
			)
		);
		$users_query = new WP_User_Query(
			array(
				'fields'  => array( 'id' ),
				'exclude' => array_merge( $admin_users->get_results(), $manager_users->get_results() )
			)
		);
		$customers = $users_query->get_results();
		return array_map( create_function('$x', 'return $x->id;'), $customers );
	}


	/**
	 * Uses get_posts to retrieve all orders from the database.
	 *
	 * @since 1.0.0
	 * @return array Array with ids
	 */
	public function get_all_order_ids() {
		$orders = get_posts( array(
			'numberposts' => -1, // 'all' from documentation
			'post_type'   => 'shop_order',
			'post_status' => array_keys( wc_get_order_statuses() )
		) );

		$order_ids = array_map(create_function('$x', 'return $x->ID;'), $orders);
		return $order_ids;
	}


	/**
	 * Attempts to create a client on Sugester.
	 *
	 * Assumption: NO FATAL ERROR is present.
	 *
	 * @since 1.0.0
	 * @param int $client_id Client's ID. 0 if order not have a client, but requires it on Sugester
	 * @param array $client_data Client metadata to be sent. Default: null
	 * @param bool $return_status If true, will return if Client was created.
	 *                             Default: false
	 * @return mixed Response from the server.
	 *               Returns false if response is empty
	 *               Success? if $return_status is true
	 */
	public function create_sugester_client($client_id, $client_data = null, $return_status = false) {
		if ( gettype( $client_id ) !== 'integer' ) {
			sugester_error("client id is not an integer");
			return false;
		}

		if ( is_null( $client_data ) )
			$client_data = $this->get_user_meta( $client_id );

		// If user was already created, will attempt to update it:
		$client_data['try_update'] = true;
		$client_data['origin'] = $this->get_origin();

		$url = $this->get_clients_url_json();
		$data = array(
			'api_token' => $this->c->api_token,
			'client' => $client_data
		);
		$response = $this->make_request( $url, 'POST', $data );

		if ( empty($response) || empty($response->id) ) {
			// Failure, will add pair (id, 0) to database.
			sugester_log( sprintf( $this->l('client.error.create'), $client_id ) );
			$this->db->insert_client( $client_id, 0 );
			return false;
		}

		$this->db->insert_client( $client_id, $response->id );
		return ( $return_status ? true : $response );
	}


	/**
	 * Prepares an order title that will be passed to Sugester.
	 *
	 * @since 1.0.0
	 * @param int $order_id Order id
	 * @return string Order title.
	 */
	public function get_order_title($order_id) {
		return sprintf(
			$this->l('order.id'),
			$order_id
		);
	}


	/**
	 * Returns order data that will be send to Sugester Deals
	 *
	 * @since 1.0.0
	 * @param int $order_id Order ID
	 * @return array Order details to send
	 */
	public function get_order_data_to_send($order_id) {
		$order = new WC_Order( $order_id );
		$total = $order->calculate_totals();
		$post = get_post($order->get_id());
		$title = $post->post_title;
		$statuses = WC_Sugester_Database::get_statuses();
		if (!empty($statuses['wc-'.$order->get_status()])) {
			$status_id = $statuses['wc-'.$order->get_status()];
		}

		$data = array(
			'url' => admin_url('post.php?post='.absint($order->get_id()).'&action=edit'),
			'name' => $this->get_order_title( $order_id ),
			'price' => $order->calculate_totals(), // todo: nie dziala przy tworzeniu automatycznym ;_________;
			//'description' => 'deal description',
			'client_id' => $this->db->get_client_id( $order->get_user_id() ),
			//'origin' => $this->get_origin(),
			'status_id' => (isset($status_id) ? $status_id : NULL),
		);

		return $data;
	}


	/**
	 * Creates client from an order.
	 *
	 * If order was created by a guest, user_id is 0 so the data has to be
	 * extracted from the order. (It will also not be added to clients)
	 *
	 * @param int $order_id Order ID
	 * @return mixed False if error
	 *               Sugester_Client_ID if success
	 */
	private function create_client_from_order($order_id) {
		$order = new WC_Order( $order_id );
		$client_id = $order->get_user_id();

		if ( empty( $client_id ) ) {
			// Order was made without registration. (Guest)
			$client_data = $this->extract_billing_data_from_order( $order_id );
			$client_data['note'] = $this->l('order.created_by_guest');
			$client_id = 0; // <- GUEST
		}
		else {
			// Checking if client was already created.
			$external_client_id = $this->db->get_client_id( $client_id );
			if ( $external_client_id === false ) {
				sugester_error( $this->l('db.error.fatal') );
				return false;
			}
			if ( $external_client_id > 0 ) {
				return $external_client_id;
			}
			assert( $external_client_id === 0 || is_null( $external_client_id ) );
			$client_data = $this->get_user_meta( $client_id );
		}

		//sugester_log($client_id);
		$response = $this->create_sugester_client( $client_id, $client_data );
		if ( empty( $response ) ) {
			sugester_error( sprintf( $this->l('client.error.empty_response'), json_encode( $response ) ) );
			return false;
		}
		return ( (int) $response->id );
	}


	/**
	 * Creates an order pair on Sugester as a Deal
	 *
	 * @todo dokonczyc RETURN w dokumentacji
	 * @since 1.0.0
	 * @param int $order_id Order id
	 * @param mixed $order_data Order data to send. Default: null
	 * @param bool $return_status Returns (response success?). Default: false
	 * @return mixed False if client was not present and failed to create it.
	 */
	public function create_deal_from_order($order_id, $order_data = null, $return_status = false) {
		if ( is_null( $order_data ) )
			$order_data = $this->get_order_data_to_send( $order_id );

		if ( empty( $order_data['client_id'] ) ) {
			// Creating client if it was not created.
			$order_data['client_id'] = $this->create_client_from_order( $order_id );
			if ( empty( $order_data['client_id'] ) )
				return false;
		}

		assert( !empty( $order_data['client_id'] ));
		$url = $this->get_deals_url_json();
		$data = array(
			'api_token' => $this->c->api_token,
			'deal'      => $order_data,
		);
		$response = $this->make_request( $url, 'POST', $data );

		if ( empty($response) || empty($response->id) ) {
			// Failure, will add pair (id, 0) to database.
			sugester_error( sprintf( $this->l('deal.error.create'), $order_id ) );
			$this->db->insert_order( $order_id, 0 );
			return false;
		}

		$this->db->insert_order( $order_id, $response->id );
		return ( $return_status ? true : $response );
	}


	/**
	 * Updates Sugester Statuses
	 * @note Triggers update on all orders
	 * @since 1.0.5
	 */
	public function update_sugester_statuses() {
		$sugester_statuses_key = 'woocommerce_sugester_statuses';
		$current_statuses = get_option( $sugester_statuses_key );
		if ( empty( $current_statuses ) ) {
			$current_statuses = array();
		}

		$wc_statuses              = wc_get_order_statuses();
		$wc_statuses_codes        = array_keys( $wc_statuses );
		$wc_statuses_translations = array_values( $wc_statuses );

		// Statuses in database (to be serialized):  status_name -> sugester_id
		$db_statuses = array();

		// Attempt to hook already created statuses
		$sugester_statuses = $this->get_sugester_statuses();
		foreach ($sugester_statuses as $s) {
			// 28.03.2017: Sugester uses "name" as a unique key.
			if ( in_array( $s->name, $wc_statuses_translations ) ) {
				// We want to check for existence of any wc_status
				if ( ! in_array( $s->code, $wc_statuses_codes ) ) {
					error_log("FATAL");
					return;
				}

				// Hooking found code to sugester id
				$db_statuses[ $s->code ] = (int) $s->id;
				unset( $s->code );
			}
		}

		foreach ( $wc_statuses as $code => $name ) {
			$url = $this->get_statuses_url_json();
			$data = array(
				'api_token' => $this->c->api_token,
				'status' => array(
					'code' => $code,
					'name' => $name,
					'kind' => 'deal',
				),
			);
			$response = $this->make_request( $url, 'POST', $data );
			if ( ! (empty( $response->id ) || empty( $response->code ) || empty( $response->name )) ) {
				if ( ! (in_array( $response->code, $wc_statuses_codes ) &&
					    in_array( $response->name, $wc_statuses_translations ) ) ) {
					error_log("fatal");
					return;
				}
				$db_statuses[ $response->code ] = $response->id;
			}
		}

		$db_statuses = array_merge($current_statuses, $db_statuses);
		update_option( $sugester_statuses_key, $db_statuses );

		$this->update_all_orders_with_new_status();
	}

	/**
	 * Updates all orders with new status_id (generated seconds before this operation)
	 *
	 * @since 1.0.5
	 * @since 1.0.9 catches exception if order was deleted
	 */
	public function update_all_orders_with_new_status() {
		$orders = WC_Sugester_Database::get_all_orders();
		$statuses = WC_Sugester_Database::get_statuses();
		foreach ($orders as $o) {
			try {
				$order = new WC_Order( (int) $o['order_id'] );
				$status = 'wc-' . $order->get_status();
				$url = $this->get_deal_url_json( $o[ 'external_order_id' ] );
				$data = array(
					'api_token' => $this->c->api_token,
					'deal' => array(
						'status_id' => $statuses[ $status ],
					),
				);
				$response = $this->make_request( $url, 'PUT', $data );
			} catch (Exception $e) {
				// Order was most probably removed
				$this->db->remove_order($o['order_id']);
			}
		}
	}

}

endif;
