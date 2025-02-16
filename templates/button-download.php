<?php
/**
 * Template for download button.
 *
 * @param string $url The URL to use.
 * @param string $download_button_attribute The attribute for force download in browser.
 * @param string $rel_attribute The value for the rel attribute.
 * @param string $download_target_attribute The value for the target attribute.
 *
 * @version: 3.7.0
 * @package download-list-block-with-icons
 */

?>
<a href="<?php echo esc_url( $url ); ?>" class="download-button button button-secondary" target="<?php echo esc_attr( $download_target_attribute ); ?>" <?php echo esc_attr( $download_button_attribute ) . ( ! empty( $rel_attribute ) ? ' rel="' . esc_attr( $rel_attribute ) . '"' : '' ); ?>><?php echo esc_html__( 'Download', 'download-list-block-with-icons' ); ?></a>
