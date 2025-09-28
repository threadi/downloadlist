<?php
/**
 * File for handling plugin updates.
 *
 * @package download-list-block-with-icons
 */

namespace DownloadListWithIcons\Plugin;

// prevent direct access.
defined( 'ABSPATH' ) || exit;

use DownloadListWithIcons\Dependencies\easyTransientsForWordPress\Transients;
use DownloadListWithIcons\Plugin\Admin\Admin;
use WP_Post;
use WP_Query;

/**
 * Object to handle plugin updates.
 */
class Updates {
	/**
	 * Instance of this object.
	 *
	 * @var ?Updates
	 */
	private static ?Updates $instance = null;

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
	public static function get_instance(): Updates {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Initialize the iconset.
	 *
	 * @return void
	 */
	public function init(): void {
		add_action( 'plugins_loaded', array( $this, 'run' ) );
	}

	/**
	 * Check on each load if plugin-version has been changed.
	 * If yes, run appropriated functions for migrate to the new version.
	 *
	 * @return void
	 */
	public function run(): void {
		// get installed plugin-version (version of the actual files in this plugin).
		$installed_plugin_version = DL_VERSION;

		// get db-version (version which was last installed).
		$db_plugin_version = get_option( 'downloadlistVersion', '3.0.0' );

		// compare version if we are not in development-mode.
		if (
			(
				(
					function_exists( 'wp_is_development_mode' ) && false === wp_is_development_mode( 'plugin' )
				)
				|| ! function_exists( 'wp_is_development_mode' )
			)
			&& version_compare( $installed_plugin_version, $db_plugin_version, '>' )
		) {
			// initialize transients.
			Admin::get_instance()->configure_transients();

			// force refresh of css on every plugin update.
			$transient_obj = Transients::get_instance()->add();
			$transient_obj->set_action( array( 'DownloadListWithIcons\Plugin\Helper', 'generate_css' ) );
			$transient_obj->set_name( 'downloadlist_refresh_css' );
			$transient_obj->save();

			// run this on update from version before 3.4.0.
			if ( version_compare( $db_plugin_version, '3.4.0', '<' ) ) {
				$this->version340();
			}

			// run this on update from version before 4.0.0.
			if ( version_compare( $db_plugin_version, '4.0.0', '<' ) ) {
				$this->version400();
			}

			// save new plugin-version in DB.
			update_option( 'downloadlistVersion', $installed_plugin_version, true );
		}
	}

	/**
	 * Run on update to 3.4.0 or newer.
	 *
	 * @return void
	 */
	private function version340(): void {
		$query = array(
			'post_type'      => 'dl_icons',
			'post_status'    => array( 'any', 'trash' ),
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_query'     => array(
				'relation' => 'AND',
				array(
					'key'     => 'generic-downloadlist',
					'compare' => 'NOT EXISTS',
				),
				array(
					'key'     => 'icon',
					'compare' => 'NOT EXISTS',
				),
			),
		);
		$posts = new WP_Query( $query );
		foreach ( $posts->get_posts() as $post_id ) {
			// bail if post_id is WP_Post.
			if ( $post_id instanceof WP_Post ) {
				continue;
			}

			// update the entry.
			update_post_meta( absint( $post_id ), 'generic-downloadlist', 1 );
		}

		// remove deprecated setting.
		delete_option( 'downloadlistVersion' );
	}

	/**
	 * Run on update to 4.0.0 or newer.
	 *
	 * @return void
	 */
	private function version400(): void {
		// install settings.
		Settings::get_instance()->add_settings();
		\DownloadListWithIcons\Dependencies\easySettingsForWordPress\Settings::get_instance()->activation();
	}
}
