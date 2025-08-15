<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Mini App</title>

    <!-- Telegram Web App Script -->
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom Styles -->
    <style>
        :root {
            --tg-theme-bg-color: {{ config('telegram.theme.bg_color', '#ffffff') }};
            --tg-theme-text-color: {{ config('telegram.theme.text_color', '#000000') }};
            --tg-theme-hint-color: {{ config('telegram.theme.hint_color', '#999999') }};
            --tg-theme-link-color: {{ config('telegram.theme.link_color', '#0088cc') }};
            --tg-theme-button-color: {{ config('telegram.theme.button_color', '#0088cc') }};
            --tg-theme-button-text-color: {{ config('telegram.theme.button_text_color', '#ffffff') }};
        }

        body {
            background-color: var(--tg-theme-bg-color);
            color: var(--tg-theme-text-color);
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .miniapp-container {
            min-height: 100vh;
            padding: 20px;
        }

        .welcome-card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .user-info {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 15px;
        }

        .btn-telegram {
            background-color: var(--tg-theme-button-color);
            color: var(--tg-theme-button-text-color);
            border: none;
            border-radius: 8px;
            padding: 12px 24px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-telegram:hover {
            opacity: 0.8;
            color: var(--tg-theme-button-text-color);
        }

        .feature-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            border-left: 3px solid var(--tg-theme-link-color);
        }
    </style>
</head>
<body>
    <div class="miniapp-container">
        <div class="welcome-card">
            <div class="user-info">
                @if(Auth::user()->telegram_photo_url)
                    <img src="{{ Auth::user()->telegram_photo_url }}" alt="Avatar" class="user-avatar">
                @else
                    <div class="user-avatar" style="background: var(--tg-theme-link-color); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                        {{ substr(Auth::user()->name, 0, 1) }}
                    </div>
                @endif
                <div>
                    <h5 class="mb-1">{{ Auth::user()->name }}</h5>
                    @if(Auth::user()->telegram_username)
                        <small style="color: var(--tg-theme-hint-color);">@{{ Auth::user()->telegram_username }}</small>
                    @endif
                </div>
            </div>
            <h4>Добро пожаловать в Mini App!</h4>
            <p style="color: var(--tg-theme-hint-color);">Вы успешно авторизованы через Telegram</p>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="feature-card">
                    <h6>🚀 Быстрый доступ</h6>
                    <p class="mb-2" style="color: var(--tg-theme-hint-color);">Мгновенный вход без паролей</p>
                </div>
            </div>
            <div class="col-12">
                <div class="feature-card">
                    <h6>🔒 Безопасность</h6>
                    <p class="mb-2" style="color: var(--tg-theme-hint-color);">Авторизация через защищенный Telegram API</p>
                </div>
            </div>
            <div class="col-12">
                <div class="feature-card">
                    <h6>📱 Удобство</h6>
                    <p class="mb-2" style="color: var(--tg-theme-hint-color);">Работает прямо в Telegram</p>
                </div>
            </div>
        </div>

        <div class="mt-4 text-center">
            <button class="btn btn-telegram" onclick="showAlert()">
                Тестовая кнопка
            </button>
            
            <button class="btn btn-telegram ms-2" onclick="closeApp()">
                Закрыть приложение
            </button>
        </div>

        <div class="mt-4">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    Выйти из аккаунта
                </button>
            </form>
        </div>
    </div>

    <script>
        // Инициализация Telegram Web App
        let tg = window.Telegram.WebApp;
        
        // Настройка темы
        tg.ready();
        tg.expand();
        
        // Применение темы Telegram
        if (tg.themeParams) {
            document.documentElement.style.setProperty('--tg-theme-bg-color', tg.themeParams.bg_color || '#ffffff');
            document.documentElement.style.setProperty('--tg-theme-text-color', tg.themeParams.text_color || '#000000');
            document.documentElement.style.setProperty('--tg-theme-hint-color', tg.themeParams.hint_color || '#999999');
            document.documentElement.style.setProperty('--tg-theme-link-color', tg.themeParams.link_color || '#0088cc');
            document.documentElement.style.setProperty('--tg-theme-button-color', tg.themeParams.button_color || '#0088cc');
            document.documentElement.style.setProperty('--tg-theme-button-text-color', tg.themeParams.button_text_color || '#ffffff');
        }

        // Функции для взаимодействия с Telegram
        function showAlert() {
            tg.showAlert('Привет из Mini App!');
        }

        function closeApp() {
            tg.close();
        }

        // Обработка нажатия кнопки "Назад"
        tg.BackButton.onClick(function() {
            tg.close();
        });

        // Показать кнопку "Назад"
        tg.BackButton.show();

        // Отправка данных о готовности приложения
        tg.sendData(JSON.stringify({
            action: 'app_ready',
            user_id: {{ Auth::user()->telegram_id ?? 'null' }},
            timestamp: Date.now()
        }));
    </script>
</body>
</html>
