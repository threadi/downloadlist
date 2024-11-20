<?php
/**
 * Template for download button.
 *
 * @param string $url The URL to use.
 * @param string $download_attribute Additional attributes.
 * @param string $rel_attribute The value for the rel attribute.
 *
 * @package download-list-block-with-icons
 */

?>
<a href="<?php echo esc_url( $url ); ?>" class="download-button button button-secondary"<?php echo esc_attr( $download_attribute ) . ( ! empty( $rel_attribute ) ? ' rel="' . esc_attr( $rel_attribute ) . '"' : '' ); ?>><?php echo esc_html__( 'Download', 'download-list-block-with-icons' ); ?></a>
