<?php
/**
 * This file defines the settings for this plugin.
 *
 * @package download-list-block-with-icons
 */

namespace DownloadListWithIcons\Plugin;

// prevent direct access.
defined( 'ABSPATH' ) || exit;

use DownloadListWithIcons\Dependencies\easyTransientsForWordPress\Transients;
use DownloadListWithIcons\Iconsets\Iconsets;
use DownloadListWithIcons\Plugin\Admin\Usage_Table;
use easySettingsForWordPress\Fields\Button;
use easySettingsForWordPress\Fields\Checkbox;
use easySettingsForWordPress\Fields\Number;
use easySettingsForWordPress\Fields\Select;
use easySettingsForWordPress\Fields\SelectPostTypeObject;
use easySettingsForWordPress\Fields\Text;
use easySettingsForWordPress\Page;
use WP_Query;

/**
 * Object, which handles the settings of this plugin.
 */
class Settings {

	/**
	 * The settings object.
	 *
	 * @var ?\easySettingsForWordPress\Settings
	 */
	private ?\easySettingsForWordPress\Settings $settings_obj = null;

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

		// use AJAX hooks.
		add_action( 'wp_ajax_downloadlist_inherit_settings', array( $this, 'inherit_settings_via_ajax' ) );
		add_action( 'wp_ajax_downloadlist_inherit_settings_get_info', array( $this, 'get_inherit_settings_progress_info' ) );

		// use action hooks.
		add_action( 'admin_action_downloadlist_reset_css', array( $this, 'reset_styles_by_request' ) );
		add_action( 'admin_action_downloadlist_reset', array( $this, 'reset_plugin_by_request' ) );
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
		$this->settings_obj = $this->get_settings_obj();
		$this->settings_obj->set_slug( 'downloadlist' );
		$this->settings_obj->set_plugin_slug( DL_PLUGIN );
		$this->settings_obj->set_menu_title( __( 'Download List Block with Icons', 'download-list-block-with-icons' ) );
		$this->settings_obj->set_title( __( 'Settings for Download List Block with Icons', 'download-list-block-with-icons' ) );
		$this->settings_obj->set_menu_slug( $this->get_menu_slug() );
		$this->settings_obj->set_menu_parent_slug( $this->get_php_page() );
		$this->settings_obj->set_translations(
			array(
				'title_settings_import_file_missing' => __( 'A required file missing', 'download-list-block-with-icons' ),
				'text_settings_import_file_missing'  => __( 'Please choose a JSON-file with settings to import.', 'download-list-block-with-icons' ),
				'lbl_ok'                             => __( 'OK', 'download-list-block-with-icons' ),
				'lbl_cancel'                         => __( 'Cancel', 'download-list-block-with-icons' ),
				'import_title'                       => __( 'Import', 'download-list-block-with-icons' ),
				'dialog_import_title'                => __( 'Import plugin settings', 'download-list-block-with-icons' ),
				'dialog_import_text'                 => __( 'Click on the button below to choose your JSON-file with the settings.', 'download-list-block-with-icons' ),
				'dialog_import_button'               => __( 'Import now', 'download-list-block-with-icons' ),
				'dialog_import_error_title'          => __( 'Error during import', 'download-list-block-with-icons' ),
				'dialog_import_error_text'           => __( 'The file could not be imported!', 'download-list-block-with-icons' ),
				'dialog_import_error_no_file'        => __( 'No file was uploaded.', 'download-list-block-with-icons' ),
				'dialog_import_error_no_size'        => __( 'The uploaded file is no size.', 'download-list-block-with-icons' ),
				'dialog_import_error_no_json'        => __( 'The uploaded file is not a valid JSON-file.', 'download-list-block-with-icons' ),
				'dialog_import_error_no_json_ext'    => __( 'The uploaded file does not have the file extension <i>.json</i>.', 'download-list-block-with-icons' ),
				'dialog_import_error_not_saved'      => __( 'The uploaded file could not be saved. Contact your hoster about this problem.', 'download-list-block-with-icons' ),
				'dialog_import_error_not_our_json'   => __( 'The uploaded file is not a valid JSON-file with settings for this plugin.', 'download-list-block-with-icons' ),
				'dialog_import_success_title'        => __( 'Settings have been imported', 'download-list-block-with-icons' ),
				'dialog_import_success_text'         => __( 'Import has been run successfully.', 'download-list-block-with-icons' ),
				'dialog_import_success_text_2'       => __( 'The new settings are now active. Click on the button below to reload the page and see the settings.', 'download-list-block-with-icons' ),
				'export_title'                       => __( 'Export', 'download-list-block-with-icons' ),
				'dialog_export_title'                => __( 'Export plugin settings', 'download-list-block-with-icons' ),
				'dialog_export_text'                 => __( 'Click on the button below to export the actual settings.', 'download-list-block-with-icons' ),
				'dialog_export_text_2'               => __( 'You can import this JSON-file in other projects using this WordPress plugin or theme.', 'download-list-block-with-icons' ),
				'dialog_export_button'               => __( 'Export now', 'download-list-block-with-icons' ),
				'table_options'                      => __( 'Options', 'download-list-block-with-icons' ),
				'table_entry'                        => __( 'Entry', 'download-list-block-with-icons' ),
				'table_no_entries'                   => __( 'No entries found.', 'download-list-block-with-icons' ),
				'plugin_settings_title'              => __( 'Settings', 'download-list-block-with-icons' ),
				'file_add_file'                      => __( 'Add file', 'download-list-block-with-icons' ),
				'file_choose_file'                   => __( 'Choose file', 'download-list-block-with-icons' ),
				'file_choose_image'                  => __( 'Upload or choose image', 'download-list-block-with-icons' ),
				'drag_n_drop'                        => __( 'Hold to drag & drop', 'download-list-block-with-icons' ),
			)
		);
		$this->settings_obj->set_view( 'dataview' );

		/**
		 * Add the settings page.
		 */
		$settings_page = $this->get_settings_obj()->get_page( $this->get_menu_slug() );

		// bail if page could not be loaded.
		if ( ! $settings_page instanceof Page ) {
			return;
		}

		/**
		 * Configure the tabs for this object.
		 */
		// the general tab.
		$general_tab = $settings_page->add_tab( 'downloadlist_general', 10 );
		$general_tab->set_name( 'downloadlist_general' );
		$general_tab->set_title( __( 'Block settings', 'download-list-block-with-icons' ) );
		$settings_page->set_default_tab( $general_tab );

		// the tab for the settings of the plugin itself.
		$plugin_tab = $settings_page->add_tab( 'downloadlist_plugin', 15 );
		$plugin_tab->set_name( 'downloadlist_plugin' );
		$plugin_tab->set_title( __( 'Settings', 'download-list-block-with-icons' ) );

		// the task tab.
		$tasks_tab = $settings_page->add_tab( 'downloadlist_tasks', 20 );
		$tasks_tab->set_name( 'downloadlist_tasks' );
		$tasks_tab->set_title( __( 'Tasks', 'download-list-block-with-icons' ) );

		// the usage tab.
		$usage_tab = $settings_page->add_tab( 'downloadlist_usage', 30 );
		$usage_tab->set_name( 'downloadlist_usage' );
		$usage_tab->set_title( __( 'Usage', 'download-list-block-with-icons' ) );
		$usage_tab->set_hide_save( true );
		$usage_tab->set_callback( array( $this, 'show_usage' ) );

		// the helper tab.
		$helper_tab = $settings_page->add_tab( 'downloadlist_helper', 70 );
		$helper_tab->set_title( ' ' );
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
		$general_tab_info->set_setting( $this->get_settings_obj() );

		// the icons section.
		$general_tab_icons = $general_tab->add_section( 'icons', 20 );
		$general_tab_icons->set_title( __( 'Icons', 'download-list-block-with-icons' ) );
		$general_tab_icons->set_setting( $this->get_settings_obj() );

		// the styles section.
		$general_tab_styles = $general_tab->add_section( 'styles', 25 );
		$general_tab_styles->set_title( __( 'Styles', 'download-list-block-with-icons' ) );
		$general_tab_styles->set_setting( $this->get_settings_obj() );

		// the link section.
		$general_tab_link = $general_tab->add_section( 'link', 30 );
		$general_tab_link->set_title( __( 'Link', 'download-list-block-with-icons' ) );
		$general_tab_link->set_setting( $this->get_settings_obj() );

		// the download button section.
		$general_tab_db = $general_tab->add_section( 'download_button', 40 );
		$general_tab_db->set_title( __( 'Download button', 'download-list-block-with-icons' ) );
		$general_tab_db->set_setting( $this->get_settings_obj() );

		// the advanced section.
		$general_tab_advanced = $general_tab->add_section( 'advanced', 50 );
		$general_tab_advanced->set_title( __( 'Advanced settings', 'download-list-block-with-icons' ) );
		$general_tab_advanced->set_setting( $this->get_settings_obj() );

		// the section for folder plugins.
		$plugin_tab_folders = $plugin_tab->add_section( 'folders', 10 );
		$plugin_tab_folders->set_title( __( 'Folders in the media library', 'download-list-block-with-icons' ) );
		$plugin_tab_folders->set_setting( $this->get_settings_obj() );

		// the section for abilities.
		$plugin_tab_abilities = $plugin_tab->add_section( 'abilities', 20 );
		$plugin_tab_abilities->set_title( __( 'Abilities for AI agents and automations', 'download-list-block-with-icons' ) );
		$plugin_tab_abilities->set_setting( $this->get_settings_obj() );

		// the section with the status of the abilities.
		$plugin_tab_abilities_status = $plugin_tab->add_section( 'abilities_status', 30 );
		$plugin_tab_abilities_status->set_title( __( 'Can the abilities be used in this project?', 'download-list-block-with-icons' ) );
		$plugin_tab_abilities_status->set_callback( array( $this, 'show_abilities_status' ) );
		$plugin_tab_abilities_status->set_setting( $this->get_settings_obj() );

		// the tasks section.
		$tasks_tab_tasks = $tasks_tab->add_section( 'tasks', 10 );
		$tasks_tab_tasks->set_title( __( 'Tasks', 'download-list-block-with-icons' ) );
		$tasks_tab_tasks->set_setting( $this->get_settings_obj() );

		// add setting.
		$hide_icon_setting = $this->get_settings_obj()->add_setting( 'downloadlist_hide_icons' );
		$hide_icon_setting->set_section( $general_tab_icons );
		$hide_icon_setting->set_default( 0 );
		$hide_icon_setting->add_custom_var( 'block-name', 'hideIcon' );
		$hide_icon_setting->add_custom_var( 'block-format', 'bool' );
		$field = new Checkbox( $this->get_settings_obj() );
		$field->set_title( __( 'Hide icons', 'download-list-block-with-icons' ) );
		$hide_icon_setting->set_field( $field );

		// get the iconsets for the settings.
		$iconsets = array();
		foreach ( Iconsets::get_instance()->get_icon_sets() as $iconset_obj ) {
			$iconsets[ $iconset_obj->get_slug() ] = $iconset_obj->get_label();
		}

		// add setting.
		$setting = $this->get_settings_obj()->add_setting( 'downloadlist_iconset' );
		$setting->set_section( $general_tab_icons );
		$setting->set_type( 'string' );
		$setting->set_default( 'dashicons' );
		$setting->add_custom_var( 'block-name', 'iconset' );
		$setting->add_custom_var( 'block-format', 'string' );
		$field = new Select( $this->get_settings_obj() );
		$field->set_title( __( 'Choose an iconset', 'download-list-block-with-icons' ) );
		$field->set_description( '<a href="' . esc_url( Iconsets::get_instance()->get_edit_link() ) . '">' . __( 'Manage Iconsets', 'download-list-block-with-icons' ) . '</a>' );
		$field->set_options( $iconsets );
		$setting->set_field( $field );

		// add setting.
		$preview_setting = $this->get_settings_obj()->add_setting( 'downloadlist_show_preview' );
		$preview_setting->set_section( $general_tab_icons );
		$preview_setting->set_default( 0 );
		$preview_setting->add_custom_var( 'block-name', 'showPreview' );
		$preview_setting->add_custom_var( 'block-format', 'bool' );
		$field = new Checkbox( $this->get_settings_obj() );
		$field->set_title( __( 'Show preview images', 'download-list-block-with-icons' ) );
		$field->set_description( $this->get_preview_description() );
		$preview_setting->set_field( $field );

		// add setting.
		$setting = $this->get_settings_obj()->add_setting( 'downloadlist_preview_width' );
		$setting->set_section( $general_tab_icons );
		$setting->set_type( 'integer' );
		$setting->set_default( 48 );
		$setting->add_custom_var( 'block-name', 'previewWidth' );
		$setting->add_custom_var( 'block-format', 'int' );
		$field = new Number( $this->get_settings_obj() );
		$field->set_title( __( 'Size of preview images and icon frames in pixel', 'download-list-block-with-icons' ) );
		$field->set_description( __( 'Used for preview images and for icons with a frame.', 'download-list-block-with-icons' ) );
		$field->set_min( 16 );
		$field->set_max( 600 );
		$setting->set_field( $field );

		// add setting.
		$setting = $this->get_settings_obj()->add_setting( 'downloadlist_media_border_width' );
		$setting->set_section( $general_tab_styles );
		$setting->set_type( 'integer' );
		$setting->set_default( 0 );
		$setting->add_custom_var( 'block-name', 'mediaBorderWidth' );
		$setting->add_custom_var( 'block-format', 'int' );
		$field = new Number( $this->get_settings_obj() );
		$field->set_title( __( 'Frame width in pixel', 'download-list-block-with-icons' ) );
		$field->set_description( __( 'Draws a frame around icons and preview images. 0 shows no frame.', 'download-list-block-with-icons' ) );
		$field->set_min( 0 );
		$field->set_max( 20 );
		$setting->set_field( $field );

		// add setting.
		$setting = $this->get_settings_obj()->add_setting( 'downloadlist_media_border_color' );
		$setting->set_section( $general_tab_styles );
		$setting->set_type( 'string' );
		$setting->set_default( '' );
		$setting->add_custom_var( 'block-name', 'mediaBorderColor' );
		$setting->add_custom_var( 'block-format', 'string' );
		$field = new Text( $this->get_settings_obj() );
		$field->set_title( __( 'Frame color', 'download-list-block-with-icons' ) );
		$field->set_description( __( 'A hex color like #dddddd. Leave empty to use the text color.', 'download-list-block-with-icons' ) );
		$field->set_placeholder( '#dddddd' );
		$field->set_sanitize_callback( array( $this, 'sanitize_color' ) );
		$setting->set_field( $field );

		// add setting.
		$setting = $this->get_settings_obj()->add_setting( 'downloadlist_media_border_radius' );
		$setting->set_section( $general_tab_styles );
		$setting->set_type( 'integer' );
		$setting->set_default( 0 );
		$setting->add_custom_var( 'block-name', 'mediaBorderRadius' );
		$setting->add_custom_var( 'block-format', 'int' );
		$field = new Number( $this->get_settings_obj() );
		$field->set_title( __( 'Frame radius in pixel', 'download-list-block-with-icons' ) );
		$field->set_min( 0 );
		$field->set_max( 300 );
		$setting->set_field( $field );

		// add setting.
		$setting = $this->get_settings_obj()->add_setting( 'downloadlist_media_padding' );
		$setting->set_section( $general_tab_styles );
		$setting->set_type( 'integer' );
		$setting->set_default( 0 );
		$setting->add_custom_var( 'block-name', 'mediaPadding' );
		$setting->add_custom_var( 'block-format', 'int' );
		$field = new Number( $this->get_settings_obj() );
		$field->set_title( __( 'Inner spacing of the frame in pixel', 'download-list-block-with-icons' ) );
		$field->set_min( 0 );
		$field->set_max( 100 );
		$setting->set_field( $field );

		// add setting.
		$setting = $this->get_settings_obj()->add_setting( 'downloadlist_media_background' );
		$setting->set_section( $general_tab_styles );
		$setting->set_type( 'string' );
		$setting->set_default( '' );
		$setting->add_custom_var( 'block-name', 'mediaBackground' );
		$setting->add_custom_var( 'block-format', 'string' );
		$field = new Text( $this->get_settings_obj() );
		$field->set_title( __( 'Background color of the frame', 'download-list-block-with-icons' ) );
		$field->set_description( __( 'A hex color like #f5f5f5. Leave empty for no background.', 'download-list-block-with-icons' ) );
		$field->set_placeholder( '#f5f5f5' );
		$field->set_sanitize_callback( array( $this, 'sanitize_color' ) );
		$setting->set_field( $field );

		// add setting.
		$setting = $this->get_settings_obj()->add_setting( 'downloadlist_folder_support' );
		$setting->set_section( $plugin_tab_folders );
		$setting->set_default( 1 );
		$field = new Checkbox( $this->get_settings_obj() );
		$field->set_title( __( 'Use folders as source of download lists', 'download-list-block-with-icons' ) );
		$field->set_description( $this->get_folder_support_description() );
		$setting->set_field( $field );

		// add the detected folder plugins to the description, after all plugins have registered their taxonomies.
		add_action(
			'admin_init',
			function () use ( $field ) {
				$field->set_description( $this->get_folder_support_description( true ) );
			}
		);

		// add setting.
		$abilities_setting = $this->get_settings_obj()->add_setting( 'downloadlist_abilities' );
		$abilities_setting->set_section( $plugin_tab_abilities );
		$abilities_setting->set_default( 1 );
		$field = new Checkbox( $this->get_settings_obj() );
		$field->set_title( __( 'Enable abilities', 'download-list-block-with-icons' ) );
		$field->set_description( __( 'Offers the functions of this plugin via the Abilities API of WordPress, e.g. for AI agents: find where files are shown, list download lists and folders, check for missing preview images and, if allowed, create download lists. The abilities always run with the rights of the logged-in user.', 'download-list-block-with-icons' ) );
		$abilities_setting->set_field( $field );

		// add setting.
		$setting = $this->get_settings_obj()->add_setting( 'downloadlist_abilities_mode' );
		$setting->set_section( $plugin_tab_abilities );
		$setting->set_type( 'string' );
		$setting->set_default( Abilities::MODE_READ );
		$field = new Select( $this->get_settings_obj() );
		$field->set_title( __( 'Allowed abilities', 'download-list-block-with-icons' ) );
		$field->set_description( __( '"Read only" offers only abilities which do not change anything. "All" also allows to assign files to download lists, to add download lists to posts and pages, to inherit the settings to all blocks and to regenerate the styles. These abilities ask AI agents to confirm each change with you first. To enforce this, set your AI client to ask before it uses them.', 'download-list-block-with-icons' ) );
		$field->set_options(
			array(
				Abilities::MODE_READ => __( 'Read only', 'download-list-block-with-icons' ),
				Abilities::MODE_ALL  => __( 'All', 'download-list-block-with-icons' ),
			)
		);
		$field->add_depend( $abilities_setting, 1 );
		$setting->set_field( $field );

		// add setting.
		$setting = $this->get_settings_obj()->add_setting( 'downloadlist_link_text' );
		$setting->set_section( $general_tab_link );
		$setting->set_default( 0 );
		$setting->add_custom_var( 'block-name', 'hideLink' );
		$setting->add_custom_var( 'block-format', 'bool' );
		$field = new Checkbox( $this->get_settings_obj() );
		$field->set_title( __( 'Show text instead of link', 'download-list-block-with-icons' ) );
		$setting->set_field( $field );

		// add setting.
		$setting = $this->get_settings_obj()->add_setting( 'downloadlist_link_target' );
		$setting->set_section( $general_tab_link );
		$setting->set_type( 'string' );
		$setting->set_default( 'direct' );
		$setting->add_custom_var( 'block-name', 'linkTarget' );
		$setting->add_custom_var( 'block-format', 'string' );
		$field = new Select( $this->get_settings_obj() );
		$field->set_title( __( 'Choose link target', 'download-list-block-with-icons' ) );
		$field->set_options(
			array(
				'direct'         => __( 'Direct link', 'download-list-block-with-icons' ),
				'attachmentpage' => __( 'Attachment page', 'download-list-block-with-icons' ),
			)
		);
		$setting->set_field( $field );

		// add setting.
		$setting = $this->get_settings_obj()->add_setting( 'downloadlist_link_no_forced_download' );
		$setting->set_section( $general_tab_link );
		$setting->set_default( 0 );
		$setting->add_custom_var( 'block-name', 'doNotForceDownload' );
		$setting->add_custom_var( 'block-format', 'bool' );
		$field = new Checkbox( $this->get_settings_obj() );
		$field->set_title( __( 'Do not force download', 'download-list-block-with-icons' ) );
		$setting->set_field( $field );

		// add setting.
		$browser_target_setting = $this->get_settings_obj()->add_setting( 'downloadlist_link_browser_target' );
		$browser_target_setting->set_section( $general_tab_link );
		$browser_target_setting->set_type( 'string' );
		$browser_target_setting->set_default( '' );
		$browser_target_setting->add_custom_var( 'block-name', 'linkBrowserTarget' );
		$browser_target_setting->add_custom_var( 'block-format', 'string' );
		$field = new Select( $this->get_settings_obj() );
		$field->set_title( __( 'Handling for link', 'download-list-block-with-icons' ) );
		$field->set_description( __( 'This setting could be overridden by the browser of your visitors. It is also against the rules for accessibility in the web.', 'download-list-block-with-icons' ) );
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
		$setting = $this->get_settings_obj()->add_setting( 'downloadlist_link_browser_target_own' );
		$setting->set_section( $general_tab_link );
		$setting->set_type( 'string' );
		$setting->set_default( '' );
		$setting->add_custom_var( 'block-name', 'linkBrowserTargetName' );
		$setting->add_custom_var( 'block-format', 'string' );
		$field = new Text( $this->get_settings_obj() );
		$field->set_title( __( 'Set frame name', 'download-list-block-with-icons' ) );
		$field->add_depend( $browser_target_setting, 'own' );
		$setting->set_field( $field );

		// add setting.
		$download_button_setting = $this->get_settings_obj()->add_setting( 'downloadlist_show_download_button' );
		$download_button_setting->set_section( $general_tab_db );
		$download_button_setting->set_default( 0 );
		$download_button_setting->add_custom_var( 'block-name', 'showDownloadButton' );
		$download_button_setting->add_custom_var( 'block-format', 'bool' );
		$field = new Checkbox( $this->get_settings_obj() );
		$field->set_title( __( 'Show download-button', 'download-list-block-with-icons' ) );
		$download_button_setting->set_field( $field );

		// add setting.
		$browser_target_setting_db = $this->get_settings_obj()->add_setting( 'downloadlist_download_button_browser_target' );
		$browser_target_setting_db->set_section( $general_tab_db );
		$browser_target_setting_db->set_type( 'string' );
		$browser_target_setting_db->set_default( '' );
		$browser_target_setting_db->add_custom_var( 'block-name', 'downloadLinkTarget' );
		$browser_target_setting_db->add_custom_var( 'block-format', 'string' );
		$field = new Select( $this->get_settings_obj() );
		$field->set_title( __( 'Handling for link', 'download-list-block-with-icons' ) );
		$field->set_description( __( 'This setting could be overridden by the browser of your visitors. It is also against the rules for accessibility in the web.', 'download-list-block-with-icons' ) );
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
		$setting = $this->get_settings_obj()->add_setting( 'downloadlist_download_button_browser_target_own' );
		$setting->set_section( $general_tab_db );
		$setting->set_type( 'string' );
		$setting->set_default( '' );
		$setting->add_custom_var( 'block-name', 'downloadLinkTargetName' );
		$setting->add_custom_var( 'block-format', 'string' );
		$field = new Text( $this->get_settings_obj() );
		$field->set_title( __( 'Set frame name', 'download-list-block-with-icons' ) );
		$field->add_depend( $browser_target_setting_db, 'own' );
		$setting->set_field( $field );

		// add setting.
		$download_button_setting = $this->get_settings_obj()->add_setting( 'downloadlist_hide_file_sizes' );
		$download_button_setting->set_section( $general_tab_advanced );
		$download_button_setting->set_default( 0 );
		$download_button_setting->add_custom_var( 'block-name', 'hideFileSize' );
		$download_button_setting->add_custom_var( 'block-format', 'bool' );
		$field = new Checkbox( $this->get_settings_obj() );
		$field->set_title( __( 'Hide file sizes', 'download-list-block-with-icons' ) );
		$download_button_setting->set_field( $field );

		// add setting.
		$download_button_setting = $this->get_settings_obj()->add_setting( 'downloadlist_hide_description' );
		$download_button_setting->set_section( $general_tab_advanced );
		$download_button_setting->set_default( 0 );
		$download_button_setting->add_custom_var( 'block-name', 'hideDescription' );
		$download_button_setting->add_custom_var( 'block-format', 'bool' );
		$field = new Checkbox( $this->get_settings_obj() );
		$field->set_title( __( 'Hide description', 'download-list-block-with-icons' ) );
		$download_button_setting->set_field( $field );

		// add setting.
		$download_button_setting = $this->get_settings_obj()->add_setting( 'downloadlist_show_file_dates' );
		$download_button_setting->set_section( $general_tab_advanced );
		$download_button_setting->set_default( 0 );
		$download_button_setting->add_custom_var( 'block-name', 'showFileDates' );
		$download_button_setting->add_custom_var( 'block-format', 'bool' );
		$field = new Checkbox( $this->get_settings_obj() );
		$field->set_title( __( 'Show file dates', 'download-list-block-with-icons' ) );
		$download_button_setting->set_field( $field );

		// add setting.
		$download_button_setting = $this->get_settings_obj()->add_setting( 'downloadlist_show_file_format_labels' );
		$download_button_setting->set_section( $general_tab_advanced );
		$download_button_setting->set_default( 0 );
		$download_button_setting->add_custom_var( 'block-name', 'showFileFormatLabel' );
		$download_button_setting->add_custom_var( 'block-format', 'bool' );
		$field = new Checkbox( $this->get_settings_obj() );
		$field->set_title( __( 'Show file format labels', 'download-list-block-with-icons' ) );
		$download_button_setting->set_field( $field );

		// add setting.
		$setting = $this->get_settings_obj()->add_setting( 'downloadlist_robots' );
		$setting->set_section( $general_tab_advanced );
		$setting->set_type( 'string' );
		$setting->set_default( 'nofollow' );
		$setting->add_custom_var( 'block-name', 'robots' );
		$setting->add_custom_var( 'block-format', 'string' );
		$field = new Select( $this->get_settings_obj() );
		$field->set_title( __( 'Robots', 'download-list-block-with-icons' ) );
		$field->set_options(
			array(
				'follow'   => __( 'follow', 'download-list-block-with-icons' ),
				'nofollow' => __( 'nofollow', 'download-list-block-with-icons' ),
			)
		);
		$setting->set_field( $field );

		// create the dialog.
		$dialog = array(
			'title'   => __( 'Inherit settings', 'download-list-block-with-icons' ),
			'texts'   => array(
				'<p><strong>' . __( 'Do you real want to inherit the settings to all download list blocks?', 'download-list-block-with-icons' ) . '</strong></p>',
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
		$setting = $this->get_settings_obj()->add_setting( 'downloadlist_inherit_settings' );
		$setting->set_section( $tasks_tab_tasks );
		$setting->prevent_export( true );
		$field = new Button( $this->get_settings_obj() );
		$field->set_title( __( 'Inherit settings', 'download-list-block-with-icons' ) );
		$field->set_button_url( '#' );
		$field->set_button_title( __( 'Inherit settings to all download list blocks', 'download-list-block-with-icons' ) );
		$field->add_data( 'dialog', Helper::get_json( $dialog ) );
		$field->add_class( 'easy-dialog-for-wordpress' );
		$setting->set_field( $field );

		// create the URL to reset styles.
		$reset_styles_url = add_query_arg(
			array(
				'action' => 'downloadlist_reset_css',
				'nonce'  => wp_create_nonce( 'downloadlist-reset-css' ),
			),
			get_admin_url() . 'admin.php'
		);

		// add setting.
		$setting = $this->get_settings_obj()->add_setting( 'downloadlist_reset_css' );
		$setting->set_section( $tasks_tab_tasks );
		$setting->prevent_export( true );
		$field = new Button( $this->get_settings_obj() );
		$field->set_title( __( 'Reset styles', 'download-list-block-with-icons' ) );
		$field->set_button_url( $reset_styles_url );
		$field->set_button_title( __( 'Reset styles', 'download-list-block-with-icons' ) );
		$setting->set_field( $field );

		// add setting.
		$setting = $this->get_settings_obj()->add_setting( 'downloadlist_add_block_to_page' );
		$setting->set_section( $tasks_tab_tasks );
		$setting->prevent_export( true );
		$setting->set_save_callback( array( $this, 'add_block_in_page' ) );
		$field = new SelectPostTypeObject( $this->get_settings_obj() );
		$field->set_title( __( 'Add the block to a page', 'download-list-block-with-icons' ) );
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
		$setting = $this->get_settings_obj()->add_setting( 'downloadlist_add_block_to_post' );
		$setting->set_section( $tasks_tab_tasks );
		$setting->prevent_export( true );
		$setting->set_save_callback( array( $this, 'add_block_in_post' ) );
		$field = new SelectPostTypeObject( $this->get_settings_obj() );
		$field->set_title( __( 'Add the block to a post', 'download-list-block-with-icons' ) );
		$field->set_button_title( __( 'Choose a post', 'download-list-block-with-icons' ) );
		$field->set_popup_title( __( 'Choose and select a post', 'download-list-block-with-icons' ) );
		$field->set_endpoint( '/wp-json/wp/v2/posts' );
		$field->set_limit( 10 );
		$field->set_chosen_title( __( 'Chosen post', 'download-list-block-with-icons' ) );
		$field->set_label_title( __( 'Search for a post', 'download-list-block-with-icons' ) );
		$field->set_placeholder( __( 'Enter the name of a post', 'download-list-block-with-icons' ) );
		$field->set_cancel_button_title( __( 'Cancel', 'download-list-block-with-icons' ) );
		$setting->set_field( $field );

		// create reset URL.
		$reset_url = add_query_arg(
			array(
				'action' => 'downloadlist_reset',
				'nonce'  => wp_create_nonce( 'downloadlist-reset' ),
			),
			get_admin_url() . 'admin.php'
		);

		// create the dialog.
		$reset_dialog = array(
			'title'   => __( 'Reset plugin', 'download-list-block-with-icons' ),
			'texts'   => array(
				/* translators: %1$s will be replaced by the plugin name. */
				'<p><strong>' . sprintf( __( 'Do you real want to reset the plugin %1$s?', 'download-list-block-with-icons' ), Helper::get_plugin_name() ) . '</strong></p>',
				'<p>' . __( 'This will remove any custom icons and iconsets.', 'download-list-block-with-icons' ) . '</p>',
				'<p>' . __( 'The titles and descriptions you have set for files in download lists will also be removed.', 'download-list-block-with-icons' ) . '</p>',
				'<p>' . __( 'Any setting of this plugin will be reset.', 'download-list-block-with-icons' ) . '</p>',
				'<p>' . __( 'It will NOT remove the download lists from your content.', 'download-list-block-with-icons' ) . '</p>',
			),
			'buttons' => array(
				array(
					'action'  => 'location.href="' . $reset_url . '"',
					'variant' => 'primary',
					'text'    => __( 'Yes, reset it', 'download-list-block-with-icons' ),
				),
				array(
					'action'  => 'closeDialog();',
					'variant' => 'primary',
					'text'    => __( 'Cancel', 'download-list-block-with-icons' ),
				),
			),
		);

		// add setting.
		$setting = $this->get_settings_obj()->add_setting( 'downloadlist_reset' );
		$setting->set_section( $tasks_tab_tasks );
		$setting->prevent_export( true );
		$field = new Button( $this->get_settings_obj() );
		$field->set_title( __( 'Reset plugin', 'download-list-block-with-icons' ) );
		$field->set_button_url( '#' );
		$field->set_button_title( __( 'Reset plugin', 'download-list-block-with-icons' ) );
		$field->add_data( 'dialog', Helper::get_json( $reset_dialog ) );
		$field->add_class( 'easy-dialog-for-wordpress' );
		$setting->set_field( $field );

		// initialize this settings object.
		$this->get_settings_obj()->init();
	}

	/**
	 * Inherit the settings to all download list blocks via AJAX request.
	 *
	 * @return void
	 */
	public function inherit_settings_via_ajax(): void {
		// check nonce.
		check_ajax_referer( 'downloadlist-inherit-settings', 'nonce' );

		// bail if capability is missing.
		if ( ! current_user_can( self::get_instance()->get_settings_obj()->get_capability() ) ) {
			wp_send_json_error( null, 403 );
		}

		// bail if inheriting is already running.
		if ( $this->is_inheriting_running() ) {
			wp_send_json_error( array( 'message' => __( 'The settings are already being inherited. Please wait until this is finished.', 'download-list-block-with-icons' ) ), 409 );
		}

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

		// bail if capability is missing.
		if ( ! current_user_can( self::get_instance()->get_settings_obj()->get_capability() ) ) {
			wp_send_json_error( null, 403 );
		}

		// create the dialog.
		$dialog = array(
			'detail' => array(
				'title'   => __( 'Inherit settings', 'download-list-block-with-icons' ),
				'texts'   => array(
					'<p><strong>' . __( 'Settings for download list block have been inherited.', 'download-list-block-with-icons' ) . '</strong></p>',
					'<p>' . __( 'Please check now your download list blocks.', 'download-list-block-with-icons' ) . '</p>',
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
				esc_html( (string) get_option( 'downloadlist_inheriting_status' ) ),
				$dialog,
			)
		);
	}

	/**
	 * Return whether inheriting of settings is running at the moment.
	 *
	 * A marker older than 10 minutes is ignored, e.g. after a timeout of the request.
	 *
	 * @return bool
	 */
	private function is_inheriting_running(): bool {
		$running = absint( get_option( 'downloadlist_inheriting_running' ) );
		return $running > 0 && ( time() - $running ) < 10 * MINUTE_IN_SECONDS;
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

		// get all post types which use the block editor (posts, pages, custom post types, synced patterns, templates).
		$post_types = array_values( array_diff( get_post_types_by_support( 'editor' ), array( 'attachment' ) ) );

		// get all objects with our block.
		$query   = array(
			'post_type'              => $post_types,
			'post_status'            => 'any',
			's'                      => 'wp:downloadlist/list',
			'fields'                 => 'ids',
			'posts_per_page'         => -1,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		);
		$results = new WP_Query( $query );
		$count   = count( $results->get_posts() );

		// show progress on WP CLI.
		$progress = Helper::is_cli() ? \WP_CLI\Utils\make_progress_bar( _n( 'Updating the object', 'Updating the objects', $count, 'download-list-block-with-icons' ), $count ) : false;

		// save the count.
		update_option( 'downloadlist_inheriting_max', $count );

		// reset the count.
		update_option( 'downloadlist_inheriting_count', 0 );

		// get the values of the settings in block format (only once for all objects).
		$values = $this->get_block_values();

		// loop through the posts.
		foreach ( $results->get_posts() as $post_id ) {
			// get the post ID.
			$post_id = absint( $post_id ); // @phpstan-ignore argument.type

			// get the unformatted post title.
			$post_title = get_post_field( 'post_title', $post_id );
			if ( ! is_string( $post_title ) ) {
				$post_title = (string) $post_id;
			}

			// update the title.
			/* translators: %1$s will be replaced by the object title. */
			update_option( 'downloadlist_inheriting_status', sprintf( __( 'Updating %1$s', 'download-list-block-with-icons' ), $post_title ) );

			// get the content.
			$content = get_post_field( 'post_content', $post_id, 'raw' );
			if ( ! is_string( $content ) ) {
				$content = '';
			}

			// get the list of parsed blocks and set the values on our blocks, also in nested blocks.
			$changed = false;
			$blocks  = $this->set_values_on_blocks( parse_blocks( $content ), $values, $changed );

			// save the content only if one of our blocks has been found.
			if ( $changed ) {
				// wp_update_post() expects slashed data, so slash it to keep backslashes (e.g. in JSON of block attributes).
				wp_update_post(
					array(
						'ID'           => $post_id,
						'post_content' => wp_slash( serialize_blocks( $blocks ) ),
					)
				);
			}

			// show progress on WP CLI.
			if ( $progress ) {
				$progress->tick();
			}

			// update the count.
			update_option( 'downloadlist_inheriting_count', absint( get_option( 'downloadlist_inheriting_count' ) ) + 1 );

			// keep the marker up to date, so a long run is not seen as dead.
			update_option( 'downloadlist_inheriting_running', time() );
		}

		// set final title.
		update_option( 'downloadlist_inheriting_status', __( 'Done', 'download-list-block-with-icons' ) );

		// end progress on WP CLI.
		if ( $progress ) {
			$progress->finish();
		}

		// remove mark that inheriting is running.
		delete_option( 'downloadlist_inheriting_running' );
	}

	/**
	 * Sanitize a color setting: allow only colors we can use in our styles.
	 *
	 * @param mixed $value The value to sanitize.
	 *
	 * @return string
	 */
	public function sanitize_color( mixed $value ): string {
		// bail if value is not a string.
		if ( ! is_string( $value ) ) {
			return '';
		}

		// return the sanitized color.
		return Helper::sanitize_css_color( $value );
	}

	/**
	 * Return the values of the settings of this plugin as block attributes.
	 *
	 * @return array<string,bool|int|string>
	 */
	private function get_block_values(): array {
		$values = array();
		foreach ( $this->get_settings_obj()->get_settings() as $setting_obj ) {
			// get the block name of this setting.
			$settings_block_name = $setting_obj->get_custom_var( 'block-name' );

			// bail if block-name is not set as custom var.
			if ( empty( $settings_block_name ) || ! is_string( $settings_block_name ) ) {
				continue;
			}

			// get value.
			$value = get_option( $setting_obj->get_name() );

			// convert value depending on settings to block compatible value.
			switch ( $setting_obj->get_custom_var( 'block-format' ) ) {
				case 'bool':
					$value = 1 === absint( $value );
					break;
				case 'int':
					$value = absint( $value );
					break;
				default:
					$value = is_scalar( $value ) ? (string) $value : '';
					break;
			}

			$values[ $settings_block_name ] = $value;
		}
		return $values;
	}

	/**
	 * Set the given values on all download list blocks in the given list, including nested blocks.
	 *
	 * @param array<int|string,array<string,mixed>> $blocks The parsed blocks.
	 * @param array<string,bool|int|string>         $values The values to set.
	 * @param bool                                  $changed Will be set to true if one of our blocks has been found.
	 *
	 * @return array<int|string,array<string,mixed>>
	 * @phpstan-param array<int|string, array{blockName: string|null, attrs: array<mixed>, innerBlocks: array<mixed>[], innerHTML: string, innerContent: array<mixed>}> $blocks
	 * @phpstan-return array<int|string, array{blockName: string|null, attrs: array<mixed>, innerBlocks: array<mixed>[], innerHTML: string, innerContent: array<mixed>}>
	 */
	private function set_values_on_blocks( array $blocks, array $values, bool &$changed ): array {
		foreach ( $blocks as $index => $block ) {
			// handle nested blocks (e.g. in groups or columns).
			if ( ! empty( $block['innerBlocks'] ) ) {
				$blocks[ $index ]['innerBlocks'] = $this->set_values_on_inner_blocks( $block['innerBlocks'], $values, $changed );
			}

			// bail if block does not match.
			if ( 'downloadlist/list' !== $block['blockName'] ) {
				continue;
			}

			// set the values.
			foreach ( $values as $name => $value ) {
				$blocks[ $index ]['attrs'][ $name ] = $value;
			}
			$changed = true;
		}
		return $blocks;
	}

	/**
	 * Set the given values on all download list blocks in the given list of inner blocks, including their inner blocks.
	 *
	 * @param array<array<mixed>>           $blocks The inner blocks.
	 * @param array<string,bool|int|string> $values The values to set.
	 * @param bool                          $changed Will be set to true if one of our blocks has been found.
	 *
	 * @return array<array<mixed>>
	 */
	private function set_values_on_inner_blocks( array $blocks, array $values, bool &$changed ): array {
		foreach ( $blocks as $index => $block ) {
			// handle nested blocks.
			if ( ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
				$blocks[ $index ]['innerBlocks'] = $this->set_values_on_inner_blocks( $block['innerBlocks'], $values, $changed );
			}

			// bail if block does not match.
			if ( empty( $block['blockName'] ) || 'downloadlist/list' !== $block['blockName'] ) {
				continue;
			}

			// set the values.
			$attrs = isset( $block['attrs'] ) && is_array( $block['attrs'] ) ? $block['attrs'] : array();
			foreach ( $values as $name => $value ) {
				$attrs[ $name ] = $value;
			}
			$blocks[ $index ]['attrs'] = $attrs;
			$changed                   = true;
		}
		return $blocks;
	}

	/**
	 * Return the description for the setting of the preview images.
	 *
	 * @return string
	 */
	private function get_preview_description(): string {
		$description  = __( 'Shows a preview image instead of the icon for files with a preview, e.g. images and PDF files. Files without preview keep their icon.', 'download-list-block-with-icons' );
		$description .= ' ' . __( 'Hint: the preview images are public in the uploads directory, even if the files are protected by another plugin.', 'download-list-block-with-icons' );

		// add a hint if this server could not generate previews of PDF files (only shown in backend).
		if ( is_admin() && ! Helper::is_pdf_preview_supported() ) {
			$description .= '<br><strong>' . __( 'This server cannot generate preview images of PDF files. Ask your hosting for Imagick with Ghostscript and a policy which allows PDF files.', 'download-list-block-with-icons' ) . '</strong>';
		}

		// return the description.
		return $description;
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

		// add the tab, if set.
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

		// show a hint for folders of folder plugins, if one is used.
		$folder_taxonomies = Helper::get_folder_taxonomies();
		if ( ! empty( $folder_taxonomies ) ) {
			/* translators: %1$s will be replaced by the name of the folder plugin, e.g. "Folderly". */
			echo '<p>' . esc_html( sprintf( __( 'A download list block can also show all files of a folder from %1$s. Choose the folder in the toolbar of the block with "Choose a download list or folder".', 'download-list-block-with-icons' ), implode( ', ', $folder_taxonomies ) ) ) . '</p>';
		}
	}

	/**
	 * Return the description for the setting to use folders of folder plugins.
	 *
	 * @param bool $with_plugins True to add the detected folder plugins (only possible after "init").
	 *
	 * @return string
	 */
	private function get_folder_support_description( bool $with_plugins = false ): string {
		$description = __( 'If a plugin for folders in the media library like Folderly, HappyFiles or Folders is used, a download list block can show all files of one of its folders. Lists which use a folder show no files while this is disabled.', 'download-list-block-with-icons' );

		// bail if the detected folder plugins should not be shown.
		if ( ! $with_plugins ) {
			return $description;
		}

		// get the detected folder plugins, also if the support is disabled.
		$taxonomies = Helper::get_folder_taxonomies( true );
		if ( empty( $taxonomies ) ) {
			return $description . ' ' . __( 'No such plugin has been found in this project.', 'download-list-block-with-icons' );
		}

		/* translators: %1$s will be replaced by the names of the folder plugins. */
		return $description . ' ' . sprintf( __( 'Found in this project: %1$s', 'download-list-block-with-icons' ), implode( ', ', array_map( 'esc_html', $taxonomies ) ) );
	}

	/**
	 * Show whether the abilities could be used in this project and what to do for it.
	 *
	 * @return void
	 */
	public function show_abilities_status(): void {
		$checks = array();

		// check the Abilities API.
		if ( ! Abilities::is_api_available() ) {
			$checks[] = array( false, __( 'The Abilities API is not available. It is part of WordPress since version 6.9. Update WordPress to use the abilities.', 'download-list-block-with-icons' ) );
		} else {
			$checks[] = array( true, __( 'The Abilities API of WordPress is available.', 'download-list-block-with-icons' ) );
		}

		// check the setting.
		if ( ! Abilities::is_enabled() ) {
			$checks[] = array( false, __( 'The abilities of this plugin are disabled above.', 'download-list-block-with-icons' ) );
		} else {
			$names = Abilities::get_instance()->get_ability_names( Abilities::get_mode() );
			/* translators: %1$s will be replaced by a list of names. */
			$checks[] = array( true, sprintf( __( 'Offered abilities: %1$s', 'download-list-block-with-icons' ), '<code>' . implode( '</code>, <code>', array_map( 'esc_html', $names ) ) . '</code>' ) );
		}

		// check the REST API, which is used by external clients.
		/* translators: %1$s will be replaced by a URL. */
		$checks[] = array( true, sprintf( __( 'External clients find the abilities in the REST API under %1$s.', 'download-list-block-with-icons' ), '<code>' . esc_html( rest_url( 'wp-abilities/v1/abilities' ) ) . '</code>' ) );

		// check HTTPS.
		if ( ! wp_is_using_https() ) {
			$checks[] = array( false, __( 'This website does not use HTTPS. Connections from external clients would not be encrypted, and application passwords are only available with HTTPS (except in local development environments).', 'download-list-block-with-icons' ) );
		}

		// check application passwords, which most external clients use to log in.
		if ( ! wp_is_application_passwords_available() ) {
			$checks[] = array( false, __( 'Application passwords are not available. Most external clients need them to log in. They could be disabled by another plugin, by the hosting or because this website does not use HTTPS.', 'download-list-block-with-icons' ) );
		} else {
			/* translators: %1$s will be replaced by a URL. */
			$checks[] = array( true, sprintf( __( 'Application passwords are available. You can create one in <a href="%1$s">your profile</a>.', 'download-list-block-with-icons' ), esc_url( admin_url( 'profile.php#application-passwords-section' ) ) ) );
		}

		// output the checks.
		echo '<ul class="downloadlist-abilities-status">';
		foreach ( $checks as $check ) {
			echo '<li><span class="dashicons ' . ( $check[0] ? 'dashicons-yes-alt' : 'dashicons-warning' ) . '" style="color: ' . ( $check[0] ? '#008a20' : '#d63638' ) . '"></span> ' . wp_kses_post( $check[1] ) . '</li>';
		}
		echo '</ul>';

		// show what to do.
		echo '<p><strong>' . esc_html__( 'How to use the abilities:', 'download-list-block-with-icons' ) . '</strong></p>';
		echo '<ol>';
		echo '<li>' . esc_html__( 'Connect your AI client or automation with this website. For AI clients which use the Model Context Protocol (MCP), you need a plugin or tool which makes the Abilities API of WordPress available via MCP. Other tools can use the REST API directly.', 'download-list-block-with-icons' ) . '</li>';
		echo '<li>' . esc_html__( 'Let the client log in as a user with the necessary rights, e.g. with an application password. The abilities only do what this user is allowed to do.', 'download-list-block-with-icons' ) . '</li>';
		echo '<li>' . esc_html__( 'Keep "Read only" unless the client should change content. You can change this here at any time.', 'download-list-block-with-icons' ) . '</li>';
		echo '</ol>';
	}

	/**
	 * Show the list of all objects which use our block.
	 *
	 * @return void
	 */
	public function show_usage(): void {
		// bail if the table could not be loaded outside of WP admin.
		if ( ! is_admin() ) {
			return;
		}

		// load and show the table.
		$table = new Usage_Table();
		$table->prepare_items();
		echo '<h2>' . esc_html__( 'Usage of the download list block', 'download-list-block-with-icons' ) . '</h2>';
		echo '<p>' . esc_html__( 'These posts, pages, patterns and templates contain at least one download list. Content which only shows a synced pattern with a download list is not listed separately.', 'download-list-block-with-icons' ) . '</p>';
		$table->display();
	}

	/**
	 * Add the block to a given page.
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
		$transient_obj->set_message( '<strong>' . sprintf( __( 'The block has been added to %1$s.', 'download-list-block-with-icons' ), '<em>' . esc_html( $title ) . '</em>' ) . '</strong> ' . sprintf( __( '<a href="%1$s">Edit the page</a> and add the files you want to list to the block.', 'download-list-block-with-icons' ), esc_url( $edit_url ) ) );
		$transient_obj->save();
	}

	/**
	 * Add the block to a given post.
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
		$transient_obj->set_message( '<strong>' . sprintf( __( 'The block has been added to the post %1$s.', 'download-list-block-with-icons' ), '<em>' . esc_html( $title ) . '</em>' ) . '</strong> ' . sprintf( __( '<a href="%1$s">Edit the post</a> and add the files you want to list to the block.', 'download-list-block-with-icons' ), esc_url( $edit_url ) ) );
		$transient_obj->save();
	}

	/**
	 * Add the block to any post-entry.
	 *
	 * @param int $post_id The post ID to use.
	 * @return void
	 */
	private function add_block( int $post_id ): void {
		// get the content.
		$content = get_post_field( 'post_content', $post_id, 'raw' );

		// bail if no content could be loaded.
		if ( ! is_string( $content ) ) {
			return;
		}

		// add our own block at the end.
		$content .= '<!-- wp:downloadlist/list /-->';

		// save the content. wp_update_post() expects slashed data, so slash it to keep backslashes.
		$query = array(
			'ID'           => $post_id,
			'post_content' => wp_slash( $content ),
		);
		wp_update_post( $query );
	}

	/**
	 * Reset plugin styles by request.
	 *
	 * @return void
	 */
	public function reset_styles_by_request(): void {
		// check nonce.
		check_admin_referer( 'downloadlist-reset-css', 'nonce' );

		// bail if capability is missing.
		if ( ! current_user_can( self::get_instance()->get_settings_obj()->get_capability() ) ) {
			wp_die( esc_html__( 'Sorry, you are not allowed to do this.', 'download-list-block-with-icons' ), 403 );
		}

		// reset it.
		Helper::regenerate_icons();
		Helper::generate_css();

		// add success message.
		$transient_obj = Transients::get_instance()->add();
		$transient_obj->set_name( 'downloadlist_reset_css' );
		$transient_obj->set_type( 'success' );
		$transient_obj->set_message( '<strong>' . __( 'The styles for the plugin has been reset.', 'download-list-block-with-icons' ) . '</strong> ' . __( 'You should now see the actual icons on your lists.', 'download-list-block-with-icons' ) );
		$transient_obj->save();

		// forward user.
		wp_safe_redirect( (string) wp_get_referer() );
		exit;
	}

	/**
	 * Reset the plugin by request.
	 *
	 * @return void
	 */
	public function reset_plugin_by_request(): void {
		// check nonce.
		check_admin_referer( 'downloadlist-reset', 'nonce' );

		// bail if capability is missing.
		if ( ! current_user_can( self::get_instance()->get_settings_obj()->get_capability() ) ) {
			wp_die( esc_html__( 'Sorry, you are not allowed to do this.', 'download-list-block-with-icons' ), 403 );
		}

		// run it.
		Uninstaller::get_instance()->run( false );
		Installer::get_instance()->activation();

		// add success message.
		$transient_obj = Transients::get_instance()->add();
		$transient_obj->set_name( 'downloadlist_reset' );
		$transient_obj->set_type( 'success' );
		$transient_obj->set_message( '<strong>' . __( 'The plugin has been reset.', 'download-list-block-with-icons' ) . '</strong> ' . __( 'You can now use it with initial settings.', 'download-list-block-with-icons' ) );
		$transient_obj->save();

		// forward user.
		wp_safe_redirect( (string) wp_get_referer() );
		exit;
	}

	/**
	 * Return the configured settings object.
	 *
	 * @return \easySettingsForWordPress\Settings
	 */
	public function get_settings_obj(): \easySettingsForWordPress\Settings {
		/**
		 * Get the object one time.
		 */
		if ( null === $this->settings_obj ) {
			$this->settings_obj = new \easySettingsForWordPress\Settings( DL_PLUGIN );
		}

		// return it.
		return $this->settings_obj;
	}
}
