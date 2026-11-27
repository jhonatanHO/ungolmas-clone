/**
 * UNGOLMAS Admin Module - Frontend Entry Point
 * Controlador interactivo de CMSNET (Temas: Light / Dark / Midnight / System)
 */

export interface ThemeController {
  currentTheme: string;
  mediaQuery: MediaQueryList;
  init(): void;
  setTheme(theme: string): void;
  applyTheme(theme: string): void;
}

export const cmsnetThemeController: ThemeController = {
  currentTheme: (() => {
    const saved = localStorage.getItem('cmsnet_theme') || 'system';
    if (saved === 'dark') return 'cmsnet-dark';
    if (saved === 'light') return 'cmsnet-light';
    if (saved === 'midnight') return 'cmsnet-midnight';
    return saved;
  })(),

  mediaQuery: window.matchMedia('(prefers-color-scheme: dark)'),

  init() {
    this.applyTheme(this.currentTheme);

    // Escucha en tiempo real si el usuario cambia el tema del sistema operativo
    this.mediaQuery.addEventListener('change', () => {
      if (this.currentTheme === 'system') {
        this.applyTheme('system');
      }
    });

    // Selecciona los botones en el DOM mediante el atributo data-theme-btn
    const buttons = document.querySelectorAll<HTMLElement>('[data-theme-btn]');
    buttons.forEach((btn) => {
      btn.addEventListener('click', () => {
        const theme = btn.getAttribute('data-theme-btn');
        if (theme) {
          this.setTheme(theme);
        }
      });
    });
  },

  setTheme(theme: string) {
    if (theme === 'dark') theme = 'cmsnet-dark';
    if (theme === 'light') theme = 'cmsnet-light';
    if (theme === 'midnight') theme = 'cmsnet-midnight';

    this.currentTheme = theme;
    localStorage.setItem('cmsnet_theme', theme);
    this.applyTheme(theme);
  },

  applyTheme(theme: string) {
    if (theme === 'dark') theme = 'cmsnet-dark';
    if (theme === 'light') theme = 'cmsnet-light';
    if (theme === 'midnight') theme = 'cmsnet-midnight';

    const isSystemDark = this.mediaQuery.matches;
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

    const activeDataTheme =
      theme === 'system' ? (isDark ? 'cmsnet-dark' : 'cmsnet-light') : theme;
    document.documentElement.setAttribute('data-theme', activeDataTheme);

    // Actualiza estado visual activo de botones data-theme-btn
    document.querySelectorAll<HTMLElement>('[data-theme-btn]').forEach((btn) => {
      const btnTheme = btn.getAttribute('data-theme-btn');
      const isSelected =
        btnTheme === theme ||
        (theme === 'cmsnet-dark' && btnTheme === 'dark') ||
        (theme === 'cmsnet-light' && btnTheme === 'light') ||
        (theme === 'cmsnet-midnight' && btnTheme === 'midnight');

      if (isSelected) {
        btn.classList.add('bg-theme-300', 'text-theme-900', 'shadow-xs');
        btn.classList.remove('text-theme-500');
      } else {
        btn.classList.remove('bg-theme-300', 'text-theme-900', 'shadow-xs');
        btn.classList.add('text-theme-500');
      }
    });
  },
};

// Auto inicializar al cargar el DOM
if (typeof document !== 'undefined') {
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => cmsnetThemeController.init());
  } else {
    cmsnetThemeController.init();
  }
}

export default cmsnetThemeController;
