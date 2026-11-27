/**
 * Historias de Storybook para el componente visual Button (WebApp Pública).
 */
export default {
  title: 'WebApp/Components/Button',
  parameters: {
    docs: {
      description: {
        component:
          'Botón base de la web pública. Las stories usan solo clases existentes en button.css.',
      },
    },
  },
};

export const Primary = {
  render: () => ({
    template: `
      <button class="button primary">
        Button
      </button>
    `,
  }),
  parameters: {
    docs: {
      source: {
        code: `<button class="button primary">Button</button>`,
      },
    },
  },
};

export const Secondary = {
  render: () => ({
    template: `
      <button class="button secondary">
        Button
      </button>
    `,
  }),
  parameters: {
    docs: {
      source: {
        code: `<button class="button secondary">Button</button>`,
      },
    },
  },
};

export const PrimaryGradient = {
  render: () => ({
    template: `
      <button class="button primary-gradient">
        Button
      </button>
    `,
  }),
  parameters: {
    docs: {
      description: {
        story: 'Variante con gradiente principal usada en CTAs del sitio.',
      },
      source: {
        code: `<button class="button primary-gradient">Button</button>`,
      },
    },
  },
};

export const SecondaryGradient = {
  render: () => ({
    template: `
      <button class="button secondary-gradient">
        Button
      </button>
    `,
  }),
  parameters: {
    docs: {
      description: {
        story: 'Variante con gradiente secundario usada en CTAs alternos.',
      },
      source: {
        code: `<button class="button secondary-gradient">Button</button>`,
      },
    },
  },
};

export const Outline = {
  render: () => ({
    template: `
      <button class="button outline">
        Button
      </button>
    `,
  }),
  parameters: {
    docs: {
      source: {
        code: `<button class="button outline">Button</button>`,
      },
    },
  },
};

export const Light = {
  render: () => ({
    template: `
      <button class="button light">
        Button
      </button>
    `,
  }),
  parameters: {
    docs: {
      source: {
        code: `<button class="button light">Button</button>`,
      },
    },
  },
};

export const Muted = {
  render: () => ({
    template: `
      <button class="button muted">
        Button
      </button>
    `,
  }),
  parameters: {
    docs: {
      source: {
        code: `<button class="button muted">Button</button>`,
      },
    },
  },
};

export const Sizes = {
  render: () => ({
    template: `
      <div class="flex flex-wrap items-center gap-3">
        <button class="button primary sm">Small</button>
        <button class="button primary">Default</button>
        <button class="button primary lg">Large</button>
        <button class="button primary xl">Extra large</button>
      </div>
    `,
  }),
  parameters: {
    docs: {
      source: {
        code: `<button class="button primary sm">Small</button>
<button class="button primary">Default</button>
<button class="button primary lg">Large</button>
<button class="button primary xl">Extra large</button>`,
      },
    },
  },
};

export const FullWidth = {
  render: () => ({
    template: `
      <button class="button primary-gradient w-f">
        Button
      </button>
    `,
  }),
  parameters: {
    docs: {
      source: {
        code: `<button class="button primary-gradient w-f">Button</button>`,
      },
    },
  },
};
