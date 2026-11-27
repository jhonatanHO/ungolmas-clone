# SSD: tema claro y oscuro de Storybook

Estado: alcance aprobado e implementación realizada. Validación visual pendiente de reiniciar
la instancia de Storybook que ya estaba abierta.

## 1. Objetivo y límites

Un único botón sol/luna cambia la apariencia de toda la experiencia de Storybook:

- Lateral, buscador, barra superior y paneles Controls/Actions.
- Welcome, Getting Started, Catalog y Docs de componentes.
- Canvas, ejemplos incrustados, texto, campos, bordes y estados interactivos.

La selección se conserva al navegar y recargar. No habrá un selector de fondos separado.
No se modifica el comportamiento de temas de Laravel, Admin o WebApp, sus plantillas,
sus estilos de producción ni sus preferencias guardadas.

## 2. Diagnóstico y viabilidad

El proyecto utiliza Storybook 8.6.14 con Vue 3 y Vite.

| Evidencia actual                                                                              | Consecuencia                                                                         |
| --------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------ |
| `.storybook/manager.ts:7-14` fija `themes.dark`                                               | La interfaz no responde hoy a una elección global.                                   |
| `.storybook/preview.ts:1-22` carga CSS de ambos paquetes                                      | Es el punto de integración del iframe y de Docs. Hay que conservar su orden lateral. |
| Las historias y MDX de AdminButton/AdminInput incluyen `cmsnet-dark`                          | Cambiar solo el tema del documento no cambia estos ejemplos.                         |
| Admin ofrece `cmsnet-light` y `cmsnet-dark`; WebApp ofrece `ungolmas-light` y `ungolmas-dark` | Se pueden reutilizar las variables existentes sin duplicar paletas.                  |
| `admin-button.css:27-46` fija blanco/negro en Primary y Secondary                             | El modo claro necesita una decisión explícita de contraste, no inversión automática. |
| `button.css:14-39` e `input.css:12-19` de WebApp contienen reglas base vacías                 | El selector no implementará variantes de WebApp que todavía no tienen estilos.       |

Conclusión: viable sin actualizar Storybook ni cambiar el framework de las historias.
Las comprobaciones del índice son orientativas; el CSS parcialmente indexado se revisó
directamente. La integración debe comprobarse en ejecución, no solo con TypeScript.

## 3. Alternativas y decisión propuesta

1. **`storybook-dark-mode@4.0.2` — recomendado para esta versión.** Ya ofrece un botón
   sol/luna en Stories y Docs, persistencia, temas del manager y clases del iframe.
   Requiere verificar su funcionamiento real con Storybook 8.6.14 y el React resuelto
   por las dependencias de Docs antes de dar por terminada la integración.
2. Addon personalizado: más código para persistencia, eventos, accesibilidad y sincronización.
   No se justifica mientras la opción anterior cumpla las pruebas.
3. Solo `addon-themes`: resuelve temas del contenido, pero no satisface por sí solo
   el cambio del manager y los paneles.

No instalar la versión más reciente de un addon sin comprobar sus requisitos.
Si falla la compatibilidad de la opción recomendada, detenerse y revisar la propuesta;
no actualizar Storybook ni añadir otro sistema de estado como arreglo improvisado.

## 4. Arquitectura

```text
Sol/luna del addon
  └─ estado y persistencia exclusivos de Storybook
      ├─ manager: interfaz y paneles
      ├─ iframe: clases de tema en html
      │   ├─ Admin: cmsnet-light / cmsnet-dark
      │   └─ WebApp: ungolmas-light / ungolmas-dark
      └─ Docs nativo: estilos MDX adaptados mediante CSS del preview
```

### Responsabilidades

- **Addon:** única fuente de verdad. No crear un segundo almacenamiento, Pinia ni un
  listener manual que compita con el addon.
- **`.storybook/theme.ts`:** datos tipados de temas del manager y branding compartido.
  Sin DOM, almacenamiento, imports de CSS ni lógica de negocio.
- **`.storybook/manager.ts`:** configuración inicial del manager usando esos datos;
  no debe sobrescribir después la elección restaurada por el addon.
- **`.storybook/preview.ts`:** parámetros del addon y carga de estilos.
  Se conserva el DocsContainer nativo de Storybook, sin imports directos de React,
  componentes React propios ni suscripciones manuales de apariencia.
- **`.storybook/preview.css`:** fondo, texto, `color-scheme` y ajustes necesarios de
  contraste exclusivos del iframe. Estilos con `@apply`, tokens existentes y selectores
  acotados al contexto de Storybook. Nunca se importa desde la aplicación.
- **Historias/MDX:** describen ejemplos, heredan el tema y no administran preferencias.

React permanece únicamente como dependencia interna de Storybook y sus addons.
No se declara directamente en el proyecto; los componentes y sus historias continúan siendo Vue.

### Comportamiento

- Inicio sin preferencia: claro. Una elección guardada prevalece en siguientes visitas.
- Modo claro: `cmsnet-light` y `ungolmas-light`.
- Modo oscuro: `cmsnet-dark`, `ungolmas-dark` y, si lo requieren las utilidades existentes, `dark`.
- El addon retira las clases del modo anterior antes de aplicar el siguiente.
- El botón está disponible tanto en Stories como en Docs, incluidas las páginas introductorias.
- Los estilos de Docs heredan las variables aplicadas por el addon: fondo, títulos,
  párrafos, tablas y código inline. No se reemplaza el proveedor de tema de DocsContainer.
  Los bloques internos que consumen ese proveedor, como el resaltado de sintaxis,
  conservan su tema nativo; no se garantiza su cambio completo mediante CSS.
- Se utilizan los mecanismos de persistencia del addon; no se leen ni escriben claves
  de preferencias de la aplicación. La persistencia depende del origen del navegador.
- La apertura del iframe sin manager se incluye en las pruebas: si el addon no restaura
  allí el modo, documentar el límite y acordar una solución antes de ampliar archivos.

## 5. Contrato visual

- Mantener el diseño de Welcome y el contenido ya aprobado.
- Mantener Primary blanco/negro y Secondary oscuro en modo oscuro.
- Propuesta en claro: Primary oscuro con texto claro; Secondary claro con borde visible;
  Outline transparente con borde discontinuo. Estos ajustes viven solo en el CSS de Storybook.
- AdminInput y textos auxiliares heredan las variables del tema.
- No invertir imágenes, logos ni colores de marca con filtros CSS.
- No inventar variantes de WebApp: aplicar sus variables existentes y `color-scheme`,
  indicando los estilos aún pendientes en vez de simular un componente terminado.
- Revisar hover, focus-visible, disabled, readOnly y código fuente en ambos modos.

## 6. Inventario cerrado de cambios propuestos

### Archivos nuevos de implementación: solo dos

| Archivo                  | Propósito                                               |
| ------------------------ | ------------------------------------------------------- |
| `.storybook/theme.ts`    | Temas y branding compartidos. Único TS nuevo propuesto. |
| `.storybook/preview.css` | Adaptación visual aislada de Storybook.                 |

No se crearán `docs-container.ts`, servicios, stores ni carpetas adicionales de tema.
Este documento es independiente de esos dos archivos y no carga código en Storybook.

### Archivos existentes a modificar

- `.storybook/main.ts`: registrar el addon y desactivar backgrounds en Essentials.
- `.storybook/manager.ts`: reutilizar los temas compartidos sin forzar el oscuro.
- `.storybook/preview.ts`: configurar el addon, las clases y el CSS aislado.
- `package.json` y `pnpm-lock.yaml`: dependencias de desarrollo estrictamente necesarias.
- `packages/core/Admin/resources/assets/css/components/admin-button/admin-button.stories.js`:
  retirar oscuro fijo y usar tokens en los textos auxiliares.
- `packages/core/Admin/resources/assets/css/components/admin-input/admin-input.stories.js`:
  retirar oscuro fijo y actualizar el comentario del helper.
- Los `.mdx` de esos dos componentes: retirar `cmsnet-dark` de los ejemplos.
- `packages/core/Admin/resources/stories/getting-started.mdx`: explicar el control único.

Dependencia directa añadida: `storybook-dark-mode@4.0.2`.
React, sus tipos y los bloques de documentación permanecen en el árbol interno de Storybook,
sin declaraciones directas ni alias para importar dependencias transitivas desde nuestro código.
No actualizar React ni el resto del árbol por conveniencia.

Fuera de alcance: CSS de producción, vistas Blade/Vue de la aplicación, base de datos,
Vite de la aplicación, watchers, actualizaciones generales y cambios en WebApp no relacionados.

## 7. Plan de implementación por fases

1. **Aprobación:** confirmar inventario y comportamiento visual. No instalar nada antes.
2. **Entorno:** usar exclusivamente WSL para instalar y ejecutar sobre este `node_modules`.
   Revisar estado de Git y versiones; no borrar ni recrear dependencias si no es necesario.
3. **Integración mínima:** añadir dependencias, temas compartidos y un único botón.
   Probar manager y una historia antes de adaptar todo el catálogo.
4. **Docs y ejemplos:** adaptar el CSS de Docs, retirar clases fijas y adaptar contraste
   exclusivamente en Storybook. Mantener tamaños, contenido y orden del lateral.
5. **Verificación:** ejecutar los controles ligeros y la matriz manual siguiente.
6. **Entrega:** informar archivos y dependencias realmente cambiados, pruebas y límites.
   No hacer commit ni push sin una nueva solicitud.

Si aparece un bloqueo de arranque, detener la ampliación y diagnosticarlo antes de seguir.

## 8. Validación y criterios de aceptación

### Comprobaciones ligeras

- Formato solo de archivos afectados, TypeScript y `git diff --check`.
- Compilación aislada del CSS y búsqueda acotada de temas fijos en las historias afectadas.
- Revisar el diff del lockfile: sin actualizaciones ajenas al addon.
- No considerar una prueba `--version` equivalente a arrancar Storybook.

### Matriz funcional manual

Probar claro y oscuro en:

- Welcome, Getting Started y Catalog.
- Docs e historias de AdminButton: variantes, tamaños y disabled.
- Docs e historias de AdminInput: texto, correo, contraseña, búsqueda, URL y estados.
- Docs e historias disponibles de WebApp, sin atribuirles estilos aún no implementados.
- Lateral, toolbar, Controls/Actions y bloques de código.

Comprobar además:

- Un único botón visible, operable por teclado y con nombre accesible de la acción.
- No existe otro selector de fondos contradictorio.
- Navegar y recargar conserva el modo, sin clases opuestas simultáneas.
- Sin errores en consola ni bucles de eventos; componentes Vue siguen montando.
- Foco visible y contraste de texto, botones y campos en ambos modos.
- Las preferencias y el aspecto de la aplicación abierta por separado no cambian.
- Acceso directo al iframe y primera carga sin preferencia: documentar resultados reales.

Utilizar una única instancia de Storybook, acordada con el usuario. No dejar servidores
en segundo plano ni ejecutar un build completo como verificación rutinaria.

## 9. Riesgos y reversión

- **Windows/WSL:** no compartir una instalación de dependencias generada por ambos entornos.
- **Compatibilidad del addon:** comprobar la versión fijada en ejecución con Docs y Vue.
- **Cascada CSS:** los CSS globales de Admin/WebApp pueden competir; resolver el entorno
  únicamente en `.storybook/preview.css`, sin cambiar estilos de producción.
- **Fidelidad visual:** los ajustes claros de botones son una adaptación explícita del catálogo,
  no un cambio silencioso del diseño de Admin.
- **Mantenimiento:** la versión del addon queda ligada a Storybook 8; reconsiderar al actualizarlo.
- **Reversión:** retirar únicamente el addon, los dos archivos nuevos y los cambios del tema,
  preservando páginas, componentes y contenido del commit base `c9fce14` y trabajo posterior ajeno.

## 10. Referencias

- Grommet: https://github.com/grommet/grommet/blob/master/storybook/preview.js
- Addon y compatibilidad: https://unpkg.com/storybook-dark-mode@4.0.2/package.json
- Registro del botón en Stories y Docs:
  https://unpkg.com/storybook-dark-mode@4.0.2/dist/esm/preset/manager.js

La referencia se usa para entender el comportamiento, no para copiar estilos o introducir Grommet.

## 11. Registro de implementación

- Creados únicamente `.storybook/theme.ts` y `.storybook/preview.css` como código nuevo.
- Instalación realizada desde WSL. Se mantiene `storybook-dark-mode@4.0.2` como dependencia
  de desarrollo. Por solicitud del usuario se retiraron las declaraciones directas de
  `react`, `@types/react` y `@storybook/blocks`; siguen disponibles internamente para Storybook.
- Manager y preview comparten branding y temas. Se desactiva el addon de fondos.
- El addon es el propietario de la persistencia y de las clases de apariencia.
- Retirado el adaptador React y su listener; Docs utiliza el contenedor nativo.
  La tipografía MDX se adapta mediante el CSS exclusivo de Storybook.
  El resaltado de sintaxis y otros bloques basados en el proveedor interno de Docs
  no se declaran sincronizados: requieren revisión visual y quedan como límite de esta alternativa.
- Eliminado el oscuro fijo de los ejemplos Admin; ajustes claros de botones limitados a Storybook.
- TypeScript estricto y ESLint de los archivos TS afectados: correctos.
- El servidor existente responde con el índice de historias, pero conserva transformaciones de
  `preview.ts` y anotaciones de backgrounds anteriores a los cambios. No se reinició ni se abrió otro.
- Pendiente tras reinicio: matriz visual, persistencia en navegador, teclado y consola.
- El iframe abierto sin manager no dispone del botón ni de la aplicación de clases por el manager.
  No se implementa un segundo lector de localStorage para restaurar allí la preferencia.
  La sincronización de apariencia está prevista para la experiencia completa de Storybook;
  la restauración en un iframe independiente queda como límite explícito, fuera de esta entrega.
- No se modificaron estilos de producción ni preferencias de la aplicación.
