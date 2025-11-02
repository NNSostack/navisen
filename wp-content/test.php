<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');

print_r("Featured: " . get_option('navisen_featured'));
echo "<br/>";
print_r("Left: " . get_option('navisen_left_list'));
echo "<br/>";
print_r("Mid: " . get_option('navisen_mid_list'));



function mark_posts_with_category($post_ids_csv, $category_id) {
    // Split komma-listen
    $post_ids = array_map('intval', explode(',', $post_ids_csv));

    foreach ($post_ids as $post_id) {
        if (get_post($post_id)) {
            // Tilføj kategorien uden at fjerne eksisterende
            wp_set_post_categories($post_id, [$category_id], true);
        }
    }
}

/**
 * Sæt udvalgt billede ud fra første <img> i indlæggets indhold,
 * men KUN hvis billedet allerede findes i mediebiblioteket.
 *
 * @param string $post_type
 * @param int    $batch_size
 */
function nns_set_featured_from_first_img_existing_only($post_type = 'post', $batch_size = 50) {
    $q = new WP_Query([
        'post_type'      => $post_type,
        'post_status'    => 'publish',
        'posts_per_page' => $batch_size,
        'meta_query'     => [
            [
                'key'     => '_thumbnail_id',
                'compare' => 'NOT EXISTS',
            ],
        ],
        'fields'         => 'ids',
    ]);

    if ( ! $q->have_posts() ) {
        msg('[nns] Ingen indlæg uden udvalgt billede.');
        return;
    }

    foreach ( $q->posts as $post_id ) {
        if ( has_post_thumbnail($post_id) ) {
            echo "Has thumbnail</br>";
            continue;
        }

        $content = get_post_field('post_content', $post_id);
        if ( empty($content) ) {
            echo "Empty</br>";
            continue;
        }

        $img_src = nns_extract_first_img_src($content);
        if ( ! $img_src ) {
            echo "No Image<br/>";
            continue;
        }

        $normalized = nns_normalize_image_url($img_src);
        if ( ! $normalized ) {
            continue;
        }
        
        // Skip eksterne domæner
        //if ( nns_is_external_url($normalized) ) {
        //    continue;
        //}

        // Find attachment i mediebiblioteket uden at sideloade
        $attachment_id = nns_find_existing_attachment_by_url($normalized);

        if ( $attachment_id ) {
            set_post_thumbnail($post_id, $attachment_id);
            msg(sprintf('[nns] Sat thumb: post %d -> attachment %d', $post_id, $attachment_id));
        } else {
            msg(sprintf('[nns] INTET match i media lib for post %d (url: %s)', $post_id, $normalized));
        }
    }
}

function msg($msg){
    error_log($msg);
    echo $msg . "<br/>";
}

/**
 * Find første <img> src i HTML.
 */
function nns_extract_first_img_src($html) {
    if ( preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $html, $m) ) {
        $src = trim($m[1]);
        if ( stripos($src, 'data:') === 0 ) {
            return null;
        }
        return $src;
    }
    return null;
}

/**
 * Normaliser billed-URL til absolut URL.
 */
function nns_normalize_image_url($url) {
    if ( preg_match('~^https?://~i', $url) ) {
        // Fjern evt. querystring/fragment for mere stabil matching
        $parts = wp_parse_url($url);
        if ( empty($parts['scheme']) || empty($parts['host']) || empty($parts['path']) ) {
            return $url;
        }
        $normalized = $parts['scheme'] . '://' . $parts['host'] . $parts['path'];
        return $normalized;
    }

    if ( strpos($url, '//') === 0 ) {
        $scheme = is_ssl() ? 'https:' : 'http:';
        $url = $scheme . $url;
        // Fjern querystring
        $parts = wp_parse_url($url);
        return $parts ? ($parts['scheme'].'://'.$parts['host'].($parts['path'] ?? '')) : $url;
    }

    // Relativ URL → site absolut
    $site = rtrim(site_url('/'), '/');
    if ( strpos($url, '/') === 0 ) {
        return $site . $url;
    }
    return $site . '/' . ltrim($url, '/');
}

/**
 * Er URL ekstern ift. sitet?
 */
function nns_is_external_url($url) {
    echo $url . "<br/>";
    $host      = wp_parse_url($url, PHP_URL_HOST);
    $site_host = wp_parse_url(home_url(), PHP_URL_HOST);
    return $host && $site_host && (strcasecmp($host, $site_host) !== 0);
}

/**
 * Prøv flere strategier for at finde et eksisterende attachment ud fra en billed-URL.
 * Ingen sideload — returnerer 0 hvis ikke fundet.
 */
function nns_find_existing_attachment_by_url($url) {
    
    // 1) Direkte WordPress helper
    $id = attachment_url_to_postid($url);
    if ( $id ) return (int) $id;

    // 2) Forsøg uden størrelsessuffiks ( -150x150, -1024x, -scaled )
    $path_no_query = (wp_parse_url($url, PHP_URL_PATH) ?: '');
    $path_clean    = nns_strip_wp_size_suffix($path_no_query);

    if ( $path_clean && $path_clean !== $path_no_query ) {
        $base = nns_url_base($url);
        if ( $base ) {
            $retry_url = rtrim($base, '/') . $path_clean;
            $id = attachment_url_to_postid($retry_url);
            if ( $id ) return (int) $id;
        }
    }

    // 3) Match via _wp_attached_file (uploads-relative sti)
    $uploads = wp_get_upload_dir();
    if ( ! empty($uploads['baseurl']) && ! empty($uploads['basedir']) ) {
        $baseurl = rtrim($uploads['baseurl'], '/');
        $basedir = rtrim($uploads['basedir'], DIRECTORY_SEPARATOR);

        // Er URL'en under uploads?
        if ( strpos($url, $baseurl) === 0 ) {
            $rel = ltrim( str_replace($baseurl, '', $url), '/' ); // fx 2024/10/billede.jpg eller 2024/10/billede-150x150.jpg
            $rel_clean = nns_strip_wp_size_suffix($rel);

            // Prøv eksakt match
            $id = nns_find_attachment_by_attached_file($rel);
            if ( $id ) return (int) $id;

            // Prøv uden størrelsessuffiks
            if ( $rel_clean !== $rel ) {
                $id = nns_find_attachment_by_attached_file($rel_clean);
                if ( $id ) return (int) $id;
            }
        }
    }

    // 4) Som sidste udvej: match på guid = URL (uden query)
    global $wpdb;
    $guid_like = esc_url_raw($url);
    $id = (int) $wpdb->get_var( $wpdb->prepare(
        "SELECT ID FROM $wpdb->posts WHERE post_type='attachment' AND guid=%s LIMIT 1",
        $guid_like
    ) );
    if ( $id ) return $id;

    return 0;
}

/**
 * Fjern WordPress billed-størrelsessuffixer fra en sti, fx:
 *  - foto-150x150.jpg → foto.jpg
 *  - foto-1024x683.jpg → foto.jpg
 *  - foto-scaled.jpg   → foto.jpg
 */
function nns_strip_wp_size_suffix($path) {
    // Fjern -WxH før filendelsen
    $path = preg_replace('/-\d+x\d+(?=\.\w+$)/', '', $path);
    // Fjern -scaled før filendelsen
    $path = preg_replace('/-scaled(?=\.\w+$)/', '', $path);
    return $path;
}

/**
 * Returner scheme+host del af URL (fx https://example.com).
 */
function nns_url_base($url) {
    $p = wp_parse_url($url);
    if ( ! $p || empty($p['scheme']) || empty($p['host']) ) return '';
    $port = isset($p['port']) ? ':' . $p['port'] : '';
    return $p['scheme'] . '://' . $p['host'] . $port;
}

/**
 * Find attachment via _wp_attached_file (uploads-relativ sti).
 */
function nns_find_attachment_by_attached_file($rel_path) {
    global $wpdb;
    // Nøjagtigt match
    $post_id = (int) $wpdb->get_var( $wpdb->prepare(
        "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_wp_attached_file' AND meta_value = %s LIMIT 1",
        $rel_path
    ) );
    if ( $post_id ) return $post_id;

    // Prøv LIKE som fallback (hvis nogle plugins ændrer stier)
    $like = '%' . $wpdb->esc_like($rel_path) . '%';
    $post_id = (int) $wpdb->get_var( $wpdb->prepare(
        "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s LIMIT 1",
        $like
    ) );
    return $post_id ?: 0;
}

/**
 * Admin-trigger til engangskørsel: /wp-admin/?nns_fix_thumbs_existing=1
 */
if ( ! is_user_logged_in() || ! current_user_can('manage_options') ) {
    return;
}

/* Step 1 - From list to categories */

if($_GET["step"] == "1"){
    mark_posts_with_category(get_option('navisen_left_list'), 4676);
    mark_posts_with_category(get_option('navisen_mid_list'), 4675);
    mark_posts_with_category(get_option('navisen_featured'), 4674);
}

/* Step 2 - Fix for first image to selected image */

if($_GET["step"] == "2"){
    nns_set_featured_from_first_img_existing_only('post', 100);
    msg('Kørsel fuldført.');
}

