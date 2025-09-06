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
Util::connectHook('OC_User', 'post_login', function(array $params) use ($sendGotify, $gotifyBaseUrl, $gotifyToken, $priorityOk, $info) {
    $user = $params['uid'] ?? '(unknown)';
    $info('post_login fired', ['uid' => $user]);
    $sendGotify($gotifyBaseUrl, $gotifyToken, $priorityOk, 'Nextcloud Login ✅', "Connexion réussie de l’utilisateur **{$user}**");
}, null);

Util::connectHook('OC_User', 'failed_login', function(array $params) use ($sendGotify, $gotifyBaseUrl, $gotifyToken, $priorityFail, $info) {
    $user = $params['uid'] ?? '(unknown)';
    $ip   = $params['ip'] ?? ($_SERVER['REMOTE_ADDR'] ?? 'unknown-ip');
    $info('failed_login fired', ['uid' => $user, 'ip' => $ip]);
    $sendGotify($gotifyBaseUrl, $gotifyToken, $priorityFail, 'Nextcloud Login ❌', "⚠️ Tentative échouée pour l’utilisateur **{$user}** depuis {$ip}");
}, null);
