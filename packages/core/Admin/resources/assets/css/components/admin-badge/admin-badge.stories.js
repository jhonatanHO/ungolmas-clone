import './admin-badge.css';

export default {
  title: 'Admin/Components/AdminBadge',
  parameters: {
    docs: {
      description: {
        component: 'Etiqueta neutral de estado utilizada en el listado de usuarios de CMSNET.',
      },
    },
  },
};

/**
 * Creates a non-interactive status label example.
 * @param {string} markup - Badge markup with a readable status.
 * @returns {import('@storybook/vue3').StoryObj} The badge story.
 */
const createBadgeStory = (markup) => ({
  /**
   * Renders neutral status labels using the active theme.
   * @returns {import('vue').Component} The story component.
   */
  render: () => ({ template: `<div class="flex flex-wrap gap-2">${markup}</div>` }),
  parameters: { docs: { source: { code: markup } } },
});

export const Default = createBadgeStory('<span class="admin-badge">Activo</span>');

export const States = createBadgeStory(`<span class="admin-badge">Activo</span>
<span class="admin-badge">Inactivo</span>
<span class="admin-badge">Sin estado</span>`);

export const LongLabel = createBadgeStory(
  '<span class="admin-badge">Pendiente de confirmación</span>'
);
