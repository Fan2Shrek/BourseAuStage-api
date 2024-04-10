DOCKER_ENABLED ?= 1

-include .env
-include .env.local

include .boing/makes/symfony.mk

git = $(shell which git)

deploy:
	$(git) pull -fr
	$(MAKE) vendor
	$(MAKE) database-migration
	$(MAKE) database-update
	$(php) bin/console cache:clear

database-migration:
	$(php) bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration
