# Telegram Mini App Setup

## Обзор

Этот проект теперь поддерживает Telegram Mini App - веб-приложение, которое открывается прямо в Telegram через вашего бота.

## Что было добавлено

### 1. Контроллеры
- `TelegramMiniAppController` - основной контроллер Mini App
- `TelegramWebhookController` - обработка команд бота

### 2. Представления
- `resources/views/telegram/miniapp.blade.php` - интерфейс Mini App

### 3. Маршруты
- `/telegram/miniapp` - главная страница Mini App
- `/telegram/miniapp/auth` - API авторизации
- `/telegram/webhook` - webhook для бота

### 4. Скрипты
- `setup_bot.php` - скрипт настройки бота

## Настройка

### Шаг 1: Настройка бота через BotFather

1. Откройте @BotFather в Telegram
2. Выберите вашего бота
3. Нажмите "Bot Settings" → "Menu Button"
4. Выберите "Configure Menu Button"
5. Введите URL: `http://ваш-домен.com/telegram/miniapp`
6. Введите текст кнопки: "Открыть приложение"

### Шаг 2: Автоматическая настройка (рекомендуется)

Запустите скрипт настройки:

```bash
php setup_bot.php
```

### Шаг 3: Ручная настройка через API

Или используйте curl команду:

```bash
curl -X POST "https://api.telegram.org/bot{BOT_TOKEN}/setChatMenuButton" \
  -H "Content-Type: application/json" \
  -d '{
    "menu_button": {
      "type": "web_app",
      "text": "Открыть приложение",
      "web_app": {
        "url": "http://ваш-домен.com/telegram/miniapp"
      }
    }
  }'
```

### Шаг 4: Настройка webhook (для продакшена)

```bash
curl -X POST "https://api.telegram.org/bot{BOT_TOKEN}/setWebhook" \
  -H "Content-Type: application/json" \
  -d '{
    "url": "http://ваш-домен.com/telegram/webhook"
  }'
```

## Как это работает

1. **Пользователь открывает бота** → видит кнопку "Открыть приложение"
2. **Нажимает кнопку** → открывается Mini App в Telegram
3. **Авторизация** → происходит автоматически через Telegram Web App API
4. **Использование** → полнофункциональное веб-приложение в Telegram

## Функции Mini App

### Для авторизованных пользователей:
- Профиль пользователя с аватаром
- Информация о Telegram аккаунте
- Интерфейс с разделами (в разработке):
  - Профиль
  - Настройки
  - Уведомления
  - Помощь

### Для неавторизованных пользователей:
- Кнопка авторизации через Telegram

## Команды бота

- `/start` - Приветствие и кнопка открытия приложения
- `/app` - Кнопка для открытия Mini App
- `/help` - Справочная информация

## Безопасность

1. **Проверка подлинности данных** - все данные от Telegram проверяются криптографически
2. **CSRF защита** - исключена только для webhook endpoint
3. **Автоматическая авторизация** - пользователи авторизуются через Telegram Web App API

## Разработка

### Локальная разработка
- В режиме `APP_ENV=local` проверка подлинности Telegram отключена
- Используйте ngrok для тестирования webhook

### Продакшен
- Убедитесь, что `APP_ENV=production`
- Настройте SSL сертификат
- Установите webhook

## Структура файлов

```
app/Http/Controllers/
├── TelegramMiniAppController.php    # Основной контроллер Mini App
└── TelegramWebhookController.php    # Обработка команд бота

resources/views/telegram/
└── miniapp.blade.php               # Интерфейс Mini App

routes/
└── web.php                         # Маршруты (обновлены)

setup_bot.php                       # Скрипт настройки бота
```

## Кастомизация

### Изменение интерфейса
Отредактируйте файл `resources/views/telegram/miniapp.blade.php`

### Добавление функций
Добавьте новые методы в `TelegramMiniAppController`

### Настройка команд бота
Измените массив команд в `setup_bot.php`

## Проблемы и решения

### Проблема: Mini App не открывается
- Проверьте правильность URL в настройках бота
- Убедитесь, что домен доступен извне

### Проблема: Ошибка авторизации
- Проверьте `TELEGRAM_BOT_TOKEN` в `.env`
- В локальной разработке проверка отключена

### Проблема: Webhook не работает
- Убедитесь, что URL доступен по HTTPS (в продакшене)
- Проверьте логи Laravel

## Полезные ссылки

- [Telegram Mini Apps Documentation](https://core.telegram.org/bots/webapps)
- [Telegram Bot API](https://core.telegram.org/bots/api)
- [Laravel Documentation](https://laravel.com/docs)
