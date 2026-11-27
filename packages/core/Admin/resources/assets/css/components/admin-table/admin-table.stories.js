import './admin-table.css';
import '../admin-badge/admin-badge.css';

export default {
  title: 'Admin/Components/AdminTable',
  parameters: {
    layout: 'fullscreen',
    docs: {
      description: {
        component:
          'Tabla de solo lectura de CMSNET con datos ficticios, estado vacío y desplazamiento horizontal.',
      },
    },
  },
};

/**
 * Creates a read-only table with matching HTML source.
 * @param {string} rows - Fictitious rows or the empty-state row.
 * @returns {import('@storybook/vue3').StoryObj} The table story.
 */
const createTableStory = (rows) => {
  const markup = `<div class="admin-table-wrapper" role="region" aria-label="Usuarios de ejemplo" tabindex="0">
  <table class="admin-table">
    <caption class="sr-only">Listado de usuarios ficticios</caption>
    <thead>
      <tr>
        <th scope="col">Usuario</th>
        <th scope="col">Nombre completo</th>
        <th scope="col">Correo</th>
        <th scope="col">Tipo</th>
        <th scope="col">Estado</th>
        <th scope="col" class="admin-table-date">Registro</th>
      </tr>
    </thead>
    <tbody>
      ${rows}
    </tbody>
  </table>
</div>`;

  return {
    /**
     * Renders the responsive table without backend access.
     * @returns {import('vue').Component} The story component.
     */
    render: () => ({ template: `<div class="p-4">${markup}</div>` }),
    parameters: { docs: { source: { code: markup } } },
  };
};

export const Default = createTableStory(`<tr>
  <td><span class="admin-table-user">jugador.demo</span><span class="admin-table-meta">#001</span></td>
  <td class="admin-table-truncate">Usuario de ejemplo</td>
  <td class="admin-table-truncate">jugador@example.com</td>
  <td>Jugador</td>
  <td><span class="admin-badge">Activo</span></td>
  <td class="admin-table-date">01/11/2026</td>
</tr>
<tr>
  <td><span class="admin-table-user">organizador.demo</span><span class="admin-table-meta">#002</span></td>
  <td class="admin-table-truncate">Organizador de ejemplo</td>
  <td class="admin-table-truncate">organizador@example.com</td>
  <td>Organizador</td>
  <td><span class="admin-badge">Inactivo</span></td>
  <td class="admin-table-date">02/11/2026</td>
</tr>`);

export const Empty = createTableStory(`<tr>
  <td colspan="6" class="admin-table-empty">No hay usuarios registrados.</td>
</tr>`);

export const LongContent = createTableStory(`<tr>
  <td><span class="admin-table-user">usuario.demostracion</span><span class="admin-table-meta">#003</span></td>
  <td class="admin-table-truncate">Nombre de usuario de demostración con contenido extenso</td>
  <td class="admin-table-truncate">direccion.de.correo.extensa.para.demostracion@example.com</td>
  <td>Sin tipo</td>
  <td><span class="admin-badge">Sin estado</span></td>
  <td class="admin-table-date">—</td>
</tr>`);
