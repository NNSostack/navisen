<?php
/**
 * A unique identifier is defined to store the options in the database and reference them from the theme.
 * By default it uses the theme name, in lowercase and without spaces, but this can be changed if needed.
 * If the identifier changes, it'll appear as if the options have been reset.
 *
 */
function optionsframework_option_name() {
	return 'advanced-newspaper';
}

/**
 * Defines an array of options that will be used to generate the settings page and be saved in the database.
 * When creating the 'id' fields, make sure to use all lowercase and no spaces.
 *  
 */

function optionsframework_options() {

	// VARIABLES
	$themename = wp_get_theme();
	$themename = $themename->{'Name'};
	$shortname = 'of';
	$themeid = '_an';
	
	// Background options
	$patterns_path = get_template_directory_uri() . '/framework/images/patterns/';
	$patterns_array = array(
	'none' => $patterns_path . 'none.jpg',
	'subtle-1' => $patterns_path . 'subtle-1.jpg',
	'subtle-2' => $patterns_path . 'subtle-2.jpg',
	'subtle-3' => $patterns_path . 'subtle-3.jpg',
	'subtle-4' => $patterns_path . 'subtle-4.jpg',
	'subtle-5' => $patterns_path . 'subtle-5.jpg',
	'subtle-6' => $patterns_path . 'subtle-6.jpg',
	'subtle-7' => $patterns_path . 'subtle-7.jpg',
	'subtle-8' => $patterns_path . 'subtle-8.jpg',
	'subtle-9' => $patterns_path . 'subtle-9.jpg',
	'subtle-10' => $patterns_path . 'subtle-10.jpg',
	'subtle-11' => $patterns_path . 'subtle-11.jpg',
	'subtle-12' => $patterns_path . 'subtle-12.jpg',
	'subtle-13' => $patterns_path . 'subtle-13.jpg',
	'subtle-14' => $patterns_path . 'subtle-14.jpg',
	'subtle-15' => $patterns_path . 'subtle-15.jpg',
	'subtle-16' => $patterns_path . 'subtle-16.jpg',
	'subtle-17' => $patterns_path . 'subtle-17.jpg',
	);
	
	// Cycle2 Options
	$cycle2_slidefx = array(
		'fade' => 'Fade',
		'fadeout' => 'Fade-Out',
		'scrollHorz' => 'Scroll Horizontally',
		'none' => 'None'
	);

	// Multicheck Defaults
	$multicheck_defaults = array(
		'one' => '1',
		'five' => '1'
	);
	
	// Background Defaults
	$background_defaults = array(
		'color' => '',
		'image' => '',
		'repeat' => 'repeat',
		'position' => 'top center',
		'attachment'=>'scroll' );

	// Typography Defaults
	$typography_defaults = array(
		'size' => '15px',
		'face' => 'georgia',
		'style' => 'bold',
		'color' => '#bada55' );
		
	// Typography Options
	$typography_options = array(
		'sizes' => array( '6','12','14','16','20' ),
		'faces' => array( 'Helvetica Neue' => 'Helvetica Neue','Arial' => 'Arial' ),
		'styles' => array( 'normal' => 'Normal','bold' => 'Bold' ),
		'color' => false
	);	
	
	// If using image radio buttons, define a directory path
	$imagepath = get_template_directory_uri() . '/framework/admin/images/';
	
	//Default Arrays 
	$options_nr = array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20);
	$options_inslider = array(__('Disable', 'gabfire'), __('Tag-based', 'gabfire'), __('Site Wide', 'gabfire'));
	$options_sort = array('ASC' => 'asc', 'desc' => 'desc');
	$options_order = array('id' => 'id', 'name' => 'name', 'count' => 'count');
	$options_logo = array('text' => __('Text Based Logo', 'gabfire'), 'image' => __('Image Based Logo', 'gabfire'));
	$options_feaslide = array('scrollUp', 'scrollDown', 'scrollLeft', 'scrollRight', 'turnUp', 'turnDown', 'turnLeft', 'turnRight', 'fade');
	
	// Pull all the categories into an array
	$options_categories = array();
	$options_categories_obj = get_categories();
	foreach ($options_categories_obj as $category) {
		$options_categories[$category->cat_ID] = $category->cat_name;
	}

	// Pull all tags into an array
	$options_tags = array();
	$options_tags_obj = get_tags();
	foreach ( $options_tags_obj as $tag ) {
		$options_tags[$tag->term_id] = $tag->name;
	}

	// Pull all the pages into an array
	$options_pages = array();
	$options_pages_obj = get_pages( 'sort_column=post_parent,menu_order' );
	$options_pages[''] = 'Select a page:';
	foreach ($options_pages_obj as $page) {
		$options_pages[$page->ID] = $page->post_title;
	}
	
	//More Options
	$uploads_arr = wp_upload_dir();
	$all_uploads_path = $uploads_arr['path'];
	$all_uploads = get_option('of_uploads');
	$other_entries = array('Select a number:',1,'2','3',4,'5','6','7','8','9','10','11','12','13','14','15','16','17','18','19');
	$body_repeat = array('no-repeat','repeat-x','repeat-y','repeat');
	$body_pos = array('top left','top center','top right','center left','center center','center right','bottom left','bottom center','bottom right');
	
	// Pull all the pages into an array
	$options_pages = array();  
	$options_pages_obj = get_pages('sort_column=post_parent,menu_order');
	$options_pages[''] = 'Select a page:';
	foreach ($options_pages_obj as $page) {
    	$options_pages[$page->ID] = $page->post_title;
	}
		
	// If using image radio buttons, define a directory path
	$imagepath = get_template_directory_uri() . '/framework/admin/images/';
	$imagepath_theme = get_template_directory_uri() . '/images/';
		
	$options = array();
		
	$options[] = array( 'name' => __('General', 'gabfire'), 'type' => 'heading');
	
		$options[] = array( 'name' => __('Homepage Layout', 'gabfire'),
							'desc' => __('Select homepage layout', 'gabfire'),
							'id' => $shortname.'_homepage',
							'type' => 'select',
							'options' => array(
								'advanced' => __('Advanced Layout with Slider', 'gabfire'),
								'simple' => __('Simple Layout without Slider', 'gabfire')));

		$options[] = array( 'name' => __('Select header style', 'gabfire'),
							'desc' => __('Set your header either to display a single image, logo and banner, or logo with quotes.', 'gabfire'),
							'id' => $shortname.'_header_type',
							'type' => 'images',
							'options' => array(
								'logobanner' => $imagepath_theme . 'logobanner.gif',
								'singleimage' => $imagepath_theme . 'single-image.gif',
								'quotelogo' => $imagepath_theme . 'quote-logo.gif'));

		$options[] = array( 'name' => __('Header image (if single image selected)', 'gabfire'),
							'desc' => __('If single image option is selected as header type, upload an image 960px wide.', 'gabfire'),
							'id' => $shortname.'_himageurl',
							'type' => 'upload');

		$options[] = array( 'name' => __('Logo Type', 'gabfire'),
							'desc' => __('If text-based logo is selected, set site name and tagline on WordPress General Settings page.', 'gabfire'),
							'id' => $shortname.'_logo_type',
							'std' => 'Text Based Logo',
							'type' => 'select',
							'options' => $options_logo); 

		$options[] = array( 'name' => __('Custom Logo', 'gabfire'),
							'desc' => __('If image-based logo is selected, upload a logo for your theme, or specify the image address of your online logo. (http://yoursite.com/logo.png) [Max Width: 360px]', 'gabfire'),
							'id' => $shortname.'_logo',
							'type' => 'upload');
							
		$options[] = array( 'name' => __('Text Logo', 'gabfire'),
							'desc' => __('If text-based logo is selected, enter text to display on first row.', 'gabfire'),
							'id' => $shortname.'_logo1',
							'std' => 'ADVANCED',
							'type' => 'text');	
							
		$options[] = array( 'desc' => __('If text-based logo is selected, enter text to display on second row.', 'gabfire'),
							'id' => $shortname.'_logo2',
							'std' => 'NEWSPAPER',
							'type' => 'text');	
							
		$options[] = array( 'name' => __('Logo Padding Top', 'gabfire'),
							'desc' => __('Set a padding value between logo and top line.', 'gabfire'),
							'id' => $shortname.'_padding_top',
							'std' => 15,
							'class' => 'mini',
							'type' => 'text');	

		$options[] = array( 'name' => __('Logo Padding Bottom', 'gabfire'),
							'desc' => __('Set a padding value between logo and bottom line.', 'gabfire'),
							'id' => $shortname.'_padding_bottom',
							'std' => 15,
							'class' => 'mini',
							'type' => 'text');
							
		$options[] = array( 'name' => __('Logo Padding Left', 'gabfire'),
							'desc' => __('Set a padding value between logo and left line.', 'gabfire'),
							'id' => $shortname.'_padding_left',
							'std' => 0,
							'type' => 'text');
							
		$options[] = array( 'name' => __('Custom Favicon', 'gabfire'),
							'desc' => __('Upload a 16px x 16px png / gif image that will represent your website favicon.', 'gabfire'),
							'id' => $shortname.'_custom_favicon',
							'type' => 'upload'); 
							
		$options[] = array( 'name' => __('RSS', 'gabfire'),
							'desc' => __('Link to third party feed handler. <br/> [http://www.url.com]', 'gabfire'),
							'id' => $shortname.'_rssaddr',
							'type' => 'text'); 				
									
	$options[] = array( 'name' => __('Quotes', 'gabfire'), 'type' => 'heading');					
		
		$options[] = array( 'name' => __('Header Left Quote (only if Header with Quotes is activated)', 'gabfire'),
							'desc' => __('Left quote caption', 'gabfire'),
							'id' => $shortname.'_leftquotecap',
							'std' => 'Vladimir Putin',
							'type' => 'text'); 		
							
		$options[] = array( 'desc' => __('Left quote text', 'gabfire'),
							'id' => $shortname.'_leftquotetext',
							'std' => 'We have one Fatherland, one people and a common future',
							'type' => 'text'); 		

		$options[] = array(	'desc' => __('Left quote image. Max width 80px', 'gabfire'),
							'id' => $shortname.'_leftquoteimg',
							'std' => get_template_directory_uri() . '/images/putin.gif',
							'type' => 'upload');
							
		$options[] = array( 'desc' => __('Left Quote Link <br /> eg. http://www.yahoo.com', 'gabfire'),
							'id' => $shortname.'_lquotelink',
							'type' => 'text'); 	

		$options[] = array( 'name' => __('Header Right Quote (only if Header with Quotes is activated)', 'gabfire'),
							'desc' => __('Right quote caption', 'gabfire'),
							'id' => $shortname.'_rightquotecap',
							'std' => 'Barack Obama',
							'type' => 'text'); 		
							
		$options[] = array( 'desc' => __('Right quote text', 'gabfire'),
							'id' => $shortname.'_rightquotetext',
							'std' => 'You know, my faith is one that admits some doubt',
							'type' => 'text'); 		

		$options[] = array(	'desc' => __('Right quote image. Max width 80px', 'gabfire'),
							'id' => $shortname.'_rightquoteimg',
							'std' => get_template_directory_uri() . '/images/obama.gif',
							'type' => 'upload');
							
		$options[] = array( 'desc' => __('Right Quote Link <br /> eg. http://www.yahoo.com', 'gabfire'),
							'id' => $shortname.'_rquotelink',
							'type' => 'text'); 									
							
	$options[] = array( 'name' => __('Navigation', 'gabfire'), 'type' => 'heading');			


		$options[] = array( 'name' => __('Sort Categories', 'gabfire'),
							'desc' => __('Display categories in ascending or descending order', 'gabfire'),
							'id' => $shortname.'_sort_cats',
							'type' => 'select',
							'class' => 'mini',
							'options' => $options_sort);

		$options[] = array( 'name' => __('Order Categories', 'gabfire'),
							'desc' => __('Display categories in alphabetical order, by category ID, or by the count of posts', 'gabfire'),
							'id' => $shortname.'_order_cats',
							'std' => 'name',
							'type' => 'select',
							'class' => 'mini',
							'options' => $options_order);
							
		$options[] = array( 'name' => __('Exclude Categories', 'gabfire'),
							'desc' => __('ID number of cat(s) to exclude from navigation (eg 1,2,3,4 - no spaces) <a href="http://www.gabfirethemes.com/how-to-check-category-ids/" target="_blank">How check category/page ID</a>', 'gabfire'),
							'id' => $shortname.'_ex_cats',
							'class' => 'mini',
							'type' => 'text'); 
							
		$options[] = array( 'name' => __('Exclude Pages', 'gabfire'),
							'desc' => __('ID number of page(s) to exclude from navigation (eg 1,2,3,4 - no spaces) <a href="http://www.gabfirethemes.com/how-to-check-category-ids/" target="_blank">How check category/page ID</a>', 'gabfire'),
							'id' => $shortname.'_ex_pages',
							'class' => 'mini',
							'type' => 'text');

	$options[] = array( 'name' => __('Homepage', 'gabfire'), 'type' => 'heading');
	
		$options[] = array(
			'desc' => __('There are two homepage layouts (Advanced and Simple) included into this theme. Go through each options below only if you have selected advanced homepage layout under General section. Otherwise skip this tab and proceed with next one.', 'gabfire'),
			'type' => 'info');	
	
		$options[] = array( 'name' => __('Do not duplicate posts', 'gabfire'),
							'desc' => __('No matter what categories are selected for front page sections, if a post is displayed once do not duplicate it on front page', 'gabfire'),
							'id' => $shortname.'_dnd',
							'std' => 1,
							'type' => 'checkbox');	
							
		$options[] = array( 'name' => __('Featured Slider', 'gabfire'),
							'desc' => __('Select a base query type to determine how this content area is populated', 'gabfire'),
							'id' => $shortname.$themeid.'_featypeadvanced',
							'std' => 'catbased',
							'type' => 'images',
							'options' => array(
								'catbased' => $imagepath . 'fea-category.gif',
								'cfbased' => $imagepath . 'fea-customfield.gif',
								'mrecent' => $imagepath . 'fea-recent.gif',
								'tagbased' => $imagepath . 'fea-tag.gif'));
								
		$options[] = array(
			'desc' => __('If base query type is Category or Tag-based, please identify the category or tag. </br>If base query type is Custom Field, posts which are marked as featured (checkbox labeled as <strong>Is Featured</strong> enabled on <strong>Add/Edit Post</strong> screen) will be displayed.', 'gabfire'),
			'type' => 'info');

		$options[] = array( 'desc' => __('If category based: Select a category for entries.', 'gabfire'),
							'id' => $shortname.$themeid.'_fea_cat',
							'type' => 'select',
							'options' => $options_categories);

		$options[] = array( 'desc' => __('If tag based: Select a tag for entries.', 'gabfire'),
							'id' => $shortname.$themeid.'_fea_tag',
							'type' => 'select',
							'options' => $options_tags);

		$options[] = array( 'desc' => __('Number of Entries to display', 'gabfire'),
							'id' => $shortname.$themeid.'_nrfea',
							'std' => 6,
							'class' => 'mini',
							'type' => 'select',
							'options' => $options_nr);
		
		$options[] = array( 'name' => __('Below Featured', 'gabfire'),
							'desc' => __('Number of entries to display. <br />Set 0 to disable', 'gabfire'),
							'desc' => __('Select a category', 'gabfire'),
							'id' => $shortname.$themeid.'_cat1',
							'type' => 'select',
							'options' => $options_categories);
							
		$options[] = array( 'desc' => __('Number of Entries to display. [2 is the suggested # of posts for best responsive layout.]', 'gabfire'),
							'id' => $shortname.$themeid.'_nr1',
							'std' => 2,
							'class' => 'mini',
							'type' => 'select',
							'options' => $options_nr);

		$options[] = array( 'name' => __('Primary Mid Column', 'gabfire'),
							'desc' => __('Select a category for right hand of featured column', 'gabfire'),
							'id' => $shortname.$themeid.'_cat2',
							'type' => 'select',
							'options' => $options_categories);
							
		$options[] = array( 'desc' => __('Number of Entries to display. [4 is the suggested # of posts for best responsive layout.]', 'gabfire'),
							'id' => $shortname.$themeid.'_nr2',
							'std' => 4,
							'class' => 'mini',
							'type' => 'select',
							'options' => $options_nr);
							
		$options[] = array( 'name' => __('Secondary Top Left [Breaking News]', 'gabfire'),
							'desc' => __('Select a category', 'gabfire'),
							'id' => $shortname.$themeid.'_cat3',
							'type' => 'select',
							'options' => $options_categories);
							
		$options[] = array( 'desc' => __('Number of entries to display. <br />Set 0 to disable', 'gabfire'),
							'id' => $shortname.$themeid.'_nr3',
							'std' => 9,
							'class' => 'mini',
							'type' => 'select',
							'options' => $options_nr);
													
		$options[] = array( 'name' => __('Secondary Top Mid Column', 'gabfire'),
							'desc' => __('Select a category', 'gabfire'),
							'id' => $shortname.$themeid.'_cat4',
							'type' => 'select',
							'options' => $options_categories);
							
		$options[] = array( 'desc' => __('Number of entries to display. <br />Set 0 to disable', 'gabfire'),
							'id' => $shortname.$themeid.'_nr4',
							'std' => 3,
							'class' => 'mini',
							'type' => 'select',
							'options' => $options_nr);
							
		$options[] = array( 'name' => __('Secondary Top Right Column', 'gabfire'),
							'desc' => __('Select a category', 'gabfire'),
							'id' => $shortname.$themeid.'_cat5',
							'type' => 'select',
							'options' => $options_categories);
							
		$options[] = array( 'desc' => __('Number of entries to display. <br />Set 0 to disable', 'gabfire'),
							'id' => $shortname.$themeid.'_nr5',
							'std' => 1,
							'class' => 'mini',
							'type' => 'select',
							'options' => $options_nr);
													
							
		$options[] = array( 'name' => __('Tabbed Content #1', 'gabfire'),
							'desc' => __('Select a category', 'gabfire'),
							'id' => $shortname.$themeid.'_tabcat1',
							'type' => 'select',
							'options' => $options_categories);
							
		$options[] = array( 'desc' => __('Number of entries to display. <br />Set 0 to disable', 'gabfire'),
							'id' => $shortname.$themeid.'_tabnr1',
							'std' => 4,
							'class' => 'mini',
							'type' => 'select',
							'options' => $options_nr);
							
		$options[] = array( 'name' => __('Tabbed Content #2', 'gabfire'),
							'desc' => __('Select a category', 'gabfire'),
							'id' => $shortname.$themeid.'_tabcat2',
							'type' => 'select',
							'options' => $options_categories);
							
		$options[] = array( 'desc' => __('Number of entries to display. <br />Set 0 to disable', 'gabfire'),
							'id' => $shortname.$themeid.'_tabnr2',
							'std' => 4,
							'class' => 'mini',
							'type' => 'select',
							'options' => $options_nr);
							
		$options[] = array( 'name' => __('Tabbed Content #3', 'gabfire'),
							'desc' => __('Select a category', 'gabfire'),
							'id' => $shortname.$themeid.'_tabcat3',
							'type' => 'select',
							'options' => $options_categories);

		$options[] = array( 'desc' => __('Number of entries to display. <br />Set 0 to disable', 'gabfire'),
							'id' => $shortname.$themeid.'_tabnr3',
							'std' => 4,
							'class' => 'mini',
							'type' => 'select',
							'options' => $options_nr);

		$options[] = array( 'name' => __('Tabbed Content #4', 'gabfire'),
							'desc' => __('Select a category', 'gabfire'),
							'id' => $shortname.$themeid.'_tabcat4',
							'type' => 'select',
							'options' => $options_categories);
							
		$options[] = array( 'desc' => __('Number of entries to display. <br />Set 0 to disable', 'gabfire'),
							'id' => $shortname.$themeid.'_tabnr4',
							'std' => 4,
							'class' => 'mini',
							'type' => 'select',
							'options' => $options_nr);

		$options[] = array( 'name' => __('Tabbed Content #5', 'gabfire'),
							'desc' => __('Select a category', 'gabfire'),
							'id' => $shortname.$themeid.'_tabcat5',
							'type' => 'select',
							'options' => $options_categories);
							
		$options[] = array( 'desc' => __('Number of entries to display. <br />Set 0 to disable', 'gabfire'),
							'id' => $shortname.$themeid.'_tabnr5',
							'std' => 4,
							'class' => 'mini',
							'type' => 'select',
							'options' => $options_nr);
							
		/*Secondary Content Left Column */
		$options[] = array( 'name' => __('Secondary Bottom Left Column', 'gabfire'),
							'desc' => __('Select a category.', 'gabfire'),
							'id' => $shortname.$themeid.'_cat6',
							'type' => 'select',
							'options' => $options_categories);

		$options[] = array( 'desc' => __('Number of entries to without thumbs but with big headlines.', 'gabfire'),
							'id' => $shortname.$themeid.'_nr6a',
							'std' => 1,
							'class' => 'mini',
							'type' => 'select',
							'options' => $options_nr);	
							
		$options[] = array( 'desc' => __('Number of entries to display with small thumbs.', 'gabfire'),
							'id' => $shortname.$themeid.'_nr6b',
							'std' => 2,
							'class' => 'mini',
							'type' => 'select',
							'options' => $options_nr);	
							
		$options[] = array( 'desc' => __('Number of entries to display with big thumbs.', 'gabfire'),
							'id' => $shortname.$themeid.'_nr6c',
							'std' => 1,
							'class' => 'mini',
							'type' => 'select',
							'options' => $options_nr);	
								
		/*Secondary Content Right Column*/
		$options[] = array( 'name' => __('Secondary Bottom Right Column', 'gabfire'),
							'desc' => __('Select a category.', 'gabfire'),
							'id' => $shortname.$themeid.'_cat7',
							'type' => 'select',
							'options' => $options_categories);

		$options[] = array( 'desc' => __('Number of entries to display.', 'gabfire'),
							'id' => $shortname.$themeid.'_nr7',
							'std' => 4,
							'class' => 'mini',
							'type' => 'select',
							'options' => $options_nr);								
							
		$options[] = array( 'name' => __('Subnews #1', 'gabfire'),
							'desc' => __('Select a category', 'gabfire'),
							'id' => $shortname.$themeid.'_subrow1cat1',
							'type' => 'select',
							'options' => $options_categories);
							
		$options[] = array( 'desc' => __('Number of entries to display. <br />Set 0 to disable', 'gabfire'),
							'id' => $shortname.$themeid.'_subrow1nr1',
							'std' => 1,
							'class' => 'mini',
							'type' => 'select',
							'options' => $options_nr);
							
		$options[] = array( 'name' => __('Subnews #2','gabfire'),
							'desc' => __('Select a category', 'gabfire'),
							'id' => $shortname.$themeid.'_subrow1cat2',
							'type' => 'select',
							'options' => $options_categories);
							
		$options[] = array( 'desc' => __('Number of entries to display. <br />Set 0 to disable', 'gabfire'),
							'id' => $shortname.$themeid.'_subrow1nr2',
							'std' => 1,
							'class' => 'mini',
							'type' => 'select',
							'options' => $options_nr);
							
		$options[] = array( 'name' => __('Subnews #3', 'gabfire'),
							'desc' => __('Select a category', 'gabfire'),
							'id' => $shortname.$themeid.'_subrow1cat3',
							'type' => 'select',
							'options' => $options_categories);
							
		$options[] = array( 'desc' => __('Number of entries to display. <br />Set 0 to disable', 'gabfire'),
							'id' => $shortname.$themeid.'_subrow1nr3',
							'std' => 1,
							'class' => 'mini',
							'type' => 'select',
							'options' => $options_nr);
				
		$options[] = array( 'name' => __('Subnews #4', 'gabfire'),
							'desc' => __('Select a category', 'gabfire'),
							'id' => $shortname.$themeid.'_subrow2cat1',
							'type' => 'select',
							'options' => $options_categories);
							
		$options[] = array( 'desc' => __('Number of entries to display. <br />Set 0 to disable', 'gabfire'),
							'id' => $shortname.$themeid.'_subrow2nr1',
							'std' => 1,
							'class' => 'mini',
							'type' => 'select',
							'options' => $options_nr);
							
		$options[] = array( 'name' => __('Subnews #5', 'gabfire'),
							'desc' => __('Select a category', 'gabfire'),
							'id' => $shortname.$themeid.'_subrow2cat2',
							'type' => 'select',
							'options' => $options_categories);
							
		$options[] = array( 'desc' => __('Number of entries to display. <br />Set 0 to disable', 'gabfire'),
							'id' => $shortname.$themeid.'_subrow2nr2',
							'std' => 1,
							'class' => 'mini',
							'type' => 'select',
							'options' => $options_nr);
							
		$options[] = array( 'name' => __('Subnews #6', 'gabfire'),
							'desc' => __('Select a category', 'gabfire'),
							'id' => $shortname.$themeid.'_subrow2cat3',
							'type' => 'select',
							'options' => $options_categories);
							
		$options[] = array( 'desc' => __('Number of entries to display. <br />Set 0 to disable', 'gabfire'),
							'id' => $shortname.$themeid.'_subrow2nr3',
							'std' => 1,
							'class' => 'mini',
							'type' => 'select',
							'options' => $options_nr);

		$options[] = array( 'name' => __('Homepage Classic', 'gabfire'), 'type' => 'heading');

		$options[] = array(
			'desc' => __('There are two homepage layouts (Advanced and Simple) included into this theme. Go through each options below only if you have selected simple homepage layout under General section. Otherwise skip this tab and proceed with next one.', 'gabfire'),
			'type' => 'info');	
		
		$options[] = array( 'name' => __('Do not duplicate posts', 'gabfire'),
							'desc' => __('No matter what categories are selected for front page sections, if a post is displayed once do not duplicate it on front page', 'gabfire'),
							'id' => $shortname.'_dnd_simple',
							'std' => 1,
							'type' => 'checkbox');		
	
		$options[] = array( 'name' => __('Primary Top Left [Breaking News]', 'gabfire'),
							'desc' => __('Select a category', 'gabfire'),
							'id' => $shortname.$themeid.'_scat1',
							'type' => 'select',
							'options' => $options_categories);
							
		$options[] = array( 'desc' => __('Number of entries to display. <br />Set 0 to disable', 'gabfire'),
							'id' => $shortname.$themeid.'_snr1',
							'std' => 8,
							'class' => 'mini',
							'type' => 'select',
							'options' => $options_nr);	
							
		$options[] = array( 'name' => __('Featured Section', 'gabfire'),
							'desc' => __('Select a base query type to determine how this content area is populated', 'gabfire'),
							'id' => $shortname.$themeid.'_featype',
							'std' => 'catbased',
							'type' => 'images',
							'options' => array(
								'catbased' => $imagepath . 'fea-category.gif',
								'cfbased' => $imagepath . 'fea-customfield.gif',
								'mrecent' => $imagepath . 'fea-recent.gif',
								'tagbased' => $imagepath . 'fea-tag.gif'));
								
		$options[] = array(
			'desc' => __('If base query type is Category or Tag-based, please identify the category or tag. </br>If base query type is Custom Field, posts which are marked as featured (checkbox labeled as <strong>Is Featured</strong> is enabled on <strong>Add/Edit Post</strong> screen) will be displayed).', 'gabfire'),
			'type' => 'info');
		
		$options[] = array( 'desc' => __('If category based: Select a category for entries.', 'gabfire'),
							'id' => $shortname.$themeid.'_scat2',
							'type' => 'select',
							'options' => $options_categories);

		$options[] = array( 'desc' => __('If tag based: Select a tag for entries.', 'gabfire'),
							'id' => $shortname.$themeid.'_stag2',
							'type' => 'select',
							'options' => $options_tags);

		$options[] = array( 'desc' => __('Number of Entries to display with big thumbnails', 'gabfire'),
							'id' => $shortname.$themeid.'_snr2',
							'std' => 4,
							'class' => 'mini',
							'type' => 'select',
							'options' => $options_nr);
							
		$options[] = array( 'desc' => __('Number of Entries to display with small thumbnails', 'gabfire'),
							'id' => $shortname.$themeid.'_snr2a',
							'std' => 4,
							'class' => 'mini',
							'type' => 'select',
							'options' => $options_nr);	

		$options[] = array( 'name' => __('Primary Top Right Column', 'gabfire'),
							'desc' => __('Select a category', 'gabfire'),
							'id' => $shortname.$themeid.'_scat3',
							'type' => 'select',
							'options' => $options_categories);
							
		$options[] = array( 'desc' => __('Number of entries to display. <br />Set 0 to disable', 'gabfire'),
							'id' => $shortname.$themeid.'_snr3',
							'std' => 2,
							'class' => 'mini',
							'type' => 'select',
							'options' => $options_nr);

		$options[] = array( 'name' => __('Tabbed Content #1', 'gabfire'),
							'desc' => __('Select a category', 'gabfire'),
							'id' => $shortname.$themeid.'_tabscat1',
							'type' => 'select',
							'options' => $options_categories);
							
		$options[] = array( 'desc' => __('Number of entries to display. <br />Set 0 to disable', 'gabfire'),
							'id' => $shortname.$themeid.'_tabsnr1',
							'std' => 3,
							'class' => 'mini',
							'type' => 'select',
							'options' => $options_nr);
							
		$options[] = array( 'name' => __('Tabbed Content #2', 'gabfire'),
							'desc' => __('Select a category', 'gabfire'),
							'id' => $shortname.$themeid.'_tabscat2',
							'type' => 'select',
							'options' => $options_categories);
							
		$options[] = array( 'desc' => __('Number of entries to display. <br />Set 0 to disable', 'gabfire'),
							'id' => $shortname.$themeid.'_tabsnr2',
							'std' => 4,
							'class' => 'mini',
							'type' => 'select',
							'options' => $options_nr);
							
		$options[] = array( 'name' => __('Tabbed Content #3', 'gabfire'),
							'desc' => __('Select a category', 'gabfire'),
							'id' => $shortname.$themeid.'_tabscat3',
							'type' => 'select',
							'options' => $options_categories);

		$options[] = array( 'desc' => __('Number of entries to display. <br />Set 0 to disable', 'gabfire'),
							'id' => $shortname.$themeid.'_tabsnr3',
							'std' => 4,
							'class' => 'mini',
							'type' => 'select',
							'options' => $options_nr);

		$options[] = array( 'name' => __('Tabbed Content #4', 'gabfire'),
							'desc' => __('Select a category', 'gabfire'),
							'id' => $shortname.$themeid.'_tabscat4',
							'type' => 'select',
							'options' => $options_categories);
							
		$options[] = array( 'desc' => __('Number of entries to display. <br />Set 0 to disable', 'gabfire'),
							'id' => $shortname.$themeid.'_tabsnr4',
							'std' => 4,
							'class' => 'mini',
							'type' => 'select',
							'options' => $options_nr);

		$options[] = array( 'name' => __('Tabbed Content #5', 'gabfire'),
							'desc' => __('Select a category', 'gabfire'),
							'id' => $shortname.$themeid.'_tabscat5',
							'type' => 'select',
							'options' => $options_categories);
							
		$options[] = array( 'desc' => __('Number of entries to display. <br />Set 0 to disable', 'gabfire'),
							'id' => $shortname.$themeid.'_tabsnr5',
							'std' => 4,
							'class' => 'mini',
							'type' => 'select',
							'options' => $options_nr);
							
		/*Secondary Content Left Column */
		$options[] = array( 'name' => __('Secondary Bottom Left Column', 'gabfire'),
							'desc' => __('Select a category.', 'gabfire'),
							'id' => $shortname.$themeid.'_scat4',
							'type' => 'select',
							'options' => $options_categories);

		$options[] = array( 'desc' => __('Number of entries to without thumbs but with big headlines.', 'gabfire'),
							'id' => $shortname.$themeid.'_snr4a',
							'std' => 0,
							'class' => 'mini',
							'type' => 'select',
							'options' => $options_nr);	
							
		$options[] = array( 'desc' => __('Number of entries to display with small thumbs.', 'gabfire'),
							'id' => $shortname.$themeid.'_snr4b',
							'std' => 0,
							'class' => 'mini',
							'type' => 'select',
							'options' => $options_nr);	
							
		$options[] = array( 'desc' => __('Number of entries to display with big thumbs.', 'gabfire'),
							'id' => $shortname.$themeid.'_snr4c',
							'std' => 0,
							'class' => 'mini',
							'type' => 'select',
							'options' => $options_nr);	
								
		/*Secondary Content Right Column*/
		$options[] = array( 'name' => __('Secondary Bottom Right Column', 'gabfire'),
							'desc' => __('Select a category.', 'gabfire'),
							'id' => $shortname.$themeid.'_scat5',
							'type' => 'select',
							'options' => $options_categories);

		$options[] = array( 'desc' => __('Number of entries to display.', 'gabfire'),
							'id' => $shortname.$themeid.'_snr5',
							'std' => 0,
							'class' => 'mini',
							'type' => 'select',
							'options' => $options_nr);								
							
		$options[] = array( 'name' => __('Subnews #1', 'gabfire'),
							'desc' => __('Select a category', 'gabfire'),
							'id' => $shortname.$themeid.'_subrow1scat1',
							'type' => 'select',
							'options' => $options_categories);
							
		$options[] = array( 'desc' => __('Number of entries to display. <br />Set 0 to disable', 'gabfire'),
							'id' => $shortname.$themeid.'_subrow1snr1',
							'std' => 2,
							'class' => 'mini',
							'type' => 'select',
							'options' => $options_nr);
							
		$options[] = array( 'name' => __('Subnews #2', 'gabfire'),
							'desc' => __('Select a category', 'gabfire'),
							'id' => $shortname.$themeid.'_subrow1scat2',
							'type' => 'select',
							'options' => $options_categories);
							
		$options[] = array( 'desc' => __('Number of entries to display. <br />Set 0 to disable', 'gabfire'),
							'id' => $shortname.$themeid.'_subrow1snr2',
							'std' => 4,
							'class' => 'mini',
							'type' => 'select',
							'options' => $options_nr);				
							
	$options[] = array( 'name' => __('Sliders', 'gabfire'), 'type' => 'heading');
			$options[] = array( 'name' => __('Featured Slider', 'gabfire'),
								'desc' => __('Auto rotation speed. Slide to next slide in X seconds', 'gabfire'),
								'id' => $shortname.$themeid.'_featimeout',
								'std' => 15,
								'type' => 'select',
								'class' => 'mini',
								'options' => $options_nr);

			$options[] = array( 'name' => __('Inner-Page Slider', 'gabfire'),
								'desc' => __('Automatically create slideshow of uploaded photos in post entries to be displayed below post title. [Note: Select options include displaying slider site-wide, tag-based, or to disable completely].', 'gabfire'),
								'id' => $shortname.'_inslider',
								'std' => 'Disable',
								'type' => 'select',
								'class' => 'mini',
								'options' => $options_inslider);
								
			$options[] = array( 'desc' => __('If tag-based option is selected, display posts assigned this tag to be shown in inner-page slider. <br/> [Note: Posts with multiple image attachments and tagged with this key will display within slider].', 'gabfire'),
								'id' => $shortname.'_inslider_tag',
								'std' => '',
								'class' => 'mini',
								'type' => 'text'); 
								
			$options[] = array( 'desc' => __('Enable auto rotation for innerpage slider.', 'gabfire'),
								'id' => $shortname.$themeid.'_inner_rotate',
								'std' => 0,
								'type' => 'checkbox');	
								
			$options[] = array( 'desc' => __('(If auto rotate enabled) define pause time between 2 slides. [in seconds]', 'gabfire'),
								'id' => $shortname.$themeid.'_inner_pause',
								'std' => '5',
								'class' => 'mini',
								'type' => 'text');

	$options[] = array( 'name' => __('Image Handling', 'gabfire'), 'type' => 'heading');

		$options[] = array( 'name' => __('Featured Image Post Display', 'gabfire'),
							'desc' => __('Auto resize and display featured image on single post - above entry.', 'gabfire'),
							'id' => $shortname.'_autoimage',
							'std' => 0,
							'type' => 'checkbox');	
	
		$options[] = array( 'name' => __('Enable images on subnews', 'gabfire'),
							'desc' => __('Check this box to display thumbnails on 8 vertical subnews blocks', 'gabfire'),
							'id' => $shortname.$themeid.'_subphotos',
							'std' => 0,
							'type' => 'checkbox');							

	$options[] = array( 'name' => __('Footer', 'gabfire'), 'type' => 'heading');

		$options[] = array( 'name' => __('Custom Text (Left)', 'gabfire'),
							'desc' => __('Custom HTML and Text that will appear in the footer of your theme.', 'gabfire'),
							'id' => $shortname.'_footer1_text',
							'type' => 'textarea');
								
		$options[] = array( 'name' => __('Custom Text (Right)', 'gabfire'),
							'desc' => __('Custom HTML and Text that will appear in the footer of your theme.', 'gabfire'),
							'id' => $shortname.'_footer2_text',
							'type' => 'textarea');

	$options[] = array( 'name' => __('Ads', 'gabfire'), 'type' => 'heading');	
				
		$options[] = array(
							'desc' => __('To display a banner on your site, Download Gabfire Widget Pack plugin -> activate the plugin -> Appearance -> Gabfire Widget Pack -> Activate Simple Ads widget -> move that widget into any widget zone where you would want to show a banner', 'gabfire'),
							'type' => 'info');		
						
	$options[] = array( 'name' => __('Miscellaneous', 'gabfire'), 'type' => 'heading');	
					
		$options[] = array(	'name' => __('Archive - Media Category Template', 'gabfire'),
							'desc' => __('ID number of cat(s) to use media gallery template (separate with comma if more than 1 category is entered)', 'gabfire'),
							'id' => $shortname.$themeid.'_media',
							'class' => 'mini',
							'type' => 'text'); 	
							
		$options[] = array(	'name' => __('Archive - 2 Column Category Template', 'gabfire'),
							'desc' => __('ID number of cat(s) to use 2 column category template (separate with comma if more than 1 category is entered)', 'gabfire'),
							'id' => $shortname.$themeid.'_2col',
							'class' => 'mini',
							'type' => 'text'); 	
							
		$options[] = array(	'name' => __('Archive - 2 Column Category Template - Full', 'gabfire'),
							'desc' => __('ID number of cat(s) to use 2 column - no sidebar category template (separate with comma if more than 1 category is entered)', 'gabfire'),
							'id' => $shortname.$themeid.'_2col_full',
							'class' => 'mini',
							'type' => 'text'); 		

		$options[] = array(	'name' => __('Archive - 2 column with sidebar and slider', 'gabfire'),
							'desc' => __('ID number of cat(s) to use magazine style category template (separate with comma if more than 1 category is entered)', 'gabfire'),
							'id' => $shortname.$themeid.'_2colslide',
							'class' => 'mini',
							'type' => 'text'); 							
							
		$options[] = array(	'name' => __('Archive - 3 Column Category Template', 'gabfire'),
							'desc' => __('ID number of cat(s) to use 3 column category template (separate with comma if more than 1 category is entered)', 'gabfire'),
							'id' => $shortname.$themeid.'_3col',
							'class' => 'mini',
							'type' => 'text'); 	
							
		$options[] = array(	'name' => __('Archive - 3 Column Category Template - Full', 'gabfire'),
							'desc' => __('ID number of cat(s) to use 3 column - no sidebar category template (separate with comma if more than 1 category is entered)', 'gabfire'),
							'id' => $shortname.$themeid.'_3col_full',
							'class' => 'mini',
							'type' => 'text'); 								

		$options[] = array(	'name' => __('Archive - 4 Column Category Template', 'gabfire'),
							'desc' => __('ID number of cat(s) to use 4 column category template (separate with comma if more than 1 category is entered)', 'gabfire'),
							'id' => $shortname.$themeid.'_4col',
							'class' => 'mini',
							'type' => 'text'); 

		$options[] = array(	'name' => __('Archive - Magazine Style Category Template', 'gabfire'),
							'desc' => __('ID number of cat(s) to use magazine style category template (separate with comma if more than 1 category is entered)', 'gabfire'),
							'id' => $shortname.$themeid.'_mag',
							'class' => 'mini',
							'type' => 'text'); 							
							
		$options[] = array( 'name' => __('Header - Social Items', 'gabfire'),
							'desc' => __('Disable header social links', 'gabfire'),
							'id' => $shortname.'_socialhead',
							'std' => 1,
							'type' => 'checkbox');

		$options[] = array( 'desc' => __('Link to Facebook account. <br/> [http://www.facebook.com/gabfire]', 'gabfire'),
							'id' => $shortname.$themeid.'_facebook',
							'std' => '',
							'type' => 'text');
							
		$options[] = array( 'desc' => __('Link to Twitter Account. <br/> [http://www.twitter.com/gabfirethemes]', 'gabfire'),
							'id' => $shortname.$themeid.'_twitter',
							'std' => '',
							'type' => 'text');
							
		$options[] = array( 'desc' => __('Link to Google+ Account. <br/> [https://plus.google.com/106104916131754615481/posts]', 'gabfire'),
							'id' => $shortname.$themeid.'_gplus',
							'std' => '',
							'type' => 'text');
							
		$options[] = array( 'desc' => __('Link to LinkedIn Account. <br/> [http://www.linkedin.com/company/gabfire-themes]', 'gabfire'),
							'id' => $shortname.$themeid.'_linkedin',
							'std' => '',
							'type' => 'text');
							
		$options[] = array( 'desc' => __('Link to Pinterest Account. <br/> [http://www.pinterest.com]', 'gabfire'),
							'id' => $shortname.$themeid.'_pinterest',
							'std' => '',
							'type' => 'text');			
							
		$options[] = array( 'desc' => __('Link to Tumblr Account. <br/> [http://www.tumblr.com]', 'gabfire'),
							'id' => $shortname.$themeid.'_tumblr',
							'std' => '',
							'type' => 'text');										
				
		$options[] = array( 'desc' => __('Link to Vimeo Account. <br/> [http://vimeo.com/gabfire]', 'gabfire'),
							'id' => $shortname.$themeid.'_vimeo',
							'std' => '',
							'type' => 'text'); 
							
		$options[] = array( 'desc' => __('Link to Youtube Account. <br/> [http://www.youtube.com/user/gabfirethemes]', 'gabfire'),
							'id' => $shortname.$themeid.'_ytube',
							'std' => '',
							'type' => 'text');
							
		$options[] = array( 'desc' => __('Email subscribe link. <br/> [http://feedburner.google.com/fb/a/mailverify?uri=gabfirethemes]', 'gabfire'),
							'id' => $shortname.$themeid.'_email',
							'std' => '',
							'type' => 'text'); 							
						
		$options[] = array( 'desc' => __('Display a link to site feeds on masthead navigation', 'gabfire'),
							'id' => $shortname.$themeid.'_sitefeed',
							'std' => 1,
							'type' => 'checkbox');									
							
		$options[] = array( 'name' => __('Shortcodes', 'gabfire'),
							'desc' => __('Enable shortcodes functionality', 'gabfire'),
							'id' => $shortname.'_shortcodes',
							'std' => 0,
							'type' => 'checkbox');								
							
		$options[] = array( 'name' => __('Widget Map', 'gabfire'),
							'desc' => __('Display the location of widgets on front page. After checking widget locations <strong>be sure to uncheck this option</strong>', 'gabfire'),
							'id' => $shortname.'_widget',
							'std' => 0,
							'type' => 'checkbox');
								
	$options[] = array( 'name' => __('Custom Styling', 'gabfire'), 'type' => 'heading');	
	
		$options[] = array( 'name' => __('Custom CSS', 'gabfire'),
							'desc' => __('If you have custom CSS code you wish to apply, enter it in this block.', 'gabfire'),
							'id' => $shortname.'_custom_css',
							'type' => 'textarea');

		$options[] = array( 'desc' => __('Enable custom styles.', 'gabfire'),
							'id' => $shortname.'_customcolors',
							'std' => 0,
							'type' => 'checkbox');
							
		$options[] = array( 'name' => __('Body background & typography', 'gabfire'),
							'desc' => __('Change body background and font style', 'gabfire'),
							'id' => $shortname.'_bodytype',
							'std' => 0,
							'class' => 'hidden',
							'type' => 'checkbox');
					
		$options[] = array( 'desc' => __('Select a background for site.', 'gabfire'),
							'id' => $shortname.'_sitebg',
							'std' => 'none',
							'type' => "images",
							'class' => 'hidden',
							'options' => $patterns_array);			
					
		$options[] = array( 'desc' => __('Specify the body font properties', 'gabfire'),
							'id' => $shortname.'_body_font',
							'std' => array('size' => '15px','face' => 'Open Sans, Tahoma, sans-serif','style' => 'normal','color' => '#555555'),
							'class' => 'hidden',
							'type' => 'typography');	

		$options[] = array( 'desc' => __('Post Title Font Family. Avilable Google Fonts are Droid Sans, Open Sans, Roboto Condensed ', 'gabfire'),
							'id' => $shortname.'_headingfontfamily',
							'std' => 'georgia, serif',
							'class' => 'hidden',
							'type' => 'text');								
							
		$options[] = array( 'name' => __('Change Colors', 'gabfire'),
							'desc' => __('Primary Site Colors', 'gabfire'),
							'id' => $shortname.'_editcolors',
							'std' => 0,
							'class' => 'hidden',
							'type' => 'checkbox');							
							
			$options[] = array( 'desc' => __('Link Color', 'gabfire'),
								'id' => $shortname.'_primarycolor',
								'std' => '#cd1713',
								'class' => 'hidden',
								'type' => 'color');							
								
			$options[] = array( 'desc' => __('Post title link color', 'gabfire'),
								'id' => $shortname.'_titlecolor',
								'std' => '#333333',
								'class' => 'hidden',
								'type' => 'color');
								
			$options[] = array( 'desc' => __('Post title hovered link color', 'gabfire'),
								'id' => $shortname.'_titlehovercolor',
								'std' => '#000000',
								'class' => 'hidden',
								'type' => 'color');
								
		$options[] = array( 'name' => __('Main Navigation', 'gabfire'),
							'desc' => __('Edit Colors on Main Navigation', 'gabfire'),
							'id' => $shortname.'_editnav2',
							'std' => 0,
							'class' => 'hidden',
							'type' => 'checkbox');									
								
			$options[] = array(	'name' => __('Main Navigation', 'gabfire'),
								'desc' => __('Main Navigation Background Image', 'gabfire'),
								'id' => $shortname.'_nav2bg',
								'std' => get_template_directory_uri() . '/images/bg-lines.gif',
								'class' => 'hidden',
								'type' => 'upload');								

			$options[] = array( 'desc' => __('Main Navigation Bar - Border Bottom Color', 'gabfire'),
								'id' => $shortname.'_nav2border',
								'std' => '#dddddd',
								'class' => 'hidden',
								'type' => 'color');
								
			$options[] = array( 'desc' => __('Main navigation Link Color', 'gabfire'),
								'id' => $shortname.'_nav2link',
								'std' => '#cd1713',
								'class' => 'hidden',
								'type' => 'color');
								
			$options[] = array( 'desc' => __('Main navigation Selected Link Color', 'gabfire'),
								'id' => $shortname.'_nav2linkcurrent',
								'std' => '#cd1713',
								'class' => 'hidden',
								'type' => 'color');								
								
			$options[] = array( 'desc' => __('Main navigation Hovered Link Color', 'gabfire'),
								'id' => $shortname.'_nav2linkhover',
								'std' => '#cd1713',
								'class' => 'hidden',
								'type' => 'color');

			$options[] = array( 'desc' => __('Main navigation Dropdown Menu Link Color', 'gabfire'),
								'id' => $shortname.'_nav2alink',
								'std' => '#444444',
								'class' => 'hidden',
								'type' => 'color');		

			$options[] = array( 'desc' => __('Main navigation Dropdown Menu Hovered Link Color', 'gabfire'),
								'id' => $shortname.'_nav2alinkhover',
								'std' => '#222222',
								'class' => 'hidden',
								'type' => 'color');										
								
			$options[] = array( 'desc' => __('Main navigation Dropdown Menu Border Color', 'gabfire'),
								'id' => $shortname.'_nav2aborder',
								'std' => '#efefef',
								'class' => 'hidden',
								'type' => 'color');									

			$options[] = array( 'desc' => __('Main navigation Dropdown Menu Background Color', 'gabfire'),
									'id' => $shortname.'_nav2abg',
									'std' => '#ffffff',
									'class' => 'hidden',
									'type' => 'color');		

			$options[] = array( 'desc' => __('Main navigation Dropdown Menu Hovered Menu Item Background Color', 'gabfire'),
								'id' => $shortname.'_nav2abghover',
								'std' => '#f5f5f5',
								'class' => 'hidden',
								'type' => 'color');	
								
			$options[] = array( 'desc' => __('Main navigation Dropdown Menu Selected Menu Item Link Color', 'gabfire'),
								'id' => $shortname.'_nav2alinkcurrent',
								'std' => '#222222',
								'class' => 'hidden',
								'type' => 'color');									
								
			$options[] = array( 'desc' => __('Main navigation Dropdown Menu Selected Menu Item Background Color', 'gabfire'),
								'id' => $shortname.'_nav2alinkbgcurrent',
								'std' => '#f5f5f5',
								'class' => 'hidden',
								'type' => 'color');	
								
			$options[] = array( 'name' => __('Secondary Navigation', 'gabfire'),
								'desc' => __('Edit Colors on Secondary Navigation', 'gabfire'),
								'id' => $shortname.'_editnav3',
								'std' => 0,
								'class' => 'hidden',
								'type' => 'checkbox');		
							
			$options[] = array(	'name' => __('Secondary Navigation Navigation', 'gabfire'),
								'desc' => __('Secondary Navigation Navigation Background Color', 'gabfire'),
								'id' => $shortname.'_nav3bg',
								'std' => '#efefef',
								'class' => 'hidden',
								'type' => 'color');							

			$options[] = array( 'desc' => __('Secondary Navigation Navigation Bar - Border Bottom Color', 'gabfire'),
								'id' => $shortname.'_nav3border',
								'std' => '#dddddd',
								'class' => 'hidden',
								'type' => 'color');
								
			$options[] = array( 'desc' => __('Secondary Navigation navigation Link Color', 'gabfire'),
								'id' => $shortname.'_nav3link',
								'std' => '#cd1713',
								'class' => 'hidden',
								'type' => 'color');
								
			$options[] = array( 'desc' => __('Secondary Navigation navigation Selected Link Color', 'gabfire'),
								'id' => $shortname.'_nav3linkcurrent',
								'std' => '#cd1713',
								'class' => 'hidden',
								'type' => 'color');								
								
			$options[] = array( 'desc' => __('Secondary Navigation navigation Hovered Link Color', 'gabfire'),
								'id' => $shortname.'_nav3linkhover',
								'std' => '#cd1713',
								'class' => 'hidden',
								'type' => 'color');

			$options[] = array( 'desc' => __('Secondary Navigation navigation Dropdown Menu Link Color', 'gabfire'),
								'id' => $shortname.'_nav3alink',
								'std' => '#444444',
								'class' => 'hidden',
								'type' => 'color');		

			$options[] = array( 'desc' => __('Secondary Navigation navigation Dropdown Menu Hovered Link Color', 'gabfire'),
								'id' => $shortname.'_nav3alinkhover',
								'std' => '#222222',
								'class' => 'hidden',
								'type' => 'color');										
								
			$options[] = array( 'desc' => __('Secondary Navigation navigation Dropdown Menu Border Color', 'gabfire'),
								'id' => $shortname.'_nav3aborder',
								'std' => '#efefef',
								'class' => 'hidden',
								'type' => 'color');									

			$options[] = array( 'desc' => __('Secondary Navigation navigation Dropdown Menu Background Color', 'gabfire'),
									'id' => $shortname.'_nav3abg',
									'std' => '#ffffff',
									'class' => 'hidden',
									'type' => 'color');		

			$options[] = array( 'desc' => __('Secondary Navigation navigation Dropdown Menu Hovered Menu Item Background Color', 'gabfire'),
								'id' => $shortname.'_nav3abghover',
								'std' => '#f5f5f5',
								'class' => 'hidden',
								'type' => 'color');	

			$options[] = array( 'desc' => __('Main navigation Dropdown Menu Selected Menu Item Link Color', 'gabfire'),
								'id' => $shortname.'_nav3alinkcurrent',
								'std' => '#222222',
								'class' => 'hidden',
								'type' => 'color');									
								
			$options[] = array( 'desc' => __('Main navigation Dropdown Menu Selected Menu Item Background Color', 'gabfire'),
								'id' => $shortname.'_nav3alinkbgcurrent',
								'std' => '#f5f5f5',
								'class' => 'hidden',
								'type' => 'color');									
						
	update_option('of_template',$options); 					  
	update_option('of_themename',$themename);   
	update_option('of_shortname',$shortname);

	/*
	$wp_editor_settings = array(
		'wpautop' => true, // Default
		'textarea_rows' => 5,
		'tinymce' => array( 'plugins' => 'wordpress,wplink' )
	);
	$options[] = array(
		'name' => __( 'Default Text Editor', 'theme-textdomain' ),
		'desc' => sprintf( __( 'You can also pass settings to the editor.  Read more about wp_editor in <a href="%1$s" target="_blank">the WordPress codex</a>', 'theme-textdomain' ), 'http://codex.wordpress.org/Function_Reference/wp_editor' ),
		'id' => 'example_editor',
		'type' => 'editor',
		'settings' => $wp_editor_settings
	);
	*/
	
	return $options;
}

add_action( 'optionsframework_custom_scripts', 'optionsframework_custom_scripts' );
function optionsframework_custom_scripts() { ?>
	<script type="text/javascript">
		jQuery(document).ready(function() {
			jQuery('#of_customcolors').click(function() {
				jQuery('#section-of_bodytype').fadeToggle(400);
				jQuery('#section-of_editcolors').fadeToggle(400);
				jQuery('#section-of_editnav2').fadeToggle(400);
				jQuery('#section-of_editnav3').fadeToggle(400);
			});
			
			if (jQuery('#of_customcolors:checked').val() !== undefined) {
				jQuery('#section-of_bodytype').show();
				jQuery('#section-of_editcolors').show();
				jQuery('#section-of_editnav2').show();
				jQuery('#section-of_editnav3').show();
			}
		
			jQuery('#of_bodytype').click(function() {
				jQuery('#section-of_sitebg').fadeToggle(400);
				jQuery('#section-of_body_font').fadeToggle(400);
				jQuery('#section-of_headingfontfamily').fadeToggle(400);
				
			});
			
			if (jQuery('#of_bodytype:checked').val() !== undefined) {
				jQuery('#section-of_sitebg').show();
				jQuery('#section-of_body_font').show();
				jQuery('#section-of_headingfontfamily').show();
			}

			jQuery('#of_editcolors').click(function() {
				jQuery('#section-of_primarycolor').fadeToggle(400);
				jQuery('#section-of_titlecolor').fadeToggle(400);
				jQuery('#section-of_titlehovercolor').fadeToggle(400);
			});
			
			if (jQuery('#of_editcolors:checked').val() !== undefined) {
				jQuery('#section-of_primarycolor').show();
				jQuery('#section-of_titlecolor').show();
				jQuery('#section-of_titlehovercolor').show();
			}		

			jQuery('#of_editnav2').click(function() {
				jQuery('#section-of_nav2bg').fadeToggle(400);
				jQuery('#section-of_nav2border').fadeToggle(400);
				jQuery('#section-of_nav2link').fadeToggle(400);
				jQuery('#section-of_nav2linkcurrent').fadeToggle(400);
				jQuery('#section-of_nav2linkhover').fadeToggle(400);
				jQuery('#section-of_nav2alink').fadeToggle(400);
				jQuery('#section-of_nav2alinkhover').fadeToggle(400);
				jQuery('#section-of_nav2aborder').fadeToggle(400);
				jQuery('#section-of_nav2abg').fadeToggle(400);
				jQuery('#section-of_nav2abghover').fadeToggle(400);
				jQuery('#section-of_nav2alinkcurrent').fadeToggle(400);
				jQuery('#section-of_nav2alinkbgcurrent').fadeToggle(400);
			});
			
			if (jQuery('#of_editnav2:checked').val() !== undefined) {
				jQuery('#section-of_nav2bg').show();
				jQuery('#section-of_nav2border').show();
				jQuery('#section-of_nav2link').show();
				jQuery('#section-of_nav2linkcurrent').show();
				jQuery('#section-of_nav2linkhover').show();
				jQuery('#section-of_nav2alink').show();
				jQuery('#section-of_nav2alinkhover').show();
				jQuery('#section-of_nav2aborder').show();
				jQuery('#section-of_nav2abg').show();
				jQuery('#section-of_nav2abghover').show();
				jQuery('#section-of_nav2alinkcurrent').show();
				jQuery('#section-of_nav2alinkbgcurrent').show();
			}		
			
			jQuery('#of_editnav3').click(function() {
				jQuery('#section-of_nav3bg').fadeToggle(400);
				jQuery('#section-of_nav3border').fadeToggle(400);
				jQuery('#section-of_nav3link').fadeToggle(400);
				jQuery('#section-of_nav3linkcurrent').fadeToggle(400);
				jQuery('#section-of_nav3linkhover').fadeToggle(400);
				jQuery('#section-of_nav3alink').fadeToggle(400);
				jQuery('#section-of_nav3alinkhover').fadeToggle(400);
				jQuery('#section-of_nav3aborder').fadeToggle(400);
				jQuery('#section-of_nav3abg').fadeToggle(400);
				jQuery('#section-of_nav3abghover').fadeToggle(400);
				jQuery('#section-of_nav3alinkcurrent').fadeToggle(400);
				jQuery('#section-of_nav3alinkbgcurrent').fadeToggle(400);
			});
			
			if (jQuery('#of_editnav3:checked').val() !== undefined) {
				jQuery('#section-of_nav3bg').show();
				jQuery('#section-of_nav3border').show();
				jQuery('#section-of_nav3link').show();
				jQuery('#section-of_nav3linkcurrent').show();
				jQuery('#section-of_nav3linkhover').show();
				jQuery('#section-of_nav3alink').show();
				jQuery('#section-of_nav3alinkhover').show();
				jQuery('#section-of_nav3aborder').show();
				jQuery('#section-of_nav3abg').show();
				jQuery('#section-of_nav3abghover').show();
				jQuery('#section-of_nav3alinkcurrent').show();
				jQuery('#section-of_nav3alinkbgcurrent').show();
			}	
		});
	</script>
<?php }