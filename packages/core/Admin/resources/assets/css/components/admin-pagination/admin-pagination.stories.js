import { computed, ref } from 'vue';
import './admin-pagination.css';

export default {
  title: 'Admin/Components/AdminPagination',
  parameters: {
    docs: {
      description: {
        component:
          'Paginación de CMSNET con contador y navegación de ejemplo en memoria, sin peticiones.',
      },
    },
  },
};

const markup = `<nav class="admin-pagination" aria-label="Paginación de usuarios de ejemplo">
  <p class="admin-pagination-summary" role="status" aria-live="polite">{{ start }}–{{ end }} de {{ totalItems }} usuarios</p>
  <div class="admin-pagination-actions">
    <button type="button" class="admin-pagination-action" :disabled="!canPrevious" @click="handlePrevious">Anterior</button>
    <button type="button" class="admin-pagination-action" :disabled="!canNext" @click="handleNext">Siguiente</button>
  </div>
</nav>`;

/**
 * Creates pagination with local-only state and explicit boundary conditions.
 * @param {number} initialPage - Initially selected page.
 * @param {number} totalItems - Number of fictitious records.
 * @returns {import('@storybook/vue3').StoryObj} The pagination story.
 */
const createPaginationStory = (initialPage, totalItems) => ({
  /**
   * Creates an isolated pagination instance for the example.
   * @returns {import('vue').Component} The story component.
   */
  render: () => ({
    /**
     * Initializes local pagination state without network or persistence.
     * @returns {object} The template bindings.
     */
    setup: () => {
      const pageSize = 10;
      const pageCount = Math.max(1, Math.ceil(totalItems / pageSize));
      const page = ref(Math.min(pageCount, Math.max(1, initialPage)));
      const start = computed(() => (totalItems === 0 ? 0 : (page.value - 1) * pageSize + 1));
      const end = computed(() => Math.min(page.value * pageSize, totalItems));
      const canPrevious = computed(() => page.value > 1);
      const canNext = computed(() => page.value < pageCount);

      /**
       * Moves to the previous available page.
       * @returns {void}
       */
      const handlePrevious = () => {
        if (canPrevious.value) page.value -= 1;
      };

      /**
       * Moves to the next available page.
       * @returns {void}
       */
      const handleNext = () => {
        if (canNext.value) page.value += 1;
      };

      return { start, end, totalItems, canPrevious, canNext, handlePrevious, handleNext };
    },
    template: `<div class="w-full">${markup}</div>`,
  }),
  parameters: { docs: { source: { code: markup } } },
});

export const Default = createPaginationStory(2, 50);
export const FirstPage = createPaginationStory(1, 50);
export const LastPage = createPaginationStory(5, 50);
export const Empty = createPaginationStory(1, 0);
