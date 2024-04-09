DOCKER_ENABLED = 1
include .boing/makes/symfony.mk

-include .env
-include .env.local

git = $(shell which git)

deploy:
	$(git) pull -fr
	$(MAKE) vendor
	$(MAKE) database-migration
	$(php) bin/console cache:clear --no-warmup

database-migration:
	$(php) bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration
