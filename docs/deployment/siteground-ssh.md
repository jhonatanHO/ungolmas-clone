# Guía de Despliegue y Análisis Técnico: SiteGround SSH (UNGOLMAS Core)

Este documento detalla la investigación técnica, restricciones de infraestructura y estrategia de despliegue para **UNGOLMAS Core** en entornos de hosting gestionado **SiteGround (Site Tools)**.

---

## 🏗 Análisis del Entorno de Infraestructura SiteGround

SiteGround opera sobre infraestructura de Google Cloud Platform con una capa propietaria de gestión llamada **Site Tools**. A diferencia de un VPS estándar o servidor dedicado, presenta restricciones clave que determinan la arquitectura de despliegue:

| Característica                | Entorno SiteGround                                                                                 | Impacto en UNGOLMAS Core                                                                                           |
| ----------------------------- | -------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------ |
| **Acceso SSH**                | Shell enjaulado (_chrooted/jailed_), usuario sin privilegios `sudo`/`root`.                        | No se pueden instalar paquetes de sistema (`apt`, `yum`) ni servicios en background como `systemd` o `supervisor`. |
| **Binario PHP**               | Rutas específicas por versión: `/usr/local/php84/bin/php-cli` o `/usr/local/php84/bin/php`.        | Las tareas programadas (cron) y scripts artisan deben invocar la ruta absoluta explícita de PHP 8.4.               |
| **Directorio Web Público**    | Fijo e inmutable a nivel de servidor web Nginx/Apache: `/home/customer/www/{dominio}/public_html`. | El cascarón de Laravel sirve desde `/public`. Se requiere estrategia de symlink seguro.                            |
| **Servicios de Caché/Sesión** | Sin Redis garantizado en planes compartidos básicos; Memcached disponible vía panel.               | Por diseño estricto, UNGOLMAS Core utiliza el driver `file` para `CACHE_STORE` y `SESSION_DRIVER`.                 |

---

## 🔗 Estrategia de Symlink para `public_html`

En SiteGround, la raíz del documento para el servidor web es `/home/customer/www/{dominio}/public_html`. Para no exponer el código fuente de Laravel (`app/`, `packages/`, `.env`, `vendor/`), la aplicación se clona en un directorio hermano no accesible públicamente:

### Estructura de Directorios en el Servidor

```
/home/customer/www/{dominio}/
├── ungolmas-core/                # Repositorio privado (fuera del DocumentRoot)
│   ├── app/
│   ├── packages/
│   ├── public/
│   ├── storage/
│   ├── vendor/
│   └── .env
└── public_html -> ungolmas-core/public   # Enlace simbólico hacia public/
```

### Procedimiento de Configuración Vía SSH

1. Conéctate vía SSH al servidor de SiteGround:
   ```bash
   ssh -p 18765 u123-xxxxxx@ssh.domain.com
   ```
2. Navega al directorio del dominio:
   ```bash
   cd /home/customer/www/{dominio}
   ```
3. Si existe una carpeta `public_html` por defecto vacía o con archivos iniciales, respaldarla o eliminarla:
   ```bash
   mv public_html public_html_backup
   ```
4. Clona el proyecto UNGOLMAS Core:
   ```bash
   git clone git@github.com:combativa/ungolmas-core.git ungolmas-core
   cd ungolmas-core
   ```
5. Crea el symlink de `public_html` hacia `ungolmas-core/public`:
   ```bash
   ln -s /home/customer/www/{dominio}/ungolmas-core/public /home/customer/www/{dominio}/public_html
   ```

---

## ⏰ Configuración del Cron Job (Scheduler de Laravel)

SiteGround permite crear Cron Jobs desde la interfaz de **Site Tools > Devs > Cron Jobs** o vía terminal `crontab -e`.

El programador de tareas de Laravel (`artisan schedule:run`) debe ejecutarse **cada minuto**:

```cron
* * * * * /usr/local/php84/bin/php /home/customer/www/{dominio}/ungolmas-core/artisan schedule:run >> /dev/null 2>&1
```

> **NOTA CRÍTICA**: Siempre especifica `/usr/local/php84/bin/php` para asegurar que el CLI ejecute con PHP 8.4 y no con la versión predeterminada del sistema.

---

## ⚡ Estrategia de Colas (Queue Workers) sin Supervisor

Dado que SiteGround **no permite procesos permanentes ni demonios administrados por `supervisor` o `systemd`**, cualquier proceso en segundo plano de larga duración corre el riesgo de ser terminado por el recolector de procesos de SiteGround (_process killer_ por límites de CPU/RAM).

Para gestionar colas asíncronas de manera robusta y compatible en SiteGround, se adopta una **estrategia de dos pilares**:

### Pilar 1: Procesamiento por Lotes Vía Scheduler (`--stop-when-empty`)

En `routes/console.php`, registramos la ejecución de colas dentro del scheduler de Laravel que corre cada minuto:

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('queue:work --stop-when-empty --tries=3 --timeout=60')
    ->everyMinute()
    ->withoutOverlapping();
```

- `--stop-when-empty`: Procesa todos los trabajos acumulados en la cola y finaliza inmediatamente. No mantiene un proceso en memoria esperando trabajos.
- `withoutOverlapping()`: Evita que se levante un segundo trabajador si el anterior aún se encuentra procesando trabajos pesados.

### Pilar 2: Tarea de Respaldo y Monitoreo de Trabajos Fallidos

Para asegurar que ningún evento de gamificación o compra quede congelado:

```php
Schedule::command('queue:retry all')->dailyAt('04:00');
```

---

## 🚀 Script Automatizado de Despliegue (`deploy.sh`)

Crea un script ejecutable en el servidor SiteGround en `~/deploy-ugm.sh`:

```bash
#!/usr/bin/env bash
set -e

PROJECT_PATH="/home/customer/www/{dominio}/ungolmas-core"
PHP_BIN="/usr/local/php84/bin/php"
COMPOSER_BIN="/usr/local/bin/composer"

echo "=== Desplegando UNGOLMAS Core ==="
cd "$PROJECT_PATH"

# Poner en modo mantenimiento
$PHP_BIN artisan down --secret="ugm-deploy-bypass"

# Pull de código más reciente
git fetch origin main
git reset --hard origin/main

# Instalar dependencias backend
$COMPOSER_BIN install --no-dev --prefer-dist --optimize-autoloader --no-interaction

# Ejecutar migraciones de base de datos
$PHP_BIN artisan migrate --force

# Limpiar y regenerar cachés optimizadas
$PHP_BIN artisan config:cache
$PHP_BIN artisan route:cache
$PHP_BIN artisan view:cache

# Salir de modo mantenimiento
$PHP_BIN artisan up

echo "=== Despliegue finalizado con éxito ==="
```

---

## 🛡 Verificación Post-Despliegue

1. **Permisos de Archivos**:
   ```bash
   chmod -R 775 storage bootstrap/cache
   ```
2. **Validación de Driver de Sesiones y Caché**:
   Verificar que `.env` en producción contenga:
   ```env
   CACHE_STORE=file
   SESSION_DRIVER=file
   ```
3. **Comprobación de Salud**:
   Visitar el endpoint `/up` provisto por Laravel para verificar el estado de conexión a la base de datos y respuesta del servidor.
