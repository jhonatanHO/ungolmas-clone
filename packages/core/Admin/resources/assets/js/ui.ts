/**
 * UI interaction handlers and event initialization for the CMSNET admin dashboard.
 */

import type { AdminDashboardElements } from '@/js/interfaces/app.interface';

/* CONSTANTS */

const DEMO_PAGE_DESCRIPTIONS: Readonly<Record<string, string>> = {
  Usuario: 'Gestión de usuarios, perfiles y permisos de acceso a la plataforma.',
  Configuraciones: 'Configuración general y preferencias de la plataforma.',
  Juego: 'Administración de juegos, sus opciones y contenido disponible.',
  Integraciones: 'Conexión con servicios externos y configuración de sus integraciones.',
  Documentación: 'Guías de uso y documentación para integrar y utilizar la plataforma.',
  'Account settings': 'Preferencias del perfil y opciones de seguridad de la cuenta.',
};

/* UI HANDLERS */

/**
 * Updates and persists the sidebar compact mode without affecting the mobile drawer.
 *
 * @param {AdminDashboardElements} elements - DOM element references for the admin dashboard.
 * @param {boolean} collapsed - Whether the sidebar should be collapsed.
 * @returns {void}
 */
const setCollapsed = (elements: AdminDashboardElements, collapsed: boolean): void => {
  const { sidebar, collapseButton, menus } = elements;
  sidebar.dataset.collapsed = String(collapsed);
  collapseButton?.setAttribute('aria-expanded', String(!collapsed));

  const label = collapsed ? 'Expandir lateral' : 'Contraer lateral';
  collapseButton?.setAttribute('aria-label', label);
  collapseButton?.setAttribute('title', label);

  menus.forEach((menu) => {
    menu.open = false;
  });

  localStorage.setItem('cmsnet_sidebar_collapsed', String(collapsed));
};

/**
 * Opens or closes the mobile navigation drawer without affecting desktop state.
 *
 * @param {AdminDashboardElements} elements - DOM element references for the admin dashboard.
 * @param {boolean} open - Whether the mobile drawer should be displayed.
 * @returns {void}
 */
const setMobileOpen = (elements: AdminDashboardElements, open: boolean): void => {
  const { sidebar, backdrop, mobileButton, closeButton, desktop } = elements;
  sidebar.classList.toggle('-translate-x-full', !open);
  backdrop?.classList.toggle('hidden', !open);
  mobileButton?.setAttribute('aria-expanded', String(open));
  document.body.classList.toggle('overflow-hidden', open);
  sidebar.inert = !desktop.matches && !open;

  if (open) {
    closeButton?.focus();
  }
};

/**
 * Binds local validation and reset events to the dashboard demo forms.
 *
 * @param {NodeListOf<HTMLFormElement> | undefined} demoForms - List of demo form elements in the preview panel.
 * @returns {void}
 */
const bindDemoForms = (demoForms: NodeListOf<HTMLFormElement> | undefined): void => {
  demoForms?.forEach((form) => {
    const status = form.querySelector<HTMLElement>('[data-demo-form-status]');

    // NOTE: Validates local fields only; never submits or persists data.
    const previewForm = (): void => {
      if (status) status.textContent = '';
      if (!form.reportValidity()) return;
      if (status) status.textContent = 'Vista previa válida. No se guardaron ni enviaron datos.';
    };

    form.addEventListener('submit', (event: SubmitEvent) => {
      event.preventDefault();
      previewForm();
    });

    form.querySelector('[data-demo-form-preview]')?.addEventListener('click', previewForm);

    const clearStatus = (): void => {
      if (status) status.textContent = '';
    };

    form.addEventListener('reset', clearStatus);
    form.addEventListener('input', clearStatus);
    form.addEventListener('change', clearStatus);
  });
};

/**
 * Displays static preview content for the selected section without navigation or business operations.
 *
 * @param {AdminDashboardElements} elements - DOM element references for the admin dashboard.
 * @param {string} page - Name of the demo page or section to display.
 * @returns {void}
 */
const showDemoPage = (elements: AdminDashboardElements, page: string): void => {
  const {
    sidebar,
    overview,
    preview,
    previewTitle,
    previewDescription,
    demoForms,
    menus,
    desktop,
  } = elements;

  if (!overview || !preview || !previewTitle || !previewDescription) return;

  const isOverview = page === 'Overview';
  const description = DEMO_PAGE_DESCRIPTIONS[page];
  if (!isOverview && !description) return;

  previewTitle.textContent = page;
  previewDescription.textContent = description ?? '';
  overview.classList.toggle('hidden', !isOverview);
  preview.classList.toggle('hidden', isOverview);

  demoForms?.forEach((form) => {
    form.classList.toggle('hidden', isOverview || form.dataset.demoForm !== page);
  });

  sidebar.querySelectorAll<HTMLElement>('nav [data-demo-page]').forEach((item) => {
    if (item.dataset.demoPage === page) {
      item.setAttribute('aria-current', 'page');
    } else {
      item.removeAttribute('aria-current');
    }
  });

  menus.forEach((menu) => {
    menu.open = false;
  });

  if (!desktop.matches) {
    setMobileOpen(elements, false);
  }

  if (isOverview) {
    const heading = overview.querySelector('h1');
    heading?.setAttribute('tabindex', '-1');
    heading?.focus();
  } else {
    previewTitle.focus();
  }
};

/**
 * Registers global outside-click and accessible keyboard navigation (`Escape` and `Tab`) listeners.
 *
 * @param {AdminDashboardElements} elements - DOM element references for the admin dashboard.
 * @returns {void}
 */
const bindDocumentEvents = (elements: AdminDashboardElements): void => {
  const { sidebar, menus, mobileButton, desktop } = elements;

  document.addEventListener('click', (event: MouseEvent) => {
    menus.forEach((menu) => {
      if (event.target instanceof Node && !menu.contains(event.target)) {
        menu.open = false;
      }
    });
  });

  document.addEventListener('keydown', (event: KeyboardEvent) => {
    if (event.key === 'Escape') {
      menus.forEach((menu) => {
        menu.open = false;
      });
      if (mobileButton?.getAttribute('aria-expanded') === 'true') {
        setMobileOpen(elements, false);
        mobileButton.focus();
      }
    }

    if (
      !desktop.matches &&
      mobileButton?.getAttribute('aria-expanded') === 'true' &&
      event.key === 'Tab'
    ) {
      const focusable = Array.from(
        sidebar.querySelectorAll<HTMLElement>('a[href], button, summary')
      ).filter((element) => element.getClientRects().length > 0);
      const first = focusable[0];
      const last = focusable[focusable.length - 1];

      if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last?.focus();
      } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first?.focus();
      }
    }
  });
};

/* LIFECYCLE */

/**
 * Initializes the admin dashboard UI state and binds all interaction listeners.
 *
 * @returns {void}
 */
export const init = (): void => {
  const sidebar = document.querySelector<HTMLElement>('#sidebar');
  if (!sidebar) return;

  const preview = document.querySelector<HTMLElement>('#dashboard-preview');
  const elements: AdminDashboardElements = {
    sidebar,
    mobileButton: document.querySelector<HTMLButtonElement>('#mobile-menu-btn'),
    closeButton: document.querySelector<HTMLButtonElement>('#mobile-close-btn'),
    backdrop: document.querySelector<HTMLButtonElement>('#mobile-backdrop'),
    collapseButton: document.querySelector<HTMLButtonElement>('#sidebar-toggle-btn'),
    profile: document.querySelector<HTMLDetailsElement>('#user-profile'),
    desktop: window.matchMedia('(min-width: 768px)'),
    menus: sidebar.querySelectorAll<HTMLDetailsElement>('[data-sidebar-menu]'),
    overview: document.querySelector<HTMLElement>('#dashboard-overview'),
    preview,
    previewTitle: document.querySelector<HTMLElement>('#dashboard-preview-title'),
    previewDescription: document.querySelector<HTMLElement>('#dashboard-preview-description'),
    demoForms: preview?.querySelectorAll<HTMLFormElement>('[data-demo-form]'),
  };

  setCollapsed(elements, localStorage.getItem('cmsnet_sidebar_collapsed') === 'true');
  elements.collapseButton?.addEventListener('click', () =>
    setCollapsed(elements, sidebar.dataset.collapsed !== 'true')
  );

  setMobileOpen(elements, false);
  elements.mobileButton?.addEventListener('click', () => setMobileOpen(elements, true));
  [elements.closeButton, elements.backdrop].forEach((button) =>
    button?.addEventListener('click', () => {
      setMobileOpen(elements, false);
      elements.mobileButton?.focus();
    })
  );
  elements.desktop.addEventListener('change', () => setMobileOpen(elements, false));

  bindDemoForms(elements.demoForms);

  sidebar.querySelectorAll<HTMLElement>('[data-demo-page]').forEach((item) => {
    item.addEventListener('click', (event: MouseEvent) => {
      if (!elements.overview || !elements.preview) return;
      const page = item.dataset.demoPage;
      if (!page) return;
      event.preventDefault();
      showDemoPage(elements, page);
    });
  });

  elements.profile?.querySelector('summary')?.addEventListener('click', () => {
    if (elements.desktop.matches && sidebar.dataset.collapsed === 'true') {
      setCollapsed(elements, false);
    }
  });

  bindDocumentEvents(elements);
};
