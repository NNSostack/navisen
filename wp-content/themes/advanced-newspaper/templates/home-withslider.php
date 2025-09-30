<section id="primarycontent" class="row">

	<div id="primary-left" class="col-lg-6 col-sm-12">
		
		<div class="row">
			<div class="col-lg-12 col-md-12 col-sm-7">
			
				<?php if (intval(of_get_option('of_an_nrfea')) > 0 ) { ?>
					<div id="featured-slider" class="test">
						<?php 
						if (of_get_option('of_an_featimeout') == 0) {
							$featimeout = 0;
						} else { 
							$featimeout = of_get_option('of_an_featimeout') . '000';
						} ?>		
					
						<div class="cycle-slideshow" 
							data-cycle-fx="fade" 
							data-cycle-timeout="<?php echo $featimeout; ?>"
							data-cycle-slides="> article"
							data-cycle-pager="#featured-nav"
							data-cycle-pager-template=""
							>
							<?php 
							$do_not_duplicate = '';
							$count = 1;
							
							if (of_get_option('of_an_featypeadvanced') == 'mrecent') {
								$args = array(
								  'posts_per_page' => of_get_option('of_an_nrfea'),
								);
							}
							elseif (of_get_option('of_an_featypeadvanced') == 'tagbased') {
								$args = array(
								  'posts_per_page' => of_get_option('of_an_nrfea'),
								  'tag_id' => of_get_option('of_an_fea_tag')
								);
							}
							elseif (of_get_option('of_an_featypeadvanced') == 'cfbased') {
								$args = array(
								  'post_type' => 'any',
								  'posts_per_page' => of_get_option('of_an_nrfea'),
								  'meta_key' => 'featured', 
								  'meta_value' => 'true'
								);
							}				
							else {
								$args = array(
								  'posts_per_page' => of_get_option('of_an_nrfea', 6),
								  'cat' => of_get_option('of_an_fea_cat', 1)
								);
							} 
							$wp_query = new WP_Query(); $wp_query->query($args); 
							while ($wp_query->have_posts()) : $wp_query->the_post();  			
							if (of_get_option('of_dnd') == 1) { $do_not_duplicate[] = $post->ID; }
							$gab_iframe = get_post_meta($post->ID, 'iframe', true);
							$video_mp4 = get_post_meta($post->ID, 'video-mp4', true);
							$video_webm = get_post_meta($post->ID, 'video-webm', true);
							$video_ogv = get_post_meta($post->ID, 'video-ogv', true);							
							?>
							
							<article itemscope="itemscope" itemtype="http://schema.org/BlogPosting" itemprop="blogPost" class="slide_item">
								<?php 
									gabfire_media(array(
										'name' => 'featured',
										'imgtag' => 1,
										'link' => 1,
										'enable_video' => 1, 
										'enable_thumb' => 1, 
										'resize_type' => 'c',
										'media_width' => 696, 
										'media_height' => 377, 
										'thumb_align' => 'media', 
										'enable_default' => 1,
										'default_name' => 'featured.jpg'
									)); 										
								?>
								
								<div class="postcaption">
									<h2 class="entry-title">
										<a href="<?php the_permalink() ?>" rel="bookmark" title="<?php printf( esc_attr__( 'Permalink to %s', 'gabfire' ), the_title_attribute( 'echo=0' ) ); ?>" ><?php the_title(); ?></a>
									</h2>
									<p class="p-summary" itemprop="text"><?php echo string_limit_words(10); ?>&hellip;</p>
								</div>
								
								<?php $format = get_post_format(); ?>
								
								<?php if ($format == 'video' ) { ?>								
									<span class="entry-title postformatfeatured"><i class="fa fa-file-video-o"></i></span>
								<?php } elseif($format == 'gallery') { ?>
									<span class="entry-title postformatfeatured"><i class="fa fa-camera-retro"></i></span>
								<?php } elseif ($format == 'audio') { ?>
									<span class="entry-title postformatfeatured"><i class="fa fa-file-audio-o"></i></span>
								<?php } ?>

							</article><!-- .slide-item -->

							<?php $count++; endwhile; wp_reset_query(); ?>
						</div><!-- .cycle-slideshow -->
						
						<div id="fea-nav">
							<ul id="featured-nav" class="cycle-pager external">
								<?php
								$count = 1;
								$wp_query = new WP_Query(); $wp_query->query($args); 
								while ($wp_query->have_posts()) : $wp_query->the_post();  if (of_get_option('of_dnd') == 1) { $do_not_duplicate[] = $post->ID; }		
								?>
									<li <?php if($count % 6 == 0) { echo 'class="last"'; } ?>>
										<?php 
										gabfire_media(array(
											'name' => 'featured-thumbs', 
											'imgtag' => 1,
											'link' => 0,
											'enable_video' => 0, 
											'enable_thumb' => 1, 
											'resize_type' => 'c',
											'media_width' => 123, 
											'media_height' => 70, 
											'thumb_align' => 'fea_thumb', 
											'enable_default' => 1,
											'default_name' => 'feathumb.jpg'
										)); 										
										?>
									</li>
								<?php $count++; endwhile; wp_reset_query(); ?>
							</ul>
							<div class="clearfix"></div>
						</div><!-- /nav -->	
						
					</div><!-- #featured-slider -->
				<?php } ?>
				
			</div>

			<div id="below-slider" class="col-lg-12 col-md-12 col-sm-5 col-xs-12">
				<?php gabfire_dynamic_sidebar('primaryleft');?>
				
				<?php if (intval(of_get_option('of_an_nr1', 2)) > 0 ) { ?>
					<p class="catname">
						<?php $catid = of_get_option('of_an_cat1'); ?>
						<span><a data-toggle="tooltip" title="<?php printf(esc_attr__('View all posts filed under %1$s','gabfire'), get_cat_name($catid)); ?>"  href="<?php echo get_category_link($catid);?>"><?php echo get_cat_name($catid); ?></a></span>
					</p>
				
					<div class="gabfire_entries">
						<?php
						$count = 1;
						$args = array(
						 'post__not_in'=> $do_not_duplicate,
						  'posts_per_page' => of_get_option('of_an_nr1', 2),
						  'cat' => of_get_option('of_an_cat1', 1)
						);
						$wp_query = new WP_Query(); $wp_query->query($args); 
						while ($wp_query->have_posts()) : $wp_query->the_post();
						if (of_get_option('of_dnd') == 1) { $do_not_duplicate[] = $post->ID; }	
						?>
						
							<article itemscope="itemscope" itemtype="http://schema.org/BlogPosting" itemprop="blogPost" <?php post_class('featuredpost'); ?>>

								<?php
									gabfire_media(array(
										'name' => 'below-featured',
										'imgtag' => 1,
										'link' => 1,
										'enable_video' => 0,
										'catch_image' => of_get_option('of_catch_img', 0),
										'enable_thumb' => 1,
										'resize_type' => 'c',
										'media_width' => 119, 
										'media_height' => 89, 
										'thumb_align' => 'alignleft hidden-sm',
										'enable_default' => 0
									));
								?>

								<h2 class="entry-title">
									<a href="<?php the_permalink() ?>" rel="bookmark" title="<?php printf( esc_attr__( 'Permalink to %s', 'gabfire' ), the_title_attribute( 'echo=0' ) ); ?>" ><?php the_title(); ?></a>
								</h2>							
								
								<p class="p-summary" itemprop="text">
									<?php echo string_limit_words(21); ?>&hellip;
								</p>
								
								<?php gabfire_postmeta(true,true,true,false); ?>
								
							</article><!-- .featuredpost -->
						<?php $count++; endwhile; wp_reset_query(); ?>
					</div>
				<?php } ?>
				<?php gabfire_dynamic_sidebar('primaryleft-2');?>
			</div>

		</div>
	</div><!-- #primary-left -->
				
	<div class="visible-sm visible-xs primarymidtopline"></div>
				
	<div id="primary-mid" class="col-lg-4 col-md-4 col-sm-9 col-xs-8">
	
		<div class="row">
			<div class="col-lg-12 col-md-12 col-sm-12">
				
			
				<?php gabfire_dynamic_sidebar('primarymid');?>
		
				<?php if (intval(of_get_option('of_an_nr2', 4)) > 0 ) { ?>
				
					<p class="catname">
						<?php $catid = of_get_option('of_an_cat2'); ?>
						<span><a data-toggle="tooltip" title="<?php printf(esc_attr__('View all posts filed under %1$s','gabfire'), get_cat_name($catid)); ?>"  href="<?php echo get_category_link($catid);?>"><?php echo get_cat_name($catid); ?></a></span>
					</p>
						
					<?php
					$count = 1;
					$args = array(
					 'post__not_in'=> $do_not_duplicate,
					  'posts_per_page' => of_get_option('of_an_nr2', 4),
					  'cat' => of_get_option('of_an_cat2')
					);
					$wp_query = new WP_Query(); $wp_query->query($args); 
					while ($wp_query->have_posts()) : $wp_query->the_post();
					if (of_get_option('of_dnd') == 1) { $do_not_duplicate[] = $post->ID; }
					
					$postclass = "featuredpost";
					if($count == of_get_option('of_an_nr2', 4)) {
						$postclass .= " lastpost";
					}
					if($count % 2 == 0) {
						$postclass .= " even";
					}
					?>
			
						<article itemscope="itemscope" itemtype="http://schema.org/BlogPosting" itemprop="blogPost" <?php post_class($postclass); ?>>

							<h2 class="entry-title">
								<a href="<?php the_permalink() ?>" rel="bookmark" title="<?php printf( esc_attr__( 'Permalink to %s', 'gabfire' ), the_title_attribute( 'echo=0' ) ); ?>" ><?php the_title(); ?></a>
							</h2>									
						
							<?php
								gabfire_media(array(
									'name' => 'featured-right',
									'imgtag' => 1,
									'link' => 1,
									'enable_video' => 0,
									'catch_image' => of_get_option('of_catch_img', 0),
									'enable_thumb' => 1,
									'resize_type' => 'c',
									'media_width' => 86, 
									'media_height' => 54, 
									'thumb_align' => 'alignleft',
									'enable_default' => 0
								));
							?>				
							
							<p class="p-summary" itemprop="text"><?php echo string_limit_words(10); ?>&hellip;</p>
							
							<?php gabfire_postmeta(true,true,true,false); ?>
							
						</article><!-- .featuredpost -->
						
						<?php if ($count % 2 == 0) { echo '<div class="clearfix visible-sm"></div>'; } ?>
					<?php $count++; endwhile; wp_reset_query(); ?>
					
				<?php } ?>
				<?php gabfire_dynamic_sidebar('primarymid-2');?>
			</div><!-- .col-X-->
		</div><!-- .row -->
	</div><!-- #primary-mid -->
	
	<div id="primary-right" class="col-lg-2 col-md-2 col-sm-3 col-xs-4">
		<?php gabfire_dynamic_sidebar('primaryright'); ?>
	</div><!-- primary-right -->	
</section><!-- #primary-content -->

<section id="secondarycontent" class="row">

	<div id="secondary-left" class="col-lg-2 col-md-2 col-sm-2 col-xs-12">
		<?php gabfire_dynamic_sidebar('secondaryleft');?>
		
		<?php if (intval(of_get_option('of_an_nr3', 9)) > 0 ) { ?>
			<p class="catname">
				<?php $catid = of_get_option('of_an_cat3'); ?>
				<span><a href="<?php echo get_category_link($catid);?>" data-toggle="tooltip" title="<?php printf(esc_attr__('View all posts filed under %1$s','gabfire'), get_cat_name($catid) ); ?>"><?php echo get_cat_name($catid); ?></a></span>
			</p>
			
			<div class="widget">
				<ul>	
					<?php
					$count = 1;
					$args = array(
					 'post__not_in'=> $do_not_duplicate,
					  'posts_per_page' => of_get_option('of_an_nr3', 9),
					  'cat' => of_get_option('of_an_cat3')
					);
					$wp_query = new WP_Query(); $wp_query->query($args); 
					while ($wp_query->have_posts()) : $wp_query->the_post();  if (of_get_option('of_dnd') == 1) { $do_not_duplicate[] = $post->ID; }
					?>	
						<li><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php printf( esc_attr__( 'Permalink to %s', 'gabfire' ), the_title_attribute( 'echo=0' ) ); ?>" ><?php the_title(); ?></a></li>
					<?php $count++; endwhile; wp_reset_query(); ?>
				</ul>
			</div><!-- .widget -->
		<?php } ?>
		
	</div><!-- #secondary-left -->
	
	<div id="secondary-mid" class="col-lg-6 col-md-6 col-sm-6 col-xs-7">
		<?php gabfire_dynamic_sidebar('secondarymid');?>
		
		<?php if (intval(of_get_option('of_an_nr4', 4)) > 0 ) { ?>
			<p class="catname">
				<?php $catid = of_get_option('of_an_cat4'); ?>
				<span><a data-toggle="tooltip" title="<?php printf(esc_attr__('View all posts filed under %1$s','gabfire'), get_cat_name($catid)); ?>"  href="<?php echo get_category_link($catid);?>"><?php echo get_cat_name($catid); ?></a></span>
			</p>
			
			<?php
			$count = 1;
			$args = array(
			 'post__not_in'=> $do_not_duplicate,
			  'posts_per_page' => of_get_option('of_an_nr4', 4),
			  'cat' => of_get_option('of_an_cat4', 1)
			);
			$wp_query = new WP_Query(); $wp_query->query($args); 
			while ($wp_query->have_posts()) : $wp_query->the_post();
			if (of_get_option('of_dnd') == 1) { $do_not_duplicate[] = $post->ID; }	
			
			$class = 'featuredpost';
			if($count == of_get_option('of_an_nr4', 4)) { $class .= ' lastpost'; }
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
					
					<p class="p-summary" itemprop="text"><?php echo string_limit_words(28); ?>&hellip;</p>
					
					<?php gabfire_postmeta(true,true,true,false); ?>
					
				</article><!-- .featuredpost -->
			<?php $count++; endwhile; wp_reset_query(); ?>
			
		<?php } ?>		
		
		<?php gabfire_dynamic_sidebar('secondarymid-2');?>
	</div><!-- #secondary-mid -->
	
	<div id="secondary-right" class="col-lg-4 col-md-4 col-sm-4 col-xs-5">
		
		<?php gabfire_dynamic_sidebar('secondaryright-top');?>
		
		<?php if (intval(of_get_option('of_an_nr5', 1)) > 0 ) { ?>
			
			<p class="catname">
				<?php $catid = of_get_option('of_an_cat5'); ?>
				<span><a data-toggle="tooltip" title="<?php printf(esc_attr__('View all posts filed under %1$s','gabfire'), get_cat_name($catid)); ?>"  href="<?php echo get_category_link($catid);?>"><?php echo get_cat_name($catid); ?></a></span>
			</p>

			<?php
			$count = 1;
			$args = array(
			 'post__not_in'=> $do_not_duplicate,
			  'posts_per_page' => of_get_option('of_an_nr5', 1),
			  'cat' => of_get_option('of_an_cat5')
			);
			$wp_query = new WP_Query(); $wp_query->query($args); 
			while ($wp_query->have_posts()) : $wp_query->the_post();
			if (of_get_option('of_dnd') == 1) { $do_not_duplicate[] = $post->ID; }	
			?>
			
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
					
					<p class="p-summary" itemprop="text"><?php echo string_limit_words(18); ?>&hellip;</p>
					
					<?php gabfire_postmeta(true,true,true,false); ?>
					
				</article><!-- .featuredpost -->
			<?php $count++; endwhile; wp_reset_query(); ?>
			
		<?php } ?>
		
		<?php gabfire_dynamic_sidebar('secondaryright-bottom');?>
		
	</div><!-- #secondary-right -->

</section><!-- #secondarycontent -->

<?php /* Call media slider if the number of entries are set greater than 0 on theme control panel */
$postnr = of_get_option('of_an_tabnr1') + of_get_option('of_an_tabnr2') + of_get_option('of_an_tabnr3') + of_get_option('of_an_tabnr4') + of_get_option('of_an_tabnr5');
if (intval($postnr) > 0 ) { ?>

	<section id="mid-slider">

		<div class="row">
			<div class="col-lg-12">
				<div class="mid-slider-nav pull-left">
					<ul class="mid-slider-pagination pull-left">
						<?php
						$cat_count = 5;
						$options = array();
						for ($i=1; $i<=$cat_count;$i++) {
							if(of_get_option('of_an_tabnr'.$i) !== '0' ) {
								if (intval(of_get_option('of_an_tabnr'.$i)) > 0 ) { ?>
									<li><?php echo get_cat_name(of_get_option('of_an_tabcat'.$i)); ?></li><?php 
								} 
							} 
						}
						?>
					</ul>
					
					<div class="pagination-arrows pull-right">
						<a class="media_prev" href="#"><i class="fa fa-arrow-circle-left"></i></a>
						<a class="media_next" href="#"><i class="fa fa-arrow-circle-right"></i></a>
					</div>
				</div><!-- /nav -->
			</div><!-- /col-lg-12 -->
		</div><!-- /row -->

		<?php 
		if (of_get_option('of_an_featimeout') == 0) {
			$featimeout = 0;
		} else { 
			$featimeout = of_get_option('of_an_featimeout') . '000';
		} ?>		

		<div class="row">
			<div class="col-lg-12">
				<div class="cycle-slideshow" 
					data-cycle-fx="fade" 
					data-cycle-timeout="<?php echo $featimeout; ?>"
					data-cycle-slides="> div"
					data-cycle-prev=".media_prev"
					data-cycle-next=".media_next"				
					data-cycle-pager=".mid-slider-pagination"
					data-cycle-pager-template="">
					
					<?php 
					$cat_count = 5;
					$options = array();

					for ($i=1; $i<=$cat_count;$i++) {
						if(0 < strlen($variable = of_get_option('of_an_tabnr'.$i))) {
							$options[$i]['posts_per_page'] = $variable;
						}
						if(0 < strlen($variable = of_get_option('of_an_tabcat'.$i))) {
							$options[$i]['cat'] = $variable;
						}
					}

					foreach ($options as $id => $option)
					{				
						 if (0 == $options[$id]['posts_per_page']) {
							continue;
						}
					?>

						<div>
							<?php
							$count = 1;
							$args = array(
							  'post__not_in'=> $do_not_duplicate,
							  'posts_per_page' => $options[$id]['posts_per_page'], 
							  'cat' => $options[$id]['cat']
							);				
							$wp_query = new WP_Query(); $wp_query->query($args); 
							while ($wp_query->have_posts()) : $wp_query->the_post();
							if (of_get_option('of_dnd') == 1) { $do_not_duplicate[] = $post->ID; }
							
							$class = 'featured-media';
							if ($count % 4 == 0 ) {
								$class .= ' every_fourth';
							}
							if ($count % 2 == 0 ) {
								$class .= ' every_second';
							}							
							?>

								<article itemscope="itemscope" itemtype="http://schema.org/BlogPosting" itemprop="blogPost" <?php post_class($class); ?>>
									<?php 
									gabfire_media(array(
										'name' => 'tabs', 
										'imgtag' => 1,
										'link' => 1,
										'enable_video' => 1, 
										'enable_thumb' => 1, 
										'resize_type' => 'c', 
										'media_width' => 254, 
										'media_height' => 141, 
										'thumb_align' => 'aligncenter', 
										'enable_default' => 0
									)); 										
									?>
									
									<h2 class="entry-title">
										<a href="<?php the_permalink() ?>" rel="bookmark" title="<?php printf( esc_attr__( 'Permalink to %s', 'gabfire' ), the_title_attribute( 'echo=0' ) ); ?>" ><?php the_title(); ?></a>
									</h2>
									
									<p class="p-summary hidden-sm hidden-xs" itemprop="text"><?php echo string_limit_words(9); ?>&hellip;</p>
									
									<?php gabfire_postmeta(true,true,false,false,false); ?>
									
								</article><!-- .featured-media -->
								<?php if($count % 4 == 0) { echo '<div class="clearfix"></div>';  } ?>
							<?php $count++; endwhile; wp_reset_query(); ?>
						</div><!-- .slide-item -->			
					<?php } ?>			
				</div>
			</div>
		</div>
	</section><!-- //Mid Tabbed Content -->

<?php } ?>

<?php
$numberofposts = of_get_option('of_an_nr6a') + of_get_option('of_an_nr6b') + of_get_option('of_an_nr6c');	 
if (intval($numberofposts) > 0 ) { ?>

	<div id="secondary-bottom" class="row">
		<div class="col-lg-6 col-md-6 col-sm-7 col-xs-12 secondary-bottom-left">
			<?php 
			/* 
			 * $numberofposts variable is used to get total number of posts set on backend for;
			 * of_get_option('of_an_nr6a') -> Number of posts with big title, no thumb
			 * of_get_option('of_an_nr6b') -> Number of posts with regular thumb
			 * of_get_option('of_an_nr6c') -> number of only titles
			 * At loop $args, numberofposts var is set return number of total posts
			 * to be fetched from database. And within loop, the entries are break into 
			 * parts using if else statemens and ...._nr7a, nr7b, and nr7c values.
			 */
			$numberofposts = of_get_option('of_an_nr6a') + of_get_option('of_an_nr6b') + of_get_option('of_an_nr6c');			
			if (intval($numberofposts) > 0 ) { ?>
				<p class="catname">
					<?php $catid = of_get_option('of_an_cat6'); ?>
					<span><a data-toggle="tooltip" title="<?php printf(esc_attr__('View all posts filed under %1$s','gabfire'), get_cat_name($catid)); ?>"  href="<?php echo get_category_link($catid);?>"><?php echo get_cat_name($catid); ?></a></span>
				</p>
				
				<?php
				$count = 1; 
				$numberofthumbs = of_get_option('of_an_nr6a') + of_get_option('of_an_nr6b');
				$args = array(
				  'post__not_in'=>$do_not_duplicate,
				  'posts_per_page' => $numberofposts, 
				  'cat' => of_get_option('of_an_cat6' , 1)
				);		
				$gab_query = new WP_Query();$gab_query->query($args); 
				while ($gab_query->have_posts()) : $gab_query->the_post();						
				if (of_get_option('of_dnd') == 1) { $do_not_duplicate[] = $post->ID; }
				?>

						<article itemscope="itemscope" itemtype="http://schema.org/BlogPosting" itemprop="blogPost" <?php post_class('featuredpost'); ?>>
							<?php 
							$enable_thumb = 0;
							$postnothumb = of_get_option('of_an_nr6a');
							$postsmallthumb = of_get_option('of_an_nr6b');
							$postbigthumb = of_get_option('of_an_nr6c');
							
							$media_width = '';
							$media_height = '';	
							$name = '';
							$enable_thumb = '';
							
							if ( ($count > $postnothumb)  and ($count <= ($postnothumb + $postsmallthumb)) ) {
								$name = 'secondarybottom-leftsmall';
								$enable_thumb = 1;
								$media_width = 124;
								$media_height = 90;	
								
							} elseif ($count > $postnothumb + $postsmallthumb) {
								$name = 'secondarybottom-leftbig';
								$enable_thumb = 1;
								$media_width = 186;
								$media_height = 135;									
							}
							
							gabfire_media(array(
								'name' => $name, 
								'imgtag' => 1,
								'link' => 1,
								'enable_video' => 0, 
								'video_id' => 'featured', 
								'enable_thumb' => $enable_thumb,
								'resize_type' => 'c',
								'media_width' => $media_width, 
								'media_height' => $media_height, 
								'thumb_align' => 'alignleft',
								'enable_default' => 0
							)); 
							?>	
							
							<h2 class="entry-title">
								<a href="<?php the_permalink() ?>" rel="bookmark" title="<?php printf( esc_attr__( 'Permalink to %s', 'gabfire' ), the_title_attribute( 'echo=0' ) ); ?>" >
									<?php the_title(); ?>
								</a>
							</h2>

							<p class="p-summary" itemprop="text"><?php echo string_limit_words(24); ?>&hellip;</p>
							<?php gabfire_postmeta(true,true,true,false); ?>

						</article><!-- end of featuredpost -->
				<?php $count++; endwhile; wp_reset_query(); ?>
			<?php } ?>
		</div><!-- left -->
		
		<div class="col-lg-6 col-md-6 col-sm-5 col-xs-12 secondary-bottom-right">
			<?php if (intval(of_get_option('of_an_nr7')) > 0 ) { ?>
				<p class="catname">
					<?php $catid = of_get_option('of_an_cat7'); ?>
					<span><a data-toggle="tooltip" title="<?php printf(esc_attr__('View all posts filed under %1$s','gabfire'), get_cat_name($catid)); ?>"  href="<?php echo get_category_link($catid);?>"><?php echo get_cat_name($catid); ?></a></span>
				</p>
				
				<?php
				$count = 1; 
				$args = array(
				  'post__not_in'=>$do_not_duplicate,
				  'posts_per_page' => of_get_option('of_an_nr7' , 7), 
				  'cat' => of_get_option('of_an_cat7' , 1)
				);		
				$gab_query = new WP_Query();$gab_query->query($args); 
				while ($gab_query->have_posts()) : $gab_query->the_post();						
				if (of_get_option('of_dnd') == 1) { $do_not_duplicate[] = $post->ID; }
				
				/* Lets display left wrapper, right wrappers start/close tags and the inner
				 * and content of left wrapper only once. And repeat the entry headlines
				 * for right wrapper as many times as it is required within wrapper of
				 * right division
				 */

				 if($count == 1) { ?>
					<article itemscope="itemscope" itemtype="http://schema.org/BlogPosting" itemprop="blogPost" <?php post_class('featuredpost'); ?>>
						<?php
							gabfire_media(array(
								'name' => 'secondarybottom-right', 
								'imgtag' => 1,
								'link' => 1,
								'enable_video' => 1, 
								'video_id' => 'featured', 
								'enable_thumb' => 1,
								'resize_type' => 'c',
								'media_width' => 570, 
								'media_height' => 324, 
								'thumb_align' => 'aligncenter',
								'enable_default' => 0
							)); 
						?>
								
						<h2 class="entry-title">
							<a href="<?php the_permalink() ?>" rel="bookmark" title="<?php printf( esc_attr__( 'Permalink to %s', 'gabfire' ), the_title_attribute( 'echo=0' ) ); ?>" >
								<?php the_title(); ?>
							</a>
						</h2>
							
						<p class="p-summary" itemprop="text"><?php echo string_limit_words(28); ?>&hellip;</p>
						<?php gabfire_postmeta(true,true,true,false); ?>
						
							
						<div class="home_468x60">
							<?php gabfire_dynamic_sidebar('bottom-468x60'); ?>
						</div>
					</article>		

					<?php } else { ?>			
						<h2 class="entry-title liststyle <?php if ($count == of_get_option('of_an_nr7')) { echo 'lastpost'; } ?>">
							<i class="fa fa-caret-right"></i>
							<a href="<?php the_permalink() ?>" rel="bookmark" title="<?php printf( esc_attr__( 'Permalink to %s', 'gabfire' ), the_title_attribute( 'echo=0' ) ); ?>" >
								<?php the_title(); ?>
							</a>
						</h2>
					<?php } ?>						
				<?php $count++; endwhile; wp_reset_query(); ?>
			<?php } ?>
		</div><!-- right -->
	</div><!-- secondary-content -->
	
<?php } ?>

<?php 
if ( is_active_sidebar( 'beforesubnews-left' ) )
{ ?>
	<section class="bottomads">
		
		<div class="large_ad pull-left">
			<?php gabfire_dynamic_sidebar('beforesubnews-left'); ?>
		</div>
		
		<div class="small_ad pull-right">
			<?php gabfire_dynamic_sidebar('beforesubnews-right'); ?>
		</div>
		
	</section><?php
} ?>

<section id="subnews" class="row">
	
	<div class="col-lg-8 col-md-8 col-sm-12 subnews_left">
	
		<div class="row subnews_row subnews_first">
			<?php 
			$cat_count = 3;
			$options = array();

			for ($i=1; $i<=$cat_count;$i++) {
				if(0 < strlen($variable = of_get_option('of_an_subrow1nr'.$i))) {
					$options[$i]['posts_per_page'] = $variable;
				}
				if(0 < strlen($variable = of_get_option('of_an_subrow1cat'.$i))) {
					$options[$i]['cat'] = $variable;
				}
			}
			
			$current = 1;
			foreach ($options as $id => $option)
			{				
				 if (0 == $options[$id]['posts_per_page']) {
					continue;
				}
			?>

				<div class="col-lg-4 col-md-4 col-sm-4 subnews_posts_wrapper">
				
					<p class="catname">
						<?php $catid = of_get_option('of_an_subrow1cat'.$current); ?>
						<span><a href="<?php echo get_category_link($catid);?>" data-toggle="tooltip" title="<?php printf(esc_attr__('View all posts filed under %1$s','gabfire'), get_cat_name($catid) ); ?>"><?php echo get_cat_name($catid); ?></a></span>
					</p>
				
					<?php
					$count = 1;
					$args = array(
					  'post__not_in'=> $do_not_duplicate,
					  'posts_per_page' => $options[$id]['posts_per_page'], 
					  'cat' => $options[$id]['cat']
					);				
					$wp_query = new WP_Query(); $wp_query->query($args); 
					while ($wp_query->have_posts()) : $wp_query->the_post();
					if (of_get_option('of_dnd') == 1) { $do_not_duplicate[] = $post->ID; }
					?>
					
						<?php if($count == 1) { ?>
						
							<article itemscope="itemscope" itemtype="http://schema.org/BlogPosting" itemprop="blogPost" <?php post_class('featuredpost'); ?>>

								<?php
									gabfire_media(array(
										'name' => 'homeslider-subnews',
										'imgtag' => 1,
										'link' => 1,
										'enable_video' => 0,
										'enable_thumb' => 1,
										'resize_type' => 'c',
										'media_width' => 219, 
										'media_height' => 115, 
										'thumb_align' => 'aligncenter',
										'enable_default' => 0
									));
								?>							
							
								<h2 class="entry-title">
									<a href="<?php the_permalink() ?>" rel="bookmark" title="<?php printf( esc_attr__( 'Permalink to %s', 'gabfire' ), the_title_attribute( 'echo=0' ) ); ?>" ><?php the_title(); ?></a>
								</h2>	
								
								<p class="p-summary" itemprop="text"><?php echo string_limit_words(18); ?>&hellip;</p>
								
								<?php gabfire_postmeta(true,true,false,false,false); ?>
								
							</article><!-- .featured-media -->
						
						<?php } else { ?>
							
							<h2 class="entry-title liststyle">
								<i class="fa fa-caret-right"></i>
								<a href="<?php the_permalink() ?>" rel="bookmark" title="<?php printf( esc_attr__( 'Permalink to %s', 'gabfire' ), the_title_attribute( 'echo=0' ) ); ?>" ><?php the_title(); ?></a>
							</h2>	
								
						<?php } ?>
					
					<?php $count++; endwhile; wp_reset_query(); ?>
				</div>
			<?php $current++; } ?>	
		</div><!-- /.subnews_row -->
		
		<div class="row subnews_row subnews_second">
			<?php 
			$cat_count = 3;
			$options = array();

			for ($i=1; $i<=$cat_count;$i++) {
				if(0 < strlen($variable = of_get_option('of_an_subrow2nr'.$i))) {
					$options[$i]['posts_per_page'] = $variable;
				}
				if(0 < strlen($variable = of_get_option('of_an_subrow2cat'.$i))) {
					$options[$i]['cat'] = $variable;
				}
			}
			
			$current = 1;
			foreach ($options as $id => $option)
			{				
				 if (0 == $options[$id]['posts_per_page']) {
					continue;
				}
			?>

				<div class="col-lg-4 col-md-4 col-sm-4 subnews_posts_wrapper">		

					<p class="catname">
						<?php $catid = of_get_option('of_an_subrow2cat'.$current); ?>
						<span><a href="<?php echo get_category_link($catid);?>" data-toggle="tooltip" title="<?php printf(esc_attr__('View all posts filed under %1$s','gabfire'), get_cat_name($catid) ); ?>"><?php echo get_cat_name($catid); ?></a></span>
					</p>

					<?php
					$count = 1;
					$args = array(
					  'post__not_in'=> $do_not_duplicate,
					  'posts_per_page' => $options[$id]['posts_per_page'], 
					  'cat' => $options[$id]['cat']
					);				
					$wp_query = new WP_Query(); $wp_query->query($args); 
					while ($wp_query->have_posts()) : $wp_query->the_post();
					if (of_get_option('of_dnd') == 1) { $do_not_duplicate[] = $post->ID; }
					?>
						<?php if($count == 1) { ?>
						
							<article itemscope="itemscope" itemtype="http://schema.org/BlogPosting" itemprop="blogPost" <?php post_class('featuredpost'); ?>>
							
								<?php
									gabfire_media(array(
										'name' => 'homeslider-subnews',
										'imgtag' => 1,
										'link' => 1,
										'enable_video' => 0,
										'enable_thumb' => 1,
										'resize_type' => 'c',
										'media_width' => 570, 
										'media_height' => 324, 
										'thumb_align' => 'aligncenter',
										'enable_default' => 0
									));
								?>							
							
								<h2 class="entry-title">
									<a href="<?php the_permalink() ?>" rel="bookmark" title="<?php printf( esc_attr__( 'Permalink to %s', 'gabfire' ), the_title_attribute( 'echo=0' ) ); ?>" ><?php the_title(); ?></a>
								</h2>	
								
								<p class="p-summary" itemprop="text"><?php echo string_limit_words(18); ?>&hellip;</p>
								
								<?php gabfire_postmeta(true,true,false,false,false); ?>
							</article><!-- .featured-media -->
						
						<?php } else { ?>
							
							<h2 class="entry-title liststyle">
								<i class="fa fa-caret-right"></i>
								<a href="<?php the_permalink() ?>" rel="bookmark" title="<?php printf( esc_attr__( 'Permalink to %s', 'gabfire' ), the_title_attribute( 'echo=0' ) ); ?>" ><?php the_title(); ?></a>
							</h2>	
								
						<?php } ?>
						
					<?php $count++; endwhile; wp_reset_query(); ?>
				</div>
			<?php $current++; } ?>	
		</div><!-- /.subnews_row -->	
		
	</div><!-- /.col-lg-8 -->
		
	
	<div class="col-lg-4 col-md-4 col-sm-12 home_sidebar">
		<div class="home_sidebarinner">
			<?php gabfire_dynamic_sidebar('homesidebar'); ?>
		</div>
	</div>

</section><!-- .subnews -->