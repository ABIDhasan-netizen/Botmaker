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

$success = flash('success');
$codeFile = botCodeFile($botId);
$fileSize = file_exists($codeFile) ? filesize($codeFile) : 0;
$logCount = (int) db()->query('SELECT COUNT(*) FROM logs WHERE bot_id = ' . $botId)->fetchColumn();
?>
<!DOCTYPE html>
<html lang="bn">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= h($bot['name']) ?> - <?= h(APP_NAME) ?></title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="topbar">
  <div class="logo">B</div>
  <div class="title"><?= h(APP_NAME) ?></div>
  <nav>
    <a href="bots.php">My Bots</a>
    <a href="logout.php">Logout</a>
  </nav>
</div>

<div class="container">
  <a href="bots.php" class="btn secondary" style="width:auto;display:inline-flex;padding:10px 18px;margin-bottom:16px;">← Back</a>

  <?php if ($success): ?>
    <div class="alert success"><?= h($success) ?></div>
  <?php endif; ?>

  <div class="bot-hero">
    <h2><?= h($bot['name']) ?></h2>
    <span class="badge <?= $bot['status'] === 'running' ? 'running' : 'pill' ?>">
      <?= $bot['status'] === 'running' ? '● Running' : '■ Stopped' ?>
    </span>
    <span class="badge pill">&lt;/&gt; PHP</span>
    <span class="badge pill"><?= h($bot['category'] ?: 'General') ?></span>
  </div>

  <div class="card">
    <b>Workspace</b>
    <p style="color:var(--muted);font-size:13px;margin-top:4px;">
      1 files · <?= $logCount ?> logs
    </p>

    <a href="file_edit.php?id=<?= $botId ?>" class="row-link">
      <div class="left">
        <div class="icon">📁</div>
        <div class="meta"><b>File Manager</b><small>Edit bot.php</small></div>
      </div>
      <span>›</span>
    </a>

    <a href="logs.php?id=<?= $botId ?>" class="row-link">
      <div class="left">
        <div class="icon">📜</div>
        <div class="meta"><b>Terminal Logs</b><small>View runtime events</small></div>
      </div>
      <span>›</span>
    </a>
  </div>

  <div class="card">
    <b>Bot Controls</b>
    <div class="grid-2" style="margin-top:12px;">
      <form method="post" action="bot_action.php">
        <input type="hidden" name="id" value="<?= $botId ?>">
        <input type="hidden" name="action" value="deploy">
        <button class="btn secondary" type="submit">☁️ Deploy</button>
      </form>
      <form method="post" action="bot_action.php">
        <input type="hidden" name="id" value="<?= $botId ?>">
        <input type="hidden" name="action" value="start">
        <button class="btn secondary" type="submit">▶️ Start</button>
      </form>
      <form method="post" action="bot_action.php">
        <input type="hidden" name="id" value="<?= $botId ?>">
        <input type="hidden" name="action" value="restart">
        <button class="btn secondary" type="submit">🔄 Restart</button>
      </form>
      <form method="post" action="bot_action.php">
        <input type="hidden" name="id" value="<?= $botId ?>">
        <input type="hidden" name="action" value="stop">
        <button class="btn secondary" type="submit">⏹ Stop</button>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="stat-label">Files</div>
    <div class="stat-value" style="font-size:20px;">1 (bot.php, <?= number_format($fileSize / 1024, 2) ?> KB)</div>
  </div>

  <div class="card">
    <div class="stat-label">Username</div>
    <div class="stat-value" style="font-size:20px;">@<?= h($bot['username']) ?></div>
  </div>

  <div class="card">
    <b>⚠️ Bot management</b>
    <form method="post" action="delete_bot.php" onsubmit="return confirm('এই বট এবং এর সব কোড/লগ স্থায়ীভাবে মুছে যাবে। আপনি নিশ্চিত?');" style="margin-top:12px;">
      <input type="hidden" name="id" value="<?= $botId ?>">
      <button class="btn danger" type="submit">🗑 Delete Bot</button>
    </form>
  </div>
</div>
</body>
</html>
