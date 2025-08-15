<?php
/**
 * Скрипт для настройки домена Telegram бота
 */

require_once __DIR__ . '/vendor/autoload.php';

// Загружаем переменные окружения
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$botToken = $_ENV['TELEGRAM_BOT_TOKEN'] ?? '';
$domain = parse_url($_ENV['APP_URL'] ?? '', PHP_URL_HOST);

if (empty($botToken)) {
    die("❌ Ошибка: TELEGRAM_BOT_TOKEN не найден в .env файле\n");
}

if (empty($domain)) {
    die("❌ Ошибка: Не удалось извлечь домен из APP_URL\n");
}

echo "🔧 Настройка домена для Telegram бота...\n";
echo "Bot Token: " . substr($botToken, 0, 10) . "...\n";
echo "Домен: " . $domain . "\n\n";

function makeRequest($method, $params = []) {
    global $botToken;
    
    $url = "https://api.telegram.org/bot{$botToken}/{$method}";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        echo "❌ HTTP код: $httpCode\n";
        echo "Ответ: $response\n";
        return false;
    }
    
    return json_decode($response, true);
}

// Попробуем установить домен через setMyDomain (если доступно)
echo "📝 Попытка установки домена...\n";

$response = makeRequest('setMyDomain', [
    'domain' => $domain
]);

if ($response && $response['ok']) {
    echo "✅ Домен '{$domain}' успешно установлен!\n";
} else {
    echo "⚠️  Автоматическая установка домена не удалась.\n";
    echo "Ответ API: " . json_encode($response) . "\n\n";
    
    echo "🔧 Пожалуйста, установите домен вручную через @BotFather:\n";
    echo "1. Откройте @BotFather в Telegram\n";
    echo "2. Отправьте команду /mybots\n";
    echo "3. Выберите вашего бота\n";
    echo "4. Нажмите 'Bot Settings'\n";
    echo "5. Выберите 'Domain'\n";
    echo "6. Введите домен: {$domain}\n\n";
}

// Проверим текущие настройки бота
echo "📋 Проверка текущих настроек бота...\n";

$meResponse = makeRequest('getMe');
if ($meResponse && $meResponse['ok']) {
    $botInfo = $meResponse['result'];
    echo "✅ Бот: @{$botInfo['username']} ({$botInfo['first_name']})\n";
    echo "ID: {$botInfo['id']}\n";
} else {
    echo "❌ Не удалось получить информацию о боте\n";
}

echo "\n🎯 Дополнительные рекомендации:\n";
echo "1. Убедитесь, что ваш сайт доступен по HTTPS\n";
echo "2. Проверьте, что SSL сертификат действителен\n";
echo "3. Telegram требует валидный SSL для Login Widget\n";
echo "4. После настройки домена может потребоваться несколько минут для активации\n";

echo "\n🔗 Ссылки для проверки:\n";
echo "• Ваш сайт: https://{$domain}/login\n";
echo "• Telegram Login: https://{$domain}/telegram/login\n";
echo "• Mini App: https://{$domain}/telegram/miniapp\n";
