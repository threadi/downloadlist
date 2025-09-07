<?php
/**
 * This file contains the tasks for WP admin.
 *
 * @package download-list-block-with-icons
 */

namespace DownloadListWithIcons\Plugin\Admin;

// prevent direct access.
defined( 'ABSPATH' ) || exit;

use DownloadListWithIcons\Plugin\Helper;
use DownloadListWithIcons\Plugin\Templates;
use DownloadListWithIcons\Plugin\Transients;

/**
 * Initialize the WP admin support.
 */
class Admin {
	/**
	 * Instance of actual object.
	 *
	 * @var ?Admin
	 */
	private static ?Admin $instance = null;

	/**
	 * Constructor, not used as this a Singleton object.
	 */
	private function __construct() {}

	/**
	 * Prevent cloning of this object.
	 *
	 * @return void
	 */
	private function __clone() {}

	/**
	 * Return instance of this object as singleton.
	 *
	 * @return Admin
	 */
	public static function get_instance(): Admin {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Initialize this object.
	 *
	 * @return void
	 */
	public function init(): void {
		// initialize the template support in backend.
		Templates::get_instance()->init();

		// initialize the transient support in backend.
		Transients::get_instance()->init();

		// initialize help system.
		Help_System::get_instance()->init();

		// use hooks.
		add_action( 'admin_enqueue_scripts', array( $this, 'add_styles_and_js_admin' ), PHP_INT_MAX );
		add_filter( 'plugin_action_links_' . plugin_basename( DL_PLUGIN ), array( $this, 'add_setting_link' ) );
		add_filter( 'plugin_row_meta', array( $this, 'add_row_meta_links' ), 10, 2 );
		add_action( 'admin_init', array( $this, 'check_php' ) );
		add_filter( 'admin_footer_text', array( $this, 'show_plugin_hint_in_footer' ) );
	}

	/**
	 * Add our own styles and js in backend.
	 *
	 * @return void
	 */
	public function add_styles_and_js_admin(): void {
		// admin-specific styles.
		wp_enqueue_style(
			'downloadlist-admin',
			trailingslashit( plugin_dir_url( DL_PLUGIN ) ) . 'admin/styles.css',
			array(),
			Helper::get_file_version( trailingslashit( plugin_dir_path( DL_PLUGIN ) ) . 'admin/styles.css' ),
		);

		// backend-JS.
		wp_enqueue_script(
			'downloadlist-admin',
			trailingslashit( plugin_dir_url( DL_PLUGIN ) ) . 'admin/js.js',
			array( 'jquery' ),
			Helper::get_file_version( trailingslashit( plugin_dir_path( DL_PLUGIN ) ) . '/admin/js.js' ),
			true
		);

		// embed media if we edit our own cpt, if not already done.
		$post_id = absint( filter_input( INPUT_GET, 'post', FILTER_SANITIZE_NUMBER_INT ) );
		if ( ! did_action( 'wp_enqueue_media' ) && 'dl_icons' === get_post_type( $post_id ) ) {
			wp_enqueue_media();
		}

		// add php-vars to our js-script.
		wp_localize_script(
			'downloadlist-admin',
			'downloadlistAdminJsVars',
			array(
				'ajax_url'          => admin_url( 'admin-ajax.php' ),
				'dismiss_nonce'     => wp_create_nonce( 'downloadlist-dismiss-nonce' ),
				'title'             => __( 'Insert image', 'download-list-block-with-icons' ),
				'lbl_button'        => __( 'Use this image', 'download-list-block-with-icons' ),
				'lbl_upload_button' => __( 'Upload image', 'download-list-block-with-icons' ),
				'title_rate_us'     => __( 'Rate this plugin', 'download-list-block-with-icons' ),
			)
		);

		// add ja-variables for block editor.
		wp_add_inline_script(
			'downloadlist-list-editor-script',
			'window.downloadlist_config = ' . wp_json_encode(
				array(
					'iconsets_url' => trailingslashit( get_admin_url() ) . 'edit-tags.php?taxonomy=dl_icon_set&post_type=dl_icons',
					'list_url'     => trailingslashit( get_admin_url() ) . 'edit-tags.php?taxonomy=dl_icon_lists&post_type=attachment',
					'support_url'  => Helper::get_support_url(),
				)
			),
			'before'
		);
	}

	/**
	 * Add link to icon management in plugin list.
	 *
	 * @param array<int,string> $links List of links.
	 * @return array<int,string>
	 */
	public function add_setting_link( array $links ): array {
		// create link to custom icon list.
		$url = add_query_arg(
			array(
				'post_type' => 'dl_icons',
			),
			admin_url() . 'edit.php'
		);

		// adds the link to the end of the array.
		$links[] = '<a href="' . esc_url( $url ) . '">' . __( 'Manage icons', 'download-list-block-with-icons' ) . '</a>';

		// get language-dependent URL for the how-to.
		$url = 'https://github.com/threadi/downloadlist/tree/master/docs/how_to_use.md';
		if ( str_starts_with( 'de_', get_locale() ) ) {
			$url = 'https://github.com/threadi/downloadlist/tree/master/docs/how_to_use_de.md';
		}

		// add the link to the list.
		$links[] = '<a href="' . esc_url( $url ) . '" target="_blank" style="font-weight:bold">' . esc_html__( 'How to use', 'download-list-block-with-icons' ) . '</a>';

		// return resulting list.
		return $links;
	}

	/**
	 * Add links in row meta in plugin list.
	 *
	 * @param array<string,string> $links List of links.
	 * @param string               $file The requested plugin file name.
	 *
	 * @return array<string,string>
	 */
	public function add_row_meta_links( array $links, string $file ): array {
		// bail if this is not our plugin.
		if ( DL_PLUGIN !== WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $file ) {
			return $links;
		}

		// add our custom links.
		$row_meta = array(
			'support' => '<a href="' . esc_url( Helper::get_plugin_support_url() ) . '" target="_blank" title="' . esc_attr__( 'Support Forum', 'download-list-block-with-icons' ) . '">' . esc_html__( 'Support Forum', 'download-list-block-with-icons' ) . '</a>',
		);

		/**
		 * Filter the links in row meta of our plugin in plugin list.
		 *
		 * @since 3.8.0 Available since 3.8.0.
		 * @param array $row_meta List of links.
		 */
		$row_meta = apply_filters( 'downloadlist_plugin_row_meta', $row_meta );

		// return the resulting list of links.
		return array_merge( $links, $row_meta );
	}

	/**
	 * Check if website is using a valid SSL and show warning if not.
	 *
	 * @return void
	 */
	public function check_php(): void {
		// get transients object.
		$transients_obj = Transients::get_instance();

		// bail if WordPress is in developer mode.
		if ( function_exists( 'wp_is_development_mode' ) && wp_is_development_mode( 'plugin' ) ) {
			$transients_obj->delete_transient( $transients_obj->get_transient_by_name( 'downloadlist_php_hint' ) );
			return;
		}

		// bail if PHP >= 8.1 is used.
		if ( PHP_VERSION_ID > 80100 ) { // @phpstan-ignore smaller.alwaysFalse
			$transients_obj->delete_transient( $transients_obj->get_transient_by_name( 'downloadlist_php_hint' ) );
			return;
		}

		// show hint for necessary configuration to restrict access to application files.
		$transient_obj = Transients::get_instance()->add();
		$transient_obj->set_type( 'error' );
		$transient_obj->set_name( 'downloadlist_php_hint' );
		$transient_obj->set_dismissible_days( 90 );
		$transient_obj->set_message( '<strong>' . __( 'Your website is using an outdated PHP-version!', 'download-list-block-with-icons' ) . '</strong><br>' . __( 'Future versions of <i>Download List with Icons</i> will no longer be compatible with PHP 8.0 or older. These versions <a href="https://www.php.net/supported-versions.php" target="_blank">are outdated</a> since December 2023. To continue using the plugins new features, please update your PHP version.', 'download-list-block-with-icons' ) . '<br>' . __( 'Talk to your hosters support team about this.', 'download-list-block-with-icons' ) );
		$transient_obj->save();
	}

	/**
	 * Show hint in footer in backend on listing and single view of positions there.
	 *
	 * @param string $content The actual footer content.
	 *
	 * @return string
	 */
	public function show_plugin_hint_in_footer( string $content ): string {
		global $pagenow;

		// show specific text on media pages.
		if ( 'upload.php' === $pagenow ) {
			// show hint for our plugin.
			/* translators: %1$s will be replaced by the plugin name. */
			return $content . ' ' . sprintf( __( 'This page has been expanded by the plugin %1$s.', 'download-list-block-with-icons' ), '<em>' . Helper::get_plugin_name() . '</em>' );
		}

		// get requested taxonomy.
		$taxonomy = filter_input( INPUT_GET, 'taxonomy', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		// get requested post type.
		$post_type = filter_input( INPUT_GET, 'post_type', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		// bail if this is not the listing or our page.
		if ( 'dl_icon_lists' !== $taxonomy && 'dl_icons' !== $post_type ) {
			return $content;
		}

		// show hint for our plugin.
		/* translators: %1$s will be replaced by the plugin name. */
		return $content . ' ' . sprintf( __( 'This page is provided by the plugin %1$s.', 'download-list-block-with-icons' ), '<em>' . Helper::get_plugin_name() . '</em>' );
	}
}
