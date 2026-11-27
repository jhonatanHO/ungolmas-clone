# Guía de Sistema de Diseño - UNGOLMAS Core (DESIGN.md)

Este documento define las pautas visuales, tokens de diseño y directrices de arquitectura frontend inspiradas en la robustez de Bagisto y la flexibilidad de Tailwind CSS.

---

## 🎨 Design Tokens

### 1. Paleta de Colores

- **Primary (Brand)**:
  - `primary-50`: `#EEF2FF`
  - `primary-500`: `#6366F1`
  - `primary-600`: `#4F46E5` (Color de acción principal)
  - `primary-700`: `#4338CA`
- **Secondary (Accent)**:
  - `secondary-400`: `#22D3EE`
  - `secondary-500`: `#06B6D4`
  - `secondary-600`: `#0891B2`
- **Gamification & Rewards**:
  - `gold`: `#F59E0B` (Monedas, trofeos, rango oro)
  - `silver`: `#94A3B8`
  - `bronze`: `#B45309`
  - `experience`: `#8B5CF6` (Puntos XP)
- **Neutrals & Dark Surface**:
  - `surface-base`: `#0F172A` (Fondo oscuro principal)
  - `surface-card`: `#1E293B` (Tarjetas y modales)
  - `surface-border`: `#334155`
  - `text-primary`: `#F8FAFC`
  - `text-muted`: `#94A3B8`

### 2. Tipografía

- **Fuente Principal (Sans)**: `Inter`, system-ui, sans-serif.
- **Fuente de Títulos y Gaming**: `Outfit`, sans-serif.
- **Monoespaciada**: `JetBrains Mono`, monospace.

### 3. Espaciado y Radios

- Radios estándar:
  - Botones y campos: `rounded-lg` (8px)
  - Tarjetas y contenedores: `rounded-xl` (12px)
  - Modales e insignias: `rounded-2xl` (16px)

---

## 🧩 Componentes UI Estándar

1. **Botones (`UgmButton`)**:
   - Variantes: `primary`, `secondary`, `outline`, `ghost`, `danger`.
   - Estados: `hover`, `active`, `focus-visible`, `disabled`, `loading` (spinner accesible).
2. **Tarjetas de Gamificación (`UgmCard`, `UgmQuestCard`)**:
   - Elevación suave con `shadow-sm` y borde `border border-slate-700/60`.
   - Fondo reactivo para elementos desbloqueables o completados.
3. **Tablas de Datos / Admin (`UgmTable`)**:
   - Cabeceras tipadas, paginación integrada, filtros dinámicos y estados vacíos (_Empty States_).
4. **Modales y Diálogos (`UgmModal`)**:
   - Teleportados al final del `<body>`, control de foco accesible, cierre con ESC y animación de backdrop.

---

## 📐 Directrices Bagisto / Tailwind

- **Utility-First Modular**: Las clases de Tailwind deben organizarse de forma lógica: Layout -> Posición -> Dimensiones -> Tipografía -> Visuales -> Interactividad.
- **Componentes Híbridos**: Blade se utiliza para la estructura base y SSR rápida de layouts, mientras que Vue 3 Composition API gestiona interfaces ricas e interactivas (tienda, inventario, progreso de juego).
- **Consistencia de Tokens**: No utilizar colores arbitrarios (`#hex` directos) en templates; siempre referenciar tokens del tema (`text-primary`, `bg-surface-card`, etc.).
