<?php
/**
 * Template for starting list.
 *
 * @param string $wrapper_attributes List of attributes.
 * @param string $iconset Style-classes for used iconset.
 * @param string $list_class Additional classes for the list (e.g. for frames of icons and preview images).
 * @param string $list_style Inline style for the list (CSS variables for frames of icons and preview images).
 *
 * @version: 5.0.0
 * @package download-list-block-with-icons
 */

// prevent direct access.
defined( 'ABSPATH' ) || exit;

?>
<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes() returns escaped attributes. ?>><ul class="downloadlist-list wp-block-downloadlist-list <?php echo esc_attr( $iconset . ( $list_class ?? '' ) ); ?>"<?php echo ! empty( $list_style ) ? ' style="' . esc_attr( $list_style ) . '"' : ''; ?>>
