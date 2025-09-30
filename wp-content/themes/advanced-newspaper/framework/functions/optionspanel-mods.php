<?php
/* OPTIONS PANEL UPDATES */
add_action('optionsframework_after', 'gabfire_optionsframework');
function gabfire_optionsframework() {
	$currentheme = wp_get_theme();

	$options_imagepath = get_template_directory_uri() . '/framework/images/gabfire-options';
	$theme_name = '<strong>' . ucwords( $currentheme->{'Name'} ). '</strong>';
	$contact_link = '<a target="_blank" href="http://www.gabfirethemes.com/hire-us/">Contact us</a>';
	
	wp_enqueue_style('gabfire-options', get_template_directory_uri() .'/framework/functions/css/gabfire-options.css');
	?>
	<div id="gabfire-options-sidebar">
		<div class="postbox">
			<div class="inside">
				<a target="_blank" href="http://www.gabfirethemes.com/wordpress-themes/">
					<img src="<?php echo $options_imagepath; ?>/gabfire-themes.jpg" width="224" height="115" alt="" />
				</a>			
				<p><?php _e('Are you looking for a new theme to change the layout of your site? <a target="_blank" href="http://www.gabfirethemes.com/wordpress-themes/">Click here</a> to see all our newest themes', 'gabfire'); ?></p>				
			</div>
		</div>
		
		<div class="postbox">
			<div class="inside">
				<a target="_blank" href="http://www.gabfirethemes.com/customization/"><img width="224" height="63" src="<?php echo $options_imagepath; ?>/customize.png" /></a>	
				<?php printf(esc_attr__('Customization by the same team that created %2$s means guaranteed quality. %1$s for a free, no-obligation, cost estimate for your project.','gabfire'), $contact_link, $theme_name); ?></p>
			</div>
		</div>		
				
		<div class="postbox">
			<div class="inside">
				<h3><?php _e('Support','gabfire'); ?></h3>
				<ul>
					<li><a class="optionspanel_sidelink optionspanel_codex" target="_blank" href="http://codex.gabfire.com"><?php _e('Gabfire Codex', 'gabfire'); ?></a></li>
					<li><a class="optionspanel_sidelink optionspanel_forums" target="_blank" href="http://forums.gabfire.com/"><?php _e('Support Forums', 'gabfire'); ?></a></li>
					<li><a class="optionspanel_sidelink optionspanel_faq" target="_blank" href="http://www.gabfirethemes.com/faq/"><?php _e('FAQ', 'gabfire'); ?></a></li>
					<li><a class="optionspanel_sidelink optionspanel_blog" target="_blank" href="http://www.gabfirethemes.com/blog/"><?php _e('Latest News', 'gabfire'); ?></a></li>
				</ul>
			</div>		
		</div>		
		
		<div class="postbox">
			<div class="inside">
				<h3><?php _e('Social','gabfire'); ?></h3>
				<ul>
					<li><a class="optionspanel_sidelink optionspanel_newsletter" target="_blank" href="http://gabfirethemes.com/go/sendinblue.php"><?php _e('Subscribe to Newsletters', 'gabfire'); ?></a></li>
					<li><a class="optionspanel_sidelink optionspanel_facebook" target="_blank" href="https://www.facebook.com/gabfire"><?php _e('Connect on Facebook', 'gabfire'); ?></a></li>
					<li><a class="optionspanel_sidelink optionspanel_twitter" target="_blank" href="http://www.twitter.com/gabfirethemes"><?php _e('Follow on Twitter', 'gabfire'); ?></a></li>
					<li><a class="optionspanel_sidelink optionspanel_rss" target="_blank" href="http://www.gabfirethemes.com/feed/"><?php _e('Subscribe to RSS', 'gabfire'); ?></a></li>
				</ul>
			</div>		
		</div>

	</div>
	
	<a href="http://www.gabfirethemes.com/customization/"><img class="gabfire-logo" src="<?php echo $options_imagepath; ?>/logo.png" /></a>
	<?php
}

/* CUSTOM FUNCTIONS */

/* Favicon */
function gabfire_theme_favicon() {
	if (of_get_option('of_custom_favicon') != '') {
		echo '<link rel="shortcut icon" href="'.of_get_option('of_custom_favicon').'"/>'."\n";
	}
	else { echo '<link rel="shortcut icon" href="'.get_template_directory_uri().'/framework/admin/images/favicon.ico" />'; }
}
add_action('wp_head', 'gabfire_theme_favicon');

/* Custom CSS */
function gabfire_head_css() {		
	// OUTPUT STYLES
	$output = '';
	$output = of_get_option('of_custom_css');

	if ($output <> '') {
		$output = "<!-- Custom Styling -->\n<style type=\"text/css\">\n".$output."</style>\n";
		echo $output;
	}
}
add_action('wp_head', 'gabfire_head_css');