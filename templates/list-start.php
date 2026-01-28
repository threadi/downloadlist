<?php
/**
 * Template for starting list.
 *
 * @param string $wrapper_attributes List of attributes.
 * @param string $iconset Style-classes for used iconset.
 *
 * @version: 3.7.0
 * @package download-list-block-with-icons
 */

// prevent direct access.
defined( 'ABSPATH' ) || exit;

?>
<div <?php echo wp_kses_data( $wrapper_attributes ); ?>><ul class="downloadlist-list wp-block-downloadlist-list <?php echo esc_attr( $iconset ); ?>">
