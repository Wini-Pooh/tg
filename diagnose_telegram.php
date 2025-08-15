<?php
/**
 * Диагностика настроек Telegram бота
 */

require_once __DIR__ . '/vendor/autoload.php';

// Загружаем Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔍 Диагностика настроек Telegram бота\n";
echo "=====================================\n\n";

// Проверяем переменные окружения
echo "📋 Переменные окружения:\n";
echo "APP_ENV: " . config('app.env') . "\n";
echo "APP_URL: " . config('app.url') . "\n";
echo "TELEGRAM_BOT_TOKEN: " . substr(config('services.telegram.bot_token'), 0, 10) . "...\n";
echo "TELEGRAM_BOT_USERNAME: " . config('services.telegram.bot_username') . "\n\n";

// Проверяем маршруты
echo "🔗 Маршруты:\n";
$loginRoute = route('telegram.login');
echo "telegram.login: " . $loginRoute . "\n\n";

// Проверяем, что будет в HTML
echo "📝 HTML код для Telegram Widget:\n";
$botUsername = config('services.telegram.bot_username');
$authUrl = route('telegram.login');

echo '<script async src="https://telegram.org/js/telegram-widget.js?22"' . "\n";
echo '        data-telegram-login="' . $botUsername . '"' . "\n";
echo '        data-size="large"' . "\n";
echo '        data-auth-url="' . $authUrl . '"' . "\n";
echo '        data-request-access="write">' . "\n";
echo '</script>' . "\n\n";

// Проверяем доступность API Telegram
echo "🌐 Проверка API Telegram:\n";

function makeRequest($method, $params = []) {
    $botToken = config('services.telegram.bot_token');
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
        return ['ok' => false, 'error' => "HTTP $httpCode", 'response' => $response];
    }
    
    return json_decode($response, true);
}

$meResponse = makeRequest('getMe');
if ($meResponse['ok']) {
    $botInfo = $meResponse['result'];
    echo "✅ Бот доступен: @{$botInfo['username']} ({$botInfo['first_name']})\n";
    echo "ID: {$botInfo['id']}\n";
    
    // Проверяем соответствие username
    if ($botInfo['username'] !== config('services.telegram.bot_username')) {
        echo "⚠️  ВНИМАНИЕ: Username в конфиге не совпадает с реальным!\n";
        echo "В конфиге: " . config('services.telegram.bot_username') . "\n";
        echo "Реальный: @{$botInfo['username']}\n";
    }
} else {
    echo "❌ Ошибка доступа к боту: " . json_encode($meResponse) . "\n";
}

echo "\n🎯 Рекомендации:\n";
echo "1. Убедитесь, что в BotFather настроен домен: " . parse_url(config('app.url'), PHP_URL_HOST) . "\n";
echo "2. Проверьте, что username бота в .env соответствует реальному\n";
echo "3. Убедитесь, что сайт доступен по HTTPS с валидным SSL\n";
echo "4. Попробуйте очистить кеш браузера\n";
