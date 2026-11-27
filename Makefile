.PHONY: help setup install up down restart build build-front build-docker dev test pint lint analyze bash migrate fresh seed free-port free-vite

APP_PORT ?= $(shell grep -E '^APP_PORT=' .env 2>/dev/null | cut -d '=' -f2)
ifeq ($(strip $(APP_PORT)),)
  APP_PORT := 8080
endif
VITE_PORT ?= 5173

help: ## Muestra la lista de comandos disponibles
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

free-port: ## Libera preventivamente el puerto del servidor web ($$APP_PORT)
	@chmod +x scripts/free-port.sh
	@./scripts/free-port.sh $(APP_PORT)

free-vite: ## Libera preventivamente el puerto de Vite ($$VITE_PORT)
	@chmod +x scripts/free-port.sh
	@./scripts/free-port.sh $(VITE_PORT)

setup: free-port ## Inicializa el proyecto desde cero (Copia .env, instala paquetes, levanta Docker y migra)
	@if [ ! -f .env ]; then cp .env.example .env && echo "📄 Archivo .env creado desde .env.example"; fi
	@echo "📦 Instalando dependencias de Composer..."
	@composer install --no-interaction
	@echo "🐳 Levantando contenedores Docker..."
	@docker compose up -d
	@echo "🔑 Generando clave de aplicación..."
	@docker compose exec -T app php artisan key:generate --force
	@echo "🗄️ Ejecutando migraciones de base de datos..."
	@docker compose exec -T app php artisan migrate --force
	@echo "📦 Instalando dependencias de frontend con pnpm..."
	@pnpm install
	@echo "✅ ¡Instalación completa! Accede a http://localhost:$(APP_PORT)"

install: free-port ## Instala dependencias de backend y frontend liberando puertos previamente
	composer install
	pnpm install

up: free-port ## Inicia los contenedores Docker liberando el puerto previamente
	docker compose up -d

down: ## Detiene y remueve los contenedores Docker
	docker compose down

restart: down up ## Reinicia todos los contenedores Docker

dev: free-vite ## Inicia el servidor de desarrollo Vite liberando el puerto 5173 previamente
	pnpm dev

test: ## Ejecuta la suite de pruebas automatizadas con PHPUnit / Artisan
	php artisan test

build: build-front build-docker ## Ejecuta el build completo (frontend Vite + imágenes Docker)

build-front: ## Compila los assets de frontend para producción con Vite
	pnpm build

build-docker: ## Construye o reconstruye las imágenes Docker sin caché
	docker compose build --no-cache

bash: ## Abre una terminal interactiva dentro del contenedor app (PHP 8.4)
	docker compose exec app bash

migrate: ## Ejecuta las migraciones pendientes en la base de datos
	docker compose exec app php artisan migrate

fresh: ## Reconstruye la base de datos desde cero ejecutando migraciones y seeders
	docker compose exec app php artisan migrate:fresh --seed

seed: ## Ejecuta los seeders de la base de datos
	docker compose exec app php artisan db:seed

pint: ## Formatea automáticamente el código PHP con Laravel Pint
	./vendor/bin/pint

lint: ## Valida el estilo del código PHP sin modificarlo
	./vendor/bin/pint --test

analyze: ## Ejecuta análisis estático estricto con PHPStan / Larastan
	./vendor/bin/phpstan analyse --memory-limit=2G