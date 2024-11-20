<?php
/**
 * File with helper-functions for this plugin.
 *
 * @package download-list-block-with-icons
 */

namespace downloadlist;

// prevent direct access.
defined( 'ABSPATH' ) || exit;

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

		/**
		 * Filter the list of possible mimetypes.
		 *
		 * @param array $mime_types List of the mime types.
		 * @since 3.4.0 Available since 3.4.0
		 */
		return apply_filters( 'downloadlist_fontawesome_files', $mime_types );
	}

	/**
	 * Return the filename for the style-file.
	 *
	 * @return string
	 */
	private static function get_style_filename(): string {
		$filename = 'downloadlist-style.css';

		/**
		 * Set the filename for the style.css which will be saved in upload-directory.
		 *
		 * @since 3.4.0 Available since 3.4.0.
		 *
		 * @param string $filename The list of iconsets.
		 */
		return apply_filters( 'downloadlist_style_filename', $filename );
	}

	/**
	 * Get path to the generated style-file of this plugin.
	 *
	 * @return string
	 */
	public static function get_style_path(): string {
		$upload_dir = wp_get_upload_dir();

		$path = trailingslashit( $upload_dir['basedir'] );

		/**
		 * Filter the path where the CSS-file will be saved.
		 *
		 * @since 3.4.0 Available since 3.4.0
		 *
		 * @param string $styles The CSS-code.
		 */
		return apply_filters( 'downloadlist_css_path', $path ) . self::get_style_filename();
	}

	/**
	 * Get path to the generated style-file of this plugin.
	 *
	 * @return string
	 */
	public static function get_style_url(): string {
		$upload_dir = wp_get_upload_dir();

		$url = trailingslashit( $upload_dir['baseurl'] );

		/**
		 * Filter the path where the CSS-file will be linked.
		 *
		 * @since 3.4.0 Available since 3.4.0
		 *
		 * @param string $styles The CSS-code.
		 */
		return apply_filters( 'downloadlist_css_url', $url ) . self::get_style_filename();
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

		$false = false;
		/**
		 * Prevent generation of new CSS-files.
		 *
		 * @param array $false Set to true to prevent the generation.
		 * @since 3.4.0 Available since 3.4.0
		 */
		if ( apply_filters( 'downloadlist_prevent_css_generation', $false ) ) {
			return;
		}

		// define variable for resulting styles.
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
			'meta_query'     => array(
				array(
					'key'     => 'file_type',
					'compare' => 'NOT EXISTS',
				),
			),
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

		// merge all results.
		$icons = array_merge( $non_generic_icons->posts, $generic_icons->posts );

		// list of slugs which are generated.
		$iconset_generated_slugs = array();

		// loop through the resulting list of icons.
		foreach ( $icons as $post_id ) {
			// get the assigned icon-set.
			$terms = wp_get_object_terms( $post_id, 'dl_icon_set' );

			// continue with next if no iconset is assigned.
			if ( empty( $terms ) ) {
				continue;
			}

			// get iconset-object for this post.
			$iconset_obj = Iconsets::get_instance()->get_iconset_by_slug( $terms[0]->slug );

			// bail if no iconset-object could be loaded.
			if ( false === $iconset_obj ) {
				continue;
			}

			// bail if this generic iconset-slug has already been generated.
			if ( $iconset_obj->is_generic() && ! empty( $iconset_generated_slugs[ $iconset_obj->get_slug() ] ) ) {
				continue;
			}

			// load just the styles on generic iconsets.
			if ( false !== $iconset_obj->is_generic() ) {
				$styles .= $iconset_obj->get_style_for_filetype( $post_id, $terms[0]->slug, '' );
			} else {
				// get file-type with main- and subtype.
				$file_type_name = get_post_meta( $post_id, 'file_type', true );

				// get type and subtype.
				list($type, $subtype) = self::get_type_and_subtype_from_mimetype( $file_type_name );

				// get iconset-specific styles.
				$styles .= $iconset_obj->get_style_for_filetype( $post_id, $terms[0]->slug, $type );
				if ( ! empty( $subtype ) && false === $iconset_obj->is_generic() ) {
					$styles .= $iconset_obj->get_style_for_filetype( $post_id, $terms[0]->slug, $subtype );
				}
			}

			// add slug to list of generated iconsets.
			$iconset_generated_slugs[ $iconset_obj->get_slug() ] = 1;
		}

		// write resulting code in upload-directory.
		$style_path = helper::get_style_path();
		if ( ! empty( $styles ) ) {
			// Make sure that the above variable is properly setup.
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();

			// create directory if it does not exist atm.
			if ( false === $wp_filesystem->exists( dirname( $style_path ) ) ) {
				$wp_filesystem->mkdir( dirname( $style_path ) );
			}

			/**
			 * Filter the CSS-code just before it is saved.
			 *
			 * @since 3.4.0 Available since 3.4.0
			 *
			 * @param string $styles The CSS-code.
			 */
			$styles = apply_filters( 'downloadlist_generate_css', $styles );

			// save the given content to the path.
			$wp_filesystem->put_contents( $style_path, $styles );

			/**
			 * Run additional tasks after generating of the CSS-file.
			 *
			 * @param string $styles The CSS-code.
			 *
			 * @since 3.4.0 Available since 3.4.0
			 */
			do_action( 'downloadlist_generate_css', $styles );
		} elseif ( file_exists( $style_path ) ) {
			wp_delete_file( $style_path );
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
		return array(
			/**
			 * Filter the string name of a mime type.
			 *
			 * @since 3.4.0 Available since 3.4.0
			 *
			 * @param string $type The name of the mime type.
			 */
			apply_filters( 'downloadlist_generate_classname', $type ),

			/**
			 * Filter the string name of a mime type.
			 *
			 * @since 3.4.0 Available since 3.4.0
			 *
			 * @param string $type The name of the mime type.
			 */
			apply_filters( 'downloadlist_generate_classname', $subtype ),
		);
	}

	/**
	 * Regenerate all icons used by iconsets of this plugin.
	 *
	 * @param int $term_id Only regenerate file of the given iconset (optional).
	 * @return void
	 */
	public static function regenerate_icons( int $term_id = 0 ): void {
		$false = false;
		/**
		 * Prevent generation of icons used by iconsets of this plugin.
		 *
		 * @param array $false Set to true to prevent the generation.
		 * @since 3.4.0 Available since 3.4.0
		 */
		if ( apply_filters( 'downloadlist_prevent_icon_generation', $false ) ) {
			return;
		}

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

			// bail if iconset is a generic iconset which does not contain images.
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
			/**
			 * Set suffix for generated filename.
			 *
			 * @param array $suffix The suffix to use.
			 * @since 3.4.0 Available since 3.4.0
			 */
			$suffix = apply_filters( 'downloadlist_prevent_icon_generation', $suffix );

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

					$false = false;
					/**
					 * Prevent generation of specific icon.
					 *
					 * @param bool $false Set to true to prevent generation.
					 * @param int $post_id The ID of the attachment.
					 * @since 3.4.0 Available since 3.4.0
					 */
					if ( apply_filters( 'downloadlist_prevent_icon_generation', $false, $post_id ) ) {
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
	 * Add generic iconsets, if they do not exist atm.
	 *
	 * @return void
	 */
	public static function add_generic_iconsets(): void {
		// add predefined iconsets to taxonomy if they do not exist.
		foreach ( Iconsets::get_instance()->get_icon_sets() as $iconset_obj ) {
			// bail if iconset has not our base-class.
			if ( ! ( $iconset_obj instanceof Iconset_Base ) ) {
				continue;
			}

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
			} else {
				$term_obj = get_term_by( 'slug', $iconset_obj->get_slug(), 'dl_icon_set' );
				$term     = array(
					'term_id' => $term_obj->term_id,
				);
			}

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

				// generate icon entry, if this is a generic or graphic iconset, if it does not exist.
				if ( $iconset_obj->is_generic() || $iconset_obj->is_gfx() ) {
					$query = array(
						'post_type'  => 'dl_icons',
						'post_title' => $iconset_obj->get_label(),
						'tax_query'  => array(
							array(
								'taxonomy' => 'dl_icon_set',
								'terms'    => $term['term_id'],
								'field'    => 'slug',
								'operator' => 'IN',
							),
						),
						'fields'     => 'ids',
					);
					$check = new WP_Query( $query );
					if ( 0 === $check->post_count ) {
						$query   = array(
							'post_type'   => 'dl_icons',
							'post_status' => 'publish',
							'post_title'  => $iconset_obj->get_label(),
						);
						$post_id = wp_insert_post( $query );
						if ( $post_id > 0 ) {
							// mark as generated.
							update_post_meta( $post_id, 'generic-downloadlist', 1 );
							// assign post to this taxonomy.
							wp_set_object_terms( $post_id, $term['term_id'], 'dl_icon_set' );
						}
					}
				}
			}
		}
	}

	/**
	 * Return the version of the given file.
	 *
	 * With WP_DEBUG or plugin-debug enabled its @filemtime().
	 * Without this it's the plugin-version.
	 *
	 * @param string $filepath The absolute path to the requested file.
	 *
	 * @return string
	 */
	public static function get_file_version( string $filepath ): string {
		// check for WP_DEBUG.
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			return filemtime( $filepath );
		}

		$plugin_version = DL_VERSION;

		/**
		 * Filter the used file version (for JS- and CSS-files which get enqueued).
		 *
		 * @since 3.6.0 Available since 3.6.0.
		 *
		 * @param string $plugin_version The plugin-version.
		 * @param string $filepath The absolute path to the requested file.
		 */
		return apply_filters( 'downloadlist_file_version', $plugin_version, $filepath );
	}
}
