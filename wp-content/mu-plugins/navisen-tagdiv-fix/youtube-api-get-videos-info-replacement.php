<?php

/*
 * Denne fil bliver IKKE kørt direkte af WordPress.
 *
 * MU-pluginet læser filen som almindelig tekst og indsætter
 * koden mellem NAVISEN_PATCH_START og NAVISEN_PATCH_END i:
 *
 * td-composer/legacy/common/wp_booster/td_remote_video.php
 *
 * Metoden der erstattes:
 *
 * youtube_api_get_videos_info()
 */


/* NAVISEN_PATCH_START */
	private static function youtube_api_get_videos_info($video_ids) {

        /*
         * Ryd tomme IDs.
         */
        $video_ids = array_values(
            array_filter($video_ids)
        );

        if (empty($video_ids)) {
            return false;
        }

        /*
         * Stabil rækkefølge giver samme cache-key,
         * selv hvis samme videosæt kommer i forskellig rækkefølge.
         */
        sort($video_ids);

        $video_ids_comma = implode(',', $video_ids);

        /*
         * Vi cacher selve det færdige tagDiv-resultat.
         *
         * Der caches KUN ved et gyldigt svar.
         */
        $cache_key =
            'navisen_td_yt_info_' .
            md5($video_ids_comma);

        $cached = get_transient($cache_key);

        if ($cached !== false) {
            return $cached;
        }

        /*
         * Byg samme YouTube API-URL som tagDiv gjorde før.
         */
        $api_url =
            'https://www.googleapis.com/youtube/v3/videos?id='
            . $video_ids_comma
            . '&part=id,contentDetails,snippet,player&key='
            . self::get_yt_api_key();

        /*
         * Målrettet request til YouTube.
         *
         * Vi går IKKE gennem td_remote_http, fordi den globale
         * timeout dér er 30 sekunder og bruges af mange andre
         * tagDiv-funktioner.
         *
         * Her begrænser vi kun YouTube video-info requestet.
         */
        $response = wp_remote_get(
            $api_url,
            array(
                'timeout'     => 2,
                'redirection' => 2,
                'sslverify'   => true,
                'headers'     => array(
                    'Accept-Language' => 'en',
                ),
                'user-agent'  =>
                    'Mozilla/5.0 (Navisen WordPress)',
            )
        );

        /*
         * Netværksfejl / timeout caches IKKE.
         */
        if (is_wp_error($response)) {

            td_log::log(
                __FILE__,
                __FUNCTION__,
                'YouTube API request failed',
                $response->get_error_message()
            );

            return false;
        }

        $status_code =
            wp_remote_retrieve_response_code($response);

        /*
         * Kun 2xx behandles som et gyldigt API-svar.
         *
         * Fejl som 403, 429, 500 osv. caches IKKE.
         */
        if (
            $status_code < 200 ||
            $status_code >= 300
        ) {

            td_log::log(
                __FILE__,
                __FUNCTION__,
                'YouTube API returned HTTP status ' . $status_code,
                $api_url
            );

            return false;
        }

        $body =
            wp_remote_retrieve_body($response);

        if ($body === '') {
            return false;
        }

        /*
         * Brug fortsat tagDivs egen JSON-validering.
         */
        $json_api_response =
            self::check_api_response(
                $body,
                $api_url
            );

        if (
            $json_api_response === false ||
            empty($json_api_response['items']) ||
            !is_array($json_api_response['items'])
        ) {
            return false;
        }

        $buffy_videos = array();

        foreach (
            $json_api_response['items']
            as $video_item
        ) {

            if (empty($video_item['id'])) {
                continue;
            }

            try {

                $duration = isset(
                    $video_item['contentDetails']['duration']
                )
                    ? $video_item['contentDetails']['duration']
                    : '';

                /*
                 * Samme duration-logik som original tagDiv.
                 */
                if (!empty($duration)) {

                    preg_match(
                        '/(\d+)H/',
                        $duration,
                        $match
                    );

                    $h = count($match)
                        ? filter_var(
                            $match[0],
                            FILTER_SANITIZE_NUMBER_INT
                        )
                        : 0;

                    preg_match(
                        '/(\d+)M/',
                        $duration,
                        $match
                    );

                    $m = count($match)
                        ? filter_var(
                            $match[0],
                            FILTER_SANITIZE_NUMBER_INT
                        )
                        : 0;

                    preg_match(
                        '/(\d+)S/',
                        $duration,
                        $match
                    );

                    $s = count($match)
                        ? filter_var(
                            $match[0],
                            FILTER_SANITIZE_NUMBER_INT
                        )
                        : 0;

                    if (intval($h) === 0) {

                        $duration = gmdate(
                            'i:s',
                            intval($m * 60 + $s)
                        );

                    } else {

                        $duration = gmdate(
                            'H:i:s',
                            intval(
                                $h * 3600 +
                                $m * 60 +
                                $s
                            )
                        );
                    }
                }

                $video_id =
                    $video_item['id'];

                $buffy_videos[$video_id] = array(

                    'thumb' =>
                        td_global::$http_or_https
                        . '://img.youtube.com/vi/'
                        . $video_id
                        . '/default.jpg',

                    'standard' =>
                        td_global::$http_or_https
                        . '://img.youtube.com/vi/'
                        . $video_id
                        . '/sddefault.jpg',

                    'title' =>
                        isset($video_item['snippet']['title'])
                            ? $video_item['snippet']['title']
                            : '',

                    'time' =>
                        $duration,

                    'embedHtml' =>
                        isset($video_item['player']['embedHtml'])
                            ? $video_item['player']['embedHtml']
                            : '',

                    'timestamp' =>
                        time(),
                );

            } catch (Exception $e) {

                /*
                 * Bevar tagDivs oprindelige adfærd:
                 * én dårlig video må ikke stoppe resten.
                 */
            }
        }

        if (empty($buffy_videos)) {
            return false;
        }

        /*
         * Et gyldigt resultat caches i 6 timer.
         *
         * Titel/embed/duration ændrer sig sjældent,
         * mens 6 timer stadig er kort nok til at opdateringer
         * slår igennem samme dag.
         */
        set_transient(
            $cache_key,
            $buffy_videos,
            6 * HOUR_IN_SECONDS
        );

        return $buffy_videos;
	}
/* NAVISEN_PATCH_END */
