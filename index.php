<?php
require_once __DIR__ . '/../includes/auth.php';

if (currentUser() !== null) {
    header('Location: dashboard.php');
} else {
    header('Location: login.php');
}
exit;
