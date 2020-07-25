FROM php:7.4-fpm-alpine

WORKDIR /var/www/html

RUN set -ex \
  && apk --no-cache add \
    postgresql-dev

RUN apk add --no-cache --virtual .phpize-deps $PHPIZE_DEPS

# Add redis extension
ENV REDIS_VERSION 5.2.2
RUN curl -L -o /tmp/redis.tar.gz https://github.com/phpredis/phpredis/archive/$REDIS_VERSION.tar.gz \
    && tar xfz /tmp/redis.tar.gz \
    && rm -r /tmp/redis.tar.gz \
    && mkdir -p /usr/src/php/ext \
    && mv phpredis-* /usr/src/php/ext/redis

RUN pecl install xdebug && docker-php-ext-enable xdebug

# Install Postgres and Redis extensions
RUN docker-php-ext-install pdo pdo_pgsql redis
