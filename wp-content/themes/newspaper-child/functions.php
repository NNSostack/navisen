<?php
/**
 * Newspaper Child: enqueue styles + register custom TagDiv block (fixed API)
 */

// Enqueue parent + child styles
add_action('wp_enqueue_scripts', function() {
    global $post;
    
    wp_enqueue_style('newspaper-parent', get_template_directory_uri() . '/style.css', [], null);
    wp_enqueue_style('newspaper-child', get_stylesheet_directory_uri() . '/style.css', ['newspaper-parent'], filemtime(get_stylesheet_directory() .  '/style.css'));
    wp_enqueue_style('newspaper-faktaboks', get_stylesheet_directory_uri() . '/faktaboks.css', ['newspaper-parent'], filemtime(get_stylesheet_directory() .  '/faktaboks.css'));
    
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
    
    if (current_user_can('administrator') || current_user_can('editor') ) {
        // Brugeren er enten admin eller editor
        include __DIR__ . '/actions.php';
        include __DIR__ . '/edit.php';
    }

    // Aktiver editor styles
    add_action('after_setup_theme', function() {
        add_theme_support('editor-styles');

        add_editor_style([
            'editor-style.css',
            'faktaboks.css',
        ]);
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

//  Order by menu_order to be able to sort manualley
add_action('pre_get_posts', function($query) {
    if (is_admin() || !$query->is_category()) {
        return;
    }
    
    // Hent nuværende kategoriobjekt
    $cat = $query->queried_object;

    // Hvis det er 'alle-nyheder', så spring over
    if ($cat && isset($cat->slug) && $cat->slug === 'alle-nyheder') {
        return;
    }

    // --- Find alle underkategorier under "lister"
    $list_parent = get_term_by('slug', 'lister', 'category');
    if (!$list_parent) {
        return;
    }

    $list_child_ids = get_terms([
        'taxonomy'   => 'category',
        'child_of'   => $list_parent->term_id,
        'hide_empty' => false,
        'fields'     => 'ids',
    ]);

    if ($query->is_category($list_child_ids)) {
        $query->set('orderby', 'menu_order date');
        $query->set('order', 'ASC');
    }
});

//  Fjern alle-nyheder og lister og underkategorier 
add_filter('get_the_terms', function($terms, $post_id, $taxonomy) {

    if (
        $taxonomy !== 'category' ||
        is_admin() ||
        (defined('REST_REQUEST') && REST_REQUEST) ||
        (defined('DOING_CRON') && DOING_CRON) ||
        (defined('DOING_AJAX') && DOING_AJAX) ||
        is_preview() ||
        empty($terms) ||
        !is_array($terms)
    ) {
        return $terms;
    }

    $all_news = get_category_by_slug('alle-nyheder');
    $lister   = get_category_by_slug('lister');

    $all_news_id = $all_news ? (int) $all_news->term_id : 0;
    $lister_id   = $lister   ? (int) $lister->term_id   : 0;

    // Helper: er term under lister (på ethvert niveau)?
    $is_under_lister = function(int $term_id) use ($lister_id): bool {
        if ($lister_id <= 0) return false;
        if ($term_id === $lister_id) return true; // selve parent
        $anc = get_ancestors($term_id, 'category');
        return !empty($anc) && in_array($lister_id, array_map('intval', $anc), true);
    };

    // 1) Byg et "effektivt" term-sæt hvor lister + listers børn IKKE tæller med
    // (de skal alligevel aldrig kunne vises)
    $effective_ids = [];
    foreach ($terms as $t) {
        $tid = (int) $t->term_id;
        if ($is_under_lister($tid)) {
            continue;
        }
        $effective_ids[$tid] = true;
    }
    $effective_ids = array_keys($effective_ids);

    // 2) Hvis (effektivt) kun alle-nyheder er valgt -> vis alle-nyheder (og skjul lister-stuff)
    $force_show_all_news_only = (
        $all_news_id > 0 &&
        count($effective_ids) === 1 &&
        $effective_ids[0] === $all_news_id
    );

    // 3) Filtrér termer:
    // - Fjern ALT under lister (altid)
    // - Fjern alle-nyheder hvis der (effektivt) også er andre kategorier end den
    $filtered = [];
    foreach ($terms as $term) {
        $tid = (int) $term->term_id;

        // ALDRIG vis noget under lister (inkl. lister selv)
        if ($is_under_lister($tid)) {
            continue;
        }

        // alle-nyheder:
        if ($all_news_id > 0 && $tid === $all_news_id) {
            // vis den kun hvis den effektivt er alene
            if ($force_show_all_news_only) {
                $filtered[] = $term;
            }
            // ellers skjul den
            continue;
        }

        // andre kategorier beholdes
        $filtered[] = $term;
    }

    // 4) Skjul parents hvis et barn er valgt på samme post (i det der er tilbage)
    if (!empty($filtered)) {
        $ids = array_values(array_unique(array_map(fn($t) => (int)$t->term_id, $filtered)));
        $lookup = array_fill_keys($ids, true);

        $hide_parents = [];
        foreach ($filtered as $t) {
            $parent_id = isset($t->parent) ? (int)$t->parent : 0;
            if ($parent_id > 0 && isset($lookup[$parent_id])) {
                $hide_parents[$parent_id] = true;
            }
        }

        if (!empty($hide_parents)) {
            $filtered = array_values(array_filter($filtered, function($t) use ($hide_parents) {
                return empty($hide_parents[(int)$t->term_id]);
            }));
        }
    }

    return $filtered;

}, 10, 3);

//  Only get alle-nyheder if not in one of the other lists
add_action('pre_get_posts', function($query) {
    if (is_admin() || !$query->is_category() || !$query->is_main_query()) {
        return;
    }
    
    if ($query->is_category('alle-nyheder')) {

        // --- Find alle underkategorier under "lister"
        $list_parent = get_term_by('slug', 'lister', 'category');
        if (!$list_parent) {
            return;
        }

        $list_child_ids = get_terms([
            'taxonomy'   => 'category',
            'child_of'   => $list_parent->term_id,
            'hide_empty' => false,
            'fields'     => 'ids',
        ]);

        if (is_wp_error($list_child_ids) || empty($list_child_ids)) {
            return;
        }

        // --- Udeluk alle posts, der er i disse underkategorier
        $query->set('category__not_in', $list_child_ids);
    }
});

/**
 * Redirect direkte til Microsoft Entra ID uden at vise WordPress-login.
 */
add_action('login_init', function () {
    // Nødadgang til almindeligt WordPress-login.
    if (
        isset($_GET['local-login']) &&
        $_GET['local-login'] === '1'
    ) {
        return;
    }

    // Gør det kun på den almindelige login-side.
    $action = isset($_REQUEST['action'])
        ? sanitize_key($_REQUEST['action'])
        : 'login';

    if ($action !== 'login') {
        return;
    }

    // Hold hele login-sidens output tilbage.
    ob_start();
}, 1);

add_action('login_footer', function () {
    if (
        isset($_GET['local-login']) &&
        $_GET['local-login'] === '1'
    ) {
        return;
    }

    if (ob_get_level() === 0) {
        return;
    }

    $html = ob_get_clean();

    // Find Microsoft-knappens dynamisk genererede URL.
    if (
        preg_match(
            '/<a[^>]*class=["\'][^"\']*\bwal-button\b[^"\']*["\'][^>]*href=["\']([^"\']+)["\']/i',
            $html,
            $matches
        )
    ) {
        $microsoft_url = html_entity_decode(
            $matches[1],
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        );

        // wp_redirect bruges, fordi destinationen er et eksternt domæne.
        wp_redirect($microsoft_url);
        exit;
    }

    // Vis login-siden normalt, hvis Microsoft-linket ikke kunne findes.
    echo $html;
}, PHP_INT_MAX);

/*
 * Plugin Name: Protect REST-API
 * Auther: Frank Wagner
 * Description: Require login to access REST-API
 */

add_filter( 'rest_authentication_errors', function( $result ) {
    // If a previous authentication check was applied,
    // pass that result along without modification.
    if ( true === $result || is_wp_error( $result ) ) {
        return $result;
    }

    // No authentication has been performed yet.
    // Return an error if user is not logged in.
    if ( ! is_user_logged_in() ) {
        return new WP_Error(
            'rest_not_logged_in',
            __( 'You are not currently logged in.' ),
            array( 'status' => 401 )
        );
    }

    // Our custom authentication check should have no effect
    // on logged-in requests
    return $result;
});

/*

add_filter('pre_http_request', function ($preempt, $parsed_args, $url) {

    $logFile = WP_CONTENT_DIR . '/outgoing-requests.log';

    $data = [
        'time'    => date('Y-m-d H:i:s'),
        'type'    => 'before_request',
        'url'     => $url,
        'method'  => $parsed_args['method'] ?? 'GET',
        'headers' => $parsed_args['headers'] ?? [],
        'body'    => $parsed_args['body'] ?? null,
    ];

    file_put_contents(
        $logFile,
        json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL . str_repeat('-', 80) . PHP_EOL,
        FILE_APPEND
    );

    return false;

}, 10, 3);


add_action('http_api_debug', function ($response, $context, $class, $parsed_args, $url) {

    $logFile = WP_CONTENT_DIR . '/outgoing-requests.log';

    $data = [
        'time'    => date('Y-m-d H:i:s'),
        'type'    => 'after_request',
        'context' => $context,
        'url'     => $url,
        'method'  => $parsed_args['method'] ?? 'GET',
        'response_code' => is_wp_error($response)
            ? 'WP_ERROR'
            : wp_remote_retrieve_response_code($response),
        'error' => is_wp_error($response)
            ? $response->get_error_message()
            : null,
    ];

    file_put_contents(
        $logFile,
        json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL . str_repeat('-', 80) . PHP_EOL,
        FILE_APPEND
    );

}, 10, 5);

*/