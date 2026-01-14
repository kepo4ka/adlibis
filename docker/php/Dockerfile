FROM php:8.4.11-fpm

# Обновление индекса пакетов с retry-логикой
RUN apt-get -y update || (sleep 5 && apt-get -y update || (sleep 10 && apt-get -y update))

# Установка зависимостей для redis и других пакетов с обработкой ошибок
RUN apt-get install -y --fix-missing netcat-openbsd libldap-dev libzstd-dev libp11-kit-dev || \
    (apt --fix-broken install -y && \
     sleep 5 && \
     apt-get update && \
     apt-get install -y --fix-missing netcat-openbsd libldap-dev libzstd-dev libp11-kit-dev) || \
    (sleep 10 && apt-get update && apt-get install -y --fix-missing netcat-openbsd libldap-dev libzstd-dev libp11-kit-dev)

RUN docker-php-ext-install pdo pdo_mysql sockets && docker-php-ext-enable pdo_mysql sockets

RUN pecl install xdebug-3.4.5 && docker-php-ext-enable xdebug
RUN pecl install redis && docker-php-ext-enable redis

# Install Composer
RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends git unzip curl; \
    curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php; \
    php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer; \
    rm -rf /var/lib/apt/lists/* /tmp/composer-setup.php

# Install Node.js для Vite.js
RUN curl -fsSL https://deb.nodesource.com/setup_lts.x | bash - && \
    apt-get install -y nodejs && \
    npm install -g npm@latest && \
    rm -rf /var/lib/apt/lists/*

RUN groupadd -g 1000 www && \
    useradd -u 1000 -ms /bin/bash -g www www

COPY docker/php/conf.d/* $PHP_INI_DIR/conf.d/

# Копируем и делаем исполняемыми скрипты entrypoint
COPY docker/php/app-entrypoint.sh /usr/local/bin/app-entrypoint.sh
RUN chmod +x /usr/local/bin/app-entrypoint.sh

WORKDIR /var/www/html

USER www

RUN echo "alias a='php /var/www/html/artisan'" >> /home/www/.bashrc && \
    echo "alias routes='php /var/www/html/artisan route:list'" >> /home/www/.bashrc && \
    echo "alias e='php /var/www/html/artisan event:list'" >> /home/www/.bashrc

CMD ["php-fpm"]
