# Symfony 6 Framework Hands-On 2022

Учебный проект на платформе udemy.com

Для проекта нужен docker (docker desktop) а так же docker-compose

Настроить свои переменные в файле `.env` для **docker  контейнеров** на основе файла `.env.dist` 
```shell
cp .env.dist .env
```

Настроить свои переменные в файле `app/.env` **для symfony приложения** на основе файла `.env.dist` 
```shell
cp app/.env.dist app/.env
```


Собрать и запустить контейнеры проекта
```shell
docker-compose up -d --build
```
#### Контейнеры и их назначение:

|Контейнер| Назначение и комментарий|
|---------|-------------------------|
|sy6-database| Postgres база (подключение через localhost порт 5432)
|sy6-mailcatcher| Для разработки и отладки отправки писем с symfony с интерфейсом просмотра писем http://localhost:1080
|sy6-nginx | Вэб сервер проекта по адресу http://localhost
|sy6-php| Контейнер с symfony с php-fpm, composer, и symfony cli утилитой.
