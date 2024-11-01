<?php

/**
 * Wrapper for error logging. Run php_logger to access these information.
 * @since 1.0.0
 * @param mixed $var Variable to log.
 */
function sugester_log( $var, $json = true ) {
	if ( SUGESTER_WC_DEBUG_LOG ) {
		/**
		 * @since 1.0.3 - PHP backwards compatibility
		 */
		$debug = debug_backtrace();
		$debug = $debug[1];
		$msg = ( $json ? json_encode( $var ) : print_r( $var, true ) );
		@error_log( 'SUGESTER: '.$debug['function'].': '.$msg );
	}
}


/**
 * Wrapper for error logging. Run php_logger to access these information.
 * @since 1.0.0
 * @param mixed $var Variable to log.
 */
function sugester_error( $var, $json = true ) {
	if ( SUGESTER_WC_DEBUG_LOG ) {
		/**
		 * @since 1.0.3 - PHP backwards compatibility
		 */
		$debug = debug_backtrace();
		$debug = $debug[1];
		$msg = ( $json ? json_encode( $var ) : print_r( $var, true ) );
		@error_log( 'SUGESTER FATAL: '.$debug['function'].': '.$msg );
	}
}