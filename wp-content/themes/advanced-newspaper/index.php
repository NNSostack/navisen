<?php 
get_header(); 

/* Select homepage template */
if (of_get_option('of_homepage') == 'advanced') {
	get_template_part('templates/home-withslider');
} else {
	get_template_part('templates/home-noslider');
}

get_footer();