let deferredInstallPrompt = null;

const createInstallButton = () => {
  if (document.querySelector('[data-pwa-install]')) {
    return document.querySelector('[data-pwa-install]');
  }

  const button = document.createElement('button');
  button.type = 'button';
  button.dataset.pwaInstall = 'true';
  button.textContent = 'Installer Ayanna';
  button.setAttribute('aria-label', 'Installer Ayanna sur cet appareil');
  button.style.cssText = [
    'position: fixed',
    'right: 1rem',
    'bottom: 1rem',
    'z-index: 9999',
    'display: none',
    'align-items: center',
    'gap: .5rem',
    'padding: .75rem 1rem',
    'border: 0',
    'border-radius: .75rem',
    'background: #3e2f24',
    'color: #fff',
    'font: 600 14px/1.2 inherit',
    'box-shadow: 0 8px 24px rgba(62, 47, 36, .25)',
    'cursor: pointer',
  ].join(';');

  button.addEventListener('click', async () => {
    if (!deferredInstallPrompt) {
      return;
    }

    deferredInstallPrompt.prompt();
    const choice = await deferredInstallPrompt.userChoice;

    if (choice.outcome === 'accepted') {
      button.remove();
    }

    deferredInstallPrompt = null;
  });

  document.body.appendChild(button);
  return button;
};

export const registerPwa = () => {
  if (!('serviceWorker' in navigator)) {
    return;
  }

  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/sw.js').catch((error) => {
      console.warn('Enregistrement de la PWA impossible:', error);
    });
  });

  window.addEventListener('beforeinstallprompt', (event) => {
    event.preventDefault();
    deferredInstallPrompt = event;

    const button = createInstallButton();
    button.style.display = 'inline-flex';
  });

  window.addEventListener('appinstalled', () => {
    deferredInstallPrompt = null;
    document.querySelector('[data-pwa-install]')?.remove();
  });
};