import domReady from '@wordpress/dom-ready';
import { createRoot } from '@wordpress/element';
import { DataViews } from '@wordpress/dataviews/wp' ;

const App = () => {
	// "view" and "setView" definition
	// "processedData" and "paginationInfo" definition
	// "actions" definition
	let processedData = wp.data.select('core').getEntityRecords('taxonomy', 'dl_icon_set', {per_page: 100})
	console.log(processedData);

	let fields = {};

	let view = {}

	return (
		<DataViews
			data={ processedData }
			fields={ fields }
			view={ view }
			onChangeView={ setView }
			defaultLayouts={ defaultLayouts }
			actions={ actions }
			paginationInfo={ paginationInfo }
		/>
	);
};

domReady( () => {
	const root = createRoot(
		document.getElementById( 'download-list-iconsets' )
	);
	root.render( <App /> );
} );
