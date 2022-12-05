FROM php:8.1-fpm-alpine

ARG UID
ARG GID
ARG APP_DIR
ENV APP_DIR ${APP_DIR}

RUN addgroup -g ${GID} -S docker
RUN adduser -S -D -u ${UID} -h /home/docker docker -G docker

RUN  apk update && apk --no-cache add bash libzip-dev postgresql-dev

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN curl -sS https://get.symfony.com/cli/installer | bash && mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

RUN docker-php-ext-install pdo_pgsql intl zip

COPY init.sh /init.sh
RUN chmod +x /init.sh

USER docker

WORKDIR ${APP_DIR}

EXPOSE 9000

CMD [ "/init.sh" ]
