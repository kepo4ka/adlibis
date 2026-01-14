#!/bin/bash

# Автоматически запускает queue worker

set -e

# Переходим в рабочую директорию Laravel
cd /var/www/html

echo "Запуск Queue Worker..."

# Установка зависимостей Composer если их нет
echo "Проверка наличия установленных зависимостей..."
if [ ! -d "vendor" ]; then
    echo "Установка зависимостей через Composer..."
    composer install --no-interaction --optimize-autoloader --no-scripts
else
    echo "Зависимости уже установлены"
fi

# Функция для graceful shutdown
cleanup() {
    echo "Получен сигнал завершения, останавливаем worker..."
    if [ ! -z "$WORKER_PID" ]; then
        kill -TERM "$WORKER_PID" 2>/dev/null || true
        wait "$WORKER_PID" 2>/dev/null || true
    fi
    echo "Queue Worker остановлен"
    exit 0
}

trap cleanup SIGTERM SIGINT

echo "Ожидание готовности базы данных..."
while ! php artisan migrate:status > /dev/null 2>&1; do
    echo "Ждём бд"
    sleep 2
done
echo "Бд готова"

# Проверяем доступность выбранного драйвера очереди
QUEUE_CONNECTION=${QUEUE_CONNECTION:-database}
echo "Используется драйвер очереди: $QUEUE_CONNECTION"

case $QUEUE_CONNECTION in
    "redis")
        echo "Проверка доступности Redis..."
        while ! nc -z redis 6379; do
            echo "Ждём Redis..."
            sleep 2
        done
        echo "Redis готов"
        ;;
    "rabbitmq")
        echo "Проверка доступности RabbitMQ..."
        while ! nc -z rabbitmq 5672; do
            echo "Ждём RabbitMQ"
            sleep 2
        done
        echo "RabbitMQ готов"
        ;;
    "database")
        echo "Database драйвер готов"
        ;;
esac

echo "Запуск queue worker для драйвера: $QUEUE_CONNECTION"

# Чтение параметров из переменных окружения
WORKER_TRIES=${WORKER_TRIES:-3}
WORKER_TIMEOUT=${WORKER_TIMEOUT:-60}
WORKER_MEMORY=${WORKER_MEMORY:-512}
WORKER_SLEEP=${WORKER_SLEEP:-3}
WORKER_MAX_JOBS=${WORKER_MAX_JOBS:-1000}

WORKER_ARGS="--tries=$WORKER_TRIES --timeout=$WORKER_TIMEOUT --memory=$WORKER_MEMORY --sleep=$WORKER_SLEEP --max-jobs=$WORKER_MAX_JOBS"

php artisan queue:work "$QUEUE_CONNECTION" "$WORKER_ARGS" &
WORKER_PID=$!

echo "Queue Worker запущен (PID: $WORKER_PID)"

wait $WORKER_PID
