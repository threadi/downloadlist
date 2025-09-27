<?php
/**
 * This file contains the handling for our own icons post type.
 *
 * @package download-list-block-with-icons
 */

namespace DownloadListWithIcons\Icons;

// prevent direct access.
defined( 'ABSPATH' ) || exit;

use DownloadListWithIcons\Iconsets\Iconsets;
use DownloadListWithIcons\Plugin\Helper;
use stdClass;
use WP_Post;

/**
 * Object to handle our icons post type.
 */
class Icons {
	/**
	 * Instance of actual object.
	 *
	 * @var ?Icons
	 */
	private static ?Icons $instance = null;

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
	 * @return Icons
	 */
	public static function get_instance(): Icons {
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
		// add hooks.
		add_action( 'init', array( $this, 'register' ) );
		add_filter( 'post_updated_messages', array( $this, 'change_post_labels' ) );
		add_filter( 'bulk_post_updated_messages', array( $this, 'change_post_labels_bulk' ), 10, 2 );
		add_action( 'save_post_dl_icons', array( $this, 'check_taxonomy' ) );
		add_action( 'add_meta_boxes_dl_icons', array( $this, 'add_meta_boxes' ), 10, 0 );
		add_action( 'manage_dl_icons_posts_custom_column', array( $this, 'set_icons_column' ), 10, 2 );
		add_filter( 'wp_count_posts', array( $this, 'reduce_count' ), 10, 2 );
		add_action( 'trashed_post', array( $this, 'trash_post' ) );
	}

	/**
	 * Add icon as custom posttype.
	 *
	 * @return void
	 */
	public function register(): void {
		// set labels for our own cpt.
		$labels = array(
			'name'              => __( 'Download List Icons', 'download-list-block-with-icons' ),
			'singular_name'     => __( 'Download List Icon', 'download-list-block-with-icons' ),
			'menu_name'         => __( 'Download List Icons', 'download-list-block-with-icons' ),
			'parent_item_colon' => __( 'Parent Download List  Icon', 'download-list-block-with-icons' ),
			'all_items'         => __( 'All Icons', 'download-list-block-with-icons' ),
			'add_new'           => __( 'Add new Icon', 'download-list-block-with-icons' ),
			'add_new_item'      => __( 'Add new Icon', 'download-list-block-with-icons' ),
			'edit_item'         => __( 'Edit Icon', 'download-list-block-with-icons' ),
			'view_item'         => __( 'View Download List Icon', 'download-list-block-with-icons' ),
			'view_items'        => __( 'View Download List Icons', 'download-list-block-with-icons' ),
			'search_items'      => __( 'Search Download List Icon', 'download-list-block-with-icons' ),
			'not_found'         => __( 'Not Found', 'download-list-block-with-icons' ),
		);

		// set arguments for our own cpt.
		$args = array(
			'label'               => $labels['name'],
			'description'         => '',
			'labels'              => $labels,
			'supports'            => array( 'title' ),
			'public'              => false,
			'hierarchical'        => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => true,
			'show_in_admin_bar'   => true,
			'has_archive'         => false,
			'can_export'          => false,
			'exclude_from_search' => true,
			'taxonomies'          => array( 'dl_icon_set' ),
			'publicly_queryable'  => false,
			'show_in_rest'        => false,
			'capability_type'     => 'post',
			'rewrite'             => array(
				'slug' => 'downloadlist_icons',
			),
			'menu_icon'           => trailingslashit( plugin_dir_url( DL_PLUGIN ) ) . 'gfx/dl_icon.png',
		);
		register_post_type( 'dl_icons', $args );
	}

	/**
	 * Check the taxonomy on each icon-cpt-item.
	 *
	 * @param int $post_id The post-ID.
	 * @return void
	 */
	public function check_taxonomy( int $post_id ): void {
		// bail if nonce does not match.
		if ( ! empty( $_POST['_wpnonce'] ) && false === wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'update-post_' . $post_id ) ) {
			return;
		}

		// do nothing if post is in trash or auto-draft.
		if ( in_array( get_post_status( $post_id ), array( 'trash', 'auto-draft' ), true ) ) {
			return;
		}

		// save assigned icon-file.
		if ( ! empty( $_POST['icon'] ) ) {
			update_post_meta( $post_id, 'icon', absint( $_POST['icon'] ) );
		}

		// save assigned file-type.
		if ( ! empty( $_POST['file_type'] ) ) {
			update_post_meta( $post_id, 'file_type', sanitize_text_field( wp_unslash( $_POST['file_type'] ) ) );
		} elseif ( 'draft' !== get_post_status( $post_id ) ) {
			delete_post_meta( $post_id, 'file_type' );
		}

		// save given unicode.
		if ( ! empty( $_POST['unicode'] ) ) {
			// get the unicode from request.
			$unicode = sanitize_text_field( wp_unslash( $_POST['unicode'] ) );

			// add slash before the code.
			$unicode = '\\\\' . $unicode;

			// save it.
			update_post_meta( $post_id, 'unicode', $unicode );
		} elseif ( 'draft' !== get_post_status( $post_id ) ) {
			delete_post_meta( $post_id, 'unicode' );
		}

		// get iconset.
		$iconset_terms = wp_get_object_terms( $post_id, 'dl_icon_set' );
		if ( is_array( $iconset_terms ) && ! empty( $iconset_terms ) ) {
			// regenerate icons and style of the chosen iconset.
			Helper::regenerate_icons( $iconset_terms[0]->term_id );
			Helper::generate_css( $iconset_terms[0]->term_id );
		}
	}

	/**
	 * Update the messages after updating or deleting posts in our cpt.
	 *
	 * @param array<string,array<int,string>> $messages List of messages.
	 * @return array<string,array<int,string>>
	 */
	public function change_post_labels( array $messages ): array {
		$messages['dl_icons'] = array(
			1 => __( 'Icon updated.', 'download-list-block-with-icons' ),
			6 => __( 'Icon added.', 'download-list-block-with-icons' ),
		);
		return $messages;
	}

	/**
	 * Update the messages on bulk-actions in our cpt.
	 *
	 * @param array<string,array<string,mixed>> $messages List of messages.
	 * @param array<string,mixed>               $bulk_counts Count of events.
	 * @return array<string,array<string,mixed>>
	 */
	public function change_post_labels_bulk( array $messages, array $bulk_counts ): array {
		/* translators: %1$d: Number of pages. */
		$messages['dl_icons']['trashed'] = _n( '%1$d icon moved to the trash.', '%1$d icons moved to the trash.', absint( $bulk_counts['trashed'] ), 'download-list-block-with-icons' );
		/* translators: %1$d: Number of pages. */
		$messages['dl_icons']['untrashed'] = _n( '%1$d icon restored from the trash.', '%1$d icon restored from the trash.', absint( $bulk_counts['untrashed'] ), 'download-list-block-with-icons' );

		// return resulting list.
		return $messages;
	}

	/**
	 * Show meta-box for cpts where the assigned term has the type "custom".
	 *
	 * @return void
	 */
	public function add_meta_boxes(): void {
		// add meta-box to add icons.
		add_meta_box(
			'downloadlist_custom_icons',
			__( 'Settings', 'download-list-block-with-icons' ),
			array( $this, 'get_meta_box_settings' ),
			'dl_icons'
		);

		// add meta-box with help options.
		add_meta_box(
			'downloadlist_help',
			__( 'Need help?', 'download-list-block-with-icons' ),
			array( $this, 'get_meta_box_help' ),
			'dl_icons'
		);
	}

	/**
	 * Form to choose a single icon image from media library and set the file-type for this entry.
	 *
	 * @param WP_Post $post The post.
	 * @return void
	 */
	public function get_meta_box_settings( WP_Post $post ): void {
		// get image_id of the icon.
		$image_id = absint( get_post_meta( $post->ID, 'icon', true ) );

		// get file-type.
		$file_type = get_post_meta( $post->ID, 'file_type', true );

		// get unicode.
		$unicode = get_post_meta( $post->ID, 'unicode', true );

		// output.
		?>
		<div class="form-field">
			<label for="icon"><?php echo esc_html__( 'Choose icon', 'download-list-block-with-icons' ); ?>:</label>
			<div>
				<?php
				$image = wp_get_attachment_image_src( $image_id );
				if ( $image_id > 0 && $image ) {
					?>
					<a href="#" class="downloadlist-image-choose"><img src="<?php echo esc_url( $image[0] ); ?>" alt="" /></a>
					<a href="#" class="downloadlist-image-remove button button-primary"><?php echo esc_html__( 'Remove image', 'download-list-block-with-icons' ); ?></a>
					<input type="hidden" name="icon" value="<?php echo absint( $image_id ); ?>">
					<?php
				} else {
					?>
					<a href="#" class="downloadlist-image-choose"><?php echo esc_html__( 'Upload or choose image', 'download-list-block-with-icons' ); ?></a>
					<a href="#" class="downloadlist-image-remove button button-primary" style="display:none"><?php echo esc_html__( 'Remove image', 'download-list-block-with-icons' ); ?></a>
					<input type="hidden" name="icon" value="">
					<?php
				}
				?>
			</div>
		</div>
		<div class="form-field">
			<label for="file_type"><?php echo esc_html__( 'Choose file type', 'download-list-block-with-icons' ); ?>:</label>
			<select name="file_type" id="file_type">
				<option value="">&nbsp;</option>
				<?php
				foreach ( Helper::get_mime_types() as $label => $mime_type ) {
					?>
					<option value="<?php echo esc_attr( $mime_type ); ?>"<?php echo $mime_type === $file_type ? ' selected="selected"' : ''; ?>><?php echo esc_html( $label ); ?></option>
					<?php
				}
				?>
			</select>
		</div>
		<div class="form-field">
			<label for="unicode"><?php echo esc_html__( 'Unicode for icon', 'download-list-block-with-icons' ); ?>:</label>
			<input type="text" name="unicode" id="unicode" value="<?php echo esc_attr( $unicode ); ?>">
		</div>
		<?php
	}

	/**
	 * Show help options.
	 *
	 * @return void
	 */
	public function get_meta_box_help(): void {
		/* translators: %1$s will be replaced by the URL for our support forum. */
		echo wp_kses_post( sprintf( __( 'You are welcome to contact <a href="%1$s" target="_blank">our support forum (opens new window)</a> if you have any questions.', 'download-list-block-with-icons' ), esc_url( Helper::get_plugin_support_url() ) ) );
	}

	/**
	 * Show file type for single icon in listing.
	 *
	 * @param string $column_name The column name.
	 * @param int    $post_id The ID of the post.
	 * @return void
	 */
	public function set_icons_column( string $column_name, int $post_id ): void {
		// bail if this is not our column.
		if ( 'downloadlist_file_type' !== $column_name ) {
			return;
		}

		// get the file type.
		$file_type = get_post_meta( $post_id, 'file_type', true );

		// get all types.
		$file_types = Helper::get_mime_types();

		// bail if type is not in list of types.
		if ( ! in_array( $file_type, $file_types, true ) ) {
			return;
		}

		// get the search result.
		$result = array_search( $file_type, $file_types, true );

		// bail if result is false.
		if ( ! $result ) {
			return;
		}

		// show the name of the type.
		echo esc_html( (string) $result );
	}

	/**
	 * Reduce the count of items in statistic for list view.
	 *
	 * @param stdClass $counts Object with counts.
	 * @param string   $type The requested post type.
	 * @return stdClass
	 */
	public function reduce_count( stdClass $counts, string $type ): stdClass {
		if ( 'dl_icons' !== $type ) {
			return $counts;
		}

		// reduce the count with the amount of generic sets.
		$counts->publish -= count( Iconsets::get_instance()->get_generic_sets_cpts() );

		// return resulting object.
		return $counts;
	}

	/**
	 * Regenerate styles if icon is sent to trash.
	 *
	 * @param int $post_id The ID of the requested post.
	 * @return void
	 */
	public function trash_post( int $post_id ): void {
		// bail if type is not ours.
		if ( 'dl_icons' !== get_post_type( $post_id ) ) {
			return;
		}

		// regenerate all icons.
		Helper::regenerate_icons();

		// regenerate the css.
		Helper::generate_css();
	}
}
