<?php
/**
 * File which holds the autoloader for this plugin.
 *
 * @package downloadlist
 */

/**
 * Add autoloader for each php-class in this plugin.
 */
spl_autoload_register( 'downloadlist_autoloader' );

/**
 * The autoloader itself. Called by PHP for each requested class-name.
 *
 * @param string $class_name The class-name.
 *
 * @return void
 */
function downloadlist_autoloader( string $class_name ): void {
	// If the specified $class_name does not include our namespace, duck out.
	if ( false === strpos( $class_name, 'downloadlist' ) ) {
		return;
	}

	// Split the class name into an array to read the namespace and class.
	$file_parts = explode( '\\', $class_name );

	// Do a reverse loop through $file_parts to build the path to the file.
	$namespace        = '';
	$filepath         = '';
	$file_name        = '';
	$file_parts_count = count( $file_parts );
	for ( $i = 1; $i < $file_parts_count; $i++ ) {
		// Read the current component of the file part.
		$current = $file_parts[ $i ];
		$current = str_ireplace( '_', '-', $current );

		// If we're at the first entry, then we're at the filename.
		$file_name = '';
		if ( $file_parts_count - 1 === $i ) {
			$file_name = $current . '.php';
		} else {
			$namespace = $namespace . '/' . $current;
		}
	}

	if ( ! empty( $file_name ) ) {
		// Now build a path to the file using mapping to the file location.
		$filepath_pre  = trailingslashit( dirname( DL_PLUGIN ) . '/classes' . $namespace );
		foreach( array( 'class', 'interface' ) as $type ) {
			$filepath = $filepath_pre.$type.'-'.strtolower($file_name);

			// If the file exists in the specified path, then include it.
			if (file_exists($filepath)) {
				include_once $filepath;
			}
		}
	}
}
