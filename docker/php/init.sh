#!/bin/sh

cd "$APP_DIR"

if [ -f composer.json ]; then
    composer install --prefer-source --no-interaction
fi

if [ "$( find ./migrations -iname '*.php' -print -quit )" ]; then
    php bin/console doctrine:migrations:migrate --no-interaction
fi
