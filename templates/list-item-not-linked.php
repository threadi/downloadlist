<?php
/**
 * Template for list item.
 *
 * @param int $file_id The ID of the file.
 * @param string $type The file type.
 * @param string $subtype The file subtype.
 * @param string $hide_icon The class to hide the icon.
 * @param string $url The URL for the file.
 * @param string $download_link_attribute The attribute for force the download in browser.
 * @param string $rel_attribute The rel attribute.
 * @param WP_Post $attachment The attachment as WP_Post object.
 * @param string $filesize The formatted file size.
 * @param string $download_button The download button.
 * @param string $description The description.
 * @param string $file_date The file date.
 * @param string $link_target The value for the target attribute.
 *
 * @version: 4.0.0
 * @package download-list-block-with-icons
 */

?>
<li class="attachment-<?php echo absint( $file_id ); ?> file_<?php echo esc_attr( $type ); ?> file_<?php echo esc_attr( $subtype ); ?> <?php echo esc_attr( $hide_icon ); ?>">
								<?php
									echo esc_html( $attachment->post_title );
									echo wp_kses_post( $filesize ) . wp_kses_post( $download_button ) . wp_kses_post( $description ) . wp_kses_post( $file_date );
								?>
</li>
