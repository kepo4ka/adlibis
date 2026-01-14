# .PHONY указывает, что эти цели — не файлы, а команды
.PHONY: up down bash composer artisan translations-check migrate fresh seed test lint fix-cs install clean logs logs-all npm-install vite vite-build optimize reset status queue-env

# Запуск контейнеров в фоне
up:
	docker-compose up -d

# Остановка и удаление контейнеров
down:
	docker-compose down

# Вход в контейнер приложения
bash:
	docker-compose exec app bash

# Выполнение composer-команд внутри контейнера
# Примеры:
#   make composer require themsaid/laravel-langman
#   make composer CMD="require --dev larswiegers/laravel-translations-checker"
composer:
ifeq ($(strip $(CMD)),)
	docker-compose exec app composer $(filter-out $@,$(MAKECMDGOALS))
else
	docker-compose exec app composer $(CMD)
endif

# Выполнение Artisan-команд
artisan:
	docker-compose exec app php artisan

# Проверка переводов
translations-check:
	docker-compose exec app php artisan translations:check

# Очистка кэша Laravel
optimize:
	docker-compose exec app php artisan optimize:clear

# Запуск миграций
migrate: optimize
	docker-compose exec app php artisan migrate

# Полная пересборка БД (migrate:fresh + seed)
fresh: optimize
	docker-compose exec app php artisan migrate:fresh --seed

# Запуск сидеров
seed:
	docker-compose exec app php artisan db:seed

# Запуск PHPUnit-тестов
test:
	docker-compose exec app php artisan test

# Проверка стиля кода (через Laravel Pint)
lint:
	docker-compose exec app ./vendor/bin/pint --test

# Автоисправление стиля кода
fix-cs:
	docker-compose exec app ./vendor/bin/pint

# Установка зависимостей (полезно при первом запуске)
install:
	docker-compose run --rm app composer install

# Установка npm зависимостей
npm-install:
	docker-compose exec app npm install

# Запуск Vite dev server
vite:
	docker-compose exec app npm run dev

# Сборка assets для production
vite-build:
	docker-compose exec app npm run build

# Полная очистка: остановка, удаление томов, пересоздание
clean:
	docker-compose down -v
	docker-compose up -d --build
	docker-compose exec app composer install
	docker-compose exec app php artisan key:generate
	docker-compose exec app php artisan migrate --seed

# Полный сброс окружения и базы
reset: down clean fresh

# Просмотр логов приложения (из Laravel storage/logs)
logs:
	docker-compose exec app tail -f storage/logs/laravel.log

# Просмотр всех логов контейнеров
logs-all:
	docker-compose logs -f

# Статус контейнеров
status:
	docker-compose ps

queue-env:
	docker-compose exec queue-worker env | grep QUEUE_CONNECTION

# Заглушка, позволяющая передавать аргументы после основной цели (например, make composer ...)
%:
	@:
