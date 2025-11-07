<?php

/**
 * Konstanter
 */
const NAVISEN_AJAX_NONCE_ACTION = 'edit';
const NAVISEN_LIST_PARENT_SLUG  = 'lister';

/**
 * Helper: hent eller opret nonce + ajaxurl som JS-objekt
 */
add_filter('the_content', 'navisen_add_ajax_config');
function navisen_add_ajax_config($content) {
    // Kun på single / pages hvis du vil begrænse det:
    // if ( ! is_singular() ) return $content;

    $config = [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce(NAVISEN_AJAX_NONCE_ACTION),
    ];

    $script  = '<script>';
    $script .= 'var myAjax = ' . wp_json_encode($config) . ';';
    $script .= '</script>';

    return $content . $script;
}

/**
 * Helper: tjek nonce (fælles)
 */
function navisen_check_ajax_nonce() {
    check_ajax_referer(NAVISEN_AJAX_NONCE_ACTION, 'nonce');
}

/**
 * Helper: find kategori ID ud fra slug eller ID
 */
function navisen_resolve_category_id($value) {
    if (empty($value) && $value !== 0 && $value !== '0') {
        return 0;
    }

    // Hvis det allerede er et tal
    if (is_numeric($value)) {
        return (int) $value;
    }

    // Ellers antager vi slug
    $term = get_term_by('slug', $value, 'category');
    if (!$term || is_wp_error($term)) {
        return 0;
    }

    return (int) $term->term_id;
}

/**
 * Helper: hent "lister"-parent-term
 */
function navisen_get_list_parent_term() {
    static $term = null;

    if ($term === null) {
        $term = get_term_by('slug', NAVISEN_LIST_PARENT_SLUG, 'category');
    }

    return $term;
}

/**
 * Helper: hent alle underkategorier under "lister" (ids)
 */
function navisen_get_list_child_ids() {
    static $ids = null;

    if ($ids !== null) {
        return $ids;
    }

    $parent = navisen_get_list_parent_term();
    if (!$parent) {
        return [];
    }

    $terms = get_terms([
        'taxonomy'   => 'category',
        'child_of'   => $parent->term_id,
        'hide_empty' => false,
        'fields'     => 'ids',
    ]);

    if (is_wp_error($terms) || empty($terms)) {
        return [];
    }

    $ids = array_map('intval', $terms);
    return $ids;
}

/**
 * Helper: fjern alle kategorier under "lister" og tilføj targetCategory
 */
function navisen_clean_and_add_category($post_id, $target_category_id) {
    $current_cat_ids = wp_get_post_categories($post_id);
    $list_child_ids  = navisen_get_list_child_ids();

    // Fjern alle underkategorier under "lister"
    if (!empty($list_child_ids) && !empty($current_cat_ids)) {
        $current_cat_ids = array_diff($current_cat_ids, $list_child_ids);
    }

    // Tilføj targetCategory hvis sat
    if ($target_category_id > 0) {
        $current_cat_ids[] = $target_category_id;
    }

    $new_cat_ids = array_unique(array_map('intval', $current_cat_ids));

    wp_set_post_categories($post_id, $new_cat_ids);

    return $new_cat_ids;
}

/**
 * Helper: flyt et enkelt post til ny kategori og sæt menu_order øverst
 */
add_action('wp_ajax_move_to_new_category', 'my_move_post_to_new_category');
function my_move_post_to_new_category() {
    navisen_check_ajax_nonce();

    // 1. Hent og valider post ID
    $post_id = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;

    if (!$post_id || !get_post($post_id)) {
        wp_send_json_error(['message' => 'Ugyldigt post ID.']);
    }

    // 2. Tjek rettigheder
    if (!current_user_can('edit_post', $post_id)) {
        wp_send_json_error(['message' => 'Du har ikke rettigheder til at redigere dette indlæg.']);
    }

    // 3. Find target kategori
    $target_raw       = isset($_POST['targetCategory']) ? sanitize_text_field($_POST['targetCategory']) : '';
    $target_cat_id    = navisen_resolve_category_id($target_raw);

    // 4. Opdater kategorier (fjern alle under "lister", tilføj target)
    $new_cat_ids = navisen_clean_and_add_category($post_id, $target_cat_id);

    // 5. Sæt menu_order øverst i target-category
    if ($target_cat_id > 0) {
        global $wpdb;

        $lowest_order = $wpdb->get_var(
            $wpdb->prepare("
                SELECT MIN(p.menu_order)
                FROM {$wpdb->posts} p
                INNER JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
                INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                WHERE tt.taxonomy = 'category'
                AND tt.term_id = %d
                AND p.post_type = 'post'
                AND p.post_status = 'publish'
            ", $target_cat_id)
        );

        $new_order = ($lowest_order !== null) ? ($lowest_order - 100) : -100;

        wp_update_post([
            'ID'         => $post_id,
            'menu_order' => $new_order,
        ]);
    }

    wp_send_json_success([
        'message'     => 'Kategorier opdateret.',
        'post_id'     => $post_id,
        'new_cat_ids' => $new_cat_ids,
    ]);
}

/**
 * Sæt kategori-list og menu_order 100, 200, 300 ...
 */
add_action('wp_ajax_set_category_list', 'my_set_category_list_handler');
function my_set_category_list_handler() {
    navisen_check_ajax_nonce();

    $ids             = isset($_POST['list']) ? (array) $_POST['list'] : [];
    $target_category = isset($_POST['targetCategory']) ? sanitize_text_field($_POST['targetCategory']) : '';

    if (empty($ids) || empty($target_category)) {
        wp_send_json_error(['message' => 'Manglende data (list eller targetCategory).']);
    }

    $ids               = array_map('intval', $ids);
    $target_category_id = navisen_resolve_category_id($target_category);

    if ($target_category_id <= 0) {
        wp_send_json_error(['message' => 'Ugyldig kategori (targetCategory).']);
    }

    // Sørg for at "lister"-parent findes (for konsistens)
    if (!navisen_get_list_parent_term()) {
        wp_send_json_error(['message' => 'Kunne ikke finde "lister"-kategorien.']);
    }

    $order     = 100;
    $processed = 0;

    foreach ($ids as $post_id) {
        if (!$post_id || !get_post($post_id)) {
            continue;
        }

        if (!current_user_can('edit_post', $post_id)) {
            continue;
        }

        // Rens kategorier for "lister"-sub og tilføj target
        navisen_clean_and_add_category($post_id, $target_category_id);

        // Sæt menu_order: 100, 200, 300, ...
        wp_update_post([
            'ID'         => $post_id,
            'menu_order' => $order,
        ]);

        $order     += 100;
        $processed++;
    }

    wp_send_json_success([
        'message'       => 'Kategorier og rækkefølge opdateret.',
        'target_cat_id' => $target_category_id,
        'processed'     => $processed,
    ]);
}

/**
 * Returnér post -> [slugs under "lister"] map som JSON
 */
add_action('wp_ajax_get_post_category_list', 'get_postCategoryList');
function get_postCategoryList() {
    $parent = navisen_get_list_parent_term();
    if (!$parent) {
        wp_send_json_error(['message' => 'Ingen kategori med slug "lister" fundet.']);
    }

    $terms = get_terms([
        'taxonomy'   => 'category',
        'hide_empty' => false,
        'parent'     => $parent->term_id
    ]);

    if (is_wp_error($terms) || empty($terms)) {
        wp_send_json_error(['message' => 'Ingen underkategorier fundet under "lister".']);
    }

    $post_map = [];

    foreach ($terms as $term) {
        $posts = get_posts([
            'category'    => $term->term_id,
            'numberposts' => -1,
            'fields'      => 'ids',
            'post_status' => ['publish', 'future']
        ]);

        foreach ($posts as $post_id) {
            if (!isset($post_map[$post_id])) {
                $post_map[$post_id] = [];
            }
            $post_map[$post_id][] = $term->slug;
        }
    }
    
    $posts = get_posts([
        'numberposts' => -1,
        'fields'      => 'ids',
        'post_status' => ['future']
    ]);

    
    wp_send_json_success([
        'data'     => $post_map,
        'future_posts'     => $posts
    ]);
}
