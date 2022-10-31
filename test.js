jQuery( document ).ready(function() {
	wp.media.view.Modal.prototype.on( "open", function() {
		// prüfen ob unser eigener Filechooser aktiv ist
		console.log(jQuery('.downloadlistfilechooser').is(':visible'));
		if( false !== jQuery('.downloadlistfilechooser').is(':visible') ) {
			console.log("ok")
			// !!! wenn ja, dann die Ausgabe von diesem ergänzen um das zusätzliche Tab
			// -> dazu müsste das objekt des offenen modals ermittelt werden
			wp.media.view.MediaFrame.Select.prototype.browseRouter = function( routerView ) {
				routerView.set({
					upload: {
						text:     'a',
						priority: 20
					},
					browse: {
						text:     'b',
						priority: 40
					},
					my_tab: {
						text:     "My tab",
						priority: 60
					}
				});
			};
			// this forces a refresh of the content.
//			wp.media.frame.content.get().collection._requery( true );
		}
	});
});
