<?php
/**
 * This file defines the settings for this plugin.
 *
 * @package download-list-block-with-icons
 */

namespace DownloadListWithIcons\Plugin;

// prevent direct access.
defined( 'ABSPATH' ) || exit;

use DownloadListWithIcons\Dependencies\easySettingsForWordPress\Fields\Button;
use DownloadListWithIcons\Dependencies\easySettingsForWordPress\Fields\Checkbox;
use DownloadListWithIcons\Dependencies\easySettingsForWordPress\Fields\Select;
use DownloadListWithIcons\Dependencies\easySettingsForWordPress\Fields\SelectPostTypeObject;
use DownloadListWithIcons\Dependencies\easySettingsForWordPress\Fields\Text;
use DownloadListWithIcons\Dependencies\easyTransientsForWordPress\Transients;
use DownloadListWithIcons\Iconsets\Iconsets;
use WP_Query;

/**
 * Object which handles the settings of this plugin.
 */
class Settings {

	/**
	 * Instance of actual object.
	 *
	 * @var ?Settings
	 */
	private static ?Settings $instance = null;

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
	 * @return Settings
	 */
	public static function get_instance(): Settings {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Initialize the settings.
	 *
	 * @return void
	 */
	public function init(): void {
		// use hooks.
		add_action( 'init', array( $this, 'add_settings' ) );
		add_action( 'wp_ajax_downloadlist_inherit_settings', array( $this, 'inherit_settings_via_ajax' ) );
		add_action( 'wp_ajax_downloadlist_inherit_settings_get_info', array( $this, 'get_inherit_settings_progress_info' ) );
	}

	/**
	 * Return the menu slug for the settings.
	 *
	 * @return string
	 */
	public function get_menu_slug(): string {
		return 'downloadlist_settings';
	}

	/**
	 * Return the php page the settings will be using.
	 *
	 * @return string
	 */
	private function get_php_page(): string {
		return 'options-general.php';
	}

	/**
	 * Add our custom settings for this plugin.
	 *
	 * @return void
	 */
	public function add_settings(): void {
		/**
		 * Configure the basic settings object.
		 */
		$settings_obj = \DownloadListWithIcons\Dependencies\easySettingsForWordPress\Settings::get_instance();
		$settings_obj->set_slug( 'downloadlist' );
		$settings_obj->set_plugin_slug( DL_PLUGIN );
		$settings_obj->set_path( Helper::get_plugin_path() . '/app/Dependencies/easySettingsForWordPress/' );
		$settings_obj->set_url( Helper::get_plugin_url() . '/app/Dependencies/easySettingsForWordPress/' );
		$settings_obj->set_menu_title( __( 'Download List Block with Icons', 'download-list-block-with-icons' ) );
		$settings_obj->set_title( __( 'Settings for Download List Block with Icons', 'download-list-block-with-icons' ) );
		$settings_obj->set_menu_slug( $this->get_menu_slug() );
		$settings_obj->set_menu_parent_slug( $this->get_php_page() );

		/**
		 * Add the settings page.
		 */
		$settings_page = $settings_obj->add_page( $this->get_menu_slug() );

		/**
		 * Configure the tabs for this object.
		 */
		// the general tab.
		$general_tab = $settings_page->add_tab( 'downloadlist_general', 10 );
		$general_tab->set_name( 'downloadlist_general' );
		$general_tab->set_title( __( 'General Settings', 'download-list-block-with-icons' ) );
		$settings_page->set_default_tab( $general_tab );

		// the task tab.
		$tasks_tab = $settings_page->add_tab( 'downloadlist_tasks', 20 );
		$tasks_tab->set_name( 'downloadlist_tasks' );
		$tasks_tab->set_title( __( 'Tasks', 'download-list-block-with-icons' ) );

		// the helper tab.
		$helper_tab = $settings_page->add_tab( 'downloadlist_helper', 70 );
		$helper_tab->set_url( Helper::get_plugin_support_url() );
		$helper_tab->set_url_target( '_blank' );
		$helper_tab->set_tab_class( 'nav-tab-help dashicons dashicons-editor-help' );

		/**
		 * Configure all sections for this settings object.
		 */
		// the info section.
		$general_tab_info = $general_tab->add_section( 'info', 10 );
		$general_tab_info->set_title( _x( 'How to use', 'Settings', 'download-list-block-with-icons' ) );
		$general_tab_info->set_callback( array( $this, 'show_info' ) );
		$general_tab_info->set_setting( $settings_obj );

		// the icons section.
		$general_tab_icons = $general_tab->add_section( 'icons', 20 );
		$general_tab_icons->set_title( __( 'Icons', 'download-list-block-with-icons' ) );
		$general_tab_icons->set_setting( $settings_obj );

		// the link section.
		$general_tab_link = $general_tab->add_section( 'link', 30 );
		$general_tab_link->set_title( __( 'Link', 'download-list-block-with-icons' ) );
		$general_tab_link->set_setting( $settings_obj );

		// the download button section.
		$general_tab_db = $general_tab->add_section( 'download_button', 40 );
		$general_tab_db->set_title( __( 'Download button', 'download-list-block-with-icons' ) );
		$general_tab_db->set_setting( $settings_obj );

		// the advanced section.
		$general_tab_advanced = $general_tab->add_section( 'advanced', 50 );
		$general_tab_advanced->set_title( __( 'Advanced settings', 'download-list-block-with-icons' ) );
		$general_tab_advanced->set_setting( $settings_obj );

		// the tasks section.
		$tasks_tab_tasks = $tasks_tab->add_section( 'tasks', 10 );
		$tasks_tab_tasks->set_title( __( 'Tasks', 'download-list-block-with-icons' ) );
		$tasks_tab_tasks->set_setting( $settings_obj );

		// add setting.
		$hide_icon_setting = $settings_obj->add_setting( 'downloadlist_hide_icons' );
		$hide_icon_setting->set_section( $general_tab_icons );
		$hide_icon_setting->set_type( 'integer' );
		$hide_icon_setting->set_default( 0 );
		$hide_icon_setting->add_custom_var( 'block-name', 'hideIcon' );
		$hide_icon_setting->add_custom_var( 'block-format', 'bool' );
		$field = new Checkbox();
		$field->set_title( __( 'Hide icons', 'download-list-block-with-icons' ) );
		$hide_icon_setting->set_field( $field );

		// get the iconsets for the settings.
		$iconsets = array();
		foreach ( Iconsets::get_instance()->get_icon_sets() as $iconset_obj ) {
			$iconsets[ $iconset_obj->get_slug() ] = $iconset_obj->get_label();
		}

		// add setting.
		$setting = $settings_obj->add_setting( 'downloadlist_iconset' );
		$setting->set_section( $general_tab_icons );
		$setting->set_type( 'string' );
		$setting->set_default( '' );
		$setting->add_custom_var( 'block-name', 'iconset' );
		$setting->add_custom_var( 'block-format', 'string' );
		$field = new Select();
		$field->set_title( __( 'Choose an iconset', 'download-list-block-with-icons' ) );
		$field->set_description( '<a href="' . esc_url( Iconsets::get_instance()->get_edit_link() ) . '">' . __( 'Manage Iconsets', 'download-list-block-with-icons' ) . '</a>' );
		$field->set_options( $iconsets );
		$setting->set_field( $field );

		// add setting.
		$setting = $settings_obj->add_setting( 'downloadlist_link_text' );
		$setting->set_section( $general_tab_link );
		$setting->set_type( 'integer' );
		$setting->set_default( 0 );
		$setting->add_custom_var( 'block-name', 'hideLink' );
		$setting->add_custom_var( 'block-format', 'bool' );
		$field = new Checkbox();
		$field->set_title( __( 'Show text instead of link', 'download-list-block-with-icons' ) );
		$setting->set_field( $field );

		// add setting.
		$setting = $settings_obj->add_setting( 'downloadlist_link_target' );
		$setting->set_section( $general_tab_link );
		$setting->set_type( 'string' );
		$setting->set_default( 'direct' );
		$setting->add_custom_var( 'block-name', 'linkTarget' );
		$setting->add_custom_var( 'block-format', 'string' );
		$field = new Select();
		$field->set_title( __( 'Choose link target', 'download-list-block-with-icons' ) );
		$field->set_options(
			array(
				'direct'         => __( 'Direct link', 'download-list-block-with-icons' ),
				'attachmentpage' => __( 'Attachment page', 'download-list-block-with-icons' ),
			)
		);
		$setting->set_field( $field );

		// add setting.
		$setting = $settings_obj->add_setting( 'downloadlist_link_no_forced_download' );
		$setting->set_section( $general_tab_link );
		$setting->set_type( 'integer' );
		$setting->set_default( 0 );
		$setting->add_custom_var( 'block-name', 'doNotForceDownload' );
		$setting->add_custom_var( 'block-format', 'bool' );
		$field = new Checkbox();
		$field->set_title( __( 'Do not force download', 'download-list-block-with-icons' ) );
		$setting->set_field( $field );

		// add setting.
		$browser_target_setting = $settings_obj->add_setting( 'downloadlist_link_browser_target' );
		$browser_target_setting->set_section( $general_tab_link );
		$browser_target_setting->set_type( 'string' );
		$browser_target_setting->set_default( '' );
		$browser_target_setting->add_custom_var( 'block-name', 'linkBrowserTarget' );
		$browser_target_setting->add_custom_var( 'block-format', 'string' );
		$field = new Select();
		$field->set_title( __( 'Handling for link', 'download-list-block-with-icons' ) );
		$field->set_description( __( 'Be aware that this setting could be overridden by the visitors browser. It is also against the rules for accessibility in the web.', 'download-list-block-with-icons' ) );
		$field->set_options(
			array(
				''        => __( 'Do not use', 'download-list-block-with-icons' ),
				'_self'   => __( 'Same tab / window', 'download-list-block-with-icons' ),
				'_blank'  => __( 'New tab / window', 'download-list-block-with-icons' ),
				'_parent' => __( 'Parent window', 'download-list-block-with-icons' ),
				'_top'    => __( 'Complete window', 'download-list-block-with-icons' ),
				'own'     => __( 'Define frame name', 'download-list-block-with-icons' ),
			)
		);
		$browser_target_setting->set_field( $field );

		// add setting.
		$setting = $settings_obj->add_setting( 'downloadlist_link_browser_target_own' );
		$setting->set_section( $general_tab_link );
		$setting->set_type( 'string' );
		$setting->set_default( '' );
		$setting->add_custom_var( 'block-name', 'linkBrowserTargetName' );
		$setting->add_custom_var( 'block-format', 'string' );
		$field = new Text();
		$field->set_title( __( 'Set frame name', 'download-list-block-with-icons' ) );
		$field->add_depend( $browser_target_setting, 'own' );
		$setting->set_field( $field );

		// add setting.
		$download_button_setting = $settings_obj->add_setting( 'downloadlist_show_download_button' );
		$download_button_setting->set_section( $general_tab_db );
		$download_button_setting->set_type( 'integer' );
		$download_button_setting->set_default( 0 );
		$download_button_setting->add_custom_var( 'block-name', 'showDownloadButton' );
		$download_button_setting->add_custom_var( 'block-format', 'bool' );
		$field = new Checkbox();
		$field->set_title( __( 'Show download-button', 'download-list-block-with-icons' ) );
		$download_button_setting->set_field( $field );

		// add setting.
		$browser_target_setting_db = $settings_obj->add_setting( 'downloadlist_link_browser_target' );
		$browser_target_setting_db->set_section( $general_tab_db );
		$browser_target_setting_db->set_type( 'string' );
		$browser_target_setting_db->set_default( '' );
		$browser_target_setting_db->add_custom_var( 'block-name', 'downloadLinkTarget' );
		$browser_target_setting_db->add_custom_var( 'block-format', 'string' );
		$field = new Select();
		$field->set_title( __( 'Handling for link', 'download-list-block-with-icons' ) );
		$field->set_description( __( 'Be aware that this setting could be overridden by the visitors browser. It is also against the rules for accessibility in the web.', 'download-list-block-with-icons' ) );
		$field->set_options(
			array(
				''        => __( 'Do not use', 'download-list-block-with-icons' ),
				'_self'   => __( 'Same tab / window', 'download-list-block-with-icons' ),
				'_blank'  => __( 'New tab / window', 'download-list-block-with-icons' ),
				'_parent' => __( 'Parent window', 'download-list-block-with-icons' ),
				'_top'    => __( 'Complete window', 'download-list-block-with-icons' ),
				'own'     => __( 'Define frame name', 'download-list-block-with-icons' ),
			)
		);
		$browser_target_setting_db->set_field( $field );

		// add setting.
		$setting = $settings_obj->add_setting( 'downloadlist_download_button_browser_target_own' );
		$setting->set_section( $general_tab_db );
		$setting->set_type( 'string' );
		$setting->set_default( '' );
		$setting->add_custom_var( 'block-name', 'downloadLinkTargetName' );
		$setting->add_custom_var( 'block-format', 'string' );
		$field = new Text();
		$field->set_title( __( 'Set frame name', 'download-list-block-with-icons' ) );
		$field->add_depend( $browser_target_setting_db, 'own' );
		$setting->set_field( $field );

		// add setting.
		$download_button_setting = $settings_obj->add_setting( 'downloadlist_hide_file_sizes' );
		$download_button_setting->set_section( $general_tab_advanced );
		$download_button_setting->set_type( 'integer' );
		$download_button_setting->set_default( 0 );
		$download_button_setting->add_custom_var( 'block-name', 'hideFileSize' );
		$download_button_setting->add_custom_var( 'block-format', 'bool' );
		$field = new Checkbox();
		$field->set_title( __( 'Hide file sizes', 'download-list-block-with-icons' ) );
		$download_button_setting->set_field( $field );

		// add setting.
		$download_button_setting = $settings_obj->add_setting( 'downloadlist_hide_description' );
		$download_button_setting->set_section( $general_tab_advanced );
		$download_button_setting->set_type( 'integer' );
		$download_button_setting->set_default( 0 );
		$download_button_setting->add_custom_var( 'block-name', 'hideDescription' );
		$download_button_setting->add_custom_var( 'block-format', 'bool' );
		$field = new Checkbox();
		$field->set_title( __( 'Hide description', 'download-list-block-with-icons' ) );
		$download_button_setting->set_field( $field );

		// add setting.
		$download_button_setting = $settings_obj->add_setting( 'downloadlist_show_file_dates' );
		$download_button_setting->set_section( $general_tab_advanced );
		$download_button_setting->set_type( 'integer' );
		$download_button_setting->set_default( 0 );
		$download_button_setting->add_custom_var( 'block-name', 'showFileDates' );
		$download_button_setting->add_custom_var( 'block-format', 'bool' );
		$field = new Checkbox();
		$field->set_title( __( 'Show file dates', 'download-list-block-with-icons' ) );
		$download_button_setting->set_field( $field );

		// add setting.
		$download_button_setting = $settings_obj->add_setting( 'downloadlist_show_file_format_labels' );
		$download_button_setting->set_section( $general_tab_advanced );
		$download_button_setting->set_type( 'integer' );
		$download_button_setting->set_default( 0 );
		$download_button_setting->add_custom_var( 'block-name', 'showFileFormatLabel' );
		$download_button_setting->add_custom_var( 'block-format', 'bool' );
		$field = new Checkbox();
		$field->set_title( __( 'Show file format labels', 'download-list-block-with-icons' ) );
		$download_button_setting->set_field( $field );

		// add setting.
		$setting = $settings_obj->add_setting( 'downloadlist_robots' );
		$setting->set_section( $general_tab_advanced );
		$setting->set_type( 'string' );
		$setting->set_default( '' );
		$setting->add_custom_var( 'block-name', 'robots' );
		$setting->add_custom_var( 'block-format', 'string' );
		$field = new Select();
		$field->set_title( __( 'Robots', 'download-list-block-with-icons' ) );
		$field->set_options(
			array(
				'follow'   => __( 'follow', 'download-list-block-with-icons' ),
				'nofollow' => __( 'nofollow', 'download-list-block-with-icons' ),
			)
		);
		$setting->set_field( $field );

		// create dialog.
		$dialog = array(
			'title'   => __( 'Inherit settings', 'download-list-block-with-icons' ),
			'texts'   => array(
				'<p><strong>' . __( 'Do you really want to inherit the settings to all download list blocks?', 'download-list-block-with-icons' ) . '</strong></p>',
				'<p>' . __( 'This will override all custom settings on these blocks.', 'download-list-block-with-icons' ) . '</p>',
			),
			'buttons' => array(
				array(
					'action'  => 'downloadlist_inherit_settings();',
					'variant' => 'primary',
					'text'    => __( 'Yes', 'download-list-block-with-icons' ),
				),
				array(
					'action'  => 'closeDialog();',
					'variant' => 'secondary',
					'text'    => __( 'Cancel', 'download-list-block-with-icons' ),
				),
			),
		);

		// add setting.
		$setting = $settings_obj->add_setting( 'downloadlist_inherit_settings' );
		$setting->set_section( $tasks_tab_tasks );
		$setting->prevent_export( true );
		$field = new Button();
		$field->set_title( __( 'Inherit settings', 'download-list-block-with-icons' ) );
		$field->set_button_url( '#' );
		$field->set_button_title( __( 'Inherit settings to all download list blocks', 'download-list-block-with-icons' ) );
		$field->add_data( 'dialog', Helper::get_json( $dialog ) );
		$field->add_class( 'easy-dialog-for-wordpress' );
		$setting->set_field( $field );

		// add setting.
		$setting = $settings_obj->add_setting( 'downloadlist_add_block_to_page' );
		$setting->set_section( $tasks_tab_tasks );
		$setting->prevent_export( true );
		$setting->set_save_callback( array( $this, 'add_block_in_page' ) );
		$field = new SelectPostTypeObject();
		$field->set_title( __( 'Add block to page', 'download-list-block-with-icons' ) );
		$field->set_button_title( __( 'Choose a page', 'download-list-block-with-icons' ) );
		$field->set_popup_title( __( 'Choose and select a page', 'download-list-block-with-icons' ) );
		$field->set_endpoint( '/wp-json/wp/v2/pages' );
		$field->set_limit( 10 );
		$field->set_chosen_title( __( 'Chosen page', 'download-list-block-with-icons' ) );
		$field->set_label_title( __( 'Search for a page', 'download-list-block-with-icons' ) );
		$field->set_placeholder( __( 'Enter the name of a page', 'download-list-block-with-icons' ) );
		$field->set_cancel_button_title( __( 'Cancel', 'download-list-block-with-icons' ) );
		$setting->set_field( $field );

		// add setting.
		$setting = $settings_obj->add_setting( 'downloadlist_add_block_to_post' );
		$setting->set_section( $tasks_tab_tasks );
		$setting->prevent_export( true );
		$setting->set_save_callback( array( $this, 'add_block_in_post' ) );
		$field = new SelectPostTypeObject();
		$field->set_title( __( 'Add block to post', 'download-list-block-with-icons' ) );
		$field->set_button_title( __( 'Choose a post', 'download-list-block-with-icons' ) );
		$field->set_popup_title( __( 'Choose and select a post', 'download-list-block-with-icons' ) );
		$field->set_endpoint( '/wp-json/wp/v2/posts' );
		$field->set_limit( 10 );
		$field->set_chosen_title( __( 'Chosen post', 'download-list-block-with-icons' ) );
		$field->set_label_title( __( 'Search for a post', 'download-list-block-with-icons' ) );
		$field->set_placeholder( __( 'Enter the name of a post', 'download-list-block-with-icons' ) );
		$field->set_cancel_button_title( __( 'Cancel', 'download-list-block-with-icons' ) );
		$setting->set_field( $field );

		// initialize this settings object.
		$settings_obj->init();
	}

	/**
	 * Inherit the settings to all download list blocks via AJAX request.
	 *
	 * @return void
	 */
	public function inherit_settings_via_ajax(): void {
		// check nonce.
		check_ajax_referer( 'downloadlist-inherit-settings', 'nonce' );

		// run the inheriting.
		$this->inherit_settings_to_blocks();

		// return result.
		wp_send_json_success();
	}

	/**
	 * Return info about inheriting progress.
	 *
	 * @return void
	 */
	public function get_inherit_settings_progress_info(): void {
		// check nonce.
		check_ajax_referer( 'downloadlist-inherit-info', 'nonce' );

		// create dialog.
		$dialog = array(
			'detail' => array(
				'title'   => __( 'Inherit settings', 'download-list-block-with-icons' ),
				'texts'   => array(
					'<p><strong>' . __( 'Settings for download list block have been inherited.', 'download-list-block-with-icons' ) . '</strong></p>',
					'<p>' . __( 'Please check your download list blocks now', 'download-list-block-with-icons' ) . '</p>',
				),
				'buttons' => array(
					array(
						'action'  => 'closeDialog();',
						'variant' => 'primary',
						'text'    => __( 'OK', 'download-list-block-with-icons' ),
					),
				),
			),
		);

		// send return value.
		wp_send_json(
			array(
				absint( get_option( 'downloadlist_inheriting_count' ) ),
				absint( get_option( 'downloadlist_inheriting_max' ) ),
				absint( get_option( 'downloadlist_inheriting_running' ) ),
				get_option( 'downloadlist_inheriting_status' ),
				$dialog,
			)
		);
	}

	/**
	 * Inherit settings to the download list blocks in this website.
	 *
	 * @return void
	 */
	public function inherit_settings_to_blocks(): void {
		// mark that inheriting is running.
		update_option( 'downloadlist_inheriting_running', time() );

		// set initial title.
		update_option( 'downloadlist_inheriting_status', __( 'Load objects ..', 'download-list-block-with-icons' ) );

		// get all objects with our block.
		$query   = array(
			'post_type'      => array( 'page', 'post' ),
			'post_status'    => 'any',
			's'              => 'wp:downloadlist/list',
			'fields'         => 'ids',
			'posts_per_page' => -1,
		);
		$results = new WP_Query( $query );

		// show progress on WP CLI.
		$progress = Helper::is_cli() ? \WP_CLI\Utils\make_progress_bar( _n( 'Updating the object', 'Updating the objects', $results->found_posts, 'download-list-block-with-icons' ), $results->found_posts ) : false;

		// save the count.
		update_option( 'downloadlist_inheriting_max', $results->found_posts );

		// reset the count.
		update_option( 'downloadlist_inheriting_count', 0 );

		// loop through the posts.
		foreach ( $results->get_posts() as $post_id ) {
			// get the post ID.
			$post_id = absint( $post_id ); // @phpstan-ignore argument.type

			// update the title.
			/* translators: %1$s will be replaced by the object title. */
			update_option( 'downloadlist_inheriting_status', sprintf( __( 'Updating %1$s', 'download-list-block-with-icons' ), get_post_field( 'post_title', $post_id ) ) );

			// get the content.
			$content = get_post_field( 'post_content', $post_id, 'raw' );

			// get the list of parsed blocks.
			$blocks = parse_blocks( $content );

			// loop through the blocks and get our own download list block.
			foreach ( $blocks as $index => $block ) {
				// bail if block does not match.
				if ( 'downloadlist/list' !== $block['blockName'] ) {
					continue;
				}

				// loop through the settings of this plugin and set the values on the block.
				foreach ( \DownloadListWithIcons\Dependencies\easySettingsForWordPress\Settings::get_instance()->get_settings() as $setting_obj ) {
					// get the block name of this setting.
					$settings_block_name = $setting_obj->get_custom_var( 'block-name' );

					// bail if block-name is not set as custom var.
					if ( empty( $settings_block_name ) ) {
						continue;
					}

					// get value.
					$value = get_option( $setting_obj->get_name() );

					// convert value depending on settings to block compatible value.
					switch ( $setting_obj->get_custom_var( 'block-format' ) ) {
						case 'bool':
							$value = 1 === absint( $value );
							break;
						default:
							$value = (string) $value;
							break;
					}

					$blocks[ $index ]['attrs'][ $settings_block_name ] = $value;
				}
			}

			// get the updated list of blocks.
			$content = serialize_blocks( $blocks );

			// save this.
			wp_update_post(
				array(
					'ID'           => $post_id,
					'post_content' => $content,
				)
			);

			// show progress on WP CLI.
			$progress ? $progress->tick() : '';

			// set initial title.
			update_option( 'downloadlist_inheriting_status', __( 'Done', 'download-list-block-with-icons' ) );

			// update the count.
			update_option( 'downloadlist_inheriting_count', absint( get_option( 'downloadlist_inheriting_count' ) + 1 ) );
		}

		// end progress on WP CLI.
		$progress ? $progress->finish() : '';

		// remove mark that inheriting is running.
		delete_option( 'downloadlist_inheriting_running' );
	}

	/**
	 * Return the link to the settings.
	 *
	 * @param string $tab The tab.
	 * @param string $sub_tab The sub tab.
	 * @param string $url The URL to filter for.
	 *
	 * @return string
	 */
	public function get_url( string $tab = '', string $sub_tab = '', string $url = '' ): string {
		// define base array.
		$array = array(
			'page' => $this->get_menu_slug(),
		);

		// add tab, if set.
		if ( ! empty( $tab ) ) {
			$array['tab'] = $tab;
		}

		// add sbu-tab, if set.
		if ( ! empty( $sub_tab ) ) {
			$array['subtab'] = $sub_tab;
		}

		// add URL, if set.
		if ( ! empty( $url ) ) {
			$array['s'] = $url;
		}

		// return the URL.
		return add_query_arg(
			$array,
			get_admin_url() . $this->get_php_page()
		);
	}

	/**
	 * Show info about these settings.
	 *
	 * @return void
	 */
	public function show_info(): void {
		/* translators: %1$s will be replaced by a URL. */
		echo '<p>' . wp_kses_post( sprintf( __( 'Set the default settings for new download list blocks here. Under <a href="%1$s">Tasks</a>, you can also inherit these settings to all existing blocks.', 'download-list-block-with-icons' ), $this->get_url( 'downloadlist_tasks' ) ) ) . '</p>';
	}

	/**
	 * Add block to given page.
	 *
	 * It will be added at the end of the content of the given page.
	 *
	 * @param string $values The value.
	 * @return void
	 */
	public function add_block_in_page( string $values ): void {
		// convert to int.
		$post_id = absint( $values );

		// bail if no page ID is given.
		if ( 0 === $post_id ) {
			return;
		}

		// add the block.
		$this->add_block( $post_id );

		// get the edit URL for this page.
		$edit_url = get_edit_post_link( $post_id );
		if ( ! is_string( $edit_url ) ) {
			return;
		}

		// get its title.
		$title = get_the_title( $post_id );

		// add success message.
		$transient_obj = Transients::get_instance()->add();
		$transient_obj->set_name( 'downloadlist_block_added' );
		$transient_obj->set_type( 'success' );
		/* translators: %1$s will be replaced by a string. */
		$transient_obj->set_message( '<strong>' . sprintf( __( 'The block has been added to %1$s.', 'download-list-block-with-icons' ), '<em>' . $title . '</em>' ) . '</strong> ' . sprintf( __( '<a href="%1$s">Edit the page</a> and add the files you want to list to the block.', 'download-list-block-with-icons' ), $edit_url ) );
		$transient_obj->save();
	}

	/**
	 * Add block to given post.
	 *
	 * It will be added at the end of the content of the given post.
	 *
	 * @param string $values The value.
	 * @return void
	 */
	public function add_block_in_post( string $values ): void {
		// convert to int.
		$post_id = absint( $values );

		// bail if no page ID is given.
		if ( 0 === $post_id ) {
			return;
		}

		// add the block.
		$this->add_block( $post_id );

		// get the edit URL for this page.
		$edit_url = get_edit_post_link( $post_id );
		if ( ! is_string( $edit_url ) ) {
			return;
		}

		// get its title.
		$title = get_the_title( $post_id );

		// add success message.
		$transient_obj = Transients::get_instance()->add();
		$transient_obj->set_name( 'downloadlist_block_added' );
		$transient_obj->set_type( 'success' );
		/* translators: %1$s will be replaced by a string. */
		$transient_obj->set_message( '<strong>' . sprintf( __( 'The block has been added to the post %1$s.', 'download-list-block-with-icons' ), '<em>' . $title . '</em>' ) . '</strong> ' . sprintf( __( '<a href="%1$s">Edit the post</a> and add the files you want to list to the block.', 'download-list-block-with-icons' ), $edit_url ) );
		$transient_obj->save();
	}

	/**
	 * Add block to any post-entry.
	 *
	 * @param int $post_id The post ID to use.
	 * @return void
	 */
	private function add_block( int $post_id ): void {
		// get the content.
		$content = get_post_field( 'post_content', $post_id, 'raw' );

		// add our own block at the end.
		$content .= '<!-- wp:downloadlist/list /-->';

		// save the content.
		$query = array(
			'ID'           => $post_id,
			'post_content' => $content,
		);
		wp_update_post( $query );
	}
}
