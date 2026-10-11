<?php
/**
 * File with helper-functions for this plugin.
 *
 * @package download-list-block-with-icons
 */

namespace DownloadListWithIcons\Plugin;

// prevent direct access.
defined( 'ABSPATH' ) || exit;

use DownloadListWithIcons\Iconsets\Iconset_Base;
use DownloadListWithIcons\Iconsets\Iconsets;
use WP_Error;
use WP_Filesystem_Base;
use WP_Filesystem_Direct;
use WP_Image_Editor;
use WP_Post;
use WP_Query;
use WP_Term;
use WP_Term_Query;

/**
 * Helper-method.
 */
class Helper {
	/**
	 * Return possible mime-types.
	 *
	 * @return array<string,string>
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
		return apply_filters( 'downloadlist_mime_types', $mime_types );
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
	 * Generate the style-file for the icons on request (e.g. if a new cpt is saved).
	 *
	 * The styles of all iconsets are saved in one file, so it is always generated for all iconsets.
	 *
	 * @param int $term_id Deprecated since 5.0.0, the styles are always generated for all iconsets.
	 * @return void
	 * @noinspection PhpConditionAlreadyCheckedInspection
	 * @noinspection PhpUnusedParameterInspection
	 */
	public static function generate_css( int $term_id = 0 ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
		$false = false;
		/**
		 * Prevent generation of new CSS-files.
		 *
		 * @param bool $false Set to true to prevent the generation.
		 * @since 3.4.0 Available since 3.4.0
		 */
		if ( apply_filters( 'downloadlist_prevent_css_generation', $false ) ) {
			return;
		}

		// define variable for the resulting styles.
		$styles = '';

		// get all icons of non-generic iconsets which are configured with icon-set and file-type.
		$query_non_generic_icons = array(
			'post_type'      => 'dl_icons',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'no_found_rows'  => true,
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Necessary meta lookup.
				array(
					'key'     => 'file_type',
					'compare' => 'EXISTS',
				),
			),
			'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Necessary meta lookup; admin/sync context.
				array(
					'taxonomy' => 'dl_icon_set',
					'operator' => 'EXISTS',
				),
			),
			'fields'         => 'ids',
		);
		$non_generic_icons       = new WP_Query( $query_non_generic_icons );

		// get all generic iconsets.
		$query_generic_icons = array(
			'post_type'      => 'dl_icons',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'no_found_rows'  => true,
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Necessary meta lookup.
				array(
					'key'     => 'file_type',
					'compare' => 'NOT EXISTS',
				),
			),
			'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Necessary meta lookup; admin/sync context.
				array(
					'taxonomy' => 'dl_icon_set',
					'terms'    => Iconsets::get_instance()->get_generic_sets_as_slug_array(),
					'field'    => 'slug',
					'operator' => 'IN',
				),
			),
			'fields'         => 'ids',
		);
		$generic_icons       = new WP_Query( $query_generic_icons );

		// merge all results.
		$icons = array_merge( $non_generic_icons->posts, $generic_icons->posts );

		// list of slugs which are generated.
		$iconset_generated_slugs = array();

		// list of terms for which the font face has been added.
		$font_faces_added = array();

		// loop through the resulting list of icons.
		foreach ( $icons as $post_id ) {
			if ( $post_id instanceof WP_Post ) {
				continue;
			}
			$post_id = absint( $post_id );

			// get the assigned icon-set.
			$terms = wp_get_object_terms( $post_id, 'dl_icon_set' );

			// bail if terms is not an array.
			if ( ! is_array( $terms ) ) {
				continue;
			}

			// bail if no first result does exist.
			if ( ! isset( $terms[0] ) ) {
				continue;
			}

			// get iconset-object for this post.
			$iconset_obj = Iconsets::get_instance()->get_iconset_by_slug( $terms[0]->slug );

			// bail if no iconset-object could be loaded.
			if ( ! $iconset_obj instanceof Iconset_Base ) {
				continue;
			}

			// bail if this generic iconset-slug has already been generated.
			if ( $iconset_obj->is_generic() && ! empty( $iconset_generated_slugs[ $iconset_obj->get_slug() ] ) ) {
				continue;
			}

			// add font face if font is set on iconset and has not already been added.
			$font = get_term_meta( $terms[0]->term_id, 'font', true );
			if ( ! empty( $font ) && is_string( $font ) && empty( $font_faces_added[ $terms[0]->term_id ] ) ) {
				$styles                                .= '@font-face {
  font-family: "' . sanitize_html_class( $terms[0]->slug ) . '";
  src: url("' . esc_url( $font ) . '");
}';
				$font_faces_added[ $terms[0]->term_id ] = 1;
			}

			// load just the styles on generic iconsets.
			if ( false !== $iconset_obj->is_generic() ) {
				$styles .= $iconset_obj->get_style_for_filetype( $post_id, $terms[0]->slug, '' );
			} else {
				// get file-type with main- and subtype.
				$file_type_name = get_post_meta( $post_id, 'file_type', true );

				// get type and subtype.
				if ( ! empty( $file_type_name ) && is_string( $file_type_name ) ) {
					list($type, $subtype) = array_pad( self::get_type_and_subtype_from_mimetype( $file_type_name ), 2, '' );

					// get iconset-specific styles.
					$styles .= $iconset_obj->get_style_for_filetype( $post_id, $terms[0]->slug, $type );
					if ( ! empty( $subtype ) ) {
						$styles .= $iconset_obj->get_style_for_filetype( $post_id, $terms[0]->slug, $subtype );
					}
				}
			}

			// add slug to list of generated iconsets.
			$iconset_generated_slugs[ $iconset_obj->get_slug() ] = 1;
		}

		// get the filesystem handler.
		$wp_filesystem = self::get_wp_filesystem();

		// get the path.
		$style_path = self::get_style_path();

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

		// save an empty file if no styles are generated, so the file is not generated again on every request.
		if ( empty( $styles ) ) {
			$styles = '/* no custom icons */';
		}

		// save the content in a temporary file first and replace the file then, so visitors never get an incomplete file.
		$temp_path = $style_path . '.' . wp_generate_password( 8, false ) . '.tmp';
		if ( ! $wp_filesystem->put_contents( $temp_path, $styles ) ) {
			return;
		}

		// rename() replaces the file in one step on local file systems; the other file systems delete it first.
		$moved = $wp_filesystem instanceof WP_Filesystem_Direct ? rename( $temp_path, $style_path ) : $wp_filesystem->move( $temp_path, $style_path, true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.rename_rename
		if ( ! $moved ) {
			$wp_filesystem->delete( $temp_path );
			return;
		}

		/**
		 * Run additional tasks after generating of the CSS-file.
		 *
		 * @param string $styles The CSS-code.
		 *
		 * @since 3.4.0 Available since 3.4.0
		 */
		do_action( 'downloadlist_generate_css', $styles );
	}

	/**
	 * Return whether this server could generate preview images of PDF files.
	 *
	 * @return bool
	 */
	public static function is_pdf_preview_supported(): bool {
		// use the cached result, as the check loads the image editors.
		$supported = get_transient( 'downloadlist_pdf_preview_supported' );
		if ( false !== $supported ) {
			return 'yes' === $supported;
		}

		// check it and cache the result for one day.
		$supported = self::can_render_pdf();
		set_transient( 'downloadlist_pdf_preview_supported', $supported ? 'yes' : 'no', DAY_IN_SECONDS );

		// return the result.
		return $supported;
	}

	/**
	 * Return whether a PDF file could really be rendered as image.
	 *
	 * Imagick reports PDF as supported even if Ghostscript is missing or the policy of ImageMagick
	 * forbids PDF files (the default in many Linux distributions). So we try to load a small PDF.
	 *
	 * @return bool
	 */
	private static function can_render_pdf(): bool {
		// bail if no image editor supports PDF files.
		if ( ! wp_image_editor_supports( array( 'mime_type' => 'application/pdf' ) ) ) {
			return false;
		}

		// create a minimal PDF file with one empty page.
		if ( ! function_exists( 'wp_tempnam' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}
		$file = wp_tempnam( 'downloadlist-preview-check.pdf' );
		$pdf  = "%PDF-1.1\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj\n3 0 obj<</Type/Page/Parent 2 0 R/MediaBox[0 0 10 10]>>endobj\ntrailer<</Root 1 0 R>>\n%%EOF";
		if ( ! self::get_wp_filesystem()->put_contents( $file, $pdf ) ) {
			return false;
		}

		// try to load it (this is the step which fails without Ghostscript or with a blocking policy).
		$editor = wp_get_image_editor( $file, array( 'mime_type' => 'application/pdf' ) );
		wp_delete_file( $file );

		// return the result.
		return ! is_wp_error( $editor );
	}

	/**
	 * Return the HTML for the preview image of the given attachment.
	 *
	 * Returns an empty string, if the attachment has no preview (e.g. a ZIP file or a PDF without generated preview).
	 *
	 * @param int $attachment_id The ID of the attachment.
	 * @param int $width The width of the preview in pixel.
	 *
	 * @return string
	 */
	public static function get_preview_image( int $attachment_id, int $width ): string {
		// use a reasonable width.
		$width = max( 16, min( 600, $width ) );

		// use the smallest generated size, which is large enough.
		$size = $width > 150 ? 'medium' : 'thumbnail';

		/**
		 * Filter the image size used for preview images.
		 *
		 * @since 5.0.0 Available since 5.0.0.
		 * @param string $size The image size.
		 * @param int $attachment_id The ID of the attachment.
		 * @param int $width The width of the preview.
		 */
		$size = apply_filters( 'downloadlist_preview_image_size', $size, $attachment_id, $width );

		// get the preview.
		$image = self::get_preview_data( $attachment_id, $size );

		// bail if no preview exists.
		if ( ! $image ) {
			return '';
		}

		// calculate the height.
		$height = (int) round( $image['height'] * $width / $image['width'] );

		// return the image.
		return '<img src="' . esc_url( $image['url'] ) . '" width="' . absint( $width ) . '" height="' . absint( $height ) . '" alt="" loading="lazy" decoding="async" class="downloadlist-preview">';
	}

	/**
	 * Return the data of the preview image of the given attachment in the given size.
	 *
	 * - Images: the image itself.
	 * - PDF files: the preview of the first page, generated by WordPress during the upload.
	 * - Audio and video files: the cover image (WordPress takes it from MP3 files during the upload,
	 *   for other files it could be set as featured image of the file).
	 *
	 * @param int    $attachment_id The ID of the attachment.
	 * @param string $size The image size.
	 *
	 * @return array{url: string, width: int, height: int}|false
	 */
	public static function get_preview_data( int $attachment_id, string $size ): array|false {
		// get the image of the file itself.
		$image = wp_get_attachment_image_src( $attachment_id, $size );

		// use the cover image of audio and video files.
		if ( ! self::is_valid_image_src( $image ) && in_array( self::get_preview_type( (string) get_post_mime_type( $attachment_id ) ), array( 'audio', 'video' ), true ) ) {
			$cover_id = absint( get_post_thumbnail_id( $attachment_id ) );
			if ( $cover_id > 0 ) {
				$image = wp_get_attachment_image_src( $cover_id, $size );
			}
		}

		// bail if no preview exists.
		if ( ! is_array( $image ) || ! self::is_valid_image_src( $image ) ) {
			return false;
		}

		// return the data.
		return array(
			'url'    => (string) $image[0],
			'width'  => absint( $image[1] ),
			'height' => absint( $image[2] ),
		);
	}

	/**
	 * Return whether the result of wp_get_attachment_image_src() is a usable image.
	 *
	 * @param mixed $image The result.
	 *
	 * @return bool
	 */
	private static function is_valid_image_src( mixed $image ): bool {
		return is_array( $image ) && ! empty( $image[0] ) && ! empty( $image[1] ) && ! empty( $image[2] );
	}

	/**
	 * Return how a file of the given mime type could get a preview image.
	 *
	 * @param string $mime_type The mime type.
	 *
	 * @return string "image", "pdf", "audio", "video" or "none" (the file type has no preview).
	 */
	public static function get_preview_type( string $mime_type ): string {
		if ( 'application/pdf' === $mime_type ) {
			return 'pdf';
		}
		foreach ( array( 'image', 'audio', 'video' ) as $type ) {
			if ( str_starts_with( $mime_type, $type . '/' ) ) {
				return $type;
			}
		}
		return 'none';
	}

	/**
	 * Return the class and the inline style for the frames of icons and preview images of a list.
	 *
	 * The frames are only used if previews are shown or if one of their options is set. Otherwise, the list keeps its
	 * previous look (no class and no style).
	 *
	 * @param array<string,mixed> $attributes The block attributes.
	 * @param bool                $show_preview Whether preview images are shown.
	 *
	 * @return array{class: string, style: string}
	 */
	public static function get_media_frame( array $attributes, bool $show_preview ): array {
		// get the values.
		$size          = ! empty( $attributes['previewWidth'] ) ? max( 16, min( 600, absint( $attributes['previewWidth'] ) ) ) : 48;
		$border_width  = isset( $attributes['mediaBorderWidth'] ) ? min( 20, absint( $attributes['mediaBorderWidth'] ) ) : 0;
		$border_radius = isset( $attributes['mediaBorderRadius'] ) ? min( 300, absint( $attributes['mediaBorderRadius'] ) ) : 0;
		$padding       = isset( $attributes['mediaPadding'] ) ? min( 100, absint( $attributes['mediaPadding'] ) ) : 0;
		$border_color  = self::sanitize_css_color( isset( $attributes['mediaBorderColor'] ) ? (string) $attributes['mediaBorderColor'] : '' );
		$background    = self::sanitize_css_color( isset( $attributes['mediaBackground'] ) ? (string) $attributes['mediaBackground'] : '' );

		// bail if no frame is used.
		if ( ! $show_preview && 0 === $border_width && 0 === $border_radius && 0 === $padding && '' === $background ) {
			return array(
				'class' => '',
				'style' => '',
			);
		}

		// collect the CSS variables.
		$style = '--downloadlist-media-size:' . $size . 'px;';
		if ( $border_width > 0 ) {
			$style .= '--downloadlist-media-border-width:' . $border_width . 'px;';
			if ( '' !== $border_color ) {
				$style .= '--downloadlist-media-border-color:' . $border_color . ';';
			}
		}
		if ( $border_radius > 0 ) {
			$style .= '--downloadlist-media-border-radius:' . $border_radius . 'px;';
		}
		if ( $padding > 0 ) {
			$style .= '--downloadlist-media-padding:' . $padding . 'px;';
		}
		if ( '' !== $background ) {
			$style .= '--downloadlist-media-background:' . $background . ';';
		}

		// return the result.
		return array(
			'class' => ' has-media-frame',
			'style' => $style,
		);
	}

	/**
	 * Return the given color, if it is a valid CSS color for our styles. Otherwise, return an empty string.
	 *
	 * Allowed to are hex colors, rgb(), rgba(), hsl(), hsla() and colors of the theme as CSS variable.
	 *
	 * @param string $color The color.
	 *
	 * @return string
	 */
	public static function sanitize_css_color( string $color ): string {
		$color = trim( $color );

		// a hex color.
		if ( preg_match( '/^#([0-9a-fA-F]{3,4}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/', $color ) ) {
			return $color;
		}

		// a color function.
		if ( preg_match( '/^(rgb|rgba|hsl|hsla)\(\s*[0-9.,%\s\/]+\)$/i', $color ) ) {
			return $color;
		}

		// a color of the theme.
		if ( preg_match( '/^var\(--wp--preset--color--[a-z0-9-]+\)$/', $color ) ) {
			return $color;
		}

		// no valid color.
		return '';
	}

	/**
	 * Return the taxonomies of folder plugins for the media library.
	 *
	 * Plugins like Folderly, HappyFiles or Folders save their virtual folders as hierarchical taxonomy
	 * for attachments. We detect them automatically, so no special code per plugin is necessary.
	 *
	 * @param bool $ignore_setting True to get them also if the support for folders is disabled in the settings.
	 *
	 * @return array<string,string> List of taxonomy names with their labels.
	 */
	public static function get_folder_taxonomies( bool $ignore_setting = false ): array {
		// bail if the support for folders is disabled.
		if ( ! $ignore_setting && ! self::is_folder_support_enabled() ) {
			return array();
		}

		$taxonomies = array();
		foreach ( get_object_taxonomies( 'attachment', 'objects' ) as $taxonomy ) {
			// bail if this is our own taxonomy for download lists.
			if ( 'dl_icon_lists' === $taxonomy->name ) {
				continue;
			}

			// bail if this taxonomy is not hierarchical, as folders are.
			if ( ! $taxonomy->hierarchical ) {
				continue;
			}

			// add the taxonomy with its label.
			$taxonomies[ $taxonomy->name ] = $taxonomy->label;
		}

		/**
		 * Filter the list of taxonomies which could be used as folders for download lists.
		 *
		 * The key is the name of the taxonomy for attachments, the value its label in the block editor.
		 *
		 * @since 5.0.0 Available since 5.0.0.
		 * @param array<string,string> $taxonomies List of taxonomies.
		 */
		$taxonomies = apply_filters( 'downloadlist_folder_taxonomies', $taxonomies );

		// return only taxonomies which are registered for attachments.
		return array_filter(
			$taxonomies,
			function ( $taxonomy ) {
				return is_object_in_taxonomy( 'attachment', (string) $taxonomy );
			},
			ARRAY_FILTER_USE_KEY
		);
	}

	/**
	 * Return whether the support for folders of folder plugins is enabled.
	 *
	 * @return bool
	 */
	public static function is_folder_support_enabled(): bool {
		return 1 === absint( get_option( 'downloadlist_folder_support', 1 ) );
	}

	/**
	 * Return the source of the files for a block, if it uses a download list or a folder.
	 *
	 * @param array<string,mixed> $attributes The block attributes.
	 *
	 * @return array{taxonomy: string, term_id: int, include_children: bool}|false
	 */
	public static function get_list_source( array $attributes ): array|false {
		// a download list of this plugin.
		if ( ! empty( $attributes['list'] ) ) {
			return array(
				'taxonomy'         => 'dl_icon_lists',
				'term_id'          => absint( $attributes['list'] ),
				'include_children' => true,
			);
		}

		// a folder of a folder plugin.
		if ( ! empty( $attributes['folder'] ) && ! empty( $attributes['folderTaxonomy'] ) && is_string( $attributes['folderTaxonomy'] ) ) {
			// bail if the folder plugin is not available anymore.
			if ( ! isset( self::get_folder_taxonomies()[ $attributes['folderTaxonomy'] ] ) ) {
				return false;
			}

			return array(
				'taxonomy'         => $attributes['folderTaxonomy'],
				'term_id'          => absint( $attributes['folder'] ),
				'include_children' => ! empty( $attributes['folderSubfolders'] ),
			);
		}

		// no source used.
		return false;
	}

	/**
	 * Return the IDs of the files in the given download list or folder.
	 *
	 * @param array{taxonomy: string, term_id: int, include_children: bool} $source The source.
	 *
	 * @return array<int,int>
	 */
	public static function get_source_file_ids( array $source ): array {
		$query = new WP_Query(
			array(
				'post_type'              => 'attachment',
				'post_status'            => 'inherit',
				'posts_per_page'         => -1,
				'fields'                 => 'ids',
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
				'tax_query'              => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Necessary lookup of the files in the list.
					array(
						'taxonomy'         => $source['taxonomy'],
						'field'            => 'term_id',
						'terms'            => $source['term_id'],
						'include_children' => $source['include_children'],
					),
				),
			)
		);

		// return the IDs.
		$ids = array();
		foreach ( $query->get_posts() as $post_id ) {
			$ids[] = absint( $post_id ); // @phpstan-ignore argument.type
		}
		return $ids;
	}

	/**
	 * Return the folders of all folder plugins.
	 *
	 * Each folder is returned with its taxonomy, the name and the depth in the tree of folders.
	 *
	 * @return array<int,array{id: string, taxonomy: string, taxonomy_label: string, term_id: int, name: string, depth: int, count: int}>
	 */
	public static function get_folders(): array {
		$folders = array();
		foreach ( self::get_folder_taxonomies() as $taxonomy => $label ) {
			$terms = get_terms(
				array(
					'taxonomy'   => $taxonomy,
					'hide_empty' => false,
					'orderby'    => 'name',
				)
			);

			// bail if no terms could be loaded.
			if ( ! is_array( $terms ) ) {
				continue;
			}

			// group them by their parent.
			$children = array();
			foreach ( $terms as $term ) {
				$children[ $term->parent ][] = $term;
			}

			// add them as tree, beginning on the top level.
			self::add_folders( $folders, $children, 0, 0, $taxonomy, $label );
		}
		return $folders;
	}

	/**
	 * Add the folders with the given parent to the list, followed by their sub folders.
	 *
	 * @param array<int,array{id: string, taxonomy: string, taxonomy_label: string, term_id: int, name: string, depth: int, count: int}> $folders The resulting list.
	 * @param array<int,array<int,WP_Term>>                                                                                              $children The terms grouped by their parent.
	 * @param int                                                                                                                        $parent_id The parent.
	 * @param int                                                                                                                        $depth The depth of the folders.
	 * @param string                                                                                                                     $taxonomy The taxonomy.
	 * @param string                                                                                                                     $label The label of the taxonomy.
	 *
	 * @return void
	 */
	private static function add_folders( array &$folders, array $children, int $parent_id, int $depth, string $taxonomy, string $label ): void {
		// bail if this folder has no sub folders or the tree is too deep (prevents endless loops on broken data).
		if ( empty( $children[ $parent_id ] ) || $depth > 20 ) {
			return;
		}

		foreach ( $children[ $parent_id ] as $term ) {
			$folders[] = array(
				'id'             => $taxonomy . ':' . $term->term_id,
				'taxonomy'       => $taxonomy,
				'taxonomy_label' => $label,
				'term_id'        => $term->term_id,
				'name'           => $term->name,
				'depth'          => $depth,
				'count'          => $term->count,
			);
			self::add_folders( $folders, $children, $term->term_id, $depth + 1, $taxonomy, $label );
		}
	}

	/**
	 * Return whether an iconset is marked as default.
	 *
	 * @return bool
	 */
	private static function has_default_iconset(): bool {
		$terms = get_terms(
			array(
				'taxonomy'   => 'dl_icon_set',
				'hide_empty' => false,
				'fields'     => 'ids',
				'number'     => 1,
				'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Necessary meta lookup, runs only on activation.
					array(
						'key'   => 'default',
						'value' => 1,
					),
				),
			)
		);
		return is_array( $terms ) && ! empty( $terms );
	}

	/**
	 * Return a string which could be used safely as string value in CSS (e.g. for "content").
	 *
	 * Removes all characters which could end the string or the rule.
	 *
	 * @param string $value The value.
	 * @return string
	 */
	public static function sanitize_css_string( string $value ): string {
		return str_replace( array( '"', "'", '<', '>', '{', '}', ';', "\n", "\r" ), '', $value );
	}

	/**
	 * Get type and subtype from given mimetype.
	 *
	 * @param string $mimetype The mimetype to split.
	 * @return array<int,string>
	 */
	public static function get_type_and_subtype_from_mimetype( string $mimetype ): array {
		// bail if no mime type is given.
		if ( empty( $mimetype ) ) {
			return array();
		}

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
	 * @noinspection PhpConditionAlreadyCheckedInspection
	 */
	public static function regenerate_icons( int $term_id = 0 ): void {
		$false = false;
		/**
		 * Prevent generation of icons used by iconsets of this plugin.
		 *
		 * @param bool $false Set to true to prevent the generation.
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
			$query['include'] = array( $term_id );
		}
		$results = new WP_Term_Query( $query );

		// get the results.
		$terms = $results->get_terms();

		// convert them if terms are not an array.
		if ( ! is_array( $terms ) ) {
			$terms = array( $terms );
		}

		// loop through them.
		foreach ( $terms as $term ) {
			// bail if term is not a term.
			if ( ! $term instanceof WP_Term ) {
				continue;
			}

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
			 * @param string $suffix The suffix to use.
			 * @since 3.4.0 Available since 3.4.0
			 */
			$suffix = apply_filters( 'downloadlist_icon_filename_suffix', $suffix );

			// get icons of this set.
			foreach ( $iconset_obj->get_icons() as $post_id ) {
				$post_id = absint( $post_id );

				// get the attachment_id.
				$attachment_id = absint( get_post_meta( $post_id, 'icon', true ) );
				if ( $attachment_id > 0 ) {
					// get the attachment metadata.
					$metadata = wp_get_attachment_metadata( $attachment_id );
					if ( ! is_array( $metadata ) ) {
						$metadata = array();
					}
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

					// bail if path could not be loaded.
					if ( ! is_string( $original_path ) ) {
						continue;
					}

					// get image editor object.
					$image_editor = wp_get_image_editor( $original_path );

					// bail if editor could not be loaded.
					if ( ! $image_editor instanceof WP_Image_Editor ) {
						continue;
					}

					// generate a proper destination filename.
					$destination_filename = $image_editor->generate_filename( $suffix );

					// resize the image via image editor object.
					$resized = $image_editor->resize( $width, $height );

					// bail on error.
					if ( is_wp_error( $resized ) ) {
						continue;
					}

					// save the resulting image under the generated destination filename.
					$saved = $image_editor->save( $destination_filename );

					// bail on error.
					if ( ! is_array( $saved ) || empty( $saved['path'] ) || ! file_exists( $saved['path'] ) ) {
						continue;
					}

					// update attachments metadata to add the new size (path and type could be changed during saving).
					$metadata['sizes'][ 'downloadlist-icon-' . $term->slug ] = array(
						'file'      => basename( $saved['path'] ),
						'width'     => $saved['width'],
						'height'    => $saved['height'],
						'mime-type' => $saved['mime-type'],
						'filesize'  => $saved['filesize'],
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
			// bail if one necessary setting is missing.
			if ( false === $iconset_obj->has_label() || false === $iconset_obj->has_type() ) {
				continue;
			}

			// check if this term already exists (by its slug, which is also used to add it).
			$is_new   = false;
			$term_obj = get_term_by( 'slug', $iconset_obj->get_slug(), 'dl_icon_set' );
			if ( $term_obj instanceof WP_Term ) {
				$term = array(
					'term_id' => $term_obj->term_id,
				);
			} else {
				// no, it does not exist. then add it now.
				$term   = wp_insert_term(
					$iconset_obj->get_label(),
					'dl_icon_set',
					array(
						'slug' => $iconset_obj->get_slug(),
					)
				);
				$is_new = is_array( $term );
			}

			if ( is_array( $term ) ) {
				// save the type for this term.
				update_term_meta( $term['term_id'], 'type', $iconset_obj->get_type() );

				// set this iconset as default, if set and if no other iconset is the default (keep the choice of the user).
				if ( $iconset_obj->should_be_default() && ( $is_new || ! self::has_default_iconset() ) ) {
					Iconsets::get_instance()->set_default_iconset( $term['term_id'] );
				}

				// set width and height to default ones, but do not overwrite values set by the user.
				if ( $is_new || empty( get_term_meta( $term['term_id'], 'width', true ) ) ) {
					update_term_meta( $term['term_id'], 'width', 24 );
				}
				if ( $is_new || empty( get_term_meta( $term['term_id'], 'height', true ) ) ) {
					update_term_meta( $term['term_id'], 'height', 24 );
				}

				// generate icon entry, if this is a generic or graphic iconset, if it does not exist.
				if ( $iconset_obj->is_generic() || $iconset_obj->is_gfx() ) {
					$query = array(
						'post_type'      => 'dl_icons',
						'post_status'    => array( 'any', 'trash' ),
						'posts_per_page' => 1,
						'no_found_rows'  => true,
						'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Necessary meta lookup; admin/sync context.
							array(
								'taxonomy' => 'dl_icon_set',
								'terms'    => $term['term_id'],
								'field'    => 'term_id',
								'operator' => 'IN',
							),
						),
						'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Necessary meta lookup; admin/sync context.
							array(
								'key'     => 'generic-downloadlist',
								'compare' => 'EXISTS',
							),
						),
						'fields'         => 'ids',
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
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && file_exists( $filepath ) ) {
			return (string) filemtime( $filepath );
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

	/**
	 * Return the support URL.
	 *
	 * @return string
	 */
	public static function get_support_url(): string {
		return 'https://wordpress.org/support/plugin/download-list-block-with-icons/';
	}

	/**
	 * Get all files of directory recursively.
	 *
	 * @param string $path The path.
	 * @return array<string,mixed>
	 */
	public static function get_files_from_directory( string $path = '.' ): array {
		$wp_filesystem = self::get_wp_filesystem();

		// get the directory listing.
		$dir_list = $wp_filesystem->dirlist( $path, true, true );

		// bail if no dir list could be loaded.
		if ( ! is_array( $dir_list ) ) {
			return array();
		}

		// load files recursive in array and return resulting list.
		return self::get_files( $dir_list, $path );
	}

	/**
	 * Recursively load files from given array.
	 *
	 * @param array<string,mixed> $files Array of file we iterate through.
	 * @param string              $path Absolute path where the files are located.
	 * @param array<string,mixed> $file_list List of files.
	 *
	 * @return array<string,mixed>
	 */
	private static function get_files( array $files, string $path, array $file_list = array() ): array {
		foreach ( $files as $filename => $settings ) {
			if ( 'f' === $settings['type'] ) {
				$file_list[ $filename ] = $path . $filename;
			}
			if ( 'd' === $settings['type'] ) {
				$file_list = self::get_files( $settings['files'], $path . trailingslashit( $filename ), $file_list );
			}
		}

		return $file_list;
	}

	/**
	 * Return the absolute URL to the plugin (already trailed with slash).
	 *
	 * @return string
	 */
	public static function get_plugin_url(): string {
		return trailingslashit( plugin_dir_url( DL_PLUGIN ) );
	}

	/**
	 * Return the absolute local filesystem-path (already trailed with slash) to the plugin.
	 *
	 * @return string
	 */
	public static function get_plugin_path(): string {
		return trailingslashit( plugin_dir_path( DL_PLUGIN ) );
	}

	/**
	 * Return the plugin support url: the forum on WordPress.org.
	 *
	 * @return string
	 */
	public static function get_plugin_support_url(): string {
		return 'https://wordpress.org/support/plugin/download-list-block-with-icons/';
	}

	/**
	 * Return the plugin name.
	 *
	 * @return string
	 */
	public static function get_plugin_name(): string {
		// get_plugin_data() is only loaded in backend, but this is also used in frontend, REST and cron.
		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$plugin_data = get_plugin_data( DL_PLUGIN, false, false );
		if ( ! empty( $plugin_data['Name'] ) ) {
			return $plugin_data['Name'];
		}
		return '';
	}

	/**
	 * Create JSON from given array.
	 *
	 * @param array<string|int,mixed>|WP_Error $source The source array.
	 * @param int                              $flag Flags to use for this JSON.
	 *
	 * @return string
	 */
	public static function get_json( array|WP_Error $source, int $flag = 0 ): string {
		// create JSON.
		$json = wp_json_encode( $source, $flag );

		// bail if creating the JSON failed.
		if ( ! $json ) {
			return '';
		}

		// return resulting JSON-string.
		return $json;
	}

	/**
	 * Check if WP CLI has been called.
	 *
	 * @return bool
	 */
	public static function is_cli(): bool {
		return defined( 'WP_CLI' ) && WP_CLI;
	}

	/**
	 * Return the WP Filesystem object.
	 *
	 * @param bool $local True to get the local filesystem object.
	 *
	 * @return WP_Filesystem_Base
	 */
	public static function get_wp_filesystem( bool $local = false ): WP_Filesystem_Base {
		// get WP Filesystem-handler for local files if requested.
		if ( $local ) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
			require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';

			return new WP_Filesystem_Direct( false );
		}

		// get global WP Filesystem handler.
		require_once ABSPATH . '/wp-admin/includes/file.php';
		\WP_Filesystem();
		global $wp_filesystem;

		// bail if "wp_filesystem" is not of "WP_Filesystem_Base".
		if ( ! $wp_filesystem instanceof WP_Filesystem_Base ) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
			require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';
			return new WP_Filesystem_Direct( false );
		}

		// return local object on any error.
		if ( $wp_filesystem->errors->has_errors() ) {
			// embed the local directory object.
			require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
			require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';

			return new WP_Filesystem_Direct( false );
		}

		// return the requested filesystem object.
		return $wp_filesystem;
	}

	/**
	 * Return whether plugin developer modus is enabled.
	 *
	 * @return bool
	 */
	public static function is_development_mode_active(): bool {
		return function_exists( 'wp_is_development_mode' ) && wp_is_development_mode( 'plugin' );
	}
}
