---
name: combativa-develop-web
description: >-
  Skill de desarrollo frontend y buenas prácticas generales para UNGOLMAS Core y
  proyectos de Combativa. Activar al desarrollar componentes Vue 3, TypeScript,
  estilos Tailwind CSS, vistas Blade, documentación con Docblocks y estandarización
  de comentarios técnicos.
---

# Skill: Desarrollo Web, Frontend y Calidad de Código (Combativa / UNGOLMAS Core)

Esta skill define las directrices obligatorias de calidad, tipado en TypeScript, desarrollo de interfaces en Vue 3 y estandarización de comentarios para **Combativa** y **UNGOLMAS Core**.

---

## 1. Comentarios de Cabecera Obligatorios (Docblocks)

Toda clase, interfaz, trait y método público o protegido en PHP y TypeScript debe incluir un bloque PHPDoc / JSDoc descriptivo:

```php
/**
 * Procesa la liquidación de una predicción deportiva y dispara el evento de recompensa.
 *
 * @param  int  $predictionId  Identificador único del pronóstico.
 * @param  int  $actualHomeScore  Goles reales del equipo local.
 * @param  int  $actualAwayScore  Goles reales del equipo visitante.
 * @return \Ugm\Game\Models\Prediction Resultado del pronóstico actualizado.
 *
 * @throws \Ugm\Game\Exceptions\PredictionAlreadySettledException Si el pronóstico ya fue procesado.
 */
public function settlePrediction(int $predictionId, int $actualHomeScore, int $actualAwayScore): Prediction
{
    // ...
}
```

---

## 2. Estandarización de Comentarios y Marcadores

Queda prohibido el uso de comentarios informales no rastreables. Se deben emplear exclusivamente los siguientes marcadores estandarizados:

- `// TODO: [Descripción clara de la tarea o mejora pendiente]`
- `// BUG: [Descripción del problema identificado y condición de fallo]`
- `// NOTE: [Explicación de contexto arquitectónico o decisión técnica no evidente]`

---

## 3. TypeScript 5+ Estricto

- **Modo Estricto Activo**: Prohibido el uso de `any`.
- **Tipado Seguro**:
  - Utilizar tipos discriminados (`type Result = Success | Failure`), genéricos estrictos y tipos de utilidad (`Readonly`, `Pick`, `Omit`).
  - Para datos de origen externo (APIs, Webhooks, LocalStorage), utilizar `unknown` acompañado de validación de esquema o type guards (`is...`).

---

## 4. Componentes Vue 3 (Composition API)

- **Sintaxis Estándar**: Utilizar exclusivamente `<script setup lang="ts">`.
- **Props y Emits Tipados**:
  ```vue
  <script setup lang="ts">
  interface Props {
    title: string;
    variant?: 'primary' | 'secondary' | 'outline' | 'danger';
    disabled?: boolean;
    count?: number;
  }

  const props = withDefaults(defineProps<Props>(), {
    variant: 'primary',
    disabled: false,
    count: 0,
  });

  const emit = defineEmits<{
    (e: 'click', event: MouseEvent): void;
    (e: 'update:modelValue', value: string): void;
  }>();
  </script>
  ```
- **Modularidad de Componentes**:
  - Ubicar componentes de UI reutilizables del paquete en `resources/assets/js/components/`.
  - Exportar componentes o registrarlos según la convención del paquete.

---

## 5. Tokens de Diseño y Tailwind CSS (`DESIGN.md`)

Respetar siempre la paleta de tokens oficial de UNGOLMAS:
- **Fondo Oscuro Principal**: `bg-surface-base` (`#0F172A`)
- **Superficie de Tarjetas**: `bg-surface-card` (`#1E293B`) con borde `border-surface-border` (`#334155`)
- **Acción Principal**: `bg-primary-600` (`#4F46E5`), hover `bg-primary-700` (`#4338CA`)
- **Gamificación**: Monedas/oro (`text-gold` / `#F59E0B`), experiencia XP (`text-experience` / `#8B5CF6`)
- **Bordes Redondeados**:
  - Botones y campos de formulario: `rounded-lg`
  - Tarjetas y contenedores: `rounded-xl`
  - Modales e insignias/chips: `rounded-2xl` o `rounded-full`
