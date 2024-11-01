jQuery(document).ready( function() {

	/**
	 * Function to create client on Sugester that is used in various sources.
	 * after clicking on "Add to Sugester"
	 *
	 * @since 1.0.0
	 * @param int client_id Client id
	 * @param string source ['users', '']
	 */
	sugester_create_client = function( client_id, source ) {
		if ( SUGESTER['ajax_source'].indexOf(source) === -1 ) {
			sugester_error('sugester_create_client was called with undefined source: "' + source + '"');
			return;
		}

		var client_key = 'client' + client_id;
		var type = typeof(SUGESTER[ client_key ]);
		var value = SUGESTER[ client_key ];

		if ( type === 'boolean' && value ) {
			sugester_toastr.warning(SUGESTER_T['display_in_progress']);
		}
		else if ( type === 'undefined' || (type === 'boolean' && !value ) ) {
			// setting flag to declare that request is running.
			SUGESTER[ client_key ] = true;
			sugester_toastr.success(SUGESTER_T['display_started']);
			
			var data = {
				'action': 'sugester_create_client',
				'client_id': client_id,
				'source': source
			};

			jQuery.post(ajaxurl, data, function(response) {
				if ( ! response['success'] ) {
					sugester_toastr.error( response['data']['msg'] );
					// error, so we mark it as so
					SUGESTER[ client_key ] = false;
				}
				else {
					data = response['data'];
					jQuery('a#sugester_client_' + parseInt( client_id ) )
						.text( data['rename'] )
						.attr("href", data['url'])
						.attr("target", "_blank")
						.removeAttr("onclick")
						.removeAttr("id");
					
					sugester_toastr.success( data['msg'] );
					// Marking that process is finished
					delete SUGESTER[ client_key ];
				}
			});
		}
		else {
			// some undefined behaviour, let's display it
			sugester_error('sugester_create_client: type of client_key "'+client_key+'" is '+type);
		}
	}


	/**
	 * Tries to create all missing customers on Sugester.
	 * @since 1.0.0
	 */
	sugester_create_all_clients = function() {
		if ( SUGESTER['creating_clients'] ) {
			sugester_toastr.warning( SUGESTER_T['display_in_progress'])
		}
		else {
			SUGESTER['creating_clients'] = true;
			sugester_toastr.success( SUGESTER_T['display_started'] )

			var data = {
				'action': 'sugester_create_all_clients'
			}

			jQuery.post(ajaxurl, data, function(response) {
				if ( ! response['success'] ) {
					sugester_toastr.error( response['data']['msg'] );
				}
				else {
					sugester_toastr.success( response['data']['msg'] );
				}
				SUGESTER['creating_clients'] = false;
			})
		}

	}
});