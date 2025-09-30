<?php
echo '<style type="text/css" media="screen">';

	if(of_get_option('of_bodytype', 0) == 1) {
		echo "body {";
		$body_font = of_get_option('of_body_font');
		$body_bg = of_get_option('of_sitebg');
		$headingfont = of_get_option('of_headingfontfamily');
		if ($body_font){
			if ($body_font['size']){ echo "font-size: ".$body_font['size'].";"; }
			if ($body_font['face']){ echo "font-family: ".$body_font['face'].";"; }
			if ($body_font['style']){ echo "font-weight: ".$body_font['style'].";"; }
			if ($body_font['color']){ echo "color: ".$body_font['color'].";"; }
		}
		if ($body_bg){
			if ($body_bg == none ){
				echo "background-image: none;";
			} else {
				echo "background: url(".get_template_directory_uri()."/framework/images/patterns/$body_bg.jpg) repeat;";
			}
		}
		echo "}";
		
		if ($body_font){
			echo "article p,.entry p {";
			if ($body_font['size']){ echo "font-size: ".$body_font['size'].";"; }
			if ($body_font['face']){ echo "font-family: ".$body_font['face'].";"; }
			if ($body_font['style']){ echo "font-weight: ".$body_font['style'].";"; }
			if ($body_font['color']){ echo "color: ".$body_font['color'].";"; }
			echo "}";
		}
		
		if ($headingfont){echo ".entry-title,.entry-title.large-posttitle, .widgettitle{font-family: $headingfont !important }";}
	}
	
	if(of_get_option('of_editcolors', 0) == 1) {
		$primarycolor = of_get_option('of_primarycolor');
		$titlecolor = of_get_option('of_primarycolor');
		$titlecolorhover = of_get_option('of_primarycolor');
		
		echo "a,
		.catname,
		.catname span ,
		.catname a:hover,
		.widget a:hover,
		nav .mastheadnav li a:hover,
		.masthead_date,
		#header .slogan a,
		#header .themequote .quotecaption,
		#header .themequote .quotecaption a,
		#header .themequote .quotecaption a:hover,
		.footercats li a:hover{color: $primarycolor }

		nav .mastheadnav li ul li a:hover,
		nav .mastheadnav li ul li a:hover,nav .mastheadnav li li.has-child-menu > a:hover,
		#noslider-left .catname,
		#noslider-left .widgettitle,
		#secondary-left .catname,
		#secondary-left .widgettitle,
		.archive-pagination .page-numbers:hover {background-color: $primarycolor }

		nav .mastheadnav > li:first-child,
		#featured-nav li.cycle-pager-active img,
		#featured-nav li:hover img {border-color: $primarycolor }
		.arrow-right {border-left:7px solid #222 !important}
		
		.entry-title a {color: $titlecolor } .entry-title a:hover {color: $titlecolorhover }";
	}

	if(of_get_option('of_editnav2', 0) == 1) {
		$nav2bg                = of_get_option('of_nav2bg');
		$of_nav2border         = of_get_option('of_nav2border');
		$of_nav2link           = of_get_option('of_nav2link');
		$of_nav2linkcurrent    = of_get_option('of_nav2linkcurrent');
		$of_nav2linkhover      = of_get_option('of_nav2linkhover');
		$of_nav2alink          = of_get_option('of_nav2alink');
		$of_nav2alinkhover     = of_get_option('of_nav2alinkhover');
		$of_nav2aborder        = of_get_option('of_nav2aborder');
		$of_nav2abg            = of_get_option('of_nav2abg');
		$of_nav2abghover       = of_get_option('of_nav2abghover');
		$of_nav2alinkcurrent   = of_get_option('of_nav2alinkcurrent');
		$of_nav2alinkbgcurrent = of_get_option('of_nav2alinkbgcurrent');

		echo "
		nav.main-navigation {background-image:url( $nav2bg ) repeat;border-color: $of_nav2border }
		/* First level menu */
		nav .mainnav li a {color: $of_nav2link }
		nav .mainnav li.current_page_item > a,
		nav .mainnav li.current-cat > a, 
		nav .mainnav li.current-menu-item > a,
		nav .mainnav li.current-cat-parent > a {color: $of_nav2linkcurrent }
		nav .mainnav li a:hover {color: $of_nav2linkhover }

		/*Second level selected menu item */
		nav .mainnav li li.current_page_item a,
		nav .mainnav li li.current-cat a, 
		nav .mainnav li li.current-menu-item a,
		nav .mainnav li li.current-cat-parent a {color:$of_nav2alinkcurrent;background:$of_nav2alinkbgcurrent }		

		/*Second level link color*/
		nav .mainnav li ul li a {color: $of_nav2alink }
		/* Second level hovered link color */
		nav .mainnav li ul li a:hover {color: $of_nav2alinkhover }
		/* Second level border color */
		nav .mainnav li ul,nav .mainnav li ul li a {border-color: $of_nav2aborder }
		/*Second level background color*/
		nav .mainnav li ul li {background-color: $of_nav2abg }
		/* Second level hovered background color */
		nav .mainnav li ul li a:hover {background-color: $of_nav2abghover }";
	}

	if(of_get_option('of_editnav3', 0) == 1) {
		$nav3bg                = of_get_option('of_nav3bg');
		$of_nav3border         = of_get_option('of_nav3border');
		$of_nav3link           = of_get_option('of_nav3link');
		$of_nav3linkcurrent    = of_get_option('of_nav3linkcurrent');
		$of_nav3linkhover      = of_get_option('of_nav3linkhover');
		$of_nav3alink          = of_get_option('of_nav3alink');
		$of_nav3alinkhover     = of_get_option('of_nav3alinkhover');
		$of_nav3aborder        = of_get_option('of_nav3aborder');
		$of_nav3abg            = of_get_option('of_nav3abg');
		$of_nav3abghover       = of_get_option('of_nav3abghover');
		$of_nav3alinkcurrent   = of_get_option('of_nav3alinkcurrent');
		$of_nav3alinkbgcurrent = of_get_option('of_nav3alinkbgcurrent');

		echo "
		nav.secondary-navigation {background-color:url( $nav3bg ) repeat;border-color: $of_nav3border }
		/* First level menu */
		nav .subnav li a {color: $of_nav3link }
		nav .subnav li.current_page_item > a,
		nav .subnav li.current-cat > a, 
		nav .subnav li.current-menu-item > a,
		nav .subnav li.current-cat-parent > a {color: $of_nav3linkcurrent }
		nav .subnav li a:hover {color: $of_nav3linkhover }

		/*Second level selected menu item */
		nav .subnav li li.current_page_item a,
		nav .subnav li li.current-cat a, 
		nav .subnav li li.current-menu-item a,
		nav .subnav li li.current-cat-parent a {color:$of_nav3alinkcurrent;background:$of_nav3alinkbgcurrent }		

		/*Second level link color*/
		nav .subnav li ul li a {color: $of_nav3alink }
		/* Second level hovered link color */
		nav .subnav li ul li a:hover {color: $of_nav3alinkhover }
		/* Second level border color */
		nav .subnav li ul,nav .subnav li ul li a {border-color: $of_nav3aborder }
		/*Second level background color*/
		nav .subnav li ul li {background-color: $of_nav3abg }
		/* Second level hovered background color */
		nav .subnav li ul li a:hover {background-color: $of_nav3abghover }";
	}

echo "</style>";