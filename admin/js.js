jQuery(document).ready(function($) {
	$('body.post-type-dl_icons h1, body.settings_page_downloadlist_settings h1').each(function() {
		let button = document.createElement('a');
		button.className = 'review-hint-button page-title-action';
		button.href = 'https://wordpress.org/plugins/download-list-block-with-icons/#reviews';
		button.innerHTML = downloadlistAdminJsVars.title_rate_us;
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
				button.html('<img src="' + attachment.url + '">').removeClass('button button-primary').next().show().next().val(attachment.id);
			}).open();

	});

	// image handling: on remove button click.
	$('body.post-type-dl_icons').on('click', '.downloadlist-image-remove', function (e) {
		e.preventDefault();
		let button = $(this);
		button.next().val('');
		button.hide().prev().html(downloadlistAdminJsVars.lbl_upload_button).addClass('button button-primary');
	});

	// save to hide transient-messages via ajax-request
	$('div[data-dismissible] button.notice-dismiss').on('click',
		function (event) {
			event.preventDefault();
			let $this = $(this);
			let attr_value, option_name, dismissible_length, data;
			attr_value = $this.closest('div[data-dismissible]').attr('data-dismissible').split('-');

			// Remove the dismissible length from the attribute value and rejoin the array.
			dismissible_length = attr_value.pop();
			option_name = attr_value.join('-');
			data = {
				'action': 'downloadlist_dismiss_admin_notice',
				'option_name': option_name,
				'dismissible_length': dismissible_length,
				'nonce': downloadlistAdminJsVars.dismiss_nonce
			};

			// run ajax request to save this setting
			$.post(downloadlistAdminJsVars.ajax_url, data);
			$this.closest('div[data-dismissible]').hide('slow');
		}
	);
});

/**
 * Inherit the settings to all blocks via AJAX.
 */
function downloadlist_inherit_settings() {
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
		}
	});
}

/**
 * Get info about inheriting progress.
 */
function downloadlist_inherit_settings_get_info() {
	jQuery.ajax( {
		type: "POST",
		url: downloadlistAdminJsVars.ajax_url,
		data: {
			'action': 'downloadlist_inherit_settings_get_info',
			'nonce': downloadlistAdminJsVars.get_inherit_info_nonce
		},
		success: function (data) {
			let count = parseInt( data[0] );
			let max = parseInt( data[1] );
			let running = parseInt( data[2] );
			let status = data[3];
			let dialog_config = data[4];

			// show progress.
			jQuery( '#progress' ).attr( 'value', (count / max) * 100 );
			jQuery( '#progress_status' ).html( status );

			/**
			 * If import is still running, get next info in xy ms.
			 * If import is not running and error occurred, show the error.
			 * If import is not running and no error occurred, show ok-message.
			 */
			if ( running > 0 ) {
				setTimeout( function () {
					downloadlist_inherit_settings_get_info()
				}, downloadlistAdminJsVars.info_timeout );
			}
			else {
				downloadlist_create_dialog( dialog_config );
			}
		}
	} )
}

/**
 * Helper to create a new dialog with given config.
 *
 * @param config
 */
function downloadlist_create_dialog( config ) {
	document.body.dispatchEvent(new CustomEvent("easy-dialog-for-wordpress", config));
}
