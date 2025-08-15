<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\TelegramAuthController;
use App\Http\Controllers\TelegramMiniAppController;
use App\Http\Controllers\TelegramWebhookController;
use App\Http\Controllers\HomeController;

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
    return view('welcome');
});

// Telegram Authentication Routes
Route::get('/login', function () {
    return view('auth.telegram-login');
})->name('login');

Route::get('/telegram/login', [TelegramAuthController::class, 'login'])->name('telegram.login');
Route::get('/telegram/auth-redirect', [TelegramAuthController::class, 'authRedirect'])->name('telegram.auth.redirect');
Route::post('/telegram/dev-login', [TelegramAuthController::class, 'devLogin'])->name('telegram.dev.login');

// Telegram Mini App Routes
Route::get('/telegram/miniapp', [TelegramMiniAppController::class, 'index'])->name('telegram.miniapp');
Route::post('/telegram/miniapp/auth', [TelegramMiniAppController::class, 'auth'])->name('telegram.miniapp.auth');

// Telegram Webhook Route
Route::post('/telegram/webhook', [TelegramWebhookController::class, 'handle'])->name('telegram.webhook');

Route::post('/logout', function () {
    Auth::logout();
    return redirect('/');
})->name('logout');

Route::get('/home', [HomeController::class, 'index'])->name('home')->middleware('auth');
