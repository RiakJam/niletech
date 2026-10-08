const endpoint = document.body.dataset.analyticsEndpoint;
function track(event, id) {
  if (!endpoint || !id) return;
  const data = new URLSearchParams({event, product_id: id});
  if (event === 'click' && navigator.sendBeacon) navigator.sendBeacon(endpoint, data);
  else fetch(endpoint, {method:'POST', body:data, credentials:'same-origin', keepalive:true}).catch(() => {});
}
const cards = document.querySelectorAll('.marketplace-product[data-product-id]');
const categoryList = document.querySelector('.marketplace-category-list');
const categoryUp = document.querySelector('[data-scroll-categories="-1"]');
const categoryDown = document.querySelector('[data-scroll-categories="1"]');
function updateCategoryScrollControls() {
  if (!categoryList || !categoryUp || !categoryDown) return;
  const maxScroll = categoryList.scrollHeight - categoryList.clientHeight;
  categoryUp.hidden = maxScroll <= 1 || categoryList.scrollTop <= 1;
  categoryDown.hidden = maxScroll <= 1 || categoryList.scrollTop >= maxScroll - 1;
}
if (categoryList) {
  categoryList.addEventListener('scroll', updateCategoryScrollControls, {passive: true});
  window.addEventListener('resize', updateCategoryScrollControls);
  requestAnimationFrame(updateCategoryScrollControls);
}
const categoryDialog = document.getElementById('marketplace-category-dialog');
categoryDialog?.addEventListener('click', event => {
  if (event.target !== categoryDialog) return;
  const bounds = categoryDialog.getBoundingClientRect();
  if (event.clientX < bounds.left || event.clientX > bounds.right || event.clientY < bounds.top || event.clientY > bounds.bottom) categoryDialog.close();
});
const galleryPhotos = Array.from(document.querySelectorAll('[data-gallery-index]'));
const galleryPhotoUrls = JSON.parse(document.querySelector('[data-gallery-images]')?.dataset.galleryImages || '[]');
let galleryIndex = 0;
function sizeGalleryWatermark() {
  const stage = document.querySelector('.storefront-detail-image');
  const watermark = stage?.querySelector('.storefront-photo-watermark');
  const photo = stage?.querySelector('#product-gallery-main, .demo-product-photo');
  if (!watermark || !photo) return;
  let visibleWidth = photo.getBoundingClientRect().width;
  if (photo instanceof HTMLImageElement && photo.naturalWidth && photo.naturalHeight) {
    const bounds = photo.getBoundingClientRect();
    visibleWidth = Math.min(bounds.width, bounds.height * photo.naturalWidth / photo.naturalHeight);
  }
  watermark.style.maxWidth = `${Math.floor(visibleWidth * 0.86)}px`;
  watermark.style.fontSize = `${Math.min(24, Math.max(10, visibleWidth / 20))}px`;
}
document.getElementById('product-gallery-main')?.addEventListener('load', sizeGalleryWatermark);
window.addEventListener('resize', sizeGalleryWatermark);
sizeGalleryWatermark();
function showGalleryPhoto(index) {
  const mainImage = document.getElementById('product-gallery-main');
  if (!mainImage || !galleryPhotoUrls.length) return;
  const selected = (index + galleryPhotoUrls.length) % galleryPhotoUrls.length;
  galleryIndex = selected;
  mainImage.src = galleryPhotoUrls[selected];
  mainImage.alt = `${mainImage.dataset.productTitle}, photo ${selected + 1} of ${galleryPhotoUrls.length}`;
  galleryPhotos.forEach((button, photoIndex) => {
    const active = photoIndex === selected || (selected >= galleryPhotos.length && photoIndex === galleryPhotos.length - 1);
    button.classList.toggle('is-active', active);
    button.setAttribute('aria-pressed', String(active));
  });
  const count = document.querySelector('[data-gallery-count]');
  if (count) count.textContent = `${selected + 1} / ${galleryPhotoUrls.length}`;
}
if ('IntersectionObserver' in window) {
  const seen = new Set();
  const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const id = entry.target.dataset.productId;
        if (!seen.has(id)) { seen.add(id); track('impression', id); }
        observer.unobserve(entry.target);
      }
    });
  }, {threshold: 0.5});
  cards.forEach(card => observer.observe(card));
}
document.addEventListener('click', event => {
  const categoryButton = event.target.closest('[data-open-category]');
  if (categoryButton && categoryDialog) {
    categoryDialog.querySelectorAll('[data-category-panel]').forEach(panel => {
      panel.hidden = panel.dataset.categoryPanel !== categoryButton.dataset.openCategory;
    });
    const title = categoryDialog.querySelector('[data-category-dialog-title]');
    if (title) title.textContent = categoryButton.querySelector('.marketplace-category-label')?.textContent || categoryButton.textContent.trim();
    categoryDialog.showModal();
    return;
  }
  if (event.target.closest('[data-close-category]')) { categoryDialog?.close(); return; }
  const categoryScrollButton = event.target.closest('[data-scroll-categories]');
  if (categoryScrollButton && categoryList) {
    categoryList.scrollBy({top: Number(categoryScrollButton.dataset.scrollCategories) * Math.max(150, categoryList.clientHeight * 0.7), behavior: 'smooth'});
    return;
  }
  const link = event.target.closest('[data-track-click]');
  if (link) track('click', link.closest('[data-product-id]')?.dataset.productId);
  if (event.target.closest('[data-open-order-message]')) document.getElementById('order-message-modal')?.showModal();
  if (event.target.closest('[data-close-order-message]')) document.getElementById('order-message-modal')?.close();
  if (event.target.closest('[data-open-follow]')) document.getElementById('follow-modal')?.showModal();
  if (event.target.closest('[data-close-follow]')) document.getElementById('follow-modal')?.close();
  if (event.target.closest('[data-open-report]')) document.getElementById('report-modal')?.showModal();
  if (event.target.closest('[data-close-report]')) document.getElementById('report-modal')?.close();
  const photo = event.target.closest('[data-gallery-index]');
  if (photo) showGalleryPhoto(Number(photo.dataset.galleryIndex));
  const galleryArrow = event.target.closest('[data-gallery-step]');
  if (galleryArrow) showGalleryPhoto(galleryIndex + Number(galleryArrow.dataset.galleryStep));
  const contactToggle = event.target.closest('[data-show-contact]');
  if (contactToggle) {
    const details = document.getElementById('storefront-contact-details');
    if (details) {
      details.hidden = !details.hidden;
      contactToggle.setAttribute('aria-expanded', String(!details.hidden));
      contactToggle.textContent = details.hidden ? 'Show contact' : 'Hide contact';
    }
  }
  const copyButton = event.target.closest('[data-copy-contact]');
  if (copyButton) {
    const copyNumber = async () => {
      if (navigator.clipboard?.writeText) return navigator.clipboard.writeText(copyButton.dataset.copyContact);
      const field = document.createElement('textarea');
      field.value = copyButton.dataset.copyContact;
      field.style.position = 'fixed';
      field.style.opacity = '0';
      document.body.append(field);
      field.select();
      const copied = document.execCommand('copy');
      field.remove();
      if (!copied) throw new Error('Copy unavailable');
    };
    copyNumber().then(() => {
      copyButton.textContent = 'Copied';
      setTimeout(() => { copyButton.textContent = 'Copy'; }, 2000);
    }).catch(() => { copyButton.textContent = 'Select number to copy'; });
  }
});
document.querySelector('[data-follow-form]')?.addEventListener('submit', async event => {
  event.preventDefault();
  const form = event.currentTarget;
  const button = form.querySelector('button[type="submit"]');
  const feedback = form.querySelector('[data-follow-feedback]');
  button.disabled = true;
  feedback.hidden = true;
  try {
    const response = await fetch(form.action, {method: 'POST', body: new FormData(form), headers: {Accept: 'application/json'}, credentials: 'same-origin'});
    const result = await response.json();
    feedback.textContent = result.message;
    feedback.hidden = false;
    if (result.success) {
      form.reset();
      button.hidden = true;
    }
  } catch {
    feedback.textContent = 'Could not follow this store. Please try again.';
    feedback.hidden = false;
  } finally {
    button.disabled = false;
  }
});

const locationDialog = document.getElementById('marketplace-location-dialog');
const priceDialog = document.getElementById('marketplace-price-dialog');
if (priceDialog) {
  document.querySelector('[data-open-price]')?.addEventListener('click', () => priceDialog.showModal());
  priceDialog.querySelector('[data-close-price]')?.addEventListener('click', () => priceDialog.close());
  priceDialog.addEventListener('click', event => { if (event.target === priceDialog) priceDialog.close(); });
  if (priceDialog.dataset.priceError === 'true') priceDialog.showModal();
}
const locationDataNode = document.getElementById('marketplace-location-data');
if (locationDialog && locationDataNode) {
  const data = JSON.parse(locationDataNode.textContent);
  const body = locationDialog.querySelector('[data-location-body]');
  const foot = locationDialog.querySelector('[data-location-foot]');
  const searchInput = locationDialog.querySelector('[data-location-search]');
  const backButton = locationDialog.querySelector('[data-location-back]');
  const title = locationDialog.querySelector('#marketplace-location-title');
  const scrollUp = locationDialog.querySelector('[data-location-scroll="-1"]');
  const scrollDown = locationDialog.querySelector('[data-location-scroll="1"]');
  let level = data.country ? 'regions' : 'countries';
  let selectedRegion = '';
  const updateScrollCues = () => {
    const remaining = body.scrollHeight - body.clientHeight;
    scrollUp.hidden = remaining <= 1 || body.scrollTop <= 1;
    scrollDown.hidden = remaining <= 1 || body.scrollTop >= remaining - 1;
  };
  const countryNames = Object.fromEntries(data.countries.map(item => [item.code, item.name]));
  const locationHref = (country, region = '', locality = '') => {
    const url = new URL(window.location.href);
    url.searchParams.delete('country');
    url.searchParams.delete('region');
    url.searchParams.delete('locality');
    if (!data.locked) url.searchParams.set('country', country || 'all');
    if (region) url.searchParams.set('region', region);
    if (locality) url.searchParams.set('locality', locality);
    return url.pathname + url.search;
  };
  const addRow = (label, count, href, drilldown = false) => {
    const row = document.createElement(href ? 'a' : 'button');
    row.className = 'marketplace-location-row';
    if (href) row.href = href;
    else row.type = 'button';
    const name = document.createElement('span');
    name.textContent = label;
    const tally = document.createElement('span');
    tally.className = 'marketplace-location-count';
    tally.textContent = `${new Intl.NumberFormat().format(count)} ${count === 1 ? 'listing' : 'listings'}`;
    row.append(name, tally);
    if (drilldown) {
      const arrow = document.createElement('span');
      arrow.className = 'marketplace-location-arrow';
      arrow.textContent = '›';
      row.append(arrow);
    }
    body.append(row);
    return row;
  };
  const render = () => {
    body.replaceChildren();
    foot.replaceChildren();
    const term = searchInput.value.trim().toLocaleLowerCase();
    const isCountry = level === 'countries';
    const isAreas = level === 'areas';
    title.textContent = isCountry ? 'Choose a country' : isAreas ? selectedRegion : `Locations in ${countryNames[data.country] || data.country}`;
    backButton.hidden = !(isAreas || (!data.locked && !isCountry));
    searchInput.placeholder = isCountry ? 'Find a country' : isAreas ? 'Find a district or area' : 'Find state, city or district';
    if (isCountry) {
      if (!term) addRow('All African countries', data.total, locationHref(''));
      data.countries.filter(item => item.name.toLocaleLowerCase().includes(term)).forEach(item => addRow(item.name, item.count, locationHref(item.code)));
    } else if (isAreas) {
      const areas = Object.entries(data.areas[selectedRegion] || {});
      const count = data.regions[selectedRegion] || 0;
      if (!term || `all ${selectedRegion}`.toLocaleLowerCase().includes(term)) addRow(`All ${selectedRegion}`, count, locationHref(data.country, selectedRegion));
      areas.filter(([name]) => name.toLocaleLowerCase().includes(term)).forEach(([name, total]) => addRow(name, total, locationHref(data.country, selectedRegion, name)));
      if (areas.length === 0) {
        const note = document.createElement('p');
        note.className = 'marketplace-location-note';
        note.textContent = 'No areas have listings yet. Browse the whole region to see new listings as they arrive.';
        foot.append(note);
      }
    } else {
      const regionEntries = Object.entries(data.regions);
      if (!term) addRow(`All ${countryNames[data.country] || data.country}`, data.countries.find(item => item.code === data.country)?.count || 0, locationHref(data.country));
      regionEntries.filter(([name]) => name.toLocaleLowerCase().includes(term) || Object.keys(data.areas[name] || {}).some(area => area.toLocaleLowerCase().includes(term))).forEach(([name, count]) => {
        const row = addRow(name, count, '', true);
        row.addEventListener('click', () => { selectedRegion = name; level = 'areas'; searchInput.value = ''; render(); });
      });
      if (!regionEntries.length) {
        const note = document.createElement('p');
        note.className = 'marketplace-location-note';
        note.textContent = 'Places will appear as listings are added in this country.';
        foot.append(note);
      }
    }
    if (!body.childElementCount && !foot.childElementCount) {
      const note = document.createElement('p');
      note.className = 'marketplace-location-note';
      note.textContent = 'No matching locations found.';
      body.append(note);
    }
    body.scrollTop = 0;
    requestAnimationFrame(updateScrollCues);
  };
  document.querySelector('[data-open-location]')?.addEventListener('click', () => {
    level = data.country ? 'regions' : 'countries';
    selectedRegion = '';
    searchInput.value = '';
    render();
    locationDialog.showModal();
    searchInput.focus();
  });
  locationDialog.querySelector('[data-close-location]')?.addEventListener('click', () => locationDialog.close());
  locationDialog.addEventListener('click', event => { if (event.target === locationDialog) locationDialog.close(); });
  backButton.addEventListener('click', () => { level = level === 'areas' ? 'regions' : 'countries'; searchInput.value = ''; render(); });
  searchInput.addEventListener('input', render);
  body.addEventListener('scroll', updateScrollCues, {passive: true});
  window.addEventListener('resize', updateScrollCues);
  [scrollUp, scrollDown].forEach(button => button.addEventListener('click', () => {
    body.scrollBy({top: Number(button.dataset.locationScroll) * Math.max(150, body.clientHeight * 0.7), behavior: 'smooth'});
  }));
}
