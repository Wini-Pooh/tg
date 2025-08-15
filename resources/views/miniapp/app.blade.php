<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @if($initData)
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
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .miniapp-container {
            min-height: 100vh;
            padding: 20px;
        }

        .welcome-card {
            background: var(--tg-theme-bg-color);
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 20px;
            border: 1px solid #e0e0e0;
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
            background: linear-gradient(135deg, var(--tg-theme-button-color), #006699);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            margin-right: 15px;
        }

        .btn-telegram {
            background: var(--tg-theme-button-color);
            color: var(--tg-theme-button-text-color);
            border: none;
            border-radius: 8px;
            padding: 12px 24px;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .btn-telegram:hover {
            background: #006699;
            color: white;
            transform: translateY(-2px);
        }

        .feature-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 12px;
            border-left: 4px solid var(--tg-theme-button-color);
        }

        .status-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }

        .status-success { background-color: #28a745; }
        .status-warning { background-color: #ffc107; }
        .status-error { background-color: #dc3545; }

        .loading {
            text-align: center;
            padding: 40px;
        }

        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid var(--tg-theme-button-color);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .welcome-message {
            text-align: center;
            margin-bottom: 30px;
        }

        .app-features {
            margin-top: 30px;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 15px;
            margin-top: 20px;
        }

        @media (min-width: 480px) {
            .feature-grid {
                grid-template-columns: 1fr 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="miniapp-container">
        <!-- Экран загрузки -->
        <div id="loading-screen" class="loading">
            <div class="spinner"></div>
            <h5>Загрузка приложения...</h5>
            <p class="text-muted">Выполняется автоматическая авторизация</p>
        </div>

        <!-- Основное содержимое -->
        <div id="main-content" style="display: none;">
            <div class="welcome-card">
                <div class="welcome-message">
                    <h4>🎉 Добро пожаловать!</h4>
                    <p class="text-muted">Ваш персональный кабинет в Telegram</p>
                </div>

                <!-- Информация о пользователе -->
                <div id="user-info" class="user-info" style="display: none;">
                    <div class="user-avatar" id="user-avatar">
                        U
                    </div>
                    <div>
                        <h6 id="user-name" class="mb-1">Пользователь</h6>
                        <small class="text-muted">
                            <span class="status-indicator status-success"></span>
                            Авторизован через Telegram
                        </small>
                    </div>
                </div>

                <!-- Возможности приложения -->
                <div class="app-features">
                    <h6>🚀 Возможности приложения:</h6>
                    <div class="feature-grid">
                        <div class="feature-card">
                            <strong>🔐 Безопасность</strong>
                            <br><small>Автоматическая авторизация через Telegram</small>
                        </div>
                        <div class="feature-card">
                            <strong>⚡ Скорость</strong>
                            <br><small>Мгновенный доступ без паролей</small>
                        </div>
                        <div class="feature-card">
                            <strong>📱 Удобство</strong>
                            <br><small>Работает прямо в Telegram</small>
                        </div>
                        <div class="feature-card">
                            <strong>🔄 Синхронизация</strong>
                            <br><small>Все данные связаны с вашим аккаунтом</small>
                        </div>
                    </div>
                </div>

                <!-- Действия -->
                <div class="mt-4 text-center">
                    <button class="btn-telegram" onclick="refreshApp()">
                        🔄 Обновить данные
                    </button>
                    <button class="btn-telegram ms-2" onclick="showProfile()">
                        👤 Мой профиль
                    </button>
                </div>
            </div>

            @if(config('app.debug'))
            <div class="mt-3">
                <details>
                    <summary class="text-muted">🔧 Отладочная информация</summary>
                    <div class="debug-info mt-2 p-3 bg-light rounded">
                        <strong>Telegram Web App данные:</strong>
                        <pre id="tg-debug" class="mt-2 small"></pre>
                        <strong>Статус авторизации:</strong>
                        <p id="auth-status" class="mt-1 small"></p>
                    </div>
                </details>
            </div>
            @endif
        </div>
    </div>

    <script>
        // Инициализация Telegram Web App
        let tg = window.Telegram.WebApp;
        let currentUser = null;
        let authInProgress = false;
        
        // Настройка темы и интерфейса
        tg.ready();
        tg.expand();

        // Проверяем, нужно ли перезагрузить страницу с initData в заголовке
        function checkInitDataReload() {
            if (tg.initData && !document.querySelector('meta[name="has-init-data"]')) {
                // Создаем мета-тег, чтобы избежать повторной перезагрузки
                const meta = document.createElement('meta');
                meta.name = 'has-init-data';
                meta.content = 'true';
                document.head.appendChild(meta);

                // Перезагружаем страницу с initData в URL параметрах
                const url = new URL(window.location);
                url.searchParams.set('tgWebAppData', tg.initData);
                window.location.href = url.toString();
                return true;
            }
            return false;
        }

        // Если нужна перезагрузка, выходим
        if (checkInitDataReload()) {
            return;
        }
        
        // Применение темы Telegram
        if (tg.themeParams) {
            const root = document.documentElement;
            if (tg.themeParams.bg_color) root.style.setProperty('--tg-theme-bg-color', tg.themeParams.bg_color);
            if (tg.themeParams.text_color) root.style.setProperty('--tg-theme-text-color', tg.themeParams.text_color);
            if (tg.themeParams.hint_color) root.style.setProperty('--tg-theme-hint-color', tg.themeParams.hint_color);
            if (tg.themeParams.link_color) root.style.setProperty('--tg-theme-link-color', tg.themeParams.link_color);
            if (tg.themeParams.button_color) root.style.setProperty('--tg-theme-button-color', tg.themeParams.button_color);
            if (tg.themeParams.button_text_color) root.style.setProperty('--tg-theme-button-text-color', tg.themeParams.button_text_color);
        }

        // Автоматическая авторизация
        async function performAuth() {
            if (authInProgress) {
                console.log('Authentication already in progress, skipping...');
                return;
            }
            
            authInProgress = true;
            
            // Диагностика состояния Telegram Web App
            console.log('=== Telegram Web App Diagnostic ===');
            console.log('Platform:', tg.platform);
            console.log('Version:', tg.version);
            console.log('initData exists:', !!tg.initData);
            console.log('initData length:', tg.initData ? tg.initData.length : 0);
            console.log('User Agent:', navigator.userAgent);
            console.log('Is from Telegram:', document.querySelector('meta[name="is-from-telegram"]')?.content);
            
            if (tg.initData) {
                console.log('initData (first 100 chars):', tg.initData.substring(0, 100) + '...');
            }
            
            try {
                // Сначала проверяем, есть ли уже авторизованный пользователь
                @if(Auth::check())
                    currentUser = {
                        id: {{ Auth::user()->id }},
                        name: "{{ Auth::user()->name }}",
                        telegram_id: {{ Auth::user()->telegram_id }},
                        telegram_username: "{{ Auth::user()->telegram_username ?? '' }}",
                        telegram_photo_url: "{{ Auth::user()->telegram_photo_url ?? '' }}"
                    };
                    showUserInfo(currentUser);
                    showMainContent();
                    console.log('User already authenticated on server');
                    return;
                @endif

                console.log('Attempting client-side authentication...');
                console.log('initData available:', !!tg.initData);
                
                // Если пользователь не авторизован, пытаемся авторизовать через Telegram Web App
                if (tg.initData) {
                    console.log('Sending auth request with initData...');
                    const response = await fetch('/api/miniapp/auth', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            initData: tg.initData
                        })
                    });

                    const result = await response.json();
                    console.log('Auth API response:', result);
                    
                    if (result.success) {
                        currentUser = result.user;
                        showUserInfo(currentUser);
                        showMainContent();
                        
                        // Убираем автоматическую перезагрузку - она больше не нужна
                        // поскольку авторизация уже работает корректно
                    } else {
                        throw new Error(result.error || 'Ошибка авторизации');
                    }
                } else {
                    console.log('No initData available, showing error');
                    throw new Error('Данные Telegram Web App недоступны');
                }
            } catch (error) {
                console.error('Ошибка авторизации:', error);
                showError(error.message);
            } finally {
                authInProgress = false;
            }
        }

        function showUserInfo(user) {
            document.getElementById('user-name').textContent = user.name;
            
            // Устанавливаем аватар
            const avatar = document.getElementById('user-avatar');
            if (user.telegram_photo_url) {
                avatar.innerHTML = `<img src="${user.telegram_photo_url}" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">`;
            } else {
                avatar.textContent = user.name.charAt(0).toUpperCase();
            }
            
            document.getElementById('user-info').style.display = 'flex';
        }

        function showMainContent() {
            document.getElementById('loading-screen').style.display = 'none';
            document.getElementById('main-content').style.display = 'block';
        }

        function showError(message) {
            document.getElementById('loading-screen').innerHTML = `
                <div class="text-center">
                    <div class="status-indicator status-error" style="width: 40px; height: 40px; margin: 0 auto 20px;"></div>
                    <h5>Ошибка авторизации</h5>
                    <p class="text-muted">${message}</p>
                    <button class="btn-telegram" onclick="window.location.reload()">Повторить</button>
                </div>
            `;
        }

        // Debug информация
        @if(config('app.debug'))
        function updateDebugInfo() {
            if (document.getElementById('tg-debug')) {
                document.getElementById('tg-debug').textContent = JSON.stringify({
                    initData: tg.initData ? tg.initData.substring(0, 200) + '...' : null,
                    user: tg.initDataUnsafe?.user,
                    themeParams: tg.themeParams,
                    version: tg.version,
                    platform: tg.platform,
                    userAgent: navigator.userAgent,
                    isFromTelegram: document.querySelector('meta[name="is-from-telegram"]')?.content,
                    hasInitData: !!tg.initData,
                    timestamp: new Date().toISOString()
                }, null, 2);
            }
            
            if (document.getElementById('auth-status')) {
                document.getElementById('auth-status').innerHTML = `
                    <strong>Статус:</strong> ${currentUser ? 'Авторизован' : 'Не авторизован'}<br>
                    <strong>Telegram Data:</strong> ${tg.initData ? 'Есть' : 'Отсутствует'}<br>
                    <strong>Platform:</strong> ${tg.platform}<br>
                    <strong>Version:</strong> ${tg.version}
                `;
            }
        }
            
            if (document.getElementById('auth-status')) {
                document.getElementById('auth-status').textContent = currentUser 
                    ? `Авторизован как ${currentUser.name} (ID: ${currentUser.telegram_id})`
                    : 'Не авторизован';
            }
        }
        @endif

        // Функции для взаимодействия с приложением
        function refreshApp() {
            tg.showAlert('Обновление данных...', () => {
                window.location.reload();
            });
        }

        function showProfile() {
            if (currentUser) {
                const message = `👤 Профиль:\n\n` +
                               `Имя: ${currentUser.name}\n` +
                               `Telegram ID: ${currentUser.telegram_id}\n` +
                               (currentUser.telegram_username ? `Username: @${currentUser.telegram_username}\n` : '') +
                               `ID в системе: ${currentUser.id}`;
                               
                tg.showAlert(message);
            }
        }

        // Кнопка "Назад"
        tg.BackButton.onClick(function() {
            tg.close();
        });

        // Уведомляем Telegram что приложение готово
        tg.sendData(JSON.stringify({
            status: 'ready',
            user_id: currentUser?.telegram_id || null
        }));

        // Запуск авторизации при загрузке
        document.addEventListener('DOMContentLoaded', () => {
            performAuth();
            
            @if(config('app.debug'))
            updateDebugInfo();
            @endif
        });

        console.log('Telegram WebApp initialized:', {
            version: tg.version,
            platform: tg.platform,
            themeParams: tg.themeParams,
            initData: !!tg.initData
        });
    </script>
</body>
</html>
