/// <reference types="vite/client" />

import.meta.glob('../images/*.svg', { eager: true, query: '?url', import: 'default' });

/**
 * Copies the canonical link while keeping feedback scoped to its share component.
 *
 * @param {MouseEvent} event - The delegated button click.
 * @returns {Promise<void>}
 */
const handleCopyLink = async (event: MouseEvent): Promise<void> => {
  const target = event.target;
  if (!(target instanceof Element)) return;

  const button = target.closest<HTMLButtonElement>('[data-news-copy]');
  const component = button?.closest<HTMLElement>('[data-news-share]');
  const feedback = component?.querySelector<HTMLElement>('[data-news-share-feedback]');
  const input = component?.querySelector<HTMLInputElement>('[data-news-share-url]');
  if (!button || !feedback || !input) return;

  button.disabled = true;
  try {
    if (!navigator.clipboard || !window.isSecureContext) throw new Error('Clipboard unavailable');
    await navigator.clipboard.writeText(input.value);
    feedback.textContent = 'Enlace copiado';
  } catch {
    input.classList.remove('hidden');
    input.focus();
    input.select();
    feedback.textContent = 'Selecciona y copia el enlace con Ctrl+C o ⌘C.';
  } finally {
    button.disabled = false;
  }
};

/**
 * Replaces a failed remote picture with the existing local SVG fallback.
 *
 * @param {Event} event - The captured image error.
 * @returns {void}
 */
const handleImageError = (event: Event): void => {
  if (!(event.target instanceof HTMLImageElement)) return;
  const container = event.target.closest<HTMLElement>('[data-news-image]');
  if (!container) return;
  container.querySelector('picture')?.remove();
  container.querySelector<HTMLElement>('[data-news-image-fallback]')?.classList.remove('hidden');
};

document.addEventListener('click', handleCopyLink);
document.addEventListener('error', handleImageError, true);
