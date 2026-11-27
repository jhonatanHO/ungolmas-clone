/**
 * Historias de Storybook para el componente visual Input (WebApp Pública).
 */
export default {
  title: 'WebApp/Components/Input',
  parameters: {
    docs: {
      description: {
        component:
          'Campo de entrada base de la web pública. Las stories usan las clases definidas en input.css.',
      },
    },
  },
};

export const Default = {
  render: () => ({
    template: `
      <input type="text" class="input" placeholder="Escribe aquí..." />
    `,
  }),
  parameters: {
    docs: {
      source: {
        code: `<input type="text" class="input" placeholder="Escribe aquí..." />`,
      },
    },
  },
};

export const Disabled = {
  render: () => ({
    template: `
      <input type="text" class="input" placeholder="Campo deshabilitado" disabled />
    `,
  }),
  parameters: {
    docs: {
      source: {
        code: `<input type="text" class="input" placeholder="Campo deshabilitado" disabled />`,
      },
    },
  },
};
