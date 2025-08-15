<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Инструкция по использованию</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom Styles -->
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .instruction-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .instruction-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            max-width: 600px;
            width: 100%;
            overflow: hidden;
        }
        
        .card-header {
            background: linear-gradient(135deg, #0088cc 0%, #006699 100%);
            color: white;
            font-weight: 600;
            padding: 30px;
            text-align: center;
        }
        
        .card-body {
            padding: 40px;
        }
        
        .telegram-logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #0088cc, #229ed9);
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: white;
        }
        
        .step {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #0088cc;
        }
        
        .step-number {
            background: #0088cc;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 15px;
        }
        
        .btn-telegram {
            background: linear-gradient(135deg, #0088cc, #229ed9);
            border: none;
            color: white;
            padding: 15px 30px;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: transform 0.2s;
        }
        
        .btn-telegram:hover {
            transform: translateY(-2px);
            color: white;
        }
        
        .warning-note {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 10px;
            padding: 15px;
            margin: 20px 0;
        }
        
        .qr-placeholder {
            background: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            padding: 30px;
            text-align: center;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="instruction-container">
        <div class="instruction-card">
            <div class="card-header">
                <div class="telegram-logo">
                    📱
                </div>
                <h2 class="mb-0">Как использовать приложение</h2>
                <p class="mb-0 mt-2">Это приложение работает только через Telegram</p>
            </div>
            
            <div class="card-body">
                <div class="warning-note">
                    <strong>⚠️ Внимание!</strong> Вы открыли приложение в браузере. Для полного функционала используйте Telegram.
                </div>
                
                <h4 class="mb-4">📋 Инструкция по запуску:</h4>
                
                <div class="step">
                    <span class="step-number">1</span>
                    <strong>Откройте Telegram</strong><br>
                    Запустите приложение Telegram на своем устройстве
                </div>
                
                <div class="step">
                    <span class="step-number">2</span>
                    <strong>Найдите нашего бота</strong><br>
                    @if($botUsername)
                        Найдите бота <strong>@{{ $botUsername }}</strong> в поиске Telegram
                    @else
                        Найдите нашего бота в поиске Telegram
                    @endif
                </div>
                
                <div class="step">
                    <span class="step-number">3</span>
                    <strong>Отправьте команду /start</strong><br>
                    Напишите боту команду <code>/start</code> чтобы начать работу
                </div>
                
                <div class="step">
                    <span class="step-number">4</span>
                    <strong>Нажмите "Открыть приложение"</strong><br>
                    Бот пришлет кнопку для запуска Mini App - нажмите на неё
                </div>
                
                @if($botUsername)
                <div class="text-center mt-4">
                    <a href="https://t.me/{{ $botUsername }}" class="btn-telegram" target="_blank">
                        🚀 Открыть бота в Telegram
                    </a>
                </div>
                @endif
                
                <div class="qr-placeholder">
                    <h5>📱 Или отсканируйте QR-код</h5>
                    @if($botUsername)
                    <p>Отсканируйте этот QR-код камерой телефона для быстрого перехода к боту:</p>
                    <div id="qrcode" class="mt-3"></div>
                    @else
                    <p>QR-код будет доступен после настройки бота</p>
                    @endif
                </div>
                
                <div class="mt-4">
                    <h5>🔧 Для разработчиков:</h5>
                    <ul>
                        <li>Убедитесь, что настроен <code>TELEGRAM_BOT_TOKEN</code></li>
                        <li>Убедитесь, что настроен <code>TELEGRAM_BOT_USERNAME</code></li>
                        <li>Проверьте настройки Mini App в BotFather</li>
                        <li>URL Mini App должен быть: <code>{{ $appUrl }}/miniapp</code></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- QR Code generation -->
    @if($botUsername)
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
    <script>
        QRCode.toCanvas(document.getElementById('qrcode'), 'https://t.me/{{ $botUsername }}', {
            width: 200,
            margin: 2,
            color: {
                dark: '#000000',
                light: '#ffffff'
            }
        });
    </script>
    @endif
</body>
</html>
