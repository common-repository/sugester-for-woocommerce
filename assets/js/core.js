/**
 * SUGESTER global variables.
 * Contains:
 *   'client{$client_id}': undefined - no request yet/finished
 *                         true      - request in progress
 *                         false     - request failed
 *   'order{$order_id}': undefined - no request yet/finished
 *                         true      - request in progress
 *                         false     - request failed
 *
 *   'creating_clients': is creating client in progress
 */
var SUGESTER = {
	ajax_source: [ 'users', 'user', 'order' ],
	creating_clients: false,
	creating_orders: false,
};

var SUGESTER_WC_DEBUG = false;

/**
 * Wrapper for logging.
 * @param mixed variable Variable to print
 */
function sugester_log( variable ) {
	if ( SUGESTER_WC_DEBUG ) {
		var date = new Date();
		var second = (date.getSeconds() < 10 ? '0' : '') + date.getSeconds(),
			minute = (date.getMinutes() < 10 ? '0' : '') + date.getMinutes(),
			hour   = (date.getHours()   < 10 ? '0' : '') + date.getHours(),
			day    = (date.getDate()    < 10 ? '0' : '') + date.getDate(),
			month  = (date.getMonth()   < 10 ? '0' : '') + date.getMonth(),
			year   = date.getFullYear();
		var timestamp = '['+year+'.'+month+'.'+day+' '+hour+':'+minute+':'+second+']';

		ret = timestamp;
		for (i = 0; i < arguments.length; i++) {
			var arg = arguments[i];
			ret += ' ' + (typeof(arg) === 'string' ? arg : JSON.stringify(arg));
		}
		console.log(ret);
	}
}


/**
 * Wrapper for errors displayed by Sugester plugin.
 *
 * @since 1.0.0
 * @param string message Message to be wrapped
 */
function sugester_error( message ) {
	sugester_toastr.error(SUGESTER_T['display_error'] + '<br>[ERROR: ' + message + ']');
}