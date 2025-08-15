<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @if(isset($initData) && $initData)
    <meta name="has-init-data" content="true">
    @endif
    @if(isset($isFromTelegram))
    <meta name="is-from-telegram" content="{{ $isFromTelegram ? 'true' : 'false' }}">
    @endif

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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }

        .miniapp-container {
            max-width: 100%;
            margin: 0 auto;
        }

        .welcome-card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            border: 1px solid rgba(0, 136, 204, 0.2);
            text-align: center;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            padding: 20px;
            background: rgba(0, 136, 204, 0.1);
            border-radius: 15px;
        }

        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #0088cc 0%, #006699 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
        }

        .btn-telegram {
            background: linear-gradient(135deg, #0088cc 0%, #006699 100%);
            border: none;
            color: white;
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            margin: 10px 5px;
        }

        .btn-telegram:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0, 136, 204, 0.3);
            color: white;
        }

        .feature-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            padding: 20px;
            margin: 15px 0;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }

        .status-success { background-color: #28a745; }
        .status-warning { background-color: #ffc107; }
        .status-error { background-color: #dc3545; }

        .loading {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--tg-theme-bg-color);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            z-index: 1000;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid rgba(0, 136, 204, 0.2);
            border-left: 4px solid #0088cc;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 20px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .welcome-message {
            text-align: center;
            margin: 30px 0;
        }

        .app-features {
            display: grid;
            gap: 15px;
            margin-top: 20px;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 15px;
        }

        @media (min-width: 480px) {
            .feature-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        .debug-info {
            background: rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            padding: 15px;
            margin: 20px 0;
            font-family: monospace;
            font-size: 0.8rem;
            border: 1px solid rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body>
    <div class="miniapp-container">
        <!-- Экран загрузки -->
        <div id="loading-screen" class="loading">
            <div class="spinner"></div>
            <div>Инициализация приложения...</div>
        </div>

        <!-- Основное содержимое -->
        <div id="main-content" style="display: none;">
            <div class="welcome-message">
                <h2>🚀 Mini App запущен!</h2>
                <p>Добро пожаловать в приложение</p>
            </div>

            @if(Auth::check())
            <div class="user-info">
                <div class="user-avatar">
                    {{ substr(Auth::user()->name, 0, 1) }}
                </div>
                <div>
                    <strong>{{ Auth::user()->name }}</strong><br>
                    <small class="text-muted">ID: {{ Auth::user()->telegram_id }}</small>
                </div>
            </div>
            @endif

            <div class="app-features">
                <div class="welcome-card">
                    <h4>✨ Приложение готово к работе!</h4>
                    <p>Все функции доступны и работают корректно</p>
                    
                    <div class="mt-3">
                        <span class="status-indicator status-success"></span>
                        <small>Соединение установлено</small>
                    </div>
                </div>

                <div class="feature-grid">
                    <div class="feature-card">
                        <h6>🔐 Авторизация</h6>
                        <p class="small mb-0">Автоматический вход через Telegram</p>
                    </div>
                    
                    <div class="feature-card">
                        <h6>📱 Интеграция</h6>
                        <p class="small mb-0">Полная поддержка Telegram Web App API</p>
                    </div>
                    
                    <div class="feature-card">
                        <h6>🔄 Синхронизация</h6>
                        <p class="small mb-0">Данные сохраняются автоматически</p>
                    </div>
                    
                    <div class="feature-card">
                        <h6>⚡ Скорость</h6>
                        <p class="small mb-0">Мгновенная загрузка и отклик</p>
                    </div>
                </div>
            </div>

            <div class="text-center mt-4">
                <button class="btn-telegram" onclick="refreshApp()">🔄 Обновить</button>
                @if(Auth::check())
                <button class="btn-telegram" onclick="showProfile()">👤 Профиль</button>
                @endif
            </div>
        </div>

        @if(config('app.debug'))
        <div class="debug-info" id="debug-info" style="display: none;">
            <strong>Debug Information:</strong><br>
            <div id="debug-content"></div>
        </div>
        @endif
    </div>

    <script>
        // Инициализация Telegram Web App
        let tg = window.Telegram.WebApp;
        let currentUser = null;
        let authInProgress = false;
        
        console.log('Starting Telegram WebApp initialization...');
        
        // Настройка темы и интерфейса
        tg.ready();
        tg.expand();
        
        // Применение темы Telegram
        if (tg.themeParams) {
            const root = document.documentElement;
            
            if (tg.themeParams.bg_color) {
                root.style.setProperty('--tg-theme-bg-color', tg.themeParams.bg_color);
                document.body.style.backgroundColor = tg.themeParams.bg_color;
            }
            if (tg.themeParams.text_color) {
                root.style.setProperty('--tg-theme-text-color', tg.themeParams.text_color);
                document.body.style.color = tg.themeParams.text_color;
            }
            if (tg.themeParams.hint_color) {
                root.style.setProperty('--tg-theme-hint-color', tg.themeParams.hint_color);
            }
            if (tg.themeParams.link_color) {
                root.style.setProperty('--tg-theme-link-color', tg.themeParams.link_color);
            }
            if (tg.themeParams.button_color) {
                root.style.setProperty('--tg-theme-button-color', tg.themeParams.button_color);
            }
            if (tg.themeParams.button_text_color) {
                root.style.setProperty('--tg-theme-button-text-color', tg.themeParams.button_text_color);
            }
        }

        // Автоматическая авторизация
        async function performAuth() {
            if (authInProgress) return;
            authInProgress = true;

            try {
                @if(!Auth::check())
                console.log('Attempting authentication...');
                if (tg.initData) {
                    console.log('InitData available, sending auth request...');
                    const response = await fetch('/api/miniapp/auth', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'X-Telegram-Init-Data': tg.initData
                        },
                        body: JSON.stringify({
                            initData: tg.initData
                        })
                    });

                    if (response.ok) {
                        const data = await response.json();
                        if (data.success) {
                            currentUser = data.user;
                            showUserInfo(data.user);
                            console.log('Auth successful:', data.user);
                            if (tg.showAlert) {
                                tg.showAlert('Добро пожаловать, ' + data.user.name + '!');
                            }
                        } else {
                            console.error('Auth failed:', data.message);
                        }
                    } else {
                        console.error('Auth request failed:', response.status);
                    }
                } else {
                    console.log('No initData available');
                }
                @else
                console.log('User already authenticated');
                @endif

                showMainContent();
            } catch (error) {
                console.error('Auth error:', error);
                showMainContent();
            } finally {
                authInProgress = false;
            }
        }

        function showUserInfo(user) {
            // Обновляем информацию о пользователе если нужно
            console.log('User info:', user);
        }

        function showMainContent() {
            document.getElementById('loading-screen').style.display = 'none';
            document.getElementById('main-content').style.display = 'block';
            console.log('Main content shown');
        }

        function showError(message) {
            document.getElementById('loading-screen').innerHTML = `
                <div class="text-center">
                    <div class="alert alert-danger">${message}</div>
                    <button class="btn-telegram" onclick="location.reload()">Попробовать снова</button>
                </div>
            `;
        }

        // Debug информация
        @if(config('app.debug'))
        function updateDebugInfo() {
            const debugContent = document.getElementById('debug-content');
            if (debugContent) {
                debugContent.innerHTML = `
                    Platform: ${tg.platform || 'Unknown'}<br>
                    Version: ${tg.version || 'Unknown'}<br>
                    InitData: ${tg.initData ? 'Present (' + tg.initData.length + ' chars)' : 'Missing'}<br>
                    ViewportHeight: ${tg.viewportHeight || 'Unknown'}<br>
                    ViewportStableHeight: ${tg.viewportStableHeight || 'Unknown'}<br>
                    IsExpanded: ${tg.isExpanded}<br>
                    User: ${JSON.stringify(tg.initDataUnsafe?.user || {})}<br>
                    ThemeParams: ${JSON.stringify(tg.themeParams || {})}<br>
                    Auth Status: {{ Auth::check() ? 'Authenticated' : 'Not authenticated' }}<br>
                    Laravel User: {{ Auth::check() ? Auth::user()->name : 'None' }}<br>
                    Current URL: ${window.location.href}<br>
                    Referrer: ${document.referrer || 'None'}
                `;
                document.getElementById('debug-info').style.display = 'block';
            }
        }
        
        // Показать debug info через 2 секунды
        setTimeout(updateDebugInfo, 2000);
        @endif

        // Функции для взаимодействия с приложением
        function refreshApp() {
            location.reload();
        }

        function showProfile() {
            if (tg.showAlert) {
                tg.showAlert('Функция профиля пока в разработке!');
            } else {
                alert('Функция профиля пока в разработке!');
            }
        }

        // Кнопка "Назад"
        if (tg.BackButton) {
            tg.BackButton.onClick(function() {
                tg.close();
            });
        }

        // Запуск приложения
        console.log('Scheduling auth in 1 second...');
        setTimeout(() => {
            performAuth();
        }, 1000);

        // Уведомляем Telegram что приложение готово
        tg.ready();
        
        // Логируем все данные для отладки
        console.log('Telegram WebApp initialized:', {
            platform: tg.platform,
            version: tg.version,
            initData: tg.initData ? 'Present (' + tg.initData.length + ' chars)' : 'Missing',
            themeParams: tg.themeParams,
            viewportHeight: tg.viewportHeight,
            user: tg.initDataUnsafe?.user,
            isFromTelegram: {{ $isFromTelegram ?? false ? 'true' : 'false' }}
        });
    </script>
</body>
</html>
