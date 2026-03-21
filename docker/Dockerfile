# syntax=docker/dockerfile:1
# Nginx + PHP-FPM 8.3 (Alpine) — минимальный рантайм для VDS
# Сборка: из корня репозитория: docker build -f docker/Dockerfile .

FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json ./
COPY app ./app
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

FROM php:8.3-fpm-alpine

RUN apk add --no-cache nginx supervisor sqlite-libs \
    && docker-php-ext-install pdo_sqlite opcache \
    && rm -rf /var/cache/apk/* \
    && sed -i 's/^clear_env = yes/clear_env = no/' /usr/local/etc/php-fpm.d/www.conf 2>/dev/null || true

RUN printf 'upload_max_filesize=12M\npost_max_size=12M\nmemory_limit=128M\n' > /usr/local/etc/php/conf.d/marketbw.ini \
    && printf 'opcache.enable=1\nopcache.memory_consumption=32\n' >> /usr/local/etc/php/conf.d/marketbw.ini

WORKDIR /var/www

COPY --from=vendor /app/vendor ./vendor
COPY app ./app
COPY public ./public
COPY templates ./templates
COPY config ./config
COPY composer.json ./
COPY VERSION /var/www/VERSION

LABEL org.opencontainers.image.version="3.2.3"

# Демо-картинки в образе; на сервере можно смонтировать том поверх /var/www/images
COPY public/images /var/www/images

COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/docker-entrypoint.sh /docker-entrypoint.sh

RUN mkdir -p /var/www/data /run/nginx \
    && chown -R www-data:www-data /var/www/data /var/www/images \
    && chmod -R g+w /var/www/images \
    && chmod +x /docker-entrypoint.sh

ENV DATA_DIR=/var/www/data \
    IMAGES_DIR=/var/www/images \
    APP_DEBUG=false

EXPOSE 80

CMD ["/docker-entrypoint.sh"]
