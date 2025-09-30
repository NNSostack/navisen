<?php
$args = array(
  'post_parent' => $post->ID,
  'post_type' => 'attachment',
  'numberposts' => -1,
  'post_mime_type' => 'image',
  'post_status' => null,
  'order' => 'ASC', 
  'orderby' => 'menu_order'
);

$attachments = get_posts( $args );

    if ( $attachments ) {
        foreach ( $attachments as $attachment ) {
	 		$caption = "";
			if ($attachment->post_excerpt) {
				$caption = '<p>' . $attachment->post_excerpt . '</p>';
			}
			
           echo '<div class="bigpicture_item">';
			   echo wp_get_attachment_image( $attachment->ID, 'postthumbnail-big' );
			   echo $caption;
           echo '</div>';
          }
    }