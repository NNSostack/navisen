<?php
/*
// DIRECTORIES
	define('OF_FILEPATH', TEMPLATEPATH);
	define('OF_DIRECTORY', get_template_directory_uri());

	define('GABFIRE_INC_PATH', TEMPLATEPATH . '/inc');
	define('GABFIRE_FRAMEWORK_PATH', TEMPLATEPATH . '/framework');
	define('GABFIRE_INC_DIR', get_template_directory_uri() . '/inc');
	define('GABFIRE_FUNCTIONS_PATH', TEMPLATEPATH . '/inc/functions');
	define('GABFIRE_JS_DIR', get_template_directory_uri() . '/inc/js');
	
	// OPTION PANEL	
	if ( !function_exists( 'optionsframework_init' ) ) {
		define( 'OPTIONS_FRAMEWORK_DIRECTORY', get_template_directory_uri() . '/framework/admin/' );
		require_once GABFIRE_FRAMEWORK_PATH . '/admin/options-framework.php';
	}
*/
/*	
	// This builds dashboard menu 
	require_once (GABFIRE_FRAMEWORK_PATH . '/admin/admin-menu.php');

	require_once (GABFIRE_INC_PATH . '/theme-js.php'); // Load theme Javascripts
	require_once (GABFIRE_INC_PATH . '/theme-comments.php');	// Load custom comments template
	require_once (GABFIRE_INC_PATH . '/widgetize-theme.php'); // Register sidebars
	require_once (GABFIRE_INC_PATH . '/I18n-functions.php'); // localization support
	require_once (GABFIRE_INC_PATH . '/post-thumbnails.php'); // Load theme thumbnails
	require_once (GABFIRE_INC_PATH . '/script-init.php'); // Javascript init
	require_once (GABFIRE_INC_PATH . '/theme-cpt.php'); // Custom post type and taxonomies (CPT is used with CMS themes only)
	require_once (GABFIRE_INC_PATH . '/custom-fields.php'); // Breadcrumb function
	
	// FRAMEWORK FILES
	require_once (GABFIRE_FRAMEWORK_PATH . '/functions/breadcrumb.php'); // Breadcrumb function
	require_once (GABFIRE_FRAMEWORK_PATH . '/functions/misc-functions.php'); // Misc theme functions
	require_once (GABFIRE_FRAMEWORK_PATH . '/functions/dashboard-widget.php'); // Gabfire Themes RSS widget for WP Dashboard
	require_once (GABFIRE_FRAMEWORK_PATH . '/functions/gabfire-media.php'); // Gabfire Media Module
	require_once (GABFIRE_FRAMEWORK_PATH . '/functions/gabfire-widgets.php'); // Custom gabfire widgets
*/
	/* Add custom navigation support
	 * For header navigations check the core files at
	 * inc/functions/misc-functions file
	 */
/* removed as suggested in http://www.gabfirethemes.com/codex/index.php?title=How_to_Create_child_themes
	register_nav_menus(array(
		'masthead' => 'Masthead',
		'primary-navigation' => 'Primary',
		'secondary-navigation' => 'Secondary',
		'footer-nav1' => 'Footer Navigation - #1',
		'footer-nav2' => 'Footer Navigation - #2'
	));
*/
ini_set('error_log', '/var/log/php/navisen_error.txt');

function navisen_content($content){
	$post = get_post();

	$author = get_the_author_meta( 'user_firstname' ).' '.get_the_author_meta( 'user_lastname' );
	$email = str_replace('@','_at_',get_the_author_meta( 'user_email' ));
	
	$date = date('d/m Y, H:i',strtotime($post->post_date));
	
	$byline = '';// "<p class='navisen_byline'>Af <a class='author' href='mailto:$email'>$author</a></p>"; // !!! frankw test
	if(!empty($post->post_excerpt)) {
		$excerpt = ''; //get_the_excerpt();
		$c = preg_split('/^(<p><img class="drupim".*"\/>)/',$content,-1,PREG_SPLIT_DELIM_CAPTURE);
		if(count($c) == 3 && is_string($excerpt)) return "<p class='navisen_underrubrik'>$excerpt</p>".$c[1].$byline.$c[2];

		$c = preg_split('/^(<p><div id.*<\/div>)/',$content,-1,PREG_SPLIT_DELIM_CAPTURE);
		if(count($c) == 3 && is_string($excerpt)) return "<p class='navisen_underrubrik'>$excerpt</p><p>".$c[1].$byline.$c[2];

		$c = preg_split('/^(<div id.*<\/div>)/',$content,-1,PREG_SPLIT_DELIM_CAPTURE);
		if(count($c) == 3 && is_string($excerpt)) return "<p class='navisen_underrubrik'>$excerpt</p><p>".$c[1].$byline.$c[2];

		$c = preg_split('/^(<p> *<a .*<img .* \/><\/a><\/p>)/',$content,-1,PREG_SPLIT_DELIM_CAPTURE);
		if(count($c) == 3 && is_string($excerpt)) return "<p class='navisen_underrubrik'>$excerpt</p>".$c[1].$byline.$c[2];

		$c = preg_split('/^(<p> *<img .* \/><\/p>)/',$content,-1,PREG_SPLIT_DELIM_CAPTURE);
		if(count($c) == 3 && is_string($excerpt)) return "<p class='navisen_underrubrik'>$excerpt</p>".$c[1].$byline.$c[2];
		
		return "$byline<p class='navisen_underrubrik'>$excerpt</p>$content";
	} else return $byline.$content;
}
add_filter( 'the_content', 'navisen_content', 99 );

function postinfo($postId){
	global $post;
	if(current_user_can('edit_others_posts')) {
		$img_info = get_stylesheet_directory_uri() . '/info.png';
		return " <img src='$img_info' title='show postinfo' onclick=\"showInfo($post->ID);\" class='postinfo thickbox' />";
//		return " <span title='show postinfo' onclick=\"showInfo($post->ID);\"><img src='$img_info' width='12px' height='12px'/></span>";
		
	}
	return '';
}

function navisen_showinfo(){
	$nonce = $_POST['updateListNonce'];
	// check to see if the submitted nonce matches with the
	// generated nonce we created earlier
	if ( ! wp_verify_nonce( $nonce, 'navisen_updatelist_nonce' ) )
		die ( 'Busted!');

	header( "Content-Type: text/html" );
	
	// ignore the request if the current user doesn't have
	// sufficient permissions
	if ( current_user_can( 'edit_others_posts' ) ) {
		// get the submitted parameters
		//print_r($_POST);
		$postId = isset($_POST['postId']) ? $_POST['postId'] : '';
		echo "<div> Add post $postId to 
<span onclick='add2list($postId,\"navisen_featured\")'>featured</span>/
<span onclick='add2list($postId,\"navisen_left_list\")'>left</span>/
<span onclick='add2list($postId,\"navisen_mid_list\")'>middle</span></div>\n";
	}
	if(class_exists('Navisen_Log')) {
		$count = Navisen_Log::getCount($postId, 3600);
		echo "<div>Views last hour: $count</div>";
		$count = Navisen_Log::getCount($postId, 86400);
		echo "<div>Views last 24 hours: $count</div>";
		$count = Navisen_Log::getCount($postId, 31536000);
		echo "<div>Total views: $count</div>";
	}
	exit;
}

function navisen_toolbar_lists( $wp_admin_bar ) {
	$hf = '';

	$args = array(
		'id' => 'navisen_lists',
		'title' => 'Post lists',
		'meta' => array('class' => 'navisen_toolbar-lists')
		);
	$wp_admin_bar->add_node($args);
	if(current_user_can('edit_others_posts')){	
		$args = array(
			'id' => 'navisen_featured',
			'title' => 'Featured list',
			'href' => '#',
			'meta' => array('class' => 'navisen_toolbar-list' ,'onclick' => 'showList("navisen_featured")'),
			'parent' => 'navisen_lists'
			);
		$wp_admin_bar->add_node($args);
		
		$args = array(
			'id' => 'navisen_left_list',
			'title' => 'Left list',
			'href' => '#',
			'meta' => array('class' => 'navisen_toolbar-list' ,'onclick' => 'showList("navisen_left_list")'),
			'parent' => 'navisen_lists'
			);
		$wp_admin_bar->add_node($args);
		
		$args = array(
			'id' => 'navisen_mid_list',
			'title' => 'Middle list',
			'href' => '#',
			'meta' => array('class' => 'navisen_toolbar-list' ,'onclick' => 'showList("navisen_mid_list");'),
			'parent' => 'navisen_lists'
			);
		$wp_admin_bar->add_node($args);
	}

	$args = array(
		'id' => 'navisen_top24',
		'title' => 'Top last 24h',
		'href' => '#',
		'meta' => array('class' => 'navisen_toolbar-list' ,'onclick' => 'showList("navisen_top24");'),
		'parent' => 'navisen_lists'
		);
	$wp_admin_bar->add_node($args);

	$args = array(
		'id' => 'navisen_top1h',
		'title' => 'Top last hour',
		'href' => '#',
		'meta' => array('class' => 'navisen_toolbar-list' ,'onclick' => 'showList("navisen_top1h");'),
		'parent' => 'navisen_lists'
		);
	$wp_admin_bar->add_node($args);

	$args = array(
		'id' => 'navisen_top_lastyear',
		'title' => 'Top last year',
		'href' => '#',
		'meta' => array('class' => 'navisen_toolbar-list' ,'onclick' => 'showList("navisen_top_lastyear");'),
		'parent' => 'navisen_lists'
		);
	$wp_admin_bar->add_node($args);
}


add_action( 'admin_bar_menu', 'navisen_toolbar_lists', 999 );



function max_in_list($listId){
	switch ($listId){
		case 'navisen_featured':
			return of_get_option('of_an_nrfea' , 5);
		case 'navisen_left_list':
			return of_get_option('of_an_nr1' , 2);
		case 'navisen_mid_list':
			return of_get_option('of_an_nr2' , 4);
		default:
			return -1;
	}
}

function navisen_show_list($listId,$maxposts = null, $edit = false){
	global $post;
	$maxposts = is_null($maxposts) ? max_in_list($listId) : $maxposts; 
	$args = array(
			'posts_per_page' => $maxposts,
			'post__in' => explode(',',get_option($listId,'')),
			'orderby' => 'post__in'
		);
	
	$img_remove = get_stylesheet_directory_uri() . '/remove.png';
//	$img_moveup = get_stylesheet_directory_uri() . '/moveup.png';
//	$img_movedown = get_stylesheet_directory_uri() . '/movedown.png';
	
	$navisen_query = new WP_Query();$navisen_query->query($args);
	if($edit) {
		echo "<b onclick=\"reorderList(0,'$listId')\">Save</b>";
		echo '<ul id="postlist">';
		$el = 'li';
	} else {
		$el = 'div';
	}
	while ($navisen_query->have_posts()) : $navisen_query->the_post();
		$postId = $post->ID; 
		$remove = $edit ? " [<img src='$img_remove' style='width:10px;height:10px' title='remove' onclick=\"removeFromList($postId,'$listId');\" />]" : '';
		$views = class_exists('Navisen_Log') ? 'Views last hour: '.Navisen_Log::getCount($postId, 3600).' - last 24 hours: '.Navisen_Log::getCount($postId, 86400). ' - total: ' . Navisen_Log::getCount($postId,31536000) : ''; 
		if($count == max_in_list($listId)) echo '<hr />';
		$lp = $count == $maxposts ? ' lastpost' : ''; 
		echo "<$el postid='$postId' class='featuredpost $lp'>";

		gabfire_media(array(
			'name' => 'an-belowfea',
			'imgtag' => 1,
			'link' => 1,
			'enable_video' => 0,
			'catch_image' => of_get_option('of_catch_img', 0),
			'enable_thumb' => 1,
			'resize_type' => 'c',
			'media_width' => 110, 
			'media_height' => 77, 
			'thumb_align' => 'alignleft',
			'enable_default' => 0
		));
		?>

		<h2 class="posttitle">
			<?php  echo $remove; ?>
			<a href="<?php the_permalink() ?>" rel="bookmark" title="<?php printf( esc_attr__( 'Permalink to %s', 'advanced' ), the_title_attribute( 'echo=0' ) ); ?>" ><?php the_title(); ?></a>
			
		</h2>							
		<?php  echo $views; ?><br />
		<p><?php echo string_limit_words(get_the_excerpt(), 28); ?>&hellip;</p>
		
		<?php 
		gabfire_postmeta( true, true, false ); 

		echo "</$el><!-- .featuredpost -->";

	$count++; 
	endwhile; 
	if($edit) echo '</ul>';
	wp_reset_query();
}


// 
wp_enqueue_script('jquery-ui-sortable', false, array( 'jquery' ));
wp_enqueue_script( 'navisen_updatelist_request', get_stylesheet_directory_uri() . '/navisen.js', array( 'jquery' ) );
//wp_enqueue_script( 'google_analytics', get_stylesheet_directory_uri() . '/google.js' );
wp_localize_script( 'navisen_updatelist_request', 'Navisen', 
		array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'updateListNonce' => wp_create_nonce( 'navisen_updatelist_nonce' ) ) 
	);
// http://minecookies.org/cookiesamtykke/ 
wp_enqueue_script( 'navisen_cookie', get_stylesheet_directory_uri() . '/cookiesamtykke-ver2.js', array( 'jquery' ) );


add_action( 'wp_ajax_nopriv_navisen_updatelist', 'wontdoit' );
add_action( 'wp_ajax_navisen_updatelist', 'navisen_updatelist' );

add_action( 'wp_ajax_nopriv_navisen_showinfo', 'wontdoit' );
add_action( 'wp_ajax_navisen_showinfo', 'navisen_showinfo' );

function wontdoit(){
	error_log("Won't do it!");
	exit();
}


function navisen_updatelist() {
	error_log('callback: navisen_updatelist');
	$nonce = $_POST['updateListNonce'];

	// check to see if the submitted nonce matches with the
	// generated nonce we created earlier
	if ( ! wp_verify_nonce( $nonce, 'navisen_updatelist_nonce' ) )
		die ( 'Busted!');

		// ignore the request if the current user doesn't have
	// sufficient permissions

	// get the submitted parameters
	$postId = isset($_POST['postId']) ? $_POST['postId'] : '';
	$listId = isset($_POST['listId']) ? $_POST['listId'] : '';
	$edit = false;
	if(current_user_can( 'edit_others_posts' ) && in_array($listId,array('navisen_featured','navisen_left_list','navisen_mid_list'))) {  //error_log("Cannot add postId: $postId, unknown listId: $listId");
		$maxposts = max_in_list($listId) + 10;
		$edit = true;
		$posts = explode(',',get_option($listId));
//		if(!in_array($postId,$posts)) array_unshift($posts,$postId);
//		while(count($posts) > $maxposts + 2) array_pop($posts);
//		$posts = implode(',',$posts);
		$cnt = 0;
		switch(@$_POST['update']){
			case 'add':
				$postlist = $postId;
				foreach ($posts as $post){
					if($post && $post != $postId) $postlist .= ','.$post;
					if(++$cnt > $maxposts) break; 
				}
				update_option($listId,$postlist);
				break;
			case 'remove':
				$postlist = '';
				foreach ($posts as $post){
					if($post && $post != $postId) $postlist .= $post.',';
					if(++$cnt > $maxposts) break;
				}
				update_option($listId,$postlist);
				break;
			case 'reorder':
				$postlist = isset($_POST['list']) ? $_POST['list'] : '';
				update_option($listId,$postlist);
				break;
		}
	} elseif($listId == 'navisen_top24' && class_exists('Navisen_Log')){
		$maxposts = 10;
		$top24 = Navisen_Log::getTop(86400);
		update_option($listId,implode(',',array_keys($top24)));
	} elseif($listId == 'navisen_top1h' && class_exists('Navisen_Log')){
		$maxposts = 25;
		$top1h = Navisen_Log::getTop(3600, $maxposts);
		update_option($listId,implode(',',array_keys($top1h)));
	} elseif($listId == 'navisen_top_lastyear' && class_exists('Navisen_Log')){
		$maxposts = 25;
		$top = Navisen_Log::getTop(31536000, $maxposts); /// !!!
		update_option($listId,implode(',',array_keys($top)));
	} else exit;
	header( "Content-Type: text/html" );
	navisen_show_list($listId, $maxposts, $edit);

	// IMPORTANT: don't forget to "exit"
	exit;
}

if ( function_exists('memory_get_usage') ) {
	$usage = memory_get_usage();
	if($usage > 26214400) {
		$memory = number_format($usage / 1048576, 1);
		$now = date('Y-m-d_H-i-s ');
		error_log("$now: {$memory}MB {$_SERVER['REQUEST_URI']}\n");
	}
} else {
	error_log('No memory_get_usage');
}

/*
 * Related Posts
*/
class navisen_relatedposts extends WP_Widget {
	function navisen_relatedposts() {
		$widget_ops = array( 'classname' => 'gab_relatedposts', 'description' => 'Display related post with thumbnails' );
		$control_ops = array( 'width' => 250, 'id_base' => 'navisen_relatedposts' );
		$this->WP_Widget( 'navisen_relatedposts', 'Navisen Widget : Related Posts Thumbs', $widget_ops, $control_ops);
	}

	function widget($args, $instance) {
		extract( $args );
		$title    = $instance['title'];
		$postnr    = $instance['postnr'];

		echo $before_widget;
		global $post,$page;
		$tags = wp_get_post_tags($post->ID);
		if ($tags) {
			$tag_ids = array();
			foreach($tags as $individual_tag) $tag_ids[] = $individual_tag->term_id;
			$args=array(
					'tag__in' => $tag_ids,
					'post__not_in' => array($post->ID),
					'posts_per_page'=> $postnr, // Enter the Number of posts that will be shown.
					'caller_get_posts'=> 1,
					'orderby'=>'rand' // Randomize the posts
			);
			$my_query = new wp_query( $args );

			if( $my_query->have_posts() ) {
				if ( $title ) {
					echo $before_title . $title . $after_title;
				}
					
				if ($postnr == 2) {
					$width = '48%';
				} elseif ($postnr == 3) {
					$width = '31%';
				} elseif ($postnr == 4) {
					$width = '23%';
				} elseif ($postnr == 5) {
					$width = '18%';
				}
					
				while( $my_query->have_posts() ) {
			$my_query->the_post(); ?>
			<ul>
<!--				<ul class="gab_relateditem" style="width:<?php echo $width; ?>">
 
				<a href="<?php the_permalink()?>" rel="bookmark" class="related_postthumb" title="<?php the_title(); ?>">
						<?php the_post_thumbnail('thumbnail'); ?>
					</a>
-->
					<li><a href="<?php the_permalink()?>" rel="bookmark" class="related_posttitle" title="<?php the_title(); ?>">
						<?php the_title(); ?>
					</a></li>
				 </ul>
			<?php }
			echo '<div class="clear"></div>';
			} }
			wp_reset_query();
		echo $after_widget;
		}
		
	function update($new_instance, $old_instance) {  
		$instance['title']  = $new_instance['title'];	
		$instance['postnr']  = (int)$new_instance['postnr'];	
		return $new_instance;
	}	  

	function form($instance) {
		$defaults	= array( 'title' => 'Related Posts', 'postnr' => '5');
		$instance = wp_parse_args( (array) $instance, $defaults ); 
	?>

	<p>
		<label for="<?php echo $this->get_field_id('title'); ?>">Title</label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($instance['title']); ?>" />
	</p>
	
		<p>
			<label for="<?php echo $this->get_field_name( 'postnr' ); ?>">Number of Posts to display?</label>
			<select id="<?php echo $this->get_field_id( 'postnr' ); ?>" name="<?php echo $this->get_field_name( 'postnr' ); ?>">			
			<?php
				for ( $i = 2; $i <= 5; ++$i )
				echo "<option value='$i' " . selected( $instance['postnr'], $i, false ) . ">$i</option>";
			?>
			</select>
		</p>			
<?php
	}
}

function navisen_register_widgets(){
    register_widget('navisen_relatedposts');
}
add_action( 'widgets_init', 'navisen_register_widgets' );

//$contributor = get_role('contributor'); // author
//$contributor->add_cap('read_private_pages');
//$contributor->add_cap('read_private_posts');
//$contributor->add_cap('edit_published_posts');
//error_log(print_r($contributor,true));

//error_log(dirname(__FILE__)."/framework/gabfire-media.php\n",3,'/var/log/php/navisen.txt');
require_once(dirname(__FILE__).'/framework/gabfire-media.php');


/* ********************
 * Limit post excerpts. Within theme files used as
 * print string_limit_words(get_the_excerpt(), 16);
 ******************************************************************** */
function navisen_string_limit_words($word_limit = NULL) {
	global $post, $page;
//	$excerpt = wp_strip_all_tags(get_the_excerpt());
	$excerpt = wp_strip_all_tags($post->post_excerpt);
	if(empty($word_limit)) {
		return $excerpt;
	}
	
	$words = explode(' ', $excerpt, ($word_limit + 1));
	if(count($words) > $word_limit)
		array_pop($words);
	return implode(' ', $words);
}

/* ******************** framework/functions/msic-functions.php line 170+
 * The post meta display below post excerpts on front page
 * default usage gabfire_postmeta(date, comment, permalink, edit-post) !!! NO !!!
 ******************************************************************** */
function navisen_postmeta($p_tag = true,$bydate = true,$comment = true,$permalink = true){
	global $post;
	$author = get_the_author_meta( 'user_firstname', $post->post_author ).' '.get_the_author_meta( 'user_lastname', $post->post_author );
	$email = str_replace('@','_at_',get_the_author_meta( 'user_email', $post->post_author ));
	
//	$date = date('d/m Y, H:i',strtotime($post->post_date));
	$date = $bydate ? date_i18n( get_option( 'date_format' ), strtotime( $post->post_date ) ) : '';
	
	$byline = "<p class='navisen_byline postmeta'>Af <a class='author' href='mailto:$email'>$author</a> <span class='date'>$date</span></p>";
	echo $byline;
/*
	echo (true === $p_tag) ? '<p class="postmeta">' : "";
	echo (true === $bydate) ? gabfire_bydate() : "";
	echo (true === $comment) ? gabfire_postcomment() : "";
	echo (true === $permalink) ? gabfire_permalink() : "";
	echo (true === $p_tag) ? '</p>' : "";
*/
}


function preprocess_comment_handler( $commentdata ) {
	$comment_post = get_post($commentdata['comment_post_ID']);
	if( $comment_post->post_type == 'post' ) {
	        return $commentdata;
	}
	wp_die('No...');
}
add_filter( 'preprocess_comment' , 'preprocess_comment_handler' );


function navisen_curl_before_request($curlhandle){
        session_write_close();
}
add_action( 'requests-curl.before_request','navisen_curl_before_request', 9999 );