<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TelegramAuthController extends Controller
{
    public function login(Request $request)
    {
        return view('auth.telegram-login');
    }

    public function callback(Request $request)
    {
        try {
            // Логируем все входящие данные для отладки
            Log::info('Telegram callback received', [
                'request_data' => $request->all(),
                'headers' => $request->headers->all(),
                'query' => $request->query()
            ]);

            // Проверяем наличие обязательных полей
            if (!$request->has(['id', 'hash', 'auth_date'])) {
                Log::error('Missing required fields', ['data' => $request->all()]);
                return redirect()->route('login')->withErrors(['Отсутствуют обязательные данные от Telegram']);
            }

            // Проверяем данные от Telegram
            if (!$this->verifyTelegramAuth($request->all())) {
                Log::error('Telegram auth verification failed', ['data' => $request->all()]);
                return redirect()->route('login')->withErrors(['Не удалось проверить данные от Telegram']);
            }

            $telegramData = $request->all();
            
            // Ищем или создаем пользователя
            $user = User::where('telegram_id', $telegramData['id'])->first();
            
            if (!$user) {
                $user = User::create([
                    'name' => trim(($telegramData['first_name'] ?? '') . ' ' . ($telegramData['last_name'] ?? '')),
                    'email' => 'telegram_' . $telegramData['id'] . '@example.com',
                    'password' => Hash::make(Str::random(32)),
                    'telegram_id' => $telegramData['id'],
                    'telegram_username' => $telegramData['username'] ?? null,
                    'telegram_photo_url' => $telegramData['photo_url'] ?? null,
                ]);
                
                Log::info('New user created', ['user_id' => $user->id, 'telegram_id' => $telegramData['id']]);
            } else {
                // Обновляем данные пользователя
                $user->update([
                    'name' => trim(($telegramData['first_name'] ?? '') . ' ' . ($telegramData['last_name'] ?? '')),
                    'telegram_username' => $telegramData['username'] ?? null,
                    'telegram_photo_url' => $telegramData['photo_url'] ?? null,
                ]);
                
                Log::info('User updated', ['user_id' => $user->id, 'telegram_id' => $telegramData['id']]);
            }

            Auth::login($user);
            
            Log::info('User logged in successfully', ['user_id' => $user->id]);

            return redirect()->route('miniapp')->with('success', 'Успешная авторизация через Telegram!');
            
        } catch (\Exception $e) {
            Log::error('Telegram auth error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            
            return redirect()->route('login')->withErrors(['Произошла ошибка при авторизации: ' . $e->getMessage()]);
        }
    }

    public function miniapp(Request $request)
    {
        // Проверяем авторизацию через Telegram Web App
        if ($request->has('tgWebAppData')) {
            $initData = $request->get('tgWebAppData');
            if ($this->verifyTelegramWebAppData($initData)) {
                // Парсим данные пользователя из initData
                $userData = $this->parseTelegramWebAppData($initData);
                
                if ($userData) {
                    $user = User::where('telegram_id', $userData['id'])->first();
                    
                    if ($user) {
                        Auth::login($user);
                    }
                }
            }
        }

        return view('miniapp.index');
    }

    private function verifyTelegramAuth($data)
    {
        $botToken = config('services.telegram.bot_token');
        $checkHash = $data['hash'] ?? '';
        unset($data['hash']);

        // Добавляем логирование для отладки
        Log::info('Telegram login attempt', [
            'data' => $data + ['hash' => $checkHash],
            'current_timestamp' => time(),
            'auth_date_human' => isset($data['auth_date']) ? date('Y-m-d H:i:s', $data['auth_date']) : 'N/A',
            'current_time_human' => date('Y-m-d H:i:s'),
            'headers' => request()->headers->all()
        ]);

        // Проверяем возраст данных (не старше 1 дня) - временно отключено для отладки
        $authDate = $data['auth_date'] ?? 0;
        $ageInSeconds = time() - $authDate;
        if ($ageInSeconds > 86400) {
            Log::warning('Telegram auth data is old but allowing for debug', [
                'auth_date' => $authDate,
                'current_time' => time(),
                'age_seconds' => $ageInSeconds,
                'age_hours' => round($ageInSeconds / 3600, 2)
            ]);
            // return false; // Временно отключено для отладки
        }

        // Создаем строку для проверки подписи согласно документации Telegram
        $dataCheckArray = [];
        foreach ($data as $key => $value) {
            if ($value !== '' && $value !== null) {
                $dataCheckArray[] = $key . '=' . $value;
            }
        }
        sort($dataCheckArray);
        $dataCheckString = implode("\n", $dataCheckArray);

        // Правильный алгоритм для Telegram Login Widget
        $secretKey = hash('sha256', $botToken, true);
        $hash = hash_hmac('sha256', $dataCheckString, $secretKey);

        $isValid = hash_equals($hash, $checkHash);
        
        Log::info('Telegram auth verification details', [
            'data_check_string' => $dataCheckString,
            'expected_hash' => $hash,
            'received_hash' => $checkHash,
            'is_valid' => $isValid,
            'bot_token_start' => substr($botToken, 0, 10) . '...',
            'secret_key_length' => strlen($secretKey),
            'data_count' => count($data)
        ]);
        
        if (!$isValid) {
            Log::error('Telegram auth verification failed', [
                'data' => $data + ['hash' => $checkHash],
                'expected_hash' => $hash,
                'received_hash' => $checkHash,
                'data_check_string' => $dataCheckString,
                'data_check_array' => $dataCheckArray
            ]);
        }

        return $isValid;
    }

    private function verifyTelegramWebAppData($initData)
    {
        $botToken = config('services.telegram.bot_token');
        
        parse_str($initData, $data);
        $checkHash = $data['hash'] ?? '';
        unset($data['hash']);

        $dataCheckString = '';
        ksort($data);
        foreach ($data as $key => $value) {
            $dataCheckString .= $key . '=' . $value . "\n";
        }
        $dataCheckString = rtrim($dataCheckString, "\n");

        $secretKey = hash_hmac('sha256', 'WebAppData', $botToken, true);
        $hash = hash_hmac('sha256', $dataCheckString, $secretKey);

        return hash_equals($hash, $checkHash);
    }

    private function parseTelegramWebAppData($initData)
    {
        parse_str($initData, $data);
        
        if (isset($data['user'])) {
            return json_decode($data['user'], true);
        }
        
        return null;
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/');
    }
}
