<?php
/**
 * File for base functions for each iconset.
 *
 * @package download-list-block-with-icons
 */

namespace downloadlist;

/**
 * Define the base-functions for each iconset.
 */
class Iconset_Base {
	/**
	 * Label of the iconset.
	 *
	 * @var string
	 */
	protected string $label = '';

	/**
	 * Slug of the iconset.
	 *
	 * @var string
	 */
	protected string $slug = '';

	/**
	 * Typ of the iconset.
	 *
	 * @var string
	 */
	protected string $type = '';

	/**
	 * Marker if this iconset should be default on plugin-installation.
	 *
	 * @var bool
	 */
	protected bool $should_be_default = false;

	/**
	 * This iconset is a generic iconset (e.g. a font) where users can not add custom icons.
	 *
	 * @var bool
	 */
	protected bool $generic = false;

	/**
	 * Instance of this object.
	 *
	 * @var ?Iconset_Base
	 */
	private static ?Iconset_Base $instance = null;

	/**
	 * Constructor for Init-Handler.
	 */
	private function __construct() {
		$this->init();
	}

	/**
	 * Prevent cloning of this object.
	 *
	 * @return void
	 */
	private function __clone() { }

	/**
	 * Return the instance of this Singleton object.
	 */
	public static function get_instance(): Iconset_Base {
		if ( ! static::$instance instanceof static ) {
			static::$instance = new static();
		}
		return static::$instance;
	}

	/**
	 * Return whether the iconset has a type set.
	 *
	 * @return bool
	 */
	public function has_type(): bool {
		return ! empty( $this->type );
	}

	/**
	 * Return whether the iconset has a label set.
	 *
	 * @return bool
	 */
	public function has_label(): bool {
		return ! empty( $this->label );
	}

	/**
	 * Return the iconset-label.
	 *
	 * @return string
	 */
	public function get_label(): string {
		return $this->label;
	}

	/**
	 * Return the iconset-type.
	 *
	 * @return string
	 */
	public function get_type(): string {
		return $this->type;
	}

	/**
	 * Return the iconset-type.
	 *
	 * @return string
	 */
	public function get_slug(): string {
		return $this->slug;
	}

	/**
	 * Return whether this iconset should be default on plugin-installation.
	 *
	 * @return bool
	 * @noinspection PhpUnused
	 */
	public function should_be_default(): bool {
		return $this->should_be_default;
	}

	/**
	 * Return whether this iconset should add an icon entry on plugin-installation.
	 *
	 * @return bool
	 * @noinspection PhpUnused
	 */
	public function is_generic(): bool {
		return $this->generic;
	}

	/**
	 * Return list of supported file-types.
	 *
	 * @return array
	 */
	public function get_file_types(): array {
		return array();
	}

	/**
	 * Get style for given file-type.
	 *
	 * @param int    $post_id ID of the icon-post.
	 * @param string $term_slug ID of the iconset-term.
	 * @param string $filetype Name for the filetype to add.
	 * @return string
	 */
	public function get_style_for_filetype( int $post_id, string $term_slug, string $filetype ): string {
		return '';
	}

	/**
	 * Get icons this set is assigned to.
	 *
	 * @return array
	 */
	public function get_icons(): array {
		return array();
	}
}
