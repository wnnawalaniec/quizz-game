FROM php:8.1.29-fpm-alpine3.20

RUN apk update
RUN apk upgrade
RUN apk add \
        libzip-dev \
        zip
RUN docker-php-ext-install pdo pdo_mysql mysqli zip

WORKDIR /var/www

COPY ./docker/php/composer.sh /tmp/composer.sh
RUN chmod +x /tmp/composer.sh
RUN /tmp/composer.sh
RUN chmod +x composer.phar

COPY composer.json /var/www/
COPY composer.lock /var/www/

RUN php composer.phar install \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --no-dev \
    --prefer-dist \
    && rm -f composer.json \
    && rm -f composer.lock \
    && rm -f composer.phar \
    && rm -f /tmp/composer.sh

COPY . /var/www