import './admin-radio.css';

export default {
  title: 'Admin/Components/AdminRadio',
  parameters: {
    docs: {
      description: {
        component:
          'Opciones nativas de selección única de CMSNET, agrupadas con fieldset y legend.',
      },
    },
  },
};

/**
 * Creates an accessible radio example and matching source.
 * @param {string} markup - Radio markup with labels and group semantics.
 * @returns {import('@storybook/vue3').StoryObj} The radio story.
 */
const createRadioStory = (markup) => ({
  /**
   * Renders radio controls without forcing a preview theme.
   * @returns {import('vue').Component} The story component.
   */
  render: () => ({ template: markup }),
  parameters: { docs: { source: { code: markup } } },
});

export const Default = createRadioStory(`<label class="admin-radio">
  <input type="radio" name="admin_radio_default" value="individual" />
  <span>Individual</span>
</label>`);

export const Selected = createRadioStory(`<label class="admin-radio">
  <input type="radio" name="admin_radio_selected" value="team" checked />
  <span>Por equipos</span>
</label>`);

export const Group = createRadioStory(`<fieldset class="admin-radio-group">
  <legend>Participación</legend>
  <div class="admin-radio-options">
    <label class="admin-radio">
      <input type="radio" name="admin_radio_participation" value="individual" checked />
      <span>Individual</span>
    </label>
    <label class="admin-radio">
      <input type="radio" name="admin_radio_participation" value="team" />
      <span>Por equipos</span>
    </label>
  </div>
</fieldset>`);

export const Disabled = createRadioStory(`<fieldset class="admin-radio-group" disabled>
  <legend>Participación no disponible</legend>
  <div class="admin-radio-options">
    <label class="admin-radio">
      <input type="radio" name="admin_radio_disabled" value="individual" checked />
      <span>Individual</span>
    </label>
    <label class="admin-radio">
      <input type="radio" name="admin_radio_disabled" value="team" />
      <span>Por equipos</span>
    </label>
  </div>
</fieldset>`);
