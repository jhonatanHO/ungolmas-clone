<p align="left">
    <a href="https://laravel.com" target="_blank">
        <img src="https://api.ungolmas.com/assets/img/ungolmas-logo.svg" width="250" alt="UNGOLMAS">
    </a>
</p>

<img src="https://img.shields.io/badge/Laravel-%5E13.0-red?logo=Laravel" alt="Laravel ^13.0 Badge">
<a href="https://www.php.net/releases/8.4/en.php">
    <img src="https://img.shields.io/badge/PHP-8.4-blue.svg?logo=php" alt="PHP 8.4 Badge">
</a>
<a href="https://httpd.apache.org/">
    <img src="https://img.shields.io/badge/Apache-2.4-D22128.svg?logo=apache" alt="Apache 2.4 Badge">
</a>
<a href="https://www.mysql.com/">
    <img src="https://img.shields.io/badge/MySQL-8.0-4479A1.svg?logo=mysql" alt="MySQL 8.0 Badge">
</a>
<a href="https://vuejs.org/">
    <img src="https://img.shields.io/badge/Vue-3.5-4FC08D.svg?logo=vuedotjs" alt="Vue 3.5 Badge">
</a>
<a href="https://www.typescriptlang.org/">
    <img src="https://img.shields.io/badge/TypeScript-5.7-3178C6.svg?logo=typescript" alt="TypeScript 5.7 Badge">
</a>
<a href="https://tailwindcss.com/">
    <img src="https://img.shields.io/badge/Tailwind_CSS-3.4-38B2AC.svg?logo=tailwindcss" alt="Tailwind CSS 3.4 Badge">
</a>
<a href="https://vitejs.dev/">
    <img src="https://img.shields.io/badge/Vite-6.0-646CFF.svg?logo=vite" alt="Vite 6.0 Badge">
</a>
<a href="https://pnpm.io/">
    <img src="https://img.shields.io/badge/pnpm-9.x-orange.svg?logo=pnpm" alt="pnpm Badge">
</a>

Web UNGOLMAS platform, developed with `PHP 8.4`, `Laravel 13`, `Apache 2.4`, `MySQL 8.0`, `Vue 3.5`, `TypeScript 5.7` and `Tailwind CSS`.

# UNGOLMAS Core (ugm-core)

Base técnica y arquitectónica de la plataforma modular de juegos y gamificación UNGOLMAS.

---

## 🏛 Arquitectura del Sistema (Laravel 13 + Monolito Modular)

UNGOLMAS Core adopta una arquitectura de **Monolito Modular con Puertos y Adaptadores (Arquitectura Hexagonal)**:

- **Cascarón (Shell - `app/`)**: El framework Laravel 13 opera **únicamente** como orquestador de bootstrap (`bootstrap/app.php`), enrutamiento de entrada y middleware global. **Está estrictamente prohibido colocar lógica de negocio en `app/`**.
- **Módulos Core (`packages/core/`) — 6 Paquetes**:
  - `Config`: Parámetros maestros en base de datos (`t_config`), variables dinámicas y caché file.
  - `User`: Identidad, perfiles, roles (`Admin`, `Game`, `Soporte`), sesiones y tokens Sanctum.
  - `WebApp`: Portal Web público para usuarios finales, landing pages, torneos y autenticación frontend (`/webapp`).
  - `Admin`: Panel administrativo centralizado (`/cmsnet`), layout maestro y menú dinámico extensible.
  - `Gamification`: Wallets de doble economía (`coins` canjeables / `points` competitivos) y ledger inmutable.
  - `Game`: Motor de pollas, pronósticos y dinámicas lúdicas, desacoplado mediante eventos (`GameFinished`).
- **Integraciones (`packages/integrations/`) — 5 Paquetes**:
  - `News`: Conector API REST a WordPress Headless (`news.ungolmas.com`).
  - `Store`: Adaptador a eCommerce externo (`ungolmasstore.pe` / VTEX).
  - `Avatar`: Handshake y sincronización con generador de avatares (`avatargoleador.com`).
  - `Ranking`: Cálculo y tablas de posiciones de torneos.
  - `Ads`: Gestión y renderizado de pautas publicitarias.

### 📦 Estructura Canónica de cada Paquete

Todos los paquetes modulares (`packages/core/*` e `packages/integrations/*`) siguen el estándar estricto de paquetes Laravel (Spatie / Bagisto):

```text
packages/{core|integrations}/{PackageName}/
├── config/              # Archivos de configuración PHP (sin declare(strict_types=1))
├── database/            # Migraciones y seeders de BD
│   ├── migrations/
│   └── seeders/
├── resources/           # Recursos visuales y de internacionalización
│   ├── lang/            # Diccionarios de idioma (es/, en/)
│   └── views/           # Vistas Blade del paquete
├── routes/              # Definición de rutas (web.php, api.php)
└── src/                 # Clases PHP bajo namespace PSR-4 (declare(strict_types=1) obligatorio)
    ├── Contracts/       # Interfaces y puertos hexagonales
    ├── Http/Controllers/# Controladores específicos del módulo
    ├── Models/          # Modelos Eloquent
    └── Providers/       # Service Provider del paquete
```

> [!IMPORTANT]
> **Convención Estricta de Nomenclatura de Directorios**:
> - **Raíz del Paquete y Recursos (`lowercase`)**: `config/`, `database/`, `resources/`, `routes/` y `src/` residen en minúsculas en la raíz del paquete. Asimismo, los subdirectorios dentro de `resources/` (`views/`, `lang/`, `assets/`) son estrictamente en minúsculas. Esto garantiza que las herramientas de análisis estático (PHPStan/Larastan) y los servidores de lenguaje (LSP) no confundan archivos de datos o plantillas con clases PHP.
> - **Namespaces PSR-4 dentro de `src/` (`PascalCase`)**: Todo subdirectorio dentro de `src/` (`Contracts/`, `Http/Controllers/`, `Models/`, `Providers/`, etc.) inicia con mayúscula (**PascalCase**) porque mapea exactamente 1:1 con el Namespace PHP (`Ugm\{PackageName}\...`). En sistemas de archivos case-sensitive (Linux en Docker y servidores de producción como SiteGround), cualquier discrepancia de mayúsculas/minúsculas provocaría errores fatales de clase no encontrada (`Class not found`).

---

## 📋 Tabla de Comandos Make

El proyecto incluye un [Makefile](Makefile) que automatiza todo el ciclo de vida. **Antes de levantar o entrar en modo dev, el sistema verifica y libera automáticamente los puertos en conflicto (`8080` y `5173`)** mediante [`scripts/free-port.sh`](scripts/free-port.sh) para prevenir errores de asignación.

| Comando             | Categoría               | ¿Qué hace?                                                                                                                                                                                    |
| ------------------- | ----------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `make setup`        | **Instalación Inicial** | **Pone en marcha el proyecto desde cero**: libera puertos, genera `.env`, instala dependencias Composer, levanta Docker, genera `APP_KEY`, corre migraciones de BD e instala paquetes `pnpm`. |
| `make up`           | **Levantamiento**       | Libera preventivamente el puerto `8080` y levanta todos los contenedores Docker en segundo plano (`ugm_core_app`, `ugm_core_web`, `ugm_core_db`, `ugm_core_phpmyadmin`).                   |
| `make dev`          | **Modo Dev**            | Libera preventivamente el puerto `5173` e inicia el servidor de desarrollo Vite con Hot Module Replacement (HMR) para Vue 3 y Tailwind CSS.                                                   |
| `make test`         | **Modo Test**           | Ejecuta la suite de pruebas unitarias y de integración de paquetes y cascarón utilizando Pest PHP (`pestphp/pest: ^3.7`).                                                                      |
| `make build`        | **Modo Build**          | Ejecuta la compilación completa de producción: compila los assets frontend con Vite (`pnpm build`) y reconstruye las imágenes Docker sin caché.                                               |
| `make build-front`  | **Modo Build**          | Compila exclusivamente los assets de TypeScript, Vue 3 y Tailwind CSS con Vite para despliegue en producción.                                                                                 |
| `make build-docker` | **Modo Build**          | Reconstruye las imágenes de Docker (`app`, `web`) desde cero sin usar caché.                                                                                                                  |
| `make install`      | **Dependencias**        | Libera puertos e instala las dependencias de Composer y pnpm localmente.                                                                                                                      |
| `make down`         | **Mantenimiento**       | Detiene y remueve los contenedores Docker de la red.                                                                                                                                          |
| `make restart`      | **Mantenimiento**       | Reinicia los contenedores Docker ejecutando `down` y luego `up`.                                                                                                                              |
| `make bash`         | **Mantenimiento**       | Abre una terminal interactiva bash dentro del contenedor `app` (PHP 8.4 FPM).                                                                                                                 |
| `make migrate`      | **Base de Datos**       | Ejecuta las migraciones de base de datos pendientes dentro del contenedor.                                                                                                                    |
| `make fresh`        | **Base de Datos**       | Reconstruye la base de datos completa desde cero (`migrate:fresh --seed`).                                                                                                                    |
| `make seed`         | **Base de Datos**       | Ejecuta los seeders de datos iniciales en la base de datos.                                                                                                                                   |
| `make pint`         | **Calidad de Código**   | Formatea automáticamente el código PHP según las reglas de Laravel Pint.                                                                                                                      |
| `make lint`         | **Calidad de Código**   | Verifica el estilo de código PHP en modo prueba sin alterar archivos.                                                                                                                         |
| `make analyze`      | **Calidad de Código**   | Ejecuta análisis estático estricto en nivel 8 con PHPStan / Larastan (`larastan/larastan: ^3.0`).                                                                                              |
| `make free-port`    | **Redes / DevOps**      | Corta / mata cualquier proceso o contenedor local que esté bloqueando el puerto web (`APP_PORT=8080`).                                                                                        |
| `make free-vite`    | **Redes / DevOps**      | Corta / mata cualquier proceso o contenedor local que esté bloqueando el puerto de Vite (`5173`).                                                                                             |

---

### 🪟 Compatibilidad con Windows: ¿Cómo instalar y usar `make`?

En Windows, `make` **no viene instalado por defecto**, pero es totalmente compatible mediante cualquiera de las siguientes alternativas:

#### Opción 1: En WSL2 (Ubuntu / Debian) — _Recomendada si usas Docker Desktop_

Si trabajas dentro del subsistema Linux en Windows (WSL2):

```bash
sudo apt update && sudo apt install make
```

_A partir de allí puedes ejecutar `make setup`, `make up`, `make dev`, etc., exactamente igual que en macOS/Linux._

#### Opción 2: En PowerShell / CMD nativo

Puedes instalar GNU Make en Windows con un solo comando desde tu terminal de administrador:

- **Con Winget** (incluido por defecto en Windows 10 y 11):
  ```powershell
  winget install GnuWin32.Make
  ```
- **Con Chocolatey**:
  ```powershell
  choco install make
  ```
- **Con Scoop**:
  ```powershell
  scoop install make
  ```

> [!TIP]
> Tras instalarlo con Winget, Chocolatey o Scoop, reinicia tu terminal (PowerShell o CMD) y verifica la instalación escribiendo `make --version`.

#### Opción 3: Sin instalar `make` (Comandos directos en Windows)

Si algún miembro del equipo prefiere no instalar `make`, puede ejecutar directamente las instrucciones nativas equivalentes en PowerShell, CMD o Git Bash:

| En vez de ejecutar | Comando directo equivalente en Windows (PowerShell / CMD)  |
| ------------------ | ---------------------------------------------------------- |
| `make up`          | `docker compose up -d`                                     |
| `make down`        | `docker compose down`                                      |
| `make dev`         | `pnpm dev`                                                 |
| `make test`        | `php artisan test`                                         |
| `make migrate`     | `docker compose exec app php artisan migrate`              |
| `make fresh`       | `docker compose exec app php artisan migrate:fresh --seed` |
| `make bash`        | `docker compose exec app bash`                             |
| `make build`       | `pnpm build && docker compose build`                       |
| `make pint`        | `./vendor/bin/pint` _(o `vendor\bin\pint` en Windows CMD)_ |

---

## 🚀 Guía Paso a Paso para Levantar el Proyecto

### A. Primera Instalación (Desde Cero)

Para un entorno recién clonado o configurado desde cero, solo debes ejecutar:

```bash
make setup
```

Este comando realiza automáticamente:

1. `make free-port`: Detiene cualquier contenedor o proceso que ocupe el puerto `8080`.
2. Crea el archivo `.env` a partir de `.env.example` si no existe.
3. Instala los paquetes de PHP con Composer (`composer install`).
4. Levanta los contenedores en Docker (`ugm_core_app`, `ugm_core_web`, `ugm_core_db`, `ugm_core_phpmyadmin`).
5. Genera la llave criptográfica `APP_KEY`.
6. Corre las migraciones iniciales de MySQL.
7. Instala las dependencias de frontend con `pnpm install`.

---

### B. Flujo Diario de Trabajo (Modo Dev)

En el día a día para trabajar en el proyecto:

```bash
# 1. Levantar el backend, servidor Apache y la base de datos (libera el puerto 8080 automáticamente):
make up

# 2. Iniciar el servidor frontend con Hot Reload (libera el puerto 5173 automáticamente):
make dev
```

#### 🌐 URLs de Acceso Local:

- **Portal Web Principal / Landing**: [http://localhost:8080](http://localhost:8080)
- **Portal WebApp (Módulo Core)**: [http://localhost:8080/webapp](http://localhost:8080/webapp)
- **Panel Administrativo (CMSNET)**: [http://localhost:8080/cmsnet](http://localhost:8080/cmsnet)
- **Panel phpMyAdmin**: [http://localhost:8081](http://localhost:8081) _(Servidor: `db` \| Usuario: `ugm_user` \| Clave: `ugm_secret`)_
- **Servidor Vite (HMR Dev)**: [http://localhost:5173](http://localhost:5173)
- **MySQL Directo**: `localhost:3306` _(Base de datos: `ungolmas_core` \| Usuario: `ugm_user`)_

---

### C. Modo Pruebas (Modo Test)

Para validar que los contratos, modelos y endpoints de los paquetes funcionen sin fallos:

```bash
make test
```

_Ejecuta Pest PHP utilizando SQLite en memoria para máxima velocidad de ejecución y aislamiento total._

Para análisis estático de tipado y estilo:

```bash
make lint       # Verifica estilo con Laravel Pint
make analyze    # Analiza tipos y contratos con PHPStan / Larastan en nivel 8
```

---

### D. Modo Compilación (Modo Build)

Cuando vayas a empaquetar para pruebas de integración o producción:

```bash
# Compilar frontend y reconstruir imágenes Docker:
make build

# O compilar únicamente los assets de producción de Vite:
make build-front
```

---

## ⚙️ ¿Cómo opera Laravel 13 en este proyecto?

1. **Bootstrap y Providers**:
   - `bootstrap/app.php` configura el pipeline de routing y middleware del cascarón.
   - `bootstrap/providers.php` registra los Service Providers de los **11 paquetes modulares** (`packages/core/*` e `packages/integrations/*`).
2. **Comunicación Web-PHP (Apache 2.4 con paridad SiteGround)**:
   - El contenedor `ugm_core_web` utiliza **Apache 2.4 Alpine** (`docker/apache/Dockerfile`) escuchando en el puerto `8080` del host.
   - Emplea `mod_proxy_fcgi` para reenviar el tráfico dinámico a `ugm_core_app:9000` (PHP 8.4 FPM).
   - Procesa directivas `.htaccess` nativas idénticas al entorno de producción en SiteGround, asegurando paridad exacta de comportamiento web.
3. **Control Automático de Puertos**:
   - Para evitar el error recurrente `Bind for 0.0.0.0:8080 failed: port is already allocated`, los targets `setup`, `up` y `dev` invocan preventivamente [`scripts/free-port.sh`](scripts/free-port.sh).
   - Este script detecta si otro contenedor de Docker o proceso huérfano (ej. túnel SSH de Colima o procesos locales) retiene el puerto, y lo detiene antes de inicializar los servicios.

---

## 🔒 Documentación Técnica y Directivas

- [AGENTS.md](AGENTS.md): Reglas de arquitectura, tipado estricto PHP 8.4 y convenciones para agentes de IA.
- [DESIGN.md](DESIGN.md): Tokens de diseño, interfaz visual y componentes UI.
- [SECURITY.md](SECURITY.md): Políticas de seguridad y reporte de vulnerabilidades.
- **Skills de IA para Desarrollo**: Ubicados en `.agents/skills/` (con enlace simbólico en `.claude/skills/`):
  - `combativa-develop-php`: Desarrollo backend, tipado estricto PHP 8.4 y arquitectura modular.
  - `combativa-develop-web`: Desarrollo frontend Vue 3, TypeScript y estilos Tailwind CSS.
- **Documento Maestro de Arquitectura**: En Obsidian: `02-Projects/📂 Combativa/UNGOLMAS.GLOBAL/UNGOLMAS CORE.md`.
- **Despliegue en Producción**: En Obsidian: `02-Projects/📂 Combativa/UNGOLMAS.GLOBAL/Deployment/2026-10-15-siteground-ssh.md`.

---

## License

MIT license

<img src="./resources/img/combativa.rm.svg?c=2" width="250" alt="Combativa">

© 2026 - Present [Combativa](https://combativa.com?resources=github)
