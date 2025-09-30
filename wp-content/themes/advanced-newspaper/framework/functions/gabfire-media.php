<?php
if ( !function_exists( 'gabfire_media' ) ) {

	function gabfire_call_iframe ($parameters){
		global $gab_iframe;
		
		$videourl = wp_oembed_get($gab_iframe);
		preg_match('/src="([^"]+)"/', $videourl, $match);
		$videourl = $match[1];
		
		echo '
			<span class="cf_video '.$parameters['thumb_align'].'">		
				<iframe title="';the_title(''); echo '" src="'. esc_url($videourl) .'?wmode=opaque&amp;wmode=opaque&amp;showinfo=0&amp;autohide=1" width="'.$parameters['media_width'].'" height="'.$parameters['media_height'].'" allowfullscreen></iframe>
			</span>';
	}

	function gabfire_call_post_thumb ($parameters){ /* Get default Post Thumbnail of WordPress */ 
		global $post;
		$image_id = get_post_thumbnail_id(); 
		$image_url = wp_get_attachment_image_src($image_id,$parameters['name']);  
		$image_url = $image_url[0]; 
		
		$alt_text = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
		
		if( !empty( $alt_text )){
			$title = $alt_text;
		} else {
			$title = get_the_title();
		}
		
		if ($parameters['link'] == 1) {
			echo "<a href='". get_permalink() . "' rel='bookmark'>";	
		}
		
		if ($parameters['imgtag'] == 1) { 
			echo "<img src='";
		}
		
		echo $image_url;
		
		if ($parameters['imgtag'] == 1) {  
			echo "' class='".$parameters['thumb_align']."' alt='" . get_the_title() ."' title='" . $title ."' />";
		}		
		
		if ($parameters['link'] == 1) {
			echo "</a>"; 
		}	
	}

	/* Catch default thumbnail (image name is written into gabfire_media array.
	 * The image is located in template_url/images/thumbs dir 
	 */ 
	function gabfire_call_default_thumb ($parameters){ 

		global $post;
		
		if ($parameters['link'] == 1) {
			echo "<a href='". get_permalink() . "' rel='bookmark'>";	
		}

		if ($parameters['imgtag'] == 1) { 
			echo "<img src='";
		}
			echo esc_url(get_template_directory_uri()) . "/images/thumbs/".$parameters['default_name'];
		
		if ($parameters['imgtag'] == 1) {  
			echo "' class='".$parameters['thumb_align']."' alt='" . get_the_title() ."' title='" . get_the_title() ."' />";
		}		
		
		if ($parameters['link'] == 1) {
			echo "</a>"; 
		}		
	}

	function gabfire_media($parameters) 
	{
		if ( !post_password_required() ) {
			global $post, $video_mp4, $video_webm, $video_ogv, $gab_iframe;
			$gab_iframe = get_post_meta($post->ID, 'iframe', true);
			
			
			/* If we have installed and activated Gabfire Media Plugin */
			if ( function_exists( 'gabfire_mediaplugin_thumbnail' ) ) {
			
				gabfire_mediaplugin($parameters);
				
			} else {
			
				if ($gab_iframe != '' and $parameters['enable_video'] == 1)	{ 
					gabfire_call_iframe ($parameters);
				}	
				elseif(has_post_thumbnail() and ($parameters['enable_thumb'] == 1))	{
					gabfire_call_post_thumb ($parameters);
				}
				elseif($parameters['enable_default'] == 1) {
					gabfire_call_default_thumb ($parameters);
				}
			}
		}
	}
}