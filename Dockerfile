FROM dunglas/frankenphp:php8.4

ENV SERVER_NAME=':80'
ENV FRANKENPHP_CONFIG='worker /app/public/index.php'
ENV APP_RUNTIME='Runtime\FrankenPhpSymfony\Runtime'

ENV APP_ENV='prod'
ENV APP_DEBUG='0'
ENV APP_SECRET=''
ENV SENTRY_DSN=''

RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip \
    && rm -rf /var/lib/apt/lists/*

RUN install-php-extensions apcu intl zip @composer

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
RUN echo 'date.timezone = "Europe/Berlin"' > /usr/local/etc/php/conf.d/timezone.ini \
    && echo 'short_open_tag = Off' > /usr/local/etc/php/conf.d/short_open_tag.ini \
    && echo 'opcache.preload_user = root' > /usr/local/etc/php/conf.d/preloading.ini \
    && echo 'opcache.preload = /app/config/preload.php' >> /usr/local/etc/php/conf.d/preloading.ini

COPY . /app
RUN composer install --no-dev --optimize-autoloader -d /app/

#RUN install-php-extensions pcov && echo 'pcov.enabled = 1' > /usr/local/etc/php/conf.d/pcov.ini
