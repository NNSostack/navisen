<?php 
// original code => dimox_breadcrumbs http://bit.ly/8UPWU3
function gabfire_breadcrumb() {
	
	global $post;
	$delimiter = '<span class="gabfire_bc_separator">&raquo;</span>';
	$home = __('Home', 'gabfire'); // text for the 'Home' link
 
  if ( !is_home() && !is_front_page() || is_paged() ) {
    
	
    $homeLink = home_url();
    echo "<a href='$homeLink'>$home</a>$delimiter";
 
	if ( is_category() ) {
		
		global $wp_query;
		$cat_obj = $wp_query->get_queried_object();
		$thisCat = $cat_obj->term_id;
		$thisCat = get_category($thisCat);
		$parentCat = get_category($thisCat->parent);
		
		echo "<span>" . __('Archives', 'gabfire') . "</span>$delimiter";
		
		if ($thisCat->parent != 0) {
			echo (get_category_parents($parentCat, TRUE, '' . $delimiter));
		}
		
		echo single_cat_title('', false);
 
    }
	
	elseif ( is_day() ) {
		
		echo "<span>" . __('Archives', 'gabfire') . "</span>$delimiter";
		echo "<a href='" . get_year_link(get_the_time("Y")) . "'>" . get_the_time("Y") . "</a>$delimiter";
		echo "<a href='" . get_month_link(get_the_time("Y"),get_the_time("m")) . "'>" . get_the_time("F") . "</a>$delimiter";
		echo get_the_time("d");
 
    } elseif ( is_month() ) {
      
		echo "<span>" . __('Archives', 'gabfire') . "</span>$delimiter";
		echo "<a href='" . get_year_link(get_the_time("Y")) . "'>" . get_the_time("Y") . "</a>$delimiter";
		echo get_the_time("F");
 
    } elseif ( is_year() ) {
		
		echo "<span>" . __('Archives', 'gabfire') . "</span>$delimiter";
		echo get_the_time("Y");
 
    } elseif ( is_single() && !is_attachment() ) {
		
		if ( get_post_type() != "post" ) {
			
			$post_type = get_post_type_object(get_post_type());
			$slug = $post_type->rewrite;
			echo "<span>" . $post_type->labels->singular_name ."</span>$delimiter";
			echo get_the_title();
      
		} else {
      
			$cat = get_the_category(); $cat = $cat[0];
			echo get_category_parents($cat, TRUE, "" . $delimiter);
			echo get_the_title();
		
		}
 
    } elseif ( !is_single() && !is_page() && get_post_type() != "post" ) {

		$post_type = get_post_type_object(get_post_type());
		echo $post_type->labels->singular_name;
 
    } elseif ( is_attachment() ) {
      
		echo __("Gallery", "gabfire") . $delimiter . get_the_title();
 
    } elseif ( is_page() && !$post->post_parent ) {
      
		echo get_the_title();
 
    } elseif ( is_page() && $post->post_parent ) {

		$parent_id  = $post->post_parent;
		$breadcrumbs = array();
		
		while ($parent_id) {
			$page = get_page($parent_id);
			$breadcrumbs[] = "<a href='" . get_permalink($page->ID) . "'>" . get_the_title($page->ID) . "</a>";
			$parent_id  = $page->post_parent;
		}
		
		$breadcrumbs = array_reverse($breadcrumbs);
		foreach ($breadcrumbs as $crumb) echo $crumb . " " . $delimiter . " ";
		echo get_the_title();
 
    } elseif ( is_search() ) {
      esc_attr_e('Search results for', 'gabfire'); 
	  echo ' ' . get_search_query();

    } elseif ( is_tag() ) {
      
		echo __("Posts tagged with", "gabfire") . $delimiter . single_tag_title("", false);
		
 
    } elseif ( is_author() ) {
		global $author;
		$userdata = get_userdata($author);
		$author_name = $userdata->display_name;
		printf(__('Entries posted by %1$s','gabfire'), $author_name);
 
    } elseif ( is_404() ) {
		_e("404! Page not found", "gabfire");
    }
 
    if ( get_query_var("paged") ) {
		echo __(" (Page", "gabfire") . " " . get_query_var("paged"); echo ")";
    }
  }
}