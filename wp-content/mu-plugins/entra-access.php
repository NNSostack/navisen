<?php
/**
 * Plugin Name: RUC Entra Access Control
 * Description: Adgangskontrol, Entra-grupper, WordPress-roller og debug.
 * Version: 1.0
 */

if (!defined('ABSPATH')) {
    exit;
}


/**
 * ============================================================
 * INDSTILLINGER
 * ============================================================
 */

/**
 * E-mailadresser der MÅ få adgang.
 */
function ruc_entra_allowed_emails() {
    return [
        'nicenew-ns@ruc.dk',
        'makaeb@ruc.dk'
    ];
}


/**
 * Side brugere uden adgang skal sendes til.
 */
function ruc_entra_denied_url() {
    return home_url('/ingen-adgang/');
}


/**
 * Mapping mellem Entra Group Object ID og WordPress-rolle.
 *
 * Rækkefølgen bestemmer prioriteten.
 *
 * Eksempel:
 *
 * 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx' => 'administrator',
 * 'yyyyyyyy-yyyy-yyyy-yyyy-yyyyyyyyyyyy' => 'editor',
 */
function ruc_entra_group_role_map() {
    return [

        // Entra admin-gruppe
        // 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx' => 'administrator',

        // Entra editor-gruppe
        '41a5a5ca-a170-463d-8fbc-67a75f97ba21' => 'contributor',

        // Entra subscriber-gruppe
        // 'zzzzzzzz-zzzz-zzzz-zzzz-zzzzzzzzzzzz' => 'subscriber',

    ];
}


/**
 * Standardrolle hvis ingen Entra-gruppe matcher.
 *
 * null = behold nuværende rolle.
 */
function ruc_entra_default_role() {
    return null;

    // Eksempel:
    // return 'subscriber';
}


/**
 * Debug til wp-content/debug.log.
 */
function ruc_entra_debug_enabled() {
    return true;
}


/**
 * ============================================================
 * DEBUG LOG
 * ============================================================
 */

function ruc_entra_debug($message, $data = null) {

    if (!ruc_entra_debug_enabled()) {
        return;
    }

    if ($data !== null) {
        $message .= ' ' . print_r($data, true);
    }

    error_log('[RUC ENTRA] ' . $message);
}


/**
 * ============================================================
 * PHP SESSION
 * ============================================================
 */

add_action('plugins_loaded', function () {

    if (!session_id() && !headers_sent()) {
        session_start();
    }

}, 1);


/**
 * ============================================================
 * OPFANG MICROSOFT TOKEN RESPONSE
 * ============================================================
 *
 * Microsoft-pluginet kalder:
 *
 * https://login.microsoftonline.com/.../oauth2/v2.0/token
 *
 * Vi lytter på svaret og gemmer access token midlertidigt
 * i PHP-sessionen.
 *
 * Authorization code bliver IKKE brugt igen.
 */
add_filter('http_response', function ($response, $parsed_args, $url) {

    if (
        strpos($url, 'login.microsoftonline.com/') === false ||
        strpos($url, '/oauth2/v2.0/token') === false
    ) {
        return $response;
    }

    if (is_wp_error($response)) {
        return $response;
    }

    $body = wp_remote_retrieve_body($response);

    if (!$body) {
        return $response;
    }

    $data = json_decode($body, true);

    if (!is_array($data)) {
        return $response;
    }

    if (empty($data['access_token'])) {
        return $response;
    }

    $_SESSION['ruc_entra_access_token'] = $data['access_token'];

    /**
     * Gem også id_token midlertidigt hvis Microsoft sender det.
     *
     * Vi bruger det kun til debug af claims.
     */
    if (!empty($data['id_token'])) {
        $_SESSION['ruc_entra_id_token'] = $data['id_token'];
    }

    ruc_entra_debug(
        'Microsoft token response opfanget.',
        [
            'keys' => array_keys($data),
        ]
    );

    return $response;

}, 10, 3);


/**
 * ============================================================
 * JWT HJÆLPEFUNKTION
 * ============================================================
 *
 * Bruges KUN til at læse claims fra id_token til debug.
 *
 * Dette verificerer IKKE JWT-signaturen.
 * Tokenet bruges derfor ikke til sikkerhedsbeslutninger her.
 */
function ruc_entra_decode_jwt_payload($jwt) {

    $parts = explode('.', $jwt);

    if (count($parts) !== 3) {
        return [];
    }

    $payload = strtr(
        $parts[1],
        '-_',
        '+/'
    );

    $padding = strlen($payload) % 4;

    if ($padding) {
        $payload .= str_repeat(
            '=',
            4 - $padding
        );
    }

    $decoded = base64_decode(
        $payload,
        true
    );

    if ($decoded === false) {
        return [];
    }

    $data = json_decode(
        $decoded,
        true
    );

    return is_array($data)
        ? $data
        : [];
}


/**
 * ============================================================
 * MICROSOFT GRAPH
 * ============================================================
 */

/**
 * Hent brugerens direkte Entra memberships.
 */
function ruc_entra_get_memberships($access_token) {

    if (!$access_token) {
        return new WP_Error(
            'missing_access_token',
            'Microsoft access token mangler.'
        );
    }

    $response = wp_remote_get(
        'https://graph.microsoft.com/v1.0/me/memberOf?$select=id,displayName',
        [
            'timeout' => 20,

            'headers' => [
                'Accept'        => 'application/json',
                'Authorization' => 'Bearer ' . $access_token,
            ],
        ]
    );

    if (is_wp_error($response)) {
        return $response;
    }

    $status = wp_remote_retrieve_response_code(
        $response
    );

    $body = wp_remote_retrieve_body(
        $response
    );

    $data = json_decode(
        $body,
        true
    );

    if (
        $status < 200 ||
        $status >= 300
    ) {

        ruc_entra_debug(
            'Microsoft Graph memberOf fejlede.',
            [
                'status' => $status,
                'body'   => $data,
            ]
        );

        return new WP_Error(
            'graph_error',
            'Kunne ikke hente Entra memberships.'
        );
    }

    if (
        !isset($data['value']) ||
        !is_array($data['value'])
    ) {
        return [];
    }

    return $data['value'];
}


/**
 * ============================================================
 * WORDPRESS ROLLE FRA ENTRA-GRUPPE
 * ============================================================
 */

function ruc_entra_get_wordpress_role_from_memberships($memberships) {

    $mapping = ruc_entra_group_role_map();

    foreach ($mapping as $entra_group_id => $wordpress_role) {

        foreach ($memberships as $membership) {

            $membership_id =
                $membership['id'] ?? '';

            if (
                $membership_id &&
                strtolower($membership_id)
                    === strtolower($entra_group_id)
            ) {
                return $wordpress_role;
            }
        }
    }

    return ruc_entra_default_role();
}


/**
 * ============================================================
 * NÅR MICROSOFT-PLUGINET SÆTTER LOGIN COOKIE
 * ============================================================
 *
 * Microsoft-pluginet bruger wp_set_auth_cookie().
 *
 * Vi bruger derfor WordPress-hooket:
 *
 * set_logged_in_cookie
 */
add_action(
    'set_logged_in_cookie',
    function (
        $logged_in_cookie,
        $expire,
        $expiration,
        $user_id,
        $scheme,
        $token
    ) {

        /**
         * Hvis vi ikke har et Entra access token,
         * er dette sandsynligvis ikke et Microsoft-login.
         */
        if (
            empty(
                $_SESSION['ruc_entra_access_token']
            )
        ) {
            return;
        }

        $access_token =
            $_SESSION['ruc_entra_access_token'];

        unset(
            $_SESSION['ruc_entra_access_token']
        );


        /**
         * ID token claims til debug.
         */
        $id_token_claims = [];

        if (
            !empty(
                $_SESSION['ruc_entra_id_token']
            )
        ) {

            $id_token_claims =
                ruc_entra_decode_jwt_payload(
                    $_SESSION['ruc_entra_id_token']
                );

            unset(
                $_SESSION['ruc_entra_id_token']
            );
        }


        $user = get_userdata(
            $user_id
        );

        if (!$user) {
            return;
        }


        ruc_entra_debug(
            'Entra login registreret.',
            [
                'user_id' => $user_id,
                'email'   => $user->user_email,
            ]
        );


        /**
         * Hent Entra memberships.
         */
        $memberships =
            ruc_entra_get_memberships(
                $access_token
            );


        if (is_wp_error($memberships)) {

            update_user_meta(
                $user_id,
                '_ruc_entra_memberships_error',
                $memberships->get_error_message()
            );

            ruc_entra_debug(
                'Memberships kunne ikke hentes.',
                $memberships->get_error_message()
            );

            return;
        }


        /**
         * Gem memberships på WordPress-brugeren.
         */
        update_user_meta(
            $user_id,
            '_ruc_entra_memberships',
            $memberships
        );


        update_user_meta(
            $user_id,
            '_ruc_entra_memberships_updated',
            current_time('mysql')
        );


        delete_user_meta(
            $user_id,
            '_ruc_entra_memberships_error'
        );


        /**
         * Gem ID token claims til debug.
         *
         * Selve tokenet gemmes IKKE.
         */
        if (!empty($id_token_claims)) {

            update_user_meta(
                $user_id,
                '_ruc_entra_id_token_claims',
                $id_token_claims
            );
        }


        ruc_entra_debug(
            'Entra memberships:',
            $memberships
        );


        /**
         * Find eventuel WordPress-rolle.
         */
        $wordpress_role =
            ruc_entra_get_wordpress_role_from_memberships(
                $memberships
            );


        if (!$wordpress_role) {

            ruc_entra_debug(
                'Ingen Entra-gruppe matchede en WordPress-rolle.'
            );

            return;
        }


        /**
         * Kontrollér at rollen findes.
         */
        $roles = wp_roles()->roles;

        if (
            !isset(
                $roles[$wordpress_role]
            )
        ) {

            ruc_entra_debug(
                'Ukendt WordPress-rolle: '
                . $wordpress_role
            );

            return;
        }


        /**
         * Sæt WordPress-rollen.
         */
        $user->set_role(
            $wordpress_role
        );


        ruc_entra_debug(
            'WordPress-rolle sat.',
            [
                'email' =>
                    $user->user_email,

                'role' =>
                    $wordpress_role,
            ]
        );

    },
    10,
    6
);


/**
 * ============================================================
 * POSITIVLISTE
 * ============================================================
 */

function ruc_entra_user_is_allowed($user = null) {

    if (!$user) {
        $user = wp_get_current_user();
    }

    if (!($user instanceof WP_User)) {
        return false;
    }

    if (!$user->exists()) {
        return false;
    }


    /**
     * 1. Adgang via e-mail positivliste.
     */
    $email = strtolower(
        trim($user->user_email)
    );

    $allowed_emails = array_map(
        function ($email) {

            return strtolower(
                trim($email)
            );
        },
        ruc_entra_allowed_emails()
    );

    if (
        $email &&
        in_array(
            $email,
            $allowed_emails,
            true
        )
    ) {
        return true;
    }


    /**
     * 2. Adgang via Entra Group Object ID (GUID).
     *
     * Alle GUID'er der findes som nøgler i
     * ruc_entra_group_role_map() giver adgang.
     */
    $memberships = get_user_meta(
        $user->ID,
        '_ruc_entra_memberships',
        true
    );

    if (
        !empty($memberships) &&
        is_array($memberships)
    ) {

        $allowed_group_guids = array_map(
            'strtolower',
            array_keys(
                ruc_entra_group_role_map()
            )
        );

        foreach ($memberships as $membership) {

            $membership_id = strtolower(
                trim(
                    $membership['id'] ?? ''
                )
            );

            if (
                $membership_id &&
                in_array(
                    $membership_id,
                    $allowed_group_guids,
                    true
                )
            ) {
                return true;
            }
        }
    }


    /**
     * Hverken e-mail eller Entra GUID gav adgang.
     */
    return false;
}


/**
 * ============================================================
 * MICROSOFT CALLBACK
 * ============================================================
 */

function ruc_entra_is_oauth_callback() {

    return (
        isset($_GET['code']) &&
        isset($_GET['state'])
    );
}


/**
 * ============================================================
 * LOG UD OG SEND TIL INGEN-ADGANG
 * ============================================================
 */

function ruc_entra_logout_and_deny() {

    wp_logout();

    wp_safe_redirect(
        ruc_entra_denied_url()
    );

    exit;
}


/**
 * ============================================================
 * TEST / DEBUG SIDE
 * ============================================================
 *
 * Brug:
 *
 * https://wp2-test.ruc.dk/?test=1
 *
 * Kræver at brugeren er logget ind.
 */
add_action(
    'template_redirect',
    function () {

        if (
            !isset($_GET['test']) ||
            sanitize_text_field(
                wp_unslash($_GET['test'])
            ) !== '1'
        ) {
            return;
        }


        /**
         * Test-siden virker kun for
         * en indlogget WordPress-bruger.
         */
        if (!is_user_logged_in()) {

            wp_die(
                'Du skal være logget ind for at se Entra debug.'
            );
        }


        $user = wp_get_current_user();


        $memberships = get_user_meta(
            $user->ID,
            '_ruc_entra_memberships',
            true
        );


        $updated = get_user_meta(
            $user->ID,
            '_ruc_entra_memberships_updated',
            true
        );


        $error = get_user_meta(
            $user->ID,
            '_ruc_entra_memberships_error',
            true
        );


        $id_token_claims = get_user_meta(
            $user->ID,
            '_ruc_entra_id_token_claims',
            true
        );


        nocache_headers();

        header(
            'Content-Type: text/html; charset=utf-8'
        );


        echo '<!DOCTYPE html>';

        echo '<html lang="da">';

        echo '<head>';

        echo '<meta charset="utf-8">';

        echo '<meta name="viewport" content="width=device-width, initial-scale=1">';

        echo '<title>Microsoft Entra Debug</title>';


        echo '<style>

            body {
                font-family:
                    -apple-system,
                    BlinkMacSystemFont,
                    "Segoe UI",
                    Arial,
                    sans-serif;

                margin: 0;
                padding: 40px;

                background: #f4f5f7;
                color: #222;
            }

            .container {
                max-width: 1300px;
                margin: 0 auto;
            }

            .box {
                background: white;
                padding: 25px;
                margin-bottom: 25px;
                border-radius: 8px;
                border: 1px solid #ddd;
            }

            h1 {
                margin-top: 0;
            }

            h2 {
                margin-top: 0;
            }

            table {
                width: 100%;
                border-collapse: collapse;
            }

            th,
            td {
                text-align: left;
                vertical-align: top;
                padding: 10px;
                border-bottom: 1px solid #ddd;
            }

            th {
                background: #f5f5f5;
            }

            code {
                font-family: monospace;
            }

            pre {
                background: #111;
                color: #eee;

                padding: 20px;

                overflow: auto;

                white-space: pre-wrap;
                word-break: break-word;

                border-radius: 5px;
            }

            .error {
                background: #ffe7e7;
                border: 1px solid #dd7777;
                padding: 15px;
            }

            .success {
                background: #eaf8ea;
                border: 1px solid #8bb98b;
                padding: 15px;
            }

        </style>';


        echo '</head>';

        echo '<body>';

        echo '<div class="container">';


        /**
         * Overskrift
         */
        echo '<div class="box">';

        echo '<h1>Microsoft Entra Debug</h1>';

        echo '<p>';

        echo 'Denne side vises kun fordi URL\'en indeholder ';

        echo '<code>?test=1</code>.';

        echo '</p>';

        echo '</div>';


        /**
         * WordPress bruger
         */
        echo '<div class="box">';

        echo '<h2>WordPress bruger</h2>';

        echo '<table>';

        echo '<tr>';

        echo '<th>ID</th>';

        echo '<td>'
            . esc_html($user->ID)
            . '</td>';

        echo '</tr>';


        echo '<tr>';

        echo '<th>Login</th>';

        echo '<td>'
            . esc_html(
                $user->user_login
            )
            . '</td>';

        echo '</tr>';


        echo '<tr>';

        echo '<th>Email</th>';

        echo '<td>'
            . esc_html(
                $user->user_email
            )
            . '</td>';

        echo '</tr>';


        echo '<tr>';

        echo '<th>Navn</th>';

        echo '<td>'
            . esc_html(
                $user->display_name
            )
            . '</td>';

        echo '</tr>';


        echo '<tr>';

        echo '<th>WordPress roller</th>';

        echo '<td>'
            . esc_html(
                implode(
                    ', ',
                    $user->roles
                )
            )
            . '</td>';

        echo '</tr>';


        echo '</table>';

        echo '</div>';


        /**
         * Status
         */
        echo '<div class="box">';

        echo '<h2>Status</h2>';


        if ($updated) {

            echo '<div class="success">';

            echo 'Entra-data blev senest hentet: ';

            echo '<strong>'
                . esc_html($updated)
                . '</strong>';

            echo '</div>';
        }


        if ($error) {

            echo '<div class="error">';

            echo '<strong>Microsoft Graph fejl:</strong><br>';

            echo esc_html($error);

            echo '</div>';
        }


        if (
            !$updated &&
            !$error
        ) {

            echo '<div class="error">';

            echo 'Der er endnu ikke gemt Entra-data for denne bruger. ';

            echo 'Log ud og log ind igen via Microsoft.';

            echo '</div>';
        }


        echo '</div>';


        /**
         * Memberships tabel
         */
        echo '<div class="box">';

        echo '<h2>Entra memberships</h2>';


        if (
            empty($memberships) ||
            !is_array($memberships)
        ) {

            echo '<p>';

            echo 'Ingen memberships fundet.';

            echo '</p>';

        } else {

            echo '<table>';

            echo '<thead>';

            echo '<tr>';

            echo '<th>Type</th>';

            echo '<th>Display name</th>';

            echo '<th>Object ID</th>';

            echo '</tr>';

            echo '</thead>';


            echo '<tbody>';


            foreach (
                $memberships
                as $membership
            ) {

                echo '<tr>';


                echo '<td>';

                echo esc_html(
                    $membership['@odata.type']
                        ?? ''
                );

                echo '</td>';


                echo '<td>';

                echo '<strong>';

                echo esc_html(
                    $membership['displayName']
                        ?? ''
                );

                echo '</strong>';

                echo '</td>';


                echo '<td>';

                echo '<code>';

                echo esc_html(
                    $membership['id']
                        ?? ''
                );

                echo '</code>';

                echo '</td>';


                echo '</tr>';
            }


            echo '</tbody>';

            echo '</table>';
        }


        echo '</div>';


        /**
         * ID token claims
         */
        echo '<div class="box">';

        echo '<h2>ID token claims</h2>';


        if (
            empty($id_token_claims) ||
            !is_array($id_token_claims)
        ) {

            echo '<p>';

            echo 'Der blev ikke gemt nogen ID token claims.';

            echo '</p>';

        } else {

            /**
             * Vis roller ekstra tydeligt hvis
             * Microsoft har sendt roles claim.
             */
            if (
                !empty(
                    $id_token_claims['roles']
                )
            ) {

                echo '<h3>Roles claim</h3>';

                echo '<pre>';

                echo esc_html(
                    print_r(
                        $id_token_claims['roles'],
                        true
                    )
                );

                echo '</pre>';
            }


            /**
             * Groups claim
             */
            if (
                !empty(
                    $id_token_claims['groups']
                )
            ) {

                echo '<h3>Groups claim</h3>';

                echo '<pre>';

                echo esc_html(
                    print_r(
                        $id_token_claims['groups'],
                        true
                    )
                );

                echo '</pre>';
            }


            echo '<h3>Alle claims</h3>';

            echo '<pre>';

            echo esc_html(
                print_r(
                    $id_token_claims,
                    true
                )
            );

            echo '</pre>';
        }


        echo '</div>';


        /**
         * RAW memberships
         */
        echo '<div class="box">';

        echo '<h2>RAW memberships</h2>';

        echo '<pre>';

        echo esc_html(
            print_r(
                $memberships,
                true
            )
        );

        echo '</pre>';

        echo '</div>';


        echo '</div>';

        echo '</body>';

        echo '</html>';

        exit;
    },
    1
);


/**
 * ============================================================
 * FRONTEND ADGANGSKONTROL
 * ============================================================
 */

add_action(
    'template_redirect',
    function () {

        /**
         * Gør ingenting hvis brugeren ikke
         * er logget ind.
         */
        if (!is_user_logged_in()) {
            return;
        }


        /**
         * Rør ikke Microsoft callback.
         */
        if (ruc_entra_is_oauth_callback()) {
            return;
        }


        /**
         * Godkendt bruger.
         */
        if (ruc_entra_user_is_allowed()) {
            return;
        }


        /**
         * Ikke godkendt.
         */
        ruc_entra_logout_and_deny();
    },
    20
);


/**
 * ============================================================
 * WORDPRESS BACKEND
 * ============================================================
 */

add_action(
    'admin_init',
    function () {

        if (!is_user_logged_in()) {
            return;
        }

        if (wp_doing_ajax()) {
            return;
        }

        if (
            ruc_entra_is_oauth_callback()
        ) {
            return;
        }

        if (
            ruc_entra_user_is_allowed()
        ) {
            return;
        }

        ruc_entra_logout_and_deny();
    }
);


/**
 * ============================================================
 * NORMALT WORDPRESS LOGOUT
 * ============================================================
 *
 * Hvis en godkendt bruger selv vælger Log ud,
 * sendes vedkommende til forsiden.
 */
add_filter(
    'logout_redirect',
    function (
        $redirect_to,
        $requested_redirect_to,
        $user
    ) {

        return home_url('/');
    },
    10,
    3
);