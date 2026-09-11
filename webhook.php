<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Always answer Telegram with 200 quickly, even on errors,
// so Telegram doesn't retry-storm the endpoint.
register_shutdown_function(function () {
    if (http_response_code() === false) {
        http_response_code(200);
    }
});

$secret = (string) ($_GET['secret'] ?? '');

if ($secret === '') {
    http_response_code(200);
    exit('OK');
}

$stmt = db()->prepare('SELECT * FROM bots WHERE webhook_secret = ?');
$stmt->execute([$secret]);
$bot = $stmt->fetch(PDO::FETCH_ASSOC);

if ($bot === false) {
    http_response_code(200);
    exit('OK');
}

$botId = (int) $bot['id'];

// Paused bots receive the webhook but do nothing.
if ($bot['status'] !== 'running') {
    http_response_code(200);
    exit('OK');
}

$raw = file_get_contents('php://input');
$update = json_decode(is_string($raw) ? $raw : '{}', true);

if (!is_array($update)) {
    $update = [];
}

$fromUser = $update['message']['from']['username']
    ?? $update['message']['from']['first_name']
    ?? 'unknown';
$chatId = $update['message']['chat']['id'] ?? 'n/a';
$incomingText = $update['message']['text'] ?? '(non-text update)';

addLog($botId, 'info', 'Received "' . substr((string) $incomingText, 0, 80) . '" from @' . $fromUser . ' (Chat ID: ' . $chatId . ')');

$codeFile = botCodeFile($botId);

if (!file_exists($codeFile)) {
    addLog($botId, 'error', 'bot.php not found for this bot.');
    http_response_code(200);
    exit('OK');
}

// --- Sandbox constants made available to the user's code ---
define('BOT_TOKEN', $bot['token']);
define('BOT_USERNAME', $bot['username']);

// Narrow filesystem access for this request to this bot's folder + tmp.
// (Best-effort isolation - the real hard boundary is disable_functions
// in php.ini, set at the Docker/server level.)
$allowedPaths = implode(PATH_SEPARATOR, [botDir($botId), sys_get_temp_dir()]);
@ini_set('open_basedir', $allowedPaths);

// Catch fatal errors / exceptions from the included user code so one
// broken bot never crashes the shared webhook process.
set_error_handler(function ($severity, $message, $file, $line) use ($botId) {
    addLog($botId, 'error', "PHP notice/warning: $message in $file:$line");
    return true; // don't execute PHP's internal handler
});

try {
    include $codeFile;
} catch (\Throwable $e) {
    addLog($botId, 'error', 'Script error in user bot.php: ' . $e->getMessage());
}

restore_error_handler();

http_response_code(200);
echo 'OK';
