<?php
/**
 * File to handle installer-tasks.
 *
 * @package download-list-block-with-icons
 */

namespace downloadlist;

/**
 * Object to handle installer-tasks.
 */
class Installer {

	/**
	 * Instance of this object.
	 *
	 * @var ?Installer
	 */
	private static ?Installer $instance = null;

	/**
	 * Constructor for Init-Handler.
	 */
	private function __construct() {}

	/**
	 * Prevent cloning of this object.
	 *
	 * @return void
	 */
	private function __clone() { }

	/**
	 * Return the instance of this Singleton object.
	 */
	public static function get_instance(): Installer {
		if ( ! static::$instance instanceof static ) {
			static::$instance = new static();
		}
		return static::$instance;
	}

	/**
	 * Run during installation/activation of this plugin.
	 *
	 * @return void
	 */
	public function activation(): void {
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

					// generate icon entry, if this is a generic iconset.
					if ( $iconset_obj->is_generic() ) {
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

		// initialize our own post-type and taxonomies during installation.
		downloadlist_add_position_posttype();
		downloadlist_add_taxonomies();

		// generate icons and styles.
		Helper::regenerate_icons();
		Helper::generate_css();
	}

	/**
	 * Run during deactivation of this plugin.
	 *
	 * @return void
	 */
	public function deactivation(): void {

	}

}
