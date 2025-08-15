<?php
/**
 * Скрипт для настройки Telegram бота с Mini App
 * Запустите этот скрипт один раз для настройки бота
 */

require_once __DIR__ . '/vendor/autoload.php';

// Загружаем переменные окружения
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$botToken = $_ENV['TELEGRAM_BOT_TOKEN'] ?? '';
$appUrl = $_ENV['APP_URL'] ?? 'http://localhost';

if (empty($botToken)) {
    die("Ошибка: TELEGRAM_BOT_TOKEN не найден в .env файле\n");
}

// URL вашего Mini App
$miniAppUrl = $appUrl . '/telegram/miniapp';

echo "Настройка Telegram бота...\n";
echo "Bot Token: " . substr($botToken, 0, 10) . "...\n";
echo "Mini App URL: " . $miniAppUrl . "\n\n";

// 1. Установка команд бота
$commands = [
    [
        'command' => 'start',
        'description' => 'Запустить приложение'
    ],
    [
        'command' => 'app',
        'description' => 'Открыть Mini App'
    ],
    [
        'command' => 'help',
        'description' => 'Помощь'
    ]
];

$response = makeRequest('setMyCommands', [
    'commands' => json_encode($commands)
]);

if ($response['ok']) {
    echo "✅ Команды бота установлены успешно\n";
} else {
    echo "❌ Ошибка установки команд: " . $response['description'] . "\n";
}

// 2. Установка описания бота
$description = "Добро пожаловать в наше приложение! Используйте команду /app для открытия Mini App.";

$response = makeRequest('setMyDescription', [
    'description' => $description
]);

if ($response['ok']) {
    echo "✅ Описание бота установлено успешно\n";
} else {
    echo "❌ Ошибка установки описания: " . $response['description'] . "\n";
}

// 3. Создание webhook (опционально, для продакшена)
if (php_sapi_name() === 'cli') {
    echo "\nХотите установить webhook для бота? (y/n): ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    if (trim($line) === 'y') {
        $webhookUrl = $appUrl . '/telegram/webhook';
        
        $response = makeRequest('setWebhook', [
            'url' => $webhookUrl
        ]);
        
        if ($response['ok']) {
            echo "✅ Webhook установлен: " . $webhookUrl . "\n";
        } else {
            echo "❌ Ошибка установки webhook: " . $response['description'] . "\n";
        }
    }
    fclose($handle);
}

echo "\n=== Инструкции по настройке ===\n";
echo "1. Откройте @BotFather в Telegram\n";
echo "2. Выберите вашего бота\n";
echo "3. Нажмите 'Bot Settings' -> 'Menu Button'\n";
echo "4. Выберите 'Configure Menu Button'\n";
echo "5. Введите URL: " . $miniAppUrl . "\n";
echo "6. Введите текст кнопки: 'Открыть приложение'\n\n";

echo "Или используйте команду для установки Menu Button:\n";
echo "curl -X POST \"https://api.telegram.org/bot{$botToken}/setChatMenuButton\" \\\n";
echo "  -H \"Content-Type: application/json\" \\\n";
echo "  -d '{\n";
echo "    \"menu_button\": {\n";
echo "      \"type\": \"web_app\",\n";
echo "      \"text\": \"Открыть приложение\",\n";
echo "      \"web_app\": {\n";
echo "        \"url\": \"" . $miniAppUrl . "\"\n";
echo "      }\n";
echo "    }\n";
echo "  }'\n\n";

echo "=== Тестирование ===\n";
echo "Отправьте боту команду /start или нажмите кнопку меню\n";

function makeRequest($method, $params = [])
{
    global $botToken;
    
    $url = "https://api.telegram.org/bot{$botToken}/{$method}";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        return ['ok' => false, 'description' => 'HTTP Error: ' . $httpCode];
    }
    
    return json_decode($response, true);
}
