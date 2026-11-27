import './admin-switch.css';

export default {
  title: 'Admin/Components/AdminSwitch',
  parameters: {
    docs: {
      description: {
        component: 'Interruptor de CMSNET basado en checkbox con role="switch" y estado nativo.',
      },
    },
  },
};

/**
 * Creates a switch example and its matching source markup.
 * @param {string} markup - Switch markup with an accessible label.
 * @returns {import('@storybook/vue3').StoryObj} The switch story.
 */
const createSwitchStory = (markup) => ({
  /**
   * Renders the switch inside the selected preview theme.
   * @returns {import('vue').Component} The story component.
   */
  render: () => ({ template: `<div class="w-full max-w-sm">${markup}</div>` }),
  parameters: { docs: { source: { code: markup } } },
});

export const Default = createSwitchStory(`<label class="admin-switch">
  <span>Permitir inscripciones</span>
  <input type="checkbox" role="switch" name="allow_registrations" />
</label>`);

export const Checked = createSwitchStory(`<label class="admin-switch">
  <span>Publicar contenido</span>
  <input type="checkbox" role="switch" name="publish_content" checked />
</label>`);

export const Disabled = createSwitchStory(`<label class="admin-switch">
  <span>Opción no disponible</span>
  <input type="checkbox" role="switch" name="unavailable_switch" disabled />
</label>`);

export const CheckedDisabled = createSwitchStory(`<label class="admin-switch">
  <span>Opción activada y bloqueada</span>
  <input type="checkbox" role="switch" name="fixed_switch" checked disabled />
</label>`);
