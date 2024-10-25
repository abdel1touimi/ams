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

clean: ## Stop and remove all containers, volumes, and networks
	@echo "${YELLOW}Cleaning up everything...${RESET}"
	@$(DC) down -v --remove-orphans
	@echo "${YELLOW}Cleaning frontend build files...${RESET}"
	@rm -rf frontend/.next frontend/node_modules frontend/next.config.ts
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

# Frontend specific commands
frontend-dev-build: ## Build frontend in development mode
	@echo "${YELLOW}Building frontend in development mode...${RESET}"
	@$(DC) exec ams_frontend sh -c "pnpm tsc --noEmit && pnpm build"

# Frontend development commands
frontend-dev: ## Start frontend development server
	$(DC) exec ams_frontend pnpm dev

frontend-clean: ## Clean frontend build files
	@echo "${YELLOW}Cleaning frontend build files...${RESET}"
	@$(DC) exec ams_frontend sh -c "rm -rf .next"

frontend-build: ## Build frontend for production
	$(DC) exec ams_frontend pnpm build

frontend-lint: ## Run frontend linting
	$(DC) exec ams_frontend pnpm lint

frontend-lint-fix: ## Fix frontend linting issues
	$(DC) exec ams_frontend pnpm lint --fix

# Clean frontend configuration
clean-frontend-config: ## Clean frontend configuration files
	@echo "${YELLOW}Cleaning frontend configuration...${RESET}"
	@rm -f frontend/next.config.ts
	@echo "${GREEN}Frontend configuration cleaned!${RESET}"

# init-frontend target
init-frontend: clean-frontend-config ## Initialize frontend dependencies and configuration
	@echo "${YELLOW}Setting up frontend configuration...${RESET}"
	@echo "/** @type {import('next').NextConfig} */\nconst nextConfig = {\n  reactStrictMode: true,\n  output: 'standalone',\n  images: {\n    unoptimized: true\n  }\n};\n\nmodule.exports = nextConfig;" > frontend/next.config.js
	@if [ ! -f frontend/.eslintrc.json ]; then \
		cp frontend/.eslintrc.json.template frontend/.eslintrc.json; \
	fi
	@echo "${YELLOW}Installing frontend dependencies...${RESET}"
	@$(DC) exec ams_frontend pnpm install
	@if [ "${NODE_ENV}" = "production" ]; then \
		echo "${YELLOW}Building frontend for production...${RESET}"; \
		$(DC) exec ams_frontend pnpm build; \
	fi
	@echo "${GREEN}Frontend initialization complete!${RESET}"

# Utility commands
init: ## Initialize project (first time setup)
	@echo "${YELLOW}Initializing project...${RESET}"
	@make up
	@make composer-install
	@make generate-jwt
	@make symfony-cache-clear
	@make init-frontend
	@echo "${GREEN}Project initialized successfully!${RESET}"

generate-jwt: ## Generate JWT keys
	@echo "${YELLOW}Generating JWT keys...${RESET}"
	@if [ ! -d backend/config/jwt ]; then \
		mkdir -p backend/config/jwt; \
	fi
	@if [ ! -f backend/config/jwt/private.pem ]; then \
		$(DC) exec ams_backend openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096 -pass pass:$(JWT_PASSPHRASE); \
	fi
	@if [ ! -f backend/config/jwt/public.pem ]; then \
		$(DC) exec ams_backend openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout -passin pass:$(JWT_PASSPHRASE); \
	fi
	@echo "${GREEN}JWT keys generated successfully!${RESET}"

restart: down up ## Restart all containers

logs-%: ## Show logs for a specific container (usage: make logs-backend)
	$(DC) logs -f $*

# Testing commands
test: ## Run all tests
	@echo "${YELLOW}Running all tests...${RESET}"
	@make test-backend

# Testing commands
test-backend: ## Run backend tests
	@echo "${YELLOW}Running backend tests...${RESET}"
	$(DC) exec ams_backend php bin/phpunit

test-unit: ## Run unit tests only
	@echo "${YELLOW}Running unit tests...${RESET}"
	$(DC) exec ams_backend php bin/phpunit --testsuite=Unit

test-functional: ## Run functional tests only
	@echo "${YELLOW}Running functional tests...${RESET}"
	$(DC) exec ams_backend php bin/phpunit --testsuite=Functional

test-coverage: ## Run tests with coverage report
	@echo "${YELLOW}Running tests with coverage report...${RESET}"
	$(DC) exec ams_backend php bin/phpunit --coverage-html var/coverage

test-clear: ## Clear test cache
	@echo "${YELLOW}Clearing test cache...${RESET}"
	$(DC) exec ams_backend rm -rf var/cache/test

# Add to the validate target
validate: ## Validate project configuration
	@echo "${YELLOW}Validating project configuration...${RESET}"
	@$(DC) config --quiet
	@echo "${YELLOW}Checking backend dependencies...${RESET}"
	@$(DC) exec ams_backend composer validate
	@echo "${YELLOW}Checking frontend dependencies...${RESET}"
	@$(DC) exec ams_frontend pnpm audit
	@echo "${YELLOW}Running frontend linting...${RESET}"
	@make frontend-lint || true
	@echo "${GREEN}Configuration validation complete!${RESET}"
