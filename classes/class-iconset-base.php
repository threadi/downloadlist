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
	 * This iconset contains graphics which will be generated.
	 *
	 * @var bool
	 */
	protected bool $gfx = false;

	/**
	 * Instance of this object.
	 *
	 * @var ?Iconset_Base
	 */
	private static ?Iconset_Base $instance = null;

	/**
	 * Constructor for every Iconbase-object.
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
	 * @noinspection PhpUnused
	 */
	public function has_type(): bool {
		return ! empty( $this->type );
	}

	/**
	 * Return whether the iconset has a label set.
	 *
	 * @return bool
	 * @noinspection PhpUnused
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
	 * Return whether this iconset used generated graphics.
	 *
	 * @return bool
	 * @noinspection PhpUnused
	 */
	public function is_gfx(): bool {
		return $this->gfx;
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
	 * Get icons this set is assigned to.
	 *
	 * @return array
	 */
	public function get_icons(): array {
		return array();
	}

	/**
	 * Return style for single file.
	 *
	 * @param int $attachment_id ID of the attachment.
	 * @return string
	 * @noinspection PhpIfWithCommonPartsInspection
	 */
	public function get_style_for_file( int $attachment_id ): string {
		if ( $attachment_id > 0 ) {
			return '';
		}
		return '';
	}

	/**
	 * Return nothing as this iconset does not use any iconset-specific styles.
	 *
	 * @return array
	 */
	public function get_style_files(): array {
		return array();
	}
}
