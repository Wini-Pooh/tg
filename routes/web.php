<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\TelegramAuthController;
use App\Http\Controllers\MiniAppController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    // Всегда перенаправляем на Mini App - авторизация произойдет автоматически
    return redirect()->route('miniapp');
});

// Mini App Routes - главная точка входа
Route::get('/miniapp', [MiniAppController::class, 'index'])->name('miniapp');

// Дополнительные маршруты (оставляем для совместимости, но скрываем)
Route::get('/login', function() {
    return redirect()->route('miniapp');
})->name('login');

Route::get('/auth/telegram/callback', [TelegramAuthController::class, 'callback'])->name('telegram.callback');
Route::post('/logout', [TelegramAuthController::class, 'logout'])->name('logout');

Route::get('/home', [HomeController::class, 'index'])->name('home')->middleware('auth');
