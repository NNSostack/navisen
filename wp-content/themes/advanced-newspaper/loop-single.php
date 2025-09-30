<?php
/* Single Post, Page and Custom Post Loop */

/* Post layout was called in single.php
 * define media width/height based on post layout
 */
 ?>
	
		<section class="article-wrapper">
		
			<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
			
				<article itemscope="itemscope" itemtype="http://schema.org/BlogPosting" itemprop="blogPost" <?php post_class('entry'); ?>>
										
					<?php
					/**
					 * gabfire_before_singlepost_content @hooked functions
					 *
					 * gabfire_singlepostshare		5
					 * gabfire_innerslide_wrapper	6
					 * gabfire_singlepostmedia		7

					 */
					do_action( 'gabfire_before_singlepost_content' );
					
					/**
					 * the_content @hooked functions
					 *
					 * gabfire_postpagination	5
					 * gabfire_postcredit		6
					 * gabfire_tags				7
					 * gabfire_singlepostmeta	8
					 */
					echo '<div class="entry-content" itemprop="text">'; the_content(); echo '</div>';
					 					 
					?>
				</article>
				
			<?php endwhile; else : endif; ?>

			<?php
			/**
			 * gabfire_after_singlepost_query @hooked functions
			 *
			 * gabfire_single_post_widget_zones		10
			 * gabfire_archivepage_display_loop		15
			 */
			do_action( 'gabfire_after_singlepost_query' );
			
			comments_template();
			?>
				
		</section><!-- articles-wrapper -->