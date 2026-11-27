/* STATE */
/** @type {ReadonlyArray<{name: string, package: 'Admin' | 'WebApp', kind: 'button' | 'input', className: string, docs: string, description: string}>} */
export const catalogItems = [
  { name: 'AdminButton', package: 'Admin', kind: 'button', className: 'admin-button', docs: 'admin-components-adminbutton', description: 'Acciones claras para el panel. Variantes Primary, Secondary y Outline en cuatro tamaños.' },
  { name: 'AdminInput', package: 'Admin', kind: 'input', className: 'admin-input', docs: 'admin-components-admininput', description: 'Campos para texto, correo, contraseña, URL y búsqueda. Incluye estados de solo lectura y deshabilitado.' },
  { name: 'Button', package: 'WebApp', kind: 'button', className: 'button', docs: 'webapp-components-button', description: 'Acciones para la web pública. Explora sus variantes, gradientes y tamaños.' },
  { name: 'Input', package: 'WebApp', kind: 'input', className: 'input', docs: 'webapp-components-input', description: 'El punto de partida de tus formularios públicos, con estados Default y Disabled.' },
];

/* UI HANDLERS */
/**
 * Filters the local documentation cards without adding a framework runtime to MDX.
 * @param {import('react').FormEvent<HTMLFormElement>} event - The catalog filter event.
 * @returns {void}
 */
export const handleCatalogFilter = (event) => {
  const form = event.currentTarget;
  const browser = form.closest('.ugm-catalog-browser');
  if (!browser) return;

  const data = new FormData(form);
  const query = String(data.get('query') ?? '').trim().toLocaleLowerCase('es');
  const selectedPackage = String(data.get('package') ?? 'all');
  let count = 0;

  browser.querySelectorAll('[data-catalog-name]').forEach((card) => {
    if (!(card instanceof HTMLElement)) return;
    const matchesName = (card.dataset.catalogName ?? '').toLocaleLowerCase('es').includes(query);
    const matchesPackage = selectedPackage === 'all' || card.dataset.catalogPackage === selectedPackage;
    card.hidden = !matchesName || !matchesPackage;
    if (!card.hidden) count += 1;
  });

  const status = browser.querySelector('[data-catalog-count]');
  const empty = browser.querySelector('[data-catalog-empty]');
  if (status) status.textContent = `${count} ${count === 1 ? 'componente' : 'componentes'}`;
  if (empty instanceof HTMLElement) empty.hidden = count !== 0;
};
