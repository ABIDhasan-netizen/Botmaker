<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$user = requireLogin();

$stmt = db()->prepare('SELECT status, COUNT(*) c FROM bots WHERE user_id = ? GROUP BY status');
$stmt->execute([$user['id']]);
$counts = ['running' => 0, 'stopped' => 0];
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $counts[$row['status']] = (int) $row['c'];
}
$total = $counts['running'] + $counts['stopped'];
?>
<!DOCTYPE html>
<html lang="bn">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Dashboard - <?= h(APP_NAME) ?></title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="topbar">
  <div class="logo">B</div>
  <div class="title"><?= h(APP_NAME) ?></div>
  <nav>
    <a href="bots.php">My Bots</a>
    <a href="create_bot.php">+ Create Bot</a>
    <a href="logout.php">Logout</a>
  </nav>
</div>

<div class="container">
  <span class="eyebrow">📊 Overview</span>
  <h1>Dashboard Overview</h1>
  <p class="subtitle">Manage your Telegram bots and runtime status.</p>

  <div class="card stat-card">
    <div class="stat-icon">🤖</div>
    <div>
      <div class="stat-value"><?= (int) $total ?></div>
      <div class="stat-label">Total Bots</div>
    </div>
  </div>

  <div class="card stat-card">
    <div class="stat-icon">▶️</div>
    <div>
      <div class="stat-value"><?= (int) $counts['running'] ?></div>
      <div class="stat-label">Running</div>
    </div>
  </div>

  <div class="card stat-card">
    <div class="stat-icon">⏹️</div>
    <div>
      <div class="stat-value"><?= (int) $counts['stopped'] ?></div>
      <div class="stat-label">Stopped</div>
    </div>
  </div>

  <a href="create_bot.php" class="btn">➕ Create Bot</a>
</div>
</body>
</html>
