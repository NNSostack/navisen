<?php 
/*
Plugin Name: Media Embeds
Description: Adds a post type to embed foreign code (html, javascript, ...) in an iframe  
Version: 0.9
*/
if(!defined('ABSPATH')) die('.');

class MediaEmbedder {
	
	static function init(){
		MediaEmbedder::registerPostTypes();
//		add_filter( "manage_vam_activity_posts_columns", array('VoiceAndMatter','change_activity_columns'));
//		add_filter( "manage_edit-vam_activity_sortable_columns", array('VoiceAndMatter','sortable_activity_columns') );
//		add_action( "manage_posts_custom_column", array('VoiceAndMatter','custom_columns'), 10, 2 );
//		add_action( 'load-edit.php', array('VoiceAndMatter','edit_load'));
		add_filter('query_vars', array('MediaEmbedder', 'add_query_vars'));
		add_filter('mediaembed_template', array('MediaEmbedder', 'post_template'));
		add_filter('single_template', array('MediaEmbedder', 'post_template'));
//		add_filter( 'excerpt_save_pre', array('MediaEmbedder', 'save_excerpt'), 1, 1 );
		add_filter( 'excerpt_edit_pre', array('MediaEmbedder', 'edit_excerpt'), 10, 2 );
		
		add_action( 'wp_enqueue_scripts', array('MediaEmbedder', 'enqueue_scripts' ));
		add_shortcode('me', array('MediaEmbedder', 'shortcode'));
		
		add_action('add_meta_boxes_mediaembed', array('MediaEmbedder', 'add_meta_boxes'));
		add_filter('wp_insert_post_data', array('MediaEmbedder', 'save_post'),99,2);
		add_action('save_post_mediaembed', array('MediaEmbedder', 'save_postdata'));
		$me_server_url = get_option('ruc_me_server_url');
//		error_log("ruc_me_server: $me_server_url");
		if(empty($me_server_url)){
			$siteurl = get_option('siteurl');
			switch ($siteurl){
				case 'http://navisen.cbit-test.ruc.dk':
					update_option('ruc_me_server_url','http://media.navisen.cbit-test.ruc.dk');
					break;
				case 'https://navisen.dk':
					update_option('ruc_me_server_url','https://navisen.ruc.dk');
					break;
				default:
					error_log("Obs! Media Embeds server unknown for: $siteurl");
			}
		}
	}

	static function enqueue_scripts() {
		wp_enqueue_script( 'mediaembeds', plugin_dir_url(__FILE__) . '/embed.js', array('jquery'));
	}

	static function shortcode($atts){
//		error_log('Doing ME_Shortcode'); 
		$fixedatts = shortcode_atts(array('id' => ''), $atts);
		if(!isset($fixedatts['id'])) return "<!-- needs id: [me id=\".....\"] -->";
		$id = $fixedatts['id'];
		$mepost = get_post($id);
		if(empty($mepost)) return "<!-- no post with id: $id -->";
		if(empty($mepost->post_name) || $mepost->post_type !== 'mediaembed') return "<!-- no medieembed post: $mepost->post_type -->";
		$me_server_url = get_option('ruc_me_server_url');
		$siteurl =  get_option('siteurl');
//		error_log(print_r($mepost,true));
		$iframe = <<< IFRAME
<iframe id='meid_$id' name='meid_$id' 
	src='$me_server_url/mediaembeds/$mepost->post_name/iframe.html'
	class='embediframe'
	data-media_server_url='$me_server_url' 
	seamless
	style='width:100%;border:0;overflow: hidden; height:400px;'
	scrolling='no'
	allowfullscreen></iframe>
<div style='margin-top:8px'><a href="$siteurl/mediaembeds/$mepost->post_name/">Åbn i et nyt vindue</a></div>

IFRAME;
//		error_log($iframe); 
		return $iframe;
	}
	
	static function registerPostTypes(){
			$embed_labels = array(
				'name'               => 'Media Embeds',
				'singular_name'      => 'Media Embed',
				'menu_name'          => 'Media Embeds',
				'name_admin_bar'     => 'Media Embed',
				'add_new'            => 'Add New',
				'add_new_item'       => 'Add New Media Embed',
				'new_item'           => 'New Media Embed',
				'edit_item'          => 'Edit Media Embed',
				'view_item'          => 'View Media Embed',
				'all_items'          => 'All Media Embeds',
				'search_items'       => 'Search Media Embeds',
				'parent_item_colon'  => 'Parent Media Embeds:',
				'not_found'          => 'No Media Embeds found.',
				'not_found_in_trash' => 'No Media Embeds found in Trash.'
			);
			$embed_args = array(
				'labels' => $embed_labels,
				'public' => true,
				'menu_position' => 20,
				'hierarchical' => false,
				'exclude_from_search' => true,
				'supports' => array( 'title'),
//				'supports' => array( 'title', 'thumbnail', 'custom-fields'),
		//		'has_archive'=>false,
				'has_archive'=>true,
				'rewrite' => array('slug'=>'mediaembeds','with_front'=>false)
		);
		register_post_type('mediaembed', $embed_args);

	}

	static function add_query_vars( $qvars ){
		$qvars[] = 'view';
		return $qvars;
	}
	

	static function post_template($single) {
		global $wp_query, $post;
		/* Checks for single template by post type */
		if ($post->post_type == "mediaembed"){
			$tpath = dirname(__FILE__). '/template.php';
			if($tpath)
				return $tpath;
		}
		return $single;
	}

	static function save_excerpt($excerpt){
		return htmlentities($excerpt);
	}

	static function edit_excerpt($excerpt, $post_id){
//		return $excerpt;
		return html_entity_decode($excerpt);
	}

	static function save_post( $data, $postarr ) {
//		error_log(print_r($data,true),3,"/var/log/php/e.log");
		if($data['post_type'] == 'mediaembed'){
//			error_log(print_r($postarr,true),3,"/var/log/php/e.log");
//			error_log(print_r($data,true),3,"/var/log/php/e.log");
			$data['post_status'] = isset($data['post_status']) && $data['post_status'] != 'pending' ? $data['post_status'] : 'publish';
			return $data;
		}
		return $data;
	}

	static function save_postdata( $post_id ) {
//		global $post;
		// verify this came from the our screen and with proper authorization,
		// because save_post can be triggered at other times
	
		if ( !wp_verify_nonce( $_POST['mediaembed_custom_box'], plugin_basename(__FILE__) ) )
			return;
	
		// verify if this is an auto save routine.
		// If it is our form has not been submitted, so we dont want to do anything
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )
			return;
		// OK, we're authenticated: we need to find and save the data
		$embedcode = htmlentities($_POST['embed_code']);
		error_log("new code for $post_id : $embedcode");
		update_post_meta($post_id, 'ruc_embed_code', $embedcode);
		return;
	}

	static function add_meta_boxes(){
		add_meta_box('mediaembed_meta', __( 'Embed code'),array('MediaEmbedder', 'mediaembed_custom_box'),'mediaembed', 'normal');
	}

	static function mediaembed_custom_box() {
		global $post;
		$embedcode = get_post_meta($post->ID, 'ruc_embed_code', true);
		// Use nonce for verification
		wp_nonce_field( plugin_basename(__FILE__), 'mediaembed_custom_box' );
		echo "<textarea style='width:100%;height:280px' name='embed_code'>$embedcode</textarea><br />";
		if(!empty($embedcode)) echo "Insert following shortcode into your article to embed the above code: <input type='text'  onClick='this.select();' readonly value='[me id=\"$post->ID\"]' />'";
/*
		$mypages = get_posts(array('post_type'=>'ok_workshop'));
		echo '<select name="showtime_workshop">';
		foreach($mypages as $page){
			echo '<option value="'.$page->ID.'"';
			if ($page->ID == $post->post_parent) echo ' selected';
			echo '>'.$page->post_title.'</option>';
		}
		echo '</select>';
*/
	}

	
}
add_action('init', array('MediaEmbedder', 'init'));


/*

// add the tab
add_filter('media_upload_tabs', 'my_upload_tab');
function my_upload_tab($tabs) {
	$tabs['mytabname'] = "My Tab Name";
	return $tabs;
}

// call the new tab with wp_iframe
add_action('media_upload_mytabname', 'add_my_new_form');
function add_my_new_form() {
	wp_iframe( 'my_new_form' );
}
add_action( 'admin_enqueue_scripts', 'wp_enqueue_media' );
// the tab content
function my_new_form() {
	echo <<<MEDIA
<p>Example HTML content goes here.</p>
<div class="uploader">
	<input id="_unique_name" name="settings[_unique_name]" type="text" />
	<input id="_unique_name_button" class="button" name="_unique_name_button" type="text" value="Upload" />
</div>
	
<script>
	console.log('media uploader...');
	console.log(wp);
	jQuery(document).ready(function($){

		var _custom_media = true,
		_orig_send_attachment = wp.media.editor.send.attachment;
	
		$('.stag-metabox-table .button').click(function(e) {
			var send_attachment_bkp = wp.media.editor.send.attachment;
			var button = $(this);
			var id = button.attr('id').replace('_button', '');
			_custom_media = true;
			wp.media.editor.send.attachment = function(props, attachment){
				if ( _custom_media ) {
					$("#"+id).val(attachment.url);
				} else {
					return _orig_send_attachment.apply( this, [props, attachment] );
				};
			}
	
			wp.media.editor.open(button);
			return false;
		});
	
		$('.add_media').on('click', function(){
			_custom_media = false;
		});

	});
</script>
MEDIA;
	
}
*/
