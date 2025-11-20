<?php
/**
 * Newspaper Child: enqueue styles + register custom TagDiv block (fixed API)
 */

// Enqueue parent + child styles
add_action('wp_enqueue_scripts', function() {
    global $post;

    wp_enqueue_style('newspaper-parent', get_template_directory_uri() . '/style.css', [], null);
    wp_enqueue_style('newspaper-child', get_stylesheet_directory_uri() . '/style.css', ['newspaper-parent'], wp_get_theme()->get('Version'));
    wp_enqueue_style('newspaper-child', get_stylesheet_directory_uri() . '/editorStyle.css', ['newspaper-parent'], wp_get_theme()->get('Version'));
    
    wp_enqueue_script('functions.js', get_stylesheet_directory_uri() . '/functions.js', array('jquery'), false);
    wp_enqueue_script('helper.js', get_stylesheet_directory_uri() . '/helper.js');
    
    if(current_user_can('edit_post', $post->ID)){
        if ( current_user_can('administrator') || current_user_can('editor') ) {
            wp_enqueue_script('dragdrop.js', get_stylesheet_directory_uri() . '/dragdrop.js', array('jquery'), false);
            wp_enqueue_script('edit.js', get_stylesheet_directory_uri() . '/edit.js', array('jquery'), false);
            wp_enqueue_style('edit-css', get_stylesheet_directory_uri() . '/edit.css', [], null);
            wp_enqueue_script('jquery-ui-sortable');
            wp_enqueue_script('popup.js', get_stylesheet_directory_uri() . '/popup.js');
        }
    }
});

//  Shortcode for imagetext from custom field
add_shortcode('featured_caption', 'featured_caption_shortcode');
function featured_caption_shortcode($atts, $content = null) {
    $atts = shortcode_atts([
        'tag'   => 'p',                    // HTML-tag omkring teksten
        'class' => 'featured-caption',     // CSS-klasse
    ], $atts, 'featured_caption');

    $caption = get_featured_caption_or_media_caption();

    if (empty($caption)) {
        return ''; // intet at vise
    }

    return sprintf(
        '<%1$s class="%2$s">%3$s</%1$s>',
        esc_attr($atts['tag']),
        esc_attr($atts['class']),
        esc_html($caption)
    );
}

function get_featured_caption_or_media_caption($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }

    // Find featured image
    $thumb_id = get_post_thumbnail_id($post_id);
    if (!$thumb_id) {
        return '';
    }

    // 1) Forsøg at bruge custom felt
    $custom = trim(get_post_meta($post_id, 'featured_caption', true));
    if (!empty($custom)) {
        return $custom;
    }

    // 2) Fald tilbage til billedecaption fra media library
    /*$media_caption = wp_get_attachment_caption($thumb_id);
    if (!empty($media_caption)) {
        return $media_caption;
    }
    */

    return '';
}

/**
 * Returnerer en array med [url, version] til brug i wp_enqueue_style/script.
 *
 * @param string $relative_path F.eks. '/edit.css' eller '/js/admin.js'
 * @return array [string $url, int|null $version]
 */
function theme_asset_with_cache_busting($relative_path) {
    $file_path = get_stylesheet_directory() . $relative_path;
    $file_url  = get_stylesheet_directory_uri() . $relative_path;

    if (file_exists($file_path)) {
        return [$file_url, filemtime($file_path)];
    }

    // Hvis filen ikke findes, returnér URL uden version
    return [$file_url, null];
}


if ( is_user_logged_in() ) {
    
    if ( current_user_can('administrator') || current_user_can('editor') ) {
        // Brugeren er enten admin eller editor
        include __DIR__ . '/actions.php';
        include __DIR__ . '/edit.php';
    }

    // Aktiver editor styles
    add_action('after_setup_theme', function() {
        add_theme_support('editor-styles');
        //add_editor_style('editor-style.css'); // filen skal ligge i roden af dit (child) theme
        list($url, $ver) = theme_asset_with_cache_busting('/editor-style.css');
        wp_enqueue_style('editor-style', $url, [], $ver);
    });

    // Tilføj knap til TinyMCE-toolbar
    function my_factbox_tinymce_button( $buttons ) {
        $buttons[] = 'factboxleft_button';    
        $buttons[] = 'factboxright_button';
        return $buttons;
    }
    add_filter( 'mce_buttons', 'my_factbox_tinymce_button' );

    // Registrér TinyMCE-plugin med vores JS
    function my_factbox_tinymce_plugin( $plugins ) {
        $plugins['factbox_buttons'] = get_stylesheet_directory_uri() . '/js/factbox-faktaboks.js';
        return $plugins;
    }
    add_filter( 'mce_external_plugins', 'my_factbox_tinymce_plugin' );

    add_action('add_meta_boxes', 'my_add_featured_caption_metabox');
    function my_add_featured_caption_metabox() {
        add_meta_box(
            'featured_caption_box',
            'Billedtekst til udvalgt billede',
            'my_featured_caption_callback',
            'post',
            'side',
            'high'
        );
    }

    function my_featured_caption_callback($post) {
        $value = get_post_meta($post->ID, 'featured_caption', true);
        echo '<textarea style="width:100%;height:80px;" name="featured_caption">' . esc_textarea($value) . '</textarea>';
    }

    add_action('save_post', 'my_save_featured_caption');
    function my_save_featured_caption($post_id) {
        if (array_key_exists('featured_caption', $_POST)) {
            update_post_meta($post_id, 'featured_caption', sanitize_text_field($_POST['featured_caption']));
        }
    }

    add_action('admin_enqueue_scripts', function($hook) {
        // Kun på indlægs-redigering
        if ($hook !== 'post.php' && $hook !== 'post-new.php') {
            return;
        }

        // Sørg for at media/featured image-scripts er tilgængelige
        wp_enqueue_media();

        // Register + enqueue dit eget script
        wp_enqueue_script(
            'my-featured-caption-live-sync',
            get_stylesheet_directory_uri() . '/js/featured-caption-live-sync.js',
            ['jquery', 'media-editor'],
            '1.0',
            true
        );
    });


}
