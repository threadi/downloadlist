<?php
/**
 * File to handle admin-specific tasks.
 *
 * @package download-list-block-with-icons
 */

use downloadlist\helper;
use downloadlist\Iconset_Base;
use downloadlist\Iconsets;

add_action( 'enqueue_block_editor_assets', 'downloadlist_enqueue_styles' );

/**
 * Add our own styles and js in backend.
 *
 * @return void
 */
function downloadlist_add_styles_and_js_admin(): void {
	// admin-specific styles.
	wp_enqueue_style(
		'downloadlist-admin',
		plugin_dir_url( DL_PLUGIN ) . '/admin/styles.css',
		array(),
		filemtime( plugin_dir_path( DL_PLUGIN ) . '/admin/styles.css' ),
	);

	// backend-JS.
	wp_enqueue_script(
		'downloadlist-admin',
		plugins_url( '/admin/js.js', DL_PLUGIN ),
		array( 'jquery' ),
		filemtime( plugin_dir_path( DL_PLUGIN ) . '/admin/js.js' ),
		true
	);

	// embed media if not already done.
	if ( ! did_action( 'wp_enqueue_media' ) ) {
		wp_enqueue_media();
	}

	// add ja-variables for block editor.
	wp_add_inline_script(
		'downloadlist-list-editor-script',
		'window.downloadlist_config = ' . wp_json_encode(
			array(
				'iconsets_url' => trailingslashit( get_admin_url() ) . 'edit-tags.php?taxonomy=dl_icon_set&post_type=dl_icons',
			)
		),
		'before'
	);
}
add_action( 'admin_enqueue_scripts', 'downloadlist_add_styles_and_js_admin', PHP_INT_MAX );

/**
 * Move the icon category-meta-box.
 *
 * @return void
 */
function downloadlist_move_meta_box(): void {
	global $current_screen, $wp_meta_boxes;

	// only do stuff if this is the editor screen for the post type 'project' and it has meta boxes.
	if ( 'dl_icons' === $current_screen->id && isset( $wp_meta_boxes['dl_icons'] ) ) {
		// loop though 'side' meta boxes.
		if ( ! empty( $wp_meta_boxes['dl_icons']['side']['core'] ) ) {
			foreach ( $wp_meta_boxes['dl_icons']['side']['core'] as $key => $high_box ) {
				// move our own taxonomy meta boxes.
				if ( 'tagsdiv-dl_icon_set' === $high_box['id'] ) {
					// grab the meta box.
					$meta_box = array( $key => $high_box );

					// remove it from the array of 'high' meta boxes.
					unset( $wp_meta_boxes['dl_icons']['side']['core'][ $key ] );

					// add it to the start of the array.
					if ( empty( $wp_meta_boxes['dl_icons']['normal']['high'] ) ) {
						$wp_meta_boxes['dl_icons']['normal']['high'] = array();
					}
					$wp_meta_boxes['dl_icons']['normal']['high'] = $meta_box + $wp_meta_boxes['dl_icons']['normal']['high'];
				}
			}
		}
	}
}
add_action( 'edit_form_after_title', 'downloadlist_move_meta_box' );

/**
 * Check the set taxonomy on each icon-cpt-item.
 *
 * @param int $post_id The post-ID.
 * @return void
 */
function downloadlist_check_taxonomy( int $post_id ): void {
	// do nothing if post is in trash.
	if ( in_array( get_post_status( $post_id ), array( 'trash', 'draft', 'auto-draft' ), true ) ) {
		return;
	}

	// save assigned icon-file.
	if ( ! empty( $_POST['icon'] ) ) {
		update_post_meta( $post_id, 'icon', absint( $_POST['icon'] ) );
	}

	// save assigned file-type.
	if ( ! empty( $_POST['file_type'] ) ) {
		update_post_meta( $post_id, 'file_type', sanitize_text_field( wp_unslash( $_POST['file_type'] ) ) );
	} else {
		delete_post_meta( $post_id, 'file_type' );
	}

	// get iconset.
	$iconset_terms = wp_get_object_terms( $post_id, 'dl_icon_set' );
	if ( ! empty( $iconset_terms ) ) {
		// regenerate icons and style of the chosen iconset.
		helper::regenerate_icons( $iconset_terms[0]->term_id );
		helper::generate_css( $iconset_terms[0]->term_id );
	}
}
add_filter( 'save_post_dl_icons', 'downloadlist_check_taxonomy', 10, 2 );

/**
 * Show meta-box for cpts where the assigned term has the type "custom".
 *
 * @param WP_Post $post The post.
 * @return void
 */
function downloadlist_admin_meta_boxes( WP_Post $post ): void {
	// bail if post-status is draft.
	if ( 'draft' === $post->post_status ) {
		return;
	}

	// add meta-box to add icons.
	add_meta_box(
		'downloadlist_custom_icons',
		__( 'Settings', 'downloadlist' ),
		'downloadlist_admin_meta_boxes_settings',
		'dl_icons'
	);
}
add_action( 'add_meta_boxes_dl_icons', 'downloadlist_admin_meta_boxes', 10, 1 );

/**
 * Form to choose a single icon image from media library and set the file-type for this entry.
 *
 * @param WP_Post $post The post.
 * @return void
 */
function downloadlist_admin_meta_boxes_settings( WP_Post $post ): void {
	// get image_id of the icon.
	$image_id = absint( get_post_meta( $post->ID, 'icon', true ) );

	// get file-type.
	$file_type = get_post_meta( $post->ID, 'file_type', true );

	// output.
	?>
	<div class="form-field">
		<label for="icon"><?php echo esc_html__( 'Choose icon', 'downloadlist' ); ?>:</label>
		<div>
			<?php
			$image = wp_get_attachment_image_src( $image_id );
			if ( $image_id > 0 && $image ) {
				?>
				<a href="#" class="downloadlist-image-choose"><img src="<?php echo esc_url( $image[0] ); ?>" alt="" /></a>
				<a href="#" class="downloadlist-image-remove"><?php echo esc_html__( 'Remove image', 'downloadlist' ); ?></a>
				<input type="hidden" name="icon" value="<?php echo esc_attr( $image_id ); ?>">
				<?php
			} else {
				?>
				<a href="#" class="downloadlist-image-choose"><?php echo esc_html__( 'Upload or choose image', 'downloadlist' ); ?></a>
				<a href="#" class="downloadlist-image-remove" style="display:none"><?php echo esc_html__( 'Remove image', 'downloadlist' ); ?></a>
				<input type="hidden" name="icon" value="">
				<?php
			}
			?>
		</div>
	</div>
	<div class="form-field">
		<label for="file_type"><?php echo esc_html__( 'Choose file type', 'downloadlist' ); ?>:</label>
		<select name="file_type" id="file_type">
			<option value="">&nbsp;</option>
			<?php
			foreach ( helper::get_mime_types() as $label => $mime_type ) {
				?>
					<option value="<?php echo esc_attr( $mime_type ); ?>"<?php echo $mime_type === $file_type ? ' selected="selected"' : ''; ?>><?php echo esc_html( $label ); ?></option>
					<?php
			}
			?>
		</select>
	</div>
	<?php
}

/**
 * Do not return generic or graphic iconsets for assignment to post-types.
 *
 * @param array       $results The resulting list.
 * @param WP_Taxonomy $taxonomy_object The taxonomy-object.
 * @return array
 */
function downloadlist_filter_icon_taxonomy_ajax( array $results, WP_Taxonomy $taxonomy_object ): array {
	// bail if it is not our own taxonomy.
	if ( 'dl_icon_set' !== $taxonomy_object->name ) {
		return $results;
	}

	foreach ( $results as $key => $result ) {
		$term = get_term_by( 'name', $result, 'dl_icon_set' );
		if ( $term instanceof WP_Term && in_array( get_term_meta( $term->term_id, 'type', true ), Iconsets::get_instance()->get_generic_sets_as_slug_array(), true ) ) {
			unset( $results[ $key ] );
		}
		if ( $term instanceof WP_Term && in_array( get_term_meta( $term->term_id, 'type', true ), Iconsets::get_instance()->get_gfx_sets_as_slug_array(), true ) ) {
			unset( $results[ $key ] );
		}
	}
	return $results;
}
add_filter( 'ajax_term_search_results', 'downloadlist_filter_icon_taxonomy_ajax', 10, 2 );

/**
 * Add setting-fields for our own taxonomy for iconsets.
 *
 * @param WP_Term|string $term The term as object.
 * @return void
 */
function downloadlist_admin_icon_set_fields( WP_Term|string $term ): void {
	if ( $term instanceof WP_Term ) {
		// get default setting.
		$default = get_term_meta( $term->term_id, 'default', true );

		// get width and height for icons of this set.
		$width  = get_term_meta( $term->term_id, 'width', true );
		$height = get_term_meta( $term->term_id, 'height', true );

		// get iconset as object.
		$iconset_obj = Iconsets::get_instance()->get_iconset_by_slug( $term->slug );
		if( $iconset_obj instanceof Iconset_Base ) {
			// output.
			?>
			<tr class="form-field">
				<th scope="row"><label for="downloadlist-iconset-default"><?php echo esc_html__( 'Set this as default iconset', 'downloadlist' ); ?></label></th>
				<td>
					<input type="checkbox" id="downloadlist-iconset-default" name="default" value="1"<?php echo 1 === absint( $default ) ? ' checked="checked"' : ''; ?>>
				</td>
			</tr>
			<?php
				if( $iconset_obj->is_generic() ) {
					?>
						<tr class="form-field">
							<th scope="row"><label for="downloadlist-iconset-width"><?php echo esc_html__( 'Set font size for icons of this set', 'downloadlist' ); ?></label></th>
							<td>
								<input type="number" id="downloadlist-iconset-width" name="width" value="<?php echo absint( $width ); ?>">
							</td>
						</tr>
					<?php
				}
				else {
					?>
					<tr class="form-field">
						<th scope="row"><label for="downloadlist-iconset-width"><?php echo esc_html__( 'Set width and height for icons of this set', 'downloadlist' ); ?></label></th>
						<td>
							<input type="number" id="downloadlist-iconset-width" name="width" value="<?php echo absint( $width ); ?>"> x <input type="number" id="downloadlist-iconset-height" name="height" value="<?php echo absint( $height ); ?>">
						</td>
					</tr>
					<?php
				}
		}
		else {
			?>
			<tr class="form-field">
				<td colspan="2">
					<p><?php echo esc_html__( 'Iconset could not be loaded.', 'downloadlist' ); ?></p>
				</td>
			</tr>
			<?php
		}
	} else {
		// output.
		?>
		<div class="form-field">
			<label for="downloadlist-iconset-default"><?php echo esc_html__( 'Set this as default iconset', 'downloadlist' ); ?></label>
			<input type="checkbox" id="downloadlist-iconset-default" name="default" value="1">
		</div>
		<div class="form-field">
			<label for="downloadlist-iconset-width"><?php echo esc_html__( 'Set width and height for icons of this set', 'downloadlist' ); ?></label>
			<input type="number" id="downloadlist-iconset-width" name="width" value="24"> x <input type="number" id="downloadlist-iconset-height" name="height" value="24">
		</div>
		<?php
	}
}
add_action( 'dl_icon_set_add_form_fields', 'downloadlist_admin_icon_set_fields' );
add_action( 'dl_icon_set_edit_form_fields', 'downloadlist_admin_icon_set_fields', 10 );

/**
 * Save settings from custom taxonomy-fields.
 *
 * @param int    $term_id The ID of the term.
 * @param int    $tt_id The taxonomy-ID of the term.
 * @param string $taxonomy The name of the taxonomy.
 * @return void
 * @noinspection PhpUnusedParameterInspection
 */
function downloadlist_admin_icon_set_fields_save( int $term_id, int $tt_id = 0, string $taxonomy = '' ): void {
	if ( 'dl_icon_set' === $taxonomy ) {
		// marker if the term has been changed that should result in new style generation.
		$generate_styles = false;

		if ( ! empty( $_POST['default'] ) ) {
			helper::set_iconset_default( $term_id );
		} else {
			// delete markier for default icon-set if checkbox is not set.
			delete_term_meta( $term_id, 'default' );
		}

		// save size for icons if they have been changed.
		$width  = ! empty( $_POST['width'] ) ? absint( $_POST['width'] ) : 0;
		$height = ! empty( $_POST['height'] ) ? absint( $_POST['height'] ) : 0;
		if ( absint( get_term_meta( $term_id, 'width', true ) ) !== $width && isset($_POST['width']) ) {
			update_term_meta( $term_id, 'width', absint( $_POST['width'] ) );
			$generate_styles = true;
		}
		if ( absint( get_term_meta( $term_id, 'height', true ) ) !== $height && isset($_POST['height']) ) {
			update_term_meta( $term_id, 'height', absint( $_POST['height'] ) );
			$generate_styles = true;
		}

		// run style-generation if changes have been saved.
		if ( $generate_styles ) {
			Helper::regenerate_icons( $term_id );
			Helper::generate_css( $term_id );
		}
	}
}
add_action( 'created_term', 'downloadlist_admin_icon_set_fields_save', 10, 3 );
add_action( 'edit_term', 'downloadlist_admin_icon_set_fields_save', 10, 3 );

/**
 * Add column for default-marker in iconset-table.
 *
 * @param array $columns List of columns.
 * @return array
 */
function downloadlist_admin_iconset_columns( array $columns ): array {
	// add our column.
	$columns['downloadlist_iconset_default'] = __( 'Default iconset', 'downloadlist' );

	// remove count-row.
	unset( $columns['posts'] );
	unset( $columns['description'] );

	// return resulting array.
	return $columns;
}
add_filter( 'manage_edit-dl_icon_set_columns', 'downloadlist_admin_iconset_columns', 10, 1 );

/**
 * Set content for new column in iconset-table.
 *
 * @param string $content The content for the column.
 * @param string $column_name The name of the column.
 * @param int    $term_id The ID of the term.
 * @return string
 */
function downloadlist_admin_iconset_column( string $content, string $column_name, int $term_id ): string {
	if ( 'downloadlist_iconset_default' === $column_name ) {
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
		$content = '<a href="' . esc_url( $link ) . '" class="dashicons dashicons-no" title="' . esc_attr__( 'Set this as default iconset', 'downloadlist' ) . '">&nbsp;</a>';
		if ( get_term_meta( $term_id, 'default', true ) ) {
			$content = '<span class="dashicons dashicons-yes" title="' . esc_attr__( 'This is the default iconset', 'downloadlist' ) . '"></span>';
		}
	}
	return $content;
}
add_filter( 'manage_dl_icon_set_custom_column', 'downloadlist_admin_iconset_column', 10, 3 );

/**
 * Set iconset as default via link-request.
 *
 * @return void
 */
function downloadlist_admin_iconset_set_default(): void {
	check_ajax_referer( 'downloadlist-set_iconset-default', 'nonce' );

	// get the term-ID from request.
	$term_id = ! empty( $_GET['term_id'] ) ? absint( $_GET['term_id'] ) : 0;

	if ( $term_id > 0 ) {
		// set this term-ID as default.
		helper::set_iconset_default( $term_id );
	}

	// redirect user.
	wp_safe_redirect( ! empty( $_SERVER['HTTP_REFERER'] ) ? wp_unslash( $_SERVER['HTTP_REFERER'] ) : '' );
}
add_action( 'admin_action_downloadlist_iconset_default', 'downloadlist_admin_iconset_set_default' );

/**
 * Hide post-entry which are assigned to generated or graphic iconsets.
 *
 * @param WP_Query $query The Query.
 * @return void
 */
function downloadlist_hide_generated_iconsets( WP_Query $query ): void {
	if ( is_admin() && $query->is_main_query() && 'dl_icons' === $query->query['post_type'] ) {
		$query->set(
			'tax_query',
			array(
				array(
					'taxonomy' => 'dl_icon_set',
					'terms'    => array_merge( Iconsets::get_instance()->get_generic_sets_as_slug_array(), Iconsets::get_instance()->get_gfx_sets_as_slug_array() ),
					'field'    => 'slug',
					'operator' => 'NOT IN',
				),
			)
		);
	}
}
add_action( 'pre_get_posts', 'downloadlist_hide_generated_iconsets' );
