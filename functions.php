<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

function randomSecret(int $bytes = 20): string
{
    return bin2hex(random_bytes($bytes));
}

function botDir(int $botId): string
{
    return BOTS_PATH . '/' . $botId;
}

function botCodeFile(int $botId): string
{
    return botDir($botId) . '/bot.php';
}

function botLogFile(int $botId): string
{
    if (!is_dir(STORAGE_PATH . '/logs')) {
        mkdir(STORAGE_PATH . '/logs', 0775, true);
    }
    return STORAGE_PATH . '/logs/' . $botId . '.log';
}

function getBotForUser(int $botId, int $userId): ?array
{
    $stmt = db()->prepare('SELECT * FROM bots WHERE id = ? AND user_id = ?');
    $stmt->execute([$botId, $userId]);
    $bot = $stmt->fetch(PDO::FETCH_ASSOC);
    return $bot ?: null;
}

function addLog(int $botId, string $level, string $message): void
{
    $stmt = db()->prepare('INSERT INTO logs (bot_id, level, message) VALUES (?, ?, ?)');
    $stmt->execute([$botId, $level, $message]);

    // trim old logs so the table doesn't grow forever (keep latest 300 per bot)
    db()->exec("
        DELETE FROM logs WHERE bot_id = $botId AND id NOT IN (
            SELECT id FROM logs WHERE bot_id = $botId ORDER BY id DESC LIMIT 300
        )
    ");
}

function getLogs(int $botId, int $limit = 50): array
{
    $stmt = db()->prepare('SELECT * FROM logs WHERE bot_id = ? ORDER BY id DESC LIMIT ?');
    $stmt->bindValue(1, $botId, PDO::PARAM_INT);
    $stmt->bindValue(2, $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/** Starter template written into every new bot's bot.php */
function defaultBotTemplate(): string
{
    return <<<'PHP'
<?php
declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Your Telegram Bot Logic
|--------------------------------------------------------------------------
| BOT_TOKEN and BOT_USERNAME are already defined for you by the panel.
| $update contains the decoded Telegram update (array).
| Use send() to reply. Avoid infinite loops - each webhook call must
| finish quickly (Telegram expects a fast response).
|--------------------------------------------------------------------------
*/

function send(string $chatId, string $text): void
{
    $url = 'https://api.telegram.org/bot' . BOT_TOKEN . '/sendMessage';
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ],
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 15,
    ]);
    curl_exec($ch);
    curl_close($ch);
}

$message = $update['message'] ?? null;

if (is_array($message)) {
    $chatId = (string) ($message['chat']['id'] ?? '');
    $text = trim((string) ($message['text'] ?? ''));

    if ($text === '/start') {
        send($chatId, "👋 Hello! Bot @" . BOT_USERNAME . " is now live.\n\nEdit bot.php from the panel to customize replies.");
    } else {
        send($chatId, "You said: " . $text);
    }
}
PHP;
}
