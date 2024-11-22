<?php
/**
 * File to handle admin-specific tasks.
 *
 * @package download-list-block-with-icons
 */

// prevent direct access.
defined( 'ABSPATH' ) || exit;

use downloadlist\helper;
use downloadlist\Iconset_Base;
use downloadlist\Iconsets;
use downloadlist\Transients;

/**
 * Register styles for block editor.
 */
add_action( 'enqueue_block_editor_assets', 'downloadlist_register_styles' );

/**
 * Enqueue styles in block editor.
 *
 * @return void
 */
add_action( 'enqueue_block_editor_assets', 'downloadlist_enqueue_styles_run', 10, 0 );

/**
 * Add our own styles and js in backend.
 *
 * @return void
 */
function downloadlist_add_styles_and_js_admin(): void {
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
				'support_url'  => Helper::get_support_url(),
			)
		),
		'before'
	);
}
add_action( 'admin_enqueue_scripts', 'downloadlist_add_styles_and_js_admin', PHP_INT_MAX );

/**
 * Check the taxonomy on each icon-cpt-item.
 *
 * @param int $post_id The post-ID.
 * @return void
 */
function downloadlist_check_taxonomy( int $post_id ): void {
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

	// get iconset.
	$iconset_terms = wp_get_object_terms( $post_id, 'dl_icon_set' );
	if ( ! empty( $iconset_terms ) ) {
		// regenerate icons and style of the chosen iconset.
		Helper::regenerate_icons( $iconset_terms[0]->term_id );
		Helper::generate_css( $iconset_terms[0]->term_id );
	}
}
add_action( 'save_post_dl_icons', 'downloadlist_check_taxonomy', 10, 2 );

/**
 * Show meta-box for cpts where the assigned term has the type "custom".
 *
 * @return void
 */
function downloadlist_admin_meta_boxes(): void {
	// add meta-box to add icons.
	add_meta_box(
		'downloadlist_custom_icons',
		__( 'Settings', 'download-list-block-with-icons' ),
		'downloadlist_admin_meta_boxes_settings',
		'dl_icons'
	);
}
add_action( 'add_meta_boxes_dl_icons', 'downloadlist_admin_meta_boxes', 10, 0 );

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
		<label for="icon"><?php echo esc_html__( 'Choose icon', 'download-list-block-with-icons' ); ?>:</label>
		<div>
			<?php
			$image = wp_get_attachment_image_src( $image_id );
			if ( $image_id > 0 && $image ) {
				?>
				<a href="#" class="downloadlist-image-choose"><img src="<?php echo esc_url( $image[0] ); ?>" alt="" /></a>
				<a href="#" class="downloadlist-image-remove button button-primary"><?php echo esc_html__( 'Remove image', 'download-list-block-with-icons' ); ?></a>
				<input type="hidden" name="icon" value="<?php echo esc_attr( $image_id ); ?>">
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
 * Do not return generic for assignment to post-types.
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

	// check if the result is a generic iconset.
	foreach ( $results as $key => $result ) {
		$term = get_term_by( 'name', $result, 'dl_icon_set' );
		if ( $term instanceof WP_Term && in_array( get_term_meta( $term->term_id, 'type', true ), Iconsets::get_instance()->get_generic_sets_as_slug_array(), true ) ) {
			// remove it from results.
			unset( $results[ $key ] );
		}
	}

	// return search results.
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

	// delete markier for default icon-set.
	delete_term_meta( $term_id, 'default' );

	// mark the icon-set as default if checkbox is set.
	if ( ! empty( $_POST['default'] ) ) {
		Helper::set_iconset_default( $term_id );
	}

	// get sizes for icons if they have been changed.
	$width  = ! empty( $_POST['width'] ) ? absint( $_POST['width'] ) : 0;
	$height = ! empty( $_POST['height'] ) ? absint( $_POST['height'] ) : 0;

	// save the width.
	if ( absint( get_term_meta( $term_id, 'width', true ) ) !== $width && isset( $_POST['width'] ) ) {
		update_term_meta( $term_id, 'width', absint( $_POST['width'] ) );
		$generate_styles = true;
	}

	// save the height.
	if ( absint( get_term_meta( $term_id, 'height', true ) ) !== $height && isset( $_POST['height'] ) ) {
		update_term_meta( $term_id, 'height', absint( $_POST['height'] ) );
		$generate_styles = true;
	}

	// run style-generation for this iconset if changes have been saved.
	if ( $generate_styles ) {
		Helper::regenerate_icons( $term_id );
		Helper::generate_css( $term_id );
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
	// add column for iconset.
	$columns['downloadlist_iconset_default'] = __( 'Default iconset', 'download-list-block-with-icons' );

	// remove count-row.
	unset( $columns['posts'] );
	unset( $columns['description'] );

	// return resulting array.
	return $columns;
}
add_filter( 'manage_edit-dl_icon_set_columns', 'downloadlist_admin_iconset_columns', 10, 1 );

/**
 * Re-order table for icons with custom columns.
 *
 * @param array $columns List of columns.
 * @return array
 */
function downloadlist_admin_icons_columns( array $columns ): array {
	$new_columns                           = array();
	$new_columns['cb']                     = $columns['cb'];
	$new_columns['title']                  = $columns['title'];
	$new_columns['downloadlist_file_type'] = __( 'File type', 'download-list-block-with-icons' );
	$new_columns['taxonomy-dl_icon_set']   = $columns['taxonomy-dl_icon_set'];
	$new_columns['date']                   = $columns['date'];

	// return resulting array.
	return $new_columns;
}
add_filter( 'manage_edit-dl_icons_columns', 'downloadlist_admin_icons_columns', 10, 1 );

/**
 * Set content for new column in iconset-table.
 *
 * @param string $content The content for the column.
 * @param string $column_name The name of the column.
 * @param int    $term_id The ID of the term.
 * @return string
 */
function downloadlist_admin_iconset_column( string $content, string $column_name, int $term_id ): string {
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
add_filter( 'manage_dl_icon_set_custom_column', 'downloadlist_admin_iconset_column', 10, 3 );

/**
 * Show file type for single icon in listing.
 *
 * @param string $column_name The column name.
 * @param int    $post_id The ID of the post.
 * @return void
 */
function downloadlist_admin_icons_column( string $column_name, int $post_id ): void {
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

	// show the name of the type.
	echo esc_html( array_search( $file_type, $file_types, true ) );
}
add_action( 'manage_dl_icons_posts_custom_column', 'downloadlist_admin_icons_column', 10, 2 );

/**
 * Set iconset as default via link-request.
 *
 * @return void
 * @noinspection PhpNoReturnAttributeCanBeAddedInspection
 */
function downloadlist_admin_iconset_set_default(): void {
	check_ajax_referer( 'downloadlist-set_iconset-default', 'nonce' );

	// get the term-ID from request.
	$term_id = ! empty( $_GET['term_id'] ) ? absint( $_GET['term_id'] ) : 0;

	if ( $term_id > 0 ) {
		// set this term-ID as default.
		Helper::set_iconset_default( $term_id );
	}

	// redirect user.
	wp_safe_redirect( wp_get_referer() );
	exit;
}
add_action( 'admin_action_downloadlist_iconset_default', 'downloadlist_admin_iconset_set_default' );

/**
 * Hide post-entry which are assigned to generated iconsets.
 *
 * @param WP_Query $query The Query.
 * @return void
 */
function downloadlist_hide_generated_iconsets( WP_Query $query ): void {
	// bail if condition is not met.
	if ( ! ( is_admin() && $query->is_main_query() && 'dl_icons' === $query->query['post_type'] ) ) {
		return;
	}

	// add filter for generic iconsets.
	$query->set(
		'meta_query',
		array(
			array(
				'key'     => 'generic-downloadlist',
				'compare' => 'NOT EXISTS',
			),
		)
	);

	// add filter for slugs which are marked as generic iconsets.
	$query->set(
		'tax_query',
		array(
			array(
				'taxonomy' => 'dl_icon_set',
				'terms'    => Iconsets::get_instance()->get_generic_sets_as_slug_array(),
				'field'    => 'slug',
				'operator' => 'NOT IN',
			),
		)
	);
}
add_action( 'pre_get_posts', 'downloadlist_hide_generated_iconsets' );

/**
 * Reduce the count of items in statistic for list view.
 *
 * @param stdClass $counts Object with counts.
 * @param string   $type The requested post type.
 * @return stdClass
 */
function downloadlist_reduce_count( stdClass $counts, string $type ): stdClass {
	if ( 'dl_icons' !== $type ) {
		return $counts;
	}

	// reduce the count with the amount of generic sets.
	$counts->publish -= count( Iconsets::get_instance()->get_generic_sets_cpts() );

	// return resulting object.
	return $counts;
}
add_filter( 'wp_count_posts', 'downloadlist_reduce_count', 10, 2 );

/**
 * Check on each load if plugin-version has been changed.
 * If yes, run appropriated functions for migrate to the new version.
 *
 * @return void
 */
function downloadlist_update(): void {
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
		// force refresh of css on every plugin update.
		$transient_obj = Transients::get_instance()->add();
		$transient_obj->set_action( array( 'downloadlist\Helper', 'generate_css' ) );
		$transient_obj->set_name( 'refresh_css' );
		$transient_obj->save();

		// run this on update from version before 3.4.0.
		if ( version_compare( $db_plugin_version, '3.4.0', '<' ) ) {
			downloadlist_cleanup();
			delete_option( 'downloadlistVersion' );
		}

		// save new plugin-version in DB.
		update_option( 'downloadlistVersion', $installed_plugin_version, true );
	}
}
add_action( 'plugins_loaded', 'downloadlist_update' );

/**
 * Cleanup DB for duplicate entries of generic styles.
 *
 * Get all entries for generic icons.
 * Add the new marker on them.
 *
 * @return void
 */
function downloadlist_cleanup(): void {
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
	foreach ( $posts->posts as $post_id ) {
		update_post_meta( $post_id, 'generic-downloadlist', 1 );
	}
}

/**
 * Show known transients only for users with rights.
 *
 * @return void
 */
function downloadlist_admin_notices(): void {
	// bail if capabilities does not match.
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// check for transients of our plugin to show them as admin notices.
	$transients_obj = Transients::get_instance();
	$transients_obj->check_transients();
}
add_action( 'admin_notices', 'downloadlist_admin_notices' );

/**
 * Add new field for attachments.
 *
 * @param array   $form_fields The list of fields.
 * @param WP_Post $post The attachment-object.
 * @return array
 */
function downloadlist_add_custom_text_field_to_attachment_fields_to_edit( array $form_fields, WP_Post $post ): array {
	// get actual custom title.
	$dl_title = get_post_meta( $post->ID, 'dl_title', true );

	// add field for title.
	$form_fields['dl_title'] = array(
		'label' => __( 'title for downloadlist (optional)', 'download-list-block-with-icons' ),
		'input' => 'text',
		'value' => $dl_title,
	);

	// get actual custom description.
	$dl_description = get_post_meta( $post->ID, 'dl_description', true );

	// add field for title.
	$form_fields['dl_description'] = array(
		'label' => __( 'description for downloadlist (optional)', 'download-list-block-with-icons' ),
		'input' => 'textarea',
		'value' => $dl_description,
	);

	// return the field list.
	return $form_fields;
}
add_filter( 'attachment_fields_to_edit', 'downloadlist_add_custom_text_field_to_attachment_fields_to_edit', null, 2 );

/**
 * Save values from our custom fields for attachments.
 *
 * @param array $post The attachment-array.
 * @param array $fields The form fields.
 * @return array
 */
function downloadlist_save_custom_text_attachment_field( array $post, array $fields ): array {
	// save the value for the title.
	if ( isset( $fields['dl_title'] ) ) {
		update_post_meta( $post['ID'], 'dl_title', sanitize_text_field( $fields['dl_title'] ) );
	} else {
		delete_post_meta( $post['ID'], 'dl_title' );
	}

	// save the value for the description.
	if ( isset( $fields['dl_description'] ) ) {
		update_post_meta( $post['ID'], 'dl_description', sanitize_textarea_field( $fields['dl_description'] ) );
	} else {
		delete_post_meta( $post['ID'], 'dl_description' );
	}

	// return post-object.
	return $post;
}
add_filter( 'attachment_fields_to_save', 'downloadlist_save_custom_text_attachment_field', null, 2 );

/**
 * Regenerate styles if icon is sent to trash.
 *
 * @param int $post_id The ID of the requested post.
 * @return void
 */
function downloadlist_trash_post( int $post_id ): void {
	// bail if type is not ours.
	if ( 'dl_icons' !== get_post_type( $post_id ) ) {
		return;
	}

	// regenerate all icons.
	Helper::regenerate_icons();

	// regenerate the css.
	Helper::generate_css();
}
add_action( 'trashed_post', 'downloadlist_trash_post' );

/**
 * Exclude our own cpt from Easy Language.
 *
 * @param array $post_types List of post types.
 * @return array
 */
function downloadlist_remove_easy_language_support( array $post_types ): array {
	if ( ! empty( $post_types['dl_icons'] ) ) {
		unset( $post_types['dl_icons'] );
	}
	return $post_types;
}
add_filter( 'easy_language_possible_post_types', 'downloadlist_remove_easy_language_support' );

/**
 * Add link to icon management in plugin list.
 *
 * @param array $links List of links.
 * @return array
 */
function downloadlist_plugin_list_add_setting_link( array $links ): array {
	$url = add_query_arg(
		array(
			'post_type' => 'dl_icons',
		),
		admin_url() . 'edit.php'
	);

	// adds the link to the end of the array.
	$links[] = '<a href="' . esc_url( $url ) . '">' . __( 'Manage icons', 'download-list-block-with-icons' ) . '</a>';

	// return resulting list.
	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( DL_PLUGIN ), 'downloadlist_plugin_list_add_setting_link' );

/**
 * Check if website is using a valid SSL and show warning if not.
 *
 * @return void
 */
function downloadlist_check_php(): void {
	// get transients object.
	$transients_obj = Transients::get_instance();

	// bail if WordPress is in developer mode.
	if ( function_exists( 'wp_is_development_mode' ) && wp_is_development_mode( 'plugin' ) ) {
		$transients_obj->delete_transient( $transients_obj->get_transient_by_name( 'downloadlist_php_hint' ) );
		return;
	}

	// bail if PHP >= 8.1 is used.
	if ( version_compare( PHP_VERSION, '8.1', '>' ) ) {
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
add_action( 'admin_init', 'downloadlist_check_php' );
