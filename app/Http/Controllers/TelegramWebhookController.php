<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TelegramWebhookController extends Controller
{
    private $botToken;
    private $botUsername;

    public function __construct()
    {
        $this->botToken = config('services.telegram.bot_token');
        $this->botUsername = config('services.telegram.bot_username');
    }

    public function webhook(Request $request)
    {
        try {
            $update = $request->all();
            Log::info('Telegram webhook received', $update);

            // Проверяем secret token если установлен
            $secretToken = $request->header('X-Telegram-Bot-Api-Secret-Token');
            // Здесь можно добавить проверку secret token если нужно

            if (isset($update['message'])) {
                $this->handleMessage($update['message']);
            }

            if (isset($update['callback_query'])) {
                $this->handleCallbackQuery($update['callback_query']);
            }

            return response('OK', 200);
        } catch (\Exception $e) {
            Log::error('Telegram webhook error: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);
            return response('Error', 500);
        }
    }

    private function handleMessage($message)
    {
        $chatId = $message['chat']['id'];
        $userId = $message['from']['id'];
        $text = $message['text'] ?? '';
        $firstName = $message['from']['first_name'] ?? '';
        $lastName = $message['from']['last_name'] ?? '';
        $username = $message['from']['username'] ?? null;

        // Создаем или обновляем пользователя
        $this->createOrUpdateUser($userId, $firstName, $lastName, $username);

        switch ($text) {
            case '/start':
                $this->sendStartMessage($chatId, $firstName);
                break;
            
            case '/app':
                $this->sendAppMessage($chatId);
                break;
            
            case '/login':
                $this->sendLoginMessage($chatId);
                break;
            
            case '/help':
                $this->sendHelpMessage($chatId);
                break;
            
            default:
                $this->sendDefaultMessage($chatId);
                break;
        }
    }

    private function handleCallbackQuery($callbackQuery)
    {
        $chatId = $callbackQuery['message']['chat']['id'];
        $data = $callbackQuery['data'];
        $callbackQueryId = $callbackQuery['id'];

        // Отвечаем на callback query
        $this->answerCallbackQuery($callbackQueryId);

        switch ($data) {
            case 'open_app':
                $this->sendAppMessage($chatId);
                break;
            
            case 'login_web':
                $this->sendLoginMessage($chatId);
                break;
                
            case 'start':
                $firstName = $callbackQuery['from']['first_name'] ?? 'пользователь';
                $this->sendStartMessage($chatId, $firstName);
                break;
                
            case 'help':
                $this->sendHelpMessage($chatId);
                break;
        }
    }

    private function createOrUpdateUser($telegramId, $firstName, $lastName, $username)
    {
        $user = User::where('telegram_id', $telegramId)->first();
        
        $name = trim($firstName . ' ' . ($lastName ?? ''));
        
        if (!$user) {
            User::create([
                'name' => $name,
                'email' => 'telegram_' . $telegramId . '@telegram.local',
                'password' => Hash::make(Str::random(32)),
                'telegram_id' => $telegramId,
                'telegram_username' => $username,
                'telegram_photo_url' => null,
            ]);
        } else {
            $user->update([
                'name' => $name,
                'telegram_username' => $username,
            ]);
        }
    }

    private function sendStartMessage($chatId, $firstName)
    {
        $message = "🎉 Добро пожаловать, {$firstName}!\n\n";
        $message .= "🤖 Добро пожаловать в наше приложение!\n\n";
        $message .= "✨ Особенности:\n";
        $message .= "• 🔐 Автоматическая авторизация через Telegram\n";
        $message .= "• 📱 Работает прямо в Telegram как Mini App\n";
        $message .= "• ⚡ Мгновенный доступ без паролей\n";
        $message .= "• 🔄 Все данные автоматически связаны с вашим аккаунтом\n\n";
        $message .= "🚀 Просто нажмите кнопку ниже и всё готово!\n\n";
        $message .= "❓ Вопросы? Команда /help";

        $keyboard = [
            'inline_keyboard' => [
                [
                    [
                        'text' => '🚀 Открыть приложение',
                        'web_app' => ['url' => config('app.url') . '/miniapp']
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

        $this->sendMessage($chatId, $message, $keyboard);
    }

    private function sendAppMessage($chatId)
    {
        $message = "📱 Ваше приложение\n\n";
        $message .= "Нажмите кнопку ниже для автоматического входа:\n\n";
        $message .= "Что вас ждет:\n";
        $message .= "• Автоматический вход в ваш аккаунт\n";
        $message .= "• Все данные сохраняются автоматически\n";
        $message .= "• Работает на любом устройстве\n";
        $message .= "• Полный функционал без ограничений";

        $keyboard = [
            'inline_keyboard' => [
                [
                    [
                        'text' => '🚀 Войти в приложение',
                        'web_app' => ['url' => config('app.url') . '/miniapp']
                    ]
                ]
            ]
        ];

        $this->sendMessage($chatId, $message, $keyboard);
    }

    private function sendLoginMessage($chatId)
    {
        $loginUrl = config('app.url') . '/miniapp';
        
        $message = "🔐 Авторизация через приложение\n\n";
        $message .= "Если Mini App не работает, попробуйте:\n\n";
        $message .= "1. Обновить Telegram до последней версии\n";
        $message .= "2. Перезапустить приложение\n";
        $message .= "3. Использовать веб-версию по ссылке:\n";
        $message .= $loginUrl . "\n\n";
        $message .= "Авторизация происходит автоматически!";

        $keyboard = [
            'inline_keyboard' => [
                [
                    [
                        'text' => '🌐 Открыть веб-версию',
                        'url' => $loginUrl
                    ]
                ],
                [
                    [
                        'text' => '🔙 Назад к Mini App',
                        'callback_data' => 'open_app'
                    ]
                ]
            ]
        ];

        $this->sendMessage($chatId, $message, $keyboard);
    }

    private function sendHelpMessage($chatId)
    {
        $message = "❓ Справка по использованию\n\n";
        $message .= "🤖 Доступные команды:\n";
        $message .= "/start - Главное меню\n";
        $message .= "/app - Открыть Mini App\n";
        $message .= "/login - Информация об авторизации\n";
        $message .= "/help - Эта справка\n\n";
        $message .= "🚀 Способы запуска:\n";
        $message .= "• Кнопка меню бота (рекомендуется)\n";
        $message .= "• Команда /app\n";
        $message .= "• Кнопки в сообщениях\n\n";
        $message .= "⚠️ Если что-то не работает:\n";
        $message .= "• Убедитесь, что у вас последняя версия Telegram\n";
        $message .= "• Проверьте интернет-соединение\n";
        $message .= "• Попробуйте веб-версию\n\n";
        $message .= "📧 Поддержка: используйте команды бота";

        $keyboard = [
            'inline_keyboard' => [
                [
                    [
                        'text' => '🚀 Открыть приложение',
                        'web_app' => ['url' => config('app.url') . '/miniapp']
                    ]
                ],
                [
                    [
                        'text' => '🏠 Главное меню',
                        'callback_data' => 'start'
                    ]
                ]
            ]
        ];

        $this->sendMessage($chatId, $message, $keyboard);
    }

    private function sendDefaultMessage($chatId)
    {
        $message = "🤖 Привет! Я не понял вашу команду.\n\n";
        $message .= "Используйте /help для получения справки или /start для главного меню.";

        $keyboard = [
            'inline_keyboard' => [
                [
                    [
                        'text' => '🏠 Главное меню',
                        'callback_data' => 'start'
                    ],
                    [
                        'text' => '❓ Помощь',
                        'callback_data' => 'help'
                    ]
                ]
            ]
        ];

        $this->sendMessage($chatId, $message, $keyboard);
    }

    private function sendMessage($chatId, $text, $keyboard = null)
    {
        $data = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'Markdown'
        ];

        if ($keyboard) {
            $data['reply_markup'] = json_encode($keyboard);
        }

        $response = Http::post("https://api.telegram.org/bot{$this->botToken}/sendMessage", $data);

        if (!$response->successful()) {
            Log::error('Failed to send Telegram message', [
                'chat_id' => $chatId,
                'response' => $response->body()
            ]);
        }
    }

    private function answerCallbackQuery($callbackQueryId, $text = null)
    {
        $data = [
            'callback_query_id' => $callbackQueryId
        ];

        if ($text) {
            $data['text'] = $text;
        }

        Http::post("https://api.telegram.org/bot{$this->botToken}/answerCallbackQuery", $data);
    }
}
