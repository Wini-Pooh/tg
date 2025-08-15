# Инструкция по развертыванию Telegram Mini App на хостинге

## 🚀 Подготовка к загрузке на хост

### 1. Настройка .env для продакшена

Обновите ваш `.env` файл для продакшена:

```env
APP_NAME=TelegramApp
APP_ENV=production
APP_KEY=base64:Bk4dzqbU6xWasrX+agRk1BT+BGlc8GS+/eKpAVu9tSg=
APP_DEBUG=false
APP_URL=https://ваш-домен.com

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=ваша_база_данных
DB_USERNAME=ваш_пользователь
DB_PASSWORD=ваш_пароль

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

TELEGRAM_BOT_TOKEN=8410914085:AAGiBJLy5RpsTYF082hrjylZw0DYi2QsQUw
TELEGRAM_BOT_USERNAME=Sticap_bot
```

### 2. Команды для выполнения на хостинге

После загрузки файлов выполните:

```bash
# 1. Установка зависимостей
composer install --optimize-autoloader --no-dev

# 2. Генерация ключа приложения (если нужно)
php artisan key:generate

# 3. Выполнение миграций
php artisan migrate --force

# 4. Очистка и кэширование конфигурации
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Установка прав на папки
chmod -R 755 storage
chmod -R 755 bootstrap/cache
```

### 3. Настройка .htaccess для Apache

Убедитесь, что в папке `public` есть файл `.htaccess`:

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

## 🤖 Настройка Telegram бота

### Вариант 1: Через скрипт (рекомендуется)

1. Загрузите `setup_bot.php` на хост
2. Выполните: `php setup_bot.php`

### Вариант 2: Вручную через API

#### Установка Menu Button для Mini App:

```bash
curl -X POST "https://api.telegram.org/bot8410914085:AAGiBJLy5RpsTYF082hrjylZw0DYi2QsQUw/setChatMenuButton" \
  -H "Content-Type: application/json" \
  -d '{
    "menu_button": {
      "type": "web_app",
      "text": "Открыть приложение",
      "web_app": {
        "url": "https://ваш-домен.com/telegram/miniapp"
      }
    }
  }'
```

#### Установка команд бота:

```bash
curl -X POST "https://api.telegram.org/bot8410914085:AAGiBJLy5RpsTYF082hrjylZw0DYi2QsQUw/setMyCommands" \
  -H "Content-Type: application/json" \
  -d '{
    "commands": [
      {
        "command": "start",
        "description": "Запустить приложение"
      },
      {
        "command": "app",
        "description": "Открыть Mini App"
      },
      {
        "command": "help",
        "description": "Помощь"
      }
    ]
  }'
```

#### Установка webhook:

```bash
curl -X POST "https://api.telegram.org/bot8410914085:AAGiBJLy5RpsTYF082hrjylZw0DYi2QsQUw/setWebhook" \
  -H "Content-Type: application/json" \
  -d '{
    "url": "https://ваш-домен.com/telegram/webhook"
  }'
```

#### Установка домена для Login Widget:

```bash
curl -X POST "https://api.telegram.org/bot8410914085:AAGiBJLy5RpsTYF082hrjylZw0DYi2QsQUw/setDomainName" \
  -H "Content-Type: application/json" \
  -d '{
    "domain_name": "ваш-домен.com"
  }'
```

### Вариант 3: Через @BotFather

1. Откройте @BotFather в Telegram
2. Выберите вашего бота Sticap_bot
3. **Установка Menu Button:**
   - Bot Settings → Menu Button → Configure Menu Button
   - URL: `https://ваш-домен.com/telegram/miniapp`
   - Text: "Открыть приложение"

4. **Установка домена:**
   - Bot Settings → Domain
   - Введите: `ваш-домен.com`

## 📋 Чек-лист перед загрузкой

- [ ] Обновлен `.env` файл (APP_ENV=production, APP_DEBUG=false, APP_URL)
- [ ] Настроена база данных
- [ ] Проверен TELEGRAM_BOT_TOKEN
- [ ] Файл `.htaccess` в папке public
- [ ] Удален файл `.env.example` (безопасность)

## 📋 Чек-лист после загрузки

- [ ] Выполнены команды composer и artisan
- [ ] Настроены права на папки (755 для storage и bootstrap/cache)
- [ ] Проверена работа сайта
- [ ] Настроен Telegram бот (Menu Button, команды, webhook, домен)
- [ ] Протестирован Mini App через бота

## 🔧 Тестирование

1. **Проверьте сайт:** `https://ваш-домен.com`
2. **Проверьте Mini App:** `https://ваш-домен.com/telegram/miniapp`
3. **Откройте бота в Telegram:** найдите @Sticap_bot
4. **Отправьте /start** → должна появиться кнопка "Открыть приложение"
5. **Нажмите кнопку** → должно открыться Mini App

## 🐛 Возможные проблемы

### Ошибка 500
- Проверьте права на папки storage и bootstrap/cache
- Проверьте настройки базы данных в .env

### Mini App не открывается
- Убедитесь, что домен установлен в @BotFather
- Проверьте SSL сертификат (обязателен для Telegram)

### Login Widget не работает
- Установите домен через @BotFather или API
- Проверьте HTTPS (обязателен)

## 📱 Структура приложения

После развертывания у вас будет:

1. **Веб-сайт** - обычный сайт с авторизацией через Telegram
2. **Mini App** - приложение в Telegram
3. **Telegram бот** - обрабатывает команды и открывает Mini App

Пользователи смогут:
- Зайти на сайт и авторизоваться через Telegram Login Widget
- Открыть бота и использовать Mini App прямо в Telegram
