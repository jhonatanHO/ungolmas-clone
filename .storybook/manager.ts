import { addons } from 'storybook/internal/manager-api';
import { storybookThemes } from './theme';

/**
 * Configuración del tema y branding del sidebar de Storybook.
 */
addons.setConfig({
  theme: storybookThemes.light,
});
