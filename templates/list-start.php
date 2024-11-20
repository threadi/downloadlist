<?php
/**
 * Template for starting list.
 *
 * @param string $wrapper_attributes List of attributes.
 * @param string $iconset Style-classes for used iconset.
 *
 * @package download-list-block-with-icons
 */

?>
<div <?php echo wp_kses_data( $wrapper_attributes ); ?>><ul class="downloadlist-list wp-block-downloadlist-list <?php echo esc_attr( $iconset ); ?>">
