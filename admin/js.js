jQuery(document).ready(function($) {
	// image handling: on upload button click.
	$('body.post-type-dl_icons').on('click', '.downloadlist-image-choose', function (e) {
		e.preventDefault();
		let button = $(this),
			custom_uploader = wp.media({
				title: 'Insert image',
				library: {
					type: 'image'
				},
				button: {
					text: 'Use this image'
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
		button.hide().prev().html('Upload image').addClass('button button-primary');
	});
});
