<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} - Mini App</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Telegram Web App CSS -->
    <style>
        :root {
            --tg-theme-bg-color: #ffffff;
            --tg-theme-text-color: #000000;
            --tg-theme-hint-color: #999999;
            --tg-theme-link-color: #0088cc;
            --tg-theme-button-color: #0088cc;
            --tg-theme-button-text-color: #ffffff;
            --tg-theme-secondary-bg-color: #f1f1f1;
        }
        
        body {
            background-color: var(--tg-theme-bg-color);
            color: var(--tg-theme-text-color);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
        
        .miniapp-container {
            min-height: 100vh;
            padding: 20px 15px;
        }
        
        .user-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--tg-theme-button-color);
        }
        
        .telegram-button {
            background-color: var(--tg-theme-button-color);
            border: none;
            color: var(--tg-theme-button-text-color);
            border-radius: 12px;
            padding: 12px 24px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .telegram-button:hover {
            background-color: var(--tg-theme-button-color);
            opacity: 0.8;
            color: var(--tg-theme-button-text-color);
        }
        
        .card {
            background-color: var(--tg-theme-secondary-bg-color);
            border: none;
            border-radius: 16px;
            box-shadow: 0 2px 16px rgba(0, 0, 0, 0.1);
        }
        
        .feature-card {
            transition: transform 0.3s ease;
            cursor: pointer;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
        }
        
        .loading {
            display: none;
        }
        
        .animate-fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="miniapp-container">
        <!-- Loading Screen -->
        <div id="loadingScreen" class="loading text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Загрузка...</span>
            </div>
            <p class="mt-3">Инициализация...</p>
        </div>
        
        <!-- Main App -->
        <div id="mainApp" class="animate-fade-in">
            @if($user)
                <!-- Авторизованный пользователь -->
                <div class="text-center mb-4">
                    <h1 class="h3 mb-3">Добро пожаловать!</h1>
                    
                    @if($user->telegram_photo_url)
                        <img src="{{ $user->telegram_photo_url }}" alt="Avatar" class="user-avatar mb-3">
                    @else
                        <div class="user-avatar d-inline-flex align-items-center justify-content-center mb-3" style="background-color: var(--tg-theme-button-color);">
                            <i class="bi bi-person-fill text-white" style="font-size: 2rem;"></i>
                        </div>
                    @endif
                    
                    <h4 class="mb-1">{{ $user->name }}</h4>
                    @if($user->telegram_username)
                        <p class="text-muted">@{{ $user->telegram_username }}</p>
                    @endif
                </div>
                
                <!-- Функции приложения -->
                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <div class="card feature-card h-100" onclick="openProfile()">
                            <div class="card-body text-center">
                                <i class="bi bi-person-circle text-primary mb-2" style="font-size: 2rem;"></i>
                                <h6 class="card-title mb-0">Профиль</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card feature-card h-100" onclick="openSettings()">
                            <div class="card-body text-center">
                                <i class="bi bi-gear text-primary mb-2" style="font-size: 2rem;"></i>
                                <h6 class="card-title mb-0">Настройки</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card feature-card h-100" onclick="openNotifications()">
                            <div class="card-body text-center">
                                <i class="bi bi-bell text-primary mb-2" style="font-size: 2rem;"></i>
                                <h6 class="card-title mb-0">Уведомления</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card feature-card h-100" onclick="openHelp()">
                            <div class="card-body text-center">
                                <i class="bi bi-question-circle text-primary mb-2" style="font-size: 2rem;"></i>
                                <h6 class="card-title mb-0">Помощь</h6>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Информационная карточка -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="bi bi-info-circle text-primary me-2"></i>
                            Информация
                        </h6>
                        <p class="card-text small text-muted mb-2">
                            <strong>Telegram ID:</strong> {{ $user->telegram_id }}
                        </p>
                        <p class="card-text small text-muted mb-2">
                            <strong>Дата регистрации:</strong> {{ $user->created_at->format('d.m.Y') }}
                        </p>
                        <p class="card-text small text-muted mb-0">
                            Вы авторизованы через Telegram Mini App
                        </p>
                    </div>
                </div>
                
            @else
                <!-- Неавторизованный пользователь -->
                <div class="text-center">
                    <div class="mb-4">
                        <i class="bi bi-telegram text-primary" style="font-size: 4rem;"></i>
                    </div>
                    <h2 class="mb-3">{{ config('app.name') }}</h2>
                    <p class="text-muted mb-4">Для использования приложения необходима авторизация через Telegram</p>
                    
                    <button id="authButton" class="btn telegram-button btn-lg" onclick="authenticate()">
                        <i class="bi bi-shield-check me-2"></i>
                        Авторизоваться
                    </button>
                </div>
            @endif
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Telegram Web App JS -->
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    
    <script>
        // Инициализация Telegram Web App
        const tg = window.Telegram.WebApp;
        
        // Настройка темы
        document.documentElement.style.setProperty('--tg-theme-bg-color', tg.themeParams.bg_color || '#ffffff');
        document.documentElement.style.setProperty('--tg-theme-text-color', tg.themeParams.text_color || '#000000');
        document.documentElement.style.setProperty('--tg-theme-hint-color', tg.themeParams.hint_color || '#999999');
        document.documentElement.style.setProperty('--tg-theme-link-color', tg.themeParams.link_color || '#0088cc');
        document.documentElement.style.setProperty('--tg-theme-button-color', tg.themeParams.button_color || '#0088cc');
        document.documentElement.style.setProperty('--tg-theme-button-text-color', tg.themeParams.button_text_color || '#ffffff');
        document.documentElement.style.setProperty('--tg-theme-secondary-bg-color', tg.themeParams.secondary_bg_color || '#f1f1f1');
        
        // Уведомляем Telegram о готовности приложения
        tg.ready();
        
        // Разворачиваем приложение на весь экран
        tg.expand();
        
        // Функция авторизации
        async function authenticate() {
            const loadingScreen = document.getElementById('loadingScreen');
            const mainApp = document.getElementById('mainApp');
            const authButton = document.getElementById('authButton');
            
            authButton.disabled = true;
            authButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Авторизация...';
            
            try {
                const response = await fetch('/telegram/miniapp/auth', {
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
                
                if (result.success) {
                    // Перезагружаем страницу после успешной авторизации
                    window.location.reload();
                } else {
                    throw new Error(result.error || 'Ошибка авторизации');
                }
            } catch (error) {
                console.error('Ошибка авторизации:', error);
                tg.showAlert('Ошибка авторизации: ' + error.message);
                
                authButton.disabled = false;
                authButton.innerHTML = '<i class="bi bi-shield-check me-2"></i>Авторизоваться';
            }
        }
        
        // Функции для кнопок функций
        function openProfile() {
            tg.showAlert('Раздел "Профиль" в разработке');
        }
        
        function openSettings() {
            tg.showAlert('Раздел "Настройки" в разработке');
        }
        
        function openNotifications() {
            tg.showAlert('Раздел "Уведомления" в разработке');
        }
        
        function openHelp() {
            tg.showAlert('Если у вас возникли вопросы, обратитесь к администратору');
        }
        
        // Обработка событий Telegram Web App
        tg.onEvent('themeChanged', function() {
            // Обновляем тему при изменении
            location.reload();
        });
        
        // Показываем главное меню при загрузке
        tg.MainButton.hide();
        
        console.log('Telegram Web App инициализирован:', {
            version: tg.version,
            platform: tg.platform,
            initData: tg.initData,
            user: tg.initDataUnsafe.user
        });
    </script>
</body>
</html>
