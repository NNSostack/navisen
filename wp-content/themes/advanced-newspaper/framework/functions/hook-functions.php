<?php
/* ********************************************************* */
/* ******* the_content hook
/* ********************************************************* */

	/* Post pagination hook below 
	 * single posts formats
	 * ******************************************************************* */
	 if (!function_exists('gabfire_postpagination')) {
		function gabfire_postpagination( $content ) {

			if ( in_the_loop() && is_single() ) 
			{
				$content .= wp_link_pages( array(
					'before'           => '<p class="post-pagination">' . __('<strong>Pages:</strong>','gabfire'),
					'after'            => '</p>',
					'link_before'      => '<span>',
					'link_after'       => '</span>',
					'next_or_number'   => 'number',
					'nextpagelink'     => __('Next page', 'gabfire'),
					'previouspagelink' => __('Previous page', 'gabfire'),
					'pagelink'         => '%',
					'echo'             => 0
				) );
			}
			
			return $content;
		}
	 }

	/* Source / Credit for the Entry
	 * ******************************************************************* */
	  if (!function_exists('gabfire_postcredit')) {
		function gabfire_postcredit( $content ) {
			if ( in_the_loop() && is_single() ) 
			{
				global $wp_query;
				$postid = $wp_query->post->ID;
				$source = get_post_meta($postid, 'source', true);
				$sourcelink = get_post_meta($postid, 'sourcelink', true);
				$sourcestring = '<strong>' . __('SOURCE', 'gabfire') . '</strong>';

				if ($sourcelink != '') {
					$content .= "<p class='entrysource'>$sourcestring: <a href='$sourcelink' target='_blank'>$source</a></p>";
				} elseif ($source != '') {
					$content .= "<p class='entrysource'>$sourcestring: $source</p>";
				}
			}
			return $content;
		}
	  }

	/* Add tags below content on single post pages
	 * ******************************************************************* */
	  if (!function_exists('gabfire_tags')) {
		function gabfire_tags( $content ) {
			if ( in_the_loop() && is_single() ) 
			{
				$content .= get_the_tag_list('<p class="posttags"><i class="fa fa-tags"></i>&nbsp;&nbsp;',', ', '</p>');
			}
			return $content;
		}
	  }

	/* Add Author After the Post
	 ******************************************************************** */
	  if (!function_exists('gabfire_singlepostmeta')) {
		function gabfire_singlepostmeta( $content ) {
			if ( in_the_loop() && is_single() ) 
			{
				$title = '<strong class="entry-title">'.get_the_title().'</strong>';
				$authorlink = '<a href="'.get_author_posts_url(get_the_author_meta( 'ID' )).'" rel="author" class="author vcard"><span class="fn">'.  get_the_author() . '</span></a>';
				$date = '<time class="published updated" datetime="'. get_the_date() . 'T' . get_the_time() .'">'. get_the_date() . '</time>';
				
				$content .=	'<div class="single_postmeta"><p>';
					$content .= get_avatar( get_the_author_meta('email'), '35' );
					$content .= '<p>' . sprintf(esc_attr__('%1$s added by %2$s on %3$s','gabfire'), $title, $authorlink, $date) . '<br />';
						$content .= '<a class="block" href="'. esc_url(get_author_posts_url( get_the_author_meta( 'ID' ) )).'">';
						$content .= sprintf( esc_attr__( 'View all posts by %s &rarr;', 'gabfire' ), get_the_author() );
						$content .=	'</a>';
					$content .=	'</p>';
				$content .=	'</div>';
			}
			return $content;
		}
	  }

/* ********************************************************* */
/* ******* gabfire_before_singlepost_content hook
/* ********************************************************* */
			
	/* Inner page slider
	 ******************************************************************** */
	  if (!function_exists('gabfire_innerslide_wrapper')) {
		function gabfire_innerslide_wrapper() {
			global $wp_query;
			$postid = $wp_query->post->ID;
			$disable_postslider = get_post_meta($postid, 'disable_postslider', true);
			
			if (( $post_layout !== 'bigslider') and ( $disable_postslider !== 'true')) {
				gabfire_innerslider();
			}	
		}
	  }

/* ********************************************************* */
/* ******* gabfire_after_singlepost_query hook
/* ********************************************************* */

	/* Display widgets below post content after query on single post pages
	 ******************************************************************** */
	  if (!function_exists('gabfire_single_post_widget_zones')) {
		function gabfire_single_post_widget_zones() {
			if ('post' == get_post_type()) {
				gabfire_dynamic_sidebar('postwidget');
			} elseif ( is_page_template('templates/tpl-widgetized.php') ) {
				gabfire_dynamic_sidebar('pagewidget');
			} 
		}
	  }
		
	/* ********************
	 * Post pagination hook below 
	 * single posts formats
	 ******************************************************************** */
	  if (!function_exists('gabfire_archivepage_generate_loop')) {
		function gabfire_archivepage_generate_loop() {
			echo '<article itemscope="itemscope" itemtype="http://schema.org/BlogPosting" itemprop="blogPost" class="entry entry-content">';
				$cats = get_categories();
				foreach ($cats as $cat) {
					query_posts('cat='.$cat->cat_ID);
					echo '<h3 class="page-header">'. $cat->cat_name .'</h3>';
					echo '<ul>';
						while (have_posts()) : the_post();
							echo '<li><a href="'. get_the_permalink() .'">'. get_the_title() .'</a> - ('. get_comments_number() .')</li>';
						endwhile;
					echo '</ul>';
				} 
			echo '</article>';
		}
	  }
	 
	  if (!function_exists('gabfire_archivepage_display_loop')) {
		function gabfire_archivepage_display_loop() {
			if ( is_page_template('templates/tpl-archives.php') ) 
			{
				gabfire_archivepage_generate_loop();
			}
		}
	  }

/* ********************
 * Display a link to theme options panel after
 * theme is activated
 ******************************************************************** */	
  if (!function_exists('gabfire_initial_theme_activation')) {
	if (of_get_option('of_logo_type') == '' and !is_admin() ) {
		function gabfire_initial_theme_activation() {
			echo "<p class='alert alert-info' style='width:940px;margin:20px auto'>Congratulations! You have successfully installed your theme. However, it may look incomplete at this moment. Do <strong>NOT</strong> panic as you simply need to configure your <a href='". get_option("siteurl") ."/wp-admin/themes.php?page=options-framework'>Theme Options</a>. Please go through the <a href='". get_option("siteurl") ."/wp-admin/themes.php?page=options-framework'>Theme Options</a> completely and select an option for each setting. After that, you\"re site will be ready for the world!";
		}
		add_action( 'gabfire_before_header_starts', 'gabfire_initial_theme_activation', 10 );
	}
  }