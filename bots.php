<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$user = requireLogin();

$stmt = db()->prepare('SELECT * FROM bots WHERE user_id = ? ORDER BY id DESC');
$stmt->execute([$user['id']]);
$bots = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="bn">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>My Bots - <?= h(APP_NAME) ?></title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="topbar">
  <div class="logo">B</div>
  <div class="title"><?= h(APP_NAME) ?></div>
  <nav>
    <a href="dashboard.php">Dashboard</a>
    <a href="logout.php">Logout</a>
  </nav>
</div>

<div class="container">
  <span class="eyebrow">🤖 My Bots</span>
  <h1>My Bots</h1>
  <p class="subtitle">Manage your bots.</p>

  <a href="create_bot.php" class="btn">➕ Create Bot</a>

  <div style="margin-top:20px;">
  <?php if (empty($bots)): ?>
    <div class="empty-state">
      <div class="icon">🤖</div>
      <p><b>No bots yet</b></p>
      <a href="create_bot.php" class="btn" style="width:auto;display:inline-flex;padding:12px 22px;">+ Create Bot</a>
    </div>
  <?php else: ?>
    <?php foreach ($bots as $bot): ?>
      <a href="bot.php?id=<?= (int) $bot['id'] ?>" class="bot-list-item">
        <div>
          <div class="name"><?= h($bot['name']) ?></div>
          <div class="sub">@<?= h($bot['username']) ?> · PHP</div>
        </div>
        <span class="badge <?= $bot['status'] === 'running' ? 'running' : 'stopped' ?>">
          <?= $bot['status'] === 'running' ? '● Running' : '■ Stopped' ?>
        </span>
      </a>
    <?php endforeach; ?>
  <?php endif; ?>
  </div>
</div>
</body>
</html>
