<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MiniAppController extends Controller
{
    public function index(Request $request)
    {
        // Проверяем, есть ли данные Telegram Web App
        $initData = $request->header('X-Telegram-Init-Data') ?: $request->get('tgWebAppData');
        
        if ($initData) {
            Log::info('Mini App accessed with init data', ['init_data' => $initData]);
            
            if ($this->verifyTelegramWebAppData($initData)) {
                $userData = $this->parseTelegramWebAppData($initData);
                
                if ($userData) {
                    $user = $this->createOrUpdateUser($userData);
                    Auth::login($user);
                    
                    Log::info('User auto-logged via Mini App', ['user_id' => $user->id]);
                }
            }
        }

        return view('miniapp.index', [
            'user' => Auth::user(),
            'initData' => $initData
        ]);
    }

    private function verifyTelegramWebAppData($initData)
    {
        $botToken = config('services.telegram.bot_token');
        
        // Парсим данные
        parse_str($initData, $data);
        $checkHash = $data['hash'] ?? '';
        unset($data['hash']);

        // Создаем строку для проверки
        $dataCheckArray = [];
        foreach ($data as $key => $value) {
            if ($value !== '' && $value !== null) {
                $dataCheckArray[] = $key . '=' . $value;
            }
        }
        sort($dataCheckArray);
        $dataCheckString = implode("\n", $dataCheckArray);

        // Создаем секретный ключ для Web App
        $secretKey = hash_hmac('sha256', 'WebAppData', $botToken, true);
        $hash = hash_hmac('sha256', $dataCheckString, $secretKey);

        $isValid = hash_equals($hash, $checkHash);
        
        Log::info('Web App data verification', [
            'is_valid' => $isValid,
            'data_check_string' => $dataCheckString,
            'expected_hash' => $hash,
            'received_hash' => $checkHash
        ]);

        return $isValid;
    }

    private function parseTelegramWebAppData($initData)
    {
        parse_str($initData, $data);
        
        if (isset($data['user'])) {
            return json_decode($data['user'], true);
        }
        
        return null;
    }

    private function createOrUpdateUser($userData)
    {
        $user = User::where('telegram_id', $userData['id'])->first();
        
        if (!$user) {
            $user = User::create([
                'name' => trim(($userData['first_name'] ?? '') . ' ' . ($userData['last_name'] ?? '')),
                'email' => 'telegram_' . $userData['id'] . '@miniapp.local',
                'password' => Hash::make(Str::random(32)),
                'telegram_id' => $userData['id'],
                'telegram_username' => $userData['username'] ?? null,
                'telegram_photo_url' => $userData['photo_url'] ?? null,
            ]);
            
            Log::info('New user created via Mini App', ['user_id' => $user->id, 'telegram_id' => $userData['id']]);
        } else {
            // Обновляем данные пользователя
            $user->update([
                'name' => trim(($userData['first_name'] ?? '') . ' ' . ($userData['last_name'] ?? '')),
                'telegram_username' => $userData['username'] ?? $user->telegram_username,
                'telegram_photo_url' => $userData['photo_url'] ?? $user->telegram_photo_url,
            ]);
            
            Log::info('User updated via Mini App', ['user_id' => $user->id, 'telegram_id' => $userData['id']]);
        }

        return $user;
    }

    public function auth(Request $request)
    {
        // Специальный endpoint для авторизации через Mini App
        $initData = $request->get('initData');
        
        if (!$initData) {
            return response()->json(['error' => 'No init data provided'], 400);
        }

        if ($this->verifyTelegramWebAppData($initData)) {
            $userData = $this->parseTelegramWebAppData($initData);
            
            if ($userData) {
                $user = $this->createOrUpdateUser($userData);
                Auth::login($user);
                
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
        }

        return response()->json(['error' => 'Invalid data'], 400);
    }
}
