<?php
/**
 * Template for starting list.
 *
 * @param string $wrapper_attributes List of attributes.
 * @param string $iconset Style-classes for used iconset.
 *
 * @version: 4.2.0
 * @package download-list-block-with-icons
 */

// prevent direct access.
defined( 'ABSPATH' ) || exit;

?>
<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes() returns escaped attributes. ?>><ul class="downloadlist-list wp-block-downloadlist-list <?php echo esc_attr( $iconset ); ?>">
