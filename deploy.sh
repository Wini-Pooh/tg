#!/bin/bash

echo "🚀 Начинаем деплой..."

# Обновляем код
echo "📥 Получаем последние изменения..."
git pull origin main

# Обновляем зависимости
echo "📦 Обновляем зависимости..."
composer install --no-dev --optimize-autoloader

# Пересобираем ассеты (если нужно)
if [ -f "package.json" ]; then
    echo "🔨 Пересобираем ассеты..."
    npm ci --production
    npm run build
fi

# Очищаем кеш
echo "🧹 Очищаем кеш..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Выполняем миграции
echo "🗄️ Выполняем миграции..."
php artisan migrate --force

# Кешируем для продакшена
echo "⚡ Создаем кеш..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Устанавливаем права
echo "🔐 Устанавливаем права..."
chmod -R 755 storage bootstrap/cache

# Создаем символическую ссылку
php artisan storage:link

echo "✅ Деплой завершен!"

# Проверяем статус
echo "🔍 Проверяем статус приложения..."
php artisan about
