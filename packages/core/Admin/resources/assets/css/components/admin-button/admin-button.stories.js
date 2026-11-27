/**
 * Historias de Storybook para el componente visual AdminButton (CMSNET Backoffice).
 */
export default {
  title: 'Admin/Components/AdminButton',
  parameters: {
    docs: {
      description: {
        component:
          'Botón base del panel administrativo CMSNET. Las stories usan las clases definidas en admin-button.css.',
      },
    },
  },
};

export const Primary = {
  render: () => ({
    template: `
      <div>
        <button type="button" class="admin-button primary">
          Probar formulario
        </button>
      </div>
    `,
  }),
  parameters: {
    docs: {
      source: {
        code: `<button type="button" class="admin-button primary">Probar formulario</button>`,
      },
    },
  },
};

export const Secondary = {
  render: () => ({
    template: `
      <div>
        <button type="button" class="admin-button secondary">
          Restablecer
        </button>
      </div>
    `,
  }),
  parameters: {
    docs: {
      source: {
        code: `<button type="button" class="admin-button secondary">Restablecer</button>`,
      },
    },
  },
};

export const Outline = {
  render: () => ({
    template: `
      <div>
        <button type="button" class="admin-button outline">
          Ver detalles
        </button>
      </div>
    `,
  }),
  parameters: {
    docs: {
      source: {
        code: `<button type="button" class="admin-button outline">Ver detalles</button>`,
      },
    },
  },
};

export const Sizes = {
  render: () => ({
    template: `
      <div class="space-y-4">
        <div class="space-y-2">
          <p class="text-sm text-[color:var(--theme-900)]">Primary</p>
          <div role="group" aria-label="Tamaños Primary" class="flex flex-wrap items-center gap-3">
            <button type="button" class="admin-button primary sm">Small</button>
            <button type="button" class="admin-button primary">Default</button>
            <button type="button" class="admin-button primary lg">Large</button>
            <button type="button" class="admin-button primary xl">Extra large</button>
          </div>
        </div>
        <div class="space-y-2">
          <p class="text-sm text-[color:var(--theme-900)]">Secondary</p>
          <div role="group" aria-label="Tamaños Secondary" class="flex flex-wrap items-center gap-3">
            <button type="button" class="admin-button secondary sm">Small</button>
            <button type="button" class="admin-button secondary">Default</button>
            <button type="button" class="admin-button secondary lg">Large</button>
            <button type="button" class="admin-button secondary xl">Extra large</button>
          </div>
        </div>
        <div class="space-y-2">
          <p class="text-sm text-[color:var(--theme-900)]">Outline</p>
          <div role="group" aria-label="Tamaños Outline" class="flex flex-wrap items-center gap-3">
            <button type="button" class="admin-button outline sm">Small</button>
            <button type="button" class="admin-button outline">Default</button>
            <button type="button" class="admin-button outline lg">Large</button>
            <button type="button" class="admin-button outline xl">Extra large</button>
          </div>
        </div>
      </div>
    `,
  }),
  parameters: {
    docs: {
      source: {
        code: `<button type="button" class="admin-button primary sm">Small</button>
<button type="button" class="admin-button primary">Default</button>
<button type="button" class="admin-button primary lg">Large</button>
<button type="button" class="admin-button primary xl">Extra large</button>

<button type="button" class="admin-button secondary sm">Small</button>
<button type="button" class="admin-button secondary">Default</button>
<button type="button" class="admin-button secondary lg">Large</button>
<button type="button" class="admin-button secondary xl">Extra large</button>

<button type="button" class="admin-button outline sm">Small</button>
<button type="button" class="admin-button outline">Default</button>
<button type="button" class="admin-button outline lg">Large</button>
<button type="button" class="admin-button outline xl">Extra large</button>`,
      },
    },
  },
};

export const Disabled = {
  render: () => ({
    template: `
      <button class="admin-button primary" disabled>
        Disabled
      </button>
    `,
  }),
  parameters: {
    docs: {
      source: {
        code: `<button class="admin-button primary" disabled>Disabled</button>`,
      },
    },
  },
};
