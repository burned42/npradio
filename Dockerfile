FROM php:7.2-apache

ENV APP_ENV prod
ENV APP_DEBUG 0
ENV APACHE_DOCUMENT_ROOT /var/www/html/public

RUN apt-get update && apt-get install -y libicu-dev
RUN docker-php-ext-configure intl
RUN docker-php-ext-install intl
RUN docker-php-ext-enable intl

RUN docker-php-ext-configure opcache
RUN docker-php-ext-install opcache
RUN docker-php-ext-enable opcache

RUN echo 'date.timezone = "Europe/Berlin"' > /usr/local/etc/php/conf.d/timezone.ini \
    && echo 'short_open_tag = Off' > /usr/local/etc/php/conf.d/short_open_tag.ini

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

RUN a2enmod rewrite

RUN echo 'PassEnv APP_ENV APP_SECRET' > /etc/apache2/conf-enabled/pass-env.conf

COPY . /var/www/html/
RUN rm -rf /var/www/html/.git/

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev -d /var/www/html/

RUN php /var/www/html/bin/console cache:clear

RUN chown -R www-data:www-data /var/www/html/
