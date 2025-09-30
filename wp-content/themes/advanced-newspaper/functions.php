<?php
/*
 * Loads the Options Panel
 *
 * If you're loading from a child theme use stylesheet_directory
 * instead of template_directory
 */		
	define( 'OPTIONS_FRAMEWORK_DIRECTORY', get_template_directory_uri() . '/framework/admin/' );
	require_once dirname( __FILE__ ) . '/framework/admin/options-framework.php';

	// Loads options.php from child or parent theme
	$optionsfile = locate_template( 'options.php' );
	load_template( $optionsfile );

	// FRAMEWORK FILES
	get_template_part( 'framework/functions/misc-functions');
	get_template_part( 'framework/functions/breadcrumb');
	get_template_part( 'framework/functions/gabfire-media');
	get_template_part( 'framework/functions/optionspanel-mods');
	
	// HOOKS
	get_template_part( 'framework/functions/hook-functions');
		
		add_filter( 'the_content', 'gabfire_postpagination', 	5 );
		add_filter( 'the_content', 'gabfire_postcredit', 		6 );
		add_filter( 'the_content', 'gabfire_tags', 				7 );
		add_filter( 'the_content', 'gabfire_singlepostmeta', 	8 );
		
		add_action( 'gabfire_before_singlepost_content', 'gabfire_innerslide_wrapper', 	6 );
		add_action( 'gabfire_before_singlepost_content', 'gabfire_singlepostmedia', 	7 );
		
		add_action( 'gabfire_after_singlepost_query', 'gabfire_single_post_widget_zones', 10 );
		add_action( 'gabfire_after_singlepost_query', 'gabfire_archivepage_display_loop', 15 );
	
	
	/* If plugin is activated, include woocommerce functions */
	if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
		get_template_part( 'woocommerce/gabfire-woocommerce');
	}	
	
if ( ! function_exists( 'gabfire_theme_setup' ) ) {
	/**
	 * Setup Theme.
	 *
	 * Set up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which
	 * runs before the init hook. The init hook is too late for some features, such
	 * as indicating support post thumbnails.
	 *
	 */
	function gabfire_theme_setup() {

		/*
		 * Make theme ready for translation
		 * Translations can be added to the /inc/lang/ directory.
		 */
		load_theme_textdomain( 'gabfire', get_template_directory() . '/inc/lang' );

		// Add RSS feed links to <head> for posts and comments.
		add_theme_support( 'automatic-feed-links' );

		// Enable support for Post Thumbnails, and declare thumbnail sizes.
		add_theme_support( 'post-thumbnails' );
		add_image_size( 'featured', 697, 376, true ); // Featured Slider
		add_image_size( 'featured-thumbs', 123, 70, true ); // Featured Thumb
		add_image_size( 'below-featured', 119, 89, true ); // Below featured
		add_image_size( 'featured-right', 86, 54, true ); // Right hand of featured
		add_image_size( 'secondary-mid', 122, 74, true ); // Secondary content mid column
		add_image_size( 'secondary-right', 220, 173, true ); // Secondary content right column
		add_image_size( 'tabs', 254, 141, true ); // tabbed content
		add_image_size( 'secondarybottom-leftsmall', 124, 90, true ); // Secondary bottom left small thumb
		add_image_size( 'secondarybottom-leftbig', 186, 173, true ); // Secondary bottom left big thumb
		add_image_size( 'secondarybottom-right', 570, 324, true ); // Secondary bottom right column
		add_image_size( 'homeslider-subnews', 570, 324, true ); // home with slider, subnews thumbs
		add_image_size( 'featured-big', 570, 9999 ); // Home no slider, big featured thumbnail
		add_image_size( 'featured-small', 120, 74, true ); // Home no slider, small featured thumbnail
		add_image_size( 'homenoslider-subnewsbig', 360, 165, true ); // Home no slider, large subnews thumbnail
		add_image_size( 'homenoslider-subnewssmal', 80, 55, true ); // Home no slider, small subnews thumbnail
		add_image_size( 'postthumbnail', 701, 999999 ); // Default post category, and single post featured thumbnail
		add_image_size( 'postthumbnail-big', 1067, 99999 ); // No sideba post template, large thumbnail
		add_image_size( 'loop-2col', 570, 320, true ); // 2 column category template thumbnail
		add_image_size( 'loop-3col', 570, 320, true ); // 3 coulum category template thumbnail
		add_image_size( 'loop-4col', 570, 293, true ); // 4 column category template thumbnail
		add_image_size( 'loopmedia-big', 1047, 450, true ); // Media category template, large thumb
		add_image_size( 'loopmedia-small', 378, 237, true ); // Media category template, small thumb
		add_image_size( 'loop-default', 701, 360, true ); // Default category template thumbnail
		add_image_size( '2col-belowslide', 225, 195, true ); // Default category template thumbnail
		add_image_size( 'mag-slider', 701, 360, true ); // Magazine slider
		add_image_size( 'ajaxtabs', 30, 30, true ); // Ajaxtabs Widget

		// This theme uses wp_nav_menu() in two locations.
		register_nav_menus( array(
			'masthead'  => __( 'Masthead Navigation', 'gabfire' ),
			'primary'   => __( 'Primary Navigation', 'gabfire' ),
			'secondary' => __( 'Secondary Navigation', 'gabfire' ),
			'shop'   	=> __( 'Shop Navigation', 'gabfire' ),
			'footer'	=> __( 'Footer Navigation', 'gabfire' ),
		) );

		/*
		 * Enable support for Post Formats.
		 * See http://codex.wordpress.org/Post_Formats
		 */
		add_theme_support( 'post-formats', array(
			'video', 'audio', 'gallery',
		) );		
		
		/*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
		add_theme_support( 'html5', array(
			'search-form', 'comment-form', 'comment-list', 'gallery', 'caption'
		) );

		if ( ! isset( $content_width ) ) {
			$content_width = 701;
		}		
		
		// This theme allows users to set a custom background.
		add_theme_support( 'custom-background', apply_filters( 'gabfire_custom_background_args', array(
			'default-color' => 'f5f5f5',
		) ) );
	}
	// gabfire_theme_setup
	add_action( 'after_setup_theme', 'gabfire_theme_setup' );	
}

/**
 * Enqueue scripts and styles for the front end.
 *
 */
 if ( ! function_exists( 'gabfire_theme_scripts' ) ) {
	function gabfire_theme_scripts() {
		wp_enqueue_style('bootstrap', get_template_directory_uri() .'/framework/bootstrap/css/bootstrap.min.css');
		wp_enqueue_style('font-awesome', get_template_directory_uri() .'/framework/font-awesome/css/font-awesome.min.css');
		wp_enqueue_style('owl-carousel', get_template_directory_uri() .'/css/owl.carousel.css');
		wp_enqueue_style('gabfire-style', get_stylesheet_uri(),array( 'bootstrap','font-awesome','owl-carousel' ));
		wp_enqueue_style('bootstrap-social', get_template_directory_uri() .'/css/bootstrap-social.css');
		
		wp_enqueue_script( 'jquery' );  
		wp_enqueue_script('cycle2', get_template_directory_uri() .'/inc/js/jquery.cycle2.min.js');	
		wp_enqueue_script('owl-carousel', get_template_directory_uri() .'/inc/js/owl.carousel.min.js');
		wp_enqueue_script('bootstrap', get_template_directory_uri() .'/framework/bootstrap/js/bootstrap.min.js',array( 'jquery' ));
		wp_enqueue_script('responsive-menu', get_template_directory_uri() .'/inc/js/responsive-menu.js');
			
		if ( is_singular() && wp_attachment_is_image() ) {
			wp_enqueue_script( 'keyboard-image-navigation', get_template_directory_uri() . '/inc/js/keyboard-image-navigation.js', array( 'jquery' ), '20130402' );
		}
		
		if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
			wp_enqueue_script( 'comment-reply' );	
		}
		
		if(file_exists(get_stylesheet_directory() . '/custom.css')) {
			wp_enqueue_style('custom-style', get_stylesheet_directory_uri() .'/custom.css');
		} elseif(file_exists(get_template_directory_uri() . '/custom.css')) {
			wp_enqueue_style('custom-style', get_template_directory_uri() .'/custom.css');
		}
	}
	add_action( 'wp_enqueue_scripts', 'gabfire_theme_scripts' );	
}
	
/* ********************
 * Initialize theme scripts
 ******************************************************************** */
	if ( !function_exists( 'gabfire_initialize_scripts' ) ) {
	
		function gabfire_initialize_scripts() {	?>
			<script type='text/javascript'>
			(function($) {
				$(document).ready(function() { 
					$(".children").parent("li").addClass("has-child-menu");
					$(".sub-menu").parent("li").addClass("has-child-menu");
					$(".drop").parent("li").addClass("has-child-menu");
					
					$('.fadeimage').hover(
						function() {$(this).stop().animate({ opacity: 0.5 }, 800);},
						function() {$(this).stop().animate({ opacity: 1.0 }, 800);}
					);
					
					$('.mastheadnav li ul,.mainnav li ul,.subnav li ul,.mastheadnav li ul,.mainnav li ul').hide().removeClass('fallback');
					$('.mastheadnav > li,.mainnav > li,.subnav > li,.mainnav > li').hover(
						function () {
							$('ul', this).stop().slideDown(250);
						},
						function () {
							$('ul', this).stop().slideUp(250);
						}
					);

					$('[data-toggle="tooltip"]').tooltip({
						'placement': 'top'
					});					
					
					/* Slide to ID & remove 80px as top offset (for navigation) when sliding down */
					$('a[href*=#respond]:not([href=#])').click(function() {
						if (location.pathname.replace(/^\//, '') == this.pathname.replace(/^\//, '') || location.hostname == this.hostname) {

							var target = $(this.hash);
							target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');
							if (target.length) {
								$('html,body').animate({
									scrollTop: target.offset().top - 65
								}, 1000);
								return false;
							}
						}
					});						
					
					/* InnerPage Slider */
					var innerslider = $(".carousel-gallery");
					innerslider.owlCarousel({
					  autoPlay: 999999,
					  pagination:true,
					  singleItem : true,
					  autoHeight : true,
					  mouseDrag: false,
					  touchDrag: false					  
					});	
					$(".carousel-gallery-next").click(function(){
						innerslider.trigger('owl.next');
					});
					$(".carousel-gallery-prev").click(function(){
						innerslider.trigger('owl.prev');
					});
					
					/* InnerPage Slider */
					var owl4 = $(".carousel-four");
					owl4.owlCarousel({
					  autoPlay: 999999,
					  pagination:true,
					  singleItem : true,
					  autoHeight : true,
					  mouseDrag: false,
					  touchDrag: false					  
					});	
					$(".carousel-four-next").click(function(){
						owl4.trigger('owl.next');
					});
					$(".carousel-four-prev").click(function(){
						owl4.trigger('owl.prev');
					});						
					
					// Responsive Menu (TinyNav)
					$(".responsive_menu").tinyNav({
						active: 'current_page_item', // Set the "active" class for default menu
						label: ''
					});
					$(".tinynav").selectbox();			
					
					$('a[href=#top]').click(function(){
						$('html, body').animate({scrollTop:0}, 'slow');
						return false;
					});
				});
			})(jQuery);
			</script>
		<?php	
		}
		add_action("wp_head", "gabfire_initialize_scripts"); 	
	}	
	
/* ********************
 * Theme comment style
 ******************************************************************** */
	if ( !function_exists( 'gabfire_comment' ) ) {

		function gabfire_comment( $comment, $args, $depth ) {
			$GLOBALS['comment'] = $comment;
			switch ( $comment->comment_type ) :
				case '' :
			?>
			<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">

				<div class="comment_container" id="comment-<?php comment_ID(); ?>">
				
					<div class="comment-top">
						<div class="comment-avatar">
							<?php echo get_avatar( $comment, 50 ); ?>
						</div> 
						<span class="comment-author">
							<i class="fa fa-user"></i> 
							<?php printf( __( '%s ', 'gabfire'), sprintf( '<cite class="fn">%s</cite>', get_comment_author_link() ) ); ?>
						</span>
						<span class="comment-date-link">
							<i class="fa fa-calendar"></i>&nbsp;&nbsp;<?php printf(esc_attr__('%1$s at %2$s','gabfire'), get_comment_date(), get_comment_time()); edit_comment_link( __( 'Edit', 'gabfire'), ' ' , ''); ?>
						</span>
					</div>				
					
					<?php if ( $comment->comment_approved == '0' ) : ?>
						<p class="waiting_approval"><?php _e( 'Your comment is awaiting moderation.', 'gabfire' ); ?></p>
					<?php endif; ?>
					
					<?php comment_text(); ?>
					
					<?php comment_reply_link( array_merge( $args, array( 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
					
				</div><!-- comment_container  -->

			<?php
					break;
				case 'pingback'  :
				case 'trackback' :
			?>
			<li class="post pingback">
				<p><?php _e( 'Pingback:', 'gabfire' ); ?> <?php comment_author_link(); ?><?php edit_comment_link( __( 'Edit', 'gabfire' ), ' ' ); ?></p>
			<?php
					break;
			endswitch;
		}
	}
	   
/* ********************
 * Widgetize theme
 ******************************************************************** */	
	if ( !function_exists( 'gabfire_register_sidebar' ) ) {
		
		function gabfire_register_sidebar($args) {
		$common = array(
			'before_widget' => '<aside id="%1$s" class="widget %2$s"><div class="widgetinner">',
			'after_widget'  => "</div></aside>\n",
			'before_title'  => '<h3 class="widgettitle">',
			'after_title'   => "</h3>\n"
		);
			$args = wp_parse_args($args, $common);
			return register_sidebar($args);
		}
			
		function gabfire_widgets_init() {
			gabfire_register_sidebar(array(
				'name' => __( 'Home Bottom Sidebar', 'gabfire'),
				'id' => 'homesidebar',
				'description' => __('Homepage - below sidebar ad', 'gabfire')
			) );
			gabfire_register_sidebar(array(
				'name' => __( 'Innerpage Sidebar', 'gabfire'),
				'id' => 'innerpagesidebar',
				'description' => __('Width: 336px', 'gabfire')
			) );
			gabfire_register_sidebar(array(
				'name' => __( 'Shop Sidebar', 'gabfire'),
				'id' => 'shop',
				'description' => __('Width: 244px - This sidebar will show on WooCommerce pages only.', 'gabfire')
			) );			
			gabfire_register_sidebar(array(
				'name' => __( 'Header 728x90', 'gabfire'),
				'id' => 'header',
				'description' => __('Width:728px - Ad a 728x90 banner in this zone  if header type is set as Logo & Banner', 'gabfire')
			) );	
			gabfire_register_sidebar(array(
				'name' => __( 'Before Header -728px', 'gabfire'),
				'id' => 'beforeheader',
				'description' => __('Width:728px - Ad a 728x90 banner in this zone to display it before header at very top of site.', 'gabfire')
			) );			
			gabfire_register_sidebar(array(
				'name' => __( 'Footer 1st Row, Left', 'gabfire'),
				'id' => 'footer-row1-left',
				'description' => __('Width: 314px', 'gabfire')
			) );	
			gabfire_register_sidebar(array(
				'name' => __( 'Footer 1st Row, Mid', 'gabfire'),
				'id' => 'footer-row1-mid',
				'description' => __('Width: 314px', 'gabfire')
			) );	
			gabfire_register_sidebar(array(
				'name' => __( 'Footer 1st Row, Right', 'gabfire'),
				'id' => 'footer-row1-right',
				'description' => __('Width: 314px', 'gabfire')
			) );			
			gabfire_register_sidebar(array(
				'name' => __( 'Primary Left Top', 'gabfire'),
				'id' => 'primaryleft',
			) );
			gabfire_register_sidebar(array(
				'name' => __( 'Primary Left Bottom', 'gabfire'),
				'id' => 'primaryleft-2',
			) );
			gabfire_register_sidebar(array(
				'name' => __( 'Primary Mid Top', 'gabfire'),
				'id' => 'primarymid',
			) );
			gabfire_register_sidebar(array(
				'name' => __( 'Primary Mid Bottom', 'gabfire'),
				'id' => 'primarymid-2',
			) );
			gabfire_register_sidebar(array(
				'name' => __( 'Primary Right 160px', 'gabfire'),
				'id' => 'primaryright',
				'description' => __('Width: 160px', 'gabfire')
			) );
			gabfire_register_sidebar(array(
				'name' => __( 'Secondary Left', 'gabfire'),
				'id' => 'secondaryleft',
				'description' => __('Homepage With Slider - Secondary Row Left Column', 'gabfire')
			) );
			gabfire_register_sidebar(array(
				'name' => __( 'Secondary Mid Top', 'gabfire'),
				'id' => 'secondarymid',
				'description' => __('Homepage With Slider - Secondary Row Mid Column - Above Entries', 'gabfire')
			) );
			gabfire_register_sidebar(array(
				'name' => __( 'Secondary Mid Bottom', 'gabfire'),
				'id' => 'secondarymid-2',
				'description' => __('Homepage With Slider - Secondary Row Mid Column - Below Entries', 'gabfire')
			) );
			gabfire_register_sidebar(array(
				'name' => __( 'Secondary Right Top - 336px', 'gabfire'),
				'id' => 'secondaryright-top',
				'description' => __('Width: 336px - Homepage With Slider - Secondary Row Right Column - Above Entries', 'gabfire')
			) );			
			gabfire_register_sidebar(array(
				'name' => __( 'Secondary Right Bottom - 336px', 'gabfire'),
				'id' => 'secondaryright-bottom',
				'description' => __('Width: 336px - Homepage With Slider - Secondary Row Right Column - Below Entries', 'gabfire')
			) );
			gabfire_register_sidebar(array(
				'name' => __( 'Mag. Cat. - Secondary Left', 'gabfire'),
				'id' => 'magazine-secondaryleft',
				'description' => __('Magazine Category Template - Secondary Row Left Column', 'gabfire')
			) );
			gabfire_register_sidebar(array(
				'name' => __( 'Mag. Cat. - Secondary Mid Top', 'gabfire'),
				'id' => 'magazine-secondarymid',
				'description' => __('Magazine Category Template - Secondary Row Mid Column - Above Entries', 'gabfire')
			) );
			gabfire_register_sidebar(array(
				'name' => __( 'Mag. Cat. - Secondary Mid Bottom', 'gabfire'),
				'id' => 'magazine-secondarymid-2',
				'description' => __('Magazine Category Template - Secondary Row Mid Column - Below Entries', 'gabfire')
			) );
			gabfire_register_sidebar(array(
				'name' => __( 'Mag. Cat. - Secondary Right Top - 336px', 'gabfire'),
				'id' => 'magazine-secondaryright-top',
				'description' => __('Width: 336px - Mag. Cat. - Secondary Row Right Column - Above Entries', 'gabfire')
			) );			
			gabfire_register_sidebar(array(
				'name' => __( 'Mag. Cat. - Secondary Right Bottom - 336px', 'gabfire'),
				'id' => 'magazine-secondaryright-bottom',
				'description' => __('Width: 336px - Mag. Cat. - Secondary Row Right Column - Below Entries', 'gabfire')
			) );			
			gabfire_register_sidebar(array(
				'name' => __( 'Homepage Bottom 468px', 'gabfire'),
				'id' => 'bottom-468x60',
				'description' => __('Width: 468px', 'gabfire')
			) );			
			gabfire_register_sidebar(array(
				'name' => __( 'Before Subnews 728x90', 'gabfire'),
				'id' => 'beforesubnews-left',
				'description' => __('Width: 728x90px - This zone shows on magazine style category template as well', 'gabfire')
			) );	
			gabfire_register_sidebar(array(
				'name' => __( 'Before Subnews 315x90', 'gabfire'),
				'id' => 'beforesubnews-right',
				'description' => __('Width: 728x90px - This zone shows on magazine style category template as well', 'gabfire')
			) );		
			gabfire_register_sidebar(array(
				'name' => __( 'Page Widget', 'gabfire'),
				'id' => 'pagewidget',
				'description' => __('Use widgetized page template', 'gabfire')
			) );			
			gabfire_register_sidebar(array(
				'name' => __( 'Post Widget', 'gabfire'),
				'id' => 'postwidget',
				'description' => __('Displays below post entries', 'gabfire')
			) );
			gabfire_register_sidebar(array(
				'name' => __( 'Classic Home - Top Left - Above Entries', 'gabfire'),
				'id' => 'nosliderhome-topleft',
				'description' => __('No Slider Homepage Template, Top Left Column - Above Entries', 'gabfire')
			) );	
			gabfire_register_sidebar(array(
				'name' => __( 'Classic Home - Top Left - Below Entries', 'gabfire'),
				'id' => 'nosliderhome-topleft2',
				'description' => __('No Slider Homepage Template, Top Left Column - Below Entries', 'gabfire')
			) );				
			gabfire_register_sidebar(array(
				'name' => __( 'Classic Home - Top Mid - Above Entries', 'gabfire'),
				'id' => 'nosliderhome-topmid',
				'description' => __('No Slider Homepage Template, Top Mid Column', 'gabfire')
			) );	
			gabfire_register_sidebar(array(
				'name' => __( 'Classic Home - Top Mid - Below Entries', 'gabfire'),
				'id' => 'nosliderhome-topmid2',
				'description' => __('No Slider Homepage Template, Top Mid Column', 'gabfire')
			) );				
			gabfire_register_sidebar(array(
				'name' => __( 'Classic Home - Top Right - Above Entries', 'gabfire'),
				'id' => 'nosliderhome-topright',
				'description' => __('No Slider Homepage Template, Top Right Column', 'gabfire')
			) );	
			gabfire_register_sidebar(array(
				'name' => __( 'Classic Home - Top Right - Below Entries', 'gabfire'),
				'id' => 'nosliderhome-topright2',
				'description' => __('No Slider Homepage Template, Top Right Column', 'gabfire')
			) );	
			gabfire_register_sidebar(array(
				'name' => __( 'Classic Home - 728px Ad', 'gabfire'),
				'id' => 'homepage-728',
				'description' => __('The 728px ad shown below top mid and right columns', 'gabfire')
			) );	
			gabfire_register_sidebar(array(
				'name' => __( '3 Column Post Layout - Right Sidebar', 'gabfire'),
				'id' => '3col-right',
				'description' => __('153px - The 3rd sidebar that shows on 3 column post layout', 'gabfire')
			) );	

		}
		add_action( 'widgets_init', 'gabfire_widgets_init' );
	} //gabfire_register_sidebar

/* ********************
 * Print a list of all site contributors who published at least one post.
 ******************************************************************** */		
if ( !function_exists( 'gabfire_list_authors' ) ) {

	function gabfire_list_authors() {
		$contributor_ids = get_users( array(
			'fields'  => 'ID',
			'orderby' => 'post_count',
			'order'   => 'DESC',
			'who'     => 'authors',
		) );

		foreach ( $contributor_ids as $contributor_id ) :
			$post_count = count_user_posts( $contributor_id );

			// Move on if user has not published a post (yet).
			if ( ! $post_count ) {
				continue;
			}
		?>

		<div class="contributor">
			<div class="contributor-info">
				<div class="contributor-avatar"><?php echo get_avatar( $contributor_id, 132 ); ?></div>
				<div class="contributor-summary">
					<h2 class="contributor-name"><?php echo get_the_author_meta( 'display_name', $contributor_id ); ?></h2>
					<p class="contributor-bio">
						<?php echo get_the_author_meta( 'description', $contributor_id ); ?>
					</p>
					<a class="button contributor-posts-link" href="<?php echo esc_url( get_author_posts_url( $contributor_id ) ); ?>">
						<?php printf( _n( '%d Article', '%d Articles', $post_count, 'gabfire' ), $post_count ); ?>
					</a>
				</div><!-- .contributor-summary -->
			</div><!-- .contributor-info -->
		</div><!-- .contributor -->

		<?php
		endforeach;
	}
}//gabfire_list_authors

/* ********************
 * Custom field panels below text editor
 ******************************************************************** */		
if ( !function_exists( 'gabfire_meta_box_add' ) ) {

	add_action( 'add_meta_boxes', 'gabfire_meta_box_add' );

	function gabfire_meta_box_add()
	{
		add_meta_box( 'gabfirecustomfields', __('Gabfire Custom Fields', 'gabfire'), 'gabfire_meta_box_post', 'post', 'normal', 'high' );
		add_meta_box( 'gabfirecustomfields', __('Gabfire Custom Fields', 'gabfire'), 'gabfire_meta_box_post', 'page', 'normal', 'high' );
	}

	/* Create Post and Page Custom Fields */
	function gabfire_meta_box_post( $post )
	{
		$values = get_post_custom( $post->ID );
		$feapost = isset( $values['featured'] ) ? esc_attr( $values['featured'][0] ) : '';  
		$social = isset( $values['disable_shareicons'] ) ? esc_attr( $values['disable_shareicons'][0] ) : '';  
		$subtitle = isset( $values['subtitle'] ) ? esc_attr( $values['subtitle'][0] ) : '';  
		if ( !is_plugin_active( 'gabfire-media-module/gabfire-media.php' ) ) {
			$video = isset( $values['iframe'] ) ? esc_attr( $values['iframe'][0] ) : '';
		}
		$postslider = isset( $values['disable_postslider'] ) ? esc_attr( $values['disable_postslider'][0] ) : '';  
		$disable_feaimage = isset( $values['post_feaimage'] ) ? esc_attr( $values['post_feaimage'][0] ) : '';  
		$selected = isset( $values['gabfire_post_template'] ) ? esc_attr( $values['gabfire_post_template'][0] ) : ''; 
		
		wp_nonce_field( 'my_meta_box_nonce', 'meta_box_nonce' );
		?>

		<div class="gabfire_fieldgroup field_checkbox">
			<p class="gabfire_fieldcaption"><?php _e('Is Featured?', 'gabfire'); ?></p>
			<p class="gabfire_fieldrow">
				<label for="featured"><?php _e('Check this box to display this post on featured section of front page.</br><strong>Note:</strong> This will only work if Custom Field option is enabled within Theme Options.','gabfire'); ?></label>
				<span class="gabfire_checkbox"><input type="checkbox" id="featured" name="featured" <?php checked( $feapost, 'true' ); ?> /></span>
			</p>	
		</div>

		<div class="gabfire_fieldgroup">
			<p class="gabfire_fieldcaption"><?php _e('Post Entrance', 'gabfire'); ?></p>
			
			<p class="gabfire_fieldrow">
				<label for="subtitle"><?php _e('Enter a paragraph of text to display with a larger font size above entry.','gabfire'); ?></label>
				<textarea class="gabfire_textinput" name="subtitle" id="subtitle"><?php echo $subtitle; ?></textarea>
			</p>		
		</div>	
		
		<?php if ( !is_plugin_active( 'gabfire-media-module/gabfire-media.php' ) ) { ?>
		<div class="gabfire_fieldgroup">
			<p class="gabfire_fieldcaption"><?php _e('Video URL', 'gabfire'); ?></p>
			<p class="gabfire_fieldrow">
				<label for="iframe"><?php _e('You can add any Youtube, Vimeo, Dailymotion or Screenr video url into this box','gabfire'); ?></label>
				<input type="text" class="gabfire_textinput" name="iframe" id="iframe" value="<?php echo $video; ?>" />
			</p>
		</div>
		<?php }?>
		
		<div class="gabfire_fieldgroup field_checkbox">
			<p class="gabfire_fieldcaption"><?php _e('Disable Featured Image on this post?', 'gabfire'); ?></p>
			<p class="gabfire_fieldrow">
				<label for="post_feaimage"><?php _e('If you have enabled featured post on single post page option, the featured image shows at top of every post automatically. You may check this box to disable that image for certain posts.','gabfire'); ?></label>
				<span class="gabfire_checkbox"><input type="checkbox" id="post_feaimage" name="post_feaimage" <?php checked( $disable_feaimage, 'true' ); ?> /></span>
			</p>	
		</div>

		<div class="gabfire_fieldgroup field_checkbox">
			<p class="gabfire_fieldcaption"><?php _e('Disable innerpage slider for this post', 'gabfire'); ?></p>
			<p class="gabfire_fieldrow">
				<label for="disable_postslider"><?php _e('If you have enabled innerpage slider sitewide, but specifically want to disable it for this post, check this box.','gabfire'); ?></label>
				<span class="gabfire_checkbox"><input type="checkbox" id="disable_postslider" name="disable_postslider" <?php checked( $postslider, 'true' ); ?> /></span>
			</p>	
		</div>
		
		<div class="gabfire_fieldgroup">
			<p class="gabfire_fieldcaption"><?php _e('Post Page Layout?', 'gabfire'); ?></p>
			<p>
				<label for="gabfire_post_template"><?php _e('Select a Post layout</br><strong>Note:</strong> Select Big Picture only if you have uploaded more than 1 photo','gabfire'); ?></label>
				
				<select name="gabfire_post_template" id="gabfire_post_template">
					<?php $posttemplate = get_post_meta( get_the_ID(), 'gabfire_post_template', true ); ?>
					<option value="default" <?php selected( $selected, 'default' ); ?>><?php _e('Default Blog Post','gabfire'); ?></option>
<!-- 
					<option value="bigpicture" <?php selected( $selected, 'bigpicture' ); ?>><?php _e('Big Picture','gabfire'); ?></option>
					<option value="bigslider" <?php selected( $selected, 'bigslider' ); ?>><?php _e('Big Slider','gabfire'); ?></option>
					<option value="3col" <?php selected( $selected, '3col' ); ?>><?php _e('3 Column Post Layout','gabfire'); ?></option>
-->
					<option value="leftsidebar" <?php selected( $selected, 'leftsidebar' ); ?>><?php _e('Left Sidebar','gabfire'); ?></option>
					<option value="fullwidth" <?php selected( $selected, 'fullwidth' ); ?>><?php _e('No Sidebar','gabfire'); ?></option>
				</select>
			</p>
		</div>	

		<div class="gabfire_fieldgroup field_checkbox">
			<p class="gabfire_fieldcaption"><?php _e('Disable Share Icons?', 'gabfire'); ?></p>
			<p class="gabfire_fieldrow">
				<label for="disable_shareicons"><?php _e('Check to disable share icons for this post/page.','gabfire'); ?></label>
				<span class="gabfire_checkbox"><input type="checkbox" id="disable_shareicons" name="disable_shareicons" <?php checked( $social, 'true' ); ?> /></span>
			</p>	
		</div>		
	<?php
	}	
	
	add_action( 'save_post', 'gabfire_meta_box_save' );
	function gabfire_meta_box_save( $post_id )
	{
		// Bail if we're doing an auto save
		if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
		
		// if our nonce isn't there, or we can't verify it, bail
		if( !isset( $_POST['meta_box_nonce'] ) || !wp_verify_nonce( $_POST['meta_box_nonce'], 'my_meta_box_nonce' ) ) return;
		
		// if our current user can't edit this post, bail
		if( !current_user_can( 'edit_post' ) ) return;
		
		// now we can actually save the data
		$allowed = array( 
			'a' => array( // on allow a tags
				'href' => array() // and those anchords can only have href attribute
			)
		);
		
		if ( !is_plugin_active( 'gabfire-media-module/gabfire-media.php' ) ) {
			if( isset( $_POST['iframe'] ) && !empty( $_POST['iframe'] ) )
				update_post_meta( $post_id, 'iframe', wp_kses( $_POST['iframe'], $allowed ) );
		}

		if( isset( $_POST['gabfire_post_template'] ) && !empty( $_POST['gabfire_post_template'] ) )	
			update_post_meta( $post_id, 'gabfire_post_template', esc_attr( $_POST['gabfire_post_template'] ) ); 	

		if( isset( $_POST['subtitle'] ) && !empty( $_POST['subtitle'] ) )
			update_post_meta( $post_id, 'subtitle', wp_kses( $_POST['subtitle'], $allowed ) );							
			
		$chk = isset( $_POST['featured'] ) && $_POST['featured'] ? 'true' : 'false';  
		update_post_meta( $post_id, 'featured', $chk );
			
		$chk3 = isset( $_POST['disable_postslider'] ) && $_POST['disable_postslider'] ? 'true' : 'false';  
		update_post_meta( $post_id, 'disable_postslider', $chk3 ); 		
		
		$chk2 = isset( $_POST['post_feaimage'] ) && $_POST['post_feaimage'] ? 'true' : 'false';  
		update_post_meta( $post_id, 'post_feaimage', $chk2 ); 
		
		$chk4 = isset( $_POST['disable_shareicons'] ) && $_POST['disable_shareicons'] ? 'true' : 'false';  
		update_post_meta( $post_id, 'disable_shareicons', $chk4 );		
	}
	
	/* Gabfire Media Module plugin loads same file. No need to load it for the second time */
	if ( !function_exists( 'gabfire_custom_fields_css' ) ) {
		function gabfire_custom_fields_css() {
			wp_enqueue_style('gabfire-customfields-style', plugins_url() .'/gabfire-media-module/style.css' );
		}
		
		add_action('admin_head-post.php', 'gabfire_custom_fields_css');
		add_action('admin_head-post-new.php', 'gabfire_custom_fields_css');
	}	
	
	/* Add a little more niceness and assign post template class to body tag */
	add_filter('body_class','gabfire_custom_body_classes');
	function gabfire_custom_body_classes( $classes ) {
	 
		if ( get_post_meta( get_the_ID(), 'gabfire_post_template', true ) ) {
			
			$classes[] = 'body-' . get_post_meta( get_the_ID(), 'gabfire_post_template', true );
			
		}
		
		// return the $classes array
		return $classes;
	}	
}
	
/* ********************
 * Set different number of entries per category
 ******************************************************************** */		
if ( !function_exists( 'entrynr_perCat' ) ) {
	function entrynr_perCat( $query ) {
		if ( is_admin() || ! $query->is_main_query() )
			return;
		
		if ( is_category(explode(',', of_get_option('of_an_2col'))) ) {
			$query->set( 'posts_per_page', 10 );
			return;
		}
		
		if ( is_category(explode(',', of_get_option('of_an_2col_full'))) ) {
			$query->set( 'posts_per_page', 6 );
			return;
		}
		
		if ( is_category(explode(',', of_get_option('of_an_3col'))) ) {
			$query->set( 'posts_per_page', 15 );
			return;
		}	

		if ( is_category(explode(',', of_get_option('of_an_3col_full'))) ) {
			$query->set( 'posts_per_page', 12 );
			return;
		}			
		
		if ( is_category(explode(',', of_get_option('of_an_4col'))) ) {
			$query->set( 'posts_per_page', 16 );
			return;
		}				
		
		if ( is_category(explode(',', of_get_option('of_an_media'))) ) {
			$query->set( 'posts_per_page', 12 );
			return;
		}		

		if ( is_category(explode(',', of_get_option('of_an_2colslide'))) ) {
			$query->set( 'posts_per_page', 10 );
			return;
		}				
		
		if ( is_category(explode(',', of_get_option('of_an_mag'))) ) {
			$query->set( 'posts_per_page', 15 );
			return;
		}						
	}
	add_action( 'pre_get_posts', 'entrynr_perCat', 1 );
}

/* Display a notice that can be dismissed */
	if (!function_exists('gabfire_admin_notice')) {
		if ( current_user_can( 'manage_options' ) ) {

			add_action('admin_notices', 'gabfire_admin_notice');
		
			function gabfire_admin_notice() {
				global $current_user;
				$user_id = $current_user->ID;
				/* Check that the user hasn't already clicked to ignore the message */
				if ( ! get_user_meta($user_id, 'example_ignore_notice') ) {
					echo '<div class="updated">'; 
					$install = 'http://dashboard.gabfire.com/my-downloads/themes/sharp/index.php#install';
					$codex = 'http://codex.gabfire.com';
					$forums = 'http://forums.gabfire.com';
					$ignore = '?gabfire_admin_notice_hide=0';
					$currentheme = wp_get_theme();
					$theme_name = ucwords( $currentheme->{'Name'} );
					
					printf(__('<p><a class="button-primary" target="_blank" href="%1$s">%4$s Installation Guide</a> <a class="button-primary" target="_blank" href="%2$s">Codex</a> <a class="button-primary" target="_blank" href="%1$s">Support Forums</a></p><p>For better Media Handling, we suggest you to install <a href="'. admin_url( 'plugin-install.php?tab=search&type=term&s=gabfire+media+module' ) .'">Gabfire Media Module</a> and <a href="'. admin_url( 'plugin-install.php?tab=search&type=term&s=otf+regenerate+thumbnails' ) .'">OTF Regenerate Thumbnails</a> plugins. | <a href="%5$s">Hide This Message</a></p>'), $install, $codex, $forums, $theme_name, $ignore );
					echo "</div>";
				}
			}

			add_action('admin_init', 'gabfire_admin_notice_hide');
			
			function gabfire_admin_notice_hide() {
				global $current_user;
				$user_id = $current_user->ID;				
				/* If user clicks to ignore the notice, add that to their user meta */
				if ( isset($_GET['gabfire_admin_notice_hide']) && '0' == $_GET['gabfire_admin_notice_hide'] ) {
					 add_user_meta($user_id, 'example_ignore_notice', 'true', true);
				}
			}
		}
	}


/* Add social share buttons
 * to single post page
 ******************************************************************** */
	if (!function_exists('gabfire_singlepostshare')) {

		add_action( 'gabfire_before_singlepost_content', 'gabfire_singlepostshare', 	5 );

		function gabfire_singlepostshare() {

			global $wp_query;
			$postid = $wp_query->post->ID;
			$social = get_post_meta($postid, 'disable_shareicons', true); ?>
			
			<?php if ($social != 'true') { ?>
				<div class="social-sharethis-post">
					<?php			
					$text 		= __( 'Send by email', 'gabfire' );
					$recommend	= __( 'I recommend this page:', 'gabfire' );
					$readon		= __( 'You can read it on:', 'gabfire' );
					
					$title = htmlspecialchars($post->post_title);
					$subject = htmlspecialchars(get_bloginfo('name')).' : '.$title;
					$body = $recommend . ' ' .$title. '.' . "\n" .$readon. ' ' .get_permalink($post->ID);
					$link = 'mailto:?subject='.rawurlencode($subject).'&amp;body='.rawurlencode($body);
					?>					
				
					<a href="http://www.facebook.com/sharer.php?u=<?php echo get_permalink();?>&t=<?php echo get_the_title(); ?>" data-toggle="tooltip" title="<?php _e('Share on Facebook', 'gabfire'); ?>" rel="nofollow"><span><?php _e('Share on Facebook','gabfire'); ?></span>
						<i class="fa fa-facebook pull-left"></i>
					</a>

					<a href="http://twitter.com/home?status=<?php echo get_the_title() .' => '.get_permalink(); ?>" data-toggle="tooltip" title="<?php _e('Share on Twitter', 'gabfire'); ?>" rel="nofollow"><span><?php _e('Follow on Facebook','gabfire'); ?></span>
						<i class="fa fa-twitter pull-left"></i>
					</a>

					<a href="https://plus.google.com/share?url=<?php echo get_permalink();?>" data-toggle="tooltip" title="<?php _e('Share on Google+', 'gabfire'); ?>" rel="nofollow"><span><?php _e('Add to Google+','gabfire'); ?></span>
						<i class="fa fa-google-plus pull-left"></i>
					</a>

					<a href="http://www.linkedin.com/shareArticle?mini=true&url=<?php echo get_permalink() ?>&title=<?php echo get_the_title(); ?>&summary=&source=<?php echo get_bloginfo('name'); ?>" data-toggle="tooltip" title="<?php _e('Share on LinkedIn', 'gabfire'); ?>" rel="nofollow"><span><?php _e('Connect on Linked in','gabfire'); ?></span>
						<i class="fa fa-linkedin pull-left"></i>
					</a>

					<a class="pull-right" href="mailto:?<?php echo $link; ?>" data-toggle="tooltip" title="<?php _e('Send by Email', 'gabfire'); ?>" rel="nofollow"><span><?php _e('Subscribe by Email','gabfire'); ?></span>
						<i class="fa fa-envelope-o pull-left"></i>
					</a>
					
					<a class="pull-right" href="javascript:window.print()" data-toggle="tooltip" title="<?php _e('Print This Post','gabfire'); ?>" rel="nofollow"><span><?php _e('Print This Post','gabfire'); ?></span>
						<i class="fa fa-print pull-left"></i>
					</a>
				</div>	
				<div class="clearfix"></div>
			<?php
			}
		}
	}
	
/* Single Post Media to 
 * display before entry
 * ******************************************************************* */
	if (!function_exists('gabfire_singlepostmedia')) {
		function gabfire_singlepostmedia() {
			
			global $wp_query;
			$postid = $wp_query->post->ID;
			$disable_feaimage = get_post_meta($postid, 'post_feaimage', true);
			$post_layout = get_post_meta($postid, 'gabfire_post_template', true);
				
			if ( ($post_layout == 'fullwidth')  or (is_page_template('tpl-fullwidth.php')) ) {
				$name = 'postthumbnail-big';
				$media_width = 1066;
				$media_height = 445;
			} else { 
				$name = 'postthumbnail';
				$media_width = 701;
				$media_height = 350;
			}	
			
			if (of_get_option('of_autoimage') == 1) { 
				if ($disable_feaimage == 'true') { 
					$enableimage = 0;
				} else { 
					$enableimage = 1;
				}
			} else {
				$enableimage = 0;
			}
								
			gabfire_media(array(
				'name' => $name,
				'imgtag' => 1,
				'link' => 0,
				'enable_video' => 1,
				'enable_thumb' => $enableimage, 
				'resize_type' => 'w', 
				'media_width' => $media_width, 
				'media_height' => $media_height, 
				'thumb_align' => 'aligncenter',
				'enable_default' => 0
			));
		}
	}
