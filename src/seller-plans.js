(() => {
  const status = document.querySelector('.seller-plans-checkout-status');
  const showStatus = (message, isError = false) => {
    if (!status) return;
    status.textContent = message;
    status.hidden = false;
    status.classList.toggle('is-error', isError);
  };

  document.querySelectorAll('[data-paystack-checkout]').forEach((form) => {
    form.addEventListener('submit', async (event) => {
      // Keep the hosted checkout as a usable fallback when Paystack Inline cannot load.
      if (typeof window.PaystackPop !== 'function') return;
      event.preventDefault();
      const button = form.querySelector('button[type="submit"]');
      if (button?.disabled) return;
      button.disabled = true;
      showStatus('Opening secure Paystack checkout…');

      try {
        const response = await fetch(form.getAttribute('action') || window.location.href, {
          method: 'POST',
          body: new FormData(form),
          credentials: 'same-origin',
          headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        const contentType = response.headers.get('content-type') || '';
        if (!contentType.includes('application/json')) {
          throw new Error(response.redirected || response.url.includes('signin')
            ? 'Your session expired. Please sign in and try again.'
            : 'Checkout returned an unexpected response. Refresh the page and try again.');
        }
        const data = await response.json();
        if (!response.ok || !data.access_code || !data.reference) {
          throw new Error(data.error || 'Checkout could not start. Please try again.');
        }
        const popup = new window.PaystackPop();
        popup.resumeTransaction(data.access_code, {
          onSuccess: () => {
            showStatus('Confirming your payment…');
            window.location.assign('seller-plans?reference=' + encodeURIComponent(data.reference));
          },
          onCancel: () => {
            button.disabled = false;
            showStatus('Payment window closed. Choose a plan when you are ready.');
          },
          onError: () => {
            // The transaction is already initialized, so its hosted checkout is safe to use.
            window.location.assign(data.authorization_url);
          },
        });
      } catch (error) {
        button.disabled = false;
        showStatus(error instanceof Error ? error.message : 'Checkout could not start. Please try again.', true);
      }
    });
  });
})();
