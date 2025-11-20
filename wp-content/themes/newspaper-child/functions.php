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
        
        wp_enqueue_style('editor-style', get_stylesheet_directory_uri() . '/editor-style.css', [], null);
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

if ( is_user_logged_in() ) {
    
    if ( current_user_can('administrator') || current_user_can('editor') ) {
        // Brugeren er enten admin eller editor
        include __DIR__ . '/actions.php';
        include __DIR__ . '/edit.php';
    }

    // Aktiver editor styles
    add_action('after_setup_theme', function() {
        add_theme_support('editor-styles');
        add_editor_style('editor-style.css'); // filen skal ligge i roden af dit (child) theme
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

    /**
     * Opdater 'featured_caption' KUN når der er valgt et NYT featured image,
     * og kun hvis featured_caption er tom.
     *
     * Fallback-rækkefølge:
     * 1) Billedtekst (post_excerpt)
     * 2) Beskrivelse (post_content)
     * 3) Alt-tekst (_wp_attachment_image_alt)
     */
    add_action('save_post', 'my_featured_caption_multi_fallback', 999, 3);
    function my_featured_caption_multi_fallback($post_id, $post, $update) {

        // Undgå autosave og revisioner
        if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
            return;
        }

        // Tjek rettigheder
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $meta_key_caption   = 'featured_caption';
        $meta_key_prev_thumb = '_prev_thumbnail_id';

        // Hent nuværende featured image
        $thumb_id = (int) get_post_thumbnail_id($post_id);

        // Hent tidligere gemt thumbnail-id (vores egen tracking)
        $prev_thumb_id = (int) get_post_meta($post_id, $meta_key_prev_thumb, true);

        // Hvis der ingen thumbnail er, så opdater bare tracking og stop
        if (!$thumb_id) {
            return;
        }

        // Hvis thumbnail IKKE er ændret siden sidst-> gør ingenting
        if ($thumb_id === $prev_thumb_id) {
            return;
        }

        // På dette tidspunkt VED vi, at der er valgt et NYT billede.
        // Opdater tracking med det nye id (så vi ikke rammer igen ved næste save)
        update_post_meta($post_id, $meta_key_prev_thumb, $thumb_id);

        // Stop hvis featured_caption allerede har tekst (respektér manuelle værdier)
        /*if (get_post_meta($post_id, $meta_key_caption, true)) {
            return;
        }*/

        // Hent attachment
        $attachment = get_post($thumb_id);
        if (!$attachment) {
            return;
        }

        // 1) Billedtekst (caption / post_excerpt)
        $imageText = trim($attachment->post_excerpt);

        // 2) Beskrivelse (post_content)
        $desc = trim($attachment->post_content);

        // 3) Alt-tekst
        $alt = trim(get_post_meta($thumb_id, '_wp_attachment_image_alt', true));

        // Vælg første der ikke er tom
        $final = '';
        if ($imageText !== '') {
            $final = $imageText;
        } elseif ($desc !== '') {
            $final = $desc;
        } elseif ($alt !== '') {
            $final = $alt;
        }

        // Gem hvis vi fandt noget
        if ($final !== '') {
            update_post_meta($post_id, $meta_key_caption, $final);

            // Sæt et flag så vi kan vise en notice på næste load
            update_post_meta($post_id, '_featured_caption_just_inserted_1', 1);
            update_post_meta($post_id, '_featured_caption_just_inserted_2', 1);
        }
    }

    //  Show message about image text inserted
    add_action('admin_notices', 'my_featured_caption_inserted_notice');
    function my_featured_caption_inserted_notice() {
        global $pagenow;

        // Kun på post-redigeringssiden
        if ($pagenow !== 'post.php') {
            return;
        }

        if (empty($_GET['post'])) {
            return;
        }

        $post_id = (int) $_GET['post'];

        // Tjek om vores flag er sat
        $just_inserted = get_post_meta($post_id, '_featured_caption_just_inserted_1', true);
        if (!$just_inserted) {
            return;
        }

        // Fjern flaget igen så den kun vises én gang
        delete_post_meta($post_id, '_featured_caption_just_inserted_1');
        ?>
        <div class="notice notice-success is-dismissible">
            <p>Billedtekst fra det valgte billede er automatisk indsat i <strong>Billedtekst til udvalgt billede</strong>.</p>
        </div>
        <?php
    }

    add_action('admin_footer-post.php', 'my_featured_caption_inline_notice');
    function my_featured_caption_inline_notice() {
        if (empty($_GET['post'])) {
            return;
        }

        $post_id = (int) $_GET['post'];

        // Har vi lige indsat en featured_caption?
        $just_inserted = get_post_meta($post_id, '_featured_caption_just_inserted_2', true);
        if (!$just_inserted) {
            return;
        }

        // Fjern flag – vi vil kun vise beskeden én gang
        delete_post_meta($post_id, '_featured_caption_just_inserted_2');
        ?>
        <script>
        (function() {
            // Vent et kort øjeblik til DOM og Gutenberg er klar
            function showFeaturedCaptionNotice() {
                // Forsøg at finde featured image-boksen
                var featuredBox =
                    document.querySelector('#postimagediv') || // Classic editor / metabox
                    document.querySelector('.editor-post-featured-image') || // Gutenberg center
                    document.querySelector('.edit-post-featured-image__container') || // andre varianter
                    document.querySelector('[data-panel-id="featured-image"]'); // Gutenberg sidebar panel

                if (!featuredBox) {
                    return;
                }

                // Opret en tydelig boks
                var notice = document.createElement('div');
                notice.className = 'my-featured-caption-notice';
                notice.innerHTML = '<strong class="info">Billedtekst indsat automatisk</strong><br>' +
                    'Teksten fra det valgte billede er nu kopieret ind i <code>Billedtekst til udvalgt billede</code>. ' +
                    'Du kan tilpasse den i feltet herunder, hvis du vil.';

                // Indsæt boksen lige før featured image-boksen
                featuredBox.parentNode.insertBefore(notice, featuredBox);

                // Scroll ned til området
                //featuredBox.scrollIntoView({ behavior: 'smooth', block: 'center' });

                // Tilføj highlight-ramme
                featuredBox.classList.add('my-featured-caption-highlight');

                // Fjern highlight igen efter et par sekunder
                setTimeout(function() {
                    featuredBox.classList.remove('my-featured-caption-highlight');
                }, 5000);
            }

            // Kør når siden er klar
            if (document.readyState === 'complete' || document.readyState === 'interactive') {
                setTimeout(showFeaturedCaptionNotice, 500);
            } else {
                document.addEventListener('DOMContentLoaded', function() {
                    setTimeout(showFeaturedCaptionNotice, 500);
                });
            }
        })();
        </script>

        <style>
        .my-featured-caption-notice {
            background: #f0f9ff;
            border-left: 4px solid #2271b1;
            padding: 12px 14px;
            margin-bottom: 12px;
            border-radius: 4px;
            font-size: 13px;
            position: relative;
            /* 🔥 Bounce animation når boksen dukker op */
            animation: my-caption-bounce 0.7s ease-out;
        }

        .my-featured-caption-notice .info{
            margin-left:15px;
        }

        .my-featured-caption-notice::before {
            content: "✨";
            position: absolute;
            left: 8px;
            top: 10px;
            font-size: 16px;
        }

        .my-featured-caption-notice code {
            background: #e2eef7;
            padding: 1px 4px;
            border-radius: 3px;
        }

        /* Highlight rundt om featured image-boksen */
        .my-featured-caption-highlight {
            box-shadow: 0 0 0 3px #2271b1;
            animation: my-caption-pulse 1.2s ease-out 0s 3;
        }

        /* 🔁 Bounce keyframes til selve boksen */
        @keyframes my-caption-bounce {
            0% {
                transform: scale(0.9) translateY(-8px);
                opacity: 0;
            }
            40% {
                transform: scale(1.03) translateY(0);
                opacity: 1;
            }
            70% {
                transform: scale(0.98) translateY(-3px);
            }
            100% {
                transform: scale(1) translateY(0);
            }
        }

        /* Pulsende highlight omkring billed-boksen */
        @keyframes my-caption-pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(34, 113, 177, 0.8);
            }
            50% {
                box-shadow: 0 0 0 6px rgba(34, 113, 177, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(34, 113, 177, 0);
            }
        }

        /* (valgfrit) lidt hensyn til brugere med reduced motion */
        @media (prefers-reduced-motion: reduce) {
            .my-featured-caption-notice,
            .my-featured-caption-highlight {
                animation: none !important;
            }
        }
    </style>
        <?php
    }

}
