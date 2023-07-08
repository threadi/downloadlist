<?php
/**
 * File with helper-functions for this plugin.
 *
 * @package download-list-block-with-icons
 */

namespace downloadlist;

use WP_Query;
use WP_Term_Query;

/**
 * Helper-method.
 */
class Helper {

	/**
	 * Return possible mime-types.
	 *
	 * @return array
	 */
	public static function get_mime_types(): array {
		// get the WordPress-list of mime-types.
		$mime_types = wp_get_mime_types();

		// add general mime-types.
		$mime_types['application'] = 'application';
		$mime_types['audio']       = 'audio';
		$mime_types['image']       = 'image';
		$mime_types['video']       = 'video';
		ksort( $mime_types );

		// return the list.
		return $mime_types;
	}

	/**
	 * Get path to the generated style-file of this plugin.
	 *
	 * @return string
	 */
	public static function get_style_path(): string {
		$upload_dir = wp_get_upload_dir();
		return trailingslashit( $upload_dir['basedir'] ) . 'downloadlist-style.css';
	}

	/**
	 * Get path to the generated style-file of this plugin.
	 *
	 * @return string
	 */
	public static function get_style_url(): string {
		$upload_dir = wp_get_upload_dir();
		return trailingslashit( $upload_dir['baseurl'] ) . 'downloadlist-style.css';
	}

	/**
	 * Set given iconset as default.
	 *
	 * @param int $term_id ID of the term to set as default.
	 * @return void
	 */
	public static function set_iconset_default( int $term_id ): void {
		// delete all default-marker for icon-sets.
		$query   = array(
			'taxonomy'   => 'dl_icon_set',
			'hide_empty' => false,
		);
		$results = new WP_Term_Query( $query );
		foreach ( $results->get_terms() as $term ) {
			delete_term_meta( $term->term_id, 'default' );
		}

		// mark this as default icon-set.
		update_term_meta( $term_id, 'default', 1 );
	}

	/**
	 * Generate the style-file for the icons on request (e.g. if a new cpt is saved).
	 *
	 * @param int $term_id Only generate styles for the given term_id.
	 * @return void
	 */
	public static function generate_css( int $term_id = 0 ): void {
		global $wp_filesystem;

		// define variable for resulting content.
		$styles = '';

		// get all icons of non-generic iconsets which are configured with icon-set and file-type.
		$query_non_generic_icons = array(
			'post_type'      => 'dl_icons',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'meta_query'     => array(
				array(
					'key'     => 'file_type',
					'compare' => 'EXISTS',
				),
			),
			'tax_query'      => array(
				array(
					'taxonomy' => 'dl_icon_set',
					'operator' => 'EXISTS',
				),
			),
			'fields'         => 'ids',
		);
		if ( $term_id > 0 ) {
			$query_non_generic_icons['tax_query']   = array();
			$query_non_generic_icons['tax_query'][] = array(
				'taxonomy' => 'dl_icon_set',
				'field'    => 'term_id',
				'terms'    => $term_id,
			);
		}
		$non_generic_icons = new WP_Query( $query_non_generic_icons );

		// get all generic iconsets.
		$query_generic_icons = array(
			'post_type'      => 'dl_icons',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'tax_query'      => array(
				array(
					'taxonomy' => 'dl_icon_set',
					'terms'    => Iconsets::get_instance()->get_generic_sets_as_slug_array(),
					'field'    => 'slug',
					'operator' => 'IN',
				),
			),
			'fields'         => 'ids',
		);
		if ( $term_id > 0 ) {
			$query_generic_icons['tax_query']   = array();
			$query_generic_icons['tax_query'][] = array(
				'taxonomy' => 'dl_icon_set',
				'field'    => 'term_id',
				'terms'    => $term_id,
				'operator' => '=',
			);
		}
		$generic_icons = new WP_Query( $query_generic_icons );

		// mix all results.
		$icons = array_merge( $non_generic_icons->posts, $generic_icons->posts );

		// loop through the resulting list of icons.
		foreach ( $icons as $post_id ) {
			// get the assigned icon-set.
			$terms = wp_get_object_terms( $post_id, 'dl_icon_set' );

			// continue with next if no iconset is assigned.
			if ( empty( $terms ) ) {
				continue;
			}

			// get file-type with main- and subtype.
			$file_type_name = get_post_meta( $post_id, 'file_type', true );

			// continue with next if no file-type is assigned.
			if ( empty( $file_type_name ) ) {
				$file_type_name = '';
			}

			// get iconset-object for this post.
			$iconset_obj = Iconsets::get_instance()->get_iconset_by_slug( $terms[0]->slug );

			// bail if no iconset-object could be loaded.
			if ( false === $iconset_obj ) {
				continue;
			}

			// get type and subtype.
			list( $type, $subtype ) = self::get_type_and_subtype_from_mimetype( $file_type_name );

			// get iconset-specific styles.
			$styles .= $iconset_obj->get_style_for_filetype( $post_id, $terms[0]->slug, $type );
			if ( ! empty( $subtype ) ) {
				$styles .= $iconset_obj->get_style_for_filetype( $post_id, $terms[0]->slug, $subtype );
			}
		}

		// write resulting code in upload-directory.
		$style_path = helper::get_style_path();
		if ( ! empty( $styles ) ) {
			// Make sure that the above variable is properly setup.
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();

			// save the given content to the path.
			$wp_filesystem->put_contents( $style_path, $styles );
		} elseif ( file_exists( $style_path ) ) {
			unlink( $style_path );
		}
	}

	/**
	 * Get type and subtype from given mimetype.
	 *
	 * @param string $mimetype The mimetype to split.
	 * @return array
	 */
	public static function get_type_and_subtype_from_mimetype( string $mimetype ): array {
		// split the string.
		$mimetype_array = explode( '/', $mimetype );

		// get type.
		$type = $mimetype_array[0];

		// get subtype, if set.
		$subtype = '';
		if ( ! empty( $mimetype_array[1] ) ) {
			$subtype = $mimetype_array[1];
		}

		// return resulting values.
		return array( $type, $subtype );
	}

	/**
	 * Regenerate all icons used by iconsets of this plugin.
	 *
	 * @param int $term_id Only regenerate file of the given iconset (optional).
	 * @return void
	 */
	public static function regenerate_icons( int $term_id = 0 ): void {
		$query = array(
			'taxonomy'   => 'dl_icon_set',
			'hide_empty' => false,
		);
		if ( $term_id > 0 ) {
			$query['term_taxonomy_id'] = $term_id;
		}
		$results = new WP_Term_Query( $query );
		foreach ( $results->get_terms() as $term ) {
			// get iconset-object.
			$iconset_obj = Iconsets::get_instance()->get_iconset_by_slug( $term->slug );

			// bail if iconset could not be loaded.
			if ( false === $iconset_obj ) {
				continue;
			}

			// bail if iconset is a generic iconset which does not contains images.
			if ( $iconset_obj->is_generic() ) {
				continue;
			}

			// get width and height set on this iconset.
			$width  = absint( get_term_meta( $term->term_id, 'width', true ) );
			$height = absint( get_term_meta( $term->term_id, 'height', true ) );

			// bail if no width or height is available.
			if ( 0 === $width || 0 === $height ) {
				continue;
			}

			// set suffix for generated filename.
			$suffix = $width . 'x' . $height;

			// get icons of this set.
			foreach ( $iconset_obj->get_icons() as $post_id ) {
				// get the attachment_id.
				$attachment_id = absint( get_post_meta( $post_id, 'icon', true ) );
				if ( $attachment_id > 0 ) {
					// get the attachment metadata.
					$metadata = wp_get_attachment_metadata( $attachment_id );
					if ( empty( $metadata['sizes'] ) ) {
						$metadata['sizes'] = array();
					}

					// bail if the new size is already used by this plugin.
					if (
						! empty( $metadata['sizes'][ 'downloadlist-icon-' . $term->slug ]['width'] )
						&& $width === $metadata['sizes'][ 'downloadlist-icon-' . $term->slug ]['width']
						&& ! empty( $metadata['sizes'][ 'downloadlist-icon-' . $term->slug ]['height'] )
						&& $height === $metadata['sizes'][ 'downloadlist-icon-' . $term->slug ]['height']
					) {
						continue;
					}

					// get the path for the original file.
					$original_path = wp_get_original_image_path( $attachment_id );

					// get image editor object.
					$image_editor = wp_get_image_editor( $original_path );

					// generate a proper destination filename.
					$destination_filename = $image_editor->generate_filename( $suffix );

					// resize the image via image editor object.
					$image_editor->resize( $width, $height );

					// save the resulting image under the generated destination filename.
					$image_editor->save( $destination_filename );

					// update attachments metadata to add the new size.
					$metadata['sizes'][ 'downloadlist-icon-' . $term->slug ] = array(
						'file'      => basename( $destination_filename ),
						'width'     => $width,
						'height'    => $height,
						'mime-type' => wp_get_image_mime( $destination_filename ),
						'filesize'  => filesize( $destination_filename ),
					);
					wp_update_attachment_metadata( $attachment_id, $metadata );
				}
			}
		}
	}

	/**
	 * Add generic iconsets, if they are not exist atm.
	 *
	 * @return void
	 */
	public static function add_generic_iconset(): void {
		// add predefined terms to taxonomy if they do not exist.
		foreach ( Iconsets::get_instance()->get_icon_sets() as $iconset_obj ) {
			// bail if one necessary setting is missing.
			if ( false === $iconset_obj->has_label() || false === $iconset_obj->has_type() ) {
				continue;
			}

			// check if this term already exists.
			if ( ! term_exists( $iconset_obj->get_label(), 'dl_icon_set' ) ) {
				// no, it does not exist. then add it now.
				$term = wp_insert_term(
					$iconset_obj->get_label(),
					'dl_icon_set',
					array(
						'slug' => $iconset_obj->get_slug(),
					)
				);

				if ( ! is_wp_error( $term ) ) {
					// save the type for this term.
					update_term_meta( $term['term_id'], 'type', $iconset_obj->get_type() );

					// set this iconset as default, if set.
					if ( $iconset_obj->should_be_default() ) {
						update_term_meta( $term['term_id'], 'default', 1 );
					}

					// set width and height to default ones.
					update_term_meta( $term['term_id'], 'width', 24 );
					update_term_meta( $term['term_id'], 'height', 24 );

					// generate icon entry, if this is a generic or graphic iconset.
					if ( $iconset_obj->is_generic() || $iconset_obj->is_gfx() ) {
						$array   = array(
							'post_type'   => 'dl_icons',
							'post_status' => 'publish',
							'post_title'  => $iconset_obj->get_label(),
						);
						$post_id = wp_insert_post( $array );
						if ( $post_id > 0 ) {
							// assign post to this taxonomy.
							wp_set_object_terms( $post_id, $term['term_id'], 'dl_icon_set' );
						}
					}
				}
			}
		}
	}
}
