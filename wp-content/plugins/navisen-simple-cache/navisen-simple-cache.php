<?php
/**
 * Plugin Name: Navisen Simple Cache
 * Description: Simpel disk-cache til anonyme besøgende på Navisen.dk.
 * Version: 2.1.2
 * Author: Navisen
 */

if (!defined('ABSPATH')) {
    exit;
}

class Navisen_Simple_Cache
{
    /**
     * Cache placeres direkte under wp-content/cache/.
     *
     * URL:
     * /nyheder/min-artikel/
     *
     * bliver til:
     * wp-content/cache/navisen-simple-cache/nyheder/min-artikel/index.html
     */
    private static function cache_dir()
    {
        return WP_CONTENT_DIR . '/cache/navisen-simple-cache';
    }

    public static function init()
    {
        add_action('template_redirect', [__CLASS__, 'start_cache'], 0);

        add_action('save_post', [__CLASS__, 'post_saved'], 20, 3);
        add_action('before_delete_post', [__CLASS__, 'post_deleted']);
        add_action('trashed_post', [__CLASS__, 'post_deleted']);

        add_action('transition_comment_status', [__CLASS__, 'comment_status_changed'], 10, 3);
        add_action('comment_post', [__CLASS__, 'comment_changed']);
        add_action('edit_comment', [__CLASS__, 'comment_changed']);

        add_action('created_term', [__CLASS__, 'clear_all_cache']);
        add_action('edited_term', [__CLASS__, 'clear_all_cache']);
        add_action('delete_term', [__CLASS__, 'clear_all_cache']);

        add_action('switch_theme', [__CLASS__, 'clear_all_cache']);

        /**
         * Admin.
         */
        add_action('admin_menu', [__CLASS__, 'admin_menu']);
        add_action('admin_bar_menu', [__CLASS__, 'admin_bar'], 100);

        add_action('admin_post_navisen_clear_cache', [__CLASS__, 'manual_clear_cache']);
        add_action('admin_post_navisen_refresh_page_cache', [__CLASS__, 'manual_refresh_page_cache']);

        register_activation_hook(__FILE__, [__CLASS__, 'activate']);
        register_deactivation_hook(__FILE__, [__CLASS__, 'clear_all_cache']);
    }

    public static function activate()
    {
        self::ensure_cache_dir();
    }

    private static function ensure_cache_dir()
    {
        $dir = self::cache_dir();

        if (!is_dir($dir)) {
            wp_mkdir_p($dir);
        }
    }

    /**
     * Afgør om denne request må caches.
     */
    private static function should_cache()
    {
        if (is_admin()) {
            return false;
        }

        if (wp_doing_ajax()) {
            return false;
        }

        if (defined('REST_REQUEST') && REST_REQUEST) {
            return false;
        }

        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
            return false;
        }

        if (is_user_logged_in()) {
            return false;
        }

        /**
         * Query strings caches ikke.
         */
        if (!empty($_SERVER['QUERY_STRING'])) {
            return false;
        }

        if (
            is_search() ||
            is_preview() ||
            is_feed() ||
            is_trackback() ||
            is_robots() ||
            is_404()
        ) {
            return false;
        }

        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        if (
            $uri === '/bookmark-page/' ||
            $uri === '/bookmark-page'
        ) {
            return false;
        }

        $blocked_paths = [
            '/wp-admin/',
            '/wp-login.php',
            '/xmlrpc.php',
            '/wp-json/',
        ];

        foreach ($blocked_paths as $blocked) {
            if (strpos($uri, $blocked) !== false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Start output buffering.
     */
    public static function start_cache()
    {
        if (!self::should_cache()) {
            return;
        }

        ob_start([__CLASS__, 'save_cache']);
    }

    /**
     * Gem den færdige HTML-side.
     */
    public static function save_cache($html)
    {
        if (!self::should_cache()) {
            return $html;
        }

        if (http_response_code() !== 200) {
            return $html;
        }

        if (trim($html) === '') {
            return $html;
        }

        $trimmed = ltrim($html);

        /**
         * Undgå JSON/XML.
         */
        if (
            strpos($trimmed, '{') === 0 ||
            strpos($trimmed, '[') === 0 ||
            stripos($trimmed, '<?xml') === 0
        ) {
            return $html;
        }

        $cache_file = self::get_cache_file_from_request();

        if (!$cache_file) {
            return $html;
        }

        $directory = dirname($cache_file);

        if (!is_dir($directory)) {
            wp_mkdir_p($directory);
        }

        if (!is_dir($directory)) {
            return $html;
        }

        /**
         * Ret Newspaper/tagDiv bogmærker på cachede sider.
         *
         * Scriptet bliver kun indsat i den HTML, som gemmes i cachen.
         * Det opdaterer både antal favoritter og selected-state ud fra
         * cookien tdb_favourites.
         */
        $bookmark_script = <<<'HTML'
<script>
(function () {

    function getCookie(name) {
        const prefix = name + '=';
        const cookies = document.cookie.split(';');

        for (let i = 0; i < cookies.length; i++) {
            const cookie = cookies[i].trim();

            if (cookie.indexOf(prefix) === 0) {
                return decodeURIComponent(
                    cookie.substring(prefix.length)
                );
            }
        }

        return '';
    }

    function updateBookmarks() {
        const cookieValue = getCookie('tdb_favourites');

        const favourites = cookieValue
            ? cookieValue
                .split(',')
                .map(function (id) {
                    return id.trim();
                })
                .filter(Boolean)
            : [];

        document
            .querySelectorAll('.tdb-wmf-count')
            .forEach(function (element) {
                element.textContent = favourites.length;
            });

        document
            .querySelectorAll('.tdb-favorite[data-post-id]')
            .forEach(function (button) {

                const postId = String(
                    button.getAttribute('data-post-id')
                );

                if (favourites.includes(postId)) {
                    button.classList.add(
                        'tdb-favorite-selected'
                    );
                } else {
                    button.classList.remove(
                        'tdb-favorite-selected'
                    );
                }

            });
    }

    if (document.readyState === 'loading') {
        document.addEventListener(
            'DOMContentLoaded',
            updateBookmarks
        );
    } else {
        updateBookmarks();
    }

    document.addEventListener('click', function (event) {

        if (!event.target.closest('.tdb-favorite[data-post-id]')) {
            return;
        }

        // Lad tagDiv opdatere tdb_favourites-cookien først.
        setTimeout(updateBookmarks, 150);
    });

})();
</script>
HTML;

        if (stripos($html, '</body>') !== false) {
            $html = str_ireplace(
                '</body>',
                $bookmark_script . '</body>',
                $html
            );
        }

        /**
         * Atomisk skrivning.
         */
        $tmp = $cache_file . '.' . uniqid('', true) . '.tmp';

        if (@file_put_contents($tmp, $html, LOCK_EX) !== false) {
            @rename($tmp, $cache_file);
        } else {
            @unlink($tmp);
        }

        return $html;
    }

    /**
     * Find cachefil for aktuel request.
     */
    private static function get_cache_file_from_request()
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        $path = parse_url($uri, PHP_URL_PATH);

        return self::path_to_cache_file($path);
    }

    /**
     * URL path -> cachefil.
     */
    private static function path_to_cache_file($path)
    {
        if (!$path) {
            $path = '/';
        }

        $path = rawurldecode($path);

        $segments = explode('/', trim($path, '/'));

        $safe_segments = [];

        foreach ($segments as $segment) {

            if ($segment === '') {
                continue;
            }

            $segment = sanitize_file_name($segment);

            if ($segment === '') {
                continue;
            }

            $safe_segments[] = $segment;
        }

        $base = trailingslashit(self::cache_dir());

        if (!$safe_segments) {
            return $base . 'index.html';
        }

        return $base
            . implode(DIRECTORY_SEPARATOR, $safe_segments)
            . DIRECTORY_SEPARATOR
            . 'index.html';
    }

    /**
     * URL -> cachefil.
     */
    private static function url_to_cache_file($url)
    {
        if (!$url) {
            return false;
        }

        $path = parse_url($url, PHP_URL_PATH);

        return self::path_to_cache_file($path);
    }

    /**
     * Indlæg gemt.
     */
    public static function post_saved($post_id, $post, $update)
    {
        if (wp_is_post_revision($post_id)) {
            return;
        }

        if (wp_is_post_autosave($post_id)) {
            return;
        }

        self::clear_post_cache($post_id);
    }

    public static function post_deleted($post_id)
    {
        self::clear_post_cache($post_id);
    }

    /**
     * Ryd relevante URL'er for et indlæg.
     */
    private static function clear_post_cache($post_id)
    {
        /**
         * Selve artiklen.
         */
        $permalink = get_permalink($post_id);

        if ($permalink) {
            self::delete_url($permalink);
        }

        /**
         * Forsiden.
         */
        self::delete_url(home_url('/'));

        /**
         * Indlægsoversigt.
         */
        $page_for_posts = (int) get_option('page_for_posts');

        if ($page_for_posts) {

            $url = get_permalink($page_for_posts);

            if ($url) {
                self::delete_url($url);
            }
        }

        /**
         * Kategorier.
         */
        $categories = get_the_category($post_id);

        foreach ($categories as $category) {

            $url = get_category_link($category);

            if (!is_wp_error($url)) {
                self::clear_archive($url);
            }
        }

        /**
         * Tags.
         */
        $tags = get_the_tags($post_id);

        if ($tags) {

            foreach ($tags as $tag) {

                $url = get_tag_link($tag);

                if (!is_wp_error($url)) {
                    self::clear_archive($url);
                }
            }
        }

        /**
         * Forfatterarkiv.
         */
        $author_id = (int) get_post_field(
            'post_author',
            $post_id
        );

        if ($author_id) {

            $url = get_author_posts_url($author_id);

            self::clear_archive($url);
        }
    }

    /**
     * Ryd et helt arkiv inkl. pagination.
     */
    private static function clear_archive($url)
    {
        $file = self::url_to_cache_file($url);

        if (!$file) {
            return;
        }

        $directory = dirname($file);

        self::delete_directory($directory);
    }

    /**
     * Slet cache for én URL.
     */
    private static function delete_url($url)
    {
        $file = self::url_to_cache_file($url);

        if ($file && file_exists($file)) {
            @unlink($file);
        }
    }

    /**
     * Kommentar ændret.
     */
    public static function comment_status_changed(
        $new_status,
        $old_status,
        $comment
    ) {
        if (!empty($comment->comment_post_ID)) {
            self::clear_post_cache(
                $comment->comment_post_ID
            );
        }
    }

    public static function comment_changed($comment_id)
    {
        $comment = get_comment($comment_id);

        if ($comment) {
            self::clear_post_cache(
                $comment->comment_post_ID
            );
        }
    }

    /**
     * Ryd hele cache.
     */
    public static function clear_all_cache()
    {
        $dir = self::cache_dir();

        if (!is_dir($dir)) {
            return;
        }

        self::delete_directory($dir);

        self::ensure_cache_dir();
    }

    /**
     * Rekursiv sletning.
     */
    private static function delete_directory($dir)
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = scandir($dir);

        if (!$items) {
            return;
        }

        foreach ($items as $item) {

            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $dir . DIRECTORY_SEPARATOR . $item;

            if (is_dir($path)) {
                self::delete_directory($path);
            } else {
                @unlink($path);
            }
        }

        @rmdir($dir);
    }

    /**
     * Hent statistik om cache.
     */
    private static function get_cache_stats()
    {
        $dir = self::cache_dir();

        $stats = [
            'files'      => 0,
            'size'       => 0,
            'oldest'     => null,
            'newest'     => null,
        ];

        if (!is_dir($dir)) {
            return $stats;
        }

        try {

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    $dir,
                    FilesystemIterator::SKIP_DOTS
                )
            );

            foreach ($iterator as $file) {

                if (!$file->isFile()) {
                    continue;
                }

                if (strtolower($file->getExtension()) !== 'html') {
                    continue;
                }

                $stats['files']++;
                $stats['size'] += $file->getSize();

                $modified = $file->getMTime();

                if (
                    $stats['oldest'] === null ||
                    $modified < $stats['oldest']
                ) {
                    $stats['oldest'] = $modified;
                }

                if (
                    $stats['newest'] === null ||
                    $modified > $stats['newest']
                ) {
                    $stats['newest'] = $modified;
                }
            }

        } catch (Exception $e) {
            // Cache-statistik må aldrig påvirke selve sitet.
        }

        return $stats;
    }

    /**
     * Formatér filstørrelse.
     */
    private static function format_bytes($bytes)
    {
        if ($bytes <= 0) {
            return '0 KB';
        }

        return size_format($bytes, 2);
    }

    /**
     * Admin-menu.
     */
    public static function admin_menu()
    {
        add_management_page(
            'Navisen Cache',
            'Navisen Cache',
            'manage_options',
            'navisen-simple-cache',
            [__CLASS__, 'admin_page']
        );
    }

    /**
     * Adminside.
     */
    public static function admin_page()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $stats = self::get_cache_stats();

        $clear_url = wp_nonce_url(
            admin_url(
                'admin-post.php?action=navisen_clear_cache'
            ),
            'navisen_clear_cache'
        );

        ?>
        <div class="wrap">

            <h1>Navisen Cache</h1>

            <?php if (!empty($_GET['cache-cleared'])) : ?>

                <div class="notice notice-success is-dismissible">
                    <p>
                        Hele cachen er blevet ryddet.
                    </p>
                </div>

            <?php endif; ?>

            <p>
                Her kan du se status på den cache, der bliver leveret
                direkte til anonyme besøgende.
            </p>

            <div
                style="
                    display:grid;
                    grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
                    gap:15px;
                    max-width:900px;
                    margin-top:25px;
                "
            >

                <div
                    style="
                        background:#fff;
                        border:1px solid #dcdcde;
                        padding:20px;
                    "
                >
                    <div
                        style="
                            font-size:13px;
                            color:#646970;
                            margin-bottom:8px;
                        "
                    >
                        Cachede sider
                    </div>

                    <div
                        style="
                            font-size:32px;
                            font-weight:600;
                        "
                    >
                        <?php echo number_format_i18n($stats['files']); ?>
                    </div>
                </div>


                <div
                    style="
                        background:#fff;
                        border:1px solid #dcdcde;
                        padding:20px;
                    "
                >
                    <div
                        style="
                            font-size:13px;
                            color:#646970;
                            margin-bottom:8px;
                        "
                    >
                        Samlet størrelse
                    </div>

                    <div
                        style="
                            font-size:32px;
                            font-weight:600;
                        "
                    >
                        <?php echo esc_html(
                            self::format_bytes($stats['size'])
                        ); ?>
                    </div>
                </div>


                <div
                    style="
                        background:#fff;
                        border:1px solid #dcdcde;
                        padding:20px;
                    "
                >
                    <div
                        style="
                            font-size:13px;
                            color:#646970;
                            margin-bottom:8px;
                        "
                    >
                        Nyeste cachefil
                    </div>

                    <div
                        style="
                            font-size:18px;
                            font-weight:600;
                        "
                    >
                        <?php

                        if ($stats['newest']) {

                            echo esc_html(
                                wp_date(
                                    'd/m/Y H:i:s',
                                    $stats['newest']
                                )
                            );

                        } else {

                            echo 'Ingen cache';

                        }

                        ?>
                    </div>
                </div>


                <div
                    style="
                        background:#fff;
                        border:1px solid #dcdcde;
                        padding:20px;
                    "
                >
                    <div
                        style="
                            font-size:13px;
                            color:#646970;
                            margin-bottom:8px;
                        "
                    >
                        Ældste cachefil
                    </div>

                    <div
                        style="
                            font-size:18px;
                            font-weight:600;
                        "
                    >
                        <?php

                        if ($stats['oldest']) {

                            echo esc_html(
                                wp_date(
                                    'd/m/Y H:i:s',
                                    $stats['oldest']
                                )
                            );

                        } else {

                            echo 'Ingen cache';

                        }

                        ?>
                    </div>
                </div>

            </div>


            <div
                style="
                    background:#fff;
                    border:1px solid #dcdcde;
                    padding:20px;
                    max-width:860px;
                    margin-top:20px;
                "
            >

                <h2 style="margin-top:0;">
                    Cachemappe
                </h2>

                <code>
                    <?php echo esc_html(self::cache_dir()); ?>
                </code>

            </div>


            <div
                style="
                    background:#fff;
                    border:1px solid #dcdcde;
                    padding:20px;
                    max-width:860px;
                    margin-top:20px;
                "
            >

                <h2 style="margin-top:0;">
                    Ryd cache
                </h2>

                <p>
                    Dette sletter alle cachede HTML-sider.
                    De bliver automatisk oprettet igen,
                    når siderne besøges.
                </p>

                <p>
                    <a
                        href="<?php echo esc_url($clear_url); ?>"
                        class="button button-primary"
                        onclick="return confirm('Er du sikker på, at du vil rydde hele cachen?');"
                    >
                        Ryd hele cachen
                    </a>
                </p>

            </div>

        </div>
        <?php
    }

    /**
     * Admin-bar knap.
     */
    public static function admin_bar($wp_admin_bar)
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $url = wp_nonce_url(
            admin_url(
                'admin-post.php?action=navisen_clear_cache'
            ),
            'navisen_clear_cache'
        );

        $wp_admin_bar->add_node([
            'id'    => 'navisen-simple-cache',
            'title' => 'Ryd Navisen cache',
            'href'  => $url,
        ]);

        /**
         * På frontend kan den aktuelle sides cache opdateres direkte.
         */
        if (!is_admin()) {
            $current_url = home_url(wp_unslash($_SERVER['REQUEST_URI'] ?? '/'));

            $refresh_url = wp_nonce_url(
                add_query_arg(
                    [
                        'action' => 'navisen_refresh_page_cache',
                        'url'    => $current_url,
                    ],
                    admin_url('admin-post.php')
                ),
                'navisen_refresh_page_cache'
            );

            $wp_admin_bar->add_node([
                'id'     => 'navisen-refresh-page-cache',
                'parent' => 'navisen-simple-cache',
                'title'  => 'Opdater cache for denne side',
                'href'   => $refresh_url,
            ]);
        }
    }

    /**
     * Ryd og genopbyg cache for én bestemt side.
     */
    public static function manual_refresh_page_cache()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Ingen adgang.');
        }

        check_admin_referer('navisen_refresh_page_cache');

        $url = isset($_GET['url'])
            ? esc_url_raw(wp_unslash($_GET['url']))
            : '';

        if (!$url) {
            wp_die('Ugyldig URL.');
        }

        /**
         * Tillad kun URL'er på dette WordPress-site.
         */
        $site_host = wp_parse_url(home_url('/'), PHP_URL_HOST);
        $url_host  = wp_parse_url($url, PHP_URL_HOST);

        if (!$site_host || !$url_host || strtolower($site_host) !== strtolower($url_host)) {
            wp_die('URL tilhører ikke dette website.');
        }

        /**
         * Cache-pluginet cacher ikke query strings, så varm kun den rene URL.
         */
        $scheme = wp_parse_url($url, PHP_URL_SCHEME);
        $host   = wp_parse_url($url, PHP_URL_HOST);
        $port   = wp_parse_url($url, PHP_URL_PORT);
        $path   = wp_parse_url($url, PHP_URL_PATH) ?: '/';

        $clean_url = $scheme . '://' . $host;

        if ($port) {
            $clean_url .= ':' . $port;
        }

        $clean_url .= $path;

        /**
         * Fjern den eksisterende cachefil.
         */
        self::delete_url($clean_url);

        /**
         * Hent siden som anonym bruger. Da cachefilen lige er slettet,
         * går requesten gennem WordPress og opretter en frisk cachefil.
         */
        wp_remote_get(
            $clean_url,
            [
                'timeout'     => 20,
                'redirection' => 3,
                'cookies'     => [],
                'headers'     => [
                    'Cache-Control' => 'no-cache',
                ],
                'user-agent'  => 'Navisen Simple Cache Warmer/' . get_bloginfo('version'),
            ]
        );

        wp_safe_redirect($clean_url);
        exit;
    }

    /**
     * Manuel cache-rydning.
     */
    public static function manual_clear_cache()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Ingen adgang.');
        }

        check_admin_referer(
            'navisen_clear_cache'
        );

        self::clear_all_cache();

        /**
         * Hvis rydning blev startet fra cache-adminsiden,
         * så send brugeren tilbage dertil.
         */
        $referer = wp_get_referer();

        if (
            $referer &&
            strpos($referer, 'page=navisen-simple-cache') !== false
        ) {

            wp_safe_redirect(
                add_query_arg(
                    'cache-cleared',
                    '1',
                    admin_url(
                        'tools.php?page=navisen-simple-cache'
                    )
                )
            );

        } else {

            wp_safe_redirect(
                $referer ?: admin_url()
            );

        }

        exit;
    }
}

/* Show in status of theme that we have a Caching plugin */
add_action('admin_footer', function () {

    if (!is_admin()) {
        return;
    }

    $page = isset($_GET['page'])
        ? sanitize_key(wp_unslash($_GET['page']))
        : '';

    if ($page !== 'td_system_status') {
        return;
    }

    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function () {

        document.querySelectorAll('tr').forEach(function (row) {

            const name = row.querySelector('.td-system-status-name');

            if (!name || name.textContent.trim() !== 'Caching plugin') {
                return;
            }

            const led = row.querySelector('.td-system-status-led');
            const value = row.querySelector('.td-system-status-value');

            if (led) {
                led.classList.remove('td-system-status-yellow');
                led.classList.add('td-system-status-green');
            }

            if (value) {
                value.innerHTML =
                    'Navisen Simple Cache - ' +
                    '<span class="td-status-small-text">' +
                    'disk cache active for anonymous visitors' +
                    '</span>';
            }

        });

    });
    </script>
    <?php
});

Navisen_Simple_Cache::init();
