#!/bin/bash

# Точка входа приложения:
# - В dev (APP_DEBUG=true) запускает Vite (npm run dev) параллельно с PHP-FPM
# - В prod (APP_DEBUG!=true) выполняет vite build перед запуском PHP-FPM

set -e

cd /var/www/html

echo "Запуск приложения... (APP_DEBUG=${APP_DEBUG:-false})"

# Установка зависимостей Composer если их нет
echo "Проверка наличия установленных зависимостей..."
if [ ! -d "vendor" ]; then
    echo "Установка зависимостей через Composer..."
    composer install --no-interaction --optimize-autoloader --no-scripts
else
    echo "Зависимости уже установлены"
fi

# Создаем storage:link если его еще нет
echo "Проверка storage:link..."
if [ ! -L public/storage ] && [ ! -d public/storage ]; then
    echo "Создание символической ссылки storage:link..."
    php artisan storage:link || echo "Предупреждение: storage:link не создан"
else
    echo "storage:link уже существует"
fi

# Функция для проверки/установки npm зависимостей
ensure_node_modules() {
  if [ ! -d "node_modules" ] || [ ! -f "node_modules/.bin/vite" ]; then
      echo "Установка npm зависимостей..."
      if [ -f package-lock.json ]; then
        npm ci || npm install || echo "Предупреждение: установка зависимостей завершилась с ошибкой"
      else
        npm install || echo "Предупреждение: npm install завершился с ошибкой"
      fi
  else
      echo "npm зависимости уже установлены"
  fi
}

# Dev режим: параллельный запуск Vite + PHP-FPM
if [ "${APP_DEBUG}" = "true" ]; then
  echo "APP_DEBUG=true: запуск Vite dev server"
  ensure_node_modules

  # Запускаем Vite в фоне
  npm run dev &
  VITE_PID=$!
  echo "Vite dev server PID: $VITE_PID"

  # Стартуем PHP-FPM в форграунде как PID 1
  exec /usr/local/bin/docker-php-entrypoint php-fpm
fi

# Prod режим: сборка ассетов и запуск PHP-FPM (удаление файла public/hot)
echo "APP_DEBUG!=true: выполняем vite build"
ensure_node_modules

# Удаляем файл public/hot, если он существует (чтобы Laravel использовал production build)
# Удаляем до и после сборки на всякий случай
if [ -f "public/hot" ]; then
    echo "Удаление public/hot для использования production build"
    rm -f public/hot
fi

npm run build || echo "Предупреждение: vite build завершился с ошибкой"

# Убеждаемся, что файл public/hot не существует после сборки
if [ -f "public/hot" ]; then
    echo "Внимание: файл public/hot создан после сборки, удаляем его"
    rm -f public/hot
fi

# Запускаем PHP-FPM
exec /usr/local/bin/docker-php-entrypoint php-fpm
