<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Принудительное использование HTTPS в продакшене
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
        
        // Принудительное использование HTTPS если запрос пришел через HTTPS
        if (request()->isSecure()) {
            URL::forceScheme('https');
        }
        
        // Для прокси серверов (nginx, cloudflare)
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            URL::forceScheme('https');
        }
    }
}
