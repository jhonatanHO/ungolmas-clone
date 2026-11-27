# Política de Seguridad - UNGOLMAS Core

En UNGOLMAS Core nos tomamos la seguridad con la máxima seriedad. Este documento describe el proceso para reportar vulnerabilidades y los estándares aplicados en la plataforma.

---

## 🛡 Versiones Soportadas

| Versión | Soportada          | Estado                         |
| ------- | ------------------ | ------------------------------ |
| `1.x`   | :white_check_mark: | Desarrollo activo / Producción |
| `< 1.0` | :x:                | Versiones preliminares         |

---

## 📢 Reporte de Vulnerabilidades

Si descubres una posible vulnerabilidad de seguridad en UNGOLMAS Core o en cualquiera de sus paquetes integrados, **NO** abras un Issue público.

1. Envía un correo electrónico detallado a **security@combativa.com**.
2. Incluye en tu reporte:
   - Descripción clara de la vulnerabilidad.
   - Pasos para reproducir (Proof of Concept, PoC).
   - Componentes o módulos afectados (`packages/core/*` o `packages/integrations/*`).
   - Impacto estimado en el sistema y los datos.
   - Posibles mitigaciones sugeridas.
3. Se confirmará la recepción dentro de **48 horas laborables**.

---

## ⏱ Proceso de Divulgación Responsable

1. **Confirmación y Triage**: Validación en entorno de pruebas aislado.
2. **Corrección**: Desarrollo y prueba de parche en repositorio privado.
3. **Liberación**: Publicación prioritaria de release de seguridad.
4. **Divulgación pública**: Notas de versión una vez mitigado el impacto.

---

## 🔒 Buenas Prácticas del Proyecto

- **Mínimo Privilegio**: Cuentas de base de datos con permisos acotados.
- **CSRF & XSS**: Validación estricta con Form Requests y escape contextual en Blade y Vue.
- **Gestión de Secretos**: Ningún secreto versionado; variables de entorno en `.env`.
- **Sesiones & Caché**: Driver `file` con permisos de sistema seguros en `storage/`.
