<?php
/**
 * Must-use plugin for the Playwright tests: registers a folder taxonomy for the media library
 * like folder plugins (e.g. Folderly) do.
 *
 * @package download-list-block-with-icons
 */

// prevent direct access.
defined( 'ABSPATH' ) || exit;

add_action(
	'init',
	function () {
		register_taxonomy(
			'playwright_folders',
			'attachment',
			array(
				'label'        => 'Playwright Folders',
				'hierarchical' => true,
				'public'       => false,
				'show_ui'      => true,
			)
		);
	}
);
