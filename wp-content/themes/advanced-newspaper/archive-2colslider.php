	<div class="row" id="category-withsidebar">
		
		<main class="col-xs-12 col-md-8 col-sm-8 post-wrapper" role="main" itemprop="mainContentOfPage" itemscope="itemscope" itemtype="http://schema.org/Blog">
			
			<div class="row">
				<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
					<nav class="gabfire_breadcrumb" itemprop="breadcrumb"><?php gabfire_breadcrumb(); ?></nav>
				</div>
			</div>

			<?php 
			$count = 1;
			if (have_posts()) : while (have_posts()) : the_post();
			?>
			
				<?php if($count == 1) { ?>
				<div class="slider-2col">
					<?php if (of_get_option('of_th_featimeout') == '0' ) { 
						$timeout = "999999";
					} else { 
						$timeout = of_get_option('of_th_featimeout');
					} ?>
					<div class="cycle-slideshow" 
						data-cycle-fx="fade" 
						data-cycle-timeout="<?php echo $timeout.'000'; ?>"
						data-cycle-slides="> article"
						data-cycle-prev=".fea-prev" 
						data-cycle-next=".fea-next"
						data-cycle-manual-fx="scrollHorz"
						data-cycle-manual-speed="300"
					><?php } ?>

					<?php if($count <= 3 ) { ?>
					<article>
						<?php 
						gabfire_media(array(
						'name' => 'loop-default', 
						'imgtag' => 1,
						'link' => 1,
						'enable_video' => 1, 
						'catch_image' => of_get_option('of_catch_img', 0),
						'video_id' => 'featured',
						'enable_thumb' => 1, 
						'resize_type' => 'c', 
						'media_width' => 701, 
						'media_height' => 360, 
						'thumb_align' => 'alignnone featured-img',
						'enable_default' => 0
						));
						?>
						
						<div class="mag-caption">
							<h2 class="entry-title">
								<a href="<?php the_permalink() ?>" rel="bookmark" title="<?php printf( esc_attr__( 'Permalink to %s', 'gabfire' ), the_title_attribute( 'echo=0' ) ); ?>" >
									<?php the_title(); ?>
								</a>								
							</h2>
						</div>
					</article>
					<?php } ?>		
					<?php if($count == 3) { ?>
					</div>
					<div class="fea-prev"><i class="fa fa-chevron-left"></i></div>
					<div class="fea-next"><i class="fa fa-chevron-right"></i></div>
				</div><!-- Magazine Slider -->				
			<?php } ?>
				
			<?php if($count == 4 ) { ?>
				<div class="below_slider_mag">

					<article>
						<div class="pull-left">
							<?php 
							gabfire_media(array(
								'name' => '2col-slider', 
								'imgtag' => 1,
								'link' => 1,
								'enable_video' => 1, 
								'catch_image' => of_get_option('of_catch_img', 0),
								'video_id' => 'featured', 
								'enable_thumb' => 1,
								'resize_type' => 'c', 
								'media_width' => 225, 
								'media_height' => 195, 
								'thumb_align' => 'alignnone',
								'enable_default' => 0
							)); 
							?>
						</div>
						
						<div class="pull-right">
							<h2 class="entry-title">
								<a href="<?php the_permalink() ?>" rel="bookmark" title="<?php printf( esc_attr__( 'Permalink to %s', 'gabfire' ), the_title_attribute( 'echo=0' ) ); ?>" >
									<?php the_title(); ?>
								</a>
							</h2>
							
							<p class="postmeta">				
								<span>
									<?php
									$authorlink = '<a href="'.get_author_posts_url(get_the_author_meta( 'ID' )).'" rel="author" class="author vcard"><span class="fn">'.  get_the_author() . '</span></a>';
									$date = '<time class="published updated" datetime="'. get_the_date() . 'T' . get_the_time() .'">'. get_the_date() . '</time>&nbsp;&nbsp;';
									printf(esc_attr__('By %1$s on %2$s','gabfire'), $authorlink, $date); ?>
								</span>
								
								<span><i class="fa fa-folder-o"></i> <?php the_category(', '); ?>&nbsp;&nbsp;</span>
								<?php edit_post_link('Edit', '<span><i class="fa fa-pencil-square-o"></i>', '</span>'); ?>
							</p>	
							
							<p class="p-summary" itemprop="text"><?php echo string_limit_words(35); ?>&hellip;</p>

							<a href="<?php the_permalink() ?>" rel="bookmark" title="<?php printf( esc_attr__( 'Permalink to %s', 'gabfire' ), the_title_attribute( 'echo=0' ) ); ?>" class="readmore">
								<i class="fa fa-chevron-right"></i> <?php _e('CONTINUE READING','gabfire'); ?>
							</a>
						</div>				
						<div class="clearfix"></div>
					</article>

				</div><!-- belowfea_secondcol -->
				<div class="article-wrapper archive-mag">		
			<?php }	?>				
				
			
			<?php if($count > 4) { ?>
				<article <?php post_class('entry'); ?>>

						<h2 class="entry-title" itemprop="headline">
							<a href="<?php the_permalink() ?>" rel="bookmark" title="<?php printf( esc_attr__( 'Permalink to %s', 'gabfire' ), the_title_attribute( 'echo=0' ) ); ?>" ><?php the_title(); ?></a>
						</h2>
						
						<p class="postmeta">				
							<span>
								<?php
								$authorlink = '<a href="'.get_author_posts_url(get_the_author_meta( 'ID' )).'" rel="author" class="author vcard"><span class="fn">'.  get_the_author() . '</span></a>';
								$date = '<time class="published updated" datetime="'. get_the_date() . 'T' . get_the_time() .'">'. get_the_date() . '</time>&nbsp;&nbsp;';
								printf(esc_attr__('By %1$s on %2$s','gabfire'), $authorlink, $date); ?>
							</span>
							
							<span><i class="fa fa-folder-o"></i> <?php the_category(', '); ?>&nbsp;&nbsp;</span>
							<?php edit_post_link('Edit', '<span><i class="fa fa-pencil-square-o"></i>', '</span>'); ?>
						</p>

						<?php 
						gabfire_media(array(
							'name' => 'loop-2col',
							'imgtag' => 1,
							'link' => 1,
							'enable_video' => 1, 
							'video_id' => 'z-archive',
							'enable_thumb' => 1, 
							'resize_type' => 'c', 
							'media_width' => 570, 
							'media_height' => 320, 
							'thumb_align' => 'aligncenter',
							'enable_default' => 0
						)); 
						?>
						<p class="p-summary" itemprop="text"><?php echo string_limit_words(20); ?>&hellip;</p>
				</article>
			<?php 
			}
				
			if ($count > 4 and ($count % 2 == 0) ) { echo "<div class='clearfix'></div>"; }
			$count++; endwhile; else: endif;
			
			gabfire_archivepagination();
			wp_reset_query(); ?>
			</div><!-- articles-wrapper -->
		
		</main><!-- col-md-8 -->
		
		<?php get_sidebar(); ?>
		
	</div>	
