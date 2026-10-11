jQuery(document).ready(function($) {
	$('body.post-type-dl_icons h1, body.settings_page_downloadlist_settings h1').each(function() {
		let button = document.createElement('a');
		button.className = 'review-hint-button page-title-action';
		button.href = 'https://wordpress.org/plugins/download-list-block-with-icons/#reviews';
		button.textContent = downloadlistAdminJsVars.title_rate_us;
		button.target = '_blank';
		this.after(button);
	})

	// image handling: on upload button click.
	$('body.post-type-dl_icons').on('click', '.downloadlist-image-choose', function (e) {
		e.preventDefault();
		let button = $(this),
			custom_uploader = wp.media({
				title: downloadlistAdminJsVars.title,
				library: {
					type: 'image'
				},
				button: {
					text: downloadlistAdminJsVars.lbl_button
				},
				multiple: false
			}).on('select', function () {
				var attachment = custom_uploader.state().get('selection').first().toJSON();
				// build the image as element, so the URL is never interpreted as HTML.
				button.empty().append( $( '<img>' ).attr( { src: attachment.url, alt: '' } ) ).removeClass('button button-primary').next().show().next().val(parseInt(attachment.id, 10) || '');
			}).open();

	});

	// image handling: on remove button click.
	$('body.post-type-dl_icons').on('click', '.downloadlist-image-remove', function (e) {
		e.preventDefault();
		let button = $(this);
		button.next().val('');
		button.hide().prev().text(downloadlistAdminJsVars.lbl_upload_button).addClass('button button-primary');
	});
});

/**
 * Marker whether the request to inherit the settings has been finished.
 *
 * @type {boolean}
 */
let downloadlist_inherit_finished = false;

/**
 * Count of requests for info about the progress (to stop after an unexpected long time).
 *
 * @type {number}
 */
let downloadlist_inherit_info_count = 0;

/**
 * Marker whether the inheriting has been stopped because of an error.
 *
 * @type {boolean}
 */
let downloadlist_inherit_failed = false;

/**
 * Inherit the settings to all blocks via AJAX.
 */
function downloadlist_inherit_settings() {
	downloadlist_inherit_finished = false;
	downloadlist_inherit_failed = false;
	downloadlist_inherit_info_count = 0;

	// send request.
	jQuery.ajax({
		url: downloadlistAdminJsVars.ajax_url,
		type: 'POST',
		data: {
			'action': 'downloadlist_inherit_settings',
			'nonce': downloadlistAdminJsVars.inherit_settings_nonce
		},
		beforeSend: function() {
			// show progress.
			let dialog_config = {
				detail: {
					className: 'eml',
					title: downloadlistAdminJsVars.title_inherit_progress,
					progressbar: {
						active: true,
						progress: 0,
						id: 'progress',
						label_id: 'progress_status'
					},
				}
			}
			downloadlist_create_dialog( dialog_config );

			// get info about progress.
			setTimeout(function() { downloadlist_inherit_settings_get_info() }, downloadlistAdminJsVars.info_timeout);
		},
		complete: function() {
			downloadlist_inherit_finished = true;
		},
		error: function( jqXHR ) {
			downloadlist_inherit_failed = true;
			downloadlist_show_error( jqXHR );
		}
	});
}

/**
 * Get info about inheriting progress.
 */
function downloadlist_inherit_settings_get_info() {
	// bail if the inheriting failed (the error is already shown).
	if ( downloadlist_inherit_failed ) {
		return;
	}
	downloadlist_inherit_info_count++;
	jQuery.ajax( {
		type: "POST",
		url: downloadlistAdminJsVars.ajax_url,
		data: {
			'action': 'downloadlist_inherit_settings_get_info',
			'nonce': downloadlistAdminJsVars.get_inherit_info_nonce
		},
		success: function (data) {
			// bail if the inheriting failed in the meantime.
			if ( downloadlist_inherit_failed ) {
				return;
			}

			// bail if the response is not the expected list.
			if ( ! Array.isArray( data ) || data.length < 5 ) {
				downloadlist_show_error( null );
				return;
			}

			let count = parseInt( data[0], 10 ) || 0;
			let max = parseInt( data[1], 10 ) || 0;
			let running = parseInt( data[2], 10 ) || 0;
			let status = String( data[3] || '' );
			let dialog_config = data[4];

			// show progress.
			jQuery( '#progress' ).attr( 'value', max > 0 ? (count / max) * 100 : 0 );
			jQuery( '#progress_status' ).text( status );

			/**
			 * If inheriting is still running (or has not started yet), get next info in xy ms.
			 * Stop after 3000 requests (10 minutes with the default timeout).
			 * If inheriting is not running anymore, show ok-message.
			 */
			if ( ( running > 0 || ! downloadlist_inherit_finished ) && downloadlist_inherit_info_count < 3000 ) {
				setTimeout( function () {
					downloadlist_inherit_settings_get_info()
				}, downloadlistAdminJsVars.info_timeout );
			}
			else {
				downloadlist_create_dialog( dialog_config );
			}
		},
		error: function( jqXHR ) {
			downloadlist_show_error( jqXHR );
		}
	} )
}

/**
 * Show an error in the dialog.
 *
 * @param jqXHR The request object or null.
 */
function downloadlist_show_error( jqXHR ) {
	let message = downloadlistAdminJsVars.txt_error;
	if ( jqXHR && jqXHR.responseJSON && jqXHR.responseJSON.data && jqXHR.responseJSON.data.message ) {
		message = jqXHR.responseJSON.data.message;
	}
	downloadlist_create_dialog( {
		detail: {
			title: downloadlistAdminJsVars.title_error,
			texts: [ '<p>' + jQuery( '<div>' ).text( message ).html() + '</p>' ],
			buttons: [
				{
					'action': 'closeDialog();',
					'variant': 'primary',
					'text': downloadlistAdminJsVars.lbl_ok
				}
			]
		}
	} );
}

/**
 * Helper to create a new dialog with given config.
 *
 * @param config
 */
function downloadlist_create_dialog( config ) {
	document.body.dispatchEvent(new CustomEvent("easy-dialog-for-wordpress", config));
}
