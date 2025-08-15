<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
        $initData = $request->input('initData');
        
        if (!$initData) {
            return response()->json(['error' => 'Отсутствуют данные инициализации'], 400);
        }
        
        if (!$this->verifyTelegramWebAppData($initData)) {
            return response()->json(['error' => 'Неверные данные авторизации'], 401);
        }
        
        $userData = $this->parseInitData($initData);
        if (!$userData) {
            return response()->json(['error' => 'Не удалось распарсить данные пользователя'], 400);
        }
        
        $user = $this->processAuth($userData);
        Auth::login($user, true);
        
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
