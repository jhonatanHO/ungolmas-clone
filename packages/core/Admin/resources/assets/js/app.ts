/**
 * DASHBOARD - UNGOLMAS CORE
 * Interactive CMSNET theme controller (Light, Dark, Midnight, System)
 * and admin UI entry point.
 */

import { createApp, defineAsyncComponent } from 'vue';
import { createPinia } from 'pinia';
import type { ThemeController } from '@/js/interfaces/app.interface';
import { init } from './ui';

/* VUE APPLICATION */

/**
 * Mounts the Vue 3 admin application with Pinia and asynchronous components when the root container exists.
 *
 * @returns {void}
 */
const initVueApp = (): void => {
  const rootContainer = document.querySelector<HTMLElement>('#app');
  if (!rootContainer) return;

  const app = createApp({});
  const pinia = createPinia();

  app.use(pinia);

  app.component(
    'admin-login',
    defineAsyncComponent(() => import('./components/admin-login.vue'))
  );

  app.mount(rootContainer);
};

/* STATE & CONTROLLER */

/**
 * Theme controller instance for the CMSNET admin panel.
 */
export const cmsnetThemeController: ThemeController = {
  currentTheme: ((): string => {
    const saved = localStorage.getItem('cmsnet_theme') || 'system';
    if (saved === 'dark') return 'cmsnet-dark';
    if (saved === 'light') return 'cmsnet-light';
    if (saved === 'midnight') return 'cmsnet-midnight';
    return saved;
  })(),

  mediaQuery: window.matchMedia('(prefers-color-scheme: dark)'),

  /**
   * Initializes the active theme and binds the DOM theme selector buttons.
   *
   * @returns {void}
   */
  init: (): void => {
    cmsnetThemeController.applyTheme(cmsnetThemeController.currentTheme);

    // NOTE: Listen in real time for operating system color scheme changes.
    cmsnetThemeController.mediaQuery.addEventListener('change', () => {
      if (cmsnetThemeController.currentTheme === 'system') {
        cmsnetThemeController.applyTheme('system');
      }
    });

    // NOTE: Select theme toggle buttons in the DOM via the data-theme-btn attribute.
    const buttons = document.querySelectorAll<HTMLElement>('[data-theme-btn]');
    buttons.forEach((btn) => {
      btn.addEventListener('click', () => {
        const theme = btn.getAttribute('data-theme-btn');
        if (theme) {
          cmsnetThemeController.setTheme(theme);
        }
      });
    });
  },

  /**
   * Persists and applies the theme selected by the user.
   *
   * @param {string} theme - The identifier of the selected theme.
   * @returns {void}
   */
  setTheme: (theme: string): void => {
    if (theme === 'dark') theme = 'cmsnet-dark';
    if (theme === 'light') theme = 'cmsnet-light';
    if (theme === 'midnight') theme = 'cmsnet-midnight';

    cmsnetThemeController.currentTheme = theme;
    localStorage.setItem('cmsnet_theme', theme);
    cmsnetThemeController.applyTheme(theme);
  },

  /**
   * Applies the corresponding CSS classes and accessibility attributes for the theme to the document.
   *
   * @param {string} theme - The identifier of the theme to apply.
   * @returns {void}
   */
  applyTheme: (theme: string): void => {
    if (theme === 'dark') theme = 'cmsnet-dark';
    if (theme === 'light') theme = 'cmsnet-light';
    if (theme === 'midnight') theme = 'cmsnet-midnight';

    const isSystemDark = cmsnetThemeController.mediaQuery.matches;
    const isDark =
      theme === 'cmsnet-dark' ||
      theme === 'cmsnet-midnight' ||
      (theme === 'system' && isSystemDark);

    document.documentElement.classList.remove(
      'cmsnet-dark',
      'cmsnet-light',
      'cmsnet-midnight',
      'dark'
    );

    if (theme === 'cmsnet-midnight') {
      document.documentElement.classList.add('cmsnet-midnight');
    } else if (isDark) {
      document.documentElement.classList.add('cmsnet-dark');
    } else {
      document.documentElement.classList.add('cmsnet-light');
    }

    const activeDataTheme = theme === 'system' ? (isDark ? 'cmsnet-dark' : 'cmsnet-light') : theme;
    document.documentElement.setAttribute('data-theme', activeDataTheme);

    // NOTE: Accessible pressed state controls the active theme label and check mark in Blade.
    document.querySelectorAll<HTMLElement>('[data-theme-btn]').forEach((btn) => {
      const btnTheme = btn.getAttribute('data-theme-btn');
      const isSelected =
        btnTheme === theme ||
        (theme === 'cmsnet-dark' && btnTheme === 'dark') ||
        (theme === 'cmsnet-light' && btnTheme === 'light') ||
        (theme === 'cmsnet-midnight' && btnTheme === 'midnight');

      btn.setAttribute('aria-pressed', String(isSelected));
    });
  },
};

/* LIFECYCLE */

/**
 * Bootstraps the admin frontend modules once the DOM is ready.
 *
 * @returns {void}
 */
const bootstrapAdmin = (): void => {
  initVueApp();

  // NOTE: Theme controller and sidebar listeners only run on dashboard views.
  if (document.body?.hasAttribute('data-admin-dashboard')) {
    cmsnetThemeController.init();
    init();
  }
};

if (typeof document !== 'undefined') {
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootstrapAdmin);
  } else {
    bootstrapAdmin();
  }
}

export default cmsnetThemeController;
