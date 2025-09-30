		<?php
		$count = 1;
		if (have_posts()) : while (have_posts()) : the_post();
		if ($count % 3 == 0) {
			$postclass = 'entry pull-left nomarginright';
		} else { 
			$postclass = 'entry pull-left';
		}
		?>
		
			<?php if ((!is_paged()) and ($count < 4)) { ?>
			
			<?php if($count == 1) { ?>
			<div class="inner-cycle">
				<div class="cycle-slideshow" 
				<?php if(of_get_option('of_inner_rotate') == 1) { ?>
					data-cycle-pause-on-hover="true"
					data-cycle-timeout="<?php echo of_get_option('of_inner_speed'); ?>000"
				<?php } else { ?>
					data-cycle-timeout="0"
				<?php } ?>
				data-cycle-loader="true"
				data-cycle-pager=".template-pager"
				data-cycle-slides="> article"
				data-cycle-prev=".innerslider_prev"
				data-cycle-next=".innerslider_next"
				>	
			<?php } ?>
				<article <?php post_class(); ?>>
				<?php
				gabfire_media(array(	
					'name' => 'loopmedia-big', 
					'imgtag' => 1,
					'link' => 1,
					'enable_video' => 0, 
					'video_id' => 'postfull',
					'enable_thumb' => 1, 
					'resize_type' => 'c', 
					'media_width' => 1047, 
					'media_height' => 450, 
					'thumb_align' => '',
					'enable_default' => 0
				)); 
				?>
				
					<div class="postcaption">
						<h2 class="entry-title"><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php printf( esc_attr__( 'Permalink to %s', 'gabfire' ), the_title_attribute( 'echo=0' ) ); ?>"><?php the_title(); ?></a></h2>
						<div class="hidden-xs">
							<?php gabfire_postmeta(true,true,false,false,true); ?>
						</div>									
						<p class="hidden-xs"><?php echo string_limit_words(get_the_excerpt(), 10); ?></p>
					</div>
				</article>				
			<?php if( $count == 3) { ?>
			</div>
			<div class="template-pager"></div>
			
			<div class="prevnext_controls">
				<a class="innerslider_prev"><img src="<?php echo get_template_directory_uri(); ?>/images/arrow-prev.png" alt="<?php _e('Previous','gabfire'); ?>" /></a>
				<a class="innerslider_next"><img src="<?php echo get_template_directory_uri(); ?>/images/arrow-next.png" alt="<?php _e('Next','gabfire'); ?>" /></a>
			</div>
			</div><!-- cycle-slideshow -->
			
			<p class="catname"><span><?php _e('MORE ON','gabfire'); ?> <?php single_cat_title(); ?></span></p>
			<?php } ?>
			
			
			<?php } else { ?>
				
				<article <?php post_class($postclass); ?>>	
					<?php
						gabfire_media(array(	
							'name' => 'loopmedia-small', 
							'imgtag' => 1,
							'link' => 1,
							'enable_video' => 1, 
							'enable_thumb' => 1, 
							'resize_type' => 'c', 
							'media_width' => 378, 
							'media_height' => 237, 
							'thumb_align' => 'aligncenter',
							'enable_default' => 0
						)); 
						?>
						
						<h2 class="entry-title">
							<a href="<?php the_permalink() ?>" rel="bookmark" title="<?php printf( esc_attr__( 'Permalink to %s', 'gabfire' ), the_title_attribute( 'echo=0' ) ); ?>" ><?php the_title(); ?></a>
						</h2>
						
						<?php gabfire_postmeta(true,true,false,false,false); ?>
				</article>
				<?php if ($count % 3 == 0) {  echo '<div class="clearfix"></div>'; } ?>
			<?php } ?>
		<?php $count++; endwhile; else: endif; ?>