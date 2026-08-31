<?php
/**
 * Plugin Name: Navisen - tagDiv Video Timeout Fix
 * Description: Patcher tagDiv video-funktioner med timeout og cache.
 * Version: 2.2.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class Navisen_TagDiv_Video_Timeout_Fix
{
    private const DEBUG = false;

    /**
     * Lav en fuld sikkerhedskontrol mindst én gang i døgnet.
     */
    private const FULL_VERIFY_INTERVAL = DAY_IN_SECONDS;

    /**
     * Gemmer signaturer for target/replacement-filer.
     */
    private const STATE_OPTION = 'navisen_tagdiv_video_fix_state_v2';

    /**
     * Alle patches samlet ét sted.
     *
     * target:
     *   Fil i td-composer som skal ændres.
     *
     * method:
     *   Navnet på metoden der skal udskiftes.
     *
     * replacement:
     *   Filen med koden mellem
     *   NAVISEN_PATCH_START og NAVISEN_PATCH_END.
     */
    private static function get_patches()
    {
        return array(

            'is_404' => array(
                'target' =>
                    WP_PLUGIN_DIR .
                    '/td-composer/legacy/common/wp_booster/td_video_support.php',

                'method' =>
                    'is_404',

                'replacement' =>
                    __DIR__ .
                    '/navisen-tagdiv-fix/is-404-replacement.php',
            ),

            'youtube_api_get_videos_info' => array(
                'target' =>
                    WP_PLUGIN_DIR .
                    '/td-composer/legacy/common/wp_booster/td_remote_video.php',

                'method' =>
                    'youtube_api_get_videos_info',

                'replacement' =>
                    __DIR__ .
                    '/navisen-tagdiv-fix/youtube-api-get-videos-info-replacement.php',
            ),
        );
    }

    private static function log($message)
    {
        if (self::DEBUG) {
            error_log('[Navisen tagDiv fix] ' . $message);
        }
    }

    /**
     * Billig filsignatur uden at læse filens indhold.
     */
    private static function get_file_signature($file)
    {
        clearstatcache(true, $file);

        if (!file_exists($file)) {
            return false;
        }

        $mtime = @filemtime($file);
        $size  = @filesize($file);

        if ($mtime === false || $size === false) {
            return false;
        }

        return array(
            'mtime' => (int) $mtime,
            'size'  => (int) $size,
        );
    }

    private static function signatures_match($a, $b)
    {
        return
            is_array($a) &&
            is_array($b) &&
            isset($a['mtime'], $a['size'], $b['mtime'], $b['size']) &&
            (int) $a['mtime'] === (int) $b['mtime'] &&
            (int) $a['size'] === (int) $b['size'];
    }

    /**
     * Bygger den nuværende billige state for alle filer.
     */
    private static function get_current_signatures()
    {
        $signatures = array();

        foreach (self::get_patches() as $key => $patch) {

            $target_signature =
                self::get_file_signature($patch['target']);

            $replacement_signature =
                self::get_file_signature($patch['replacement']);

            if ($target_signature === false) {
                error_log(
                    '[Navisen tagDiv fix] Target-fil blev ikke fundet for '
                    . $key . ': ' . $patch['target']
                );

                return false;
            }

            if ($replacement_signature === false) {
                error_log(
                    '[Navisen tagDiv fix] Replacement-fil blev ikke fundet for '
                    . $key . ': ' . $patch['replacement']
                );

                return false;
            }

            $signatures[$key] = array(
                'target'      => $target_signature,
                'replacement' => $replacement_signature,
            );
        }

        return $signatures;
    }

    private static function save_state()
    {
        $signatures = self::get_current_signatures();

        if ($signatures === false) {
            return;
        }

        update_option(
            self::STATE_OPTION,
            array(
                'signatures'    => $signatures,
                'last_verified' => time(),
                'version'       => '2.2.0',
            ),
            false
        );
    }

    /**
     * Hurtigt check i admin.
     *
     * På normale requests:
     * - filemtime()
     * - filesize()
     * - get_option()
     *
     * Hele PHP-filer læses kun hvis noget er ændret,
     * eller 24-timers sikkerhedskontrollen er udløbet.
     */
    public static function maybe_patch()
    {
        $current = self::get_current_signatures();

        if ($current === false) {
            return;
        }

        $state = get_option(self::STATE_OPTION, array());

        $all_unchanged = true;

        foreach ($current as $key => $signatures) {

            if (
                empty($state['signatures'][$key]['target']) ||
                empty($state['signatures'][$key]['replacement']) ||
                !self::signatures_match(
                    $state['signatures'][$key]['target'],
                    $signatures['target']
                ) ||
                !self::signatures_match(
                    $state['signatures'][$key]['replacement'],
                    $signatures['replacement']
                )
            ) {
                $all_unchanged = false;
                break;
            }
        }

        $last_verified = isset($state['last_verified'])
            ? (int) $state['last_verified']
            : 0;

        $full_verify_needed =
            $last_verified <= 0 ||
            (time() - $last_verified) >= self::FULL_VERIFY_INTERVAL;

        if ($all_unchanged && !$full_verify_needed) {
            self::log('Ingen filændringer - fuld kontrol springes over.');
            return;
        }

        self::patch_all();
    }

    /**
     * Henter koden mellem markørerne i replacement-filen.
     */
    private static function get_replacement_code($file)
    {
        if (!file_exists($file)) {
            error_log(
                '[Navisen tagDiv fix] Replacement-fil blev ikke fundet: '
                . $file
            );

            return false;
        }

        if (!is_readable($file)) {
            error_log(
                '[Navisen tagDiv fix] Replacement-fil kan ikke læses: '
                . $file
            );

            return false;
        }

        $contents = file_get_contents($file);

        if ($contents === false) {
            error_log(
                '[Navisen tagDiv fix] Kunne ikke læse replacement-fil: '
                . $file
            );

            return false;
        }

        $start_marker = '/* NAVISEN_PATCH_START */';
        $end_marker   = '/* NAVISEN_PATCH_END */';

        $start = strpos($contents, $start_marker);
        $end   = strpos($contents, $end_marker);

        if ($start === false || $end === false || $end <= $start) {
            error_log(
                '[Navisen tagDiv fix] START/END-markører mangler i: '
                . $file
            );

            return false;
        }

        $start += strlen($start_marker);

        return trim(
            substr(
                $contents,
                $start,
                $end - $start
            ),
            "\r\n"
        );
    }

    /**
     * Finder en navngiven metode via PHP tokenizer.
     */
    private static function find_method($contents, $wanted_method)
    {
        $tokens      = token_get_all($contents);
        $offset      = 0;
        $token_count = count($tokens);

        for ($i = 0; $i < $token_count; $i++) {

            $token = $tokens[$i];

            if (is_array($token)) {
                $text = $token[1];
                $id   = $token[0];
            } else {
                $text = $token;
                $id   = null;
            }

            if ($id === T_FUNCTION) {

                $function_offset = $offset;
                $function_name   = null;

                for ($j = $i + 1; $j < $token_count; $j++) {

                    $next = $tokens[$j];

                    if (is_array($next)) {

                        if ($next[0] === T_STRING) {
                            $function_name = $next[1];
                            break;
                        }

                    } elseif ($next === '(') {
                        break;
                    }
                }

                if ($function_name === $wanted_method) {

                    $before     = substr($contents, 0, $function_offset);
                    $line_start = strrpos($before, "\n");

                    if ($line_start === false) {
                        $line_start = 0;
                    } else {
                        $line_start++;
                    }

                    $brace_depth = 0;
                    $found_open  = false;
                    $scan_offset = $offset;

                    for ($k = $i; $k < $token_count; $k++) {

                        $scan_token = $tokens[$k];

                        if (is_array($scan_token)) {
                            $scan_text = $scan_token[1];
                        } else {
                            $scan_text = $scan_token;
                        }

                        if (!is_array($scan_token)) {

                            if ($scan_text === '{') {

                                $brace_depth++;
                                $found_open = true;

                            } elseif (
                                $scan_text === '}' &&
                                $found_open
                            ) {

                                $brace_depth--;

                                if ($brace_depth === 0) {

                                    $method_end =
                                        $scan_offset +
                                        strlen($scan_text);

                                    return array(
                                        'start'  => $line_start,
                                        'end'    => $method_end,
                                        'length' => $method_end - $line_start,
                                    );
                                }
                            }
                        }

                        $scan_offset += strlen($scan_text);
                    }
                }
            }

            $offset += strlen($text);
        }

        return false;
    }

    /**
     * Finder en rigtig PHP CLI-binær.
     */
    private static function get_php_cli()
    {
        $candidates = array(
            '/usr/bin/php8.4',
            '/usr/bin/php',
            '/usr/local/bin/php8.4',
            '/usr/local/bin/php',
        );

        foreach ($candidates as $candidate) {
            if (is_file($candidate) && is_executable($candidate)) {
                return $candidate;
            }
        }

        return false;
    }

    /**
     * Syntakstjek af midlertidig PHP-fil.
     */
    private static function syntax_check($file)
    {
        if (!function_exists('exec')) {
            return true;
        }

        $php_cli = self::get_php_cli();

        if ($php_cli === false) {
            self::log('Ingen PHP CLI fundet. Syntakstjek springes over.');
            return true;
        }

        $output    = array();
        $exit_code = 0;

        @exec(
            escapeshellcmd($php_cli)
            . ' -l '
            . escapeshellarg($file)
            . ' 2>&1',
            $output,
            $exit_code
        );

        if ($exit_code !== 0) {
            error_log(
                '[Navisen tagDiv fix] PHP syntaksfejl. '
                . 'Originalfilen blev IKKE ændret. '
                . implode(' ', $output)
            );

            return false;
        }

        return true;
    }

    /**
     * Patcher én metode.
     */
    private static function patch_one($key, $patch)
    {
        $target_file = $patch['target'];

        self::log(
            'Kontrollerer ' . $key . ' i ' . $target_file
        );

        if (!file_exists($target_file)) {
            error_log(
                '[Navisen tagDiv fix] Target-fil blev ikke fundet: '
                . $target_file
            );

            return false;
        }

        if (!is_readable($target_file)) {
            error_log(
                '[Navisen tagDiv fix] Target-fil kan ikke læses: '
                . $target_file
            );

            return false;
        }

        if (!is_writable($target_file)) {
            error_log(
                '[Navisen tagDiv fix] Target-fil kan ikke skrives: '
                . $target_file
            );

            return false;
        }

        $contents = file_get_contents($target_file);

        if ($contents === false) {
            error_log(
                '[Navisen tagDiv fix] Kunne ikke læse target-fil: '
                . $target_file
            );

            return false;
        }

        $replacement =
            self::get_replacement_code($patch['replacement']);

        if ($replacement === false) {
            return false;
        }

        $method =
            self::find_method($contents, $patch['method']);

        if ($method === false) {
            error_log(
                '[Navisen tagDiv fix] Kunne ikke finde metoden '
                . $patch['method']
                . ' i '
                . $target_file
            );

            return false;
        }

        $existing_method = substr(
            $contents,
            $method['start'],
            $method['length']
        );

        if (
            trim($existing_method) ===
            trim($replacement)
        ) {
            self::log($key . ' er allerede korrekt patched.');
            return true;
        }

        /**
         * Backup pr. fil.
         */
        $backup = $target_file . '.navisen-backup';

        if (!file_exists($backup)) {
            if (!@copy($target_file, $backup)) {
                error_log(
                    '[Navisen tagDiv fix] ADVARSEL: Kunne ikke oprette backup: '
                    . $backup
                );
            }
        }

        $new_contents =
            substr($contents, 0, $method['start'])
            . $replacement
            . substr($contents, $method['end']);

        $temporary_file =
            $target_file . '.navisen-tmp';

        if (
            file_put_contents(
                $temporary_file,
                $new_contents,
                LOCK_EX
            ) === false
        ) {
            error_log(
                '[Navisen tagDiv fix] Kunne ikke skrive temp-fil: '
                . $temporary_file
            );

            return false;
        }

        if (!self::syntax_check($temporary_file)) {
            @unlink($temporary_file);
            return false;
        }

        if (!@rename($temporary_file, $target_file)) {

            @unlink($temporary_file);

            error_log(
                '[Navisen tagDiv fix] Kunne ikke erstatte target-fil: '
                . $target_file
            );

            return false;
        }

        if (function_exists('opcache_invalidate')) {
            @opcache_invalidate($target_file, true);
        }

        error_log(
            '[Navisen tagDiv fix] SUCCESS - patched '
            . $patch['method']
            . ' i '
            . basename($target_file)
        );

        return true;
    }

    /**
     * Fuld kontrol af alle patches.
     */
    public static function patch_all()
    {
        self::log('Fuld PATCH-kontrol køres.');

        $all_ok = true;

        foreach (self::get_patches() as $key => $patch) {

            if (!self::patch_one($key, $patch)) {
                $all_ok = false;
            }
        }

        /**
         * State gemmes kun hvis alle patches er verificeret
         * eller gennemført korrekt.
         */
        if ($all_ok) {
            self::save_state();
        }
    }
}


/**
 * Efter plugin-opdateringer:
 * kør fuld kontrol med det samme.
 */
add_action(
    'upgrader_process_complete',
    function ($upgrader, $options) {

        if (
            isset($options['type']) &&
            $options['type'] === 'plugin'
        ) {
            Navisen_TagDiv_Video_Timeout_Fix::patch_all();
        }

    },
    10,
    2
);


/**
 * Billigt sikkerhedscheck når en administrator åbner wp-admin.
 */
add_action(
    'admin_init',
    function () {

        if (current_user_can('manage_options')) {
            Navisen_TagDiv_Video_Timeout_Fix::maybe_patch();
        }

    }
);
