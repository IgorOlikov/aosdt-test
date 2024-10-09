FROM php:8.3-cli-alpine

RUN apk update && apk upgrade
RUN apk add libpq-dev
RUN docker-php-ext-install pdo pdo_pgsql pgsql

RUN mv $PHP_INI_DIR/php.ini-development $PHP_INI_DIR/php.ini


RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/bin --filename=composer --quiet

RUN addgroup -g 1000 app && adduser -u 1000 -G app -s /bin/sh -D app

WORKDIR /app

USER app
