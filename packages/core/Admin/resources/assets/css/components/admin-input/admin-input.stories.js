/**
 * Documents the existing Admin input styles with fictitious values.
 */
export default {
  title: 'Admin/Components/AdminInput',
  parameters: {
    docs: {
      description: {
        component:
          'Inputs compactos del Admin: texto, correo, contraseña, búsqueda y URL. Solo presentación, sin enviar datos.',
      },
    },
  },
};

/**
 * Creates a themed input example and its matching HTML source.
 *
 * @param {string} markup - Accessible input markup for the example.
 * @returns {import('@storybook/vue3').StoryObj} The input story configuration.
 */
const createInputStory = (markup) => ({
  /**
   * Renders the example using the selected Storybook theme.
   *
   * @returns {import('vue').Component} The story component.
   */
  render: () => ({
    template: `
      <div class="w-full max-w-sm">
        ${markup}
      </div>
    `,
  }),
  parameters: {
    docs: {
      source: {
        code: markup,
      },
    },
  },
});

export const Default = {
  ...createInputStory(`<label for="admin-input-text" class="admin-input-label">Nombre</label>
<input id="admin-input-text" type="text" class="admin-input" placeholder="Nombre de ejemplo" />`),
};

export const Email = {
  ...createInputStory(`<label for="admin-input-email" class="admin-input-label">Correo electrónico</label>
<input id="admin-input-email" type="email" class="admin-input" placeholder="usuario@example.com" autocomplete="off" />`),
};

export const Password = {
  ...createInputStory(`<label for="admin-input-password" class="admin-input-label">Contraseña</label>
<input id="admin-input-password" type="password" class="admin-input" placeholder="Ingresa tu contraseña" autocomplete="new-password" />`),
};

export const Search = {
  ...createInputStory(`<label for="admin-input-search" class="admin-input-label">Buscar usuarios</label>
<div class="admin-input-search">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    <circle cx="10.5" cy="10.5" r="7.5" />
    <path d="m16 16 5 5" />
  </svg>
  <input id="admin-input-search" type="search" class="admin-input" placeholder="Buscar usuarios..." />
</div>`),
};

export const Url = {
  ...createInputStory(`<label for="admin-input-url" class="admin-input-label">URL del servicio</label>
<input id="admin-input-url" type="url" class="admin-input" placeholder="https://example.com" autocomplete="off" />`),
};

export const Disabled = {
  ...createInputStory(`<label for="admin-input-disabled" class="admin-input-label">Campo deshabilitado</label>
<input id="admin-input-disabled" type="text" class="admin-input" placeholder="No disponible" disabled />`),
};

export const ReadOnly = {
  ...createInputStory(`<label for="admin-input-readonly" class="admin-input-label">Solo lectura</label>
<input id="admin-input-readonly" type="text" class="admin-input" value="UNGOLMAS" readonly />`),
};
