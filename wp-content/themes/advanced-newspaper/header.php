<!DOCTYPE html>
<!--[if IE 7]>
<html class="ie ie7" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 8]>
<html class="ie ie8" <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 7) | !(IE 8) ]><!-->
<html <?php language_attributes(); ?>>
<!--<![endif]-->
<head>

	<meta charset="<?php bloginfo('charset'); ?>" />
	<meta name="viewport" content="width=device-width">
	
	<title><?php wp_title(); ?></title>
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="<?php if ( of_get_option('of_rssaddr') <> '' ) { echo of_get_option('of_rssaddr'); } else { echo bloginfo('rss2_url'); } ?>" />	
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />	
	
	<!--[if lt IE 9]>
	<script src="<?php echo get_template_directory_uri(); ?>/inc/js/html5.js"></script>
	<![endif]-->	
	
	<?php 
		wp_head(); 

		if (of_get_option('of_customcolors', 0) == 1) { 
			get_template_part( 'css/custom-colors', '' );
		}	
	?>

</head>

<body <?php body_class(); ?> itemscope="itemscope" itemtype="http://schema.org/WebPage">

<?php
	/**
	 * gabfire_before_header_starts hook
	 *
	 * @hooked gabfire_initial_theme_activation - 10 (outputs a notification message for initial theme installation)
	 */
	do_action( 'gabfire_before_header_starts' );
	
	/* Include a widget zone to add a banner before header */
	if ( is_active_sidebar( 'beforeheader' ) ) {
		echo '<div class="beforeheader_728">';
			gabfire_dynamic_sidebar('beforeheader');
		echo '</div>';
	}
?>

<div class="container">

	<header itemscope="itemscope" itemtype="http://schema.org/WPHeader" role="banner">
		<div class="row"><!-- Site Masthead Row-->
			<nav class="col-md-12 masthead-navigation" itemscope="itemscope" itemtype="http://schema.org/SiteNavigationElement" role="navigation">
				<ul class="mastheadnav">
					
					<li><span class="arrow-right"></span></li>
					<?php if ( has_nav_menu( 'masthead' ) ) {
						wp_nav_menu( array('theme_location' => 'masthead', 'container' => false, 'items_wrap' => '%3$s'));
					} else { ?>
						<li class="masthead_date">
							<script type="text/javascript">
								<!--
								var mydate=new Date()
								var year=mydate.getYear()
								if (year < 1000)
								year+=1900
								var day=mydate.getDay()
								var month=mydate.getMonth()
								var daym=mydate.getDate()
								if (daym<10)
								daym="0"+daym
								var dayarray=new Array("<?php _e('Sunday', 'gabfire'); ?>","<?php _e('Monday', 'gabfire'); ?>","<?php _e('Tuesday', 'gabfire'); ?>","<?php _e('Wednesday', 'gabfire'); ?>","<?php _e('Thursday', 'gabfire'); ?>","<?php _e('Friday', 'gabfire'); ?>","<?php _e('Saturday', 'gabfire'); ?>")
								var montharray=new Array("<?php _e('January', 'gabfire'); ?>","<?php _e('February', 'gabfire'); ?>","<?php _e('March', 'gabfire'); ?>","<?php _e('April', 'gabfire'); ?>","<?php _e('May', 'gabfire'); ?>","<?php _e('June', 'gabfire'); ?>","<?php _e('July', 'gabfire'); ?>","<?php _e('August', 'gabfire'); ?>","<?php _e('September', 'gabfire'); ?>","<?php _e('October', 'gabfire'); ?>","<?php _e('November', 'gabfire'); ?>","<?php _e('December', 'gabfire'); ?>")
								document.write(""+dayarray[day]+", "+montharray[month]+" "+daym+", "+year+"")
								// -->
							</script>
						</li>
					<?php } ?>
					 
					<?php if (of_get_option('of_socialhead' ) == 0) { ?>
						<li class="pull-right social_header">
							<?php if ( of_get_option('of_an_facebook') <> "" ) { ?>
								<a class="socialsite-facebook" href="<?php echo of_get_option('of_an_facebook'); ?>" title="<?php _e('Facebook', 'gabfire'); ?>" rel="nofollow"><span><?php _e('Friend on Facebook','gabfire'); ?></span>
									<i class="fa fa-facebook pull-left"></i>
								</a>
							<?php } ?>

							<?php if ( of_get_option('of_an_twitter') <> "" ) { ?>
								<a class="socialsite-twitter" href="<?php echo of_get_option('of_an_twitter'); ?>" title="<?php _e('Twitter', 'gabfire'); ?>" rel="nofollow"><span><?php _e('Follow on Twitter','gabfire'); ?></span>
									<i class="fa fa-twitter pull-left"></i>
								</a>
							<?php } ?>

							<?php if ( of_get_option('of_an_gplus') <> "" ) { ?>
								<a class="socialsite-google" href="<?php echo of_get_option('of_an_gplus'); ?>" title="<?php _e('Google+', 'gabfire'); ?>" rel="nofollow"><span><?php _e('Add to Google+ Circle','gabfire'); ?></span>
									<i class="fa fa-google-plus pull-left"></i>
								</a>
							<?php } ?>

							<?php if ( of_get_option('of_an_linkedin') <> "" ) { ?>
								<a class="socialsite-linkedin" href="<?php echo of_get_option('of_an_linkedin'); ?>" title="<?php _e('LinkedIn', 'gabfire'); ?>" rel="nofollow"><span><?php _e('Connect on Linked in','gabfire'); ?></span>
									<i class="fa fa-linkedin pull-left"></i>
								</a>
							<?php } ?>

							<?php if ( of_get_option('of_an_pinterest') <> "" ) { ?>
								<a class="socialsite-pinterest" href="<?php echo of_get_option('of_an_pinterest'); ?>" title="<?php _e('Pinterest', 'gabfire'); ?>" rel="nofollow"><span><?php _e('Follow on Pinterest','gabfire'); ?></span>
									<i class="fa fa-pinterest pull-left"></i>
								</a>
							<?php } ?>
							
							<?php if ( of_get_option('of_an_tumblr') <> "" ) { ?>
								<a class="socialsite-tumblr" href="<?php echo of_get_option('of_an_tumblr'); ?>" title="<?php _e('tumblr', 'gabfire'); ?>" rel="nofollow"><span><?php _e('Follow on Tumblr','gabfire'); ?></span>
									<i class="fa fa-tumblr"></i>
								</a>
							<?php } ?>							
							
							<?php if ( of_get_option('of_an_vimeo') <> "" ) { ?>
								<a class="socialsite-vimeo" href="<?php echo of_get_option('of_an_vimeo'); ?>" title="<?php _e('Vimeo', 'gabfire'); ?>" rel="nofollow"><span><?php _e('Watch on Vimeo','gabfire'); ?></span>
									<i class="fa fa-vimeo-square pull-left"></i>
								</a>
							<?php } ?>
							
							<?php if ( of_get_option('of_an_ytube') <> "" ) { ?>
								<a class="socialsite-youtube" href="<?php echo of_get_option('of_an_ytube'); ?>" title="<?php _e('Youtube', 'gabfire'); ?>" rel="nofollow"><span><?php _e('Watch on YouTube','gabfire'); ?></span>
									<i class="fa fa-youtube pull-left"></i>
								</a>
							<?php } ?>	

							<?php if ( of_get_option('of_an_email') <> "" ) { ?>
								<a class="socialsite-envelope" href="<?php echo of_get_option('of_an_email'); ?>" title="<?php _e('Subscribe by Email', 'gabfire'); ?>" rel="nofollow"><span><?php _e('Subscribe by Email','gabfire'); ?></span>
									<i class="fa fa-envelope-o pull-left"></i>
								</a>
							<?php } ?>								

							<a class="socialsite-rss" href="<?php if ( of_get_option('of_rssaddr') <> "" ) { echo of_get_option('of_rssaddr'); } else { echo bloginfo('rss2_url'); } ?>" title="<?php _e('Site feed', 'gabfire'); ?>" rel="nofollow">
								<span><?php _e('Subscribe to RSS','gabfire'); ?></span> <i class="fa fa-rss pull-left"></i>
							</a>
						</li>
					<?php } ?>						
					
					<li class="pull-right gab_headersearch"> <a data-toggle="modal" href="#searchModal"><i class="fa fa-search"></i> <?php _e('Search','gabfire'); ?></a></li>
				</ul>
			</nav>

			<?php get_search_form(); ?>
		</div><!-- /.row Site Masthead Row -->
	
		<div class="row">
			<div class="col-lg-12">
				<div id="header">
					<?php 
					
					/* ******************** IF HEADER WITH A SINGLE IMAGE IS ACTIVATED *************************** */
					if(of_get_option('of_header_type') == 'singleimage') { ?>
						<a href="<?php echo home_url('/'); ?>" title="<?php bloginfo('description'); ?>">
							<img src="<?php echo of_get_option('of_himageurl'); ?>" id="header_banner" alt="<?php bloginfo('name'); ?>" title="<?php bloginfo('name'); ?>"/>
						</a>
					<?php } 
					
					/* ******************** IF HEADER LOGO - BANNER IS ACTIVATED ********************************* */
					elseif(of_get_option('of_header_type') == 'logobanner') { ?>
						
						<div class="logo logo-banner pull-left" style="float:left;margin:0;padding:<?php echo of_get_option('of_padding_top', 0); ?>px 0px <?php echo of_get_option('of_padding_bottom', 0); ?>px <?php echo of_get_option('of_padding_left', 0); ?>px;">	
							<?php if ( of_get_option('of_logo_type', 'text') == 'image') { ?>
								<h1>
									<a href="<?php echo home_url('/'); ?>" title="<?php bloginfo('description'); ?>">
										<img src="<?php echo of_get_option('of_logo'); ?>" alt="<?php bloginfo('name'); ?>" title="<?php bloginfo('name'); ?>"/>
									</a>
								</h1>
							<?php } else { ?>
								<h1>
									<span class="name"><a href="<?php echo home_url('/'); ?>" title="<?php bloginfo('description'); ?>"><?php echo of_get_option('of_logo1'); ?></a></span>
									<span class="slogan"><a href="<?php echo home_url('/'); ?>" title="<?php bloginfo('description'); ?>"><?php echo of_get_option('of_logo2'); ?></a></span>
								</h1>
							<?php } ?>
						</div><!-- logo -->
						
						<div class="header-ad pull-right logo-banner">
							<?php gabfire_dynamic_sidebar('header'); ?>
						</div>
						
						<div class="clearfix"></div>
					<?php } 
					
					/* ******************** IF HEADER WITH QUOTES IS ACTIVATED ********************************* */
					else { ?>
					
						<div class="themequote quoteleft">
						
							<?php if ( of_get_option('of_leftquoteimg') <> '' ) { ?>
								<span class="quoteimg hidden-sm">
									<?php if ( of_get_option('of_lquotelink') <> '' ) { echo '<a href="' . of_get_option('of_lquotelink') . '" rel="bookmark">'; } ?>
										<img src="<?php echo of_get_option('of_leftquoteimg'); ?>" alt="" />
									<?php if ( of_get_option('of_lquotelink') <> '' ) { echo '</a>'; } ?>
								</span>
							<?php } ?>
							
							<span class="quotetext">
							
								<span class="quotecaption">
									<?php 
									if ( of_get_option('of_lquotelink') <> '' ) { echo '<a href="' . of_get_option('of_lquotelink') . '" rel="bookmark">'; } 
										echo of_get_option('of_leftquotecap');
									if ( of_get_option('of_lquotelink') <> '' ) { echo '</a>'; } 
									?>
								</span>
								
								<span class="quote">
									<?php 
									if ( of_get_option('of_lquotelink') <> '' ) { echo '<a href="' . of_get_option('of_lquotelink') . '" rel="bookmark">'; } 
										echo of_get_option('of_leftquotetext');
									if ( of_get_option('of_lquotelink') <> '' ) { echo '</a>'; } 
									?>
								</span>
								
							</span>
						</div><!-- themequote quoteleft -->
						
						<div class="logo quotelogo" style="padding:<?php echo of_get_option('of_padding_top', 0); ?>px 0px <?php echo of_get_option('of_padding_bottom', 0); ?>px <?php echo of_get_option('of_padding_left', 0); ?>px;">	
							<?php if ( of_get_option('of_logo_type', 'text') == 'image') { ?>
								<h1>
									<a href="<?php echo home_url('/'); ?>" title="<?php bloginfo('description'); ?>">
										<img src="<?php echo of_get_option('of_logo'); ?>" alt="<?php bloginfo('name'); ?>" title="<?php bloginfo('name'); ?>"/>
									</a>
								</h1>
							<?php } else { ?>
								<h1>
									<span class="name"><a href="<?php echo home_url('/'); ?>" title="<?php bloginfo('description'); ?>"><?php echo of_get_option('of_logo1'); ?></a></span>
									<span class="slogan"><a href="<?php echo home_url('/'); ?>" title="<?php bloginfo('description'); ?>"><?php echo of_get_option('of_logo2'); ?></a></span>
								</h1>
							<?php } ?>
						</div><!-- logo -->
						
						<div class="themequote quoteright">
							
							<?php if ( of_get_option('of_rightquoteimg') <> '' ) { ?>
								<span class="quoteimg hidden-sm">
									<?php if ( of_get_option('of_rquotelink') <> '' ) { echo '<a href="' . of_get_option('of_rquotelink') . '" rel="bookmark">'; } ?>
										<img src="<?php echo of_get_option('of_rightquoteimg'); ?>" alt="" />
									<?php if ( of_get_option('of_rquotelink') <> '' ) { echo '</a>'; } ?>
								</span>
							<?php } ?>
							
							<span class="quotetext">
							
								<span class="quotecaption">
									<?php 
									if ( of_get_option('of_rquotelink') <> '' ) { echo '<a href="' . of_get_option('of_rquotelink') . '" rel="bookmark">'; } 
										echo of_get_option('of_rightquotecap');
									if ( of_get_option('of_rquotelink') <> '' ) { echo '</a>'; } 
									?>
								</span>
								<span class="quote">
									<?php 
									if ( of_get_option('of_rquotelink') <> '' ) { echo '<a href="' . of_get_option('of_rquotelink') . '" rel="bookmark">'; } 
										echo of_get_option('of_rightquotetext');
									if ( of_get_option('of_rquotelink') <> '' ) { echo '</a>'; } 
									?>
								</span>
								
							</span>
							
						</div><!-- themequote quoteright -->
					<?php } ?>
						

				</div><!-- /header -->	
			</div><!-- /col-lg-12 -->	
		</div><!-- /row -->	
		
		<div class="row site-nav">
			<div class="col-lg-12">
				
				<nav class="main-navigation" itemscope="itemscope" itemtype="http://schema.org/SiteNavigationElement" role="navigation">
					<ul class="mainnav responsive_menu">
						<li><a href="<?php echo home_url('/'); ?>"><?php _e('HOME', 'gabfire'); ?></a></li>
						<?php
						if ( has_nav_menu( 'primary' ) ) {
							wp_nav_menu( array('theme_location' => 'primary', 'container' => false, 'items_wrap' => '%3$s'));
						} else {
							wp_list_categories('orderby='. of_get_option('of_order_cats') .'&order='. of_get_option('of_sort_cats') .'&title_li=&exclude='. of_get_option('of_ex_cats'));
						}

						if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
						global $woocommerce;
						$cart_url = $woocommerce->cart->get_cart_url();
							echo "<li class='woo-cartlink'><a href='$cart_url'><i class='fa fa-shopping-cart'></i> " . __('Cart', 'gabfire') . "</a></li>";
						} ?>
					</ul>
				</nav>
				
				<nav class="secondary-navigation" itemscope="itemscope" itemtype="http://schema.org/SiteNavigationElement" role="navigation">
					<ul class="subnav responsive_menu">
						<?php if ( has_nav_menu( 'secondary' ) ) {
							wp_nav_menu( array('theme_location' => 'secondary', 'container' => false, 'items_wrap' => '%3$s'));
						} else { ?>
							<?php wp_list_pages('sort_column=menu_order&title_li=&exclude='. of_get_option('of_ex_pages')); ?>
						<?php } ?>	
					</ul>
				</nav>				
				
			</div>
		</div>	
	</header>