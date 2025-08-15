#!/bin/bash

# Bash скрипт настройки Telegram бота для РЕАЛЬНЫХ пользователей
# Версия для Linux/Unix

# Цвета для вывода
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

echo -e "${CYAN}🤖 Настройка Telegram Bot для РЕАЛЬНЫХ пользователей...${NC}"
echo -e "${CYAN}=======================================================${NC}"

# Проверяем наличие .env файла
if [ ! -f ".env" ]; then
    echo -e "${RED}❌ Файл .env не найден!${NC}"
    exit 1
fi

# Загружаем переменные из .env
export $(grep -v '^#' .env | xargs)

# Проверяем обязательные переменные
if [ -z "$TELEGRAM_BOT_TOKEN" ]; then
    echo -e "${RED}❌ TELEGRAM_BOT_TOKEN не установлен в .env${NC}"
    exit 1
fi

if [ -z "$TELEGRAM_BOT_USERNAME" ]; then
    echo -e "${RED}❌ TELEGRAM_BOT_USERNAME не установлен в .env${NC}"
    exit 1
fi

if [ -z "$APP_URL" ]; then
    echo -e "${RED}❌ APP_URL не установлен в .env${NC}"
    exit 1
fi

BOT_TOKEN=$TELEGRAM_BOT_TOKEN
BOT_USERNAME=$TELEGRAM_BOT_USERNAME
DOMAIN=$(echo $APP_URL | sed 's/https\?:\/\///')
WEBHOOK_URL="$APP_URL/api/telegram/webhook"
MINIAPP_URL="$APP_URL/miniapp"
LOGIN_URL="$APP_URL/login"

echo ""
echo -e "${YELLOW}📋 Конфигурация:${NC}"
echo -e "• Bot Token: ${BOT_TOKEN:0:10}***"
echo -e "• Bot Username: @$BOT_USERNAME"
echo -e "• Domain: $DOMAIN"
echo -e "• Webhook: $WEBHOOK_URL"
echo -e "• Mini App: $MINIAPP_URL"
echo -e "• Login: $LOGIN_URL"
echo ""

# Функция для выполнения API запросов
telegram_request() {
    local method=$1
    local data=$2
    local url="https://api.telegram.org/bot$BOT_TOKEN/$method"
    
    echo -e "${BLUE}🔧 Выполнение $method...${NC}"
    
    response=$(curl -s -X POST \
        -H "Content-Type: application/json" \
        -d "$data" \
        "$url")
    
    if echo "$response" | grep -q '"ok":true'; then
        echo -e "${GREEN}✅ $method успешно выполнен${NC}"
        return 0
    else
        echo -e "${RED}❌ $method не выполнен:${NC}"
        echo "$response"
        return 1
    fi
}

echo -e "${GREEN}🚀 Начинаем настройку...${NC}"
echo ""

# 1. Удаляем старый webhook
telegram_request "deleteWebhook" '{"drop_pending_updates": true}'

# 2. Устанавливаем новый webhook
SECRET_TOKEN=$(openssl rand -hex 16 2>/dev/null || date +%s | sha256sum | base64 | head -c 32)
webhook_data="{
    \"url\": \"$WEBHOOK_URL\",
    \"allowed_updates\": [\"message\", \"edited_message\", \"callback_query\", \"inline_query\"],
    \"drop_pending_updates\": true,
    \"secret_token\": \"$SECRET_TOKEN\"
}"
telegram_request "setWebhook" "$webhook_data"

# 3. Настраиваем команды бота
commands_data='{
    "commands": [
        {"command": "start", "description": "🚀 Запустить приложение"},
        {"command": "app", "description": "📱 Открыть Mini App"},
        {"command": "login", "description": "🔐 Авторизация"},
        {"command": "help", "description": "❓ Помощь"}
    ]
}'
telegram_request "setMyCommands" "$commands_data"

# 4. Настраиваем описание бота
description_data='{
    "description": "🤖 Официальный бот для доступа к приложению\n\n✨ Возможности:\n• 🔐 Безопасная авторизация через Telegram\n• 📱 Запуск Mini App\n• ⚡ Мгновенный доступ без паролей\n\n👤 Для реальных пользователей",
    "short_description": "Официальный бот приложения"
}'
telegram_request "setMyDescription" "$description_data"

# 5. Настраиваем кнопку меню Mini App
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

# 6. Проверяем настройки
echo ""
echo -e "${YELLOW}🔍 Проверка настроек...${NC}"

# Информация о боте
echo -e "${BLUE}📊 Информация о боте:${NC}"
bot_info=$(curl -s "https://api.telegram.org/bot$BOT_TOKEN/getMe")
echo "$bot_info"

echo ""

# Информация о webhook
echo -e "${BLUE}📊 Информация о webhook:${NC}"
webhook_info=$(curl -s "https://api.telegram.org/bot$BOT_TOKEN/getWebhookInfo")
echo "$webhook_info"

echo ""
echo -e "${GREEN}✅ НАСТРОЙКА ЗАВЕРШЕНА!${NC}"
echo ""
echo -e "${CYAN}🎯 Инструкции для РЕАЛЬНЫХ пользователей:${NC}"
echo -e "${CYAN}==========================================${NC}"
echo ""
echo -e "${YELLOW}1️⃣ Найдите бота в Telegram:${NC}"
echo -e "   https://t.me/$BOT_USERNAME"
echo ""
echo -e "${YELLOW}2️⃣ Отправьте команду /start${NC}"
echo ""
echo -e "${YELLOW}3️⃣ Способы запуска приложения:${NC}"
echo -e "   а) Нажмите кнопку '🚀 Открыть приложение' в меню бота"
echo -e "   б) Используйте команду /app"
echo -e "   в) Или нажмите на кнопку в сообщении бота"
echo ""
echo -e "${YELLOW}4️⃣ Авторизация происходит автоматически через Telegram Mini App${NC}"
echo ""
echo -e "${BLUE}🔗 Ссылки:${NC}"
echo -e "• Бот: https://t.me/$BOT_USERNAME"
echo -e "• Mini App: $MINIAPP_URL"
echo -e "• Альтернативный вход: $LOGIN_URL"
echo ""
echo -e "${BLUE}🛠️ Техническая информация:${NC}"
echo -e "• Webhook: $WEBHOOK_URL"
echo -e "• Secret Token: $SECRET_TOKEN"
echo ""
echo -e "${RED}⚠️ ВАЖНЫЕ ТРЕБОВАНИЯ:${NC}"
echo -e "• ✅ SSL сертификат должен быть ВАЛИДНЫМ"
echo -e "• ✅ Домен $DOMAIN должен быть доступен из интернета"
echo -e "• ✅ Webhook должен отвечать HTTP 200"
echo -e "• ✅ Порт должен быть 80, 88, 443 или 8443"
echo ""
echo -e "${GREEN}🎉 Бот готов для работы с реальными пользователями!${NC}"
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
