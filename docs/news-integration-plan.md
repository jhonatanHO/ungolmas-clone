# Integración News: análisis y plan de migración

**Estado:** tareas 1 y 2 implementadas; acceso WordPress confirmado tras autorizar la IP local. Tarea 3 implementada y disponible para revisión visual.  
**Fecha:** 2026-11-24.  
**Destino:** `packages/integrations/News`.  
**Rama de trabajo:** `ungolmas-core`, creada desde `develop` local.  
**Referencia:** [Combativa/ugm-docker-www, develop](https://github.com/Combativa/ugm-docker-www/tree/develop).  
**Commit analizado:** `dc6e5a6e7edcdfc94677f2ae74cab01afefd547c`.

**Copia local reanalizada:** `C:/Users/jhonatan/Downloads/ugm-docker-www-develop/ugm-docker-www-develop`.

**Fuente local principal elegida por el usuario:** `H:/clientes/LNM-COMBATIVA/ungolmasv2/ugm-docker-www/`. Usar este repositorio para el trabajo posterior; la descarga queda solo como contraste ya realizado. Mantener `develop` como referencia mediante sus objetos Git, sin cambiar de rama ni tocar los cambios locales del repositorio fuente.

**Aclaración de destino:** «Core» significa este desarrollo de **UNGOLMAS Core**, en `H:/clientes/LNM-COMBATIVA/UGMCORE/ungolmas-core`; no es WordPress ni otro repositorio descargado. `packages/core/` es una capa interna de este proyecto, no el destino solicitado para News. News pertenece a `packages/integrations/News` dentro de esta misma aplicación.

## 1. Objetivo y alcance

Migrar el consumidor WordPress y las páginas de noticias a un paquete desacoplado de Core, preservando sus URLs, contrato JSON, contenido y estructura visual. Las interfaces serán exclusivamente Blade, con Tailwind CSS y componentes reutilizables. Un módulo TypeScript pequeño podrá mejorar copiar enlace, sin Vue ni un runtime de aplicación para News.

Este documento diferencia comportamiento verificado en código, adaptaciones propuestas y dependencias pendientes. No acredita funcionamiento contra producción ni paridad visual validada en navegador.

### Límites

- WordPress conserva posts, categorías, medios, administración y hooks de su tema.
- No migrar el lector RSS SPORT del repositorio API, ni duplicar contenido en tablas locales.
- No copiar la aplicación Vue, autenticación, recompensas, marketing o configuración privada del portal anterior.
- No modificar Admin, Storybook ni el repositorio de referencia.
- No abrir credenciales, consultar servicios productivos, hacer commit o push sin autorización.
- No eliminar la carpeta vacía `packages/core/News` creada anteriormente sin solicitud expresa.

## 2. Método y evidencia

Se contrastó `origin/develop` con la referencia remota. La copia fuente está en otra rama con trabajo local: se inspeccionaron los objetos Git de `origin/develop` mediante `git show` y `git grep`, no sus archivos de trabajo como fuente de verdad.

El grafo de Core identifica `Ugm\News\Providers\NewsServiceProvider`; las comprobaciones de cobertura indican metadatos cambiados y cobertura parcial en estilos/Blade. Por ello se complementó el grafo con lecturas dirigidas. El índice fuente corresponde al checkout, no garantiza representar el commit elegido.

### Segunda revisión: copia descargada proporcionada por el usuario

Se compararon 30 archivos explícitos de la descarga contra el commit anterior, normalizando únicamente CRLF/LF. **Los 30 coinciden**: servicio, controller News, webhook, middleware, tres clases de componentes, rutas News/API, bootstrap, configuración services/custom, tres páginas y página vacía, cuatro templates de componentes, layout maestro, entrada y tres parciales SCSS News, pruebas de caché, Composer, package.json, Vite y app.js.

Esta verificación confirma el contenido de ese conjunto, no la identidad del commit de toda la descarga: no se encontró `.git` entre sus entradas raíz. No se inspeccionaron `.env`, `.envBase`, credenciales ni servicios remotos. La descarga no figura en el índice MCP; se utilizaron comparación de contenido y lecturas acotadas sin indexar masivamente el directorio.

El análisis y mapeo anteriores se mantienen. La revisión adicional del controlador base fuente y del layout WebApp de este Core precisa estas adaptaciones:

| Evidencia | Hallazgo / consecuencia |
| --- | --- |
| Fuente `app/Http/Controllers/Controller.php:29-49,52-67` | `setupConfig()`, `getPageData()` y `setting_SEO()` pertenecen al controlador global del portal. Generan configuración de aplicación/sesión y flags ajenos a News. Reemplazarlos por datos propios de página y SEO; no copiarlos al shell `app/` de Core. |
| Fuente `app/Http/Controllers/NewsController.php:40-45,62-98` | Se consulta `hero`, pero no se extrae ni se pasa a la vista. El fallback propuesto requiere conectarlo explícitamente; hoy no está disponible en el template. |
| Fuente `app/Http/Controllers/NewsController.php:172-177` | Se marca la visita en sesión incluso si `trackPostView()` devuelve false. Es el comportamiento actual; decidir y probar expresamente si se conserva o si se marca solo tras éxito. |
| Fuente `app/Http/Middleware/HeadlessNews.php:26-33` y controller News `:87,133,210` | Middleware y controller piden categorías en la misma petición. La caché suele evitar red repetida, pero con TTL desactivado hay consultas redundantes. Centralizar los datos para vistas News. |
| Core `packages/core/WebApp/resources/views/layouts/master.blade.php:13,35-49` | Ya hay layout Blade, título, carga de assets y parciales header/footer, pero no puntos de extensión SEO/News equivalentes al portal fuente. No asumir que extenderlo replica el diseño ni resuelve el SEO automáticamente. |
| Core `packages/core/WebApp/resources/views/layouts/headers/index.blade.php:1-26` y `layouts/footer/index.blade.php:1-5` | Son piezas simples del nuevo Core, distintas del shell del portal antiguo. Reutilización posible solo después de resolver fidelidad visual, rutas y assets, sin modificarlas globalmente por esta migración. |
| Core `packages/integrations/News/src/Providers/NewsServiceProvider.php:21-26,32-63` | El scaffold carga configuración/vistas/rutas, pero no comprueba `news.enabled` antes de registrar rutas. La bandera existe en configuración, no implementa todavía la desactivación prevista. |
| Core `composer.json:40` y `vite.config.ts:10-17` | Namespace ya mapeado al scaffold `News`; entradas de assets todavía no incluyen News. Adaptar el paquete existente y registrar sus assets. |

No se ejecutaron las pruebas fuente ni un build: esta segunda revisión es estática y comparativa, no una validación de conectividad ni de paridad visual.

### Tarea 1 — Referencia arquitectónica de Admin

Admin es la referencia de organización del paquete, no una funcionalidad a migrar ni un paquete que deba modificarse. La rama `package-core-admin` que aparece en la captura queda sustituida por `ungolmas-core`, según indicación del usuario.

Se revisaron el árbol versionado de `packages/core/Admin`, su Composer, provider, configuración, rutas web y una prueba de integración. El scaffold News ya replica la separación modular básica. La adaptación debe conservar ese trabajo y evitar crear un segundo paquete.

| Referencia de Admin | Aplicación prevista en News |
| --- | --- |
| `composer.json` | Conservar identidad `ugm/integration-news` y namespace `Ugm\News`; ajustar ruta PSR-4 raíz al cambiar casing. |
| `src/Providers/AdminServiceProvider.php:22-27,33-68` | Mantener combinación de configuración, namespace de vistas y agrupación de rutas desde un único provider News. No copiar comandos de usuarios Admin. |
| `config/config.php` | Configuración propia `news.*`; no copiar contraseñas de seed ni configuración de autenticación. |
| `routes/{web,api,admin}.php` | Conservar separación y placeholders existentes; implementar endpoints en las tareas posteriores, sin duplicar el prefijo API del provider. |
| `database/{migrations,seeders}` | Mantener directorios base, sin copiar tablas ni usuarios. Esta integración no necesita persistir posts locales. |
| `resources/views`, layouts y parciales | Reservar organización Blade propia de News; las pantallas se implementan en la tarea 3. |
| `resources/assets/{css,js}` | Conservar separación de assets; no copiar Vue, stores, login ni Storybook de Admin. |
| `src/{Contracts,Http,Models}` | Conservar organización PSR-4 del scaffold. Añadir servicios y componentes PHP cuando se implementen sus responsabilidades. |
| `tests/Feature` | Usar pruebas del paquete con el runner raíz ya configurado; sin copiar pruebas o datos de administradores. |

Estructura base preparada para la tarea 1. Por indicación del usuario, se retiraron los 7 `.gitkeep` nuevos; se conservan únicamente los heredados del scaffold. Los directorios nuevos vacíos del esquema no se conservarán en Git hasta que tengan archivos. No contiene todavía servicios WordPress ni vistas funcionales:

```text
packages/integrations/News/
├── composer.json
├── config/config.php
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   ├── assets/
│   │   ├── css/
│   │   └── js/
│   └── views/
│       ├── components/
│       ├── layouts/
│       └── partials/
├── routes/
│   ├── web.php
│   ├── api.php
│   └── admin.php
├── src/
│   ├── Contracts/
│   ├── Http/
│   │   ├── Controllers/
│   │   ├── Middleware/
│   │   └── Requests/
│   ├── Models/
│   ├── Providers/NewsServiceProvider.php
│   ├── Services/
│   └── View/Components/
└── tests/
    ├── Unit/
    └── Feature/
```

#### Separación de las tres tareas

1. **Plan y estructura:** revisión Admin, análisis fuente, mapeo, dependencias y adaptación del scaffold. Rama `ungolmas-core` creada desde `develop` local; tras probar el nombre minúsculo se conserva finalmente `News`, como en el scaffold original, por indicación del usuario. Se conservan provider, configuración, rutas y assets existentes, sin lógica nueva de conexión o presentación en esta fase. No se añaden `.gitkeep` ni documentación adicional al paquete; los directorios nuevos se versionarán cuando contengan su código.
2. **Conectar WordPress:** configuración de conexión, servicio, respuestas, errores, pruebas locales y caché exclusiva de consultas GET a la API durante **5 horas (18 000 segundos)**. Tras corregir la autorización por IP del WordPress desplegado, el usuario confirmó respuestas correctas en categorías, posts, destacados y populares. No se cachean páginas completas ni se implementan POST de visitas. Ver sección 14.
3. **Mostrar noticias:** controller público, rutas navegables, componentes Blade y migración Tailwind implementados. El detalle canónico utiliza `/noticia/{category_slug}/{slug}`, como exige la tarea; la URL plural original redirige con 301. Ver sección 15 para verificaciones y límites de paridad.

#### Validación de la tarea 1

- [x] Revisado Admin como referencia arquitectónica, sin modificar sus archivos.
- [x] Plan y mapeo preparados antes de modificar el scaffold.
- [x] Rama `ungolmas-core` creada desde `develop` local; ascendencia comprobada.
- [x] Paquete único en `packages/integrations/News`; estructura base comprobada antes de retirar los marcadores nuevos. Los directorios nuevos sin archivos no forman parte del contenido versionable.
- [x] Los 16 archivos del scaffold original se conservan; el provider se adapta únicamente para la carga base del paquete.
- [x] Mapeos PSR-4 raíz y del paquete, identidad Composer y registro único del provider verificados.
- [x] `composer dump-autoload --no-scripts --no-interaction` finalizado correctamente; sin instalar ni actualizar dependencias.
- [x] Carga real del provider mediante Composer comprobada por reflexión; ruta ajustada a `News` según indicación posterior del usuario.
- [x] Sintaxis PHP de los 5 archivos del scaffold y `git diff --check HEAD` correctos.
- [x] Código base del provider: respeta `news.enabled`, evita registrar de nuevo las rutas cuando están cacheadas y utiliza `custom.admin_url`, como Admin.
- [x] `NewsServiceProviderTest`: 4 pruebas, 12 aserciones correctas sobre registro, autoload, configuración y namespace de vistas. Ninguna solicitud HTTP.
- [x] Pint correcto en el provider y las pruebas añadidas.

La tarea 1 queda implementada en el árbol de trabajo, sin cambios staged, commit ni push. El renombrado físico se conserva junto con la actualización PSR-4; los `.gitkeep` heredados mantienen su contenido original. Se ejecutaron únicamente pruebas del código base del paquete, no pruebas funcionales de WordPress, build frontend ni validación Linux. No se hicieron solicitudes a WordPress ni se modificó `.env`.

No comenzar la tarea 2 automáticamente al completar el scaffold.

Anclas principales en el commit fuente:

- `app/Http/Controllers/NewsController.php:30-86`: consultas de portada y preparación de datos.
- `app/Http/Controllers/NewsController.php:104-132`: resolución de categoría y SEO.
- `app/Http/Controllers/NewsController.php:150-209`: artículo, pertenencia a categoría y populares.
- `app/Http/Controllers/NewsController.php:229-250`: API simple/full.
- `app/Services/WordPressService.php:243-264`: categoría con 24 posts y consultas concurrentes.
- `app/Services/WordPressService.php:326-357`: categorías embebidas y campos normalizados.
- `resources/views/news/index.blade.php:14-35`: cuatro posiciones destacadas indexadas directamente.
- `resources/js/app.js:553-582`: dependencia del lazy loader anterior.

## 3. Arquitectura actual y flujos

```text
WordPress + tema headless + extensiones pendientes de identificar
    ↓ REST /wp-json/wp/v2, medios y endpoint de visitas
WordPressService → HTTP / pool → caché → normalización
    ↓
HeadlessNews + NewsController → Blade → shell global del portal

WordPress → POST webhook autenticado → invalidación de caché
```

### Servicio

`WordPressService` usa HTTP y Cache de Laravel y Carbon. Resuelve `services.wordpress.api_url`, con fallback a `custom.wp_api + /wp-json/wp/v2`. Acepta GET individual o grupos nombrados concurrentes; normaliza respuestas de posts.

Normalización existente: `id`, `title`, `slug`, `date` en español, `author`, `image`, `images`, `excerpt`, `contentHtml`, `categories`, `category_slug`, `category_name`, `yoast_head_json`, `views_count`, `is_featured`. Obtiene autor, medios y términos desde `_embedded`, e inserta `referrerpolicy="no-referrer"` en imágenes del contenido/extracto. Los fallbacks actuales incluyen imágenes externas; se propone sustituirlos por un recurso local.

La navegación pide hasta 100 categorías con `hide_empty=true`, excluye `uncategorized` y `sin-categoria`. No es una paginación completa de todas las categorías. Mantener este límite inicialmente y documentarlo.

### Páginas y API

| Método / URL | Nombre existente | Comportamiento a conservar |
| --- | --- | --- |
| GET `/noticias` | `news.index` | Solicita hero (1), destacados (4, `featured=true`) y últimas (40). |
| GET `/noticias/{category_slug}` | `news.category` | Busca categoría por slug; 404 si no existe; consulta 24 posts por defecto. |
| GET `/noticias/{category_slug}/{slug}` | `news.post` | Busca categoría/artículo en paralelo, valida pertenencia; SEO Yoast con fallback; solicita 8 populares. |
| GET `/api/news` | `news.index.api` | Solicita 20 posts, no los 10 mencionados en comentarios antiguos. |
| POST `/api/webhooks/wordpress` | Sin nombre requerido | Autentica token y solicita invalidación acotada. |

La portada renderiza el hero desde `postsTop[0]`, aunque también consulta `hero`; no cambiar silenciosamente la prioridad editorial. Definir fallback seguro: primer destacado → hero → primer post disponible; ocultar posiciones inexistentes sin duplicar innecesariamente tarjetas.

El API conserva `status: success`, `data.news` y campos `title`, `fecha`, `link`, `slug`. `typeResult=full` agrega `image` e `images`. Los enlaces apuntan al frontend Core, no a WordPress. Mantener compatibilidad del parámetro camelCase externo, aunque configuración y métodos internos sigan las convenciones Core.

El artículo usa `viewed_posts` en sesión para evitar repetir el POST de visitas en esa sesión. El registro es best-effort y nunca debe impedir leer el artículo. Probar si la marca se guarda solo tras éxito; no introducir reintentos automáticos del POST sin garantía de idempotencia.

### Caché y webhook existentes

- TTL configurable, 600 segundos por defecto; TTL <= 0 evita caché.
- Clave basada en consulta o pool, excluyendo `_` (cache-buster).
- Tags `wp_api` para una lista fija de drivers; otros usan `wp_cache_keys_registry` persistente.
- El webhook acepta `X-WP-Webhook-Token` o `secret_token`; configuración ausente o token incorrecto devuelve 401.
- El registro manual puede crecer y sufrir carreras; la lista fija de drivers puede no representar la capacidad real del store. No copiar estas limitaciones sin revisión.

## 4. WordPress: contrato y límites del material recibido

La carpeta suministrada `ugm-news-main` es un tema headless, no una instalación completa. Sus módulos proporcionan restricción REST por IP, redirección del frontend, tamaños de imágenes, conversión WebP y rutas de medios bajo `/storage`.

La IP de salida del servidor Laravel debe estar autorizada. La restricción puede devolver **400**, no necesariamente 401/403. Verificar confianza en cabeceras de proxy; no copiar esa lógica al consumidor Core.

| Capacidad | Estado |
| --- | --- |
| Posts/categorías REST y `_embed` | Contrato consumido por el portal; verificar con fixtures y luego en entorno autorizado. |
| `rectangle-3xl/xl/lg/md/sm`, `square-md/sm` | Registrados por el tema recibido. |
| `featured`, `is_featured` | Extensión utilizada por Laravel; proveedor no confirmado en el tema revisado. |
| `orderby=views`, `views_count` | Extensión utilizada; proveedor pendiente. |
| POST `/wp-json/blogugm/v1/posts/{id}/view` | Usado por Laravel; implementación WordPress pendiente de localizar. |
| `yoast_head_json` | Campo opcional; confirmar plugin y disponibilidad, conservar fallback. |
| Emisor del webhook y eventos de publicación/edición/borrado | Endpoint receptor identificado; emisor pendiente de verificar. |

No asumir que `sticky=true` equivale a `featured=true`: el helper `getIndexPosts()` usa sticky y 12 elementos, pero la portada activa tiene otro contrato. No sustituir uno por otro sin aprobación.

## 5. Situación de Core

Ya existe `packages/integrations/News`, identidad Composer `ugm/integration-news`, namespace `Ugm\News`. Su provider combina configuración `news`, carga vistas `news::`, migraciones y rutas web/API/admin. La configuración actual solo contiene `enabled`; las rutas web son un placeholder.

`composer.json` raíz contiene el mapeo PSR-4 hacia `packages/integrations/News/src/`; `bootstrap/providers.php:12,30` ya registra el provider. **Adaptar este paquete, no crear otro.**

Se probó inicialmente el cambio de casing a `news`. Por indicación posterior del usuario se restauró `News`, el nombre original de Daniel, y se ajustaron Composer, Vite, vistas y pruebas a esa ruta. Se mantienen namespace, identidad y registro único del provider. No se cambia el casing de otros paquetes ni se deja un renombrado en staging.

Core usa Tailwind 4/Vite y tiene entradas de root, Admin y WebApp; falta planificar las de News. `phpunit.xml:9-14` ya descubre pruebas dentro de `packages/*/*/tests/{Unit,Feature}`. No hace falta añadir un runner paralelo.

## 6. Mapeo fuente → destino propuesto

Todas las rutas destino siguientes son relativas a `packages/integrations/News/` salvo indicación contraria.

| Fuente develop | Destino / adaptación |
| --- | --- |
| `app/Services/WordPressService.php` | `src/Services/WordPressService.php`; HTTP validado, configuración propia y contrato inyectable. |
| Consumidor implícito del servicio | `src/Contracts/NewsRepository.php`; operaciones explícitas para portada, categorías, artículo, listado y visitas. |
| Caché dentro del servicio | `src/Services/NewsCacheService.php`; invalidación acotada y store configurable. |
| `app/Http/Controllers/NewsController.php` | `src/Http/Controllers/NewsController.php`; `index`, `category`, `show`; separar API en `NewsApiController.php`. |
| `app/Http/Controllers/WordPressWebhookController.php` | `src/Http/Controllers/WordPressWebhookController.php`; comparación segura y respuesta compatible. |
| `app/Http/Middleware/HeadlessNews.php` | Composer de vistas o datos explícitos para vistas News; no modificar `services.*` por petición ni compartir globalmente con Admin. |
| `app/View/Components/NewsBlock.php` | `src/View/Components/NewsBlock.php`, propiedades tipadas y variantes validadas. |
| `app/View/Components/NewsImage.php` | `src/View/Components/NewsImage.php`, tamaños/fallback y carga nativa. |
| `app/View/Components/SharePage.php` | `src/View/Components/SharePage.php`, URLs codificadas. |
| `resources/views/news/{index,categories,single}.blade.php` | `resources/views/{index,categories,single}.blade.php`. |
| Cuatro templates de `resources/views/news/components/` | `resources/views/components/{news-block,news-image,news-nav-categories,share-page}.blade.php`. |
| `resources/views/layouts/theme-master.blade.php` | `resources/views/layouts/news.blade.php`; shell Blade acotado, sin copiar runtime Vue. |
| `resources/views/components/seo.blade.php` y `Helper::getURLComplete()` | Componente SEO propio, canonical desde rutas/Request; sin depender de Helper global fuente. |
| Includes header/footer/sidebar y publicidad | Parciales Blade propios o componentes Blade Core verificados; slots de publicidad desacoplados. |
| `resources/scss/news.scss` y parciales News | `resources/assets/css/news.css` solo como entrada Tailwind/tokens; estilos de componentes mediante utilities. |
| Lazy loader y función global de copiar | Carga nativa y `resources/assets/js/news.ts` mínimo, sin Vue. |
| `public/assets/img/default/news-image-default.svg` | Recurso local del paquete con publicación o importación Vite definida y probada. |
| `routes/news.php`, webhook en `routes/api.php` | `routes/web.php` y `routes/api.php` del paquete. |
| `config/services.php`, `config/custom.php` | `config/config.php` bajo `news.*`; variables documentadas en `.env.example` raíz. |
| `tests/Feature/WordPressCacheTest.php` | Pruebas en `tests/Unit` y `tests/Feature` del paquete; sin limpiar caché compartida. |
| `resources/views/news/page.blade.php` | Archivo vacío; no migrar ni inventar una ruta. |

Los nombres de archivos adicionales son diseño propuesto, no clases ya presentes. Datos externos deben tener validación y tipos/array shapes explícitos; puede añadirse un DTO readonly si reduce ambigüedad sin romper la salida JSON.

## 7. Configuración y conectividad propuestas

Solo leer `env()` desde configuración. No leer `.env` ni incrustar secretos en las vistas.

| Variable | Configuración `news.*` | Valor / criterio |
| --- | --- | --- |
| `UGM_NEWS_ENABLED` | `enabled` | Mantener bandera existente; deshabilitar rutas sin hacer HTTP. |
| `WP_API` | `wordpress.base_url` | Mantener compatibilidad; URL base, ejemplo `https://news.example.test`. |
| `WORDPRESS_CACHE_TTL` | `cache.ttl` | 18 000 segundos (5 horas), según tarea 2; <= 0 desactiva. Los 600 segundos corresponden únicamente a la referencia fuente. |
| `WORDPRESS_WEBHOOK_SECRET` | `webhook.secret` | Vacío por defecto; rechazar webhooks hasta configurarlo. |
| `UGM_NEWS_CACHE_STORE` | `cache.store` | Null → store Laravel predeterminado. |
| `UGM_NEWS_HTTP_TIMEOUT` | `wordpress.timeout` | Propuesta: 10 segundos. |
| `UGM_NEWS_HTTP_CONNECT_TIMEOUT` | `wordpress.connect_timeout` | Propuesta: 3 segundos. |
| `UGM_NEWS_HTTP_RETRIES` | `wordpress.retries` | Propuesta: un reintento adicional, solo GET transitorio. |

Derivar REST `/wp-json/wp/v2` y visitas `/wp-json/blogugm/v1/posts/{id}/view` de la base; no usar sustituciones ambiguas de cadenas. Validar esquema/host de configuración; endpoints y filtros permitidos definidos por el servidor, nunca una URL libre de la petición pública. Producción requiere HTTPS; HTTP local solo mediante configuración controlada.

Reutilizar Illuminate HTTP/Cache/View, Carbon y las herramientas existentes. Verificar requisitos Composer directos del paquete (`illuminate/http`, `illuminate/cache`, `illuminate/view`, según clases utilizadas) y versiones compatibles; no trasladar Sanctum, Sail, Sass, Axios, Ladda, Luxon, NProgress o Vue por pertenecer al portal fuente. Revisar PHP real: raíz declara ^8.3, paquete ^8.4; no ampliar el alcance a una actualización general de dependencias.

HTTP individual y pool deben compartir timeout, política de reintentos y validación de JSON. Reintentar GET solo ante desconexión, 429/5xx seleccionados, con espera acotada; no reintentar errores de autorización/IP. Traducir fallos a 502 genérico y registrar contexto mínimo sin token, cuerpo completo ni detalles internos. No cachear errores como listas vacías. Distinguir recurso inexistente (404) de backend indisponible (502).

## 8. Caché e invalidación objetivo

1. Prefijo exclusivo `ugm:news`, incluyendo identidad de sitio/API, versión del formato y consulta canónica; excluir `_`, ordenar claves asociativas sin reordenar listas semánticas.
2. Mantener consultas individuales y pools nombrados; conservar nombres de resultados y evitar que una respuesta fallida se almacene como éxito.
3. Consultar capacidad real del repositorio/store para tags, no comparar solo su nombre de configuración.
4. Para stores sin tags, preferir namespace generacional con token aleatorio acotado frente al registro infinito de claves. Invalidar cambia generación; entradas antiguas vencen por TTL. Probar inicialización concurrente y que una petición iniciada antes de invalidar no repueble la generación nueva.
5. No usar `Cache::flush()` global. La invalidación debe afectar únicamente News y el sitio configurado.
6. El POST de visitas no se cachea. No invalidar toda la caché por cada visita: definir frescura de populares dentro del TTL.

Webhook: comparar strings con `hash_equals`, rechazar secretos ausentes o tipos inválidos, usar header preferentemente. Preservar temporalmente `secret_token` por compatibilidad; desaconsejar query-string por fuga en logs y documentar retirada futura. Limitar frecuencia sin bloquear publicaciones legítimas. No aceptar URLs o claves de caché del payload. Si la invalidación falla, no responder éxito falso.

Registrar el webhook en grupo API sin sesión/CSRF y evitar doble prefijo `/api`. Las páginas requieren grupo web por la sesión de visitas. El provider actual aplica prefijos: adaptar sus grupos y archivos juntos para no producir `/api/api/...`. Mantener nombres públicos y parámetros de ruta existentes aunque los argumentos PHP sean camelCase.

## 9. Blade, Tailwind y fidelidad visual

### Componentes y comportamiento

- Registrar componentes con namespace de paquete, por ejemplo `<x-news::news-block>`, evitando aliases globales genéricos.
- Mantener variantes `hero`, `big`, `box`, `single-hero`, títulos h1–h4, extractos y control de enlaces.
- Navegación horizontal desplazable con estado activo y enlace inicial «Noticias de hoy»; eliminar la función PHP global declarada dentro de Blade.
- Breadcrumbs, autor/fecha, cuerpo del artículo, populares y sidebar deben seguir disponibles; listas cortas/vacías deben renderizar sin errores.
- Compartir en Facebook, X, WhatsApp, Telegram y copiar enlace; usar listeners por componente, sin función global ni ID fijo duplicado. Añadir estado accesible de éxito/error.
- Usar `<picture>` con `src`/`srcset` reales y `loading="lazy"` para imágenes no prioritarias; imagen principal eager, dimensiones y alt adecuados. Eliminar `v-pre` y la dependencia `.js-lazy`/`data-src`.

### Traducción de estilos

| Fuente | Equivalencia / criterio Tailwind |
| --- | --- |
| Grid 4 columnas → 3 → 1 | `grid`, variantes responsive `grid-cols-*`, `gap-4`; calibrar breakpoints reales antes de aceptar capturas. |
| Destacados 2-col-masonry | Layout de dos columnas asimétricas, grupo lateral apilado, principal sticky; una columna en móvil. No sustituir por masonry automático. |
| Hero fila invertida en escritorio | `flex-col` móvil, `flex-row-reverse` en escritorio, mitades equilibradas. |
| Box horizontal en móvil | Utilities responsive para dirección; miniatura de 94px y extracto oculto en móvil. |
| Imagen hero / big / artículo / box | Ratios `21/10`, `2/1`, `3.7/1.8`, `3/2`; conservar cambios móviles 16/9 donde corresponda. |
| Cuerpo HTML | Utilities descendientes para headings, párrafos, listas, enlaces, imágenes, tablas y embeds; sin plugin de tipografía obligatorio. |
| Share sticky, botones 40px | `sticky`, offset, `size-10`, `rounded-full`, estados hover/focus y movimiento reducido. |
| `.c-*` espaciado/color/tipografía | Traducir valores efectivos, no quitar simplemente el prefijo: `mb-2` antiguo puede no equivaler a Tailwind `mb-2`. |

Conservar la paleta y tipografía de `www` mediante tokens exclusivos `news-*`, sin rediseñarlas con los estilos de Admin/WebApp ni modificar los tokens de esos paquetes. La referencia usa Roboto para texto y tarjetas, y Oswald para encabezados de sección. Servir ambas fuentes desde assets locales del paquete, sin hojas de estilos externas. La navegación del header se limita a Noticias según indicación expresa del usuario.

Crear entrada Tailwind 4 con fuentes `@source` explícitas para vistas/componentes del paquete y clases completas detectables; evitar concatenar nombres de utilities. Añadir entradas News a `vite.config.ts` y usarlas únicamente en su layout. No copiar SCSS legado ni introducir un segundo Tailwind independiente.

### Shell, anuncios y HTML remoto

El layout fuente depende de header/footer/sidebar, SEO, marketing y numerosos componentes Vue. El include `a-display` contiene videos y recompensas mediante Vue. Conservar ubicación y tamaño de slots mediante Blade y medios configurables, pero **la equivalencia funcional de recompensas publicitarias queda pendiente de un contrato aprobado con Ads**. No copiar campañas/identificadores ni afirmar paridad completa de ese subsistema.

Antes de cerrar la UI, completar inventario de assets compartidos, valores efectivos de wrappers/breakpoints y header/footer; son un pendiente de fidelidad visual, no un motivo para importar todo el shell antiguo.

`contentHtml` se imprime sin escape en la referencia. Antes de reproducirlo, definir frontera de confianza: sanitización server-side por allowlist con herramienta mantenida y tratamiento explícito de embeds/URLs, o política documentada de contenido editorial confiable y mitigaciones verificadas. La elección es un bloqueo de seguridad para liberar; no inventar un sanitizador por regex. Títulos/extractos/metadatos deben escaparse; no insertar `yoast_head` crudo. Generar title, description, canonical, OG y Twitter desde datos normalizados y URLs Core.

## 10. Fases de ejecución y entregables

### Fase 0 — Revisión de esta especificación

- Aprobar alcance Blade/Tailwind, casing, fallback editorial y configuración.
- Solicitar código/lista de plugins WordPress para destacados, visitas, populares y webhook.
- Resolver política de HTML y alcance de publicidad; no solicitar credenciales productivas.
- Completar referencias de assets/breakpoints y capturas locales con fixtures autorizados.

### Fase 1 — Paquete y contratos

- Comprobar cambios locales y reglas del paquete antes de editar.
- Renombrar scaffold sin duplicarlo; ajustar PSR-4 y referencias exactas.
- Configurar binding de contrato, bandera enabled, rutas y componentes namespaced.
- Documentar variables en `.env.example`, sin tocar `.env` real.
- Verificar autoload y rutas con módulo habilitado/deshabilitado, sin red.

### Fase 2 — WordPress y caché

- Adaptar transporte GET/pool, normalización, categorías y visitas.
- Implementar validación de payloads, errores seguros y fallbacks locales.
- Añadir caché canónica, TTL y mecanismos de invalidación con/sin tags.
- Implementar webhook autenticado y pruebas de aislamiento/concurrencia.

### Fase 3 — Controllers y contrato público

- Migrar portada/categoría/artículo y respuesta simple/full.
- Corregir nombres internos `cateogryWP`/`sigleWP` sin cambiar URLs/nombres de rutas.
- Añadir sesiones, 404 por pertenencia incorrecta y SEO independiente del Helper fuente.
- No introducir paginación pública, búsquedas ni otros endpoints no solicitados.

### Fase 4 — Presentación

- Migrar tres páginas y cuatro componentes a Blade/Tailwind.
- Construir shell Blade acotado, slots y estilos de contenido remoto.
- Añadir recursos Vite y mejora progresiva para compartir/copiar.
- Comparar vistas con fixtures en móvil/tablet/escritorio, teclado y sin JS.

### Fase 5 — Validación y entrega

- Ejecutar pruebas específicas, Pint/estático sobre código cambiado y build frontend desde WSL existente; no reinstalar dependencias innecesariamente.
- Ejecutar suite de regresión disponible y separar fallos preexistentes de regresiones News.
- Comprobar route/config/view cache, autoload y casing en Linux.
- Documentar operación: IP autorizada, TTL, token, errores 400 por restricción, webhook, fallback y desactivación.
- Solo con autorización, hacer smoke test en staging con WordPress y campañas de prueba. No llamar a producción por defecto.

## 11. Matriz mínima de pruebas

Usar `Http::fake()` y `Http::preventStrayRequests()`; fixtures locales sin datos privados. No usar `Cache::flush()` sobre stores compartidos.

| Área | Casos obligatorios |
| --- | --- |
| HTTP | URL y filtros; GET/pool; timeout/desconexión; 400/401/403/429/5xx; JSON inválido; error objeto en lugar de lista; ningún mensaje interno expuesto. |
| Normalización | `_embedded` ausente; medios sin tamaños; autor/términos ausentes; fecha inválida; Yoast opcional; categorías múltiples; contenido seguro. |
| Caché | Hit/miss; expiración; TTL cero/negativo; `_` ignorado; parámetros equivalentes; sitios distintos; pool con fallo no cacheado. |
| Invalidación | Tags y store sin tags; conservar clave ajena; peticiones concurrentes; webhook no autorizado no invalida. |
| Webhook | Header/body compatible; falta secreto; token erróneo/array; comparación segura; fallo de store y respuesta correcta; sin CSRF/duplicación de prefijo. |
| Rutas | Cinco URLs públicas; nombres preservados; enabled=false; 404 real frente a 502; pertenencia de artículo a categoría. |
| Portada | Cero/uno/dos/tres/cuatro destacados; hero disponible/ausente; sin acceso a índices inexistentes. |
| API | 20 posts solicitados; envelope y campos exactos simple/full; URLs Core; lista vacía; query desconocida compatible. |
| Visitas | Una vez por sesión; sesiones distintas; POST fallido no rompe lectura; sin caché ni reintento inseguro. |
| Blade | Componentes repetidos sin colisión; imágenes funcionales sin JS; teclado/foco, alt, enlaces compartidos; HTML peligroso rechazado según política. |
| Integración | Build de entradas News; utilities no purgadas; rutas/config/vistas cacheables; Admin/WebApp sin cambios visuales involuntarios. |

## 12. Riesgos y criterios de aceptación

### Pendientes para cerrar paridad

1. Identificar extensiones WordPress de destacados/populares/visitas y emisor del webhook.
2. Aprobar política segura para HTML editorial y embeds.
3. Completar shell/assets/breakpoints y decidir tipografía respecto a tokens Core.
4. Acordar publicidad: slots visuales Blade no equivalen al sistema Vue de recompensas.
5. Validar conectividad/IP y contratos en staging autorizado. Hasta entonces, el resultado solo puede declararse probado con fixtures.

### Definición de terminado

- [ ] Un solo paquete en `packages/integrations/News`, namespace `Ugm\News`, sin lógica News en `app/`.
- [ ] API WordPress, caché y webhook probados; ninguna invalidación global.
- [ ] URLs, nombres de rutas y contrato JSON conservados.
- [ ] Páginas/componentes Blade, estilos Tailwind y cero dependencia Vue para News.
- [ ] Estados vacíos/errores, imágenes, SEO, accesibilidad y sesiones cubiertos.
- [ ] Paridad visual revisada con las diferencias aprobadas y pendientes explicitados.
- [ ] Configuración documentada, secretos ausentes del código y pruebas sin red real.
- [ ] Autoload/casing/build y regresiones revisados, sin cambios ajenos ni commit/push automático.

## 13. Instrucción para implementar tras aprobación

Usar este documento como especificación y verificar el estado actual del repositorio antes de actuar. Implementar por fases, conservar el commit fuente como referencia y adaptar el scaffold existente. Respetar AGENTS.md y skills PHP/web: PHP 8.4, strict types en clases, tipos explícitos, PHPDoc/JSDoc en inglés, TypeScript estricto sin `any`, contratos inyectados y recursos fuera de `src/`. No introducir supuestos sobre plugins, HTML confiable o Ads como si estuvieran verificados. Reportar las validaciones realizadas y sus límites antes de declarar completada la migración.

## 14. Tarea 2 — Implementación y validación de conexión

### Código incorporado

- `src/Contracts/NewsRepository.php`: puerto de lectura para colecciones, portada, categorías y búsqueda conjunta de artículo/categoría.
- `src/Services/WordPressService.php`: GET individual y pools nombrados, validación de origen/endpoints/JSON, normalización de posts, timeouts y reintentos acotados únicamente para desconexión, 429 y 5xx.
- `src/Services/NewsCacheService.php`: TTL predeterminado 18 000 segundos, claves canónicas que ignoran `_`, separación por sitio/consulta y namespace generacional para invalidación exclusiva de News. Se utiliza el mismo mecanismo en stores con o sin tags; no se necesita una lista de drivers ni un registro infinito de claves. No se usa `Cache::flush()`.
- `src/Exceptions/WordPressApiException.php`: error seguro con estado HTTP opcional, sin cuerpo remoto, secretos ni detalles internos en el mensaje.
- `src/Console/Commands/CheckWordPressConnectionCommand.php`: comando local `php artisan news:check-connection`, sin interfaces ni endpoints nuevos.
- Provider: binding del contrato al servicio y registro del comando cuando el módulo está habilitado.
- `config/config.php` y `.env.example`: URL base `WP_API`, bandera de activación, store/TTL y límites HTTP. No se modificó el `.env` real.
- Composer del paquete: dependencias Illuminate HTTP, Cache y Console declaradas sin instalar ni actualizar librerías.

El comando realiza consultas GET mínimas a categorías, posts, destacados y populares, informa cantidades sin imprimir el contenido editorial y respeta la caché configurada. Una colección vacía válida no es un error. No considera que una respuesta 200 demuestre que WordPress aplica `featured` u `orderby=views`.

El servicio admite exclusivamente los endpoints relativos `posts` y `categories`, nunca URLs arbitrarias de una petición pública. La caché inicialmente compuesta se reemplazó por entradas por endpoint y parámetros canónicos, compartidas entre consultas individuales y pools; solo se solicitan los elementos faltantes y los alias idénticos se resuelven una vez. Una respuesta parcial fallida no guarda ninguna de las nuevas respuestas del pool ni elimina aciertos previos. El contenido HTML se conserva como dato no confiable, sin renderizarlo. El fallback visual local y el tratamiento seguro de ese HTML corresponden a la tarea de presentación.

No se han añadido controllers públicos, vistas, estilos, webhook receptor ni escritura de visitas. La invalidación interna ya está disponible para conectar el receptor del webhook cuando se aborde su adaptación.

### Pruebas automatizadas realizadas

`php vendor/phpunit/phpunit/phpunit packages/integrations/News/tests/Feature`

**Resultado: 42 pruebas correctas, 135 aserciones**, incluidas las 4 pruebas base de la tarea 1.

Cobertura comprobada: normalización y opcionales ausentes; fechas inválidas; TTL de 5 horas; TTL cero/negativo; claves canónicas y separación por sitio/filtros; listas vacías; concurrencia y fallos parciales; 400/401/403/429/503/302; JSON inválido, objetos en lugar de listas y filas malformadas; reintentos individuales y en pool; no reintentar restricciones por IP; desconexión; invalidación sin afectar claves ajenas; invalidación durante una petición en curso; store array y store file sin tags; origen inválido, módulo deshabilitado y comando de diagnóstico.

Pint y `git diff --check` correctos. PHPStan nivel 8 sobre `packages/integrations/News/src` terminó sin errores, usando una configuración temporal fuera del repositorio para desactivar únicamente el aviso de patrones globales ignorados que no aplican al análisis acotado; no se modificó la configuración estática raíz ni se añadieron supresiones al código.

### Validación real desde el entorno local

Se ejecutó el comando contra `https://news.ungolmas.com` con store array y reintentos desactivados mediante variables de proceso, sin escribir `.env` ni caché compartida:

```bash
WP_API=https://news.ungolmas.com UGM_NEWS_ENABLED=true UGM_NEWS_CACHE_STORE=array UGM_NEWS_HTTP_RETRIES=0 php artisan news:check-connection
```

- La primera consulta, a categorías, recibió **HTTP 400** y el comando terminó con error, sin continuar ni declarar conexión correcta.
- Una consulta GET adicional y mínima a posts desde Python recibió **HTTP 403**, sin mensaje WordPress útil. Puede existir una diferencia de filtrado por cliente/WAF; no se concluye una causa exacta solo con esos estados.
- El tema recibido contempla restricciones REST por IP que pueden devolver 400. Revisar la IP de salida autorizada, las reglas del servidor/WAF y la URL de API permitida para este entorno.
- No hubo POST, cambios remotos, registro de visitas ni ejecución de webhooks.

**Bloqueo observado en la primera prueba (resuelto posteriormente):** era necesario confirmar el acceso del entorno local y repetir el diagnóstico hasta obtener colecciones válidas. Las pruebas con fixtures no sustituían ese criterio de aceptación. La resolución se registra a continuación; los filtros personalizados todavía no deben considerarse equivalentes solo porque respondan correctamente.

**Resolución posterior del acceso:** el usuario autorizó su IP en el tema WordPress desplegado y corrigió una coma ausente en la lista de IPs. Confirmó el diagnóstico con las cuatro colecciones en estado OK. En la tarea 3 se verificó además HTTP 200 para `/noticias` y la navegación real a categoría y artículo desde el navegador local. Esto confirma acceso y estructura de respuestas, no la semántica editorial de los plugins personalizados.

## 15. Tarea 3 — Vistas Blade, componentes y Tailwind

### Ajuste posterior: caché compartida

- Las entradas de caché conservan su TTL original de **18000 segundos** al reutilizarlas; las listas vacías también son aciertos. Se mantienen el aislamiento por sitio y la invalidación por generación, incluso durante una carga. La deduplicación de alias dentro de un lote funciona también con caché desactivada. Esto no añade bloqueo distribuido para peticiones HTTP independientes simultáneas.
- A petición del usuario se retiraron el componente de anuncios, su configuración y sus pruebas. Solo permanecen los tres espacios visuales originales del sidebar; la gestión de anuncios queda pospuesta para Ads. La sección opcional `news-sidebar` se conserva.
- Las pruebas de caché incluyen reutilización individual/lote, lotes solapados, alias, listas vacías, vencimiento sin extensión y errores parciales.

### Rutas implementadas

**Adaptación de `HeadlessNews`:** el middleware reside en `src/Http/Middleware/HeadlessNews.php` y se aplica únicamente a portada, categoría y artículo. Obtiene las categorías mediante `NewsRepository` y su caché existente, y las entrega en el atributo de petición `news.categories`; el controlador solo las pasa a las vistas como `wpCategories`. Se eliminó la consulta común del controlador, evitando duplicación. Un fallo de navegación produce una lista vacía sin impedir leer contenido disponible. A diferencia del original, no modifica configuración ni comparte variables globalmente entre vistas; el estado queda limitado a la petición. La redirección legacy no carga categorías ni consulta WordPress. Validación: **69 pruebas correctas, 258 aserciones**.

| URL | Nombre | Resultado |
| --- | --- | --- |
| `/noticias` | `news.index` | Portada con hero, destacados y últimas noticias. |
| `/noticias/{category_slug}` | `news.category` | Listado y navegación de categoría activa. |
| `/noticia/{category_slug}/{slug}` | `news.post` | Artículo, breadcrumbs, autor/fecha, compartir y populares. |
| `/noticias/{category_slug}/{slug}` | `news.post.legacy` | Redirección 301 al detalle singular, sin consultas WordPress adicionales. |

`NewsController` pertenece al paquete; no se añadió lógica a `app/`. Mantiene 404 para categoría/artículo inexistente o pertenencia incorrecta. Un fallo de la consulta principal devuelve una página Blade 502 sin detalles internos; los errores de navegación o populares opcionales no impiden leer contenido disponible.

### Componentes y presentación

- Clases `NewsBlock`, `NewsImage` y `SharePage` adaptadas al namespace `<x-news::...>`; navegación y sidebar como componentes Blade anónimos.
- Templates reutilizables bajo `resources/views/components`, páginas `index`, `categories`, `single` y `unavailable`, layout propio y parciales header/footer.
- Variantes `hero`, `big`, `box` y `single-hero`; headings h1–h4 validados. El hero conserva prioridad de destacados y tiene fallback a hero/últimas noticias; las colecciones incompletas no producen errores por índices ausentes.
- Grid 1/3/4 columnas, destacados asimétricos, sidebar sticky, miniaturas móviles de 94px y ratios adaptados desde SCSS. Breakpoint de 992px expresado mediante variantes Tailwind explícitas.
- `<picture>` con `src`/`srcset` reales, carga nativa lazy/eager y fallback SVG inline local; no requiere Vue ni el lazy loader anterior.
- Enlaces de compartir Facebook, X, WhatsApp y Telegram con SVG reutilizados de la referencia. Copiar enlace usa TypeScript delegado, feedback por instancia y alternativa manual cuando Clipboard API no está disponible; no hay función global ni IDs compartidos.
- SEO title/description/canonical/OG/Twitter con escape Blade; canonical y enlaces apuntan a Core.
- CSS limitado a entrada Tailwind, tokens `news-*` y declaraciones locales `@font-face`. No se importa SCSS heredado ni una hoja de estilos externa. Se conserva la apariencia de `www`: negro/degradado, grises originales, Roboto y Oswald. Ver la corrección visual de la sección 16.
- Entradas CSS/TS propias registradas en Vite; News no monta Vue y no importa los assets de Admin/WebApp.

### HTML remoto y dependencia de seguridad

Se incorpora `symfony/html-sanitizer:^7.4` (y su dependencia de parsing HTML5) en lugar de un sanitizador artesanal por regex o de imprimir directamente HTML remoto. `NewsPresentationService` conserva estructura editorial segura, imágenes HTTP(S), listas y enlaces; elimina scripts, estilos, eventos inline, URLs peligrosas y embeds no aprobados. El contenido sigue siendo dato remoto; solo el resultado sanitizado llega al bloque Blade sin escape.

La instalación se limitó a esta dependencia de seguridad: las versiones existentes de `league/uri`, `league/uri-interfaces` y `symfony/deprecation-contracts` se conservaron tras la resolución. No se reinstalaron dependencias frontend. Composer reportó 7 avisos en 4 paquetes existentes (`laravel/framework`, `symfony/http-foundation`, `symfony/http-kernel`, `symfony/routing`); el sanitizador añadido no aparece afectado. No se hicieron actualizaciones generales fuera del alcance News.

### Verificaciones realizadas

- Suite News: **61 pruebas correctas, 214 aserciones**, incluyendo tareas anteriores, rutas, estados vacíos, destacados 0–4, 404/502, pertenencia a categoría, HTML seguro, SEO, imágenes y componentes repetidos.
- PHPStan nivel 8 sobre `packages/integrations/News/src`: sin errores. Pint, ESLint sobre el TS News y formato Blade verificados.
- Build completo `pnpm run build` desde WSL: TypeScript y Vite correctos.
- `php artisan route:list --path=noticia`: las cuatro rutas registradas.
- Petición real local a `http://localhost:8080/noticias`: HTTP 200.
- Chrome headless con viewport real de 1440px y emulación móvil de 390px: portada, categoría y artículo navegables, imágenes cargadas y un h1 por página. En móvil, `scrollWidth` e `innerWidth` fueron 390px, sin desbordamiento horizontal del documento; el menú de categorías conserva scroll propio.
- Capturas de revisión y herramientas temporales fuera del repositorio. No se añadieron `.gitkeep`, cambios staged, commits ni push.

### Límites de esta entrega

La estructura editorial está migrada y funciona con datos reales. La primera adaptación de colores, fuentes y layout a Core fue rechazada por el usuario y se corrigió usando los valores de `www`. El header queda limitado a Noticias y no se conectan otras secciones del portal ni enlaces de footer ajenos a News. Queda la aprobación visual del usuario; las verificaciones técnicas no equivalen por sí solas a una comparación pixel a pixel de todo el portal.

Se conservan tres espacios publicitarios como slots Blade, sin copiar campañas, recompensas, videos ni el runtime Vue de Ads. No se incluyen el POST de visitas, el receptor del webhook ni `/api/news` en esta tarea de interfaces; esas piezas del alcance global siguen pendientes de adaptación específica. Los embeds ejecutables se excluyen hasta definir una allowlist aprobada. No se modificaron Admin, Storybook ni el repositorio fuente.

## 16. Corrección visual solicitada: conservar `www`, sin vínculos adicionales

Esta corrección sustituye la reinterpretación visual inicial. Migrar a Tailwind no autoriza cambiar el diseño original ni conectar otros paquetes.

- Header fijo con el **logo SVG original de UNGOLMAS** y **un único enlace de navegación Noticias**, según aclaración posterior del usuario. El logo se sirve como asset local, sin enlace a otro módulo. Se retiran el logo recreado, el enlace `/webapp` y cualquier navegación a otras secciones. El footer no agrega enlaces externos ni navegación adicional.
- Roboto para cuerpo y tarjetas; Oswald para títulos de sección, servidas desde `resources/assets/fonts` con sus licencias OFL. Vite empaqueta los archivos; no hay peticiones a Google Fonts en ejecución.
- Fondo negro/degradado de referencia; colores originales `#262626`, `#737373`, `#a3a3a3`, `#f0ca2c` y `#fdf1b3`. Tokens exclusivos de News, sin sustituir la paleta Core global.
- Wrapper máximo 1300px, ancho 96% en el tramo intermedio y ancho completo en móvil. Breakpoints de News definidos en px: 576, 768, 992 y 1300, evitando mezclar unidades de variantes responsive.
- Roboto 500 en títulos de tarjetas: hero 36/42px en escritorio y 30/36px en móvil; artículo 48/54px y 30/36px respectivamente; big 30/36px; box 16/22px. Fechas 12/18px, espacios de tarjeta 10px y de información 5px.
- Excerpt de tarjetas limitado a 200 caracteres, como la referencia; el del artículo no se trunca artificialmente. Se retira la fila visible de autor/fecha añadida al hero del artículo, que no estaba en la vista fuente; la metadata de autor se conserva.
- Separaciones de 20/30px y líneas decorativas en los encabezados de sección. Se retira el título visible «Últimas noticias» añadido durante la primera adaptación; el listado conserva su etiqueta accesible.
- Imágenes sin bordes redondeados, box con ratio 3/2 también en móvil y cambio de fuente de imagen a 499px como el componente original.
- Grid 1/3/4 con gap de 16px; destacados asimétricos y sticky a 16px. Sidebar a 60px y compartir a 100px; el detalle no muestra la barra de categorías y compartir lateral se oculta en móvil, como en la referencia.
- No se añaden campañas, anuncios enlazados, perfiles sociales, autenticación ni funcionalidades de otros módulos.

Validación tras la corrección: **62 pruebas correctas, 225 aserciones**, incluyendo logo original y un único enlace Noticias en el header; formato PHP/Blade y build WSL correctos. Chrome comprobó valores calculados en 1440/1024/800/390px: Roboto y Oswald cargadas, navegación del header limitada a Noticias, cero peticiones a Google Fonts, hero de 1300px en escritorio, gap 10px, radio 0px, títulos 36/42 o 30/36 y grid 4/4/3/1 respectivamente. No hubo desbordamiento horizontal del documento. En detalle móvil se comprobó un h1 y ausencia de la barra de categorías. Capturas fuera del repositorio; sin staging, commit ni push.
