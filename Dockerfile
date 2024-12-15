FROM php:8.4-apache

ENV APP_ENV prod
ENV APP_DEBUG false
ENV APP_SECRET ''
ENV SENTRY_DSN ''

RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    libicu-dev \
    libzip-dev \
    unzip \
    && rm -rf /var/lib/apt/lists/*

RUN php_extensions="intl opcache zip" \
    && docker-php-ext-install -j$(nproc) $php_extensions \
    && pecl install apcu \
    && docker-php-ext-enable $php_extensions apcu

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
RUN echo 'date.timezone = "Europe/Berlin"' > /usr/local/etc/php/conf.d/timezone.ini \
    && echo 'short_open_tag = Off' > /usr/local/etc/php/conf.d/short_open_tag.ini \
    && echo 'opcache.preload_user = www-data' > /usr/local/etc/php/conf.d/preloading.ini \
    && echo 'opcache.preload = /var/www/html/config/preload.php' >> /usr/local/etc/php/conf.d/preloading.ini

RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!/var/www/html/public!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && echo 'PassEnv APP_ENV APP_DEBUG APP_SECRET SENTRY_DSN' > /etc/apache2/conf-enabled/pass-env.conf \
    && a2enmod rewrite

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html/

USER www-data
RUN composer install --no-dev -d /var/www/html/
USER root

#RUN pecl install pcov && docker-php-ext-enable pcov && echo 'pcov.enabled = 1' > /usr/local/etc/php/conf.d/pcov.ini
