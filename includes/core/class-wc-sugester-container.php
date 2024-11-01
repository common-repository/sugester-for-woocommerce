<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WC_Sugester_Container' ) ) :

/**
 * WC Sugester Container.
 *
 * Container for easier OOP management. Provides access to fields fields for
 * other classes.
 *
 * @note: If you want to add new settings, simply add your key with
 *        it's default value to $fields array.
 *        Now you can use it as "$this->key" :)
 *
 * @package  WC_Sugester_Core
 * @category Integration Core
 * @author   Sugester
 */
class WC_Sugester_Container {

	/**
	 * Contains all fields provided in settings
	 * that will be saved in the database.
	 * 'key'   -> option key in database
	 * 'value' -> array(
	 *               -> 'type' from [string, bool...]
	 *               -> 'default' - default value provided for that key
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private static $fields = array(
		'api_token'          => array( 'type' => 'string', 'default' => '' ),
	);


	/**
	 * Constructor for container class.
	 *
	 * If given param is an integration object, will reach out to
	 * sugester settings and create an array with it
	 *
	 * Afterwards will create public attributes based on self::$fields
	 *
	 * @since 1.0.0
	 * @param mixed $settings Integration class or Array
	 * @return mixed Container object
	 */
	public function __construct( $settings ) {
		$type = gettype( $settings );
		if ( $type !== 'object' && $type !== 'array' ) {
			exit( sprintf(
				$this->l('container.error.constructor.unknown_type'),
				$type
			));
		}

		// construct with database options
		if ( $type === 'object' ) {
			// should be called only and only with this class
			assert( get_class( $settings ) === 'WC_Sugester_Integration' );

			$settings_as_array = array();
			foreach ( self::$fields as $field => $value )
				$settings_as_array[$field] = $settings->get_option( $field, $value['default'] );

			$settings = $settings_as_array;
		}

		$settings = $this->fill_unset_values( $settings );
		$settings = $this->unset_undefined_values( $settings );
		$this->construct_from_array( $settings );

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
	 * Constructs public container attributes based on settings key
	 *
	 * Providing "$settings['example']" will be evaluated to
	 * "$this->example = $settings['example']", which follows DRY principle.
	 *
	 * @param array $settings Contains keys and values
	 * @since 1.0.0
	 */
	private function construct_from_array( $settings ) {
		foreach ( self::$fields as $key => $value ) {
			$this->{ $key } = $settings[ $key ];

			if ( self::$fields[$key]['type'] === 'bool' ) {
				// checkbox is saved as "yes" or "no"...
				$this->{ $key } = ( $this->{ $key } === 'yes' );
			}
			elseif ( self::$fields[$key]['type'] === 'int' ) {
				$this->{ $key } = (int) $this->{ $key };
			}
		}
	}


	/**
	 * Fills values that are defined in self::$fields, but
	 * undefined in $settings
	 *
	 * @param array $settings settings to fill unset values
	 * @return array $settings settings with all values set
	 */
	private function fill_unset_values( $settings ) {
		foreach ( self::$fields as $field => $value ) {
			if ( ! isset( $settings[$field] ) ) {
				$settings[$field] = $value['default'];
			}
		}
		return $settings;
	}


	/**
	 * Unsets values that are undefined in self::$fields.
	 *
	 * @param array $settings settings to unset if necessary
	 * @return array $settings unset settings
	 */
	private function unset_undefined_values( $settings ) {
		$field_keys = array_keys( self::$fields );

		foreach ( $settings as $key => $value ) {
			if ( ! in_array( $key, $field_keys ) ) {
				unset( $settings[$key] );
			}
		}
		return $settings;
	}


	/**
	 * Returns container as an array.
	 *
	 * @since 1.0.0
	 * @return array Container as an array
	 */
	public function as_array() {
		$field_keys = array_keys( self::$fields );

		$array_to_return = array();
		foreach ( $field_keys as $key ) {
			$array_to_return[ $key ] = $this->{ $key };
		}

		return $array_to_return;
	}
}

endif;