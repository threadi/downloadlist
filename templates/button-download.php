<?php
/**
 * Template for download button.
 *
 * @param string $url The URL to use.
 * @param string $download_button_attribute The attribute for force download in browser.
 * @param string $rel_attribute The value for the rel attribute.
 * @param string $download_target_attribute The value for the target attribute.
 *
 * @version: 5.0.0
 * @package download-list-block-with-icons
 */

// prevent direct access.
defined( 'ABSPATH' ) || exit;

?>
<a href="<?php echo esc_url( $url ); ?>" class="download-button button button-secondary"<?php echo ! empty( $download_target_attribute ) ? ' target="' . esc_attr( $download_target_attribute ) . '"' : ''; ?><?php echo $download_button_attribute; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- the complete attribute is escaped in Init::get_download_attribute(). ?><?php echo ! empty( $rel_attribute ) ? ' rel="' . esc_attr( $rel_attribute ) . '"' : ''; ?>><?php echo esc_html__( 'Download', 'download-list-block-with-icons' ); ?></a>
