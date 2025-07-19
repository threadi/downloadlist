<?php
/**
 * File to handle template-tasks of this plugin.
 *
 * @package download-list-block-with-icons
 */

namespace DownloadListWithIcons\Plugin;

// prevent direct access.
defined( 'ABSPATH' ) || exit;

/**
 * Handler for templates.
 */
class Templates {

	/**
	 * Instance of this object.
	 *
	 * @var ?Templates
	 */
	private static ?Templates $instance = null;

	/**
	 * Constructor for Init-Handler.
	 */
	private function __construct() {}

	/**
	 * Prevent cloning of this object.
	 *
	 * @return void
	 */
	private function __clone() {}

	/**
	 * Return the instance of this Singleton object.
	 */
	public static function get_instance(): Templates {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Initialize the templates.
	 *
	 * @return void
	 */
	public function init(): void {
		// check for changed templates.
		add_action( 'admin_init', array( $this, 'check_child_theme_templates' ) );
	}

	/**
	 * Check for changed templates of our own plugin in the child-theme, if one is used.
	 *
	 * @return void
	 */
	public function check_child_theme_templates(): void {
		// get transients object.
		$transients_obj = Transients::get_instance();

		// bail if user has not the capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			$transients_obj->get_transient_by_name( 'downloadlist_old_templates' )->delete();
			return;
		}

		// bail if it is not a child-theme.
		if ( ! is_child_theme() ) {
			$transients_obj->get_transient_by_name( 'downloadlist_old_templates' )->delete();
			return;
		}

		// get path for child-theme-templates-directory and check its existence.
		$path = trailingslashit( get_stylesheet_directory() ) . 'download-list-block-with-icons/';
		if ( ! file_exists( $path ) ) {
			$transients_obj->get_transient_by_name( 'downloadlist_old_templates' )->delete();
			return;
		}

		// get all files from child-theme-templates-directory.
		$files = Helper::get_files_from_directory( $path );
		if ( empty( $files ) ) {
			$transients_obj->get_transient_by_name( 'downloadlist_old_templates' )->delete();
			return;
		}

		// get list of all templates of this plugin.
		$plugin_files = Helper::get_files_from_directory( Helper::get_plugin_path() . 'templates/' );

		// collect warnings.
		$warnings = array();

		// set headers to check.
		$headers = array(
			'version' => 'Version',
		);

		// check the files from child-theme and compare them with our own.
		foreach ( $files as $file ) {
			// bail if file does not exist in our plugin.
			if ( ! isset( $plugin_files[ basename( $file ) ] ) ) {
				continue;
			}

			// get the file-version-data of the child-template-file.
			$file_data = get_file_data( $file, $headers );

			// bail if version does not exist.
			if ( ! isset( $file_data['version'] ) ) {
				continue;
			}

			// if version is empty, show warning (aka: no setting found).
			if ( empty( $file_data['version'] ) ) {
				$warnings[] = $file;
			} elseif ( ! empty( $plugin_files[ basename( $file ) ] ) ) {
				// get data of the original template.
				$plugin_file_data = get_file_data( $plugin_files[ basename( $file ) ], $headers );

				// bail if no version is set in original.
				if ( ! isset( $plugin_file_data['version'] ) ) {
					continue;
				}

				// trigger warning for this file.
				if ( version_compare( $plugin_file_data['version'], $file_data['version'], '>' ) ) {
					$warnings[] = $file;
				}
			}
		}

		if ( ! empty( $warnings ) ) {
			// generate html-list of the files.
			$html_list = '<ul>';
			foreach ( $warnings as $file ) {
				$html_list .= '<li>' . esc_html( basename( $file ) ) . '</li>';
			}
			$html_list .= '</ul>';

			// show a transient.
			$transient_obj = $transients_obj->add();
			$transient_obj->set_name( 'downloadlist_old_templates' );
			$transient_obj->set_message( __( '<strong>You are using a child theme that contains outdated template files for the plugin "Download List Block with Icons".</strong> Please compare the following files in your child-theme with the one this plugin provides:', 'download-list-block-with-icons' ) . $html_list . '<strong>' . __( 'Hints:', 'download-list-block-with-icons' ) . '</strong><br>' . __( 'The version-number in the header of the files must match.', 'download-list-block-with-icons' ) . '<br>' . __( 'If you have any questions about this, talk to the technical administrator of your website.', 'download-list-block-with-icons' ) );
			$transient_obj->set_type( 'error' );
			$transient_obj->set_dismissible_days( 14 );
			$transient_obj->save();
		} else {
			$transients_obj->get_transient_by_name( 'downloadlist_old_templates' )->delete();
		}
	}
}
