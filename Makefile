DOCKER_ENABLED ?= 1
PHPSTAN_CONFIGURATION_FILE = bas.neon

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

pipeline-phpcs:
	php vendor/bin/php-cs-fixer fix --config=$(PHP_CS_FIXER_CONFIGURATION_FILE) --dry-run

pipeline-phpstan:
	php vendor/bin/phpstan analyse $(PHPSTAN_CODE_PATH) --configuration=$(PHPSTAN_CONFIGURATION_FILE)
