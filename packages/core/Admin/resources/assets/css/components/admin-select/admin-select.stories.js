import './admin-select.css';

export default {
  title: 'Admin/Components/AdminSelect',
  parameters: {
    docs: {
      description: {
        component: 'Select nativo de CMSNET con flecha, etiqueta y estados accesibles.',
      },
    },
  },
};

/**
 * Creates an accessible select example with matching source markup.
 * @param {string} markup - Select markup using the current preview theme.
 * @returns {import('@storybook/vue3').StoryObj} The select story.
 */
const createSelectStory = (markup) => ({
  /**
   * Renders the select without overriding the selected theme.
   * @returns {import('vue').Component} The story component.
   */
  render: () => ({ template: `<div class="w-full max-w-sm">${markup}</div>` }),
  parameters: { docs: { source: { code: markup } } },
});

const arrow = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
  <path d="m7 10 5 5 5-5" />
</svg>`;

export const Default =
  createSelectStory(`<label for="admin-select-default" class="admin-select-label">Tipo de usuario</label>
<div class="admin-select-wrapper">
  <select id="admin-select-default" class="admin-select" name="user_type">
    <option value="">Selecciona un tipo</option>
    <option value="player">Jugador</option>
    <option value="organizer">Organizador</option>
  </select>
  ${arrow}
</div>`);

export const Selected =
  createSelectStory(`<label for="admin-select-selected" class="admin-select-label">Estado</label>
<div class="admin-select-wrapper">
  <select id="admin-select-selected" class="admin-select" name="state">
    <option value="active" selected>Activo</option>
    <option value="inactive">Inactivo</option>
  </select>
  ${arrow}
</div>`);

export const Disabled =
  createSelectStory(`<label for="admin-select-disabled" class="admin-select-label">Tipo de usuario</label>
<div class="admin-select-wrapper">
  <select id="admin-select-disabled" class="admin-select" disabled>
    <option>Sin opciones disponibles</option>
  </select>
  ${arrow}
</div>`);

export const DisabledOption =
  createSelectStory(`<label for="admin-select-option" class="admin-select-label">Estado</label>
<div class="admin-select-wrapper">
  <select id="admin-select-option" class="admin-select" name="available_state">
    <option value="active">Activo</option>
    <option value="inactive">Inactivo</option>
    <option value="pending" disabled>Pendiente (no disponible)</option>
  </select>
  ${arrow}
</div>`);
