# PowerShell скрипт настройки Telegram бота для РЕАЛЬНЫХ пользователей
# Версия для Windows

Write-Host "🤖 Настройка Te# 5. Настраиваем кнопку меню Mini App


Write-Host "🔧 Настройка кнопки Mini App..."
$menuData = @{
    menu_button = @{
        type = "web_app"
        text = "🚀 Открыть App"
        web_app = @{
            url = "$MINIAPP_URL"
        }
    }
} | ConvertTo-Json -Depth 3
Invoke-TelegramRequest -Method "setChatMenuButton" -Data $menuDataля РЕАЛЬНЫХ пользователей..." -ForegroundColor Cyan
Write-Host "=======================================================" -ForegroundColor Cyan

# Проверяем наличие .env файла
if (-not (Test-Path ".env")) {
    Write-Host "❌ Файл .env не найден!" -ForegroundColor Red
    exit 1
}

# Загружаем переменные окружения из .env
Get-Content ".env" | ForEach-Object {
    if ($_ -match "^([^#][^=]*?)=(.*)$") {
        [Environment]::SetEnvironmentVariable($matches[1], $matches[2])
    }
}

# Получаем переменные
$BOT_TOKEN = $env:TELEGRAM_BOT_TOKEN
$BOT_USERNAME = $env:TELEGRAM_BOT_USERNAME
$APP_URL = $env:APP_URL

# Проверяем обязательные переменные
if (-not $BOT_TOKEN) {
    Write-Host "❌ TELEGRAM_BOT_TOKEN не установлен в .env" -ForegroundColor Red
    exit 1
}

if (-not $BOT_USERNAME) {
    Write-Host "❌ TELEGRAM_BOT_USERNAME не установлен в .env" -ForegroundColor Red
    exit 1
}

if (-not $APP_URL) {
    Write-Host "❌ APP_URL не установлен в .env" -ForegroundColor Red
    exit 1
}

$DOMAIN = $APP_URL -replace "https?://", ""
$WEBHOOK_URL = "$APP_URL/api/telegram/webhook"
$MINIAPP_URL = "$APP_URL/miniapp"
$LOGIN_URL = "$APP_URL/login"

Write-Host ""
Write-Host "📋 Конфигурация:" -ForegroundColor Yellow
Write-Host "• Bot Token: $($BOT_TOKEN.Substring(0,10))***"
Write-Host "• Bot Username: @$BOT_USERNAME"
Write-Host "• Domain: $DOMAIN"
Write-Host "• Webhook: $WEBHOOK_URL"
Write-Host "• Mini App: $MINIAPP_URL"
Write-Host "• Login: $LOGIN_URL"
Write-Host ""

# Функция для выполнения API запросов
function Invoke-TelegramRequest {
    param(
        [string]$Method,
        [string]$Data
    )
    
    try {
        $uri = "https://api.telegram.org/bot$BOT_TOKEN/$Method"
        $headers = @{
            'Content-Type' = 'application/json'
        }
        
        $response = Invoke-RestMethod -Uri $uri -Method Post -Body $Data -Headers $headers
        
        if ($response.ok) {
            Write-Host "✅ $Method успешно выполнен" -ForegroundColor Green
            return $true
        } else {
            Write-Host "❌ $Method не выполнен: $($response.description)" -ForegroundColor Red
            return $false
        }
    }
    catch {
        Write-Host "❌ Ошибка при выполнении $Method`: $($_.Exception.Message)" -ForegroundColor Red
        return $false
    }
}

Write-Host "🚀 Начинаем настройку..." -ForegroundColor Green
Write-Host ""

# 1. Удаляем старый webhook
Write-Host "🔧 Удаление старого webhook..."
$deleteData = '{"drop_pending_updates": true}'
Invoke-TelegramRequest -Method "deleteWebhook" -Data $deleteData

# 2. Устанавливаем новый webhook
Write-Host "🔧 Установка webhook для реальных пользователей..."
$secretToken = -join ((1..32) | ForEach {'{0:X}' -f (Get-Random -Max 16)})
$webhookData = @{
    url = $WEBHOOK_URL
    allowed_updates = @("message", "edited_message", "callback_query", "inline_query")
    drop_pending_updates = $true
    secret_token = $secretToken
} | ConvertTo-Json
Invoke-TelegramRequest -Method "setWebhook" -Data $webhookData

# 3. Настраиваем команды бота
Write-Host "🔧 Настройка команд бота..."
$commandsData = @{
    commands = @(
        @{command = "start"; description = "🚀 Запустить приложение"},
        @{command = "app"; description = "📱 Открыть Mini App"},
        @{command = "login"; description = "🔐 Авторизация"},
        @{command = "help"; description = "❓ Помощь"}
    )
} | ConvertTo-Json -Depth 3
Invoke-TelegramRequest -Method "setMyCommands" -Data $commandsData

# 4. Настраиваем описание бота
Write-Host "🔧 Настройка описания бота..."
$descriptionData = @{
    description = "🤖 Официальный бот для доступа к приложению`n`n✨ Возможности:`n• 🔐 Безопасная авторизация через Telegram`n• 📱 Запуск Mini App`n• ⚡ Мгновенный доступ без паролей`n`n👤 Для реальных пользователей"
    short_description = "Официальный бот приложения"
} | ConvertTo-Json
Invoke-TelegramRequest -Method "setMyDescription" -Data $descriptionData

# 5. Настраиваем кнопку меню Mini App
Write-Host "🔧 Настройка кнопки Mini App..."
$menuData = @{
    menu_button = @{
        type = "web_app"
        text = "🚀 Открыть приложение"
        web_app = @{
            url = $MINIAPP_URL
        }
    }
} | ConvertTo-Json -Depth 3
Invoke-TelegramRequest -Method "setChatMenuButton" -Data $menuData

# 6. Проверяем настройки
Write-Host ""
Write-Host "🔍 Проверка настроек..." -ForegroundColor Yellow

# Информация о боте
Write-Host "📊 Информация о боте:"
try {
    $botInfo = Invoke-RestMethod -Uri "https://api.telegram.org/bot$BOT_TOKEN/getMe"
    $botInfo | ConvertTo-Json -Depth 3
}
catch {
    Write-Host "Ошибка получения информации о боте: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""

# Информация о webhook
Write-Host "📊 Информация о webhook:"
try {
    $webhookInfo = Invoke-RestMethod -Uri "https://api.telegram.org/bot$BOT_TOKEN/getWebhookInfo"
    $webhookInfo | ConvertTo-Json -Depth 3
}
catch {
    Write-Host "Ошибка получения информации о webhook: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "✅ НАСТРОЙКА ЗАВЕРШЕНА!" -ForegroundColor Green
Write-Host ""
Write-Host "🎯 Инструкции для РЕАЛЬНЫХ пользователей:" -ForegroundColor Cyan
Write-Host "=========================================="
Write-Host ""
Write-Host "1️⃣ Найдите бота в Telegram:"
Write-Host "   https://t.me/$BOT_USERNAME"
Write-Host ""
Write-Host "2️⃣ Отправьте команду /start"
Write-Host ""
Write-Host "3️⃣ Способы авторизации:"
Write-Host "   а) Нажмите кнопку 'Открыть приложение' в меню бота"
Write-Host "   б) Или перейдите на: $LOGIN_URL"
Write-Host ""
Write-Host "4️⃣ Авторизуйтесь через Telegram Login Widget"
Write-Host ""
Write-Host "🔗 Ссылки:" -ForegroundColor Yellow
Write-Host "• Бот: https://t.me/$BOT_USERNAME"
Write-Host "• Приложение: $MINIAPP_URL"
Write-Host "• Вход: $LOGIN_URL"
Write-Host ""
Write-Host "🛠️ Техническая информация:" -ForegroundColor Yellow
Write-Host "• Webhook: $WEBHOOK_URL"
Write-Host "• Проверка webhook: Invoke-RestMethod https://api.telegram.org/bot$BOT_TOKEN/getWebhookInfo"
Write-Host ""
Write-Host "⚠️ ВАЖНЫЕ ТРЕБОВАНИЯ:" -ForegroundColor Red
Write-Host "• ✅ SSL сертификат должен быть ВАЛИДНЫМ"
Write-Host "• ✅ Домен $DOMAIN должен быть доступен из интернета"
Write-Host "• ✅ Webhook должен отвечать HTTP 200"
Write-Host "• ✅ Порт должен быть 80, 88, 443 или 8443"
Write-Host ""
Write-Host "🎉 Бот готов для работы с реальными пользователями!" -ForegroundColor Green
