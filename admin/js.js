jQuery(document).ready(function($) {
	// image handling: on upload button click
	$('body.post-type-dl_icons').on('click', '.downloadlist-image-choose', function (e) {
		e.preventDefault();
		let button = $(this),
			custom_uploader = wp.media({
				title: 'Insert image',
				library: {
					type: 'image'
				},
				button: {
					text: 'Use this image' // button label text
				},
				multiple: false
			}).on('select', function () { // it also has "open" and "close" events
				var attachment = custom_uploader.state().get('selection').first().toJSON();
				button.html('<img src="' + attachment.url + '">').next().show().next().val(attachment.id);
			}).open();

	});

	// image handling: on remove button click
	$('body.post-type-dl_icons').on('click', '.downloadlist-image-remove', function (e) {
		e.preventDefault();
		let button = $(this);
		button.next().val(''); // emptying the hidden field
		button.hide().prev().html('Upload image');
	});
});
