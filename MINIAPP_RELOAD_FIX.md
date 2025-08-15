# Исправление бесконечных перезагрузок Mini App

## Проблемы

1. ❌ **Бесконечные перезагрузки страницы** - каждые 1-2 секунды
2. ❌ **Сессия не сохраняется** - `auth_user` всегда `null`
3. ❌ **Повторная авторизация** - пользователь авторизуется заново при каждом запросе

## Исправления

### 1. Конфигурация сессий для Telegram Mini App

**Файл: `.env`**
```env
SESSION_SECURE_COOKIE=true
SESSION_DOMAIN=.sticap.ru
SESSION_SAME_SITE=none
SANCTUM_STATEFUL_DOMAINS=tg.sticap.ru
```

**Файл: `config/session.php`**
```php
'same_site' => env('SESSION_SAME_SITE', 'none'),
```

### 2. Логика предотвращения повторной авторизации

**Файл: `app/Http/Controllers/MiniAppController.php`**

- ✅ Добавлена проверка `Auth::check()` в методе `index()`
- ✅ Добавлена проверка `Auth::check()` в методе `auth()`
- ✅ Предотвращение обработки `initData` для уже авторизованных пользователей

### 3. Frontend - убрана автоматическая перезагрузка

**Файл: `resources/views/miniapp/app.blade.php`**

- ✅ Убрана строка `window.location.reload()` после успешной авторизации
- ✅ Добавлена проверка `authInProgress` для предотвращения повторных вызовов
- ✅ Улучшена логика проверки уже авторизованного пользователя

## Команды для деплоя

```bash
# 1. Локально закоммитить изменения
git add .
git commit -m "Fix: Infinite reloads in Mini App - session config and auth logic"
git push origin main

# 2. На продакшн сервере
cd /path/to/project
git pull origin main

# 3. Очистить кеши
php artisan config:clear
php artisan view:clear
php artisan cache:clear

# 4. Перезапустить веб-сервер
sudo systemctl reload nginx
# или 
sudo systemctl reload apache2
```

## Проверка исправлений

1. **Открыть Mini App через Telegram бота**
2. **Авторизация должна пройти один раз** 
3. **Страница НЕ должна перезагружаться каждые несколько секунд**
4. **В логах должно появиться:** `"User already authenticated, skipping initData processing"`

## Ожидаемые логи после исправления

```
[INFO] Mini App accessed {"has_init_data":true,"auth_user":1107317588}
[INFO] User already authenticated, skipping initData processing
```

**Вместо:**
```
[INFO] Mini App accessed {"has_init_data":false,"auth_user":null}
[WARNING] Web App data verification failed
[INFO] Temporarily allowing unverified Web App data for testing
[INFO] User updated via Mini App
```

## Важные моменты

⚠️ **ВНИМАНИЕ:** Настройки сессии `SESSION_SAME_SITE=none` и `SESSION_SECURE_COOKIE=true` критически важны для работы в Telegram Mini App, так как приложение работает в iframe с другого домена.

🔧 **Диагностика:** Если проблема остается, проверьте в браузере (DevTools > Application > Cookies), сохраняются ли куки сессии между запросами.

📝 **Мониторинг:** После деплоя следить за логами - количество запросов на `/miniapp` должно резко сократиться.
