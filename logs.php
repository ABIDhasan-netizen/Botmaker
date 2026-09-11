<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$user = requireLogin();

$botId = (int) ($_GET['id'] ?? 0);
$bot = getBotForUser($botId, $user['id']);

if ($bot === null) {
    header('Location: bots.php');
    exit;
}

$logs = getLogs($botId, 100);
?>
<!DOCTYPE html>
<html lang="bn">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Terminal Logs - <?= h(APP_NAME) ?></title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="topbar">
  <div class="logo">B</div>
  <div class="title"><?= h(APP_NAME) ?></div>
  <nav><a href="logout.php">Logout</a></nav>
</div>

<div class="container">
  <a href="bot.php?id=<?= $botId ?>" class="btn secondary" style="width:auto;display:inline-flex;padding:10px 18px;margin-bottom:16px;">← Back</a>

  <span class="eyebrow">📜 Terminal Logs</span>
  <h1><?= h($bot['name']) ?></h1>
  <p class="subtitle">Live webhook and execution trace.</p>

  <a href="logs.php?id=<?= $botId ?>" class="btn secondary" style="width:auto;display:inline-flex;padding:10px 18px;margin-bottom:16px;">🔄 Refresh</a>

  <div class="log-box">
    <?php if (empty($logs)): ?>
      No logs yet. Send a message to your bot on Telegram to generate activity.
    <?php else: ?>
      <?php foreach ($logs as $log): ?>
<span class="ts">[<?= h(substr($log['created_at'], 11, 8)) ?>]</span> <span class="lvl-<?= h($log['level']) ?>">[<?= h($log['level']) ?>]</span> <?= h($log['message']) ?>

      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
