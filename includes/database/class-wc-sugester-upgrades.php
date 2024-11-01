<?php

if ( ( !defined('ABSPATH')) ) exit;

if ( ! class_exists( 'WC_Sugester_Upgrades' ) ) :

/**
 * WC Sugester Upgrades class.
 *
 * Class responsible for database upgrades.
 *
 * @package  WC_Sugester_Database
 * @category Integration Database
 * @author   Sugester
 * @since 1.0.2
 */
class WC_Sugester_Upgrades {

	/**
	 * Contains files for upgrades
	 *
	 * @since 1.0.2
	 * @var array List of files
	 */
	private static $upgrade_files;


	/**
	 * Loads upgrades files list from 'upgrades' folder.
	 *
	 * @since 1.0.2
	 * @param string $installed_version Installed version
	 * @param string $new_version New version to be installed
	 * @return bool Is any upgrade required?
	 */
	private static function load_upgrade_list($installed_version, $new_version) {
		$list = array();
		$upgrade_path = dirname( __FILE__ ) . '/upgrades/';
		if ( file_exists($upgrade_path) ) {
			$files = scandir( $upgrade_path );
			foreach ( $files as $file ) {
				if (!in_array($file, array('.', '..', '.svn', 'index.php'))) {
					$tab = explode('-', $file);
					$file_version = basename($tab[1], '.php');
					// Compare version, if minor than actual, we need to upgrade the module
					if (count($tab) == 2 &&
						version_compare( $file_version, $installed_version, '>' ) &&
						version_compare( $file_version, $new_version, '<=' ) )
					{
						$list[] = array(
							'file' => $upgrade_path.$file,
							'version' => $file_version,
							'upgrade_function' => 'woocommerce_sugester_upgrade_'.str_replace('.', '_', $file_version)
						);
					}
				}
			}
		}

		usort(
			$list,
			create_function('$a, $b', 'return version_compare($a["version"], $b["version"]);')
		);


		self::$upgrade_files = $list;
		return (bool) count($list);
	}


	/**
	 * Runs upgrades if any is required.
	 *
	 * @since 1.0.2
	 * @param string $installed_version Installed version
	 * @param string $new_version New version to be installed
	 */
	public static function upgrade($installed_version, $new_version) {
		if ( self::load_upgrade_list($installed_version, $new_version) ) {
			foreach (self::$upgrade_files as $upg) {
				$upgrade_result = true;
				include_once($upg['file']);
				if ( function_exists($upg['upgrade_function']) ) {
					$upgrade_result = $upg['upgrade_function']();
				}

				if ( $upgrade_result === false ) {
					// We deactivate the plugin on failure :<
					require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
					deactivate_plugins('sugester-for-woocommerce', true);
					break;
				}
			}
		}
	}

}

endif;