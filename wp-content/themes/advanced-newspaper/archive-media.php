<section class="row archive-media">
	<div class="col-xs-12 col-md-12 col-lg-12 col-sm-12 archive-woutsidebar">		
	
		<?php get_template_part( 'loop', 'media' ); ?>
		
		<?php if((get_next_posts_link()) or (get_previous_posts_link())) { ?>
			<div class="archive-pagination">
				<?php
				// load pagination
				global $wp_query;

				$big = 999999999;
				echo paginate_links( array(
					'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
					'format' => '?paged=%#%',
					'current' => max( 1, get_query_var('paged') ),
					'total' => $wp_query->max_num_pages
				) );
				?>
			</div>
		<?php } ?>
		<div class="clearfix"></div>
		
	</div><!-- col-md-12 -->
	
	<div class="clearfix hidden-lg hidden-md"></div>
</section>