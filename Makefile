# Executables (local)
DOCKER_COMP = docker compose

# Docker containers
PHP_CONT = $(DOCKER_COMP) exec php
PHP_CONT_TEST = $(DOCKER_COMP) exec test-php

# Executables
PHP      = $(PHP_CONT) php
COMPOSER = $(PHP_CONT) composer
SYMFONY  = $(PHP) bin/console

## —— Docker 🐳 ————————————————————————————————————————————————————————————————
build: ## Builds the Docker images
	@$(DOCKER_COMP) build --pull --no-cache

up: ## Start the docker hub in detached mode (no logs)
	@$(DOCKER_COMP) up --detach

up-test: ## Start the docker hub in detached mode (no logs)
	@$(DOCKER_COMP) --profile test up --detach

set-permissions: ## Set file permissions
	@$(DOCKER_COMP) run --rm php sh -c "chown -R www-data:www-data /var/www/html/var && chmod -R 775 /var/www/html/var"

start: build up set-permissions ## Build and start the containers and set permissions
start-test: build up-test set-permissions ## Build and start the containers and set permissions
start-install: start install ## Start the containers and install the project

down: ## Stop the docker hub
	@$(DOCKER_COMP) down --remove-orphans

logs: ## Show live logs
	@$(DOCKER_COMP) logs --tail=0 --follow

sh: ## Connect to the PHP FPM container
	@$(PHP_CONT) bash

sh-test: ## Connect to the PHP FPM container
	@$(PHP_CONT_TEST) bash

clear-cache: ## Clear Symfony cache
	@$(SYMFONY) cache:clear
	@$(SYMFONY) cache:warmup

clear-pimcore-cache: ## Clear Pimcore cache
	@$(SYMFONY) pimcore:cache:clear
	@$(SYMFONY) pimcore:cache:warming

install: ## Install the project
	@$(COMPOSER) install --no-interaction --no-progress

install-pimcore: ## Install Pimcore
	@$(SYMFONY) pimcore:install --admin-username=Admin --admin-password=admin123 --install-bundles=false --no-interaction

import-teams: ## Import teams
	@$(SYMFONY) app:import-teams assets/documents/football_teams.xlsx