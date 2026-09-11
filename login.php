<?php
require_once __DIR__ . '/../includes/auth.php';

if (currentUser() !== null) {
    header('Location: dashboard.php');
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = loginUser($_POST['email'] ?? '', $_POST['password'] ?? '');
    if ($result['ok']) {
        header('Location: dashboard.php');
        exit;
    }
    $error = $result['error'];
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Log In - <?= h(APP_NAME) ?></title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="topbar">
  <div class="logo">B</div>
  <div class="title"><?= h(APP_NAME) ?></div>
</div>

<div class="container">
  <span class="eyebrow">🔐 Welcome back</span>
  <h1>Log in</h1>
  <p class="subtitle">আপনার bot hosting panel এ প্রবেশ করুন।</p>

  <?php if ($error): ?>
    <div class="alert error"><?= h($error) ?></div>
  <?php endif; ?>

  <div class="card">
    <form method="post">
      <label>Email</label>
      <input type="email" name="email" placeholder="you@example.com" required value="<?= h($_POST['email'] ?? '') ?>">

      <label>Password</label>
      <input type="password" name="password" placeholder="আপনার পাসওয়ার্ড" required>

      <div style="margin-top:20px;">
        <button class="btn" type="submit">Log In</button>
      </div>
    </form>
  </div>

  <p style="text-align:center;color:var(--muted);">
    নতুন এখানে? <a href="register.php" style="color:var(--primary);font-weight:700;">Create an account</a>
  </p>
</div>
</body>
</html>
