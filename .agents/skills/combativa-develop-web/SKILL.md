---
name: combativa-develop-web
description: >-
  Skill de desarrollo frontend y buenas prácticas generales para UNGOLMAS Core y
  proyectos de Combativa. Activar al desarrollar componentes Vue 3, TypeScript,
  JavaScript, estilos Tailwind CSS, vistas Blade, documentación con JSDoc (en inglés) /
  PHPDoc, encabezados de sección limpios y separación estricta entre eventos de UI
  (handle*) y servicios de API (callBack*).
---

# Skill: Desarrollo Web, Frontend y Calidad de Código (Combativa / UNGOLMAS Core)

Esta skill define las directrices obligatorias de arquitectura frontend, calidad, tipado en TypeScript, desarrollo de interfaces en Vue 3 (`<script setup lang="ts">`), separación de capas (`handle*` vs `callBack*`) y estandarización de comentarios para **Combativa** y **UNGOLMAS Core**.

Cada vez que se genere, refactorice o revise código en este repositorio, estas convenciones deben aplicarse estrictamente.

---

## 1. Estándares de Documentación (JSDoc & PHPDoc)

### 1.1. JSDoc en Frontend (Vue 3, TypeScript, JavaScript)

Todas las funciones en frontend (`callBack*`, `handle*`, helpers y propiedades computadas complejas) deben documentarse en formato JSDoc cumpliendo estas reglas:

1. **Idioma**: **100% en inglés** (nunca usar español ni mezclar idiomas en comentarios JSDoc de código frontend).
2. **Longitud**: Conciso y directo (1 oración descriptiva). Sin prosa exagerada ni redundante.
3. **Etiquetas obligatorias**:
   - `@param {type} name - description` para cada parámetro recibido.
   - `@returns {void}` o `@returns {Promise<void>}` (o el tipo de retorno correspondiente) en toda función.

### 1.2. PHPDoc y Block Comments en Backend / Rutas / Clases Compartidas

Toda clase, interfaz, trait, archivo de rutas y método público o protegido en PHP debe incluir comentarios y bloques PHPDoc **100% en inglés** y con una descripción corta (1 oración):

```php
/**
 * Settles a sports prediction and dispatches the reward event.
 *
 * @param  int  $predictionId  Unique identifier of the prediction.
 * @param  int  $actualHomeScore  Final score of the home team.
 * @param  int  $actualAwayScore  Final score of the away team.
 * @return \Ugm\Game\Models\Prediction Updated prediction instance.
 *
 * @throws \Ugm\Game\Exceptions\PredictionAlreadySettledException When the prediction was already settled.
 */
public function settlePrediction(int $predictionId, int $actualHomeScore, int $actualAwayScore): Prediction
{
    // ...
}
```

---

## 2. Estandarización de Comentarios, Marcadores y Encabezados de Sección

### 2.1. Marcadores de Desarrollo Rastreables

Queda prohibido el uso de comentarios informales no rastreables. Se deben emplear exclusivamente los siguientes marcadores estandarizados:

- `// TODO: [Descripción clara de la tarea o mejora pendiente]`
- `// BUG: [Descripción del problema identificado y condición de fallo]`
- `// NOTE: [Explicación de contexto arquitectónico o decisión técnica no evidente]`

### 2.2. Encabezados de Sección vs Divisores ASCII

#### ✅ DO: Encabezados de Sección Limpios
Utilizar comentarios de bloque cortos y limpios para dividir las secciones dentro de `<script setup lang="ts">` o módulos TypeScript:

```typescript
/* PROPS & EMITS */
/* STATE */
/* COMPUTED */
/* API SERVICES */
/* UI HANDLERS */
/* LIFECYCLE */
```

#### ❌ DON'T: Divisores con Bordes ASCII
Nunca utilizar decoraciones ASCII largas, guiones ni signos de igual repetidos:

```typescript
// ❌ PROHIBIDO:
// =========================================================================
// API SERVICES
// =========================================================================
```

---

## 3. TypeScript 5+ Estricto

- **Modo Estricto Activo**: Prohibido el uso de `any`.
- **Arrow Functions Obligatorias**:
  - Utilizar exclusivamente **arrow functions tipadas** (`const myFunction = (param: string): void => { ... };`) en todo código TypeScript, JavaScript y Vue 3.
  - Queda prohibido el uso de declaraciones clásicas `function myFunction() {}`.
- **Tipado Seguro**:
  - Utilizar tipos discriminados (`type Result = Success | Failure`), genéricos estrictos y tipos de utilidad (`Readonly`, `Pick`, `Omit`).
  - Para datos de origen externo (APIs, Webhooks, LocalStorage), utilizar `unknown` acompañado de validación de esquema o type guards (`is...`).

---

## 4. Arquitectura de Componentes Vue 3 (`<script setup lang="ts">`)

- **Sintaxis Estándar**: Utilizar exclusivamente `<script setup lang="ts">`.
- **Modularidad en Paquetes**:
  - Ubicar componentes de UI reutilizables de cada paquete en `resources/assets/js/components/`.
  - Exportar componentes o registrarlos de manera asíncrona con `defineAsyncComponent` en el `app.ts` del paquete.
- **Gestión de Estado Global (Pinia `stores/`)**:
  - Prohibido definir objetos de estado gigantes dentro de `createApp({})` en `app.ts`. `app.ts` actúa exclusivamente como punto de entrada (bootstrap) donde se registra `app.use(createPinia())`.
  - Los estados globales deben residir en la carpeta `stores/` del paquete correspondiente (`resources/assets/js/stores/use*Store.ts`) o en `resources/js/stores/` si son transversales, utilizando la sintaxis Composition API de Pinia (`defineStore('name', () => { ... })`).
- **Orden Predecible de Secciones (Top-to-Bottom)**:
  Todo componente `<script setup lang="ts">` debe respetar estrictamente el siguiente orden de secciones de arriba hacia abajo:

```vue
<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import nprogress from 'nprogress';

/* PROPS & EMITS */
interface Props {
    friendUuid: string;
    variant?: 'primary' | 'secondary' | 'outline' | 'danger';
    disabled?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    variant: 'primary',
    disabled: false,
});

const emit = defineEmits<{
    (e: 'item-removed', uuid: string): void;
}>();

/* STATE */
const isSubmitting = ref<boolean>(false);
const isDropdownOpen = ref<boolean>(false);

/* COMPUTED */
const isDisabled = computed<boolean>(() => props.disabled || isSubmitting.value);

/* API SERVICES */
// Pure async API calls prefixed with callBack*

/* UI HANDLERS */
// User interaction handlers prefixed with handle*

/* LIFECYCLE */
onMounted(() => {
    // Initial fetch or listeners
});
</script>
```

---

## 5. Separación Estricta: UI Handlers (`handle*`) vs API Services (`callBack*`)

### Capa 1: API Services (`callBack*`)
Las funciones con prefijo `callBack*` representan **operaciones puras de red** (peticiones HTTP mediante Axios, Fetch o Inertia):
- **Parámetros**: Reciben únicamente identificadores o payloads explícitos (`uuid: string`, `payload: UserData`) y un callback opcional de éxito: `callBackSuccess?: () => void` o `callBackSuccess?: (data: T) => void`.
- **Manejo de Excepciones**: Siempre envueltas en un bloque `try { ... } catch (error) { ... } finally { ... }`.
- **Ejecución**:
  - En `try`: Ejecutar la llamada HTTP, verificar la respuesta e invocar `if (callBackSuccess) callBackSuccess(response.data)`.
  - En `catch`: Manejar el error y mostrar alertas o notificaciones al usuario.
  - En `finally`: **Siempre reiniciar los indicadores de carga** (`isSubmitting.value = false; nprogress.done()`).

```typescript
/**
 * Sends a request to remove a friend by UUID.
 *
 * @param {string} friendUuid - The UUID of the friend to remove.
 * @param {() => void} [callBackSuccess] - Optional callback executed upon successful removal.
 * @returns {Promise<void>}
 */
const callBackFriendRemove = async (friendUuid: string, callBackSuccess?: () => void): Promise<void> => {
    try {
        await axios.delete(`/api/friends/${friendUuid}`);
        if (callBackSuccess) callBackSuccess();
    } catch (error) {
        console.error('Failed to remove friend:', error);
    } finally {
        isSubmitting.value = false;
        nprogress.done();
    }
};
```

### Capa 2: UI Handlers (`handle*`)
Las funciones con prefijo `handle*` gestionan **eventos de interfaz de usuario** (clicks, envío de formularios, pulsaciones de teclas, apertura/cierre de modales):
- **Responsabilidades**:
  - Validar precondiciones a nivel de UI.
  - Activar indicadores de carga (`isSubmitting.value = true; nprogress.start()`).
  - Cerrar dropdowns, diálogos o actualizar estado local de UI.
  - Definir qué ocurre tras el éxito de la operación dentro de un closure local `callBackSuccess` (ej. emitir eventos al padre o mutar listas locales).
  - Invocar el servicio de API: `callBackFriendRemove(props.friendUuid, callBackSuccess)`.

```typescript
/**
 * Handles the friend removal button click and dispatches the API callback.
 *
 * @returns {void}
 */
const handleRemoveFriend = (): void => {
    isDropdownOpen.value = false;
    isSubmitting.value = true;
    nprogress.start();

    const callBackSuccess = (): void => {
        emit('item-removed', props.friendUuid);
    };

    callBackFriendRemove(props.friendUuid, callBackSuccess);
};
```

---

## 6. Tokens de Diseño y Tailwind CSS (`DESIGN.md`)

Respetar siempre la paleta de tokens oficial de UNGOLMAS:
- **Fondo Oscuro Principal**: `bg-surface-base` (`#0F172A`)
- **Superficie de Tarjetas**: `bg-surface-card` (`#1E293B`) con borde `border-surface-border` (`#334155`)
- **Acción Principal**: `bg-primary-600` (`#4F46E5`), hover `bg-primary-700` (`#4338CA`)
- **Gamificación**: Monedas/oro (`text-gold` / `#F59E0B`), experiencia XP (`text-experience` / `#8B5CF6`)
- **Bordes Redondeados**:
  - Botones y campos de formulario: `rounded-lg`
  - Tarjetas y contenedores: `rounded-xl`
  - Modales e insignias/chips: `rounded-2xl` o `rounded-full`

---

## 7. Implementación de Referencia y Checklist de Pre-generación

Para consultar un componente completo listo para producción que ilustra este estándar, revisar:
- [vue-sfc-pattern.md](./references/vue-sfc-pattern.md)

### Checklist de Pre-generación para IA y Desarrolladores

Antes de generar o entregar cualquier solución en Vue, TypeScript o JavaScript, verificar:
- [ ] ¿Están todas las funciones declaradas como **arrow functions tipadas** (`const fn = (...): Type => { ... }`) y sin `function` tradicional?
- [ ] ¿Están todos los comentarios JSDoc en **inglés**?
- [ ] ¿Están todas las funciones documentadas con un resumen breve de 1 oración, `@param` y `@returns`?
- [ ] ¿Las acciones de UI llevan el prefijo `handle*` y las operaciones de red el prefijo `callBack*`?
- [ ] ¿Se inician los indicadores de carga en `handle*` y se finalizan dentro del bloque `finally` de `callBack*`?
- [ ] ¿Los encabezados de sección tienen el formato `/* SECTION */` sin bordes de líneas ASCII?
