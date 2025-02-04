DOCKER_COMPOSE := docker compose
SYMFONY := $(DOCKER_COMPOSE) exec -T php php -d memory_limit=2G bin/console
YARN := $(DOCKER_COMPOSE) run --rm node yarn
COMPOSER := $(DOCKER_COMPOSE) exec -T php composer

### Docker

up: ## Démarre les containers
	$(MAKE) run cmd='rm -rf var/cache/prod'
	$(DOCKER_COMPOSE) up  -d --no-recreate nginx

stop: ## Stop les containers
	$(DOCKER_COMPOSE) stop

down: ## Supprime les containers
	$(DOCKER_COMPOSE) down

build: ## Build les différentes images
	$(eval service :=)
	$(eval target :=)
	$(target) $(DOCKER_COMPOSE) build --no-cache $(service)

exec: ## Connexion au container php
	$(eval c := php)
	$(eval cmd := sh)
	$(DOCKER_COMPOSE) exec  $(c) $(cmd)

run: ## Démarre un container
	$(eval c := php)
	$(eval cmd := sh)
	$(DOCKER_COMPOSE) run --rm --no-deps $(c) $(cmd)

### Environnement de développement

init:
	$(MAKE) composer.json
	$(MAKE) build up assets-build fixtures jwt-tokens clear-cache fix-permissions

fixtures: vendor ## Charge les fixtures en base de données
	$(SYMFONY) doctrine:database:drop --if-exists --force
	$(SYMFONY) doctrine:database:create
	$(SYMFONY) doctrine:migrations:migrate --no-interaction --allow-no-migration
	$(SYMFONY) doctrine:fixture:load --no-interaction || true

grumphp: ## Lance grumphp
	$(DOCKER_COMPOSE) run --rm --no-deps php ./vendor/bin/grumphp run

phpunit: ## Lance phpunit
	$(DOCKER_COMPOSE) exec -T php ./vendor/bin/phpunit

clear-cache: ## Vide les caches applicatifs
	$(SYMFONY) c:c

fix-permissions: ## Corrige les problèmes de permissions
	$(DOCKER_COMPOSE) exec -T php chown -R www-data:www-data var public

vendor: ## Install les dépendances composer
	$(COMPOSER) install

migration: ## Créer un fichier de migration
	$(SYMFONY) make:migration

migrate: ## Lance les migrations
	$(SYMFONY) doctrine:migration:migrate -n

refresh-db:
	$(SYMFONY) doctrine:database:drop --if-exists --force
	$(SYMFONY) doctrine:database:create
	$(SYMFONY) doctrine:migrations:migrate --no-interaction --allow-no-migration

refresh-test-db:
	$(SYMFONY) doctrine:database:drop --if-exists --force -e test
	$(SYMFONY) doctrine:database:create -e test
	$(SYMFONY) doctrine:migrations:migrate --no-interaction --allow-no-migration -e test

jwt-tokens:
	$(SYMFONY) lexik:jwt:generate-keypair
	$(DOCKER_COMPOSE) exec -T php chown -R www-data:www-data config/jwt

composer:
	$(DOCKER_COMPOSE) build php
	$(DOCKER_COMPOSE) run --rm --no-deps php sh /usr/local/bin/install-symfony.sh
