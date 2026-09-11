<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/telegram_api.php';

$user = requireLogin();

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $token = trim($_POST['token'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($name === '' || $token === '') {
        $error = 'Bot Name এবং Bot Token দুটোই দিতে হবে।';
    } else {
        // Step 1: verify token is real and belongs to a real Telegram bot
        $verify = tgVerifyToken($token);

        if (!$verify['ok']) {
            $error = $verify['error'];
        } else {
            // Step 2: prevent the same token being added twice
            $dupe = db()->prepare('SELECT id FROM bots WHERE token = ?');
            $dupe->execute([$token]);
            if ($dupe->fetch()) {
                $error = 'এই bot token দিয়ে ইতিমধ্যে একটা বট এই প্যানেলে আছে।';
            } else {
                $webhookSecret = randomSecret();

                $stmt = db()->prepare('
                    INSERT INTO bots (user_id, name, username, token, webhook_secret, status, category, description)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ');
                $stmt->execute([
                    $user['id'],
                    $name,
                    $verify['username'] ?? 'unknown',
                    $token,
                    $webhookSecret,
                    'stopped',
                    $category ?: 'General',
                    $description,
                ]);
                $botId = (int) db()->lastInsertId();

                // Step 3: write a starter bot.php for this bot
                @mkdir(botDir($botId), 0775, true);
                file_put_contents(botCodeFile($botId), defaultBotTemplate());

                // Step 4: point Telegram's webhook at our panel and mark running
                $webhookUrl = APP_URL . '/webhook.php?secret=' . $webhookSecret;
                $set = tgSetWebhook($token, $webhookUrl);

                if ($set['ok']) {
                    db()->prepare('UPDATE bots SET status = ? WHERE id = ?')->execute(['running', $botId]);
                    addLog($botId, 'info', 'Bot added and webhook set successfully. Telegram verified username as @' . $verify['username']);
                    flash('success', 'Bot added and started successfully. Send /start in Telegram to test it.');
                } else {
                    addLog($botId, 'error', 'Bot created but webhook could not be set: ' . $set['error']);
                    flash('success', 'Bot created, but webhook setup failed: ' . $set['error'] . ' (আপনি পরে বট পেজ থেকে আবার Deploy করতে পারবেন)');
                }

                header('Location: bot.php?id=' . $botId);
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Create Bot - <?= h(APP_NAME) ?></title>
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
  <span class="eyebrow">➕ Create Bot</span>
  <h1>Create Bot</h1>
  <p class="subtitle">Add your Telegram bot credentials.</p>

  <a href="bots.php" class="btn secondary" style="width:auto;display:inline-flex;padding:10px 18px;margin-bottom:16px;">← Back</a>

  <?php if ($error): ?>
    <div class="alert error"><?= h($error) ?></div>
  <?php endif; ?>

  <div class="card">
    <form method="post">
      <label>Bot Name</label>
      <input type="text" name="name" placeholder="My Earn Bot" required value="<?= h($_POST['name'] ?? '') ?>">

      <label>Bot Token</label>
      <input type="text" name="token" placeholder="123456:ABC..." required value="<?= h($_POST['token'] ?? '') ?>">
      <small class="hint">@BotFather থেকে পাওয়া টোকেন দিন। প্যানেল Telegram-এ কল করে টোকেন যাচাই করবে এবং সঠিক username বের করে নেবে।</small>

      <label>Category (optional)</label>
      <input type="text" name="category" placeholder="Refer Earn / Utility / Sales" value="<?= h($_POST['category'] ?? '') ?>">

      <label>Description (optional)</label>
      <textarea name="description" placeholder="Short note about this bot..." rows="3"><?= h($_POST['description'] ?? '') ?></textarea>

      <div style="margin-top:20px;">
        <button class="btn" type="submit">➕ Add &amp; Start Bot</button>
      </div>
    </form>
  </div>
</div>
</body>
</html>
