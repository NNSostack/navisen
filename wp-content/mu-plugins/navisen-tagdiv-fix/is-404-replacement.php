<?php

/*
 * Denne fil bliver IKKE kørt af WordPress.
 *
 * Navisen TagDiv MU-pluginet læser filen som almindelig tekst
 * og indsætter koden mellem NAVISEN_PATCH_START og
 * NAVISEN_PATCH_END i:
 *
 * td-composer/legacy/common/wp_booster/td_video_support.php
 */


/* NAVISEN_PATCH_START */
    private static function is_404($url) {

        /*
         * Cache-key baseret på den komplette thumbnail-URL.
         *
         * Vi cacher kun resultatet af spørgsmålet:
         *
         *     "Giver denne URL 404?"
         *
         * Selve thumbnail-billedet caches IKKE her.
         */
        $cache_key = 'navisen_td_head_' . md5($url);

        $cached = get_transient($cache_key);

        if ($cached !== false) {

            if ($cached === '404') {
                return true;
            }

            if ($cached === 'ok') {
                return false;
            }
        }


        /*
         * Lav et HEAD request i stedet for PHP get_headers().
         *
         * Timeout holdes meget lav, så en langsom ekstern
         * server ikke holder en PHP-FPM worker optaget.
         */
        $response = wp_remote_head(
            $url,
            array(
                'timeout'     => 1,
                'redirection' => 2,
            )
        );


        /*
         * Netværksfejl / timeout.
         *
         * Vi cacher IKKE fejlen.
         *
         * Det sikrer, at en midlertidig fejl hos YouTube ikke
         * bliver husket som en permanent 404.
         */
        if (is_wp_error($response)) {
            return true;
        }


        $status_code =
            wp_remote_retrieve_response_code($response);


        /*
         * Rigtig 404.
         *
         * Cache i én time.
         *
         * Vi bruger en kortere cachetid for 404, fordi en
         * højopløst YouTube-thumbnail godt kan dukke op senere.
         */
        if ($status_code === 404) {

            set_transient(
                $cache_key,
                '404',
                HOUR_IN_SECONDS
            );

            return true;
        }


        /*
         * URL'en findes.
         *
         * Cache resultatet i 24 timer.
         */
        if (
            $status_code >= 200 &&
            $status_code < 400
        ) {

            set_transient(
                $cache_key,
                'ok',
                DAY_IN_SECONDS
            );

            return false;
        }


        /*
         * Fx:
         *
         * 403
         * 429
         * 500
         * 503
         *
         * Disse caches ikke, fordi de kan være midlertidige.
         *
         * Samme grundlæggende adfærd som den gamle tagDiv-kode:
         * kun en rigtig 404 betragtes som "findes ikke".
         */
        return false;
    }
/* NAVISEN_PATCH_END */