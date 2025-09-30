<?php get_header(); ?>
	
	<div class="row">
		
		<main class="col-xs-12 col-md-8 col-sm-8 post-wrapper" role="main" itemprop="mainContentOfPage" itemscope="itemscope" itemtype="http://schema.org/Blog">

			<div class="row">
				<div class="col-lg-12 col-md-12">
					<div class="post-lead">
						<h1 class="entry-title single-post-title" itemprop="headline">
							<?php _e('Page not found!', 'gabfire'); ?>
						</h1>
					</div>		
				</div>
			</div>			
			
			<section class="article-wrapper">
				<article class="entry">
					<p class="subtitle"><?php _e('Sorry the page you were looking is not here.', 'gabfire'); ?></p>
		
					<form action="<?php echo esc_url( home_url( '/' ) ); ?>">
						<div class="input-prepend">
							<label><?php _e('To search in site, type a keyword and hit enter', 'gabfire'); ?></label>
							<input type="text" name="s" class="form-control" placeholder="<?php _e('Type keyword and hit enter', 'gabfire'); ?>">
						</div>
					</form>	
				</article>
			</section>
		</main><!-- col-md-8 -->
		
		<?php get_sidebar(); ?>
	</div>	
	
	
<?php get_footer(); ?>