<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/telegram_api.php';

$user = requireLogin();

$botId = (int) ($_POST['id'] ?? 0);
$action = $_POST['action'] ?? '';
$bot = getBotForUser($botId, $user['id']);

if ($bot === null) {
    header('Location: bots.php');
    exit;
}

switch ($action) {
    case 'start':
    case 'deploy':
    case 'restart':
        $webhookUrl = APP_URL . '/webhook.php?secret=' . $bot['webhook_secret'];
        $set = tgSetWebhook($bot['token'], $webhookUrl);
        if ($set['ok']) {
            db()->prepare('UPDATE bots SET status = ? WHERE id = ?')->execute(['running', $botId]);
            addLog($botId, 'info', ucfirst($action) . ' successful. Webhook is live.');
            flash('success', 'Bot is now running.');
        } else {
            addLog($botId, 'error', ucfirst($action) . ' failed: ' . $set['error']);
            flash('success', 'Action failed: ' . $set['error']);
        }
        break;

    case 'stop':
        tgDeleteWebhook($bot['token']);
        db()->prepare('UPDATE bots SET status = ? WHERE id = ?')->execute(['stopped', $botId]);
        addLog($botId, 'info', 'Bot stopped. Webhook removed.');
        flash('success', 'Bot stopped.');
        break;

    default:
        // no-op
        break;
}

header('Location: bot.php?id=' . $botId);
exit;
