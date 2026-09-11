<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/telegram_api.php';

$user = requireLogin();

$botId = (int) ($_POST['id'] ?? 0);
$bot = getBotForUser($botId, $user['id']);

if ($bot !== null) {
    tgDeleteWebhook($bot['token']);

    db()->prepare('DELETE FROM bots WHERE id = ?')->execute([$botId]);
    // logs are removed automatically via ON DELETE CASCADE

    $dir = botDir($botId);
    if (is_dir($dir)) {
        array_map('unlink', glob($dir . '/*'));
        rmdir($dir);
    }
}

header('Location: bots.php');
exit;
