<?php
declare(strict_types=1);

// ---------------------------------------------------------------
// APP CONFIG
// ---------------------------------------------------------------
// APP_URL must be your live public URL (used to build Telegram
// webhook URLs). Set it via environment variable on Render:
// Dashboard -> your service -> Environment -> APP_URL
// Example: https://bot-panel.onrender.com
// ---------------------------------------------------------------

define('APP_NAME', 'Bot Panel');

$envUrl = getenv('APP_URL');
define('APP_URL', $envUrl !== false && $envUrl !== '' ? rtrim($envUrl, '/') : 'http://localhost');

define('STORAGE_PATH', dirname(__DIR__) . '/storage');
define('BOTS_PATH', STORAGE_PATH . '/bots');
define('DB_PATH', STORAGE_PATH . '/database.sqlite');

define('MAX_CODE_FILE_SIZE', 512 * 1024); // 512KB per bot.php file

session_start();
