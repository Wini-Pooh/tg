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
            --tg-theme-bg-color: #ffffff;
            --tg-theme-text-color: #000000;
            --tg-theme-hint-color: #999999;
            --tg-theme-link-color: #0088cc;
            --tg-theme-button-color: #0088cc;
            --tg-theme-button-text-color: #ffffff;
        }

        body {
            background-color: var(--tg-theme-bg-color);
            color: var(--tg-theme-text-color);
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            min-height: 100vh;
        }

        .miniapp-container {
            min-height: 100vh;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .welcome-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .user-info {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            margin: 20px 0;
        }

        .user-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: 3px solid #0088cc;
        }

        .btn-telegram {
            background: linear-gradient(135deg, #0088cc, #006699);
            border: none;
            border-radius: 10px;
            color: white;
            padding: 12px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-telegram:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 136, 204, 0.3);
            color: white;
        }

        .feature-card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 20px;
            margin: 10px 0;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .status-indicator {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 8px;
        }

        .status-success { background-color: #28a745; }
        .status-warning { background-color: #ffc107; }
        .status-error { background-color: #dc3545; }

        .debug-info {
            background: rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            padding: 15px;
            font-family: monospace;
            font-size: 12px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="miniapp-container">
        <div class="welcome-card">
            <h1>🚀 Добро пожаловать в Mini App!</h1>
            
            @if(Auth::check())
                <div class="user-info">
                    @if(Auth::user()->telegram_photo_url)
                        <img src="{{ Auth::user()->telegram_photo_url }}" alt="Avatar" class="user-avatar">
                    @else
                        <div class="user-avatar d-flex align-items-center justify-content-center bg-primary text-white">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                    @endif
                    <div>
                        <h4 class="mb-0">{{ Auth::user()->name }}</h4>
                        @if(Auth::user()->telegram_username)
                            <small class="text-muted">@{{ Auth::user()->telegram_username }}</small>
                        @endif
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="feature-card">
                            <h5>👤 Профиль</h5>
                            <p>Управление профилем пользователя</p>
                            <button class="btn btn-telegram btn-sm">Открыть</button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="feature-card">
                            <h5>📊 Статистика</h5>
                            <p>Просмотр аналитики и статистики</p>
                            <button class="btn btn-telegram btn-sm">Открыть</button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="feature-card">
                            <h5>⚙️ Настройки</h5>
                            <p>Конфигурация приложения</p>
                            <button class="btn btn-telegram btn-sm">Открыть</button>
                        </div>
                    </div>
                </div>
            @else
                <div id="auth-status">
                    <span class="status-indicator status-warning"></span>
                    Проверяем авторизацию через Telegram...
                </div>
                
                <div class="mt-3">
                    <button id="manual-auth" class="btn btn-telegram" style="display: none;">
                        Перейти к авторизации
                    </button>
                </div>
            @endif
        </div>

        @if(config('app.debug'))
            <div class="debug-info">
                <strong>Debug Information:</strong><br>
                User: {{ Auth::check() ? Auth::user()->name : 'Not authenticated' }}<br>
                Telegram ID: {{ Auth::check() ? Auth::user()->telegram_id : 'N/A' }}<br>
                Init Data: {{ isset($initData) && $initData ? 'Present' : 'Not provided' }}<br>
                <div id="tg-debug"></div>
            </div>
        @endif
    </div>

    <script>
        // Инициализация Telegram Web App
        let tg = window.Telegram.WebApp;
        
        // Настройка темы и интерфейса
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

        // Автоматическая авторизация
        @if(!Auth::check())
        if (tg.initData) {
            console.log('Attempting auto-auth with initData:', tg.initData);
            
            fetch('/api/miniapp/auth', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    initData: tg.initData
                })
            })
            .then(response => response.json())
            .then(data => {
                console.log('Auth response:', data);
                if (data.success) {
                    document.getElementById('auth-status').innerHTML = 
                        '<span class="status-indicator status-success"></span>Авторизация успешна! Перезагружаем...';
                    
                    // Перезагружаем страницу для отображения авторизованного состояния
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    document.getElementById('auth-status').innerHTML = 
                        '<span class="status-indicator status-error"></span>Ошибка авторизации: ' + (data.error || 'Неизвестная ошибка');
                    document.getElementById('manual-auth').style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Auth error:', error);
                document.getElementById('auth-status').innerHTML = 
                    '<span class="status-indicator status-error"></span>Ошибка соединения';
                document.getElementById('manual-auth').style.display = 'block';
            });
        } else {
            console.log('No initData available');
            document.getElementById('auth-status').innerHTML = 
                '<span class="status-indicator status-warning"></span>Нет данных Telegram для автоматической авторизации';
            document.getElementById('manual-auth').style.display = 'block';
        }
        @endif

        // Обработка кнопки ручной авторизации
        document.getElementById('manual-auth')?.addEventListener('click', function() {
            window.location.href = '/login';
        });

        // Debug информация
        if (document.getElementById('tg-debug')) {
            document.getElementById('tg-debug').innerHTML = 
                'Platform: ' + tg.platform + '<br>' +
                'Version: ' + tg.version + '<br>' +
                'Init Data Available: ' + (tg.initData ? 'Yes (' + tg.initData.length + ' chars)' : 'No') + '<br>' +
                'User ID: ' + (tg.initDataUnsafe.user ? tg.initDataUnsafe.user.id : 'N/A') + '<br>' +
                'User Name: ' + (tg.initDataUnsafe.user ? tg.initDataUnsafe.user.first_name : 'N/A');
        }

        // Показать кнопку "Назад" если нужно
        tg.BackButton.onClick(function() {
            tg.close();
        });

        // Функции для взаимодействия с ботом
        function showAlert(message) {
            tg.showAlert(message);
        }

        function showConfirm(message, callback) {
            tg.showConfirm(message, callback);
        }

        function closeApp() {
            tg.close();
        }

        // Уведомляем Telegram что приложение готово
        tg.sendData(JSON.stringify({
            action: 'ready',
            timestamp: Date.now(),
            user_id: {{ Auth::check() ? Auth::user()->telegram_id : 'null' }}
        }));

        // Логируем все данные для отладки
        console.log('Telegram WebApp initialized:', {
            version: tg.version,
            platform: tg.platform,
            initData: tg.initData,
            initDataUnsafe: tg.initDataUnsafe,
            themeParams: tg.themeParams
        });
    </script>
</body>
</html>
