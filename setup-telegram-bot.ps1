# Скрипт автоматической настройки Telegram бота для Mini App (PowerShell версия)

# Функции для цветного вывода
function Write-Info {
    param($Message)
    Write-Host "[INFO] $Message" -ForegroundColor Green
}

function Write-Error {
    param($Message)
    Write-Host "[ERROR] $Message" -ForegroundColor Red
}

function Write-Warning {
    param($Message)
    Write-Host "[WARNING] $Message" -ForegroundColor Yellow
}

function Write-Success {
    param($Message)
    Write-Host "$Message" -ForegroundColor Cyan
}

# Проверка наличия .env файла
if (-not (Test-Path ".env")) {
    Write-Error "Файл .env не найден!"
    exit 1
}

Write-Info "📁 Загружаем конфигурацию из .env..."

# Загрузка переменных из .env
Get-Content ".env" | ForEach-Object {
    if ($_ -match "^([^#].*)=(.*)$") {
        $name = $Matches[1]
        $value = $Matches[2].Trim('"')
        Set-Variable -Name $name -Value $value -Scope Global
    }
}

# Проверка обязательных переменных
if (-not $TELEGRAM_BOT_TOKEN) {
    Write-Error "TELEGRAM_BOT_TOKEN не найден в .env файле!"
    exit 1
}

if (-not $TELEGRAM_BOT_USERNAME) {
    Write-Error "TELEGRAM_BOT_USERNAME не найден в .env файле!"
    exit 1
}

if (-not $APP_URL) {
    Write-Error "APP_URL не найден в .env файле!"
    exit 1
}

Write-Info "🤖 Начинаем настройку Telegram бота..."
Write-Success "Bot Token: $($TELEGRAM_BOT_TOKEN.Substring(0, 10))..."
Write-Success "Bot Username: $TELEGRAM_BOT_USERNAME"
Write-Success "App URL: $APP_URL"

# Функция для выполнения HTTP запросов к Telegram API
function Invoke-TelegramAPI {
    param(
        [string]$Method,
        [hashtable]$Body
    )
    
    $uri = "https://api.telegram.org/bot$TELEGRAM_BOT_TOKEN/$Method"
    $jsonBody = $Body | ConvertTo-Json -Depth 10
    
    try {
        $response = Invoke-RestMethod -Uri $uri -Method Post -Body $jsonBody -ContentType "application/json"
        return $response
    }
    catch {
        Write-Error "Ошибка при вызове $Method : $($_.Exception.Message)"
        return $null
    }
}

# 1. Установка описания бота
Write-Info "📝 Устанавливаем описание бота..."
$descriptionResult = Invoke-TelegramAPI -Method "setMyDescription" -Body @{
    description = "🚀 Telegram Mini App с авторизацией через Telegram. Быстрый и безопасный вход без паролей!"
}
if ($descriptionResult.ok) {
    Write-Success "✅ Описание установлено"
} else {
    Write-Error "❌ Ошибка установки описания: $($descriptionResult.description)"
}

# 2. Установка короткого описания
Write-Info "📄 Устанавливаем короткое описание..."
$shortDescResult = Invoke-TelegramAPI -Method "setMyShortDescription" -Body @{
    short_description = "Mini App с Telegram авторизацией"
}
if ($shortDescResult.ok) {
    Write-Success "✅ Короткое описание установлено"
} else {
    Write-Error "❌ Ошибка установки короткого описания: $($shortDescResult.description)"
}

# 3. Установка команд бота
Write-Info "⚡ Устанавливаем команды бота..."
$commandsResult = Invoke-TelegramAPI -Method "setMyCommands" -Body @{
    commands = @(
        @{
            command = "start"
            description = "🚀 Запустить Mini App"
        },
        @{
            command = "app"
            description = "📱 Открыть приложение"
        },
        @{
            command = "help"
            description = "❓ Получить помощь"
        }
    )
}
if ($commandsResult.ok) {
    Write-Success "✅ Команды установлены"
} else {
    Write-Error "❌ Ошибка установки команд: $($commandsResult.description)"
}

# 4. Установка Menu Button для запуска Mini App
Write-Info "🎯 Устанавливаем кнопку меню для Mini App..."
$menuButtonResult = Invoke-TelegramAPI -Method "setChatMenuButton" -Body @{
    menu_button = @{
        type = "web_app"
        text = "🚀 Открыть App"
        web_app = @{
            url = "$APP_URL/miniapp"
        }
    }
}
if ($menuButtonResult.ok) {
    Write-Success "✅ Кнопка меню установлена"
} else {
    Write-Error "❌ Ошибка установки кнопки меню: $($menuButtonResult.description)"
}

# 5. Настройка webhook (опционально)
$setupWebhook = Read-Host "Хотите настроить webhook для получения обновлений? (y/n)"

if ($setupWebhook -eq "y" -or $setupWebhook -eq "Y") {
    Write-Info "🔗 Настраиваем webhook..."
    $webhookResult = Invoke-TelegramAPI -Method "setWebhook" -Body @{
        url = "$APP_URL/api/telegram/webhook"
        allowed_updates = @("message", "callback_query", "inline_query")
    }
    if ($webhookResult.ok) {
        Write-Success "✅ Webhook настроен"
    } else {
        Write-Error "❌ Ошибка настройки webhook: $($webhookResult.description)"
    }
} else {
    Write-Info "⏭️ Пропускаем настройку webhook"
}

# 6. Получение информации о боте
Write-Info "🔍 Получаем информацию о боте..."
$botInfo = Invoke-TelegramAPI -Method "getMe" -Body @{}
if ($botInfo.ok) {
    Write-Success "✅ Информация о боте получена:"
    Write-Host "  👤 Имя: $($botInfo.result.first_name)"
    Write-Host "  🆔 Username: @$($botInfo.result.username)"
    Write-Host "  🔢 ID: $($botInfo.result.id)"
    Write-Host "  🤖 Is Bot: $($botInfo.result.is_bot)"
} else {
    Write-Error "❌ Ошибка получения информации о боте"
}

# 7. Проверка статуса webhook
Write-Info "📡 Проверяем статус webhook..."
$webhookInfo = Invoke-TelegramAPI -Method "getWebhookInfo" -Body @{}
if ($webhookInfo.ok) {
    Write-Success "📊 Статус webhook:"
    Write-Host "  🔗 URL: $($webhookInfo.result.url)"
    Write-Host "  ✅ Pending Updates: $($webhookInfo.result.pending_update_count)"
    if ($webhookInfo.result.last_error_date) {
        Write-Warning "  ⚠️ Последняя ошибка: $($webhookInfo.result.last_error_message)"
    }
} else {
    Write-Error "❌ Ошибка получения статуса webhook"
}

# 8. Сводка
Write-Host ""
Write-Success "✅ Настройка бота завершена!"
Write-Host ""
Write-Host "📋 Сводка настроек:" -ForegroundColor Green
Write-Host "  🤖 Bot Username: @$TELEGRAM_BOT_USERNAME"
Write-Host "  🌐 Mini App URL: $APP_URL/miniapp"
Write-Host "  🔗 Login URL: $APP_URL/login"
Write-Host ""
Write-Host "📝 Что делать дальше:" -ForegroundColor Yellow
Write-Host "  1. Найдите бота @$TELEGRAM_BOT_USERNAME в Telegram"
Write-Host "  2. Отправьте команду /start"
Write-Host "  3. Нажмите кнопку '🚀 Открыть App' в меню"
Write-Host "  4. Или используйте команду /app"
Write-Host ""
Write-Host "💡 Полезные ссылки:" -ForegroundColor Cyan
Write-Host "  🔗 Прямая ссылка на бота: https://t.me/$TELEGRAM_BOT_USERNAME"
Write-Host "  📱 Mini App: https://t.me/$TELEGRAM_BOT_USERNAME/app"
Write-Host ""

Write-Success "🎉 Настройка завершена успешно!"

# Пауза перед закрытием
Read-Host "Нажмите Enter для выхода..."
