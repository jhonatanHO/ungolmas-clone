import './admin-checkbox.css';

export default {
  title: 'Admin/Components/AdminCheckbox',
  parameters: {
    docs: {
      description: {
        component: 'Casilla nativa de CMSNET para opciones independientes, con etiqueta asociada.',
      },
    },
  },
};

/**
 * Creates a checkbox example and its matching source.
 * @param {string} markup - Checkbox markup with a wrapping label.
 * @returns {import('@storybook/vue3').StoryObj} The checkbox story.
 */
const createCheckboxStory = (markup) => ({
  /**
   * Renders the native checkbox with the active theme.
   * @returns {import('vue').Component} The story component.
   */
  render: () => ({ template: markup }),
  parameters: { docs: { source: { code: markup } } },
});

export const Default = createCheckboxStory(`<label class="admin-checkbox">
  <input type="checkbox" name="receive_updates" />
  <span>Recibir novedades</span>
</label>`);

export const Checked = createCheckboxStory(`<label class="admin-checkbox">
  <input type="checkbox" name="show_profile" checked />
  <span>Mostrar perfil</span>
</label>`);

export const Disabled = createCheckboxStory(`<label class="admin-checkbox">
  <input type="checkbox" name="unavailable_option" disabled />
  <span>Opción no disponible</span>
</label>`);

export const CheckedDisabled = createCheckboxStory(`<label class="admin-checkbox">
  <input type="checkbox" name="fixed_option" checked disabled />
  <span>Opción activada y bloqueada</span>
</label>`);
