FROM php:8.1-fpm-alpine

ARG UID
ARG GID
ARG APP_DIR
ARG APP_ENV

RUN addgroup -g ${GID} -S docker
RUN adduser -S -D -u ${UID} -h /home/docker docker -G docker

RUN  apk add --no-cache libzip-dev postgresql-dev bash

RUN docker-php-ext-install pdo_pgsql intl zip opcache

COPY --from=composer/composer:2-bin /composer /usr/bin/composer

RUN curl -sS https://get.symfony.com/cli/installer | bash \
    && mv /root/.symfony5/bin/symfony /usr/local/bin/symfony


ADD php-base.ini /usr/local/etc/php/conf.d/php-base.ini
ADD php-${APP_ENV}.ini /usr/local/etc/php/conf.d/php-${APP_ENV}.ini

USER docker

WORKDIR ${APP_DIR}

CMD ["php-fpm"]
