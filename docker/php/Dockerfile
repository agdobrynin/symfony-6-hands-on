FROM php:fpm-alpine3.17

ARG UID
ARG GID
ARG APP_DIR
ENV APP_DIR ${APP_DIR}

RUN addgroup -g ${GID} -S docker
RUN adduser -S -D -u ${UID} -h /home/docker docker -G docker

RUN  apk update \
        && apk --no-cache add libzip-dev postgresql-dev bash

RUN docker-php-ext-install pdo_pgsql intl zip

COPY --from=composer:2.4.4 /usr/bin/composer /usr/local/bin/composer

RUN curl -sS https://get.symfony.com/cli/installer | bash \
    && mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

# TODO meke as ARG APP_ENV different config for DEV or PROD
COPY php-dev.ini /usr/local/etc/php/conf.d/php-dev.ini

USER docker

WORKDIR ${APP_DIR}

CMD ["php-fpm"]
