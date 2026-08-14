import './bootstrap';
import { posApp } from './posApp';

import Alpine from 'alpinejs';

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

const normalizeRole = (role) => String(role ?? '').trim().toLowerCase();

const getSessionExpiredRedirectUrl = () => {
  const role = normalizeRole(window.USER_ROLE);

  if (role === 'serveuse') {
    return '/serveuse-login';
  }

  return '/login';
};

const originalFetch = window.fetch.bind(window);

window.__isHandlingSessionExpiry = false;

window.fetch = async (...args) => {
  const response = await originalFetch(...args);

  if (response?.status === 419 && !window.__isHandlingSessionExpiry) {
    window.__isHandlingSessionExpiry = true;

    try {
      await originalFetch('/logout', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
      });
    } catch (error) {
      console.warn('Erreur lors de la déconnexion après 419:', error);
    }

    const redirectUrl = getSessionExpiredRedirectUrl();
    window.location.replace(redirectUrl);

    return Promise.reject(new Error('Session expirée. Redirection vers la reconnexion.'));
  }

  return response;
};

window.Alpine = Alpine;
Alpine.data('posApp', posApp);

Alpine.start();
