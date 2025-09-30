		<?php if(of_get_option('of_an_4col') <> '' && is_category(explode(',', of_get_option('of_an_4col')))) { ?>
			<div class="row" id="category-withsidebar">
				
				<main class="col-xs-12 col-md-12 col-sm-12 col-lg-12 post-wrapper" role="main" itemprop="mainContentOfPage" itemscope="itemscope" itemtype="http://schema.org/Blog">
					
					<div class="article-wrapper archive-4col">
						<div class="row">
							<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
								<nav class="gabfire_breadcrumb" itemprop="breadcrumb"><?php gabfire_breadcrumb(); ?></nav>
							</div>
						</div>

						<?php get_template_part('loop', '4col'); ?>
					</div><!-- articles-wrapper -->
					
				</main><!-- col-md-8 -->
				
			</div>	
		<?php } ?>	