<?php
namespace OCA\GotifyLogin;

use OCP\Util;
use OCP\User\Events\PostLoginEvent;
use OCP\EventDispatcher\IEventDispatcher;
use OCP\Authentication\Events\AnyLoginFailedEvent;

$server = \OC::$server;
$dispatcher = $server->get(IEventDispatcher::class);

// Variables Gotify
$gotifyUrl   = getenv('GOTIFY_URL');
$gotifyToken = getenv('GOTIFY_TOKEN');
$priorityOk  = getenv('GOTIFY_PRIORITY_SUCCESS') ?: 5;
$priorityFail= getenv('GOTIFY_PRIORITY_FAIL') ?: 8;

// Fonction d’envoi
$sendGotify = function(string $title, string $message, int $priority) use ($gotifyUrl, $gotifyToken) {
    if (!$gotifyUrl || !$gotifyToken) return;
    $endpoint = rtrim($gotifyUrl, '/') . '/message?token=' . rawurlencode($gotifyToken);
    $payload = json_encode([
        'message'  => $message,
        'title'    => $title,
        'priority' => $priority,
        'extras'   => [
            'client::display' => ['contentType' => 'text/markdown'],
        ],
    ]);
    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);
};

// Hook connexion réussie
$dispatcher->addListener(PostLoginEvent::class, function(PostLoginEvent $event) use ($sendGotify, $priorityOk) {
    $user = $event->getUser()->getUID();
    $sendGotify('Nextcloud Login ✅', "User **$user** logged in successfully", $priorityOk);
});

// Hook connexion échouée
$dispatcher->addListener(AnyLoginFailedEvent::class, function(AnyLoginFailedEvent $event) use ($sendGotify, $priorityFail) {
    $ip   = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $sendGotify('Nextcloud Login ❌', "⚠️ Failed login attempt from $ip", $priorityFail);
});
