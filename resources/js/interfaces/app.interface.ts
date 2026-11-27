/**
 * Global application and UI controller interfaces for UNGOLMAS Core.
 */

/**
 * Contract for the interactive CMSNET theme controller (Light, Dark, Midnight, and System).
 */
export interface ThemeController {
  currentTheme: string;
  mediaQuery: MediaQueryList;
  init: () => void;
  setTheme: (theme: string) => void;
  applyTheme: (theme: string) => void;
}

/**
 * Typed DOM element references for the admin dashboard sidebar layout and preview panels.
 */
export interface AdminDashboardElements {
  sidebar: HTMLElement;
  mobileButton: HTMLButtonElement | null;
  closeButton: HTMLButtonElement | null;
  backdrop: HTMLButtonElement | null;
  collapseButton: HTMLButtonElement | null;
  profile: HTMLDetailsElement | null;
  desktop: MediaQueryList;
  menus: NodeListOf<HTMLDetailsElement>;
  overview: HTMLElement | null;
  preview: HTMLElement | null;
  previewTitle: HTMLElement | null;
  previewDescription: HTMLElement | null;
  demoForms: NodeListOf<HTMLFormElement> | undefined;
}
