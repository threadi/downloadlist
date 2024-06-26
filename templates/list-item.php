<?php
/**
 * Template for list item
 *
 * @package download-list-block-with-icons
 */

?>
<li class="attachment-<?php echo absint( $file_id ); ?> file_<?php echo esc_attr( $type ); ?> file_<?php echo esc_attr( $subtype ); ?> <?php echo esc_attr( $hide_icon ); ?>"><a href="<?php echo esc_url( $url ); ?>"<?php echo esc_attr( $download_attribute ); ?> <?php echo ! empty( $rel_attribute ) ? ' rel="' . esc_attr( $rel_attribute ) . '"' : ''; ?>><?php echo esc_html( $attachment->post_title ); ?></a><?php echo wp_kses_post( $filesize ) . wp_kses_post( $download_button ) . wp_kses_post( $description ); ?></li>
