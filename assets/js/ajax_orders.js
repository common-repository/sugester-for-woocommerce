jQuery(document).ready( function() {

	/**
	 * Function to create order (deal) on Sugester that is used after clicking
	 * 'generate order'.
	 *
	 * @since 1.0.0
	 * @param int order_id Order id
	 */
	sugester_create_order = function(order_id) {
		var order_key = 'order' + order_id;
		var type = typeof(SUGESTER[ order_key ]);
		var value = SUGESTER[ order_key ];

		if (type === 'boolean' && value) {
			sugester_toastr.warning(SUGESTER_T['display_in_progress']);
		}
		else if (type === 'undefined' || (type === 'boolean' && !value)) {
			SUGESTER[ order_key ] = true;
			sugester_toastr.success(SUGESTER_T['display_started']); // todo: dodac jako metoda i napisac tez w ajax_clients.js

			var data = {
				'action': 'sugester_create_order',
				'order_id': order_id
			}

			jQuery.post(ajaxurl, data, function(response) {
				if ( !response['success'] ) {
					sugester_toastr.error(response['data']['msg']);
					SUGESTER[ order_key ] = false;
				}
				else {
					data = response['data'];

					jQuery('a#sugester_order_' + parseInt( order_id ) )
						.text( data['rename'] )
						.attr("href", data['url'])
						.attr("target", "_blank")
						.removeAttr("onclick")
						.removeAttr("id");

					sugester_toastr.success(data['msg']);
					// Marking that process is finished
					delete SUGESTER[ order_key ];
				}
			});
		}
		else {
			// some undefined behaviour, let's display it
			sugester_error('sugester_create_order: type of order_key "'+order_key+'" is '+type);
		}
	}


	/**
	 * Tries to create all missing orders on Sugester.
	 * @todo DRY
	 * @since 1.0.0
	 */
	sugester_create_all_orders = function() {
		if ( SUGESTER['creating_orders'] ) {
			sugester_toastr.warning( SUGESTER_T['display_in_progress'])
		}
		else {
			SUGESTER['creating_orders'] = true;
			sugester_toastr.success( SUGESTER_T['display_started'] )

			var data = {
				'action': 'sugester_create_all_orders'
			}

			jQuery.post(ajaxurl, data, function(response) {
				if ( ! response['success'] ) {
					sugester_toastr.error( response['data']['msg'] );
				}
				else {
					sugester_toastr.success( response['data']['msg'] );
				}
				SUGESTER['creating_orders'] = false;
			})
		}
	}
});