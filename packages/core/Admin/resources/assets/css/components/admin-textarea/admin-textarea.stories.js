import './admin-textarea.css';

export default {
  title: 'Admin/Components/AdminTextarea',
  parameters: {
    docs: {
      description: {
        component: 'Campo multilínea de CMSNET, con altura mínima de 80 px y ajuste vertical.',
      },
    },
  },
};

/**
 * Creates a multiline field example and matching source markup.
 * @param {string} markup - Accessible textarea markup.
 * @returns {import('@storybook/vue3').StoryObj} The textarea story.
 */
const createTextareaStory = (markup) => ({
  /**
   * Renders the field using the selected preview theme.
   * @returns {import('vue').Component} The story component.
   */
  render: () => ({ template: `<div class="w-full max-w-sm">${markup}</div>` }),
  parameters: { docs: { source: { code: markup } } },
});

export const Default =
  createTextareaStory(`<label for="admin-textarea-default" class="admin-textarea-label">Descripción</label>
<textarea id="admin-textarea-default" class="admin-textarea" name="description" rows="3" maxlength="1000" placeholder="Escribe una descripción..."></textarea>`);

export const WithValue =
  createTextareaStory(`<label for="admin-textarea-value" class="admin-textarea-label">Observaciones</label>
<textarea id="admin-textarea-value" class="admin-textarea" name="notes" rows="3" maxlength="1000">Texto de ejemplo para revisar el campo multilínea.</textarea>`);

export const Disabled =
  createTextareaStory(`<label for="admin-textarea-disabled" class="admin-textarea-label">Descripción</label>
<textarea id="admin-textarea-disabled" class="admin-textarea" rows="3" placeholder="No disponible" disabled></textarea>`);

export const ReadOnly =
  createTextareaStory(`<label for="admin-textarea-readonly" class="admin-textarea-label">Observaciones de solo lectura</label>
<textarea id="admin-textarea-readonly" class="admin-textarea" rows="3" readonly>Información de ejemplo que no se puede editar.</textarea>`);
