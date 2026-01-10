<?php
/**
 * File to handle all taxonomies of this plugin.
 *
 * @package download-list-block-with-icons
 */

namespace DownloadListWithIcons\Plugin;

// prevent direct access.
defined( 'ABSPATH' ) || exit;

use DownloadListWithIcons\Iconsets\Iconset_Base;
use DownloadListWithIcons\Iconsets\Iconsets;
use WP_Taxonomy;
use WP_Term;

/**
 * Object to handle all taxonomies of this plugin.
 */
class Taxonomies {

	/**
	 * Instance of this object.
	 *
	 * @var ?Taxonomies
	 */
	private static ?Taxonomies $instance = null;

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
	public static function get_instance(): Taxonomies {
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
		// use hooks.
		add_action( 'init', array( $this, 'register' ) );
		add_action( 'dl_icon_set_pre_add_form', array( $this, 'show_iconset_description' ) );
		add_action( 'dl_icon_lists_pre_add_form', array( $this, 'show_list_description' ) );
		add_filter( 'manage_edit-dl_icon_set_columns', array( $this, 'set_iconset_columns' ) );
		add_filter( 'manage_edit-dl_icon_lists_columns', array( $this, 'set_lists_columns' ) );
		add_filter( 'ajax_term_search_results', array( $this, 'filter_icon_taxonomy_ajax' ), 10, 2 );
		add_action( 'dl_icon_set_add_form_fields', array( $this, 'add_icon_set_fields' ) );
		add_action( 'dl_icon_set_edit_form_fields', array( $this, 'add_icon_set_fields' ), 10 );
		add_action( 'created_term', array( $this, 'save_icon_set_fields' ), 10, 3 );
		add_action( 'edit_term', array( $this, 'save_icon_set_fields' ), 10, 3 );
		add_filter( 'manage_edit-dl_icons_columns', array( $this, 'set_icons_columns' ) );
		add_filter( 'manage_dl_icon_set_custom_column', array( $this, 'set_iconset_column' ), 10, 3 );

		// use our own hooks.
		add_filter( 'downloadlist_taxonomies', array( $this, 'filter_taxonomies' ) );
	}

	/**
	 * Register all taxonomies of this plugin.
	 *
	 * @return void
	 */
	public function register(): void {
		// loop through the taxonomies.
		foreach ( $this->get_taxonomies() as $taxonomy_name => $settings ) {
			// bail if post_types or args are not set.
			if ( ! isset( $settings['post_types'], $settings['args'] ) ) {
				continue;
			}

			// register this taxonomy.
			register_taxonomy( $taxonomy_name, $settings['post_types'], $settings['args'] );

			// register metas for this taxonomy, if set.
			if ( ! empty( $settings['metas'] ) ) {
				foreach ( $settings['metas'] as $meta_key => $meta_settings ) {
					// add term meta for default-marker.
					register_term_meta(
						$taxonomy_name,
						$meta_key,
						$meta_settings
					);
				}
			}
		}
	}

	/**
	 * Return list of all supported taxonomies.
	 *
	 * @return array<string,mixed>
	 */
	public function get_taxonomies(): array {
		$taxonomies = array(
			'dl_icon_set'   => array(
				'post_types' => array( 'dl_icons' ),
				'args'       => array(
					'hierarchical'       => false,
					'labels'             => array(
						'name'          => _x( 'Iconsets', 'taxonomy general name', 'download-list-block-with-icons' ),
						'singular_name' => _x( 'Iconset', 'taxonomy singular name', 'download-list-block-with-icons' ),
						'search_items'  => __( 'Search iconset', 'download-list-block-with-icons' ),
						'edit_item'     => __( 'Edit iconset', 'download-list-block-with-icons' ),
						'update_item'   => __( 'Update iconset', 'download-list-block-with-icons' ),
						'menu_name'     => __( 'Iconsets', 'download-list-block-with-icons' ),
						'add_new'       => __( 'Add new Iconset', 'download-list-block-with-icons' ),
						'add_new_item'  => __( 'Add new Iconset', 'download-list-block-with-icons' ),
						'back_to_items' => __( 'Go to iconsets', 'download-list-block-with-icons' ),
					),
					'public'             => false,
					'show_ui'            => true,
					'show_in_menu'       => true,
					'show_in_nav_menus'  => true,
					'show_admin_column'  => true,
					'show_tagcloud'      => true,
					'show_in_quick_edit' => true,
					'show_in_rest'       => true,
					'query_var'          => true,
					'rewrite'            => true,
					'capabilities'       => array(
						'manage_terms' => 'manage_options',
						'edit_terms'   => 'manage_options',
						'delete_terms' => 'manage_options',
						'assign_terms' => 'manage_options',
					),
				),
				'metas'      => array(
					'default' => array(
						'type'         => 'integer',
						'single'       => true,
						'show_in_rest' => true,
					),
				),
			),
			'dl_icon_lists' => array(
				'post_types' => array( 'attachment' ),
				'args'       => array(
					'hierarchical'       => false,
					'labels'             => array(
						'name'          => _x( 'Download Lists', 'taxonomy general name', 'download-list-block-with-icons' ),
						'singular_name' => _x( 'Download List', 'taxonomy singular name', 'download-list-block-with-icons' ),
						'search_items'  => __( 'Search Download Lists', 'download-list-block-with-icons' ),
						'edit_item'     => __( 'Edit Download List', 'download-list-block-with-icons' ),
						'update_item'   => __( 'Update Download List', 'download-list-block-with-icons' ),
						'menu_name'     => __( 'Download Lists', 'download-list-block-with-icons' ),
						'add_new'       => __( 'Add new Download List', 'download-list-block-with-icons' ),
						'add_new_item'  => __( 'Add new Download List', 'download-list-block-with-icons' ),
						'back_to_items' => __( 'Go to Download Lists', 'download-list-block-with-icons' ),
					),
					'public'             => false,
					'show_ui'            => true,
					'show_in_menu'       => true,
					'show_in_nav_menus'  => true,
					'show_admin_column'  => true,
					'show_tagcloud'      => true,
					'show_in_quick_edit' => true,
					'show_in_rest'       => true,
					'query_var'          => true,
					'rewrite'            => true,
					'capabilities'       => array(
						'manage_terms' => 'manage_options',
						'edit_terms'   => 'manage_options',
						'delete_terms' => 'manage_options',
						'assign_terms' => 'manage_options',
					),
				),
			),
		);

		/**
		 * Filter the taxonomies this plugin is supporting.
		 *
		 * @since 4.0.0 Available since 4.0.0.
		 * @param array<string,mixed> $taxonomies List of taxonomies.
		 */
		return apply_filters( 'downloadlist_taxonomies', $taxonomies );
	}

	/**
	 * Change setting for taxonomies.
	 *
	 * @param array<string,mixed> $taxonomies List of taxonomies.
	 * @return array<string,mixed>
	 */
	public function filter_taxonomies( array $taxonomies ): array {
		// bail if user is logged in.
		if ( is_user_logged_in() ) {
			return $taxonomies;
		}

		// change settings for iconset taxonomy.
		$taxonomies['dl_icon_set']['args']['rewrite']      = false;
		$taxonomies['dl_icon_set']['args']['show_in_rest'] = false;

		// change settings for list taxonomy.
		$taxonomies['dl_icon_lists']['args']['rewrite']      = false;
		$taxonomies['dl_icon_lists']['args']['show_in_rest'] = false;

		// return list of taxonomies.
		return $taxonomies;
	}

	/**
	 * Show description for iconset handling.
	 *
	 * @return void
	 */
	public function show_iconset_description(): void {
		echo '<p>' . esc_html__( 'Icon sets contain information about which icons should be used for which file types.', 'download-list-block-with-icons' ) . '</p>';
	}

	/**
	 * Show description for list handling.
	 *
	 * @return void
	 */
	public function show_list_description(): void {
		echo '<p>' . wp_kses_post( __( 'Download Lists contain files that are to be displayed in a single list of files. Assign files in your media library to these list to show them in the list. This possibility is added by the plugin <em>Download List Block with Icons</em>.', 'download-list-block-with-icons' ) ) . '</p>';
	}

	/**
	 * Add column for default-marker in iconset-table.
	 *
	 * @param array<string,string> $columns List of columns.
	 * @return array<string,string>
	 */
	public function set_iconset_columns( array $columns ): array {
		// add column for iconset.
		$columns['downloadlist_iconset_default'] = __( 'Default iconset', 'download-list-block-with-icons' );

		// remove count-row.
		unset( $columns['posts'], $columns['description'] );

		// return resulting array.
		return $columns;
	}

	/**
	 * Remove columns in lists-table.
	 *
	 * @param array<string,string> $columns List of columns.
	 * @return array<string,string>
	 */
	public function set_lists_columns( array $columns ): array {
		// remove some rows.
		unset( $columns['description'], $columns['slug'] );

		// return resulting array.
		return $columns;
	}

	/**
	 * Do not return generic for assignment to post-types.
	 *
	 * @param array<string,mixed> $results The resulting list.
	 * @param WP_Taxonomy         $taxonomy_object The taxonomy-object.
	 * @return array<string,mixed>
	 */
	public function filter_icon_taxonomy_ajax( array $results, WP_Taxonomy $taxonomy_object ): array {
		// bail if it is not our own taxonomy.
		if ( 'dl_icon_set' !== $taxonomy_object->name ) {
			return $results;
		}

		// check if the result is a generic iconset.
		foreach ( $results as $key => $result ) {
			// get the term.
			$term = get_term_by( 'name', $result, 'dl_icon_set' );

			// bail if term is not a term object.
			if ( ! $term instanceof WP_Term ) {
				continue;
			}

			// bail if term is not part of the iconsets.
			if ( ! in_array( get_term_meta( $term->term_id, 'type', true ), Iconsets::get_instance()->get_generic_sets_as_slug_array(), true ) ) {
				continue;
			}

			// remove it from results.
			unset( $results[ $key ] );
		}

		// return search results.
		return $results;
	}

	/**
	 * Add setting-fields for our own taxonomy for iconsets.
	 *
	 * @param WP_Term|string $term The term as object.
	 * @return void
	 */
	public function add_icon_set_fields( WP_Term|string $term ): void {
		if ( $term instanceof WP_Term ) {
			// get default setting.
			$default = get_term_meta( $term->term_id, 'default', true );

			// get width and height for icons of this set.
			$width  = get_term_meta( $term->term_id, 'width', true );
			$height = get_term_meta( $term->term_id, 'height', true );

			// get font URL of this set.
			$font        = get_term_meta( $term->term_id, 'font', true );
			$font_size   = get_term_meta( $term->term_id, 'font_size', true );
			$font_weight = get_term_meta( $term->term_id, 'font_weight', true );

			// get iconset as object.
			$iconset_obj = Iconsets::get_instance()->get_iconset_by_slug( $term->slug );
			if ( $iconset_obj instanceof Iconset_Base ) {
				// output.
				?>
				<tr class="form-field">
					<th scope="row"><label for="downloadlist-iconset-default"><?php echo esc_html__( 'Set this as default iconset', 'download-list-block-with-icons' ); ?></label></th>
					<td>
						<input type="checkbox" id="downloadlist-iconset-default" name="default" value="1"<?php echo 1 === absint( $default ) ? ' checked="checked"' : ''; ?>>
					</td>
				</tr>
				<?php
				if ( $iconset_obj->is_generic() ) {
					?>
					<tr class="form-field">
						<th scope="row"><label for="downloadlist-iconset-width"><?php echo esc_html__( 'Set font size for icons of this set', 'download-list-block-with-icons' ); ?></label></th>
						<td>
							<input type="number" id="downloadlist-iconset-width" name="width" value="<?php echo absint( $width ); ?>">
						</td>
					</tr>
					<?php
				} else {
					?>
					<tr class="form-field">
						<th scope="row"><label for="downloadlist-iconset-width"><?php echo esc_html__( 'Set width and height for icons of this set', 'download-list-block-with-icons' ); ?></label></th>
						<td>
							<input type="number" id="downloadlist-iconset-width" name="width" value="<?php echo absint( $width ); ?>"> x <input type="number" id="downloadlist-iconset-height" name="height" value="<?php echo absint( $height ); ?>">
						</td>
					</tr>
					<tr class="form-field">
						<th scope="row"><label for="downloadlist-iconset-font-file"><?php echo esc_html__( 'Set URL for font file (optional)', 'download-list-block-with-icons' ); ?></label></th>
						<td>
							<input type="url" id="downloadlist-iconset-font-file" name="font" value="<?php echo esc_url( $font ); ?>" placeholder="https://example.com/font.ttf">
						</td>
					</tr>
					<tr class="form-field">
						<th scope="row"><label for="downloadlist-iconset-font-weight"><?php echo esc_html__( 'Set font weight for font file icons (optional)', 'download-list-block-with-icons' ); ?></label></th>
						<td>
							<input type="number" id="downloadlist-iconset-font-weight" name="font_weight" value="<?php echo absint( $font_weight ); ?>">
						</td>
					</tr>
					<tr class="form-field">
						<th scope="row"><label for="downloadlist-iconset-font-size"><?php echo esc_html__( 'Set font size for font file icons (optional, in pixel)', 'download-list-block-with-icons' ); ?></label></th>
						<td>
							<input type="number" id="downloadlist-iconset-font-size" name="font_size" value="<?php echo absint( $font_size ); ?>">
						</td>
					</tr>
					<?php
				}
			} else {
				?>
				<tr class="form-field">
					<td colspan="2">
						<p><?php echo esc_html__( 'Iconset could not be loaded.', 'download-list-block-with-icons' ); ?></p>
					</td>
				</tr>
				<?php
			}
		} else {
			// output.
			?>
			<div class="form-field">
				<label for="downloadlist-iconset-default"><?php echo esc_html__( 'Set this as default iconset', 'download-list-block-with-icons' ); ?></label>
				<input type="checkbox" id="downloadlist-iconset-default" name="default" value="1">
			</div>
			<div class="form-field">
				<label for="downloadlist-iconset-width"><?php echo esc_html__( 'Set width and height for icons of this set', 'download-list-block-with-icons' ); ?></label>
				<input type="number" id="downloadlist-iconset-width" name="width" value="24"> x <input type="number" id="downloadlist-iconset-height" name="height" value="24">
			</div>
			<div class="form-field">
				<label for="downloadlist-iconset-font-file"><?php echo esc_html__( 'Set URL for font file (optional)', 'download-list-block-with-icons' ); ?></label>
				<input type="url" id="downloadlist-iconset-font-file" name="font" value="" placeholder="https://example.com/font.ttf">
			</div>
			<?php
		}
	}

	/**
	 * Save settings from custom taxonomy-fields.
	 *
	 * @param int    $term_id The ID of the term.
	 * @param int    $tt_id The taxonomy-ID of the term.
	 * @param string $taxonomy The name of the taxonomy.
	 * @return void
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function save_icon_set_fields( int $term_id, int $tt_id = 0, string $taxonomy = '' ): void {
		// bail if this is not our taxonomy.
		if ( 'dl_icon_set' !== $taxonomy ) {
			return;
		}

		// bail if nonce does not match.
		if ( ! empty( $_POST['_wpnonce'] ) && false === wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'update-tag_' . $term_id ) ) {
			return;
		}

		// marker if the term has been changed that should result in new style generation.
		$generate_styles = false;

		// delete marker for default icon-set.
		delete_term_meta( $term_id, 'default' );

		// mark the icon-set as default if checkbox is set.
		if ( ! empty( $_POST['default'] ) ) {
			Iconsets::get_instance()->set_default_iconset( $term_id );
		}

		// get sizes for icons if they have been changed.
		$width  = ! empty( $_POST['width'] ) ? absint( $_POST['width'] ) : 0;
		$height = ! empty( $_POST['height'] ) ? absint( $_POST['height'] ) : 0;

		// save the width.
		if ( isset( $_POST['width'] ) && absint( get_term_meta( $term_id, 'width', true ) ) !== $width ) {
			update_term_meta( $term_id, 'width', $width );
			$generate_styles = true;
		}

		// save the height.
		if ( isset( $_POST['height'] ) && absint( get_term_meta( $term_id, 'height', true ) ) !== $height ) {
			update_term_meta( $term_id, 'height', $height );
			$generate_styles = true;
		}

		// get the font URL from field, if set.
		$font = ! empty( $_POST['font'] ) ? sanitize_url( wp_unslash( $_POST['font'] ) ) : '';

		// save the font URL.
		if ( ! empty( $font ) ) {
			update_term_meta( $term_id, 'font', $font );
		}

		// get the font weight.
		$font_size = ! empty( $_POST['font_size'] ) ? absint( $_POST['font_size'] ) : 0;

		// save the font size.
		if ( ! empty( $font_size ) ) {
			update_term_meta( $term_id, 'font_size', $font_size );
		}

		// get the font weight.
		$font_weight = ! empty( $_POST['font_weight'] ) ? absint( $_POST['font_weight'] ) : 0;

		// save the font weight.
		if ( ! empty( $font_weight ) ) {
			update_term_meta( $term_id, 'font_weight', $font_weight );
		}

		// run style-generation for this iconset if changes have been saved.
		if ( $generate_styles ) {
			Helper::regenerate_icons( $term_id );
			Helper::generate_css( $term_id );
		}
	}

	/**
	 * Set content for new column in iconset-table.
	 *
	 * @param string $content The content for the column.
	 * @param string $column_name The name of the column.
	 * @param int    $term_id The ID of the term.
	 * @return string
	 */
	public function set_iconset_column( string $content, string $column_name, int $term_id ): string {
		// bail if this is not our column.
		if ( 'downloadlist_iconset_default' !== $column_name ) {
			return $content;
		}

		// define link to set iconset as default.
		$link = add_query_arg(
			array(
				'action'  => 'downloadlist_iconset_default',
				'nonce'   => wp_create_nonce( 'downloadlist-set_iconset-default' ),
				'term_id' => $term_id,
			),
			get_admin_url() . 'admin.php'
		);

		// define output.
		$content = '<a href="' . esc_url( $link ) . '" class="dashicons dashicons-no" title="' . esc_attr__( 'Set this as default iconset', 'download-list-block-with-icons' ) . '">&nbsp;</a>';
		if ( get_term_meta( $term_id, 'default', true ) ) {
			$content = '<span class="dashicons dashicons-yes" title="' . esc_attr__( 'This is the default iconset', 'download-list-block-with-icons' ) . '"></span>';
		}

		// return the resulting content.
		return $content;
	}

	/**
	 * Re-order table for icons with custom columns.
	 *
	 * @param array<string,string> $columns List of columns.
	 * @return array<string,string>
	 */
	public function set_icons_columns( array $columns ): array {
		$new_columns                           = array();
		$new_columns['cb']                     = $columns['cb'];
		$new_columns['title']                  = $columns['title'];
		$new_columns['downloadlist_file_type'] = __( 'File type', 'download-list-block-with-icons' );
		$new_columns['taxonomy-dl_icon_set']   = $columns['taxonomy-dl_icon_set'];
		$new_columns['date']                   = $columns['date'];

		// return resulting array.
		return $new_columns;
	}
}
