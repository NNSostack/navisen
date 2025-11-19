<?php


/**
 * Tilføj kategorier som JSON til indholdet
 */
add_filter('the_content', 'append_subcategories_of_lister');
function append_subcategories_of_lister($content) {

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

    // === 1) listCategories: ren liste over underkategorier ===
    $cats = array_map(function ($term) {
        return [
            'id'    => $term->term_id,
            'name'  => $term->name,
            'slug'  => $term->slug,
            'url'   => get_term_link($term),
            'count' => (int) $term->count,
        ];
    }, $terms);

    $cats_json = wp_json_encode($cats, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    // Script med begge globale variabler
    $script_block  = "<script id=\"category-json\">\n";
    $script_block .= "window.listCategories = " . $cats_json . ";\n";
    $script_block .= "</script>";

    return $content . $script_block;
}

/**
 * Tilføj kategorier som JSON til indholdet
 */
add_filter('the_content', 'append_flexbox_config');
function append_flexbox_config($content) {

    $front_page_id = get_option('page_on_front');
    $content .= getBoxesConfig($front_page_id, "frontpageBoxes");
    $content .= getBoxesConfig(null, "currentPageBoxes");

    return $content;

}

//  Only get alle-nyheder if not in one of the other lists
add_action('pre_get_posts', function($query) {
    if (is_admin() || !$query->is_category()) {
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
    // Stop i backend, REST, cron, og preview
    if (
        $taxonomy !== 'category' ||
        is_admin() ||
        defined('REST_REQUEST') && REST_REQUEST ||
        defined('DOING_CRON') && DOING_CRON ||
        (defined('DOING_AJAX') && DOING_AJAX) ||
        is_preview() ||
        empty($terms)
    ) {
        return $terms;
    }

    // Find ID’er på kategorier, der skal skjules
    $exclude_ids = [];
    $all_news = get_category_by_slug('alle-nyheder');
    $lister   = get_category_by_slug('lister');

    if ($all_news) {
        $exclude_ids[] = $all_news->term_id;
    }

    if ($lister) {
        $exclude_ids[] = $lister->term_id;

        $child_terms = get_terms([
            'taxonomy'   => 'category',
            'child_of'   => $lister->term_id,
            'hide_empty' => false,
            'fields'     => 'ids',
        ]);

        if (!is_wp_error($child_terms)) {
            $exclude_ids = array_merge($exclude_ids, $child_terms);
        }
    }

    // Filtrér uønskede kategorier fra
    $filtered = array_filter($terms, function($term) use ($exclude_ids) {
        return !in_array($term->term_id, $exclude_ids);
    });

    return $filtered;
}, 10, 3);

//  Also show future posts
add_action('pre_get_posts', function($query) {
    global $isEditListPage;
    // Kun frontend + hoved-query
    if (is_admin() || !$isEditListPage) {
        return;
    }

    // Kun for brugere der kan redigere indlæg (redaktør, admin osv.)
    if (!is_user_logged_in() || !current_user_can('edit_posts')) {
        return;
    }

    // Tillad publish + future
    $query->set('post_status', ['publish', 'future']);
});

$isEditListPage = false;
//  Set edit lists url og var, der fortæller om vi allerede er på den side
add_action('wp_head', function () {
    global $isEditListPage;
    global $post;

    // Standardværdier (så variablerne altid eksisterer)
    $edit_lists_url = 'null';
    $edit_lists     = 'false';

    // 1️⃣ Find siden med edit_lists = true
    $edit_page = get_pages([
        'meta_key'   => 'edit_lists',
        'meta_value' => 'true',
        'post_status'=> 'publish',
        'number'     => 1
    ]);

    if (!empty($edit_page)) {
        $edit_lists_url = "'" . esc_url(get_permalink($edit_page[0]->ID)) . "'";
    }

    // 2️⃣ Hvis vi står på selve redigeringssiden
    if (is_page() && $post && get_post_meta($post->ID, 'edit_lists', true) === 'true') {
        $edit_lists = 'true';
        $isEditListPage = true;
    }

    // 3️⃣ Udskriv samlet <script>-blok
    echo "<script>
        window.editListsUrl = {$edit_lists_url};
        window.editLists = {$edit_lists};
    </script>";
});


function getBoxesConfig($id, $jsonName){
    $boxes = get_tagdiv_flexboxes_config($id);
    if ( empty( $boxes ) ) {
        return '';
    }

    $json = wp_json_encode(
        $boxes,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );

    $script  = "<script id=\"flexboxes-" . $jsonName        ."config\">\n";
    $script .= "window." . $jsonName . " = " . $json . ";\n";
    $script .= "</script>";

    return $script;
}


/**
 * Find TagDiv Flex Blocks på en given side, og returnér config som array.
 *
 * @param int|null $post_id
 * @return array
 */
function get_tagdiv_flexboxes_config( $post_id = null ) {
    if ( ! $post_id ) {
        $post_id = get_the_ID();
    }
    if ( ! $post_id ) {
        return [];
    }

    // Forsøg at hente TagDiv Composer-indhold
    $raw = get_post_meta( $post_id, 'tdc_content', true );
    if ( empty( $raw ) ) {
        $raw = get_post_meta( $post_id, 'tdc_layout', true );
    }
    if ( empty( $raw ) ) {
        $post = get_post( $post_id );
        $raw  = $post ? $post->post_content : '';
    }

    if ( empty( $raw ) ) {
        return [];
    }

    // Justér her hvilke shortcodes du vil fange:
    // tdb_flex_block_1 = TagDiv Cloud Template flex block
    // td_block_big_grid_flex_1 = klassisk Newspaper Flex Grid
    $pattern = '/\[(td_flex_block_\d+)([^\]]*)\]/';

    if ( ! preg_match_all( $pattern, $raw, $matches, PREG_SET_ORDER ) ) {
        return [];
    }

    $boxes = [];
    $index = 0;

    foreach ( $matches as $match ) {
        $shortcode_tag = isset( $match[1] ) ? $match[1] : '';
        $attr_string   = isset( $match[2] ) ? $match[2] : '';

        $atts = shortcode_parse_atts( $attr_string );

        // Læs limit (antal posts i boksen)
        $limit = isset( $atts['limit'] ) ? (int) $atts['limit'] : null;

        // Læs kategorier – TagDiv bruger typisk category_id eller category_ids
        $category_ids = [];

        if ( ! empty( $atts['category_id'] ) ) {
            $category_ids[] = (int) $atts['category_id'];
        }

        if ( ! empty( $atts['category_ids'] ) ) {
            $parts = array_map( 'trim', explode( ',', $atts['category_ids'] ) );
            foreach ( $parts as $id ) {
                if ( $id !== '' ) {
                    $category_ids[] = (int) $id;
                }
            }
        }

        $category_ids = array_unique( $category_ids );

        $categories = [];
        foreach ( $category_ids as $cat_id ) {
            $term = get_category( $cat_id );
            if ( $term && ! is_wp_error( $term ) ) {
                $categories[] = [
                    'id'   => $term->term_id,
                    'slug' => $term->slug,
                    'name' => $term->name,
                ];
            }
        }

        $boxes[] = [
            'index'      => $index++,
            'shortcode'  => $shortcode_tag,
            'limit'      => $limit,
            'category' => count($categories) > 0 ? $categories[0] : null,
        ];
    }

    return $boxes;
}

