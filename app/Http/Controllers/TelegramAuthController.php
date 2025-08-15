<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TelegramAuthController extends Controller
{
    public function login(Request $request)
    {
        $telegramData = $request->all();
        
        // Проверяем подлинность данных от Telegram
        if (!$this->verifyTelegramAuth($telegramData)) {
            return redirect()->route('login')->with('error', 'Неверные данные авторизации Telegram');
        }
        
        return $this->processAuth($telegramData);
    }
    
    public function devLogin(Request $request)
    {
        // Только для разработки
        if (config('app.env') !== 'local') {
            abort(404);
        }
        
        $request->validate([
            'telegram_id' => 'required|numeric',
            'first_name' => 'required|string|max:255',
            'username' => 'nullable|string|max:255',
        ]);
        
        $telegramData = [
            'id' => $request->telegram_id,
            'first_name' => $request->first_name,
            'last_name' => '',
            'username' => $request->username,
            'photo_url' => null,
        ];
        
        return $this->processAuth($telegramData);
    }
    
   
    private function processAuth($telegramData)
    {
        // Ищем пользователя по telegram_id
        $user = User::where('telegram_id', $telegramData['id'])->first();
        
        if (!$user) {
            // Создаем нового пользователя
            $user = User::create([
                'name' => $telegramData['first_name'] . ' ' . ($telegramData['last_name'] ?? ''),
                'email' => $telegramData['id'] . '@telegram.user', // Фиктивный email
                'telegram_id' => $telegramData['id'],
                'telegram_username' => $telegramData['username'] ?? null,
                'telegram_photo_url' => $telegramData['photo_url'] ?? null,
                'password' => Hash::make(Str::random(16)), // Случайный пароль
                'email_verified_at' => now(),
            ]);
        } else {
            // Обновляем информацию о пользователе
            $user->update([
                'name' => $telegramData['first_name'] . ' ' . ($telegramData['last_name'] ?? ''),
                'telegram_username' => $telegramData['username'] ?? null,
                'telegram_photo_url' => $telegramData['photo_url'] ?? null,
            ]);
        }
        
        // Авторизуем пользователя
        Auth::login($user, true);
        
        return redirect()->intended('/home');
    }
   
    
    private function verifyTelegramAuth($data)
    {
        $botToken = config('services.telegram.bot_token');
        
        if (!$botToken) {
            return false;
        }
        
        $checkHash = $data['hash'] ?? '';
        unset($data['hash']);
        
        $dataCheckArr = [];
        foreach ($data as $key => $value) {
            $dataCheckArr[] = $key . '=' . $value;
        }
        sort($dataCheckArr);
        
        $dataCheckString = implode("\n", $dataCheckArr);
        $secretKey = hash('sha256', $botToken, true);
        $hash = hash_hmac('sha256', $dataCheckString, $secretKey);
        
        return hash_equals($hash, $checkHash);
    }
}
