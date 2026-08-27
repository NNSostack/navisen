<?php
/**
 * Plugin Name: Navisen - tagDiv Video Timeout Fix
 * Description: Sikrer at td_video_support.php ikke bruger get_headers() uden timeout.
 * Det hele kører bare langsomt hvis ikke det bliver opdateret.
 * Version: 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

error_log('[Navisen tagDiv fix] MU-PLUGIN ER LOADED');
error_log('[Navisen tagDiv fix] WP_PLUGIN_DIR = ' . WP_PLUGIN_DIR);

class Navisen_TagDiv_Video_Timeout_Fix
{
    private static function get_target_file()
    {
        return WP_PLUGIN_DIR . '/td-composer/legacy/common/wp_booster/td_video_support.php';
    }

    private static function get_old_method()
    {
        return <<<'PHP'
private static function is_404($url) {
    $headers = @get_headers($url);
    if (!empty($headers[0]) and strpos($headers[0],'404') !== false) {
        return true;
    }
    return false;
}
PHP;
    }

    private static function get_new_method()
    {
        return <<<'PHP'
private static function is_404($url) {

    $response = wp_remote_head($url, array(
        'timeout'     => 1,
        'redirection' => 2,
    ));

    if (is_wp_error($response)) {
        return true;
    }

    $status_code = wp_remote_retrieve_response_code($response);

    return $status_code === 404;
}
PHP;
    }

    public static function patch()
    {
        $file = self::get_target_file();

        error_log('[Navisen tagDiv fix] PATCH() KØRES');
        error_log('[Navisen tagDiv fix] Target = ' . $file);

        if (!file_exists($file)) {
            error_log('[Navisen tagDiv fix] FIL IKKE FUNDET');
            return;
        }

        if (!is_readable($file)) {
            error_log('[Navisen tagDiv fix] FIL KAN IKKE LÆSES');
            return;
        }

        if (!is_writable($file)) {
            error_log('[Navisen tagDiv fix] FIL KAN IKKE SKRIVES');
            return;
        }

        $contents = file_get_contents($file);

        if ($contents === false) {
            error_log('[Navisen tagDiv fix] Kunne ikke læse filen');
            return;
        }

        /*
         * Matcher KUN den rigtige is_404()-metode,
         * hvis den stadig indeholder get_headers().
         */
        $pattern = '~private\s+static\s+function\s+is_404\s*\(\s*\$url\s*\)\s*\{
            .*?
            \$headers\s*=\s*@get_headers\s*\(\s*\$url\s*\)\s*;
            .*?
            return\s+false\s*;
            \s*
        \}~sx';

        /*
         * Hvis metoden allerede er patched, gør ingenting.
         */
        $already_patched_pattern = '~private\s+static\s+function\s+is_404\s*\(\s*\$url\s*\)\s*\{
            .*?
            wp_remote_head\s*\(
        ~sx';

        if (preg_match($already_patched_pattern, $contents)) {
            error_log('[Navisen tagDiv fix] OK - is_404() er allerede patched');
            return;
        }

        if (!preg_match($pattern, $contents)) {
            error_log('[Navisen tagDiv fix] FEJL - kunne stadig ikke finde is_404() med get_headers()');
            return;
        }

        $replacement = <<<'PHP'
    private static function is_404($url) {

            $response = wp_remote_head($url, array(
                'timeout'     => 1,
                'redirection' => 2,
            ));

            if (is_wp_error($response)) {
                return true;
            }

            $status_code = wp_remote_retrieve_response_code($response);

            return $status_code === 404;
        }
    PHP;

        $new_contents = preg_replace(
            $pattern,
            $replacement,
            $contents,
            1,
            $count
        );

        error_log('[Navisen tagDiv fix] Antal matches = ' . $count);

        if ($new_contents === null || $count !== 1) {
            error_log('[Navisen tagDiv fix] FEJL - patch blev ikke udført');
            return;
        }

        /*
         * Backup
         */
        $backup = $file . '.navisen-backup';

        if (!file_exists($backup)) {
            @copy($file, $backup);
        }

        if (file_put_contents($file, $new_contents, LOCK_EX) === false) {
            error_log('[Navisen tagDiv fix] FEJL - kunne ikke skrive filen');
            return;
        }

        /*
         * Ryd OPcache
         */
        if (function_exists('opcache_invalidate')) {
            @opcache_invalidate($file, true);
        }

        error_log('[Navisen tagDiv fix] SUCCESS - td_video_support.php blev patched');
    }
}

/*
 * Kør efter plugin-opdateringer.
 */
add_action('upgrader_process_complete', function ($upgrader, $options) {

    if (
        isset($options['type']) &&
        $options['type'] === 'plugin'
    ) {
        Navisen_TagDiv_Video_Timeout_Fix::patch();
    }

}, 10, 2);


/*
 * Sikkerhedscheck i admin.
 * Kører kun for administratorer og er meget billigt,
 * da den blot læser én fil.
 */
add_action('admin_init', function () {

    if (current_user_can('manage_options')) {
        Navisen_TagDiv_Video_Timeout_Fix::patch();
    }

});