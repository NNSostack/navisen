<section id="noslider-top" class="row">
	
	<div id="noslider-mid-and-right-wrapper" class="col-lg-10 col-md-10 col-sm-12 col-xs-12">
		<?php
		/* ***************************************** LARGE POST TITLE */
		
		/* Checking theme control panel options here 
		 * since we will query one single title only before the loop
		 * $args does not need to be rechecked again below while we create
		 * a loop for featured entries below */
		 
		$do_not_duplicate = '';
		$count = 1;
		$nrofposts = of_get_option('of_an_snr2') + of_get_option('of_an_snr2a');
		if (of_get_option('of_an_featype') == 'mrecent') {
			$args = array(
			  'posts_per_page' => $nrofposts,
			);
		}
		elseif (of_get_option('of_an_featype') == 'tagbased') {
			$args = array(
			  'posts_per_page' => $nrofposts,
			  'tag_id' => of_get_option('of_an_stag2')
			);
		}
		elseif (of_get_option('of_an_featype') == 'cfbased') {
			$args = array(
			  'post_type' => 'any',
			  'posts_per_page' => $nrofposts,
			  'meta_key' => 'featured', 
			  'meta_value' => 'true'
			);
		}				
		else {
			$args = array(
			  'posts_per_page' => $nrofposts,
			  'cat' => of_get_option('of_an_scat2', 1)
			);
		} 
		
		$wp_query = new WP_Query(); $wp_query->query($args); 
		while ($wp_query->have_posts()) : $wp_query->the_post();
		?>
		
			<?php if($count == 1 ) { ?>
				<h2 class="entry-title large-posttitle">
					<a href="<?php the_permalink() ?>" rel="bookmark" title="<?php printf( esc_attr__( 'Permalink to %s', 'gabfire' ), the_title_attribute( 'echo=0' ) ); ?>" ><?php the_title(); ?></a>
				</h2>
			<?php } ?>
		
		<?php $count++; endwhile; wp_reset_query();
		/* ***************************************** LARGE POST TITLE */
		?>

		<div class="row">
		
			<div id="noslider-mid" class="col-lg-7 col-md-7 col-sm-7 col-xs-12">
				<?php gabfire_dynamic_sidebar('nosliderhome-topmid');?>
				
				<?php if (intval(of_get_option('of_an_snr2', 4)) > 0 ) { ?>
					
					<?php
					$count = 1;
					$wp_query = new WP_Query(); $wp_query->query($args); 
					while ($wp_query->have_posts()) : $wp_query->the_post();
					if (of_get_option('of_dnd_simple') == 1) { $do_not_duplicate[] = $post->ID; }	
					
					$class = 'featuredpost';
					if($count == $nrofposts ) { $class .= ' lastpost'; }
					?>
					
						<article itemscope="itemscope" itemtype="http://schema.org/BlogPosting" itemprop="blogPost" <?php post_class($class); ?>>
							<?php if( $count !== 1 ) { ?>
							<h2 class="entry-title">
								<a href="<?php the_permalink() ?>" rel="bookmark" title="<?php printf( esc_attr__( 'Permalink to %s', 'gabfire' ), the_title_attribute( 'echo=0' ) ); ?>" ><?php the_title(); ?></a>
							</h2>	
							<?php } ?>
							
							<?php
							if( $count <= of_get_option('of_an_snr2') ) {
								$name = 'featured-big';
								$media_width = 570;
								$media_height = 321;
								$resize_type = 'c';
								$media_class = 'aligncenter';
							} else {
								$name = 'featured-small';
								$media_width = 120;
								$media_height = 74;
								$resize_type = 'c';
								$media_class = 'alignleft';
							}
							gabfire_media(array(
								'name' => $name,
								'imgtag' => 1,
								'link' => 1,
								'enable_video' => 0,
								'enable_thumb' => 1,
								'resize_type' => $resize_type,
								'media_width' => $media_width, 
								'media_height' => $media_height, 
								'thumb_align' => $media_class,
								'enable_default' => 0
							));
							?>
							
							<p class="p-summary" itemprop="text"><?php echo string_limit_words(30); ?>&hellip;</p>
							
							<?php gabfire_postmeta(true,true,true,true,true); ?>
							
						</article><!-- .featuredpost -->
					<?php $count++; endwhile; wp_reset_query(); ?>
					
				<?php } ?>		
				<?php gabfire_dynamic_sidebar('nosliderhome-topmid2');?>
			</div><!-- #secondary-left -->
			
			<div id="noslider-right" class="col-lg-5 col-md-5 col-sm-5 col-xs-12">
				
				<?php gabfire_dynamic_sidebar('nosliderhome-topright');?>
				
				<?php if (intval(of_get_option('of_an_snr3', 1)) > 0 ) { ?>
					
					<p class="catname">
						<?php $catid = of_get_option('of_an_scat3'); ?>
						<span><a href="<?php echo get_category_link($catid);?>" data-toggle="tooltip" title="<?php printf(esc_attr__('View all posts filed under %1$s','gabfire'), get_cat_name($catid) ); ?>"><?php echo get_cat_name($catid); ?></a></span>
					</p>

					<?php
					$count = 1;
					$args = array(
					 'post__not_in'=> $do_not_duplicate,
					  'posts_per_page' => of_get_option('of_an_snr3', 1),
					  'cat' => of_get_option('of_an_scat3')
					);
					$wp_query = new WP_Query(); $wp_query->query($args); 
					while ($wp_query->have_posts()) : $wp_query->the_post();
					if (of_get_option('of_dnd_simple') == 1) { $do_not_duplicate[] = $post->ID; }	
					?>
					
						<article itemscope="itemscope" itemtype="http://schema.org/BlogPosting" itemprop="blogPost" <?php post_class('featuredpost'); ?>>

							<h2 class="entry-title">
								<a href="<?php the_permalink() ?>" rel="bookmark" title="<?php printf( esc_attr__( 'Permalink to %s', 'gabfire' ), the_title_attribute( 'echo=0' ) ); ?>" ><?php the_title(); ?></a>
							</h2>									
						
							<?php
								gabfire_media(array(
									'name' => 'featured-small',
									'imgtag' => 1,
									'link' => 1,
									'enable_video' => 0,
									'enable_thumb' => 1,
									'resize_type' => 'c',
									'media_width' => 120, 
									'media_height' => 74, 
									'thumb_align' => 'alignleft',
									'enable_default' => 0
								));
							?>				
							
							<p class="p-summary" itemprop="text"><?php echo string_limit_words(16); ?>&hellip;</p>
							
							<?php gabfire_postmeta(true,true,true,true,true); ?>
							
						</article><!-- .featuredpost -->
					<?php $count++; endwhile; wp_reset_query(); ?>
					
				<?php } ?>
				<?php gabfire_dynamic_sidebar('nosliderhome-topright2');?>
			</div><!-- #secondary-right -->	
		</div>
				
		<?php if( is_active_sidebar('homepage-728') ) { ?>
			<div class="row">
				<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 homepage728body">
					<?php gabfire_dynamic_sidebar('homepage-728'); ?>
				</div>
			</div>
		<?php } ?>
	</div><!-- #noslider-mid-and-right-wrapper -->
		
	<!-- Added to right hand to show it below featured section while coding responsive -->
	<div id="noslider-left" class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
		<?php gabfire_dynamic_sidebar('nosliderhome-topleft');?>
		
		<?php if (intval(of_get_option('of_an_snr1', 9)) > 0 ) { ?>
			<p class="catname">
				<?php $catid = of_get_option('of_an_scat1'); ?>
				<span><a href="<?php echo get_category_link($catid);?>" data-toggle="tooltip" title="<?php printf(esc_attr__('View all posts filed under %1$s','gabfire'), get_cat_name($catid) ); ?>"><?php echo get_cat_name($catid); ?></a></span>
			</p>
			
			<div class="widget">
				<ul class="noborderli">	
					<?php
					$do_not_duplicate = '';
					$count = 1;
					$args = array(
					  'post__not_in'=> $do_not_duplicate,
					  'posts_per_page' => of_get_option('of_an_snr1', 9),
					  'cat' => of_get_option('of_an_scat1')
					);
					$wp_query = new WP_Query(); $wp_query->query($args); 
					while ($wp_query->have_posts()) : $wp_query->the_post();  if (of_get_option('of_dnd_simple') == 1) { $do_not_duplicate[] = $post->ID; }
					?>	
						<li <?php post_class();?>>
							<a class="entry-title" href="<?php the_permalink() ?>" rel="bookmark" title="<?php printf( esc_attr__( 'Permalink to %s', 'gabfire' ), the_title_attribute( 'echo=0' ) ); ?>" ><?php the_title(); ?></a>
							<?php gabfire_postmeta(true,true,false,false,false); ?>
						</li>
					<?php $count++; endwhile; wp_reset_query(); ?>
				</ul>
			</div><!-- .widget -->
		<?php } ?>
		<?php gabfire_dynamic_sidebar('nosliderhome-topleft2');?>
	</div><!-- #noslider-left -->			
</section><!-- #noslider-top -->

<?php /* Call media slider if the number of entries are set greater than 0 on theme control panel */
$postnr = of_get_option('of_an_tabsnr1') + of_get_option('of_an_tabsnr2') + of_get_option('of_an_tabsnr3') + of_get_option('of_an_tabsnr4') + of_get_option('of_an_tabsnr5');
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
							if(of_get_option('of_an_tabsnr'.$i) !== '0' ) {
								if (intval(of_get_option('of_an_tabsnr'.$i)) > 0 ) { ?>
									<li><?php echo get_cat_name(of_get_option('of_an_tabscat'.$i)); ?></li><?php 
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
						if(0 < strlen($variable = of_get_option('of_an_tabsnr'.$i))) {
							$options[$i]['posts_per_page'] = $variable;
						}
						if(0 < strlen($variable = of_get_option('of_an_tabscat'.$i))) {
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
							if (of_get_option('of_dnd_simple') == 1) { $do_not_duplicate[] = $post->ID; }
							
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
$numberofposts = of_get_option('of_an_snr4a') + of_get_option('of_an_snr4b') + of_get_option('of_an_snr4c') + of_get_option('of_an_snr5c');	 
if (intval($numberofposts) > 0 ) { ?>

	<div id="secondary-bottom" class="row">
		<div class="col-lg-6 col-md-6 col-sm-7 col-xs-12 secondary-bottom-left">
			<?php 
			/* 
			 * $numberofposts variable is used to get total number of posts set on backend for;
			 * of_get_option('of_an_snr4a') -> Number of posts with big title, no thumb
			 * of_get_option('of_an_snr4b') -> Number of posts with regular thumb
			 * of_get_option('of_an_snr4c') -> number of only titles
			 * At loop $args, numberofposts var is set return number of total posts
			 * to be fetched from database. And within loop, the entries are break into 
			 * parts using if else statements and ...._snr4a, snr4b, and snr4c values.
			 */
			$numberofposts = of_get_option('of_an_snr4a') + of_get_option('of_an_snr4b') + of_get_option('of_an_snr4c');			
			if (intval($numberofposts) > 0 ) { ?>
				<p class="catname">
					<?php $catid = of_get_option('of_an_scat4'); ?>
					<span><a href="<?php echo get_category_link($catid);?>" data-toggle="tooltip" title="<?php printf(esc_attr__('View all posts filed under %1$s','gabfire'), get_cat_name($catid) ); ?>"><?php echo get_cat_name($catid); ?></a></span>
				</p>
				
				<?php
				$count = 1; 
				$numberofthumbs = of_get_option('of_an_snr4a') + of_get_option('of_an_snr4b');
				$args = array(
				  'post__not_in'=>$do_not_duplicate,
				  'posts_per_page' => $numberofposts, 
				  'cat' => of_get_option('of_an_scat4' , 1)
				);		
				$gab_query = new WP_Query();$gab_query->query($args); 
				while ($gab_query->have_posts()) : $gab_query->the_post();						
				if (of_get_option('of_dnd_simple') == 1) { $do_not_duplicate[] = $post->ID; }
				?>

						<article itemscope="itemscope" itemtype="http://schema.org/BlogPosting" itemprop="blogPost" <?php post_class('featuredpost'); ?>>
							<?php 
							$enable_thumb = 0;
							$postnothumb = of_get_option('of_an_snr4a');
							$postsmallthumb = of_get_option('of_an_snr4b');
							$postbigthumb = of_get_option('of_an_snr4c');

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
			<?php if (intval(of_get_option('of_an_snr5')) > 0 ) { ?>
				<p class="catname">
					<?php $catid = of_get_option('of_an_scat5'); ?>
					<span><a href="<?php echo get_category_link($catid);?>" data-toggle="tooltip" title="<?php printf(esc_attr__('View all posts filed under %1$s','gabfire'), get_cat_name($catid) ); ?>"><?php echo get_cat_name($catid); ?></a></span>
				</p>
				
				<?php
				$count = 1; 
				$args = array(
				  'post__not_in'=>$do_not_duplicate,
				  'posts_per_page' => of_get_option('of_an_snr5' , 7), 
				  'cat' => of_get_option('of_an_scat5' , 1)
				);		
				$gab_query = new WP_Query();$gab_query->query($args); 
				while ($gab_query->have_posts()) : $gab_query->the_post();						
				if (of_get_option('of_dnd_simple') == 1) { $do_not_duplicate[] = $post->ID; }
				
				/* Lets display left wrapper, right wrappers start/close tags and the inner
				 * and content of left wrapper only once. And repeat the entry headlines
				 * for right wrapper as many times as it is required within wrapper of
				 * right division
				 */

				 if($count == 1) { ?>
					<article itemscope="itemscope" itemtype="http://schema.org/BlogPosting" itemprop="blogPost" <?php post_class('featuredpost'); ?>>
						<?php
							gabfire_media(array(
								'name' => 'secondary-right', 
								'imgtag' => 1,
								'link' => 1,
								'enable_video' => 0, 
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
						<h2 class="entry-title liststyle <?php if ($count == of_get_option('of_an_snr5')) { echo 'lastpost'; } ?>">
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

<?php if (intval(of_get_option('of_an_subrow1snr1')) > 0 )
{ ?>
	<section id="subnews" class="row">
		
		<div class="col-lg-8 col-md-8 col-sm-12 subnews_left">
			<div class="row noslider-subnews">
				<div class="col-lg-6 col-md-12 firstsubnews-col">
					<p class="catname">
						<?php $catid = of_get_option('of_an_subrow1scat1'); ?>
						<span><a href="<?php echo get_category_link($catid);?>" data-toggle="tooltip" title="<?php printf(esc_attr__('View all posts filed under %1$s','gabfire'), get_cat_name($catid) ); ?>"><?php echo get_cat_name($catid); ?></a></span>
					</p>
					
					<?php
						$count = 1; 
						$args = array(
						  'post__not_in'=>$do_not_duplicate,
						  'posts_per_page' => of_get_option('of_an_subrow1snr1' , 7), 
						  'cat' => of_get_option('of_an_subrow1scat1' , 1)
						);		
						$gab_query = new WP_Query();$gab_query->query($args); 
						while ($gab_query->have_posts()) : $gab_query->the_post();						
						if (of_get_option('of_dnd_simple') == 1) { $do_not_duplicate[] = $post->ID; }
					?>
						<article itemscope="itemscope" itemtype="http://schema.org/BlogPosting" itemprop="blogPost" <?php post_class('featuredpost'); ?>>

							<?php
							gabfire_media(array(
								'name' => 'homenoslider-subnewsbig', 
								'imgtag' => 1,
								'link' => 1,
								'enable_video' => 0, 
								'video_id' => 'featured', 
								'enable_thumb' => 1,
								'resize_type' => 'c',
								'media_width' => 360, 
								'media_height' => 165, 
								'thumb_align' => 'aligncenter',
								'enable_default' => 0
							)); 
							?>	
							
							<h2 class="entry-title">
								<a href="<?php the_permalink() ?>" rel="bookmark" title="<?php printf( esc_attr__( 'Permalink to %s', 'gabfire' ), the_title_attribute( 'echo=0' ) ); ?>" >
									<?php the_title(); ?>
								</a>
							</h2>

							<p class="p-summary" itemprop="text"><?php echo string_limit_words(14); ?>&hellip;</p>
							<?php gabfire_postmeta(true,true,true,false); ?>		
						</article>
					<?php $count++; endwhile; wp_reset_query(); ?>
				</div>
				
				<div class="col-lg-6 col-md-12 secondsubnews-col">
				
					<p class="catname">
						<?php $catid = of_get_option('of_an_subrow1scat2'); ?>
						<span><a href="<?php echo get_category_link($catid);?>" data-toggle="tooltip" title="<?php printf(esc_attr__('View all posts filed under %1$s','gabfire'), get_cat_name($catid) ); ?>"><?php echo get_cat_name($catid); ?></a></span>
					</p>			
				
					<?php
					$count = 1; 
					$args = array(
					  'post__not_in'=>$do_not_duplicate,
					  'posts_per_page' => of_get_option('of_an_subrow1snr2' , 7), 
					  'cat' => of_get_option('of_an_subrow1scat2' , 1)
					);		
					$gab_query = new WP_Query();$gab_query->query($args); 
					while ($gab_query->have_posts()) : $gab_query->the_post();						
					if (of_get_option('of_dnd_simple') == 1) { $do_not_duplicate[] = $post->ID; }
					?>
					
					<article itemscope="itemscope" itemtype="http://schema.org/BlogPosting" itemprop="blogPost" <?php post_class('featuredpost'); ?>>
						<h2 class="entry-title">
							<a href="<?php the_permalink() ?>" rel="bookmark" title="<?php printf( esc_attr__( 'Permalink to %s', 'gabfire' ), the_title_attribute( 'echo=0' ) ); ?>" >
								<?php the_title(); ?>
							</a>
						</h2>
						
						<?php
						gabfire_media(array(
							'name' => 'homenoslider-subnewssmal', 
							'imgtag' => 1,
							'link' => 1,
							'enable_video' => 0, 
							'video_id' => 'featured', 
							'enable_thumb' => 1,
							'resize_type' => 'c',
							'media_width' => 80, 
							'media_height' => 55, 
							'thumb_align' => 'alignright',
							'enable_default' => 0
						)); 
						?>	
						
						<p class="p-summary" itemprop="text"><?php echo string_limit_words(15); ?>&hellip;</p>
						<?php gabfire_postmeta(true,true,true,false); ?>					
					</article>
					<?php $count++; endwhile; wp_reset_query(); ?>
				</div>
				
			</div>
		</div><!-- /.col-lg-8 -->
			
		
		<div class="col-lg-4 col-md-4 col-sm-12 home_sidebar">
			<div class="home_sidebarinner">
				<?php gabfire_dynamic_sidebar('homesidebar'); ?>
			</div>
		</div>

	</section><!-- .subnews --><?php
} ?>