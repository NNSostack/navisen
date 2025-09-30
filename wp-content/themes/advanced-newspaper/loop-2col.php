	<?php 
	$count = 1;
	if (have_posts()) : while (have_posts()) : the_post();
	$subtitle = get_post_meta($post->ID, 'subtitle', true);
	?>
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
					if( of_get_option('of_an_2col_full') <> '' && is_category(explode(',', of_get_option('of_an_2col_full')) ) && $subtitle != '' ) {				
							echo "<p class='subtitle'>$subtitle</p>";
					}

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
		if ($count % 2 == 0) { echo "<div class='clearfix'></div>"; }
	
	$count++; endwhile; else: endif;
	
	gabfire_archivepagination();
	wp_reset_query(); ?>