<?php
/**
 * Newspaper Child: enqueue styles + register custom TagDiv block (fixed API)
 */

// Enqueue parent + child styles
add_action('wp_enqueue_scripts', function() {
    global $post;

    wp_enqueue_style('newspaper-parent', get_template_directory_uri() . '/style.css', [], null);
    wp_enqueue_style('newspaper-child', get_stylesheet_directory_uri() . '/style.css', ['newspaper-parent'], wp_get_theme()->get('Version'));
    
    wp_enqueue_script('functions.js', get_stylesheet_directory_uri() . '/functions.js', array('jquery'), false);
    wp_enqueue_script('helper.js', get_stylesheet_directory_uri() . '/helper.js');
    
    if(current_user_can('edit_post', $post->ID)){
        wp_enqueue_script('dragdrop.js', get_stylesheet_directory_uri() . '/dragdrop.js', array('jquery'), false);
        wp_enqueue_script('edit.js', get_stylesheet_directory_uri() . '/edit.js', array('jquery'), false);
        wp_enqueue_style('edit-css', get_stylesheet_directory_uri() . '/edit.css', [], null);
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script('popup.js', get_stylesheet_directory_uri() . '/popup.js');
    }
});

include 'actions.php';

if ( is_user_logged_in() ) {
    include __DIR__ . '/edit.php';
}