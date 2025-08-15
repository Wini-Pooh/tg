#!/bin/bash

# Скрипт автоматической настройки Telegram бота для Mini App

# Цвета для вывода
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Функция для логирования
log() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

# Получение данных из .env
if [ ! -f ".env" ]; then
    error "Файл .env не найден!"
    exit 1
fi

# Загружаем переменные из .env
export $(grep -v '^#' .env | xargs)

# Проверяем наличие токена бота
if [ -z "$TELEGRAM_BOT_TOKEN" ]; then
    error "TELEGRAM_BOT_TOKEN не найден в .env файле!"
    exit 1
fi

if [ -z "$TELEGRAM_BOT_USERNAME" ]; then
    error "TELEGRAM_BOT_USERNAME не найден в .env файле!"
    exit 1
fi

if [ -z "$APP_URL" ]; then
    error "APP_URL не найден в .env файле!"
    exit 1
fi

log "🤖 Начинаем настройку Telegram бота..."
log "Bot Token: ${TELEGRAM_BOT_TOKEN:0:10}..."
log "Bot Username: $TELEGRAM_BOT_USERNAME"
log "App URL: $APP_URL"

# 1. Установка описания бота
log "📝 Устанавливаем описание бота..."
curl -s -X POST "https://api.telegram.org/bot$TELEGRAM_BOT_TOKEN/setMyDescription" \
  -H "Content-Type: application/json" \
  -d '{
    "description": "🚀 Telegram Mini App с авторизацией через Telegram. Быстрый и безопасный вход без паролей!"
  }' | jq '.'

# 2. Установка короткого описания
log "📄 Устанавливаем короткое описание..."
curl -s -X POST "https://api.telegram.org/bot$TELEGRAM_BOT_TOKEN/setMyShortDescription" \
  -H "Content-Type: application/json" \
  -d '{
    "short_description": "Mini App с Telegram авторизацией"
  }' | jq '.'

# 3. Установка команд бота
log "⚡ Устанавливаем команды бота..."
curl -s -X POST "https://api.telegram.org/bot$TELEGRAM_BOT_TOKEN/setMyCommands" \
  -H "Content-Type: application/json" \
  -d '{
    "commands": [
      {
        "command": "start",
        "description": "🚀 Запустить Mini App"
      },
      {
        "command": "app",
        "description": "📱 Открыть приложение"
      },
      {
        "command": "help",
        "description": "❓ Получить помощь"
      }
    ]
  }' | jq '.'

# 4. Установка Menu Button для запуска Mini App
log "🎯 Устанавливаем кнопку меню для Mini App..."
curl -s -X POST "https://api.telegram.org/bot$TELEGRAM_BOT_TOKEN/setChatMenuButton" \
  -H "Content-Type: application/json" \
  -d "{
    \"menu_button\": {
      \"type\": \"web_app\",
      \"text\": \"🚀 Открыть App\",
      \"web_app\": {
        \"url\": \"$APP_URL/miniapp\"
      }
    }
  }" | jq '.'

# 5. Настройка webhook для получения обновлений (опционально)
read -p "Хотите настроить webhook для получения обновлений? (y/n): " setup_webhook

if [ "$setup_webhook" = "y" ] || [ "$setup_webhook" = "Y" ]; then
    log "🔗 Настраиваем webhook..."
    curl -s -X POST "https://api.telegram.org/bot$TELEGRAM_BOT_TOKEN/setWebhook" \
      -H "Content-Type: application/json" \
      -d "{
        \"url\": \"$APP_URL/api/telegram/webhook\",
        \"allowed_updates\": [\"message\", \"callback_query\", \"inline_query\"]
      }" | jq '.'
else
    log "⏭️ Пропускаем настройку webhook"
fi

# 6. Получение информации о боте
log "🔍 Получаем информацию о боте..."
BOT_INFO=$(curl -s "https://api.telegram.org/bot$TELEGRAM_BOT_TOKEN/getMe" | jq '.')
echo -e "${BLUE}$BOT_INFO${NC}"

# 7. Проверяем статус webhook
log "📡 Проверяем статус webhook..."
WEBHOOK_INFO=$(curl -s "https://api.telegram.org/bot$TELEGRAM_BOT_TOKEN/getWebhookInfo" | jq '.')
echo -e "${BLUE}$WEBHOOK_INFO${NC}"

# 8. Создание тестового сообщения для проверки
echo ""
log "✅ Настройка бота завершена!"
echo ""
echo -e "${GREEN}📋 Сводка настроек:${NC}"
echo -e "  🤖 Bot Username: @$TELEGRAM_BOT_USERNAME"
echo -e "  🌐 Mini App URL: $APP_URL/miniapp"
echo -e "  🔗 Login URL: $APP_URL/login"
echo ""
echo -e "${YELLOW}📝 Что делать дальше:${NC}"
echo -e "  1. Найдите бота @$TELEGRAM_BOT_USERNAME в Telegram"
echo -e "  2. Отправьте команду /start"
echo -e "  3. Нажмите кнопку '🚀 Открыть App' в меню"
echo -e "  4. Или используйте команду /app"
echo ""
echo -e "${BLUE}💡 Полезные ссылки:${NC}"
echo -e "  🔗 Прямая ссылка на бота: https://t.me/$TELEGRAM_BOT_USERNAME"
echo -e "  📱 Mini App: https://t.me/$TELEGRAM_BOT_USERNAME/app"
echo ""

# Создание QR кода для быстрого доступа (если доступен qrencode)
if command -v qrencode &> /dev/null; then
    log "📱 Создаем QR код для быстрого доступа..."
    qrencode -t ANSI "https://t.me/$TELEGRAM_BOT_USERNAME"
else
    warning "qrencode не установлен. Для создания QR кода установите: apt-get install qrencode"
fi

log "🎉 Настройка завершена успешно!"
