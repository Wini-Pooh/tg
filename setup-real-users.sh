#!/bin/bash

# Скрипт настройки Telegram бота для РЕАЛЬНЫХ пользователей
# Обновленная версия для продакшена

echo "🤖 Настройка Telegram Bot для РЕАЛЬНЫХ пользователей..."
echo "======================================================="

# Загружаем переменные окружения
if [ -f .env ]; then
    export $(cat .env | grep -v '#' | awk '/=/ {print $0}')
else
    echo "❌ Файл .env не найден!"
    exit 1
fi

# Проверяем обязательные переменные
if [ -z "$TELEGRAM_BOT_TOKEN" ]; then
    echo "❌ TELEGRAM_BOT_TOKEN не установлен в .env"
    exit 1
fi

if [ -z "$TELEGRAM_BOT_USERNAME" ]; then
    echo "❌ TELEGRAM_BOT_USERNAME не установлен в .env"
    exit 1
fi

if [ -z "$APP_URL" ]; then
    echo "❌ APP_URL не установлен в .env"
    exit 1
fi

BOT_TOKEN="$TELEGRAM_BOT_TOKEN"
BOT_USERNAME="$TELEGRAM_BOT_USERNAME"
DOMAIN=$(echo "$APP_URL" | sed 's|https\?://||')
WEBHOOK_URL="$APP_URL/api/telegram/webhook"
MINIAPP_URL="$APP_URL/miniapp"
LOGIN_URL="$APP_URL/login"

echo "📋 Конфигурация:"
echo "• Bot Token: ${BOT_TOKEN:0:10}***"
echo "• Bot Username: @$BOT_USERNAME"
echo "• Domain: $DOMAIN"
echo "• Webhook: $WEBHOOK_URL"
echo "• Mini App: $MINIAPP_URL"
echo "• Login: $LOGIN_URL"
echo ""

# Функция для выполнения API запросов
telegram_request() {
    local method="$1"
    local data="$2"
    
    response=$(curl -s -w "HTTPSTATUS:%{http_code}" \
        -X POST "https://api.telegram.org/bot$BOT_TOKEN/$method" \
        -H "Content-Type: application/json" \
        -d "$data")
    
    http_code=$(echo $response | tr -d '\n' | sed -e 's/.*HTTPSTATUS://')
    body=$(echo $response | sed -e 's/HTTPSTATUS\:.*//g')
    
    if [ "$http_code" -eq 200 ]; then
        if echo "$body" | grep -q '"ok":true'; then
            echo "✅ $method успешно выполнен"
            return 0
        else
            echo "❌ $method не выполнен: $body"
            return 1
        fi
    else
        echo "❌ HTTP ошибка $http_code для $method: $body"
        return 1
    fi
}

echo "🚀 Начинаем настройку..."
echo ""

# 1. Удаляем старый webhook если есть
echo "🔧 Удаление старого webhook..."
telegram_request "deleteWebhook" "{\"drop_pending_updates\": true}"

# 2. Устанавливаем новый webhook
echo "🔧 Установка webhook для реальных пользователей..."
webhook_data="{
    \"url\": \"$WEBHOOK_URL\",
    \"allowed_updates\": [\"message\", \"edited_message\", \"callback_query\", \"inline_query\"],
    \"drop_pending_updates\": true,
    \"secret_token\": \"$(openssl rand -hex 32)\"
}"
telegram_request "setWebhook" "$webhook_data"

# 3. Настраиваем команды бота
echo "🔧 Настройка команд бота..."
commands_data="{
    \"commands\": [
        {\"command\": \"start\", \"description\": \"🚀 Запустить приложение\"},
        {\"command\": \"app\", \"description\": \"📱 Открыть Mini App\"},
        {\"command\": \"login\", \"description\": \"🔐 Авторизация\"},
        {\"command\": \"help\", \"description\": \"❓ Помощь\"}
    ]
}"
telegram_request "setMyCommands" "$commands_data"

# 4. Настраиваем описание бота
echo "🔧 Настройка описания бота..."
description_data="{
    \"description\": \"🤖 Официальный бот для доступа к приложению\n\n✨ Возможности:\n• 🔐 Безопасная авторизация через Telegram\n• 📱 Запуск Mini App\n• ⚡ Мгновенный доступ без паролей\n\n👤 Для реальных пользователей\",
    \"short_description\": \"Официальный бот приложения\"
}"
telegram_request "setMyDescription" "$description_data"

# 5. Настраиваем кнопку меню Mini App
echo "🔧 Настройка кнопки Mini App..."
menu_data="{
    \"menu_button\": {
        \"type\": \"web_app\",
        \"text\": \"🚀 Открыть приложение\",
        \"web_app\": {
            \"url\": \"$MINIAPP_URL\"
        }
    }
}"
telegram_request "setChatMenuButton" "$menu_data"

# 6. Устанавливаем домен для Login Widget
echo "🔧 Настройка домена для Login Widget..."
domain_data="{\"domain\": \"$DOMAIN\"}"
telegram_request "setMyDomain" "$domain_data" || echo "⚠️ setMyDomain может не поддерживаться"

# 7. Проверяем настройки
echo ""
echo "🔍 Проверка настроек..."

# Информация о боте
echo "📊 Информация о боте:"
bot_info=$(curl -s "https://api.telegram.org/bot$BOT_TOKEN/getMe")
echo "$bot_info" | python3 -m json.tool 2>/dev/null || echo "$bot_info"

echo ""

# Информация о webhook
echo "📊 Информация о webhook:"
webhook_info=$(curl -s "https://api.telegram.org/bot$BOT_TOKEN/getWebhookInfo")
echo "$webhook_info" | python3 -m json.tool 2>/dev/null || echo "$webhook_info"

echo ""
echo "✅ НАСТРОЙКА ЗАВЕРШЕНА!"
echo ""
echo "🎯 Инструкции для РЕАЛЬНЫХ пользователей:"
echo "=========================================="
echo ""
echo "1️⃣ Найдите бота в Telegram:"
echo "   https://t.me/$BOT_USERNAME"
echo ""
echo "2️⃣ Отправьте команду /start"
echo ""
echo "3️⃣ Способы авторизации:"
echo "   а) Нажмите кнопку 'Открыть приложение' в меню бота"
echo "   б) Или перейдите на: $LOGIN_URL"
echo ""
echo "4️⃣ Авторизуйтесь через Telegram Login Widget"
echo ""
echo "🔗 Ссылки:"
echo "• Бот: https://t.me/$BOT_USERNAME"
echo "• Приложение: $MINIAPP_URL"
echo "• Вход: $LOGIN_URL"
echo ""
echo "🛠️ Техническая информация:"
echo "• Webhook: $WEBHOOK_URL"
echo "• Статус webhook: curl https://api.telegram.org/bot$BOT_TOKEN/getWebhookInfo"
echo ""
echo "⚠️ ВАЖНЫЕ ТРЕБОВАНИЯ:"
echo "• ✅ SSL сертификат должен быть ВАЛИДНЫМ"
echo "• ✅ Домен $DOMAIN должен быть доступен из интернета"
echo "• ✅ Webhook должен отвечать HTTP 200"
echo "• ✅ Порт должен быть 80, 88, 443 или 8443"
echo ""
echo "🎉 Бот готов для работы с реальными пользователями!"
