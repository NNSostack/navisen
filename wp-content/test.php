<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');

print_r("Featured: " . get_option('navisen_featured'));
echo "<br/>";
print_r("Left: " . get_option('navisen_left_list'));
echo "<br/>";
print_r("Mid: " . get_option('navisen_mid_list'));

function mark_posts_with_category($post_ids_csv, $category_id) {
    $post_ids = array_map('intval', explode(',', $post_ids_csv));
    foreach ($post_ids as $post_id) {
        if (get_post($post_id)) {
            wp_set_post_categories($post_id, [$category_id], true);
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
    $path = wp_parse_url($image_url, PHP_URL_PATH);
    if ( ! $path ) return 0;

    $path_clean = nns_strip_wp_size_suffix($path);

    $docroot   = rtrim( $_SERVER['DOCUMENT_ROOT'], DIRECTORY_SEPARATOR );
    $local_abs = $docroot . $path_clean;

    if ( ! file_exists($local_abs) ) {
        $local_abs = $docroot . rawurldecode($path_clean);
    }
    if ( ! file_exists($local_abs) ) {
        $fallback_abs = $docroot . $path;
        if ( ! file_exists($fallback_abs) ) {
            $fallback_abs = $docroot . rawurldecode($path);
        }
        if ( file_exists($fallback_abs) ) {
            $local_abs = $fallback_abs;
        }
    }

    if ( ! file_exists($local_abs) || ! is_readable($local_abs) ) {
        return 0;
    }

    $uploads = wp_get_upload_dir();
    if ( empty($uploads['basedir']) || empty($uploads['baseurl']) ) {
        return 0;
    }

    $rel_from_root = ltrim( str_replace('\\', '/', $path_clean), '/' );
    $rel_from_root = trim($rel_from_root, "/ \t\n\r\0\x0B");

    $dest_abs  = trailingslashit($uploads['basedir']) . $rel_from_root;
    $dest_dir  = dirname($dest_abs);
    $dest_url  = trailingslashit($uploads['baseurl']) . $rel_from_root;

    if ( ! wp_mkdir_p($dest_dir) ) {
        return 0;
    }
    if ( ! file_exists($dest_abs) ) {
        if ( ! copy($local_abs, $dest_abs) ) {
            return 0;
        }
    }

    // Undgå at oprette duplikat-attachment hvis der allerede findes et for denne sti
    $rel_for_meta = ltrim( str_replace( trailingslashit($uploads['basedir']), '', $dest_abs ), '/' );
    $existing = nns_find_attachment_by_attached_file( $rel_for_meta );
    if ( $existing ) {
        return (int) $existing;
    }

    $filetype = wp_check_filetype( basename($dest_abs), null );
    if ( empty($filetype['type']) && function_exists('exif_imagetype') ) {
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
        if ( $it && isset($types[$it]) ) {
            $filetype['type'] = $types[$it];
        }
    }
    if ( empty($filetype['type']) ) {
        $filetype['type'] = 'image/jpeg';
    }

    $attach_title = sanitize_text_field( pathinfo($dest_abs, PATHINFO_FILENAME) );
    $attachment = [
        'post_mime_type' => $filetype['type'],
        'post_title'     => $attach_title,
        'post_content'   => '',
        'post_status'    => 'inherit',
        'guid'           => $dest_url,
    ];

    $attach_id = wp_insert_attachment($attachment, $dest_abs, $post_id);
    if ( is_wp_error($attach_id) || ! $attach_id ) {
        return 0;
    }

    update_attached_file($attach_id, $rel_for_meta);

    require_once ABSPATH . 'wp-admin/includes/image.php';
    $attach_data = wp_generate_attachment_metadata($attach_id, $dest_abs);
    if ( ! is_wp_error($attach_data) && ! empty($attach_data) ) {
        wp_update_attachment_metadata($attach_id, $attach_data);
    }

    return (int) $attach_id;
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
            // Spring over hvis vi ikke må overskrive og der allerede er thumbnail
            if (!$overwrite_existing && has_post_thumbnail($post_id)) {
                msg(sprintf('[nns] Post %d har allerede thumbnail – springer over.', $post_id));
                continue;
            }

            $content = get_post_field('post_content', $post_id);
            if (empty($content)) {
                msg(sprintf('[nns] Post %d: tomt indhold.', $post_id));
                continue;
            }

            // Find første IMG-tag (vi skal både bruge hele tagget og src)
            $img_tag_regex = '#<img\b[^>]*\bsrc\s*=\s*([\'"])(?P<src>[^\'"]+)\1[^>]*>#i';
            if (!preg_match($img_tag_regex, $content, $m, PREG_OFFSET_CAPTURE)) {
                // msg(sprintf('[nns] Post %d: intet billede i HTML.', $post_id));
                continue;
            }

            $full_tag      = $m[0][0];
            $full_tag_pos  = $m[0][1];
            $img_src       = $m['src'][0];

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
                    } else {
                        // Hvis vi ikke må overskrive – så rør ikke indholdet
                        continue;
                    }
                } else {
                    set_post_thumbnail($post_id, $attachment_id);
                }

                // Fjern kun den FØRSTE forekomst af netop dette img-tag fra content
                // (brug position fra regex for at undgå at fjerne senere identiske tags)
                $before = substr($content, 0, $full_tag_pos);
                $after  = substr($content, $full_tag_pos + strlen($full_tag));
                $new_content = $before . $after;

                // Opdater indlæggets indhold
                wp_update_post([
                    'ID'           => $post_id,
                    'post_content' => $new_content,
                ]);

                $total_updated++;
                msg(sprintf('[nns] Post %d: Thumbnail sat til attachment %d og første <img> fjernet.', $post_id, $attachment_id));
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


/* Step 1 - From list to categories */
if ( isset($_GET["step"]) && $_GET["step"] == "1" ) {
    mark_posts_with_category(get_option('navisen_left_list'), 4676);
    mark_posts_with_category(get_option('navisen_mid_list'), 4675);
    mark_posts_with_category(get_option('navisen_featured'), 4674);
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


/* Step 0 - Remap første billede til WP (opdater indhold + evt. featured) */
if ( isset($_GET["step"]) && $_GET["step"] == "0" ) {
    // Justér batch-størrelse og om vi også sætter featured her:
    nns_step0_remap_first_image_to_wordpress('post', 100, true);
    msg('Kørsel fuldført (step 0).');
}

