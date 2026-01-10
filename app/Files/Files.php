<?php
/**
 * File for handling files.
 *
 * @package download-list-block-with-icons
 */

namespace DownloadListWithIcons\Files;

// prevent direct access.
defined( 'ABSPATH' ) || exit;

use WP_Post;

/**
 * Object to handle files.
 */
class Files {
	/**
	 * Instance of this object.
	 *
	 * @var ?Files
	 */
	private static ?Files $instance = null;

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
	public static function get_instance(): Files {
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
		add_filter( 'attachment_fields_to_edit', array( $this, 'add_custom_text_field_to_attachment_fields_to_edit' ), 10, 2 );
		add_filter( 'attachment_fields_to_save', array( $this, 'save_custom_text_attachment_field' ), 10, 2 );
	}

	/**
	 * Add new field for attachments.
	 *
	 * @param array<string,array<string,string>> $form_fields The list of fields.
	 * @param WP_Post                            $post The attachment-object.
	 * @return array<string,array<string,string>>
	 */
	public function add_custom_text_field_to_attachment_fields_to_edit( array $form_fields, WP_Post $post ): array {
		// get actual custom title.
		$dl_title = get_post_meta( $post->ID, 'dl_title', true );

		// add field for title.
		$form_fields['dl_title'] = array(
			'label' => __( 'Title for download list (optional)', 'download-list-block-with-icons' ),
			'input' => 'text',
			'value' => $dl_title,
		);

		// get actual custom description.
		$dl_description = get_post_meta( $post->ID, 'dl_description', true );

		// add field for title.
		$form_fields['dl_description'] = array(
			'label' => __( 'Description for download list (optional)', 'download-list-block-with-icons' ),
			'input' => 'textarea',
			'value' => $dl_description,
		);

		// return the field list.
		return $form_fields;
	}

	/**
	 * Save values from our custom fields for attachments.
	 *
	 * @param array<string,int>   $post The attachment-array.
	 * @param array<string,mixed> $fields The form fields.
	 * @return array<string,int>
	 */
	public function save_custom_text_attachment_field( array $post, array $fields ): array {
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
}
