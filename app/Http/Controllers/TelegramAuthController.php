<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TelegramAuthController extends Controller
{
    public function login(Request $request)
    {
        return view('auth.telegram-login');
    }

    public function callback(Request $request)
    {
        // Проверяем данные от Telegram
        if (!$this->verifyTelegramAuth($request->all())) {
            return redirect()->route('login')->withErrors(['error' => 'Неверные данные авторизации']);
        }

        $telegramData = $request->all();
        
        // Ищем или создаем пользователя
        $user = User::where('telegram_id', $telegramData['id'])->first();
        
        if (!$user) {
            $user = User::create([
                'name' => $telegramData['first_name'] . ' ' . ($telegramData['last_name'] ?? ''),
                'email' => 'telegram_' . $telegramData['id'] . '@example.com',
                'password' => Hash::make(Str::random(32)),
                'telegram_id' => $telegramData['id'],
                'telegram_username' => $telegramData['username'] ?? null,
                'telegram_photo_url' => $telegramData['photo_url'] ?? null,
            ]);
        } else {
            // Обновляем данные пользователя
            $user->update([
                'name' => $telegramData['first_name'] . ' ' . ($telegramData['last_name'] ?? ''),
                'telegram_username' => $telegramData['username'] ?? null,
                'telegram_photo_url' => $telegramData['photo_url'] ?? null,
            ]);
        }

        Auth::login($user);

        return redirect()->route('miniapp');
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

        $dataCheckString = '';
        ksort($data);
        foreach ($data as $key => $value) {
            if ($value !== '') {
                $dataCheckString .= $key . '=' . $value . "\n";
            }
        }
        $dataCheckString = rtrim($dataCheckString, "\n");

        $secretKey = hash('sha256', $botToken, true);
        $hash = hash_hmac('sha256', $dataCheckString, $secretKey);

        return hash_equals($hash, $checkHash);
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
