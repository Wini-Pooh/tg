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
    if (Auth::check()) {
        return redirect()->route('miniapp');
    }
    return view('welcome');
});

// Telegram Authentication Routes
Route::get('/login', [TelegramAuthController::class, 'login'])->name('login');
Route::get('/login/test', function() { return view('auth.telegram-test'); })->name('login.test');
Route::get('/auth/telegram/callback', [TelegramAuthController::class, 'callback'])->name('telegram.callback');
Route::post('/logout', [TelegramAuthController::class, 'logout'])->name('logout');

// Mini App Routes
Route::get('/miniapp', [MiniAppController::class, 'index'])->name('miniapp');

Route::get('/home', [HomeController::class, 'index'])->name('home')->middleware('auth');
