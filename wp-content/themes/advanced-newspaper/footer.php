	<footer role="contentinfo" itemscope="itemscope" itemtype="http://schema.org/WPFooter">

		<div class="row footercats">
			<div class="col-md-12">
				<ul itemscope="itemscope" itemtype="http://schema.org/SiteNavigationElement" role="navigation">
					<?php
					if(of_get_option('of_nav4', 1) == 1) { 
						wp_nav_menu( array('theme_location' => 'footer', 'container' => false, 'items_wrap' => '%3$s'));
					} else { 
						wp_list_categories('depth=1&orderby='. of_get_option('of_order_cats') .'&order='. of_get_option('of_sort_cats') .'&title_li=&exclude='. of_get_option('of_ex_cats'));
					} ?>	
				</ul>
			</div>
		</div>
			
		<div class="footer-firstrow row">
		
			<div class="col-md-4 col-lg-4 col-sm-3 col-xs-12 footer-firstrow-widget">
				<?php gabfire_dynamic_sidebar('footer-row1-left'); ?>
			</div>
			
			<div class="col-md-4 col-lg-4 col-sm-4 col-xs-12 footer-firstrow-widget footer-mid-column">
				<?php gabfire_dynamic_sidebar('footer-row1-mid'); ?>
			</div>
			
			<div class="col-md-4 col-lg-4 col-sm-5 col-xs-12 footer-firstrow-widget">
				<?php gabfire_dynamic_sidebar('footer-row1-right'); ?>
			</div>
			
		</div>
	<div class="row footer-meta-wrapper">
		<div class="col-md-12">
			<div class="footer-meta">
			
				<div class="footer-metaleft pull-left">
					<?php
					if ( of_get_option('of_footer1_text') <> '' ) {
						echo of_get_option('of_footer1_text');
					} else { 
						$info = getdate();
						$year = $info['year'];
						echo '&copy; '. $year .', <a href="#top" title="' . get_bloginfo('name') .'" rel="home"><strong>&uarr;</strong> ' . get_bloginfo('name') .'</a>';
					} ?>
				</div><!-- #site-info -->
							
				<div class="footer-metaleft pull-right">
					<?php /* Replace default text if option is set */
					if ( of_get_option('of_footer2_text') <> '' ) {
						echo of_get_option('of_footer2_text');
					} else {
						wp_loginout(); 
						if ( is_user_logged_in() ) { 
							echo '-'; ?>
							<a href="<?php echo home_url('/'); ?>wp-admin/edit.php">Posts</a> - 
							<a href="<?php echo home_url('/'); ?>wp-admin/post-new.php">Add New</a>
						<?php } ?> - 			
					<?php } ?>
					<a href="http://wordpress.org/" title="<?php esc_attr_e('Semantic Personal Publishing Platform', 'gabfire'); ?>" rel="generator"><?php _e('Powered by WordPress', 'gabfire'); ?></a> - 
					<a href="http://www.gabfirethemes.com/" title="WordPress Newspaper Themes">Gabfire Themes</a> 
					<?php wp_footer(); ?>
				</div> <!-- #footer-right-side -->
			</div>
		</div>
	</div>
		
	</footer><!-- /footer -->
	
</div><!-- /container -->

</body>
</html>