const menuButton = document.querySelector('.menu-toggle');
const navigation = document.querySelector('.site-nav');

menuButton?.addEventListener('click', () => {
  const expanded = menuButton.getAttribute('aria-expanded') === 'true';
  menuButton.setAttribute('aria-expanded', String(!expanded));
  menuButton.setAttribute('aria-label', expanded ? 'Open menu' : 'Close menu');
  navigation.classList.toggle('is-open', !expanded);
});
navigation?.querySelectorAll('a').forEach(link => link.addEventListener('click', () => {
  navigation.classList.remove('is-open');
  menuButton.setAttribute('aria-expanded', 'false');
  menuButton.setAttribute('aria-label', 'Open menu');
}));
document.addEventListener('keydown', event => {
  if (event.key === 'Escape') {
    if (navigation?.classList.contains('is-open')) {
      navigation.classList.remove('is-open');
      menuButton?.setAttribute('aria-expanded', 'false');
      menuButton?.setAttribute('aria-label', 'Open menu');
      menuButton?.focus();
    }
  }
});

const siteBase = new URL('../', document.currentScript.src).pathname.replace(/\/$/, '');
const contactDialog = document.querySelector('#site-contact-dialog');

function openContactDialog(service = '') {
  if (!(contactDialog instanceof HTMLDialogElement)) return;
  const intro = contactDialog.querySelector('[data-contact-intro]');
  if (intro) intro.textContent = service === 'website-development'
    ? 'Tell us about the website you want to build. Choose how to reach our team.'
    : service === 'website-maintenance'
      ? 'Tell us what your website needs. Choose how to reach our team.'
      : 'Choose the easiest way to reach our team.';
  if (!contactDialog.open) contactDialog.showModal();
}

if (location.hash === '#site-contact-dialog') openContactDialog(new URL(location.href).searchParams.get('contact') || '');
document.addEventListener('click', async event => {
  const close = event.target.closest('[data-contact-close]');
  if (close) { contactDialog?.close(); return; }
  const copy = event.target.closest('[data-contact-copy]');
  if (!copy) return;
  const value = copy.dataset.contactCopy;
  const feedback = contactDialog?.querySelector('[data-contact-feedback]');
  try {
    if (navigator.clipboard?.writeText) await navigator.clipboard.writeText(value);
    else {
      const field = document.createElement('textarea');
      field.value = value;
      field.style.position = 'fixed';
      field.style.opacity = '0';
      document.body.append(field);
      field.select();
      const copied = document.execCommand('copy');
      field.remove();
      if (!copied) throw new Error('Copy failed');
    }
    if (feedback) feedback.textContent = `${copy.dataset.contactLabel} copied.`;
  } catch (_) {
    if (feedback) feedback.textContent = `Could not copy automatically. Select the ${copy.dataset.contactLabel.toLowerCase()} above to copy it.`;
  }
});
contactDialog?.addEventListener('click', event => {
  if (event.target !== contactDialog) return;
  const bounds = contactDialog.getBoundingClientRect();
  if (event.clientX < bounds.left || event.clientX > bounds.right || event.clientY < bounds.top || event.clientY > bounds.bottom) contactDialog.close();
});

document.addEventListener('click', event => {
  const link = event.target.closest('a[href]');
  if (document.body.classList.contains('auth-layout')) return;
  if (!link || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || link.target || link.hasAttribute('download')) return;

  const url = new URL(link.href);
  if (url.origin === location.origin && url.pathname === `${siteBase}/contact`) {
    event.preventDefault();
    openContactDialog(url.searchParams.get('service') || '');
    navigation?.classList.remove('is-open');
    menuButton?.setAttribute('aria-expanded', 'false');
    menuButton?.setAttribute('aria-label', 'Open menu');
    return;
  }
  // Let the browser load each page's own stylesheets and scripts.
});

const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
let revealObserver;
function observeReveals() {
  revealObserver?.disconnect();
  const elements = document.querySelectorAll('main .section-head, main .app-card, main .directory-service, main .process-grid article, main .contact-panel, main .step');
  if (reduceMotion.matches || !('IntersectionObserver' in window)) {
    elements.forEach(element => element.classList.add('is-visible'));
    return;
  }
  revealObserver = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (!entry.isIntersecting) return;
      entry.target.classList.add('is-visible');
      revealObserver.unobserve(entry.target);
    });
  }, { threshold: 0.08, rootMargin: '0px 0px -35px 0px' });
  elements.forEach(element => {
    element.classList.add('reveal');
    revealObserver.observe(element);
  });
}
observeReveals();

document.addEventListener('click', async (event) => {
  const button = event.target.closest('[data-share-url]');
  if (!button) return;
  const url = new URL(button.dataset.shareUrl, window.location.href).href;
  try {
    if (navigator.share) await navigator.share({ url });
    else { await navigator.clipboard.writeText(url); button.textContent = 'Link copied'; }
  } catch (error) {
    if (error.name !== 'AbortError') window.prompt('Copy this link:', url);
  }
});
