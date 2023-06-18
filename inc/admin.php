<?php
/**
 * File to handle admin-specific tasks.
 *
 * @package downloadlist
 */

use downloadlist\helper;
use downloadlist\Iconsets;

/**
 * Add our own styles and js in backend.
 *
 * @return void
 */
function downloadlist_add_styles_and_js_admin(): void {
	downloadlist_generate_css();

	// admin-specific styles
	wp_enqueue_style('downloadlist-admin',
		plugin_dir_url(DL_PLUGIN) . '/admin/styles.css',
		array(),
		filemtime(plugin_dir_path(DL_PLUGIN) . '/admin/styles.css'),
	);

	// backend-JS
	wp_enqueue_script( 'downloadlist-admin',
		plugins_url( '/admin/js.js' , DL_PLUGIN ),
		array( 'jquery' ),
		filemtime(plugin_dir_path(DL_PLUGIN) . '/admin/js.js'),
	);

	// embed media if not already done.
	if( ! did_action( 'wp_enqueue_media' ) ) {
		wp_enqueue_media();
	}
}
add_action( 'admin_enqueue_scripts', 'downloadlist_add_styles_and_js_admin', PHP_INT_MAX );

/**
 * Move the icon category-meta-box.
 *
 * @return void
 */
function downloadlist_move_meta_box(): void {
	global $current_screen, $wp_meta_boxes;

	// only do stuff if this is the editor screen for the post type 'project' and it has meta boxes
	if( 'dl_icons' === $current_screen->id && isset($wp_meta_boxes['dl_icons']) ) {
		// loop though 'side' meta boxes
		if( !empty($wp_meta_boxes['dl_icons']['side']['core']) ) {
			foreach ($wp_meta_boxes['dl_icons']['side']['core'] as $key => $high_box) {
				// move our own taxonomy meta boxes.
				if( 'tagsdiv-dl_icon_set' === $high_box['id'] ) {
					// grab the meta box
					$meta_box = [$key => $high_box];

					// remove it from the array of 'high' meta boxes
					unset($wp_meta_boxes['dl_icons']['side']['core'][$key]);

					// add it to the start of the array
					if( empty($wp_meta_boxes['dl_icons']['normal']['high']) ) {
						$wp_meta_boxes['dl_icons']['normal']['high'] = array();
					}
					$wp_meta_boxes['dl_icons']['normal']['high'] = $meta_box + $wp_meta_boxes['dl_icons']['normal']['high'];
				}
			}
		}
	}
}
add_action('edit_form_after_title', 'downloadlist_move_meta_box' );

/**
 * One-time function to install prepared iconsets.
 *
 * @return void
 */
function downloadlist_add_taxonomy_defaults(): void {
	// Exit if the work has already been done.
	if ( get_option( 'downloadlistTaxonomyDefaults', 0 ) == 1 || defined('DOING_AJAX') ) {
		return;
	}

	// add default terms to taxonomy if they do not exist (only in admin).
	foreach( Iconsets::get_instance()->get_icon_sets() as $iconset_obj ) {
		// bail if one necessary setting is missing.
		if( false === $iconset_obj->has_label() || false === $iconset_obj->has_type() ) {
			continue;
		}

		// check if this term already exists.
		if (!term_exists($iconset_obj->get_label(), 'dl_icon_set')) {
			// no, it does not exist. then add it now.
			$term = wp_insert_term(
				$iconset_obj->get_label(),
				'dl_icon_set',
				array(
					'slug' => $iconset_obj->get_slug()
				)
			);

			if( !is_wp_error($term) ) {
				// save the type for this term.
				update_term_meta($term['term_id'], 'type', $iconset_obj->get_type());

				// set this iconset as default, if set.
				if( $iconset_obj->should_be_default() ) {
					update_term_meta($term['term_id'], 'default', 1);
				}

				// generate icon entry, if enabled.
				if( $iconset_obj->add_icon_entry() ) {
					$array = array(
						'post_type' => 'dl_icons',
						'post_status' => 'publish',
						'post_title' => $iconset_obj->get_label()
					);
					$post_id = wp_insert_post($array);
					if ($post_id > 0) {
						// assign post to this taxonomy.
						wp_set_object_terms( $post_id, $term['term_id'], 'dl_icon_set' );
					}
				}
			}
		}
	}

	// add or update the option to prevent it to run again.
	update_option( 'downloadlistTaxonomyDefaults', 1 );
}
add_action( 'init', 'downloadlist_add_taxonomy_defaults', 20 );

/**
 * Check the set taxonomy on each icon-cpt-item.
 *
 * @param int $post_id The post-ID.
 * @return void
 */
function downloadlist_check_taxonomy( int $post_id ): void {
	// do nothing if post is in trash.
	if( in_array( get_post_status( $post_id ), array('trash', 'draft', 'auto-draft') ) ) {
		return;
	}

	// save assigned icon-file.
	if( !empty($_POST['icon']) ) {
		update_post_meta( $post_id, 'icon', absint($_POST['icon']) );
	}

	// save assigned file-type.
	if( !empty($_POST['file_type']) ) {
		update_post_meta( $post_id, 'file_type', sanitize_text_field($_POST['file_type']) );
	}
	else {
		delete_post_meta( $post_id, 'file_type' );
	}
}
add_filter( 'save_post_dl_icons', 'downloadlist_check_taxonomy', 10, 2 );

/**
 * Show meta-box for cpts where the assigned term has the type "custom".
 *
 * @param $post
 * @return void
 */
function downloadlist_admin_meta_boxes( $post ): void {
	// bail if post-status is draft.
	if( 'draft' === $post->post_status ) {
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
 * @param WP_Post $post
 * @return void
 */
function downloadlist_admin_meta_boxes_settings( WP_Post $post ): void {
	// get image_id of the icon.
	$image_id = absint(get_post_meta( $post->ID, 'icon', true ));

	// get file-type.
	$file_type = get_post_meta( $post->ID, 'file_type', true );

	// output.
	?>
	<div class="form-field">
		<label for="icon"><?php _e('Choose icon', 'downloadlist'); ?>:</label>
		<div>
			<?php
			if( $image_id > 0 && $image = wp_get_attachment_image_src( $image_id ) ) {
				?>
				<a href="#" class="downloadlist-image-choose"><img src="<?php echo esc_url($image[0]); ?>" alt="" /></a>
				<a href="#" class="downloadlist-image-remove"><?php _e('Remove image', 'downloadlist'); ?></a>
				<input type="hidden" name="icon" value="<?php echo esc_attr($image_id); ?>">
				<?php
			} else {
				?>
				<a href="#" class="downloadlist-image-choose"><?php _e('Upload or choose image', 'downloadlist'); ?></a>
				<a href="#" class="downloadlist-image-remove" style="display:none"><?php _e('Remove image', 'downloadlist'); ?></a>
				<input type="hidden" name="icon" value="">
				<?php
			}
			?>
		</div>
	</div>
	<div class="form-field">
		<label for="file_type"><?php _e('Choose file type', 'downloadlist'); ?>:</label>
		<select name="file_type" id="file_type">
			<option value="">&nbsp;</option>
			<?php
				foreach( helper::get_mime_types() as $label => $mime_type ) {
					?><option value="<?php echo esc_attr($mime_type); ?>"<?php echo $mime_type === $file_type ? ' selected="selected"' : ''; ?>><?php echo esc_html($label); ?></option><?php
				}
			?>
		</select>
	</div>
	<?php
}

/**
 * Generate the style-file for the icons on request (e.g. if a new cpt is saved).
 *
 * TODO nur ausführen wenn sich an Iconsets etwas ändert.
 *
 * @return void
 */
function downloadlist_generate_css(): void {
	// define variable for resulting content.
	$styles = '';

	// get all icons of non-generic iconsets which are configured with icon-set and file-type.
	$query_non_generic_icons = array(
		'post_type' => 'dl_icons',
		'post_status' => 'publish',
		'posts_per_page' => -1,
		'meta_query' => array(
			array(
				'key' => 'file_type',
				'compare' => 'EXISTS'
			)
		),
		'tax_query' => array(
			array(
				'taxonomy' => 'dl_icon_set',
				'operator' => 'EXISTS'
			)
		),
		'fields' => 'ids'
	);
	$non_generic_icons = new WP_Query( $query_non_generic_icons );

	// get all generic iconsets.
	$query_generic_icons = array(
		'post_type' => 'dl_icons',
		'post_status' => 'publish',
		'posts_per_page' => -1,
		'tax_query' => array(
			array(
				'taxonomy' => 'dl_icon_set',
				'terms' => Iconsets::get_instance()->get_generic_sets_as_slug_array(),
				'field' => 'slug',
				'operator' => 'IN'
			)
		),
		'fields' => 'ids'
	);
	$generic_icons = new WP_Query( $query_generic_icons );

	// mix all results.
	$icons = array_merge( $non_generic_icons->posts, $generic_icons->posts );

	// loop through the resulting list of icons.
	foreach( $icons as $post_id ) {
		// get the assigned icon-set.
		$terms = wp_get_object_terms($post_id, 'dl_icon_set');

		// get iconset-object for this post.
		$iconset_obj = Iconsets::get_instance()->get_icon_set_by_slug($terms[0]->slug);

		// get file-type with main- and subtype.
		$file_type_name = get_post_meta($post_id, 'file_type', true);

		// get type and subtype.
		$mimetypeArray = explode("/", $file_type_name);
		$type = $mimetypeArray[0];
		$subtype = '';
		if (!empty($mimetypeArray[1])) {
			$subtype = $mimetypeArray[1];
		}

		// get iconset-specific styles.
		$styles .= $iconset_obj[0]->get_style_for_filetype($post_id, $terms[0]->term_id, $type);
		if (!empty($subtype)) {
			$styles .= $iconset_obj[0]->get_style_for_filetype($post_id, $terms[0]->term_id, $subtype);
		}
	}

	// write resulting code in upload-directory.
	file_put_contents( helper::get_style_path(), $styles );
}

add_action( 'enqueue_block_editor_assets', 'downloadlist_enqueue_styles' );

/**
 * Do not return generic iconsets for assignment to post-types.
 *
 * @param $results
 * @param $taxonomy_object
 * @return array
 */
function downloadlist_filter_icon_taxonomy_ajax( $results, $taxonomy_object ): array {
	// bail if it is not our own taxonomy.
	if( 'dl_icon_set' !== $taxonomy_object->name ) {
		return $results;
	}

	foreach( $results as $key => $result ) {
		$term = get_term_by( 'name', $result, 'dl_icon_set' );
		if( $term instanceof WP_Term && in_array(get_term_meta( $term->term_id, 'type', true ), Iconsets::get_instance()->get_generic_sets_as_slug_array() ) ) {
			unset($results[$key]);
		}
	}
	return $results;
}
add_filter( 'ajax_term_search_results', 'downloadlist_filter_icon_taxonomy_ajax', 10, 2 );

/**
 * Add setting-fields for our own taxonomy for iconsets.
 *
 * @param $term
 * @return void
 */
function downloadlist_admin_icon_set_fields( $term ): void {
	if( $term instanceof WP_Term ) {
		// get actual value.
		$value = get_term_meta( $term->term_id, 'default', true );

		// output.
		?>
		<tr class="form-field">
			<th scope="row"><label for="downloadlist-iconset-default"><?php _e('Set default', 'downloadlist'); ?></label></th>
			<td>
				<input type="checkbox" id="downloadlist-iconset-default" name="default" value="1"<?php echo 1 === absint($value) ? ' checked="checked"' : ''; ?>>
			</td>
		</tr>
		<?php
	}
	else {
		// output.
		?>
		<div class="form-field">
			<label for="downloadlist-iconset-default"><?php _e('Set default', 'downloadlist'); ?></label>
			<input type="checkbox" id="downloadlist-iconset-default" name="default" value="1">
		</div>
		<?php
	}
}
add_action( 'dl_icon_set_add_form_fields', 'downloadlist_admin_icon_set_fields');
add_action( 'dl_icon_set_edit_form_fields', 'downloadlist_admin_icon_set_fields', 10);

/**
 * Save settings from custom taxonomy-fields.
 *
 * @param int $term_id
 * @param int $tt_id
 * @param string $taxonomy
 * @return void
 * @noinspection PhpUnusedParameterInspection
 */
function downloadlist_admin_icon_set_fields_save( int $term_id, int $tt_id = 0, string $taxonomy = '' ): void {
	if( 'dl_icon_set' === $taxonomy ) {
		if( !empty($_POST['default']) ) {
			helper::set_iconset_default($term_id);
		}
		else {
			// delete markier for default icon-set if checkbox is not set.
			delete_term_meta( $term_id, 'default' );
		}
	}
}
add_action( 'created_term', 'downloadlist_admin_icon_set_fields_save', 10, 3);
add_action( 'edit_term', 'downloadlist_admin_icon_set_fields_save', 10, 3);

/**
 * Add column for default-marker in iconset-table.
 *
 * @param array $columns List of columns
 * @return array
 */
function downloadlist_admin_iconset_columns( array $columns ): array {
	// add our column.
	$columns['downloadlist_iconset_default'] = __( 'Default iconset', 'downloadlist' );

	// remove count-row.
	unset($columns['posts']);
	unset($columns['description']);

	// return resulting array.
	return $columns;
}
add_filter( 'manage_edit-dl_icon_set_columns', 'downloadlist_admin_iconset_columns', 10, 1 );

/**
 * Set content for new column in iconset-table.
 *
 * @param $content
 * @param $column_name
 * @param $term_id
 * @return string
 */
function downloadlist_admin_iconset_column( $content, $column_name, $term_id ): string {
	if( 'downloadlist_iconset_default' === $column_name ) {
		// define link to set iconset as default.
		$link = add_query_arg([
				'action' => 'downloadlist_iconset_default',
				'nonce' => wp_create_nonce( 'downloadlist-set_iconset-default' ),
				'term_id' => $term_id
			],
			get_admin_url() . 'admin.php'
		);

		// define output.
		$content = '<a href="'.esc_url($link).'" class="dashicons dashicons-no">&nbsp;</a>';
		if( get_term_meta( $term_id, 'default', true ) ) {
			$content = '<span class="dashicons dashicons-yes"></span>';
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

	$term_id = absint($_GET['term_id']);

	if( $term_id > 0 ) {
		helper::set_iconset_default( $term_id );
	}

	// redirect user
	wp_redirect($_SERVER['HTTP_REFERER']);
}
add_action( 'admin_action_downloadlist_iconset_default', 'downloadlist_admin_iconset_set_default');

/**
 * Hide post-entry which are assigned to generated iconsets.
 *
 * @param $query
 * @return void
 */
function downloadlist_hide_generated_iconsets( $query ): void {
	if( is_admin() && $query->is_main_query() && 'dl_icons' === $query->query['post_type'] ) {
		$query->set( 'tax_query', array(
			array(
				'taxonomy' => 'dl_icon_set',
				'terms' => Iconsets::get_instance()->get_generic_sets_as_slug_array(),
				'field' => 'slug',
				'operator' => 'NOT IN'
			)
		) );
	}
}
add_action( 'pre_get_posts', 'downloadlist_hide_generated_iconsets' );
