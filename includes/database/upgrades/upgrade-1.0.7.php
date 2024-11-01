<?php
/**
 * Provided because several bugs were mentioned.
 * Triggers upgrade 1.0.5 just to be sure that it was run
 * @since 1.0.7
 */
function woocommerce_sugester_upgrade_1_0_7() {
	include_once( "upgrade-1.0.5.php" );
	if ( ! function_exists( 'woocommerce_sugester_upgrade_1_0_5' ) ) {
		return false;
	}

	return woocommerce_sugester_upgrade_1_0_5();
}