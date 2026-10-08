document.addEventListener('click', event => {
  const button = event.target.closest('[data-toggle-password]');
  if (!button) return;
  const field = document.getElementById(button.dataset.togglePassword);
  if (!(field instanceof HTMLInputElement)) return;
  const showing = field.type === 'password';
  field.type = showing ? 'text' : 'password';
  button.textContent = showing ? 'Hide' : 'Show';
  button.setAttribute('aria-label', showing ? 'Hide password' : 'Show password');
  button.setAttribute('aria-pressed', String(showing));
});

const countrySelect = document.querySelector('[data-country-select]');
const countryDialog = document.querySelector('#auth-country-dialog');
if (countrySelect && countryDialog && typeof countryDialog.showModal === 'function') {
  const trigger = document.querySelector('[data-country-open]');
  const triggerLabel = trigger.querySelector('[data-country-label]');
  const search = countryDialog.querySelector('[data-country-search]');
  const optionsList = countryDialog.querySelector('[data-country-options]');
  const error = document.querySelector('[data-country-error]');
  const countries = Array.from(countrySelect.options).filter(option => option.value).map(option => ({code: option.value, name: option.textContent.trim()}));
  const renderCountries = () => {
    optionsList.replaceChildren();
    const term = search.value.trim().toLocaleLowerCase();
    const matches = countries.filter(country => country.name.toLocaleLowerCase().includes(term));
    for (const country of matches) {
      const option = document.createElement('button');
      option.type = 'button';
      option.className = 'auth-country-option';
      if (countrySelect.value === country.code) option.classList.add('is-selected');
      option.textContent = country.name;
      if (countrySelect.value === country.code) {
        const check = document.createElement('span');
        check.setAttribute('aria-hidden', 'true');
        check.textContent = '✓';
        option.append(check);
      }
      option.addEventListener('click', () => {
        countrySelect.value = country.code;
        countrySelect.dispatchEvent(new Event('change', {bubbles: true}));
        triggerLabel.textContent = country.name;
        error.hidden = true;
        trigger.removeAttribute('aria-invalid');
        countryDialog.close();
        trigger.focus();
      });
      optionsList.append(option);
    }
    if (!matches.length) {
      const empty = document.createElement('p');
      empty.className = 'auth-country-empty';
      empty.textContent = 'No matching country found.';
      optionsList.append(empty);
    }
  };
  countrySelect.required = false;
  countrySelect.hidden = true;
  trigger.hidden = false;
  if (countrySelect.value) triggerLabel.textContent = countrySelect.selectedOptions[0].textContent;
  trigger.addEventListener('click', () => {
    search.value = '';
    renderCountries();
    countryDialog.showModal();
    search.focus();
  });
  countryDialog.querySelector('[data-country-close]').addEventListener('click', () => countryDialog.close());
  countryDialog.addEventListener('click', event => { if (event.target === countryDialog) countryDialog.close(); });
  search.addEventListener('input', renderCountries);
  countrySelect.form.addEventListener('submit', event => {
    if (countrySelect.value) return;
    event.preventDefault();
    error.hidden = false;
    trigger.setAttribute('aria-invalid', 'true');
    trigger.click();
  });
}
