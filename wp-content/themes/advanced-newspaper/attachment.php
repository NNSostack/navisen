<?php get_header(); ?>
	
	<div class="row">
		
		<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
		<main class="col-xs-12 col-md-8 col-sm-8 post-wrapper" role="main" itemprop="mainContentOfPage" itemscope="itemscope" itemtype="http://schema.org/Blog">

			<div class="row">
				<div class="col-lg-12 col-md-12">
					<div class="post-lead">
						<h1 class="entry-title single-post-title" itemprop="headline">
							<?php
							$parent_title = get_the_title($post->post_parent);
							echo $parent_title;
							?>
						</h1>
						
						<?php 
						gabfire_postmeta(true,true,true,false,false);
						$thumb_img = get_post( get_post_thumbnail_id() ); 
						if ($thumb_img->post_excerpt != '') { 
							echo "<p class='subtitle'>$thumb_img->post_excerpt</p>";
						}
						?>
					</div>		
				</div>
			</div>			
			
			<section class="article-wrapper">
				<article itemscope="itemscope" itemtype="http://schema.org/BlogPosting" itemprop="blogPost" <?php post_class('entry'); ?>>
					<?php if($social !== 'true') { ?>
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
						
							<a href="http://www.facebook.com/sharer.php?u=<?php echo get_permalink();?>&t=<?php echo get_the_title(); ?>" data-toggle="tooltip" title="<?php _e('Share to Facebook', 'gabfire'); ?>" rel="nofollow"><span><?php _e('Friend on Facebook','gabfire'); ?></span>
								<i class="fa fa-facebook pull-left"></i>
							</a>

							<a href="http://twitter.com/home?status=<?php echo get_the_title() .' => '.get_permalink(); ?>" data-toggle="tooltip" title="<?php _e('Share on Twitter', 'gabfire'); ?>" rel="nofollow"><span><?php _e('Follow on Facebook','gabfire'); ?></span>
								<i class="fa fa-twitter pull-left"></i>
							</a>

							<a href="https://plus.google.com/share?url=<?php echo get_permalink();?>" data-toggle="tooltip" title="<?php _e('Share on Google+', 'gabfire'); ?>" rel="nofollow"><span><?php _e('Add to Google+ Circle','gabfire'); ?></span>
								<i class="fa fa-google-plus pull-left"></i>
							</a>

							<a href="http://www.linkedin.com/shareArticle?mini=true&url=<?php echo get_permalink() ?>&title=<?php echo get_the_title(); ?>&summary=&source=<?php echo get_bloginfo('name'); ?>" data-toggle="tooltip" title="<?php _e('Share on LinkedIn', 'gabfire'); ?>" rel="nofollow"><span><?php _e('Connect on Linked in','gabfire'); ?></span>
								<i class="fa fa-linkedin pull-left"></i>
							</a>

							<a class="pull-right" href="<?php echo of_get_option('of_an_email'); ?>" data-toggle="tooltip" title="<?php _e('Send by Email', 'gabfire'); ?>" rel="nofollow"><span><?php _e('Subscribe by Email','gabfire'); ?></span>
								<i class="fa fa-envelope-o pull-left"></i>
							</a>
							
							<a class="pull-right" href="<?php echo of_get_option('of_an_email'); ?>" data-toggle="tooltip" title="<?php _e('Send by Email', 'gabfire'); ?>" rel="nofollow"><span><?php _e('Subscribe by Email','gabfire'); ?></span>
								<i class="fa fa-print pull-left"></i>
							</a>
						</div>
						<div class="clearfix"></div>
					<?php } ?>		
				
				
					<?php $image = wp_get_attachment_image_src( $attachment->id, "sb-postfull-nosidebar"); ?>
					<img src="<?php echo $image[0]; ?>" alt="<?php the_title(); ?>" class="attachment-full" />
									
					<div class="attachment-nav">
						<?php previous_image_link( false, __('&laquo; Previous Image','gabfire')); ?> <a href="<?php echo get_permalink($post->post_parent); ?>"><?php _e('Back to Post','gabfire'); ?></a> <?php next_image_link( false, __('Next Image &raquo;','gabfire')); ?>
					</div>
					
					<div class="gallery attachment-gallery">
						<?php
							$count = 1;
							$args = array(
							'post_type' => 'attachment',
							'numberposts' => -1,
							'order' => 'ASC',
							'post_parent' => $post->post_parent);
							$attachments = get_posts($args);

							if ($attachments){
								foreach ($attachments as $attachment){
									echo '<dl class="gallery-item"><dt class="gallery-icon">';
									echo '<a href="'.get_attachment_link($attachment->ID, false).'">'.wp_get_attachment_image($attachment->ID, 'thumbnail').'</a>';
									echo '</dt></dl>';
									$count++;
								}
							}
						?>
					</div>
				</article>
			<?php comments_template(); ?>
			</section>
		</main><!-- col-md-8 -->
		<?php endwhile; else : endif; ?>
		
		<?php get_sidebar(); ?>
	</div>	
	
	
<?php get_footer(); ?>