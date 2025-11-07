<?php

/**
 * Tilføj ajaxurl og nounce
 */
add_filter('the_content', 'addAjaxUrl');
function addAjaxUrl($content) {

    // Script med begge globale variabler
    $script_block  = '<script>var myAjax = {
    ajaxurl: "' . admin_url("admin-ajax.php") . '",
    nonce: "' . wp_create_nonce('edit') . '"}</script>';

    return $content . $script_block;
}

add_action('wp_ajax_move_to_new_category', 'my_move_post_to_new_category');
function my_move_post_to_new_category() {

    // (Valgfri) Nonce-tjek – anbefalet hvis du kalder det fra backend
    check_ajax_referer('edit', 'nonce');

    // 1. Hent og valider post ID
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

    if ( ! $post_id || ! get_post($post_id) ) {
        wp_send_json_error(['message' => 'Ugyldigt post ID.']);
    }

    // 2. Tjek rettigheder
    if ( ! current_user_can('edit_post', $post_id) ) {
        wp_send_json_error(['message' => 'Du har ikke rettigheder til at redigere dette indlæg.']);
    }

    $targetCat = "";

    if($_POST["targetCategory"] != ""){
        $targetCat = $_POST["targetCategory"];
    }

    $new_cat_id = -1;

    // 3. Definér kategori-ID'er (RET DISSE)
    $list_parent_cat_id = get_term_by('slug', 'lister', 'category')->term_id; // ID på "liste-kategori" (parent)
    if($targetCat != ""){
        $new_cat_id         = get_term_by('slug', $targetCat, 'category')->term_id; // ID på "alle-nyheder"-kategorien
    }

    // 4. Hent nuværende kategorier for posten
    $current_cat_ids = wp_get_post_categories($post_id);

    // 5. Hent ALLE underkategorier under "liste-kategorien"
    $list_child_ids = get_terms([
        'taxonomy'   => 'category',
        'child_of'   => $list_parent_cat_id,
        'hide_empty' => false,
        'fields'     => 'ids',
    ]);

    if ( is_wp_error($list_child_ids) ) {
        wp_send_json_error(['message' => 'Kunne ikke hente underkategorier.']);
    }

    // 6. Fjern alle underkategorier fra postens kategorier
    $new_cat_ids = array_diff($current_cat_ids, $list_child_ids);

    // 7. Sørg for at "alle-nyheder" er på
    if($new_cat_id > -1){
        $new_cat_ids[] = $new_cat_id;
    }
    
    $new_cat_ids   = array_unique(array_map('intval', $new_cat_ids));

    // 8. Gem de nye kategorier på posten
    wp_set_post_categories($post_id, $new_cat_ids);

    //  Put it at the top
    
    global $wpdb;

    $lowest_order = $wpdb->get_var( $wpdb->prepare("
        SELECT MIN(p.menu_order)
        FROM {$wpdb->posts} p
        INNER JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
        INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
        WHERE tt.taxonomy = 'category'
        AND tt.term_id = %d
        AND p.post_type = 'post'
        AND p.post_status = 'publish'
    ", $new_cat_id) );

    // Hvis der findes en laveste værdi, gå 1 lavere – ellers sæt -100 som fallback
    $new_order = ($lowest_order !== null) ? ($lowest_order - 100) : -100;

    wp_update_post([
        'ID'         => $post_id,
        'menu_order' => $new_order
    ]);
    
    // 9. Returnér succes
    wp_send_json_success([
        'message'      => 'Kategorier opdateret.',
        'post_id'      => $post_id,
        'new_cat_ids'  => $new_cat_ids,
    ]);
}

add_action('wp_ajax_set_category_list', 'my_set_category_list_handler');

function my_set_category_list_handler() {
    // 1. Nonce-tjek
    check_ajax_referer('edit', 'nonce');

    // 2. Hent data fra request
    $ids             = isset($_POST['list']) ? (array) $_POST['list'] : [];
    $target_category = isset($_POST['targetCategory']) ? sanitize_text_field($_POST['targetCategory']) : '';

    if ( empty($ids) || empty($target_category) ) {
        wp_send_json_error(['message' => 'Manglende data (list eller targetCategory).']);
    }

    // Gør post-ids til ints
    $ids = array_map('intval', $ids);

    // 3. Find targetCategory som ID (slug eller ID)
    if ( ! is_numeric($target_category) ) {
        $cat = get_term_by('slug', $target_category, 'category');
        if ( ! $cat ) {
            wp_send_json_error(['message' => 'Ugyldig kategori (targetCategory).']);
        }
        $target_category_id = (int) $cat->term_id;
    } else {
        $target_category_id = (int) $target_category;
    }

    // 4. Find "list"-parent-kategorien (RET sluggen hvis den ikke er 'lister')
    $list_parent = get_term_by('slug', 'lister', 'category');
    if ( ! $list_parent ) {
        wp_send_json_error(['message' => 'Kunne ikke finde "lister"-kategorien.']);
    }
    $list_parent_id = (int) $list_parent->term_id;

    // 5. Hent alle underkategorier under "lister"
    $list_child_ids = get_terms([
        'taxonomy'   => 'category',
        'child_of'   => $list_parent_id,
        'hide_empty' => false,
        'fields'     => 'ids',
    ]);

    if ( is_wp_error($list_child_ids) ) {
        wp_send_json_error(['message' => 'Kunne ikke hente underkategorier under "lister".']);
    }

    // Sørg for at vi har et array (kan være tomt, og det er ok)
    $list_child_ids = array_map('intval', (array) $list_child_ids);

    // 6. Loop gennem posts og opdater kategorier + menu_order
    $order      = 100;
    $processed  = 0;

    foreach ( $ids as $post_id ) {
        if ( ! $post_id || ! get_post($post_id) ) {
            continue;
        }

        // Tjek rettigheder
        if ( ! current_user_can('edit_post', $post_id) ) {
            continue;
        }

        // Hent nuværende kategorier
        $current_cat_ids = wp_get_post_categories($post_id);

        // Fjern alle kategorier, der ligger under "lister"
        if ( ! empty($list_child_ids) && ! empty($current_cat_ids) ) {
            $current_cat_ids = array_diff($current_cat_ids, $list_child_ids);
        }

        // Tilføj targetCategory
        $current_cat_ids[] = $target_category_id;
        $new_cat_ids       = array_unique(array_map('intval', $current_cat_ids));

        // Gem kategorier
        wp_set_post_categories($post_id, $new_cat_ids);

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
 * Tilføj kategorier som JSON til indholdet
 */
add_action('wp_ajax_get_post_category_list', 'get_postCategoryList');
function get_postCategoryList() {

    // Find parent-kategorien via slug (mere robust end navn)
    $parent_cat = get_term_by('slug', 'lister', 'category');
    if (!$parent_cat) {
        return $content . '<p><strong>Ingen kategori med slug "lister" fundet.</strong></p>';
    }

    // Hent kun underkategorier af "lister"
    $terms = get_terms([
        'taxonomy'   => 'category',
        'hide_empty' => false,
        'parent'     => $parent_cat->term_id,
    ]);

    if (is_wp_error($terms) || empty($terms)) {
        return $content . '<p><strong>Ingen underkategorier fundet under "lister".</strong></p>';
    }

    // === 2) postListCategories: map postId -> [slugs...] ===
    $post_map = [];

    foreach ($terms as $term) {
        $posts = get_posts([
            'category'    => $term->term_id,
            'numberposts' => -1,
            'fields'      => 'ids',
        ]);

        foreach ($posts as $post_id) {
            if (!isset($post_map[$post_id])) {
                $post_map[$post_id] = [];
            }
            $post_map[$post_id][] = $term->slug;
        }
    }

    // 9. Returnér succes
    wp_send_json_success($post_map  );
    ;
}