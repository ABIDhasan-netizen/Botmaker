<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

function currentUser(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    $stmt = db()->prepare('SELECT id, name, email FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    return $user ?: null;
}

function requireLogin(): array
{
    $user = currentUser();
    if ($user === null) {
        header('Location: login.php');
        exit;
    }
    return $user;
}

function registerUser(string $name, string $email, string $password): array
{
    $name = trim($name);
    $email = strtolower(trim($email));

    if ($name === '' || $email === '' || strlen($password) < 6) {
        return ['ok' => false, 'error' => 'সব ফিল্ড সঠিকভাবে পূরণ করুন। পাসওয়ার্ড কমপক্ষে ৬ অক্ষরের হতে হবে।'];
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['ok' => false, 'error' => 'সঠিক ইমেইল দিন।'];
    }

    $stmt = db()->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return ['ok' => false, 'error' => 'এই ইমেইল দিয়ে আগেই অ্যাকাউন্ট আছে।'];
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = db()->prepare('INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)');
    $stmt->execute([$name, $email, $hash]);

    return ['ok' => true, 'id' => (int) db()->lastInsertId()];
}

function loginUser(string $email, string $password): array
{
    $email = strtolower(trim($email));

    $stmt = db()->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['password_hash'])) {
        return ['ok' => false, 'error' => 'ইমেইল বা পাসওয়ার্ড ভুল।'];
    }

    $_SESSION['user_id'] = (int) $user['id'];
    return ['ok' => true];
}

function logoutUser(): void
{
    $_SESSION = [];
    session_destroy();
}

function flash(string $key, ?string $message = null)
{
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }
    $msg = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $msg;
}

function h(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
