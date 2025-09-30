<?php 
/* Check if this is a photo/video custom post type archive
 * or a category page defined on theme options page
 * to display media layout; If true load archive-media.php.
 *
 * If this is a category archive, which is defined on theme
 * option page to display in 2 column format, load archive-2col.php
 *
 * If both conditions above are false, get archive-default.php
 */ 
get_header();

	if((of_get_option('of_an_2col') <> '' && is_category(explode(',', of_get_option('of_an_2col'))))
		or (of_get_option('of_an_2col_full') <> '' && is_category(explode(',', of_get_option('of_an_2col_full'))))) {
		
		get_template_part('archive', '2col');
			
	} elseif((of_get_option('of_an_3col') <> '' && is_category(explode(',', of_get_option('of_an_3col'))))
		or (of_get_option('of_an_3col_full') <> '' && is_category(explode(',', of_get_option('of_an_3col_full'))))) {

		get_template_part('archive', '3col');
			
	} elseif((of_get_option('of_an_4col') <> '' && is_category(explode(',', of_get_option('of_an_4col'))))
		or (of_get_option('of_an_4col_full') <> '' && is_category(explode(',', of_get_option('of_an_4col_full'))))) {

		get_template_part('archive', '4col');
		
	} elseif(of_get_option('of_an_media') <> '' && is_category(explode(',', of_get_option('of_an_media')))){
		
		get_template_part('archive', 'media');
		
	} elseif(of_get_option('of_an_2colslide') <> '' && is_category(explode(',', of_get_option('of_an_2colslide')))){
		if(!is_paged()) {
			get_template_part( 'archive', '2colslider' );
		} else { 
			get_template_part( 'archive', '2col' );
		}		

	} elseif(of_get_option('of_an_mag') <> '' && is_category(explode(',', of_get_option('of_an_mag')))){
		if(!is_paged()) {
			get_template_part( 'archive', 'magazine' );
		} else { 
			get_template_part( 'archive', '3col' );
		}		
		
	} else { 

		get_template_part('archive', 'default');

	}

get_footer();