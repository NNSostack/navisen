<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');

print_r("Featured: " . get_option('navisen_featured'));
echo "<br/>";
print_r("Left: " . get_option('navisen_left_list'));
echo "<br/>";
print_r("Mid: " . get_option('navisen_mid_list'));

// Hæv eksekveringstid
set_time_limit(600);

// Hæv memory (virker kun hvis PHP ikke er låst af hosten)
ini_set('memory_limit', '512M');
ini_set('max_execution_time', '1200');

function mark_posts_with_category($post_ids_csv, $category_id) {
    $post_ids = array_map('intval', explode(',', $post_ids_csv));

    $order = 100;

    foreach ($post_ids as $post_id) {

        // Tjek at posten eksisterer
        if (get_post($post_id)) {

            // Tilføj kategori
            wp_set_post_categories($post_id, [$category_id], true);

            // Sæt menu_order
            wp_update_post([
                'ID'         => $post_id,
                'menu_order' => $order
            ]);

            $order += 100; // næste bliver +100
        }
    }
}

/**
 * STEP 0:
 * - Find første <img> i indholdet
 * - Map til aktuelt domæne (samme path)
 * - Hvis billedet findes i WP-medier → brug dets URL
 * - Ellers upload fra disk (samme mappe-struktur under uploads) → brug ny URL
 * - Opdater post_content (kun første <img>) til WordPress-URL
 * - (valgfrit) sæt som featured hvis ikke sat
 */
function nns_step0_remap_first_image_to_wordpress($post_type = 'post', $batch_size = 100, $also_set_featured = true) {
    $paged = 1;

    do {
        $q = new WP_Query([
            'post_type'      => $post_type,
            'post_status'    => 'publish',
            'posts_per_page' => $batch_size,
            'paged'          => $paged,
            'fields'         => 'ids',
        ]);

        if ( ! $q->have_posts() ) {
            msg("[nns:0] Ingen flere indlæg på side {$paged}.");
            break;
        }

        foreach ( $q->posts as $post_id ) {
            // … (resten af koden for hvert post)
            // fx kald din eksisterende blok her:
            nns_step0_process_single_post($post_id, $also_set_featured);
        }

        $paged++;
        wp_reset_postdata();

    } while ( $q->max_num_pages >= $paged );

    msg("[nns:0] Kørsel færdig. Behandlede {$paged} sider.");
}


function msg($msg){
    error_log($msg);
    echo $msg . "<br/>";
}

/** Find første <img> src i HTML. */
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

/** Erstat kun første <img>’s src i HTML. */
function nns_replace_first_img_src($html, $new_src) {
    // Match første <img ... src="...">
    return preg_replace(
        '/(<img\b[^>]*\ssrc=["\'])([^"\']+)(["\'][^>]*>)/i',
        '$1' . addcslashes($new_src, '\\$') . '$3',
        $html,
        1
    );
}

/** Normaliser billed-URL til absolut URL uden query/fragment. */
function nns_normalize_image_url($url) {
    if ( preg_match('~^https?://~i', $url) ) {
        $parts = wp_parse_url($url);
        if ( empty($parts['scheme']) || empty($parts['host']) || empty($parts['path']) ) {
            return $url;
        }
        return $parts['scheme'] . '://' . $parts['host'] . $parts['path'];
    }
    if ( strpos($url, '//') === 0 ) {
        $scheme = is_ssl() ? 'https:' : 'http:';
        $url = $scheme . $url;
        $parts = wp_parse_url($url);
        return $parts ? ($parts['scheme'].'://'.$parts['host'].($parts['path'] ?? '')) : $url;
    }
    $site = rtrim(site_url('/'), '/');
    if ( strpos($url, '/') === 0 ) {
        return $site . $url;
    }
    return $site . '/' . ltrim($url, '/');
}

/** Map en given URL til samme path på nuværende host (scheme/host fra home_url). */
function nns_remap_url_to_current_host($url) {
    $path = wp_parse_url($url, PHP_URL_PATH);
    if ( ! $path ) return nns_normalize_image_url($url);
    $home = home_url('/');
    $p = wp_parse_url($home);
    $scheme = $p['scheme'] ?? (is_ssl() ? 'https' : 'http');
    $host   = $p['host']   ?? $_SERVER['HTTP_HOST'];
    $port   = isset($p['port']) ? ':' . $p['port'] : '';
    return $scheme . '://' . $host . $port . $path;
}

/** Returner true hvis URL er ekstern ift. sitet (ikke brugt i step 0, men nyttig). */
function nns_is_external_url($url) {
    $host      = wp_parse_url($url, PHP_URL_HOST);
    $site_host = wp_parse_url(home_url(), PHP_URL_HOST);
    return $host && $site_host && (strcasecmp($host, $site_host) !== 0);
}

/** Forsøg forskellige strategier for at finde eksisterende attachment ud fra URL. */
function nns_find_existing_attachment_by_url($url) {
    $id = attachment_url_to_postid($url);
    if ( $id ) return (int) $id;

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

    $uploads = wp_get_upload_dir();
    if ( ! empty($uploads['baseurl']) && ! empty($uploads['basedir']) ) {
        $baseurl = rtrim($uploads['baseurl'], '/');
        if ( strpos($url, $baseurl) === 0 ) {
            $rel = ltrim( str_replace($baseurl, '', $url), '/' );
            $rel_clean = nns_strip_wp_size_suffix($rel);

            $id = nns_find_attachment_by_attached_file($rel);
            if ( $id ) return (int) $id;

            if ( $rel_clean !== $rel ) {
                $id = nns_find_attachment_by_attached_file($rel_clean);
                if ( $id ) return (int) $id;
            }
        }
    }

    global $wpdb;
    $guid_like = esc_url_raw($url);
    $id = (int) $wpdb->get_var( $wpdb->prepare(
        "SELECT ID FROM $wpdb->posts WHERE post_type='attachment' AND guid=%s LIMIT 1",
        $guid_like
    ) );
    if ( $id ) return $id;

    return 0;
}

/** Fjern WordPress billed-størrelsessuffixer fra en sti. */
function nns_strip_wp_size_suffix($path) {
    $path = preg_replace('/-\d+x\d+(?=\.\w+$)/', '', $path);
    $path = preg_replace('/-scaled(?=\.\w+$)/', '', $path);
    return $path;
}

/** Returner scheme+host del af URL (fx https://example.com). */
function nns_url_base($url) {
    $p = wp_parse_url($url);
    if ( ! $p || empty($p['scheme']) || empty($p['host']) ) return '';
    $port = isset($p['port']) ? ':' . $p['port'] : '';
    return $p['scheme'] . '://' . $p['host'] . $port;
}

/** Find attachment via _wp_attached_file (uploads-relativ sti). */
function nns_find_attachment_by_attached_file($rel_path) {
    global $wpdb;
    $post_id = (int) $wpdb->get_var( $wpdb->prepare(
        "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_wp_attached_file' AND meta_value = %s LIMIT 1",
        $rel_path
    ) );
    if ( $post_id ) return $post_id;

    $like = '%' . $wpdb->esc_like($rel_path) . '%';
    $post_id = (int) $wpdb->get_var( $wpdb->prepare(
        "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s LIMIT 1",
        $like
    ) );
    return $post_id ?: 0;
}

/**
 * Forsøg import:
 * - Brug path fra URL
 * - Find fysisk fil under DOCUMENT_ROOT
 * - Kopiér til uploads/<samme/sti> og opret attachment
 * Returnerer attachment_id eller 0.
 */
function nns_try_import_local_mirror_and_attach($image_url, $post_id) {

    msg(sprintf('[nns] Post %d: Starter import for URL: %s', $post_id, $image_url));

    $path = wp_parse_url($image_url, PHP_URL_PATH);
    if ( ! $path ) {
        msg(sprintf('[nns] Post %d: Ingen path fundet i URL – returnerer 0.', $post_id));
        return 0;
    }

    $path_clean = nns_strip_wp_size_suffix($path);

    $docroot   = rtrim($_SERVER['DOCUMENT_ROOT'], DIRECTORY_SEPARATOR);
    $local_abs = $docroot . $path_clean;

    msg(sprintf('[nns] Post %d: Forsøger lokal sti: %s', $post_id, $local_abs));

    if ( ! file_exists($local_abs) ) {
        $local_abs = $docroot . rawurldecode($path_clean);
        msg(sprintf('[nns] Post %d: Forsøger rawurldecode sti: %s', $post_id, $local_abs));
    }

    if ( ! file_exists($local_abs) ) {
        $fallback_abs = $docroot . $path;
        msg(sprintf('[nns] Post %d: Forsøger fallback sti: %s', $post_id, $fallback_abs));

        if ( ! file_exists($fallback_abs) ) {
            $fallback_abs = $docroot . rawurldecode($path);
            msg(sprintf('[nns] Post %d: Forsøger fallback rawurldecode sti: %s', $post_id, $fallback_abs));
        }

        if ( file_exists($fallback_abs) ) {
            $local_abs = $fallback_abs;
            msg(sprintf('[nns] Post %d: Fandt fallback fil: %s', $post_id, $local_abs));
        }
    }

    // === NYT: YouTube-import hvis fil ikke kan findes lokalt ===
    if ( ! file_exists($local_abs) || ! is_readable($local_abs) ) {

        msg(sprintf('[nns] Post %d: Ingen lokal fil fundet. Tjekker om det er YouTube…', $post_id));

        $host = wp_parse_url($image_url, PHP_URL_HOST);

        if (
            $host &&
            (
                stripos($host, 'youtube.com')     !== false ||
                stripos($host, 'youtu.be')        !== false ||
                stripos($host, 'img.youtube.com') !== false
            )
        ) {
            msg(sprintf('[nns] Post %d: URL %s peger på YouTube → forsøger hentning af thumbnail.', $post_id, $image_url));
            $yt = nns_import_youtube_thumb_and_attach($image_url, $post_id);

            if ($yt) {
                msg(sprintf('[nns] Post %d: YouTube-thumbnail hentet og attached som %d.', $post_id, $yt));
            } else {
                msg(sprintf('[nns] Post %d: Kunne ikke hente YouTube-thumbnail.', $post_id));
            }

            return $yt;
        }

        // Ikke YouTube → stop
        msg(sprintf('[nns] Post %d: Ikke YouTube og ingen lokal fil – returnerer 0.', $post_id));
        return 0;
    }

    msg(sprintf('[nns] Post %d: Lokal fil fundet: %s', $post_id, $local_abs));

    $uploads = wp_get_upload_dir();
    if ( empty($uploads['basedir']) || empty($uploads['baseurl']) ) {
        msg(sprintf('[nns] Post %d: Upload dir ikke tilgængelig – returnerer 0.', $post_id));
        return 0;
    }

    $rel_from_root = ltrim(str_replace('\\', '/', $path_clean), '/');
    $rel_from_root = trim($rel_from_root, "/ \t\n\r\0\x0B");

    $dest_abs = trailingslashit($uploads['basedir']) . $rel_from_root;
    $dest_dir = dirname($dest_abs);
    $dest_url = trailingslashit($uploads['baseurl']) . $rel_from_root;

    msg(sprintf('[nns] Post %d: Destination: %s', $post_id, $dest_abs));

    if ( ! wp_mkdir_p($dest_dir) ) {
        msg(sprintf('[nns] Post %d: Kunne ikke oprette mappe: %s', $post_id, $dest_dir));
        return 0;
    }

    if ( ! file_exists($dest_abs) ) {
        if ( ! copy($local_abs, $dest_abs) ) {
            msg(sprintf('[nns] Post %d: Fejl: kunne ikke kopiere %s → %s', $post_id, $local_abs, $dest_abs));
            return 0;
        } else {
            msg(sprintf('[nns] Post %d: Kopierede fil til uploads.', $post_id));
        }
    } else {
        msg(sprintf('[nns] Post %d: Fil findes allerede i uploads.', $post_id));
    }

    // Tjek om der allerede findes attachment
    $rel_for_meta = ltrim(str_replace(trailingslashit($uploads['basedir']), '', $dest_abs), '/');
    $existing = nns_find_attachment_by_attached_file($rel_for_meta);

    if ($existing) {
        msg(sprintf('[nns] Post %d: Attachment eksisterer allerede (%d).', $post_id, $existing));
        return (int)$existing;
    }

    $filetype = wp_check_filetype(basename($dest_abs), null);
    if (empty($filetype['type']) && function_exists('exif_imagetype')) {
        msg(sprintf('[nns] Post %d: Filetype ukendt – forsøger exif.', $post_id));

        $types = [
            IMAGETYPE_GIF  => 'image/gif',
            IMAGETYPE_JPEG => 'image/jpeg',
            IMAGETYPE_PNG  => 'image/png',
            IMAGETYPE_WEBP => 'image/webp',
            IMAGETYPE_BMP  => 'image/bmp',
            IMAGETYPE_TIFF_II => 'image/tiff',
            IMAGETYPE_TIFF_MM => 'image/tiff',
            IMAGETYPE_AVIF => 'image/avif',
            IMAGETYPE_HEIC => 'image/heic',
        ];
        $it = @exif_imagetype($dest_abs);
        if ($it && isset($types[$it])) {
            $filetype['type'] = $types[$it];
            msg(sprintf('[nns] Post %d: Filetype bestemt til %s', $post_id, $filetype['type']));
        }
    }

    if (empty($filetype['type'])) {
        msg(sprintf('[nns] Post %d: Filetype stadig ukendt – fallback image/jpeg.', $post_id));
        $filetype['type'] = 'image/jpeg';
    }

    $attach_title = sanitize_text_field(pathinfo($dest_abs, PATHINFO_FILENAME));

    msg(sprintf('[nns] Post %d: Opretter attachment: %s', $post_id, $attach_title));

    $attachment = [
        'post_mime_type' => $filetype['type'],
        'post_title'     => $attach_title,
        'post_content'   => '',
        'post_status'    => 'inherit',
        'guid'           => $dest_url,
    ];

    $attach_id = wp_insert_attachment($attachment, $dest_abs, $post_id);
    if (is_wp_error($attach_id) || !$attach_id) {
        msg(sprintf('[nns] Post %d: Fejl ved wp_insert_attachment.', $post_id));
        return 0;
    }

    update_attached_file($attach_id, $rel_for_meta);

    require_once ABSPATH . 'wp-admin/includes/image.php';
    $attach_data = wp_generate_attachment_metadata($attach_id, $dest_abs);

    if ( ! is_wp_error($attach_data) && ! empty($attach_data) ) {
        wp_update_attachment_metadata($attach_id, $attach_data);
        msg(sprintf('[nns] Post %d: Metadata genereret og opdateret for attachment %d.', $post_id, $attach_id));
    } else {
        msg(sprintf('[nns] Post %d: Kunne ikke generere metadata for %d.', $post_id, $attach_id));
    }

    msg(sprintf('[nns] Post %d: Import færdig – attachment ID %d.', $post_id, $attach_id));

    return (int)$attach_id;
}



/**
 * STEP 2 (eksisterende): gennemgå indlæg uden thumbnail og sæt udvalgt billede fra første img,
 * kun hvis billedet i forvejen findes/kan importeres.
 */
/**
 * Gennemløber ALLE posts, finder første <img> i indholdet,
 * sætter den som udvalgt billede og fjerner netop denne <img> fra HTML'en.
 *
 * Kræver at hjælpefunktionerne eksisterer:
 * - nns_extract_first_img_src($html)            // returnerer URL for første img
 * - nns_normalize_image_url($url)
 * - nns_remap_url_to_current_host($url)
 * - nns_find_existing_attachment_by_url($url)   // returnerer attachment_id eller 0/false
 * - nns_try_import_local_mirror_and_attach($url, $post_id) // returnerer attachment_id eller 0/false
 * - msg($text)                                  // (valgfri) logger/echo
 *
 * @param string $post_type
 * @param int    $batch_size         hvor mange pr. side (pagination)
 * @param bool   $overwrite_existing true = overskriv eksisterende thumbnails
 */
function nns_set_featured_from_first_img_all($post_type = 'post', $batch_size = 100, $overwrite_existing = false) {
    $page = 1;
    $total_updated = 0;

    do {
        $q = new WP_Query([
            'post_type'      => $post_type,
            'post_status'    => 'publish',
            'fields'         => 'ids',
            'posts_per_page' => $batch_size,
            'paged'          => $page,
            'no_found_rows'  => true,
        ]);

        if ( ! $q->have_posts() ) {
            if ($page === 1) {
                msg('[nns] Ingen indlæg fundet.');
            }
            break;
        }

        foreach ( $q->posts as $post_id ) {
            if(isset($_GET["id"]) && $_GET["id"] != $post_id){
                continue;
            }

            /**
             * 0) Håndtering af iframe → udvalgt video
             * -------------------------------------------------
             * Hvis iframe-feltet har indhold:
             *  - Ekstrahér video-URL (fra <iframe src="..."> eller ren tekst)
             *  - Gem i temaets "featured video"-felt
             *  - Tøm iframe-feltet
             *  - Slet evt. udvalgt billede
             *  - Gå videre til næste post (ingen auto-thumbnail)
             */

            // Tilpas disse to nøgler til din installation:
            $iframe_field_key      = 'iframe';              // eksisterende custom felt med iframe/embed
            $theme_video_field_key = 'td_post_video';  // TODO: skift til temaets "udvalgt video"-felt

            $iframe_value = get_post_meta($post_id, $iframe_field_key, true);

            if ( ! empty($iframe_value) ) {
                $video_url = null;

                // Prøv først at finde src="" i et iframe-tag
                if (preg_match('#<iframe[^>]+src=["\']([^"\']+)["\'][^>]*>#i', $iframe_value, $m_iframe)) {
                    $video_url = $m_iframe[1];
                } else {
                    // Hvis der ikke er et iframe-tag, antag at feltet indeholder en URL eller tekst
                    $video_url = trim( wp_strip_all_tags( $iframe_value ) );
                }

                if ( ! empty($video_url) ) {

                    // Sæt post format til VIDEO
                    set_post_format($post_id, 'video');

                    // Gem video i Newspaper temaets video-meta
                    update_post_meta($post_id, $theme_video_field_key, [
                        'td_video' => esc_url_raw($video_url)
                    ]);

                    // Behold udvalgt billede
                    // Slet udvalgt billede, hvis der er et
                    /*if (has_post_thumbnail($post_id)) {
                        delete_post_thumbnail($post_id);
                        msg(sprintf('[nns] Post %d: Slettede udvalgt billede pga. iframe-video.', $post_id));
                    }*/

                    //  Opret thumbnail til video
                    if (!has_post_thumbnail($post_id)) {
                        $video_id = nns_extract_youtube_id($video_url);

                        if ($video_id) {
                            // Importer den som attachment
                            $attachment_id = nns_try_import_local_mirror_and_attach($video_url, $post_id);
                            msg("[nns] Post $post_id: Henter thumb: ($video_url), ($attachment_id)");

                            if ($attachment_id) {
                                set_post_thumbnail($post_id, $attachment_id);
                                msg("[nns] Post $post_id: Udvalgt billede sat ud fra videoen ($thumb_url).");
                            }
                        }
                    }

                    msg(sprintf(
                        '[nns] Post %d: Flyttede iframe-video til meta "%s" (%s).',
                        $post_id,
                        $theme_video_field_key,
                        $video_url
                    ));
                } else {
                    msg(sprintf(
                        '[nns] Post %d: iframe-felt havde indhold, men kunne ikke finde en video-URL.',
                        $post_id
                    ));
                }

                // Tøm iframe-feltet (du kan bytte til update_post_meta(..., "") hvis du hellere vil gemme empty value)
                delete_post_meta($post_id, $iframe_field_key);

                // Spring resten af billed-logikken over for denne post
                continue;
            }

            //  Hvis der allerede er en thumbnail må vi godt gå videre for at slette billede
            // Spring over hvis vi ikke må overskrive og der allerede er thumbnail
            /*if (!$overwrite_existing && has_post_thumbnail($post_id)) {
                msg(sprintf('[nns] Post %d har allerede thumbnail – springer over.', $post_id));
                continue;
            }*/

            $content = get_post_field('post_content', $post_id);
            if (empty($content)) {
                msg(sprintf('[nns] Post %d: tomt indhold.', $post_id));
                continue;
            }

            $img_src      = null;
            $remove_start = null;
            $remove_len   = null;
            $caption_text = null;

            // === 1) Forsøg først at finde en [caption]-shortcode med et IMG ===
            $caption_regex = '#\[caption\b[^\]]*\](?P<inner>.*?)\[/caption\]#is';
            if (preg_match($caption_regex, $content, $cap, PREG_OFFSET_CAPTURE)) {
                $caption_full  = $cap[0][0];
                $caption_pos   = $cap[0][1];
                $caption_inner = $cap['inner'][0];

                // Find IMG inde i caption-indholdet
                $img_tag_regex = '#<img\b[^>]*\bsrc\s*=\s*([\'"])(?P<src>[^\'"]+)\1[^>]*>#i';
                if (preg_match($img_tag_regex, $caption_inner, $m_img)) {
                    $img_src  = $m_img['src'];
                    $img_tag  = $m_img[0];

                    // Udtræk billedteksten = caption-indhold minus IMG-tagget
                    $caption_text_raw = trim(str_replace($img_tag, '', $caption_inner));
                    // Fjern HTML-tags og normaliser mellemrum
                    $caption_plain = wp_strip_all_tags($caption_text_raw);
                    $caption_plain = preg_replace('/\s+/', ' ', $caption_plain);
                    $caption_text  = trim($caption_plain);

                    // Vi fjerner hele caption-blokken
                    $remove_start = $caption_pos;
                    $remove_len   = strlen($caption_full);
                }
            }

            // === 2) Hvis ingen caption/IMG fundet, falder vi tilbage til første IMG i content ===
            if (!$img_src) {
                $img_tag_regex = '#<img\b[^>]*\bsrc\s*=\s*([\'"])(?P<src>[^\'"]+)\1[^>]*>#i';
                if (!preg_match($img_tag_regex, $content, $m, PREG_OFFSET_CAPTURE)) {
                    // msg(sprintf('[nns] Post %d: intet billede i HTML.', $post_id));
                    continue;
                }

                $full_tag      = $m[0][0];
                $full_tag_pos  = $m[0][1];
                $img_src       = $m['src'][0];

                // I fallback-tilfældet fjerner vi kun selve IMG-tagget
                $remove_start = $full_tag_pos;
                $remove_len   = strlen($full_tag);
            }

            // Hvis vi stadig ikke har et billede, så videre
            if (!$img_src) {
                continue;
            }

            // Kør din eksisterende URL-normalisering / remapping
            $normalized = nns_normalize_image_url($img_src);
            $mapped     = nns_remap_url_to_current_host($normalized);

            // Forsøg at finde eksisterende attachment; ellers importer/attach
            $attachment_id = nns_find_existing_attachment_by_url($mapped);
            if (!$attachment_id) {
                $attachment_id = nns_try_import_local_mirror_and_attach($mapped, $post_id);
            }

            if ($attachment_id) {
                // Sæt thumbnail (overskriv hvis det er slået til)
                if (has_post_thumbnail($post_id)) {
                    if ($overwrite_existing) {
                        set_post_thumbnail($post_id, $attachment_id);
                        // Hvis vi har en billedtekst fra caption, gem den i custom feltet 'featured_caption'
                        if (!empty($caption_text)) {
                            update_post_meta($post_id, 'featured_caption', $caption_text);
                        }
                    }
                    else{
                        $caption_text = '';
                    }
                } else {
                    set_post_thumbnail($post_id, $attachment_id);
                    // Hvis vi har en billedtekst fra caption, gem den i custom feltet 'featured_caption'
                    if (!empty($caption_text)) {
                        update_post_meta($post_id, 'featured_caption', $caption_text);
                    }
                }
                
                // Fjern den del af content, vi har markeret (caption-blok eller kun IMG)
                if ($remove_start !== null && $remove_len !== null) {
                    $before      = substr($content, 0, $remove_start);
                    $after       = substr($content, $remove_start + $remove_len);
                    $new_content = $before . $after;

                    // Opdater indlæggets indhold
                    wp_update_post([
                        'ID'           => $post_id,
                        'post_content' => $new_content,
                    ]);
                }

                $total_updated++;
                msg(sprintf(
                    '[nns] Post %d: Thumbnail sat til attachment %d, billedet fjernet%s.',
                    $post_id,
                    $attachment_id,
                    $caption_text ? ' og featured_caption opdateret' : ''
                ));
            } else {
                msg(sprintf('[nns] Post %d: Kunne ikke mappe/importere billede (%s).', $post_id, esc_url($mapped)));
            }
        }

        wp_reset_postdata();
        $page++;
    } while (true);

    msg(sprintf('[nns] Færdig. Opdaterede %d posts.', $total_updated));
}



/** Admin-utility */
if ( ! is_user_logged_in() || ! current_user_can('manage_options') ) {
    return;
}

function nns_step0_process_single_post($post_id, $also_set_featured) {
    $content = get_post_field('post_content', $post_id);
    if ( empty($content) ) return;

    $first_src = nns_extract_first_img_src($content);
    if ( ! $first_src ) return;

    $normalized = nns_normalize_image_url($first_src);
    $mapped     = nns_remap_url_to_current_host($normalized);

    $attach_id = nns_find_existing_attachment_by_url($mapped);
    if ( ! $attach_id ) {
        $attach_id = nns_try_import_local_mirror_and_attach($mapped, $post_id);
    }

    if ( $attach_id ) {
        $new_src = wp_get_attachment_url($attach_id);
        if ( $new_src ) {
            $new_content = nns_replace_first_img_src($content, $new_src);
            if ( $new_content !== $content ) {
                wp_update_post(['ID' => $post_id, 'post_content' => $new_content]);
            }
            if ( $also_set_featured && ! has_post_thumbnail($post_id) ) {
                set_post_thumbnail($post_id, $attach_id);
            }
        }
    } else {
        // fallback mapping
        if ( $mapped && $mapped !== $first_src ) {
            $new_content = nns_replace_first_img_src($content, $mapped);
            if ( $new_content !== $content ) {
                wp_update_post(['ID' => $post_id, 'post_content' => $new_content]);
            }
        }
    }
}

/**
 * 1) Skift permalink-struktur til /%postname%/ og flush rewrite rules
 */
function nns_set_simple_postname_permalinks() {
    $target = '/%postname%/';
    if (get_option('permalink_structure') !== $target) {
        update_option('permalink_structure', $target);
    }
    flush_rewrite_rules(false);
}

/**
 * Hjælp: Lav absolut URL for mulig relativ intern URL
 */
function nns_abs_internal_url_simple($url) {
    $home = home_url('/');
    $url  = trim($url);

    if (strpos($url, '//') === 0) {
        $scheme = parse_url($home, PHP_URL_SCHEME) ?: 'https';
        return $scheme . ':' . $url;
    }
    $parsed = wp_parse_url($url);
    if (!empty($parsed['scheme'])) return $url;

    if (strpos($url, '/') === 0) {
        return rtrim($home, '/') . $url;
    }
    return $home . ltrim($url, '/');
}

/**
 * Hjælp: Fjern præcis ét '/blog/' segment lige efter domænet eller starten af path.
 * Bevarer query og fragment. Returnerer samme “form” (relativ/absolut) som input.
 */
function nns_strip_leading_blog_segment($href) {
    $home    = rtrim(home_url('/'), '/');
    $abs     = nns_abs_internal_url_simple($href);
    $parts   = wp_parse_url($abs);

    // Kun interne links
    $home_host = parse_url($home, PHP_URL_HOST);
    if (empty($parts['host']) || strcasecmp($parts['host'], $home_host) !== 0) {
        return $href;
    }

    $path = isset($parts['path']) ? $parts['path'] : '/';
    if (strpos($path, '/blog/') === 0) {
        $path = substr($path, 5); // fjern "/blog"
        if ($path === '' || $path[0] !== '/') $path = '/' . ltrim($path, '/');
    } else {
        // Hvis path hedder fx /blog (uden trailing slash), håndter også det
        if ($path === '/blog') $path = '/';
    }

    // Sæt URL sammen igen
    $rebuilt = (isset($parts['scheme']) ? $parts['scheme'] : 'https') . '://' . $parts['host']
             . (isset($parts['port']) ? ':' . $parts['port'] : '')
             . $path
             . (isset($parts['query']) ? '?' . $parts['query'] : '')
             . (isset($parts['fragment']) ? '#' . $parts['fragment'] : '');

    // Returnér i samme “form” som input (relativ hvis input var relativ)
    $input = trim($href);
    $input_was_relative = (strpos($input, 'http://') !== 0 && strpos($input, 'https://') !== 0 && strpos($input, '//') !== 0);
    if ($input_was_relative) {
        if (strpos($rebuilt, $home) === 0) {
            $rel = substr($rebuilt, strlen($home));
            return $rel === '' ? '/' : $rel;
        }
    }
    return $rebuilt;
}

/**
 * 2) Opdater links i HTML-indhold (post_content) – pagineret.
 * - Retter KUN <a href="...">, hvor stien starter med /blog/ (eller er /blog)
 * - Simpelt og hurtigt – ingen ekstra opslag.
 *
 * @param array|string $post_types  Fx ['post','page'] eller 'any'
 * @param int          $batch_size
 * @param bool         $dry_run     true = test (gemmer ikke), false = gem
 */
function nns_fix_html_links_remove_blog_segment($post_types = ['post','page'], $batch_size = 200, $dry_run = true) {
    $page = 1; $posts_changed = 0; $links_changed = 0;

    do {
        $q = new WP_Query([
            'post_type'      => $post_types,
            'post_status'    => 'any',
            'fields'         => 'ids',
            'posts_per_page' => $batch_size,
            'paged'          => $page,
            'no_found_rows'  => true,
        ]);
        if (!$q->have_posts()) break;

        foreach ($q->posts as $post_id) {
            $content = get_post_field('post_content', $post_id);
            if (!$content || stripos($content, '<a ') === false) continue;

            $changed_here = 0;
            $new_content = preg_replace_callback(
                '#(<a\b[^>]*\bhref\s*=\s*)([\'"])(?P<href>[^\'"]+)\2#i',
                function ($m) use (&$changed_here) {
                    $prefix = $m[1];
                    $q      = $m[2];
                    $href   = $m['href'];

                    // Kun hvis der reelt er et /blog i starten af path (relativ eller absolut)
                    $abs = nns_abs_internal_url_simple($href);
                    $p   = wp_parse_url($abs);
                    $path = isset($p['path']) ? $p['path'] : '/';
                    if ($path !== '/blog' && strpos($path, '/blog/') !== 0) {
                        return $m[0];
                    }

                    $new = nns_strip_leading_blog_segment($href);
                    if ($new !== $href) {
                        $changed_here++;
                        return $prefix . $q . $new . $q;
                    }
                    return $m[0];
                },
                $content
            );

            if ($changed_here > 0 && $new_content !== null && $new_content !== $content) {
                if (!$dry_run) {
                    wp_update_post([
                        'ID'           => $post_id,
                        'post_content' => $new_content,
                    ]);
                }
                $posts_changed++;
                $links_changed += $changed_here;
                error_log(sprintf('[nns] Post %d: %d link(s) opdateret.', $post_id, $changed_here));
            }
        }

        wp_reset_postdata();
        $page++;
    } while (true);

    error_log(sprintf('[nns] HTML-link fix %s — %d posts, %d links.',
        $dry_run ? '(DRY RUN)' : '(SAVED)',
        $posts_changed,
        $links_changed
    ));
}

/**
 * Lille "runner" du kan kalde fra WP-CLI eller en midlertidig admin action.
 */
function nns_run_simple_permalink_and_html_link_fix($post_types = ['post','page'], $batch_size = 200, $dry_run_html = true) {
    // 1) Skift permalinks
    nns_set_simple_postname_permalinks();

    // 2) Ret HTML-links i indhold
    nns_fix_html_links_remove_blog_segment($post_types, $batch_size, $dry_run_html);

    if ($dry_run_html) {
        error_log('[nns] Done. HTML-links kørte i DRY RUN. Sæt $dry_run_html=false for at gemme.');
    } else {
        error_log('[nns] Done. Permalinks sat og HTML-links gemt.');
    }
}

/**
 * Gennemløb alle brugere og sæt display_name = "Fornavn Efternavn"
 */
function nns_step_update_all_display_names($batch_size = 200) {
    $page = 1;
    $updated = 0;

    do {
        $args = [
            'number' => $batch_size,
            'paged'  => $page,
            'fields' => ['ID', 'display_name'],
        ];

        $users = get_users($args);
        if (empty($users)) break;

        foreach ($users as $user) {
            $user_id = $user->ID;
            $first   = get_user_meta($user_id, 'first_name', true);
            $last    = get_user_meta($user_id, 'last_name', true);

            // Spring over hvis ikke noget navn
            if (empty($first) && empty($last)) continue;

            $new_display = trim($first . ' ' . $last);

            // Spring over hvis allerede sat korrekt
            if ($user->display_name === $new_display) continue;

            // Opdater brugeren
            wp_update_user([
                'ID'           => $user_id,
                'display_name' => $new_display,
            ]);

            $updated++;
        }

        $page++;
    } while (count($users) === $batch_size);

    error_log("[nns] STEP: Display_name sat for {$updated} brugere.");
}

function nns_extract_youtube_id($url) {
    if (preg_match('#(?:v=|youtu\.be/)([A-Za-z0-9_-]+)#', $url, $m)) {
        return $m[1];
    }
    return null;
}

function nns_import_youtube_thumb_and_attach($youtube_url, $post_id) {

    msg(sprintf('[nns] Post %d: YouTube-import startet for URL: %s', $post_id, $youtube_url));

    $video_id = nns_extract_youtube_id($youtube_url);
    if ( ! $video_id ) {
        msg(sprintf('[nns] Post %d: Kunne ikke finde YouTube video-ID.', $post_id));
        return 0;
    }

    msg(sprintf('[nns] Post %d: Fundet YouTube video-ID: %s', $post_id, $video_id));

    $uploads = wp_get_upload_dir();
    if ( empty($uploads['basedir']) || empty($uploads['baseurl']) ) {
        msg(sprintf('[nns] Post %d: Upload folders ikke tilgængelige.', $post_id));
        return 0;
    }

    // Kandidater i prioriteret rækkefølge
    $candidates = [
        "https://img.youtube.com/vi/{$video_id}/maxresdefault.jpg",
        "https://img.youtube.com/vi/{$video_id}/hqdefault.jpg",
        "https://img.youtube.com/vi/{$video_id}/mqdefault.jpg",
    ];

    if ( ! function_exists('download_url') ) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
    }

    $tmp_file   = '';
    $thumb_used = '';

    msg(sprintf('[nns] Post %d: Forsøger at hente thumbnail fra YouTube…', $post_id));

    foreach ($candidates as $thumb_url) {

        msg(sprintf('[nns] Post %d: Forsøger download af: %s', $post_id, $thumb_url));

        $tmp = download_url($thumb_url);

        if (is_wp_error($tmp)) {
            msg(sprintf('[nns] Post %d: Fejl ved download: %s', $post_id, $tmp->get_error_message()));
            continue;
        }

        msg(sprintf('[nns] Post %d: Download OK → %s', $post_id, $thumb_url));

        $tmp_file   = $tmp;
        $thumb_used = $thumb_url;
        break;
    }

    if ( ! $tmp_file ) {
        msg(sprintf('[nns] Post %d: Ingen thumbnails kunne downloades.', $post_id));
        return 0;
    }

    // Ind i uploads/youtube-thumbs/
    $subdir       = 'youtube-thumbs';
    $filename_src = basename(wp_parse_url($thumb_used, PHP_URL_PATH));
    $filename     = 'yt-' . $video_id . '-' . $filename_src;

    $dest_dir = trailingslashit($uploads['basedir']) . $subdir;

    if ( ! wp_mkdir_p($dest_dir) ) {
        msg(sprintf('[nns] Post %d: Kunne ikke oprette mappe: %s', $post_id, $dest_dir));
        @unlink($tmp_file);
        return 0;
    }

    $dest_abs     = trailingslashit($dest_dir) . $filename;
    $rel_for_meta = trailingslashit($subdir) . $filename;
    $dest_url     = trailingslashit($uploads['baseurl']) . $rel_for_meta;

    msg(sprintf('[nns] Post %d: Thumbnail gemmes som: %s', $post_id, $dest_abs));

    // Tjek om der allerede ligger en attachment med samme fil
    if (function_exists('nns_find_attachment_by_attached_file')) {
        $existing = nns_find_attachment_by_attached_file($rel_for_meta);
        if ($existing) {
            msg(sprintf('[nns] Post %d: Attachment findes allerede (%d).', $post_id, $existing));
            @unlink($tmp_file);
            return (int) $existing;
        }
    }

    // Flyt temp-filen
    if ( ! @rename($tmp_file, $dest_abs) ) {
        msg(sprintf('[nns] Post %d: Fejl: kunne ikke flytte temp-file til %s', $post_id, $dest_abs));
        @unlink($tmp_file);
        return 0;
    }

    msg(sprintf('[nns] Post %d: Thumbnail flyttet til uploads.', $post_id));

    // MIME-type
    $filetype = wp_check_filetype($filename, null);
    if ( empty($filetype['type']) ) {
        $filetype['type'] = 'image/jpeg';
        msg(sprintf('[nns] Post %d: Ukendt MIME – fallback til image/jpeg.', $post_id));
    }

    // Attachment data
    $attachment = [
        'post_mime_type' => $filetype['type'],
        'post_title'     => sanitize_text_field('YouTube thumb ' . $video_id),
        'post_content'   => '',
        'post_status'    => 'inherit',
        'guid'           => $dest_url,
    ];

    msg(sprintf('[nns] Post %d: Indsætter attachment…', $post_id));

    // Opret attachment
    $attach_id = wp_insert_attachment($attachment, $dest_abs, $post_id);
    if ( is_wp_error($attach_id) || ! $attach_id ) {
        msg(sprintf('[nns] Post %d: wp_insert_attachment fejlede.', $post_id));
        return 0;
    }

    msg(sprintf('[nns] Post %d: Attachment oprettet med ID %d', $post_id, $attach_id));

    update_attached_file($attach_id, $rel_for_meta);

    require_once ABSPATH . 'wp-admin/includes/image.php';
    $attach_data = wp_generate_attachment_metadata($attach_id, $dest_abs);

    if ( ! is_wp_error($attach_data) && ! empty($attach_data) ) {
        wp_update_attachment_metadata($attach_id, $attach_data);
        msg(sprintf('[nns] Post %d: Metadata genereret & opdateret for %d', $post_id, $attach_id));
    } else {
        msg(sprintf('[nns] Post %d: Kunne ikke generere metadata for %d', $post_id, $attach_id));
    }

    msg(sprintf('[nns] Post %d: YouTube thumbnail import afsluttet. Attachment ID: %d', $post_id, $attach_id));

    return (int) $attach_id;
}



/* Step 1 - From list to categories */
if ( isset($_GET["step"]) && $_GET["step"] == "1" ) {
    mark_posts_with_category(get_option('navisen_left_list'), 4659);
    mark_posts_with_category(get_option('navisen_mid_list'), 4660);
    mark_posts_with_category(get_option('navisen_featured'), 4661);
}

/* Step 2 - Fix for first image to selected image */
if ( isset($_GET["step"]) && $_GET["step"] == "2" ) {
    nns_set_featured_from_first_img_all();
    msg('Kørsel fuldført (step 2).');
}

/* Step 3 - Change from /blog/ to no /blog/ */
if ( isset($_GET["step"]) && $_GET["step"] == "3" ) {
    // Test først (ingen ændringer gemmes for HTML-delen):
    //nns_run_simple_permalink_and_html_link_fix(['post','page'], 100, true);

    // Gem ændringer:
    nns_run_simple_permalink_and_html_link_fix(['post','page'], 200, false);
    msg('Kørsel fuldført (step 3).');
}

/* Step 4 - Set displayname of all users */
if ( isset($_GET["step"]) && $_GET["step"] == "4" ) {
    nns_step_update_all_display_names();
    msg('Kørsel fuldført (step 4).');
}


/* Step 0 - Remap første billede til WP (opdater indhold + evt. featured) */
if ( isset($_GET["step"]) && $_GET["step"] == "0" ) {
    // Justér batch-størrelse og om vi også sætter featured her:
    nns_step0_remap_first_image_to_wordpress('post', 100, true);
    msg('Kørsel fuldført (step 0).');
}


/*

Changes to site

1. Brugers "Vis navn" offentligt som skal ændres til navn
2. 

*/