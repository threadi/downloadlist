<?php
/**
 * File to handle installer-tasks.
 *
 * @package download-list-block-with-icons
 */

namespace DownloadListWithIcons\Plugin;

// prevent direct access.
defined( 'ABSPATH' ) || exit;

use DownloadListWithIcons\Icons\Icons;

/**
 * Object to handle installer-tasks.
 */
class Installer {

	/**
	 * Instance of this object.
	 *
	 * @var ?Installer
	 */
	private static ?Installer $instance = null;

	/**
	 * Constructor for Init-Handler.
	 */
	private function __construct() {}

	/**
	 * Prevent cloning of this object.
	 *
	 * @return void
	 */
	private function __clone() { }

	/**
	 * Return the instance of this Singleton object.
	 */
	public static function get_instance(): Installer {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Initialize the installer: set up new sites in a multisite, if the plugin is activated in the network.
	 *
	 * @return void
	 */
	public function init(): void {
		add_action( 'wp_initialize_site', array( $this, 'initialize_site' ), 200 );
	}

	/**
	 * Run the activation on a new site in a multisite, if the plugin is activated in the network.
	 *
	 * @param \WP_Site $site The new site.
	 *
	 * @return void
	 */
	public function initialize_site( \WP_Site $site ): void {
		// bail if the plugin is not activated in the network.
		if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		if ( ! is_plugin_active_for_network( plugin_basename( DL_PLUGIN ) ) ) {
			return;
		}

		// run the activation on the new site.
		switch_to_blog( (int) $site->blog_id );
		$this->activation();
		restore_current_blog();
	}

	/**
	 * Run during installation/activation of this plugin.
	 *
	 * On activation in the network of a multisite it runs for every site.
	 *
	 * @param bool $network_wide True if the plugin is activated in the network.
	 *
	 * @return void
	 */
	public function activation( bool $network_wide = false ): void {
		// run it for every site, if the plugin is activated in the network.
		if ( $network_wide && is_multisite() ) {
			foreach ( get_sites(
				array(
					'fields' => 'ids',
					'number' => 0,
				)
			) as $blog_id ) {
				switch_to_blog( absint( $blog_id ) );
				$this->activation_for_site();
				restore_current_blog();
			}
			return;
		}

		// run it for the actual site.
		$this->activation_for_site();
	}

	/**
	 * Run the activation tasks for the actual site.
	 *
	 * @return void
	 */
	private function activation_for_site(): void {
		if ( ! get_option( 'downloadlistVersion', false ) ) {
			add_option( 'downloadlistVersion', DL_VERSION, '', true );
		}

		// install settings.
		$settings_obj = Settings::get_instance();
		$settings_obj->add_settings();
		$settings_obj->get_settings_obj()->activation();

		// initialize our own post-type and taxonomies during installation.
		Taxonomies::get_instance()->register();
		Icons::get_instance()->register();

		// add generic iconset.
		Helper::add_generic_iconsets();

		// generate icons and styles.
		Helper::regenerate_icons();
		Helper::generate_css();
	}

	/**
	 * Run during deactivation of this plugin.
	 *
	 * @return void
	 */
	public function deactivation(): void {}
}
