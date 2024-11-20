<?php
/**
 * Template for list item.
 *
 * @param int $file_id The ID of the file.
 * @param string $type The file type.
 * @param string $subtype The file subtype.
 * @param string $hide_icon The class to hide the icon.
 * @param string $url The URL for the file.
 * @param string $download_attribute The attributes for the download.
 * @param string $rel_attribute The rel attribute.
 * @param WP_Post $attachment The attachment as WP_Post object.
 * @param string $filesize The formatted file size.
 * @param string $download_button The download button.
 * @param string $description The description.
 *
 * @package download-list-block-with-icons
 */

?>
<li class="attachment-<?php echo absint( $file_id ); ?> file_<?php echo esc_attr( $type ); ?> file_<?php echo esc_attr( $subtype ); ?> <?php echo esc_attr( $hide_icon ); ?>"><a href="<?php echo esc_url( $url ); ?>"<?php echo esc_attr( $download_attribute ); ?> <?php echo ! empty( $rel_attribute ) ? ' rel="' . esc_attr( $rel_attribute ) . '"' : ''; ?>><?php echo esc_html( $attachment->post_title ); ?></a><?php echo wp_kses_post( $filesize ) . wp_kses_post( $download_button ) . wp_kses_post( $description ); ?></li>
