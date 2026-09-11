<?php
declare(strict_types=1);

/**
 * Low-level call to the Telegram Bot API.
 */
function tgCall(string $token, string $method, array $params = []): array
{
    $url = 'https://api.telegram.org/bot' . $token . '/' . $method;

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $params,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 20,
    ]);
    $raw = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($raw === false) {
        return ['ok' => false, 'description' => $err ?: 'Network error'];
    }

    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        return ['ok' => false, 'description' => 'Invalid Telegram response'];
    }

    return $decoded;
}

/**
 * Verify a bot token by calling getMe. Returns bot info on success.
 */
function tgVerifyToken(string $token): array
{
    $token = trim($token);

    if ($token === '' || !preg_match('/^\d+:[A-Za-z0-9_-]+$/', $token)) {
        return ['ok' => false, 'error' => 'টোকেনের ফরম্যাট সঠিক না। উদাহরণ: 123456:ABC-DEF...'];
    }

    $res = tgCall($token, 'getMe');

    if (empty($res['ok'])) {
        return ['ok' => false, 'error' => 'Telegram টোকেন যাচাই করতে পারেনি: ' . ($res['description'] ?? 'Unknown error')];
    }

    return [
        'ok' => true,
        'id' => $res['result']['id'] ?? null,
        'username' => $res['result']['username'] ?? null,
        'first_name' => $res['result']['first_name'] ?? null,
    ];
}

/**
 * Point the bot's webhook at our panel.
 */
function tgSetWebhook(string $token, string $webhookUrl): array
{
    $res = tgCall($token, 'setWebhook', ['url' => $webhookUrl]);

    if (empty($res['ok'])) {
        return ['ok' => false, 'error' => $res['description'] ?? 'setWebhook failed'];
    }

    return ['ok' => true];
}

/**
 * Remove the webhook (used when a bot is stopped/deleted).
 */
function tgDeleteWebhook(string $token): array
{
    $res = tgCall($token, 'deleteWebhook');
    return ['ok' => !empty($res['ok'])];
}
