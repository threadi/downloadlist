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
		// run after the settings have been registered (on init with priority 10).
		add_action( 'init', array( $this, 'run' ), 20 );
	}

	/**
	 * Check each load if plugin-version has been changed.
	 * If yes, run appropriated functions for migrate to the new version.
	 *
	 * @return void
	 */
	public function run(): void {
		// get installed plugin-version (version of the actual files in this plugin).
		$installed_plugin_version = DL_VERSION;

		// get db-version (version, which was last installed).
		$db_plugin_version = get_option( 'downloadlistVersion', '3.0.0' );

		// do not run migrations in development-mode (the version number is not set there).
		if ( Helper::is_development_mode_active() ) {
			return;
		}

		// bail if the version number is not a valid version (e.g. the placeholder in a checkout).
		if ( ! preg_match( '/^\d+\.\d+\.\d+/', $installed_plugin_version ) ) {
			return;
		}

		// compare version.
		if ( version_compare( $installed_plugin_version, $db_plugin_version, '>' ) ) {
			// bail if another request runs the migration at the moment (add_option() fails if the option exists).
			$running = absint( get_option( 'downloadlist_update_running', 0 ) );
			if ( $running > 0 && ( time() - $running ) > 10 * MINUTE_IN_SECONDS ) {
				// remove an old marker, e.g. after a timeout.
				delete_option( 'downloadlist_update_running' );
			}
			if ( ! add_option( 'downloadlist_update_running', time(), '', false ) ) {
				return;
			}

			// initialize transients.
			Admin::get_instance()->configure_transients();

			// force refresh of CSS on every plugin update.
			$transient_obj = Transients::get_instance()->add();
			$transient_obj->set_action( array( 'DownloadListWithIcons\Plugin\Helper', 'generate_css' ) );
			$transient_obj->set_name( 'downloadlist_refresh_css' );
			$transient_obj->save();

			// run this on an update from the version before 3.4.0.
			if ( version_compare( $db_plugin_version, '3.4.0', '<' ) ) {
				$this->version340();
			}

			// run this on an update from the version before 4.0.0.
			if ( version_compare( $db_plugin_version, '4.0.0', '<' ) ) {
				$this->version400();
			}

			// run this on an update from the version before 4.1.0.
			if ( version_compare( $db_plugin_version, '4.1.0', '<' ) ) {
				$this->version410();
			}

			// run this on an update from the version before 4.2.0.
			if ( version_compare( $db_plugin_version, '4.2.0', '<' ) ) {
				$this->version415();
			}

			// save new plugin-version in the database.
			update_option( 'downloadlistVersion', $installed_plugin_version, true );

			// remove the marker for the running migration.
			delete_option( 'downloadlist_update_running' );
		}
	}

	/**
	 * Run on an update to 3.4.0 or newer.
	 *
	 * @return void
	 */
	private function version340(): void {
		$query = array(
			'post_type'      => 'dl_icons',
			'post_status'    => array( 'any', 'trash' ),
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Necessary meta lookup.
				'relation' => 'AND',
				array(
					'key'     => 'generic-downloadlist',
					'compare' => 'NOT EXISTS',
				),
				array(
					'key'     => 'icon',
					'compare' => 'NOT EXISTS',
				),
				// custom icons always have a file type or a unicode, generic entries never.
				array(
					'key'     => 'file_type',
					'compare' => 'NOT EXISTS',
				),
				array(
					'key'     => 'unicode',
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
	 * Run on an update to 4.0.0 or newer.
	 *
	 * @return void
	 */
	private function version400(): void {
		// install settings (they are already registered, see init()).
		Settings::get_instance()->get_settings_obj()->activation();
	}

	/**
	 * Run on an update to 4.1.0 or newer.
	 *
	 * @return void
	 */
	private function version410(): void {
		// initialize our own post-type and taxonomies during installation.
		Taxonomies::get_instance()->register();
		Helper::add_generic_iconsets();
		Helper::generate_css();
	}

	/**
	 * Run on an update to 4.2.0 or newer.
	 *
	 * - Use an own setting for the target of the download button (it shared the setting of the link before).
	 * - Remove duplicate generic icon entries, which have been added on each activation or reset before.
	 * - Add the new settings for preview images.
	 *
	 * @return void
	 */
	private function version415(): void {
		// add the new settings with their default values (existing settings are not changed).
		Settings::get_instance()->get_settings_obj()->activation();

		// copy the value of the shared setting to the new setting for the download button.
		if ( false === get_option( 'downloadlist_download_button_browser_target', false ) ) {
			add_option( 'downloadlist_download_button_browser_target', (string) get_option( 'downloadlist_link_browser_target', '' ) );
		}

		// get all generic icon entries.
		Taxonomies::get_instance()->register();
		$query = array(
			'post_type'      => 'dl_icons',
			'post_status'    => array( 'any', 'trash' ),
			'posts_per_page' => -1,
			'orderby'        => 'ID',
			'order'          => 'ASC',
			'fields'         => 'ids',
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Necessary meta lookup, runs only once.
				'relation' => 'AND',
				array(
					'key'     => 'generic-downloadlist',
					'compare' => 'EXISTS',
				),
				array(
					'key'     => 'file_type',
					'compare' => 'NOT EXISTS',
				),
			),
		);
		$posts = new WP_Query( $query );

		// published entries first, so they are kept (only they are used for the styles).
		$post_ids = array_map( 'absint', $posts->get_posts() ); // @phpstan-ignore argument.type
		usort(
			$post_ids,
			function ( int $a, int $b ): int {
				$by_status = ( 'publish' === get_post_status( $b ) ? 1 : 0 ) <=> ( 'publish' === get_post_status( $a ) ? 1 : 0 );
				return 0 !== $by_status ? $by_status : $a <=> $b;
			}
		);

		// keep the first entry per iconset and delete the others.
		$kept = array();
		foreach ( $post_ids as $post_id ) {
			$terms = wp_get_object_terms( $post_id, 'dl_icon_set', array( 'fields' => 'ids' ) );
			if ( ! is_array( $terms ) || empty( $terms ) ) {
				continue;
			}
			$term_id = absint( $terms[0] );
			if ( ! isset( $kept[ $term_id ] ) ) {
				$kept[ $term_id ] = $post_id;
				continue;
			}
			wp_delete_post( $post_id, true );
		}
	}
}
