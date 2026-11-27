---
name: combativa-develop-php
description: >-
  Skill de desarrollo backend para UNGOLMAS Core y proyectos de Combativa.
  Activar al crear, modificar, analizar o refactorizar código PHP, controladores,
  modelos, servicios, migraciones y paquetes modulares. Aplica Nomenclatura Laravel
  estricta, directivas de PHP 8.4 y la arquitectura de paquetes desacoplados.
---

# Skill: Desarrollo PHP y Nomenclatura Laravel (Combativa / UNGOLMAS Core)

Esta skill establece las normas de desarrollo backend en PHP para el proyecto **UNGOLMAS Core** y el ecosistema de Combativa.

---

## 1. Nomenclatura Laravel Estricta

Todo elemento de código PHP debe adherirse obligatoriamente a esta tabla:

| Elemento | Convención | Ejemplo |
| :--- | :--- | :--- |
| **Clases / Enums / Interfaces** | `PascalCase` | `UserRegisteredEvent`, `RewardType`, `WalletService` |
| **Modelos Eloquent** | `PascalCase` singular | `Prediction`, `Wallet`, `GameRule` |
| **Controladores** | `PascalCase` + `Controller` | `AuthController`, `GameController` |
| **Servicios** | `PascalCase` + `Service` | `TransactionService`, `ConfigService` |
| **Form Requests** | `PascalCase` + `Request` | `StorePredictionRequest` |
| **Métodos y Funciones** | `camelCase` | `calculateExperience()`, `findUserById()` |
| **Variables y Propiedades** | `camelCase` | `$userId`, `$totalScore`, `$walletBalance` |
| **Tablas de Base de Datos** | `snake_case` plural | `ugm_users`, `gamification_badges`, `t_config` |
| **Columnas de Base de Datos** | `snake_case` | `user_id`, `created_at`, `is_active` |
| **Rutas Web y API (URIs)** | `kebab-case` | `/api/user-profile`, `/admin/leader-boards` |
| **Claves de Configuración** | `snake_case` | `config('gamification.max_daily_points')` |

---

## 2. Directivas PHP 8.4 Moderno

- **Tipado Explícito Obligatorio**:
  - Declarar tipo en todos los parámetros y retornos de funciones y métodos (`public function find(int $id): ?User`).
- **Promoción de Propiedades en Constructor**:
  - Utilizar constructor promotion para inyección de dependencias y asignación de propiedades:
    ```php
    public function __construct(
        protected readonly WalletRepositoryInterface $walletRepository,
        protected readonly GamificationService $gamificationService,
    ) {}
    ```
- **Clases e Inmutabilidad**:
  - Usar clases `readonly` o propiedades `readonly` para DTOs, Eventos y VOs.
  - Usar `final` en clases que no están diseñadas para ser heredadas (ej. `final class WebAppServiceProvider`).
- **Estructuras Modernas**:
  - Preferir expresiones `match` sobre sentencias `switch` largas.
  - Usar Enums respaldados (`BackedEnum`) con tipos `string` o `int`.

---

## 3. Política de `declare(strict_types=1);`

- **En clases de código fuente (`src/**/*.php`)**:
  - Incluir `declare(strict_types=1);` en la línea 3 inmediatamente después de `<?php`.
- **En archivos de datos o rutas (`config/`, `routes/`, `resources/lang/`)**:
  - **No incluir** `declare(strict_types=1);` para mantener los archivos limpios y evitar ruido visual en archivos que solo retornan arrays o registran rutas.

---

## 4. Arquitectura Modular y Paquetes (`packages/`)

- **Regla de Oro**: `app/` es únicamente un cascarón (shell). Toda la lógica de negocio debe residir en `packages/core/` o `packages/integrations/`.
- **Estructura Estándar de Paquete**:
  - `src/`: Contiene **únicamente** clases PHP bajo el namespace PSR-4 (`Contracts/`, `Http/`, `Models/`, `Providers/`).
  - `config/`, `database/` (migrations/seeders), `resources/` (views/lang/assets) y `routes/`: Residen en la **raíz del paquete**, fuera de `src/`.
- **Desacoplamiento Dirigido por Eventos**:
  - Paquetes satélites y dinámicas (`Game`, `Store`) disparan eventos (ej. `GameFinished`, `StoreItemPurchased`).
  - `Gamification` escucha mediante listeners dedicados; nunca acoplar llamadas sincrónicas directas para alteración de balances.
