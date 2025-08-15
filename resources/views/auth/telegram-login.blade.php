<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Авторизация</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Figtree', sans-serif;
        }
        
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            max-width: 500px;
            width: 100%;
        }
        
        .card-header {
            background: linear-gradient(135deg, #0088cc 0%, #006699 100%);
            color: white;
            font-weight: 600;
            border-radius: 20px 20px 0 0;
            padding: 30px;
            text-align: center;
            font-size: 1.5rem;
        }
        
        .card-body {
            padding: 40px;
        }
        
        .telegram-login-widget {
            display: flex;
            justify-content: center;
            margin: 30px 0;
        }
        
        .welcome-text {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .welcome-text h4 {
            color: #333;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .app-logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #0088cc, #006699);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 2rem;
            font-weight: bold;
        }
        
        .footer-text {
            text-align: center;
            margin-top: 30px;
            font-size: 0.9rem;
            color: #666;
            line-height: 1.5;
        }
        
        .alert {
            border-radius: 10px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="card-header">
                <div class="app-logo">🚀</div>
                Авторизация через Telegram
            </div>

            <div class="card-body">
                <div class="welcome-text">
                    <h4>Добро пожаловать!</h4>
                    <p class="text-muted">Для входа в приложение используйте ваш аккаунт Telegram</p>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <strong>Ошибка:</strong> {{ $errors->first() }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger">
                        <strong>Ошибка:</strong> {{ session('error') }}
                    </div>
                @endif

                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="telegram-login-widget">
                    <script async src="https://telegram.org/js/telegram-widget.js?22" 
                            data-telegram-login="{{ config('services.telegram.bot_username') }}" 
                            data-size="large" 
                            data-auth-url="{{ route('telegram.callback') }}" 
                            data-request-access="write">
                    </script>
                </div>

                <div class="footer-text">
                    <p>
                        🔒 Безопасная авторизация через Telegram<br>
                        ⚡ Быстрый вход без паролей<br>
                        📱 Нажимая кнопку входа, вы соглашаетесь с передачей данных из Telegram
                    </p>
                </div>

                <div class="text-center mt-4">
                    <a href="{{ url('/') }}" class="btn btn-outline-secondary">
                        ← Вернуться на главную
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Дополнительная проверка загрузки виджета
        window.addEventListener('load', function() {
            console.log('Страница загружена');
            console.log('Bot username:', '{{ config("services.telegram.bot_username") }}');
            console.log('Callback URL:', '{{ route("telegram.callback") }}');
            
            // Проверяем, загрузился ли скрипт Telegram
            setTimeout(function() {
                const telegramWidget = document.querySelector('iframe[src*="oauth.telegram.org"]');
                if (!telegramWidget) {
                    console.error('Telegram widget не загрузился');
                    document.querySelector('.telegram-login-widget').innerHTML = 
                        '<div class="alert alert-warning">Виджет Telegram не загрузился. Попробуйте перезагрузить страницу.</div>';
                }
            }, 3000);
        });
    </script>
</body>
</html>

@section('styles')
<style>
.telegram-login-widget {
    display: inline-block;
    margin: 20px 0;
}

.card-body {
    padding: 2rem;
}

.card-header {
    background: linear-gradient(135deg, #0088cc 0%, #006699 100%);
    color: white;
    font-weight: 600;
}
</style>
@endsection
