# Colors for terminal output
YELLOW := \033[1;33m
GREEN := \033[1;32m
RED := \033[1;31m
RESET := \033[0m

# Docker Compose command
DC := docker compose

# Load environment variables from .env file
include .env
export

# Default target
.DEFAULT_GOAL := help

# Show help
help: ## Show this help message
	@echo '${YELLOW}Usage:${RESET}'
	@echo '  make ${GREEN}<target>${RESET}'
	@echo ''
	@echo '${YELLOW}Targets:${RESET}'
	@awk 'BEGIN {FS = ":.*##"; printf ""} /^[a-zA-Z_-]+:.*?##/ { printf "  ${GREEN}%-15s${RESET} %s\n", $$1, $$2 }' $(MAKEFILE_LIST)

# Build and start containers
up: ## Build and start all containers
	@echo "${YELLOW}Starting all containers...${RESET}"
	$(DC) up -d --build
	@echo "${GREEN}All containers are up and running!${RESET}"

# Stop containers
down: ## Stop all containers
	@echo "${YELLOW}Stopping all containers...${RESET}"
	$(DC) down
	@echo "${GREEN}All containers stopped!${RESET}"

# Stop and remove all containers, volumes, and networks
clean: ## Stop and remove all containers, volumes, and networks
	@echo "${YELLOW}Cleaning up everything...${RESET}"
	$(DC) down -v --remove-orphans
	@echo "${GREEN}Clean up complete!${RESET}"

# Show container logs
logs: ## Show logs of all containers
	$(DC) logs -f

# Backend commands
symfony-bash: ## Access Symfony container bash
	@echo "${YELLOW}Accessing Symfony container...${RESET}"
	$(DC) exec ams_backend bash

composer-install: ## Install Composer dependencies
	@echo "${YELLOW}Installing Composer dependencies...${RESET}"
	$(DC) exec ams_backend composer install

symfony-cache-clear: ## Clear Symfony cache
	@echo "${YELLOW}Clearing Symfony cache...${RESET}"
	$(DC) exec ams_backend php bin/console cache:clear

# Database commands
mongo-shell: ## Access MongoDB shell
	@echo "${YELLOW}Accessing MongoDB shell...${RESET}"
	@$(DC) exec ams_db mongosh \
		--username "$(MONGO_ROOT_USERNAME)" \
		--password "$(MONGO_ROOT_PASSWORD)" \
		--authenticationDatabase admin \
		"mongodb://localhost:27017/$(MONGO_DATABASE)"

# Status commands
ps: ## Show container status
	$(DC) ps

# Development shortcuts
dev: up ## Start development environment
	@echo "${GREEN}Development environment is ready!${RESET}"
	@echo "Backend URL: http://localhost:80"
	@echo "MongoDB port: 27017"

# Testing commands
test-backend: ## Run backend tests
	@echo "${YELLOW}Running backend tests...${RESET}"
	$(DC) exec ams_backend php bin/phpunit

# Utility commands
init: ## Initialize project (first time setup)
	@echo "${YELLOW}Initializing project...${RESET}"
	@make up
	@make composer-install
	@make symfony-cache-clear
	@echo "${GREEN}Project initialized successfully!${RESET}"

restart: down up ## Restart all containers

logs-%: ## Show logs for a specific container (usage: make logs-backend)
	$(DC) logs -f $*

# Testing commands
test: ## Run all tests
	@echo "${YELLOW}Running all tests...${RESET}"
	$(DC) exec ams_backend php bin/phpunit

test-unit: ## Run unit tests only
	@echo "${YELLOW}Running unit tests...${RESET}"
	$(DC) exec ams_backend php bin/phpunit --testsuite=Unit

test-integration: ## Run integration tests only
	@echo "${YELLOW}Running integration tests...${RESET}"
	$(DC) exec ams_backend php bin/phpunit --testsuite=Integration

test-functional: ## Run functional tests only
	@echo "${YELLOW}Running functional tests...${RESET}"
	$(DC) exec ams_backend php bin/phpunit --testsuite=Functional

test-coverage: ## Run tests with coverage report
	@echo "${YELLOW}Running tests with coverage report...${RESET}"
	$(DC) exec ams_backend php bin/phpunit --coverage-html var/coverage

test-clear: ## Clear test cache
	@echo "${YELLOW}Clearing test cache...${RESET}"
	$(DC) exec ams_backend rm -rf var/cache/test
