<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TelegramMiniAppController extends Controller
{
    /**
     * Главная страница Mini App
     */
    public function index(Request $request)
    {
        // Проверяем данные от Telegram Web App
        $initData = $request->input('tgWebAppData');
        
        if ($initData) {
            $userData = $this->parseInitData($initData);
            if ($userData && $this->verifyTelegramWebAppData($initData)) {
                $user = $this->processAuth($userData);
                Auth::login($user, true);
            }
        }
        
        return view('telegram.miniapp', [
            'user' => Auth::user(),
            'initData' => $initData
        ]);
    }
    
    /**
     * API для авторизации через Mini App
     */
    public function auth(Request $request)
    {
        Log::info('Telegram Mini App auth request', [
            'headers' => $request->headers->all(),
            'input' => $request->all()
        ]);
        
        $initData = $request->input('initData');
        $userData = $request->input('user'); // Добавляем объявление переменной
        
        if (!$initData) {
            Log::error('Missing initData in auth request');
            return response()->json(['error' => 'Отсутствуют данные инициализации'], 400);
        }
        
        // Для разработки можем пропустить проверку подписи
        if (config('app.env') === 'local') {
            Log::info('Local environment - skipping signature verification');
            
            // Попробуем получить данные пользователя из разных источников
            $parsedUserData = null;
            
            if ($userData) {
                $parsedUserData = $userData;
                Log::info('Using provided user data', ['userData' => $userData]);
            } else {
                $parsedUserData = $this->parseInitData($initData);
                Log::info('Parsed init data', ['parsedUserData' => $parsedUserData]);
            }
            
            // Если данные все еще пустые, используем тестовые данные для localhost
            if (!$parsedUserData && $initData === 'test_data') {
                $parsedUserData = [
                    'id' => 123456789,
                    'first_name' => 'Test',
                    'last_name' => 'User',
                    'username' => 'testuser',
                    'photo_url' => null,
                    'language_code' => 'ru'
                ];
                Log::info('Using test user data for localhost');
            }
            
            if (!$parsedUserData) {
                Log::error('Failed to parse user data', ['initData' => $initData, 'userData' => $userData]);
                return response()->json(['error' => 'Не удалось получить данные пользователя'], 400);
            }
            
        } else {
            // В продакшене проверяем подпись
            if (!$this->verifyTelegramWebAppData($initData)) {
                Log::error('Invalid signature verification');
                return response()->json(['error' => 'Неверные данные авторизации'], 401);
            }
            
            $parsedUserData = $this->parseInitData($initData);
            if (!$parsedUserData) {
                Log::error('Failed to parse init data');
                return response()->json(['error' => 'Не удалось распарсить данные пользователя'], 400);
            }
        }
        
        try {
            $user = $this->processAuth($parsedUserData);
            Auth::login($user, true);
            
            Log::info('Successful authentication', ['user_id' => $user->id]);
            
            return response()->json([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'telegram_id' => $user->telegram_id,
                    'telegram_username' => $user->telegram_username,
                    'telegram_photo_url' => $user->telegram_photo_url,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Auth process failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Ошибка обработки авторизации: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Парсинг данных инициализации от Telegram Web App
     */
    private function parseInitData($initData)
    {
        parse_str($initData, $data);
        
        if (!isset($data['user'])) {
            return null;
        }
        
        $userData = json_decode($data['user'], true);
        
        return [
            'id' => $userData['id'] ?? null,
            'first_name' => $userData['first_name'] ?? '',
            'last_name' => $userData['last_name'] ?? '',
            'username' => $userData['username'] ?? null,
            'photo_url' => $userData['photo_url'] ?? null,
            'language_code' => $userData['language_code'] ?? 'ru',
        ];
    }
    
    /**
     * Проверка подлинности данных Telegram Web App
     */
    private function verifyTelegramWebAppData($initData)
    {
        $botToken = config('services.telegram.bot_token');
        
        if (!$botToken) {
            return false;
        }
        
        // В режиме разработки пропускаем проверку
        if (config('app.env') === 'local') {
            return true;
        }
        
        parse_str($initData, $data);
        
        if (!isset($data['hash'])) {
            return false;
        }
        
        $hash = $data['hash'];
        unset($data['hash']);
        
        $dataCheckArr = [];
        foreach ($data as $key => $value) {
            $dataCheckArr[] = $key . '=' . $value;
        }
        sort($dataCheckArr);
        
        $dataCheckString = implode("\n", $dataCheckArr);
        $secretKey = hash_hmac('sha256', 'WebAppData', $botToken, true);
        $calculatedHash = hash_hmac('sha256', $dataCheckString, $secretKey);
        
        return hash_equals($calculatedHash, $hash);
    }
    
    /**
     * Обработка авторизации пользователя
     */
    private function processAuth($telegramData)
    {
        // Ищем пользователя по telegram_id
        $user = User::where('telegram_id', $telegramData['id'])->first();
        
        if (!$user) {
            // Создаем нового пользователя
            $user = User::create([
                'name' => trim($telegramData['first_name'] . ' ' . ($telegramData['last_name'] ?? '')),
                'email' => $telegramData['id'] . '@telegram.user',
                'telegram_id' => $telegramData['id'],
                'telegram_username' => $telegramData['username'],
                'telegram_photo_url' => $telegramData['photo_url'],
                'password' => Hash::make(Str::random(16)),
                'email_verified_at' => now(),
            ]);
        } else {
            // Обновляем информацию о пользователе
            $user->update([
                'name' => trim($telegramData['first_name'] . ' ' . ($telegramData['last_name'] ?? '')),
                'telegram_username' => $telegramData['username'],
                'telegram_photo_url' => $telegramData['photo_url'],
            ]);
        }
        
        return $user;
    }
}
