jQuery(document).ready(function($) {
	$('body.post-type-dl_icons h1').each(function() {
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
