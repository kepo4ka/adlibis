# Comments API

REST API для системы комментариев с поддержкой полиморфных связей, вложенных комментариев и курсорной пагинации.
## Основные endpoints

### Вход
- `POST /api/auth/login` - Вход и получение токена
- `POST /api/auth/logout` - Выход 
- `GET /api/auth/my-info` - Данные обо мне

### Новости
- `GET /api/news` - Список новостей
- `POST /api/news` - Создание новости 
- `GET /api/news/{id}` - Получение новости с комментариями 
- `PUT /api/news/{id}` - Обновление новости
- `DELETE /api/news/{id}` - Удаление новости

### Видео посты
- `GET /api/video-posts` - Список видео постов
- `POST /api/video-posts` - Создание видео поста 
- `GET /api/video-posts/{id}` - Получение видео поста с комментариями
- `PUT /api/video-posts/{id}` - Обновление видео поста 
- `DELETE /api/video-posts/{id}` - Удаление видео поста 

### Комментарии
- `POST /api/comments` - Создание комментария 
- `PUT /api/comments/{id}` - Обновление комментария
- `DELETE /api/comments/{id}` - Удаление комментария 

## Пагинация

- `limit` - количество (по умолчанию 20)
- `cursor` - курсор (время) для получения следующей страницы


## Авторизация

```
Authorization: Bearer {token}
```

##  пользователь

- Email: `test@example.com`
- Password: `password`

## Postman

Postman коллекция в файле `api postman.js`

## Технологии

- Laravel 12
- MySQL
- Docker (окружение разработки)

## Установка и запуск

1. Клонируйте репозиторий
2. Запустите Docker контейнеры: `docker-compose up -d`
3. Установите зависимости: `docker-compose exec app composer install`
4. Выполните миграции: `docker-compose exec app php artisan migrate`
