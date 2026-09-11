<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$user = requireLogin();

$botId = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$bot = getBotForUser($botId, $user['id']);

if ($bot === null) {
    header('Location: bots.php');
    exit;
}

$codeFile = botCodeFile($botId);
$error = null;
$saved = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = (string) ($_POST['code'] ?? '');

    if (strlen($code) > MAX_CODE_FILE_SIZE) {
        $error = 'ফাইল সাইজ সীমার (512KB) বেশি।';
    } elseif (stripos($code, '<?php') === false) {
        $error = 'ফাইল অবশ্যই <?php দিয়ে শুরু হতে হবে।';
    } else {
        @mkdir(botDir($botId), 0775, true);
        file_put_contents($codeFile, $code);
        addLog($botId, 'info', 'Saved bot.php (' . strlen($code) . ' bytes). Active immediately on next incoming message.');
        $saved = true;
    }
}

$currentCode = file_exists($codeFile) ? file_get_contents($codeFile) : defaultBotTemplate();
?>
<!DOCTYPE html>
<html lang="bn">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>File Manager - <?= h(APP_NAME) ?></title>
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

  <span class="eyebrow">📁 File Manager</span>
  <h1><?= h($bot['name']) ?></h1>
  <p class="subtitle">Edit your PHP code. Main file: <b>bot.php</b></p>

  <?php if ($saved): ?>
    <div class="alert success">✅ File updated: bot.php</div>
  <?php endif; ?>
  <?php if ($error): ?>
    <div class="alert error"><?= h($error) ?></div>
  <?php endif; ?>

  <div class="card">
    <form method="post">
      <input type="hidden" name="id" value="<?= $botId ?>">
      <label>bot.php</label>
      <textarea name="code" class="code-editor" spellcheck="false"><?= h($currentCode) ?></textarea>
      <div style="margin-top:16px;">
        <button class="btn" type="submit">💾 Save Changes</button>
      </div>
    </form>
  </div>

  <div class="card">
    <small class="hint">
      💡 এই ফাইলে <code>BOT_TOKEN</code> এবং <code>BOT_USERNAME</code> constant হিসেবে আগে থেকেই ডিফাইন করা থাকবে,
      এবং Telegram এর incoming update <code>$update</code> array হিসেবে পাওয়া যাবে — ঠিক আপনার আগের bot.php ফাইলের মতোই।
      সিকিউরিটির জন্য <code>exec, shell_exec, system, proc_open</code> ইত্যাদি ফাংশন সার্ভারে বন্ধ রাখা আছে।
    </small>
  </div>
</div>
</body>
</html>
