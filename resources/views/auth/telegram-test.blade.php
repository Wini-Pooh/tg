<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Тест Telegram Widget</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 20px; 
            background: #f5f5f5;
        }
        .container { 
            max-width: 600px; 
            margin: 0 auto; 
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .info { 
            background: #e3f2fd; 
            padding: 15px; 
            border-radius: 5px; 
            margin: 15px 0;
        }
        .debug { 
            background: #f3e5f5; 
            padding: 15px; 
            border-radius: 5px; 
            margin: 15px 0;
            font-family: monospace;
            font-size: 12px;
        }
        .widget-container {
            text-align: center;
            margin: 30px 0;
            padding: 20px;
            border: 2px dashed #ccc;
            border-radius: 10px;
        }
        h1 { color: #333; text-align: center; }
        h2 { color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Тест Telegram Login Widget</h1>
        
        <div class="info">
            <h2>Информация о боте:</h2>
            <p><strong>Username:</strong> {{ config('services.telegram.bot_username') }}</p>
            <p><strong>Token:</strong> {{ substr(config('services.telegram.bot_token'), 0, 10) }}...</p>
            <p><strong>Callback URL:</strong> {{ route('telegram.callback') }}</p>
        </div>

        <div class="widget-container">
            <h2>Виджет авторизации:</h2>
            <script async src="https://telegram.org/js/telegram-widget.js?22" 
                    data-telegram-login="{{ config('services.telegram.bot_username') }}" 
                    data-size="large" 
                    data-auth-url="{{ route('telegram.callback') }}" 
                    data-request-access="write"
                    data-onauth="onTelegramAuth(user)">
            </script>
        </div>

        <div class="debug">
            <h2>Отладочная информация:</h2>
            <div id="debug-info">Ожидание загрузки виджета...</div>
        </div>

        <div style="text-align: center; margin-top: 30px;">
            <a href="{{ route('login') }}" style="color: #0088cc; text-decoration: none;">← Назад к основной странице логина</a>
        </div>
    </div>

    <script>
        // Функция обратного вызова для успешной авторизации
        function onTelegramAuth(user) {
            console.log('Telegram auth successful:', user);
            document.getElementById('debug-info').innerHTML += '<br><strong>Авторизация успешна!</strong><br>' + JSON.stringify(user, null, 2);
        }

        // Отладочная информация
        window.addEventListener('load', function() {
            const debugInfo = document.getElementById('debug-info');
            
            debugInfo.innerHTML = 
                'Страница загружена: ' + new Date().toLocaleString() + '<br>' +
                'Bot username: {{ config("services.telegram.bot_username") }}<br>' +
                'Callback URL: {{ route("telegram.callback") }}<br>' +
                'User Agent: ' + navigator.userAgent + '<br>';

            // Проверяем загрузку виджета
            setTimeout(function() {
                const iframe = document.querySelector('iframe[src*="oauth.telegram.org"]');
                if (iframe) {
                    debugInfo.innerHTML += '<br><strong style="color: green;">✅ Виджет Telegram загружен успешно</strong>';
                    debugInfo.innerHTML += '<br>Iframe src: ' + iframe.src;
                } else {
                    debugInfo.innerHTML += '<br><strong style="color: red;">❌ Виджет Telegram НЕ загрузился</strong>';
                    debugInfo.innerHTML += '<br>Возможные причины:';
                    debugInfo.innerHTML += '<br>- Неправильный username бота';
                    debugInfo.innerHTML += '<br>- Проблемы с сетью';
                    debugInfo.innerHTML += '<br>- Блокировка содержимого браузером';
                }
            }, 3000);

            // Проверяем ошибки скрипта
            window.addEventListener('error', function(e) {
                debugInfo.innerHTML += '<br><strong style="color: red;">Ошибка JavaScript:</strong> ' + e.message;
            });
        });
    </script>
</body>
</html>
