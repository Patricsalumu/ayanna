const loadingForms = new WeakSet();

const setButtonLoading = (button) => {
  if (button.dataset.loadingActive === 'true') {
    return;
  }

  button.dataset.loadingActive = 'true';
  button.setAttribute('aria-busy', 'true');
  button.setAttribute('aria-disabled', 'true');
  button.disabled = true;
  button.classList.add('is-loading');
};

const setLinkLoading = (link) => {
  if (link.dataset.loadingActive === 'true') {
    return;
  }

  link.dataset.loadingActive = 'true';
  link.setAttribute('aria-busy', 'true');
  link.classList.add('is-loading');
};

const addLoadingStyles = () => {
  if (document.getElementById('button-feedback-styles')) {
    return;
  }

  const style = document.createElement('style');
  style.id = 'button-feedback-styles';
  style.textContent = `
    button:not(:disabled):active, a:not(.is-loading):active { transform: scale(.98); filter: brightness(.94); }
    button, a { transition: transform .12s ease, filter .12s ease, opacity .12s ease; }
    .is-loading { position: relative; pointer-events: none; opacity: .78; }
    .is-loading::after {
      content: '';
      display: inline-block;
      width: 1em;
      height: 1em;
      margin-right: .45em;
      margin-left: .45em;
      border: 2px solid currentColor;
      border-right-color: transparent;
      border-radius: 9999px;
      vertical-align: -.15em;
      animation: button-feedback-spin .65s linear infinite;
    }
    @keyframes button-feedback-spin { to { transform: rotate(360deg); } }
  `;
  document.head.appendChild(style);
};

const markFormAsLoading = (form) => {
  if (loadingForms.has(form)) {
    return;
  }

  loadingForms.add(form);
  form.setAttribute('aria-busy', 'true');

  form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach((button) => {
    if (button instanceof HTMLInputElement) {
      button.dataset.loadingValue = button.value;
      button.value = button.dataset.loadingText || 'Chargement...';
      button.disabled = true;
      button.setAttribute('aria-busy', 'true');
      return;
    }

    setButtonLoading(button);
  });
};

export const registerButtonFeedback = () => {
  addLoadingStyles();

  document.addEventListener('submit', (event) => {
    if (event.target instanceof HTMLFormElement && !event.target.matches('[data-no-loading]')) {
      markFormAsLoading(event.target);
    }
  }, true);

  document.addEventListener('click', (event) => {
    if (!(event.target instanceof Element) || event.defaultPrevented) {
      return;
    }

    const link = event.target.closest('a[href]');
    if (!link || link.matches('[data-no-loading], [href="#"], [href^="javascript:"], [target="_blank"]')) {
      return;
    }

    if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || link.closest('form')) {
      return;
    }

    setLinkLoading(link);
  }, true);
};