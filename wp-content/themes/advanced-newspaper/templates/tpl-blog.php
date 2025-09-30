<?php
	/*
		Template Name: Blog
	*/
get_header(); ?>

	<div class="row archive">
		<main class="col-xs-12 col-md-8 col-sm-8 post-wrapper" role="main" itemprop="mainContentOfPage" itemscope="itemscope" itemtype="http://schema.org/Blog">
			<div class="article-wrapper archive-default">
				<div class="row">
					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<p class="gabfire_breadcrumb"><?php gabfire_breadcrumb(); ?></p>
					</div>
				</div>
				
				<?php 
				if ( get_query_var( 'paged') ) $paged = get_query_var( 'paged' ); elseif ( get_query_var( 'page') ) $paged = get_query_var( 'page' ); else $paged = 1;
				query_posts( "post_type=post&showposts=4&paged=$paged" ); 
							
				get_template_part('loop', 'default'); ?>
			</div>
		</main><!-- col-md-8 -->
		
		<?php get_sidebar(); ?>
	</div>	
	
<?php get_footer(); ?>