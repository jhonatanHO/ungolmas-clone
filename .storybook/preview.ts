import type { Preview } from '@storybook/vue3';
import { storybookThemes } from './theme';
import '../resources/css/app.css';
import '../packages/core/Admin/resources/assets/css/admin.css';
import '../packages/core/WebApp/resources/assets/css/webapp.css';
import './preview.css';

const preview: Preview = {
  parameters: {
    darkMode: {
      current: 'light',
      light: storybookThemes.light,
      dark: storybookThemes.dark,
      classTarget: 'html',
      lightClass: ['cmsnet-light', 'ungolmas-light'],
      darkClass: ['cmsnet-dark', 'ungolmas-dark', 'dark'],
      stylePreview: true,
    },
    options: {
      storySort: {
        order: ['Welcome', 'Getting Started', 'Catalog', 'Admin', 'WebApp'],
      },
    },
    controls: {
      matchers: {
        color: /(background|color)$/i,
        date: /Date$/i,
      },
    },
  },
};

export default preview;
