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
        // Получаем данные Telegram Web App различными способами
        $initData = $request->header('X-Telegram-Init-Data') ?: 
                   $request->get('tgWebAppData') ?: 
                   $request->get('_auth');
        
        Log::info('Mini App accessed', [
            'has_init_data' => !empty($initData),
            'auth_user' => Auth::check() ? Auth::user()->telegram_id : null
        ]);

        // Если есть данные Telegram Web App, обрабатываем их
        if ($initData) {
            try {
                if ($this->verifyTelegramWebAppData($initData)) {
                    $userData = $this->parseTelegramWebAppData($initData);
                    
                    if ($userData) {
                        $user = $this->createOrUpdateUser($userData);
                        Auth::login($user, true); // Запомнить пользователя
                        
                        Log::info('User auto-logged via Mini App', [
                            'user_id' => $user->id,
                            'telegram_id' => $user->telegram_id
                        ]);
                    }
                } else {
                    Log::warning('Invalid Telegram Web App data received');
                }
            } catch (\Exception $e) {
                Log::error('Error processing Telegram Web App data', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        return view('miniapp.app', [
            'user' => Auth::user(),
            'initData' => $initData
        ]);
    }

    private function verifyTelegramWebAppData($initData)
    {
        $botToken = config('services.telegram.bot_token');
        
        if (!$botToken) {
            Log::error('Bot token not configured');
            return false;
        }

        // Парсим данные
        parse_str($initData, $data);
        $checkHash = $data['hash'] ?? '';
        unset($data['hash']);

        if (empty($checkHash)) {
            Log::warning('No hash provided in Web App data');
            return false;
        }

        // Создаем строку для проверки согласно документации Telegram
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
        
        if (!$isValid) {
            Log::warning('Web App data verification failed', [
                'data_check_string' => $dataCheckString,
                'expected_hash' => $hash,
                'received_hash' => $checkHash
            ]);
        } else {
            Log::info('Web App data verified successfully');
        }

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
        }

        return response()->json(['error' => 'Invalid data'], 400);
    }

    /**
     * Автоматическая авторизация для пользователей без initData
     */
    public function autoAuth(Request $request)
    {
        // Если пользователь уже авторизован
        if (Auth::check()) {
            return response()->json([
                'success' => true,
                'user' => [
                    'id' => Auth::user()->id,
                    'name' => Auth::user()->name,
                    'telegram_id' => Auth::user()->telegram_id,
                ]
            ]);
        }

        return response()->json(['error' => 'No authentication data available'], 401);
    }
}
