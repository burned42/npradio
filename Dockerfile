FROM dunglas/frankenphp:php8.5

ENV SERVER_NAME=':80'
ENV FRANKENPHP_CONFIG='worker /app/public/index.php'

ENV APP_ENV='prod'
ENV APP_DEBUG='0'
ENV APP_SECRET=''
ENV SENTRY_DSN=''

RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip \
    && rm -rf /var/lib/apt/lists/*

RUN install-php-extensions apcu intl zip @composer

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY <<-EOF /usr/local/etc/php/conf.d/local.ini
	date.timezone = "Europe/Berlin"
	short_open_tag = Off
	expose_php = Off
	opcache.preload_user = root
	opcache.preload = /app/config/preload.php
	register_argc_argv = On
EOF

COPY . /app
RUN composer install --no-dev --optimize-autoloader -d /app/

#RUN install-php-extensions pcov && echo 'pcov.enabled = 1' > /usr/local/etc/php/conf.d/pcov.ini
