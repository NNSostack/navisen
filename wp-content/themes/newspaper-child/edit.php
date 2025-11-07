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



//  Order by menu_order to be able to sort manualley
add_action('pre_get_posts', function($query) {
    if ($query->is_category()) {
        $query->set('orderby', 'menu_order date');
        $query->set('order', 'ASC');
    }
});