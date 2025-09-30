	<?php 
	$count = 1;
	if (have_posts()) : while (have_posts()) : the_post();
	$subtitle = get_post_meta($post->ID, 'subtitle', true);
	?>
		<article <?php post_class('entry'); ?>>

				<h2 class="entry-title" itemprop="headline">
					<a href="<?php the_permalink() ?>" rel="bookmark" title="<?php printf( esc_attr__( 'Permalink to %s', 'gabfire' ), the_title_attribute( 'echo=0' ) ); ?>" ><?php the_title(); ?></a>
				</h2>
<!-- 				
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
-->
				<?php navisen_postmeta(true,true,true,false); ?>
				<?php 
				if ($subtitle != '') { 
					echo "<p class='subtitle'>$subtitle</p>";
				}

				gabfire_media(array(
					'name' => 'postthumbnail',
					'imgtag' => 1,
					'link' => 1,
					'enable_video' => 1, 
					'enable_thumb' => 1, 
					'resize_type' => 'c', 
					'media_width' => 701, 
					'media_height' => 360, 
					'thumb_align' => 'aligncenter',
					'enable_default' => 0
				)); 

				the_excerpt();
				?>
		</article>
		
		<?php
	
	$count++; endwhile; else: ?>
		<div <?php post_class('entry'); ?>>
			<h2 class="entry-title" itemprop="headline">
				<?php _e('Sorry, nothing matched your criteria','quickstart');?>
			</h2>
		</div>
	<?php endif;
	
	gabfire_archivepagination();
	wp_reset_query(); ?>