	<div class="row" id="category-woutsidebar">
		
		<main class="col-xs-12 col-md-12 col-sm-12 post-wrapper article-wrapper mag-archive" role="main" itemprop="mainContentOfPage" itemscope="itemscope" itemtype="http://schema.org/Blog">
			
			<section class="row">
				<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
					<nav class="gabfire_breadcrumb" itemprop="breadcrumb"><?php gabfire_breadcrumb(); ?></nav>
				</div>
			</section>

			<section class="row mag-firstrow">
				<div class="col-md-7 col-lg-7 col-sm-7 col-xs-12">
				<?php 
				$count = 1;
				if (have_posts()) : while (have_posts()) : the_post();
				?>
				
					<?php if($count == 1) { ?>
					<div class="mag-slider">
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

						<?php if($count <= 5 ) { ?>
						<article>
							<?php 
							gabfire_media(array(
							'name' => 'mag-slider', 
							'imgtag' => 1,
							'link' => 1,
							'enable_video' => 1, 
							'catch_image' => of_get_option('of_catch_img', 0),
							'video_id' => 'featured',
							'enable_thumb' => 1, 
							'resize_type' => 'c', 
							'media_width' => 720, 
							'media_height' => 370,
							'thumb_align' => 'alignnone',
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
						<?php if($count == 5) { ?>
						</div>
						<div class="fea-prev"><i class="fa fa-chevron-left"></i></div>
						<div class="fea-next"><i class="fa fa-chevron-right"></i></div>
					</div><!-- Magazine Slider -->				
				<?php } ?>
				
				<?php if($count == 6 ) { ?>
				</div>
		
				<div class="col-md-5 col-lg-5 col-sm-5 col-xs-12">
				<?php } ?>
					<?php if ( ($count == 6 ) or ($count == 7 ) )  { ?>
						<article itemscope="itemscope" itemtype="http://schema.org/BlogPosting" itemprop="blogPost" <?php post_class('featuredpost'); ?>>

							<h2 class="entry-title">
								<a href="<?php the_permalink() ?>" rel="bookmark" title="<?php printf( esc_attr__( 'Permalink to %s', 'gabfire' ), the_title_attribute( 'echo=0' ) ); ?>" ><?php the_title(); ?></a>
							</h2>									
						
							<?php
								gabfire_media(array(
									'name' => 'secondary-mid',
									'imgtag' => 1,
									'link' => 1,
									'enable_video' => 0,
									'catch_image' => of_get_option('of_catch_img', 0),
									'enable_thumb' => 1,
									'resize_type' => 'c',
									'media_width' => 121, 
									'media_height' => 74, 
									'thumb_align' => 'alignleft',
									'enable_default' => 0
								));
							?>				
							
							<p class="p-summary" itemprop="text"><?php echo string_limit_words(15); ?>&hellip;</p>
							
							<?php gabfire_postmeta(true,true,true,false); ?>
							
						</article><!-- .featuredpost -->
					<?php } ?>
			<?php if($count == 7) { ?>
				</div>	
			</section><!-- row -->
			
			<section id="secondarycontent" class="row">

				<div id="secondary-left" class="col-lg-2 col-md-2 col-sm-2 col-xs-12">
					<?php gabfire_dynamic_sidebar('magazine-secondaryleft');?>
				</div><!-- #secondary-left -->		

				<div id="secondary-mid" class="col-lg-6 col-md-6 col-sm-6 col-xs-7">
					<?php gabfire_dynamic_sidebar('magazine-secondarymid');?>				
			<?php }	?>				
				

				<?php if ( ($count == 8) or ($count == 9) or ($count == 10) ) { 
				$class = 'featuredpost';
				if($count == 10 ) { $class .= ' lastpost'; }				
				?>
				
				<article itemscope="itemscope" itemtype="http://schema.org/BlogPosting" itemprop="blogPost" <?php post_class($class); ?>>
					<h2 class="entry-title">
						<a href="<?php the_permalink() ?>" rel="bookmark" title="<?php printf( esc_attr__( 'Permalink to %s', 'gabfire' ), the_title_attribute( 'echo=0' ) ); ?>" ><?php the_title(); ?></a>
					</h2>		
					
					<?php
						gabfire_media(array(
							'name' => 'secondary-mid',
							'imgtag' => 1,
							'link' => 1,
							'enable_video' => 0,
							'catch_image' => of_get_option('of_catch_img', 0),
							'enable_thumb' => 1,
							'resize_type' => 'c',
							'media_width' => 121, 
							'media_height' => 74, 
							'thumb_align' => 'alignleft',
							'enable_default' => 0
						));
					?>					
					
					<p class="p-summary" itemprop="text"><?php echo string_limit_words(20); ?>&hellip;</p>
					
					<?php gabfire_postmeta(true,true,true,false); ?>
					
				</article><!-- .featuredpost -->
				<?php } ?>
				
				<?php if ($count == 10) { ?>
				<?php gabfire_dynamic_sidebar('magazine-secondarymid-2');?>
				</div><!-- #secondary-left -->
				
				<div id="secondary-right" class="col-lg-4 col-md-4 col-sm-4 col-xs-5">
					<?php gabfire_dynamic_sidebar('magazine-secondaryright-top');?>
				<?php } ?>		
		
					<?php if($count == 11) { ?>
						<article itemscope="itemscope" itemtype="http://schema.org/BlogPosting" itemprop="blogPost" <?php post_class('featuredpost'); ?>>

							<h2 class="entry-title">
								<a href="<?php the_permalink() ?>" rel="bookmark" title="<?php printf( esc_attr__( 'Permalink to %s', 'gabfire' ), the_title_attribute( 'echo=0' ) ); ?>" ><?php the_title(); ?></a>
							</h2>									
						
							<?php
								gabfire_media(array(
									'name' => 'secondary-right',
									'imgtag' => 1,
									'link' => 1,
									'enable_video' => 0,
									'catch_image' => of_get_option('of_catch_img', 0),
									'enable_thumb' => 1,
									'resize_type' => 'c',
									'media_width' => 220, 
									'media_height' => 173, 
									'thumb_align' => 'alignleft',
									'enable_default' => 0
								));
							?>				
							
							<p class="p-summary" itemprop="text"><?php echo string_limit_words(14); ?>&hellip;</p>
							
							<?php gabfire_postmeta(true,true,true,false); ?>
							
						</article><!-- .featuredpost -->
						
							<?php gabfire_dynamic_sidebar('magazine-secondaryright-bottom');?>
							
						</div><!-- #secondary-right -->

					</section><!-- #secondarycontent -->
					
					<section class="bottomads">
						
						<div class="large_ad pull-left">
							<?php gabfire_dynamic_sidebar('beforesubnews-left'); ?>
						</div>
						
						<div class="small_ad pull-right">
							<?php gabfire_dynamic_sidebar('beforesubnews-right'); ?>
						</div>
						
					</section>
					
					<section class="row archive-4col">
					<div class="col-md-12">
					<?php } ?>
					
					<?php if ( ($count == 12) or ($count == 13) or ($count == 14) or ($count == 15) ) { 
					if ($count == 15) { 
						$class = 'entry everyfourth';
					} elseif ($count == 13) { 
						$class = 'entry everysecond';
					} else {
						$class = 'entry';
					}					
					?>
					
						<article <?php post_class($class); ?>>
						
								<h2 class="entry-title" itemprop="headline">
									<a href="<?php the_permalink() ?>" rel="bookmark" title="<?php printf( esc_attr__( 'Permalink to %s', 'gabfire' ), the_title_attribute( 'echo=0' ) ); ?>" ><?php the_title(); ?></a>
								</h2>
								
								
								<?php 
								gabfire_media(array(
									'name' => 'loop-4col',
									'imgtag' => 1,
									'link' => 1,
									'enable_video' => 1, 
									'video_id' => 'z-archive',
									'enable_thumb' => 1, 
									'resize_type' => 'c', 
									'media_width' => 570, 
									'media_height' => 293, 
									'thumb_align' => 'aligncenter',
									'enable_default' => 0
								)); 
								?>						

								<p class="p-summary" itemprop="text"><?php echo string_limit_words(12); ?>&hellip;</p>
								
								<p class="postmeta">				
									<span>
										<?php
										$authorlink = '<a href="'.get_author_posts_url(get_the_author_meta( 'ID' )).'" rel="author" class="author vcard"><span class="fn">'.  get_the_author() . '</span></a>';
										$date = '<time class="published updated" datetime="'. get_the_date() . 'T' . get_the_time() .'">'. get_the_date() . '</time>&nbsp;&nbsp;';
										printf(esc_attr__('By %1$s on %2$s','gabfire'), $authorlink, $date); ?>
									</span>

									<?php edit_post_link('Edit', '<span><i class="fa fa-pencil-square-o"></i>', '</span>'); ?>
								</p>								
						</article>
					
					<?php } ?>
					
					<?php if($count == 15) { ?>
					</div>
					</section>
					<?php } ?>

			<?php $count++; endwhile; else: endif;
			
			gabfire_archivepagination();
			wp_reset_query(); ?>
			
		</main><!-- col-md-12 -->
	</div><!-- articles-wrapper -->
