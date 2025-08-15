<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $update = $request->all();
        
        Log::info('Telegram webhook received', $update);
        
        if (isset($update['message'])) {
            $this->handleMessage($update['message']);
        }
        
        return response('OK');
    }
    
    private function handleMessage($message)
    {
        $chatId = $message['chat']['id'];
        $text = $message['text'] ?? '';
        
        switch ($text) {
            case '/start':
                $this->sendWelcomeMessage($chatId);
                break;
                
            case '/app':
                $this->sendMiniAppButton($chatId);
                break;
                
            case '/help':
                $this->sendHelpMessage($chatId);
                break;
                
            default:
                $this->sendDefaultMessage($chatId);
                break;
        }
    }
    
    private function sendWelcomeMessage($chatId)
    {
        $appName = config('app.name');
        $miniAppUrl = config('app.url') . '/telegram/miniapp';
        
        $keyboard = [
            'inline_keyboard' => [
                [
                    [
                        'text' => '🚀 Открыть приложение',
                        'web_app' => [
                            'url' => $miniAppUrl
                        ]
                    ]
                ],
                [
                    [
                        'text' => '❓ Помощь',
                        'callback_data' => 'help'
                    ]
                ]
            ]
        ];
        
        $message = "🎉 Добро пожаловать в {$appName}!\n\n";
        $message .= "Нажмите кнопку ниже, чтобы открыть наше приложение:\n\n";
        $message .= "• 📱 Полнофункциональный интерфейс\n";
        $message .= "• 🔐 Безопасная авторизация\n";
        $message .= "• ⚡ Быстрая работа\n\n";
        $message .= "Или используйте команду /app";
        
        $this->sendMessage($chatId, $message, $keyboard);
    }
    
    private function sendMiniAppButton($chatId)
    {
        $miniAppUrl = config('app.url') . '/telegram/miniapp';
        
        $keyboard = [
            'inline_keyboard' => [
                [
                    [
                        'text' => '🚀 Открыть приложение',
                        'web_app' => [
                            'url' => $miniAppUrl
                        ]
                    ]
                ]
            ]
        ];
        
        $this->sendMessage($chatId, '🚀 Нажмите кнопку ниже, чтобы открыть приложение:', $keyboard);
    }
    
    private function sendHelpMessage($chatId)
    {
        $message = "📖 *Помощь*\n\n";
        $message .= "Доступные команды:\n";
        $message .= "/start - Главное меню\n";
        $message .= "/app - Открыть приложение\n";
        $message .= "/help - Эта справка\n\n";
        $message .= "💡 *Как пользоваться:*\n";
        $message .= "1. Нажмите кнопку \"Открыть приложение\"\n";
        $message .= "2. Разрешите доступ к данным Telegram\n";
        $message .= "3. Пользуйтесь всеми функциями!\n\n";
        $message .= "❓ Есть вопросы? Обратитесь к администратору.";
        
        $this->sendMessage($chatId, $message, null, 'Markdown');
    }
    
    private function sendDefaultMessage($chatId)
    {
        $message = "🤔 Не понимаю эту команду.\n\n";
        $message .= "Используйте:\n";
        $message .= "/start - Главное меню\n";
        $message .= "/app - Открыть приложение\n";
        $message .= "/help - Помощь";
        
        $this->sendMessage($chatId, $message);
    }
    
    private function sendMessage($chatId, $text, $keyboard = null, $parseMode = null)
    {
        $botToken = config('services.telegram.bot_token');
        
        if (!$botToken) {
            Log::error('Telegram bot token not configured');
            return;
        }
        
        $data = [
            'chat_id' => $chatId,
            'text' => $text,
        ];
        
        if ($keyboard) {
            $data['reply_markup'] = json_encode($keyboard);
        }
        
        if ($parseMode) {
            $data['parse_mode'] = $parseMode;
        }
        
        $url = "https://api.telegram.org/bot{$botToken}/sendMessage";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            Log::error('Failed to send Telegram message', [
                'http_code' => $httpCode,
                'response' => $response
            ]);
        }
    }
}
