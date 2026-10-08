const main = document.querySelector('#main-content');
const breadcrumb = document.querySelector('.workspace-breadcrumb strong');
const navigation = document.querySelector('.workspace-nav');
const sidebar = document.querySelector('.workspace-sidebar');
const menuToggle = document.querySelector('[data-dashboard-menu]');
const topbar = document.querySelector('.workspace-topbar');
let pendingRequest;
let loadingTimer;
let renderedUrl = location.href;

function closeDashboardMenu(returnFocus = false) {
  sidebar?.classList.remove('is-menu-open');
  menuToggle?.setAttribute('aria-expanded', 'false');
  menuToggle?.setAttribute('aria-label', 'Open dashboard menu');
  if (returnFocus) menuToggle?.focus();
}

menuToggle?.addEventListener('click', () => {
  const expanded = menuToggle.getAttribute('aria-expanded') === 'true';
  sidebar?.classList.toggle('is-menu-open', !expanded);
  menuToggle.setAttribute('aria-expanded', String(!expanded));
  menuToggle.setAttribute('aria-label', expanded ? 'Open dashboard menu' : 'Close dashboard menu');
});
navigation?.addEventListener('click', event => { if (event.target.closest('a')) closeDashboardMenu(); });
document.addEventListener('click', event => { if (!sidebar?.contains(event.target)) closeDashboardMenu(); });
document.addEventListener('keydown', event => { if (event.key === 'Escape' && sidebar?.classList.contains('is-menu-open')) closeDashboardMenu(true); });
window.matchMedia('(min-width: 851px)').addEventListener('change', event => { if (event.matches) closeDashboardMenu(); });

function dashboardUrl(url) {
  return url.origin === location.origin && url.pathname === location.pathname;
}

function showLoading() {
  window.clearTimeout(loadingTimer);
  loadingTimer = window.setTimeout(() => {
    topbar.classList.add('is-loading');
    topbar.setAttribute('aria-busy', 'true');
  }, 180);
}

function hideLoading() {
  window.clearTimeout(loadingTimer);
  topbar.classList.remove('is-loading');
  topbar.removeAttribute('aria-busy');
}

function openModal(id) {
  const dialog = document.getElementById(id);
  if (dialog instanceof HTMLDialogElement && !dialog.open) dialog.showModal();
}

function openHashModal(url) {
  const id = decodeURIComponent(url.hash.slice(1));
  if (id && document.getElementById(id) instanceof HTMLDialogElement) openModal(id);
}

function scrollToDestination(url) {
  let target;
  try {
    target = url.hash && document.getElementById(decodeURIComponent(url.hash.slice(1)));
  } catch (_) {
    target = null;
  }
  const root = document.documentElement;
  const previousBehavior = root.style.scrollBehavior;
  root.style.scrollBehavior = 'auto';
  if (target instanceof HTMLDialogElement) openModal(target.id);
  else if (target) target.scrollIntoView({ behavior: 'auto' });
  else window.scrollTo({ top: 0, behavior: 'auto' });
  requestAnimationFrame(() => { root.style.scrollBehavior = previousBehavior; });
}

async function navigate(url, pushHistory = true) {
  pendingRequest?.abort();
  const request = new AbortController();
  pendingRequest = request;
  showLoading();

  try {
    const response = await fetch(url.href, {
      signal: request.signal,
      headers: { 'X-Requested-With': 'fetch' }
    });
    if (response.redirected && response.url !== url.href) { location.assign(response.url); return; }
    if (!response.ok || !response.headers.get('content-type')?.includes('text/html')) throw new Error('Dashboard unavailable');
    const nextPage = new DOMParser().parseFromString(await response.text(), 'text/html');
    const nextMain = nextPage.querySelector('#main-content');
    const nextBreadcrumb = nextPage.querySelector('.workspace-breadcrumb strong');
    const nextNavigation = nextPage.querySelector('.workspace-nav');
    if (!nextMain || !nextBreadcrumb || !nextNavigation) throw new Error('Dashboard content unavailable');
    if (request.signal.aborted) return;

    main.replaceChildren(...Array.from(nextMain.childNodes));
    main.dataset.setupModal = nextMain.dataset.setupModal || '';
    breadcrumb.textContent = nextBreadcrumb.textContent;
    navigation.innerHTML = nextNavigation.innerHTML;
    document.title = nextPage.title;
    if (pushHistory) history.pushState({}, '', url.href);
    renderedUrl = url.href;
    scrollToDestination(url);
    const error = main.querySelector('.workspace-modal .workspace-error, .workspace-modal .workspace-form-error');
    if (error) openModal(error.closest('dialog').id);
    else if (main.dataset.setupModal) openModal(main.dataset.setupModal);
  } catch (error) {
    if (error.name !== 'AbortError') location.assign(url.href);
  } finally {
    if (pendingRequest === request) {
      pendingRequest = undefined;
      hideLoading();
    }
  }
}

document.addEventListener('click', event => {
  const pageChoice = event.target.closest('[data-entry-page]');
  if (pageChoice) {
    const pageSelect = document.querySelector('#entry-modal select[name=page_id]');
    if (pageSelect) pageSelect.value = pageChoice.dataset.entryPage;
    openModal('entry-modal');
    return;
  }
  const opener = event.target.closest('[data-open-modal]');
  if (opener) { openModal(opener.dataset.openModal); return; }
  const closer = event.target.closest('[data-close-modal]');
  if (closer) { closer.closest('dialog')?.close(); return; }
  if (event.target instanceof HTMLDialogElement) {
    const bounds = event.target.getBoundingClientRect();
    if (event.clientX < bounds.left || event.clientX > bounds.right || event.clientY < bounds.top || event.clientY > bounds.bottom) event.target.close();
    return;
  }
  const link = event.target.closest('a[href]');
  if (!link || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || link.target || link.hasAttribute('download')) return;
  const url = new URL(link.href);
  if (!dashboardUrl(url)) return;
  if (url.pathname === location.pathname && url.search === location.search) {
    if (url.hash && document.getElementById(decodeURIComponent(url.hash.slice(1))) instanceof HTMLDialogElement) {
      event.preventDefault(); history.pushState({}, '', url.href); openHashModal(url);
    }
    return;
  }
  event.preventDefault();
  navigate(url);
});

window.addEventListener('popstate', () => {
  const url = new URL(location.href);
  if (!dashboardUrl(url)) return;
  if (url.pathname + url.search === new URL(renderedUrl).pathname + new URL(renderedUrl).search) {
    scrollToDestination(url);
    return;
  }
  navigate(url, false);
});

openHashModal(new URL(location.href));
if (document.querySelector('.workspace-modal .workspace-error, .workspace-modal .workspace-form-error')) {
  const dialog = document.querySelector('.workspace-modal .workspace-error, .workspace-modal .workspace-form-error').closest('dialog');
  if (dialog) openModal(dialog.id);
} else if (main.dataset.setupModal) openModal(main.dataset.setupModal);
