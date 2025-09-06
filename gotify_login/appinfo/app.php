<?php

namespace OCA\GotifyLogin;

use OCP\Util;

// Gotify configuration from environment variables
$gotifyUrl    = getenv('GOTIFY_URL');   // ex: https://gotify.example.com
$gotifyToken  = getenv('GOTIFY_TOKEN');
$priorityOk   = getenv('GOTIFY_PRIORITY_SUCCESS') ?: 2;
$priorityFail = getenv('GOTIFY_PRIORITY_FAIL') ?: 5;

function sendGotify($url, $token, $priority, $title, $message) {
    $endpoint = rtrim($url, '/') . '/message?token=' . $token;

    $payload = json_encode([
        "message"  => $message,
        "title"    => $title,
        "priority" => (int)$priority,
        "extras"   => [
            "client::display" => [
                "contentType" => "text/markdown"
            ]
        ]
    ]);

    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);
}

// successful login
\OCP\Util::connectHook('OC_User', 'post_login', function($params) use ($gotifyUrl, $gotifyToken, $priorityOk) {
    $user = $params['uid'];
    $ip   = $params['ip'];
    sendGotify($gotifyUrl, $gotifyToken, $priorityOk, "Nextcloud Login ✅", "Connexion réussie de l’utilisateur **$user** depuis $ip");
});

// failed login
\OCP\Util::connectHook('OC_User', 'failed_login', function($params) use ($gotifyUrl, $gotifyToken, $priorityFail) {
    $user = $params['uid'];
    $ip   = $params['ip'];
    sendGotify($gotifyUrl, $gotifyToken, $priorityFail, "Nextcloud Login ❌", "⚠️ Tentative échouée pour l’utilisateur **$user** depuis $ip");
});
    