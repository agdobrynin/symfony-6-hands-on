SHELL := /bin/bash

build-up:
	@docker-compose up -d --build
	@docker-compose ps

build:
	@docker-compose build
	@docker-compose ps

up:
	@docker-compose up -d
	@docker-compose ps

stop:
	@docker-compose stop
