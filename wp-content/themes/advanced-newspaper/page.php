<?php 
	get_header(); 
	global $wp_query;
	$postid = $wp_query->post->ID;
		
	/* Assing custom field values to variables */
	$post_layout = get_post_meta($postid, 'gabfire_post_template', true);
	$subtitle = get_post_meta($postid, 'subtitle', true);
?>

	<?php
	/* Get the corrent template from inc folder
	 * and define correct classes if this is a
	 * bigpicture or bigslider template 
	 */
	if ( in_array($post_layout, array('bigpicture','bigslider'), true ) )
	{
		if ($post_layout == 'bigpicture') {
			$wrapper_class = 'bigpicture_wrapper';
			$template = 'inc/theme-gallery-bigpicture';
		} else {
			$wrapper_class = 'bigslider_wrapper';
			$template = 'inc/theme-gallery-bigslider';
		} ?>
		
		<div class="row">
			<div class="col-lg-12 col-md-12">
				<div class="post-lead">
					<h1 class="entry-title single-post-title" itemprop="headline">
						<?php single_post_title(); ?>
					</h1>
					
					<?php 					
					if ($subtitle != '') { 
						echo "<p class='subtitle'>$subtitle</p>";
					}
					?>
				</div>		
			</div>
		</div>			
		
		<div class="row">
			<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
			
				<div class="<?php echo $wrapper_class; ?>">
					<?php			
					/* Load pictures attached to this post */
					get_template_part( $template ); ?>		
				</div>
				
			</div>			
		</div>	
	<?php 
	}
	?>

	<div class="row <?php echo $post_layout; ?>">
	
		<?php
		/* Give a different class to main wrapper
		 * to make sure that it looks fine on
		 * smaller screens 
		 */
		if ($post_layout == '3col') {
			$class = 'col-xs-12 col-md-6 col-sm-8 post-wrapper';
		} else { 
			$class = 'col-xs-12 col-md-8 col-sm-8 post-wrapper';
		}
		?>
		
		<main class="<?php echo $class; ?>" role="main" itemprop="mainContentOfPage" itemscope="itemscope" itemtype="http://schema.org/Blog">

			<?php if ( !in_array($post_layout, array('bigpicture','bigslider'), true ) ) { ?>
				<div class="row">
					<div class="col-lg-12 col-md-12">
						<div class="post-lead">
							<h1 class="entry-title single-post-title" itemprop="headline">
								<?php single_post_title(); ?>
							</h1>
							
							<?php 
							if ($subtitle != '') { 
								echo "<p class='subtitle'>$subtitle</p>";
							}
							?>
						</div>		
					</div>
				</div>			
			<?php } ?>
			
			<?php get_template_part('loop', 'single'); ?>
			
		</main><!-- col-md-8 -->
		
		<?php
		/* Get the default sidebar or 
		 * 3col-right sidebar based on template 
		 */
		if ($post_layout !== 'fullwidth' ) { 
			if ($post_layout == '3col') {
				get_sidebar('3col-right'); 
			} else {
				get_sidebar(); 
			}
		} 
		?>
	</div>	
	
<?php get_footer(); ?>