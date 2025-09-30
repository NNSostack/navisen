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

	echo '
	<div class="innerslider-wrapper">
		<div class="carousel-gallery-controls">
			<span class="carousel-gallery-prev pull-left"><i class="fa fa-angle-left"></i></span>
			<span class="carousel-gallery-next pull-right"><i class="fa fa-angle-right"></i></span>
		</div>
		
		<div class="carousel-gallery">';
		 
			foreach ( $attachments as $attachment ) {
				$caption = "";
				if ($attachment->post_excerpt) {
					$caption = '<div class="postcaption"><p class="innerslide_text">' . $attachment->post_excerpt . '</p></div>';
				}			
				
			   echo '<div class="bigpicture_item">';
				   echo wp_get_attachment_image( $attachment->ID, 'postthumbnail-big' );
				   echo $caption;
			   echo '</div>';
			}
		
		echo '
		</div>
	</div>';  
}
	
	