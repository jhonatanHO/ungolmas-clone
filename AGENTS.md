# Directivas para Agentes de IA - UNGOLMAS Core (AGENTS.md)

Este documento contiene las reglas inviolables de arquitectura, desarrollo y convenciones para cualquier agente de inteligencia artificial que opere en este repositorio.

---

## ⚡ Regla de Oro Arquitectónica

> **`app/` opera ÚNICAMENTE como cascarón (shell) de bootstrap, middleware global y enrutamiento básico. Toda la lógica de negocio debe residir dentro de `packages/core/` o `packages/integrations/`.**

1. **Nunca crees controladores o modelos de negocio en `app/`**. Todo modelo, migración, acción, servicio y controlador pertenece a su módulo correspondiente en `packages/*/*`.
2. **Estructura Estándar de Paquetes (Spatie / Bagisto / Laravel)**:
   - `src/` contiene **exclusivamente** clases PHP bajo el namespace PSR-4 (`Contracts/`, `Http/`, `Models/`, `Providers/`, etc.).
   - `config/`, `database/` (migrations/seeders), `resources/` (views/lang/assets) y `routes/` residen en la **raíz de cada paquete**, fuera de `src/`, para evitar que los LSPs interpreten archivos de datos/rutas como clases PSR-4.
3. **Puertos y Adaptadores (Hexagonal)**:
   - Define interfaces en `Contracts/`.
   - Las implementaciones concretas residen en sus respectivos paquetes.
   - El contenedor de servicios de Laravel inyecta las dependencias a través de los ServiceProviders.

---

## 🔄 Desacoplamiento Dirigido por Eventos

- **Prohibido el acoplamiento circular o dependencias duras innecesarias entre paquetes**:
  - `Game` **NUNCA** debe instanciar directamente servicios de `Gamification`. En su lugar, `Game` dispara un evento (ej. `GameCompletedEvent`).
  - `Gamification` registra un Listener para `GameCompletedEvent` y calcula puntos, logros o insignias.
  - `Store` dispara eventos (ej. `ItemPurchasedEvent`) que `User` o `Gamification` escuchan.

---

## 🏷 Nomenclatura Laravel Estricta

| Elemento                    | Convención        | Ejemplo                                     |
| --------------------------- | ----------------- | ------------------------------------------- |
| Clases / Enums / Interfaces | PascalCase        | `UserRegisteredEvent`, `RewardType`         |
| Métodos y Funciones         | camelCase         | `calculateExperience()`, `findUserById()`   |
| Variables y Propiedades     | camelCase         | `$userId`, `$totalScore`                    |
| Tablas de Base de Datos     | snake_case plural | `ugm_users`, `gamification_badges`          |
| Columnas de Base de Datos   | snake_case        | `user_id`, `created_at`, `is_active`        |
| Rutas Web y API (URIs)      | kebab-case        | `/api/user-profile`, `/admin/leader-boards` |
| Claves de Configuración     | snake_case        | `config('gamification.max_daily_points')`   |
| Directorios Raíz de Paquete | lowercase         | `config/`, `database/`, `resources/`, `routes/`, `src/` |
| Directorios de Recursos     | lowercase         | `resources/views/`, `resources/lang/`, `resources/assets/` |
| Directorios PSR-4 (`src/`)  | PascalCase        | `src/Contracts/`, `src/Http/Controllers/`, `src/Providers/` |

---

## 🛡 Tipado Estricto Obligatorio

- **PHP 8.4**:
  - Encabezado `declare(strict_types=1);` obligatorio en todas las clases, interfaces, traits y enums (`src/**/*.php`).
  - **No incluir** `declare(strict_types=1);` en archivos de datos o configuración (`config/`, `routes/`, `resources/lang/`) para evitar ruido visual en archivos sin clases.
  - Tipado de parámetros y retorno explícito en todas las funciones y métodos.
  - Uso de características modernas: clases `readonly`, `match`, Enums respaldados, Promoción de propiedades en constructor.
- **TypeScript 5+**:
  - Modo estricto activado (`"strict": true`).
  - Prohibido el uso de `any`; utilizar tipos discriminados, genéricos o `unknown` con validación.
  - Tipado estricto en Props y Emits de Vue 3 (`defineProps<{ ... }>()`, `defineEmits<{ ... }>()`).

---

## 📝 Documentación y Marcadores

- Bloques PHPDoc en clases y funciones públicas explicando propósito, parámetros y excepciones lanzadas.
- Comentarios estandarizados para notas de desarrollo:
  - `// TODO: [Descripción]`
  - `// BUG: [Descripción]`
  - `// NOTE: [Descripción]`
