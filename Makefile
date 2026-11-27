.PHONY: help setup install up down restart build build-front build-docker dev test pint lint analyze bash mysql art artisan migrate fresh seed free-port free-vite

# Si el comando principal es `art` o `artisan`, aislar objetivos para evitar colisión con otros targets (ej. `migrate`, `test`, `seed`)
ifneq ($(filter art artisan,$(firstword $(MAKECMDGOALS))),)
  ART_ARGS := $(wordlist 2,$(words $(MAKECMDGOALS)),$(MAKECMDGOALS))
  ifneq ($(strip $(ART_ARGS)),)
    $(eval $(subst :,\:,$(ART_ARGS)):;@:)
  endif

art artisan:
	docker compose exec app php artisan $(if $(cmd),$(cmd),$(ART_ARGS) $(filter --%,$(MAKEOVERRIDES)))

else

APP_PORT ?= $(shell grep -E '^APP_PORT=' .env 2>/dev/null | cut -d '=' -f2)
ifeq ($(strip $(APP_PORT)),)
  APP_PORT := 8080
endif
DB_PORT ?= $(shell grep -E '^DB_PORT=' .env 2>/dev/null | cut -d '=' -f2)
ifeq ($(strip $(DB_PORT)),)
  DB_PORT := 3306
endif
PMA_PORT ?= 8081
VITE_PORT ?= 5173

help: ## Muestra la lista de comandos disponibles
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

free-port: ## Libera preventivamente los puertos de Docker ($$APP_PORT, $$DB_PORT, $$PMA_PORT)
	@chmod +x scripts/free-port.sh
	@./scripts/free-port.sh $(APP_PORT) $(DB_PORT) $(PMA_PORT)

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

art: ## Ejecuta cualquier comando Artisan en el contenedor (ej: make art migrate:status o make art cmd="migrate --path=...")
	docker compose exec app php artisan $(if $(cmd),$(cmd),$(ART_ARGS) $(filter --%,$(MAKEOVERRIDES)))

artisan: art

migrate: ## Ejecuta migraciones en el contenedor (opcional: path=packages/core/Admin/database/migrations)
	docker compose exec app php artisan migrate $(if $(path),--path=$(path),)

fresh: ## Reconstruye la base de datos desde cero ejecutando migraciones y seeders
	docker compose exec app php artisan migrate:fresh --seed

seed: ## Ejecuta los seeders en el contenedor (opcional: class=NombreSeeder)
	docker compose exec app php artisan db:seed $(if $(class),--class=$(class),)

mysql: ## Abre una consola interactiva de MySQL dentro del contenedor db
	docker compose exec db mysql -u$(shell grep -E '^DB_USERNAME=' .env 2>/dev/null | cut -d '=' -f2 || echo ugm_user) -p$(shell grep -E '^DB_PASSWORD=' .env 2>/dev/null | cut -d '=' -f2 || echo ugm_secret) $(shell grep -E '^DB_DATABASE=' .env 2>/dev/null | cut -d '=' -f2 || echo ungolmas_core)

pint: ## Formatea automáticamente el código PHP con Laravel Pint
	./vendor/bin/pint

lint: ## Valida el estilo del código PHP sin modificarlo
	./vendor/bin/pint --test

analyze: ## Ejecuta análisis estático estricto con PHPStan / Larastan
	./vendor/bin/phpstan analyse --memory-limit=2G

endif