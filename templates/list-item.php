<?php

/**
 * Template for list item
 */

?>
<li class="file_<?php echo esc_attr($type); ?> file_<?php echo esc_attr($subtype); ?>"><a href="<?php echo esc_url($url); ?>"<?php echo esc_attr($downloadAttribute); ?>><?php echo esc_html($attachment->post_title); ?></a><?php echo wp_kses_post($fileSize).wp_kses_post($description); ?></li>
