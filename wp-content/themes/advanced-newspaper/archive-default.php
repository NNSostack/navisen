		<div class="row">
			
			<main class="col-xs-12 col-md-8 col-sm-8 post-wrapper" role="main" itemprop="mainContentOfPage" itemscope="itemscope" itemtype="http://schema.org/Blog">
				
				<div class="article-wrapper archive-default">
					<div class="row">
						<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
							<nav class="gabfire_breadcrumb" itemprop="breadcrumb"><?php gabfire_breadcrumb(); ?></nav>
						</div>
					</div>

					<?php get_template_part('loop', 'default'); ?>
				</div><!-- articles-wrapper -->
				
			</main><!-- col-md-8 -->
			
			<?php get_sidebar(); ?>
			
		</div>	