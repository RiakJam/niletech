document.querySelector('[data-niche-category-select]')?.addEventListener('change', (event) => {
  if (event.target.value) window.location.assign(event.target.value);
});

document.querySelectorAll('[data-niche-favorite]').forEach((button) => {
  button.addEventListener('click', () => {
    const name = 'nileteck_niche_favorites';
    const current = document.cookie.split('; ').find((part) => part.startsWith(`${name}=`));
    const saved = new Set(current ? decodeURIComponent(current.split('=').slice(1).join('=')).split(',').filter(Boolean) : []);
    const key = button.dataset.nicheFavorite;
    if (saved.has(key)) saved.delete(key);
    else saved.add(key);
    document.cookie = `${name}=${encodeURIComponent([...saved].join(','))}; Path=/; Max-Age=31536000; SameSite=Lax`;
    const isSaved = saved.has(key);
    button.classList.toggle('is-saved', isSaved);
    button.setAttribute('aria-pressed', String(isSaved));
    button.setAttribute('aria-label', `${isSaved ? 'Remove' : 'Save'} ${button.closest('.niche-result-card').querySelector('h3').textContent} ${isSaved ? 'from' : 'to'} favorites`);
    if (new URLSearchParams(window.location.search).get('favorites') === '1' && !isSaved) window.location.reload();
  });
});

const modal = document.querySelector('#niche-detail-modal');
const nicheData = document.querySelector('#niche-data');
if (modal && nicheData) {
  const niches = JSON.parse(nicheData.textContent);
  const byKey = new Map(niches.map((niche) => [niche.key, niche]));
  const relatedList = modal.querySelector('#niche-modal-related-list');
  const moreButton = modal.querySelector('#niche-modal-related-more');
  let related = [];
  let relatedLimit = 5;

  const setText = (id, value) => { modal.querySelector(`#${id}`).textContent = value; };
  const renderRelated = () => {
    relatedList.replaceChildren();
    related.slice(0, relatedLimit).forEach((niche) => {
      const button = document.createElement('button');
      button.type = 'button';
      button.className = 'niche-modal-related-item';
      button.dataset.relatedNiche = niche.key;
      const name = document.createElement('span');
      name.textContent = niche.label;
      const count = document.createElement('strong');
      count.textContent = `${niche.count} ${niche.count === 1 ? 'listing' : 'listings'}`;
      button.append(name, count);
      relatedList.append(button);
    });
    moreButton.hidden = related.length <= relatedLimit;
  };
  const showNiche = (key) => {
    const niche = byKey.get(key);
    if (!niche) return;
    const group = niches.filter((item) => item.group_key === niche.group_key);
    const groupListings = group.reduce((sum, item) => sum + item.count, 0);
    setText('niche-modal-title', niche.label);
    setText('niche-modal-group', niche.group);
    setText('niche-modal-listings', niche.count.toLocaleString());
    setText('niche-modal-listings-note', niche.count === 0 ? 'No current listings' : 'Live in this niche');
    setText('niche-modal-sellers', niche.sellers.toLocaleString());
    setText('niche-modal-group-count', groupListings.toLocaleString());
    setText('niche-modal-group-name', niche.group);
    setText('niche-modal-status', niche.sellers === 0 ? 'None' : niche.sellers >= 3 ? 'High' : 'Low');
    setText('niche-modal-status-note', niche.sellers === 0 ? 'No active sellers' : `${niche.sellers} ${niche.sellers === 1 ? 'seller' : 'sellers'} in this niche`);
    modal.querySelector('#niche-modal-view').href = `${modal.dataset.marketBase}${encodeURIComponent(key)}&country=${encodeURIComponent(modal.dataset.marketCountry)}`;
    related = group.filter((item) => item.key !== key).sort((a, b) => b.count - a.count || a.label.localeCompare(b.label));
    relatedLimit = 5;
    renderRelated();
    if (!modal.open) modal.showModal();
    modal.scrollTop = 0;
  };

  document.querySelectorAll('[data-niche-open]').forEach((button) => button.addEventListener('click', () => showNiche(button.dataset.nicheOpen)));
  relatedList.addEventListener('click', (event) => {
    const button = event.target.closest('[data-related-niche]');
    if (button) showNiche(button.dataset.relatedNiche);
  });
  moreButton.addEventListener('click', () => { relatedLimit += 5; renderRelated(); });
  modal.querySelector('[data-niche-close]').addEventListener('click', () => modal.close());
  modal.addEventListener('click', (event) => { if (event.target === modal) modal.close(); });
}
