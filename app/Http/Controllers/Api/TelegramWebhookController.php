<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // Проверяем подпись webhook
        if (!$this->verifyWebhook($request)) {
            return response('Unauthorized', 401);
        }

        $update = $request->all();
        Log::info('Telegram webhook received', $update);

        // Обрабатываем сообщение
        if (isset($update['message'])) {
            $this->handleMessage($update['message']);
        }

        // Обрабатываем callback query
        if (isset($update['callback_query'])) {
            $this->handleCallbackQuery($update['callback_query']);
        }

        return response('OK', 200);
    }

    private function verifyWebhook(Request $request)
    {
        $botToken = config('services.telegram.bot_token');
        $secretToken = hash('sha256', $botToken);
        
        $telegramSignature = $request->header('X-Telegram-Bot-Api-Secret-Token');
        
        return hash_equals($secretToken, $telegramSignature ?? '');
    }

    private function handleMessage($message)
    {
        $chatId = $message['chat']['id'];
        $text = $message['text'] ?? '';

        switch ($text) {
            case '/start':
                $this->sendStartMessage($chatId);
                break;
            case '/app':
                $this->sendAppLink($chatId);
                break;
            case '/help':
                $this->sendHelpMessage($chatId);
                break;
            default:
                $this->sendDefaultMessage($chatId);
                break;
        }
    }

    private function sendStartMessage($chatId)
    {
        $message = "🚀 Добро пожаловать в наш Mini App!\n\n";
        $message .= "Для запуска приложения:\n";
        $message .= "• Нажмите кнопку '🚀 Открыть App' в меню\n";
        $message .= "• Или используйте команду /app\n\n";
        $message .= "✨ Быстрая авторизация через Telegram\n";
        $message .= "🔒 Безопасный вход без паролей\n";
        $message .= "📱 Работает прямо в Telegram";

        $keyboard = [
            'inline_keyboard' => [
                [
                    [
                        'text' => '🚀 Открыть Mini App',
                        'web_app' => [
                            'url' => config('app.url') . '/miniapp'
                        ]
                    ]
                ],
                [
                    [
                        'text' => '🌐 Открыть в браузере',
                        'url' => config('app.url') . '/login'
                    ]
                ]
            ]
        ];

        $this->sendMessage($chatId, $message, $keyboard);
    }

    private function sendAppLink($chatId)
    {
        $message = "📱 Откройте наше приложение:";
        
        $keyboard = [
            'inline_keyboard' => [
                [
                    [
                        'text' => '🚀 Открыть Mini App',
                        'web_app' => [
                            'url' => config('app.url') . '/miniapp'
                        ]
                    ]
                ]
            ]
        ];

        $this->sendMessage($chatId, $message, $keyboard);
    }

    private function sendHelpMessage($chatId)
    {
        $message = "❓ Помощь по использованию:\n\n";
        $message .= "🤖 Доступные команды:\n";
        $message .= "/start - Запустить приложение\n";
        $message .= "/app - Открыть Mini App\n";
        $message .= "/help - Показать эту справку\n\n";
        $message .= "💡 Совет: Используйте кнопку меню для быстрого доступа к приложению!";

        $this->sendMessage($chatId, $message);
    }

    private function sendDefaultMessage($chatId)
    {
        $message = "🤖 Привет! Используйте команду /start для запуска приложения или /help для получения справки.";
        $this->sendMessage($chatId, $message);
    }

    private function handleCallbackQuery($callbackQuery)
    {
        $chatId = $callbackQuery['message']['chat']['id'];
        $data = $callbackQuery['data'] ?? '';
        $callbackQueryId = $callbackQuery['id'];

        // Отвечаем на callback query
        $this->answerCallbackQuery($callbackQueryId, 'Обработано ✅');

        // Здесь можно добавить обработку различных callback данных
        switch ($data) {
            case 'open_app':
                $this->sendAppLink($chatId);
                break;
            default:
                break;
        }
    }

    private function sendMessage($chatId, $text, $replyMarkup = null)
    {
        $botToken = config('services.telegram.bot_token');
        $url = "https://api.telegram.org/bot{$botToken}/sendMessage";

        $data = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML'
        ];

        if ($replyMarkup) {
            $data['reply_markup'] = json_encode($replyMarkup);
        }

        $this->makeRequest($url, $data);
    }

    private function answerCallbackQuery($callbackQueryId, $text = '')
    {
        $botToken = config('services.telegram.bot_token');
        $url = "https://api.telegram.org/bot{$botToken}/answerCallbackQuery";

        $data = [
            'callback_query_id' => $callbackQueryId,
            'text' => $text
        ];

        $this->makeRequest($url, $data);
    }

    private function makeRequest($url, $data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            Log::error('Telegram API error', [
                'url' => $url,
                'data' => $data,
                'response' => $response,
                'http_code' => $httpCode
            ]);
        }

        return json_decode($response, true);
    }
}
