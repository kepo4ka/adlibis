<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Comments API</title>
            <style>
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                line-height: 1.6;
                color: #333;
                background: #fff;
                max-width: 800px;
                margin: 40px auto;
                padding: 20px;
            }
            h1 {
                margin-bottom: 20px;
            }
            h2 {
                margin-top: 30px;
                margin-bottom: 15px;
            }
            p {
                margin-bottom: 15px;
            }
            .endpoint {
                font-family: 'Courier New', monospace;
            }
            ul {
                margin-left: 20px;
                margin-bottom: 15px;
            }
            li {
                margin-bottom: 8px;
            }
            a {
                color: #0066cc;
                text-decoration: none;
            }
            a:hover {
                text-decoration: underline;
            }
            </style>
    </head>
    <body>
        <h1>Comments API</h1>
        
        <p>
            REST API для системы комментариев с поддержкой полиморфных связей, 
            вложенных комментариев и курсорной пагинации.
        </p>

        <p>
            <strong>Base URL:</strong> <span class="endpoint">{{ url('/api') }}</span>
        </p>

        <h2>Основные endpoints</h2>
        <ul>
            <li><span class="endpoint">POST /api/auth/login</span> - Вход и получение токена</li>
            <li><span class="endpoint">GET /api/news</span> - Список новостей</li>
            <li><span class="endpoint">GET /api/news/{id}</span> - Новость с комментариями (курсорная пагинация)</li>
            <li><span class="endpoint">POST /api/comments</span> - Создание комментария (требует авторизации)</li>
            <li><span class="endpoint">GET /api/video-posts</span> - Список видео постов</li>
                    </ul>

        <h2>Документация</h2>
        <p>
            Postman коллекция с автоматической подстановкой токена авторизации доступна в репозитории проекта.
        </p>

        <p style="margin-top: 30px; font-size: 0.9em; color: #666;">
            Используйте тестового пользователя: <strong>test@example.com</strong> / <strong>password</strong>
        </p>
    </body>
</html>
