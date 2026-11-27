/**
 * UNGOLMAS WebApp Module - Frontend Entry Point
 * Controlador interactivo de temas para la WebApp pública
 * (Por defecto siempre activo: ungolmas-dark)
 */

export interface WebAppThemeController {
  currentTheme: string;
  init(): void;
  toggleTheme(): void;
  setTheme(theme: string): void;
  applyTheme(theme: string): void;
}

export const ugmThemeController: WebAppThemeController = {
  // En WebApp, por defecto es SIEMPRE ungolmas-dark
  currentTheme: localStorage.getItem('ugm_theme') || 'ungolmas-dark',

  init() {
    this.applyTheme(this.currentTheme);

    // Botones de toggle rápido (Sol / Luna)
    const toggles = document.querySelectorAll<HTMLElement>('[data-ugm-theme-toggle]');
    toggles.forEach((btn) => {
      btn.addEventListener('click', () => {
        this.toggleTheme();
      });
    });

    // Botones específicos de tema
    const themeButtons = document.querySelectorAll<HTMLElement>('[data-ugm-theme]');
    themeButtons.forEach((btn) => {
      btn.addEventListener('click', () => {
        const theme = btn.getAttribute('data-ugm-theme');
        if (theme) {
          this.setTheme(theme);
        }
      });
    });
  },

  toggleTheme() {
    const nextTheme = this.currentTheme === 'ungolmas-dark' ? 'ungolmas-light' : 'ungolmas-dark';
    this.setTheme(nextTheme);
  },

  setTheme(theme: string) {
    if (theme !== 'ungolmas-dark' && theme !== 'ungolmas-light') {
      theme = 'ungolmas-dark';
    }

    this.currentTheme = theme;
    localStorage.setItem('ugm_theme', theme);
    this.applyTheme(theme);
  },

  applyTheme(theme: string) {
    document.documentElement.classList.remove('ungolmas-dark', 'ungolmas-light', 'dark');

    if (theme === 'ungolmas-light') {
      document.documentElement.classList.add('ungolmas-light');
      document.documentElement.setAttribute('data-theme', 'ungolmas-light');
    } else {
      document.documentElement.classList.add('ungolmas-dark', 'dark');
      document.documentElement.setAttribute('data-theme', 'ungolmas-dark');
    }

    // Actualiza textos o iconos en toggles si existen
    document.querySelectorAll<HTMLElement>('[data-ugm-theme-label]').forEach((el) => {
      el.textContent = theme === 'ungolmas-dark' ? 'Modo Oscuro' : 'Modo Claro';
    });
  },
};

// Auto inicializar al cargar el DOM
if (typeof document !== 'undefined') {
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => ugmThemeController.init());
  } else {
    ugmThemeController.init();
  }
}

export default ugmThemeController;
