/**
 * Copy the icon fonts from node_modules into the plugin (part of "npm run build").
 *
 * Uses only Node.js, so it works on every operating system.
 */
const fs = require( 'fs' );
const path = require( 'path' );

const root = path.resolve( __dirname, '..' );

/**
 * List of files and directories to copy: [ source in node_modules, target in the plugin ].
 */
const copies = [
	[ '@fortawesome/fontawesome-free/css/all.css', 'css/fontawesome/fontawesome.css' ],
	[ '@fortawesome/fontawesome-free/webfonts', 'css/webfonts' ],
	[ 'bootstrap-icons/font/bootstrap-icons.css', 'css/bootstrap/bootstrap-icons.css' ],
	[ 'bootstrap-icons/font/fonts', 'css/bootstrap/fonts' ],
];

for ( const [ source, target ] of copies ) {
	const from = path.join( root, 'node_modules', source );
	const to = path.join( root, target );
	if ( ! fs.existsSync( from ) ) {
		console.error( `Missing ${ from } - run "npm ci" first.` );
		process.exit( 1 );
	}
	fs.mkdirSync( path.dirname( to ), { recursive: true } );
	fs.cpSync( from, to, { recursive: true } );
}
