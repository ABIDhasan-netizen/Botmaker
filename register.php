<?php
require_once __DIR__ . '/../includes/auth.php';

if (currentUser() !== null) {
    header('Location: dashboard.php');
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = registerUser(
        $_POST['name'] ?? '',
        $_POST['email'] ?? '',
        $_POST['password'] ?? ''
    );

    if ($result['ok']) {
        loginUser($_POST['email'], $_POST['password']);
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
<title>Sign Up - <?= h(APP_NAME) ?></title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="topbar">
  <div class="logo">B</div>
  <div class="title"><?= h(APP_NAME) ?></div>
</div>

<div class="container">
  <span class="eyebrow">🚀 Get Started</span>
  <h1>Create your account</h1>
  <p class="subtitle">Telegram bot হোস্ট করা শুরু করুন কয়েক সেকেন্ডে।</p>

  <?php if ($error): ?>
    <div class="alert error"><?= h($error) ?></div>
  <?php endif; ?>

  <div class="card">
    <form method="post">
      <label>Full Name</label>
      <input type="text" name="name" placeholder="আপনার নাম" required value="<?= h($_POST['name'] ?? '') ?>">

      <label>Email</label>
      <input type="email" name="email" placeholder="you@example.com" required value="<?= h($_POST['email'] ?? '') ?>">

      <label>Password</label>
      <input type="password" name="password" placeholder="কমপক্ষে ৬ অক্ষর" required>

      <div style="margin-top:20px;">
        <button class="btn" type="submit">Create Account</button>
      </div>
    </form>
  </div>

  <p style="text-align:center;color:var(--muted);">
    আগে থেকেই অ্যাকাউন্ট আছে? <a href="login.php" style="color:var(--primary);font-weight:700;">Log in</a>
  </p>
</div>
</body>
</html>
