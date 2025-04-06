<?php
/**
 * File for handling site health options of this plugin.
 *
 * @package download-list-block-with-icons
 */

namespace downloadlist;

// prevent direct access.
defined( 'ABSPATH' ) || exit;

use WP_Screen;

/**
 * Helper-function for Dashboard options of this plugin.
 */
class Help_System {
	/**
	 * Instance of this object.
	 *
	 * @var ?Help_System
	 */
	private static ?Help_System $instance = null;

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
	public static function get_instance(): Help_System {
		if ( ! static::$instance instanceof static ) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	/**
	 * Initialize the site health support.
	 *
	 * @return void
	 */
	public function init(): void {
		add_action( 'current_screen', array( $this, 'add_help' ) );
		add_filter( 'downloadlist_light_help_tabs', array( $this, 'add_use_block_help' ) );
		add_filter( 'downloadlist_light_help_tabs', array( $this, 'add_manage_icon_help' ) );
		add_filter( 'downloadlist_light_help_tabs', array( $this, 'add_use_icon_help' ) );
		add_filter( 'downloadlist_light_help_tabs', array( $this, 'add_manage_iconsets_help' ) );
	}

	/**
	 * Add the help box to our own pages with the configured contents.
	 *
	 * @param WP_Screen $screen The screen object.
	 *
	 * @return void
	 */
	public function add_help( WP_Screen $screen ): void {
		// bail if we are not in our cpt.
		if ( 'dl_icons' !== $screen->post_type ) {
			return;
		}

		// get the help tabs.
		$help_tabs = $this->get_help_tabs();

		// bail if list is empty.
		if ( empty( $help_tabs ) ) {
			return;
		}

		// add our own help tabs.
		foreach ( $help_tabs as $help_tab ) {
			$screen->add_help_tab( $help_tab );
		}

		// add the sidebar.
		$this->add_sidebar( $screen );
	}

	/**
	 * Add the sidebar with its content.
	 *
	 * @param WP_Screen $screen The screen object.
	 *
	 * @return void
	 */
	private function add_sidebar( WP_Screen $screen ): void {
		// get content for sidebar.
		$sidebar_content = '<p><strong>' . __( 'Question not answered?', 'download-list-block-with-icons' ) . '</strong></p><p><a href="' . esc_url( Helper::get_plugin_support_url() ) . '" target="_blank">' . esc_html__( 'Ask in our forum', 'download-list-block-with-icons' ) . '</a></p>';

		/**
		 * Filter the sidebar content.
		 *
		 * @since 3.8.0 Available since 3.8.0.
		 * @param string $sidebar_content The content.
		 */
		$sidebar_content = apply_filters( 'downloadlist_help_sidebar_content', $sidebar_content );

		// add help sidebar with the given content.
		$screen->set_help_sidebar( $sidebar_content );
	}

	/**
	 * Return the list of help tabs.
	 *
	 * @return array
	 */
	private function get_help_tabs(): array {
		$list = array();

		/**
		 * Filter the list of help tabs with its contents.
		 *
		 * @since 3.8.0 Available since 3.8.0.
		 * @param array $list List of help tabs.
		 */
		return apply_filters( 'downloadlist_light_help_tabs', $list );
	}

	/**
	 * Add icon management help.
	 *
	 * @param array $help_list List if help texts.
	 * @return array
	 */
	public function add_manage_icon_help( array $help_list ): array {
		// create links.
		$iconset_url = add_query_arg( array( 'taxonomy' => 'dl_icon_set', 'post_type' => 'dl_icons' ), admin_url( 'edit-tags.php' ) );
		$new_icon_url = add_query_arg( array( 'post_type' => 'dl_icons' ), 'post-new.php' );

		// create content for this help page.
		$content = '<h2>' . __( 'Managing Download List Icons', 'download-list-block-with-icons' ) . '</h2><p>' . __( 'Icons are used to make your download list look prettier. You have the option of using icon sets with ready-made icons as well as individual icons.', 'download-list-block-with-icons' ) . '</p>';
		$content .= '<h3>' . __( 'Add individual icons', 'download-list-block-with-icons' ) . '</h3>';
		$content .= '<ol>';
		$content .= '<li>' . sprintf( __( 'Go to <a href="%1$s">Iconsets</a> and add a new iconset.', 'download-list-block-with-icons' ), $iconset_url ) . '</li>';
		$content .= '<li>' . sprintf( __( 'Now go to the page where you can <a href="%1$s">add a new icon</a>.', 'download-list-block-with-icons' ), $new_icon_url ) . '</li>';
		$content .= '<li>' . __( 'First give the new icon a title. This title is never used public, its only for you.', 'download-list-block-with-icons' ) . '</li>';
		$content .= '<li>' . __( 'Then select the graphic you want to use as an icon.', 'download-list-block-with-icons' ) . '</li>';
		$content .= '<li>' . __( 'Then select the file type where you want to use this graphic.', 'download-list-block-with-icons' ) . '</li>';
		$content .= '<li>' . __( 'Then select the iconset you created in the first step.', 'download-list-block-with-icons' ) . '</li>';
		$content .= '<li>' . __( 'Save this settings and use it in the block.', 'download-list-block-with-icons' ) . '</li>';
		$content .= '</ol>';
		$content .= '<h3>' . __( 'Delete individual icons', 'download-list-block-with-icons' ) . '</h3>';
		$content .= '<p>' . __( 'You can also delete icons you have saved at any time. Please note that this will also change the output in the frontend. Files that previously used the icon will no longer have one.', 'download-list-block-with-icons' ) . '</p>';

		// add help for the icons.
		$help_list[] = array(
			'id'      => 'dl_icons_managing',
			'title'   => __( 'Managing icons', 'download-list-block-with-icons' ),
			'content' => $content,
		);

		// return the resulting help list.
		return $help_list;
	}

	/**
	 * Add icon usage help.
	 *
	 * @param array $help_list List if help texts.
	 * @return array
	 */
	public function add_use_icon_help( array $help_list ): array {
		// create content for this help page.
		$content = '<h2>' . __( 'Use Download List Icons', 'download-list-block-with-icons' ) . '</h2><p>' . __( 'You can display icons for each file in your download files. This is done automatically based on the file type of the file displayed in the list.', 'download-list-block-with-icons' ) . '</p>';

		// add help for the icons.
		$help_list[] = array(
			'id'      => 'dl_icons_usage',
			'title'   => __( 'Use icons', 'download-list-block-with-icons' ),
			'content' => $content,
		);

		// return the resulting help list.
		return $help_list;
	}

	/**
	 * Add iconset usage help.
	 *
	 * @param array $help_list List if help texts.
	 * @return array
	 */
	public function add_manage_iconsets_help( array $help_list ): array {
		// create links.
		$iconset_url = add_query_arg( array( 'taxonomy' => 'dl_icon_set', 'post_type' => 'dl_icons' ), admin_url( 'edit-tags.php' ) );

		// create content for this help page.
		$content = '<h2>' . __( 'Managing Download List Iconsets', 'download-list-block-with-icons' ) . '</h2><p>' . __( 'Icon sets are a collection of icons that can be used for many file types. They simplify the management of icons for your files.', 'download-list-block-with-icons' ) . '</p>';
		$content .= '<h3>' . __( 'Types of iconsets', 'download-list-block-with-icons' ) . '</h3>';
		$content .= '<p>' . __( 'There are 2 types of iconsets:', 'download-list-block-with-icons' ) . '</p>';
		$content .= '<ul>';
		$content .= '<li>' . __( 'Generic - uses an icon font to provide icons', 'download-list-block-with-icons' ) . '</li>';
		$content .= '<li>' . __( 'Custom - uses individual icons', 'download-list-block-with-icons' ) . '</li>';
		$content .= '</ul>';
		$content .= '<h3>' . __( 'Adding an iconset', 'download-list-block-with-icons' ) . '</h3>';
		$content .= '<ol>';
		$content .= '<li>' . sprintf( __( 'Go to <a href="%1$s">Iconsets</a>.', 'download-list-block-with-icons' ), $iconset_url ) . '</li>';
		$content .= '<li>' . __( 'Set a name. This will not used in public, its only for you.', 'download-list-block-with-icons' ) . '</li>';
		$content .= '<li>' . __( 'Set width and height for the icons to show.', 'download-list-block-with-icons' ) . '</li>';
		$content .= '<li>' . __( 'Save the settings.', 'download-list-block-with-icons' ) . '</li>';
		$content .= '</ol>';
		$content .= '<strong>' . __( 'Hint', 'download-list-block-with-icons' ) . '</strong> ' . __( 'You have to add custom icons to use this iconset on your download lists.', 'download-list-block-with-icons' ) . '</p>' ;

		// add help for the icons.
		$help_list[] = array(
			'id'      => 'dl_iconset_usage',
			'title'   => __( 'Managing iconsets', 'download-list-block-with-icons' ),
			'content' => $content,
		);

		// return the resulting help list.
		return $help_list;
	}

	/**
	 * Add block usage help.
	 *
	 * @param array $help_list List if help texts.
	 * @return array
	 */
	public function add_use_block_help( array $help_list ): array {
		// create content for this help page.
		$content = '<h2>' . __( 'Use Download List with Icons Block', 'download-list-block-with-icons' ) . '</h2><p>' . __( '', 'download-list-block-with-icons' ) . '</p>';
		$content .= '<ol>';
		$content .= '<li>' . __( 'Go to “Create new page” under “Pages” in the WordPress backend', 'download-list-block-with-icons' ) . '</li>';
		$content .= '<li>' . __( 'Add the “Download List with Icons” block there.', 'download-list-block-with-icons' ) . '</li>';
		$content .= '<li>' . __( 'You will see a button where you can open your media library and choose which files you want to use for this list.', '' ) . '</li>';
		$content .= '<li>' . __( 'You can sort the files by its date or title on the options above the list.', 'download-list-block-with-icons' ) . '</li>';
		$content .= '<li>' . __( 'On the sidebar on the right you can set how the list should be presented in frontend.', 'download-list-block-with-icons' ) . '</li>';
		$content .= '</ol>';

		// add help for the block.
		$help_list[] = array(
			'id'      => 'dl_icons_block',
			'title'   => __( 'Use the block', 'download-list-block-with-icons' ),
			'content' => $content,
		);

		// return the resulting help list.
		return $help_list;
	}
}
