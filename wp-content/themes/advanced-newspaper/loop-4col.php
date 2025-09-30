	<?php 
	$count = 1;
	if (have_posts()) : while (have_posts()) : the_post();
	if ($count % 4 == 0) { 
		$class = 'entry everyfourth';
	} elseif ($count % 2 == 0) { 
		$class = 'entry everysecond';
	} else {
		$class = 'entry';
	}
	?>
		<article <?php post_class($class); ?>>

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

					<?php edit_post_link('Edit', '<span><i class="fa fa-pencil-square-o"></i>', '</span>'); ?>
				</p>

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
				<p class="p-summary" itemprop="text"><?php echo string_limit_words(15); ?>&hellip;</p>
		</article>
		
		<?php
		if ($count % 4 == 0) { echo "<div class='clearfix'></div>"; }
	
	$count++; endwhile; else: endif;
	
	gabfire_archivepagination();
	wp_reset_query(); ?>