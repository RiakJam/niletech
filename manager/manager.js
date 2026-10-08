const toggle = document.querySelector('.manager-menu');
const sidebar = document.querySelector('.manager-sidebar');
toggle?.addEventListener('click', () => {
  const open = sidebar.classList.toggle('is-open');
  toggle.setAttribute('aria-expanded', String(open));
  toggle.setAttribute('aria-label', open ? 'Close manager navigation' : 'Open manager navigation');
});
document.addEventListener('keydown', (event) => {
  if (event.key === 'Escape') {
    sidebar?.classList.remove('is-open');
    toggle?.setAttribute('aria-expanded', 'false');
  }
});
