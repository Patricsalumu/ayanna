import './bootstrap';
import { posApp } from './posApp';

import Alpine from 'alpinejs';

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

const normalizeRole = (role) => String(role ?? '').trim().toLowerCase();

const getSessionExpiredRedirectUrl = () => {
  const pathname = window.location.pathname.toLowerCase();
  const role = normalizeRole(window.USER_ROLE);

  if (pathname.endsWith('/serveuse-login') || role === 'serveuse') {
    return '/serveuse-login';
  }

  return '/login';
};

const isLoginPage = () => {
  const pathname = window.location.pathname.toLowerCase();
  return pathname === '/login' || pathname.endsWith('/serveuse-login');
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
          'X-XSRF-TOKEN': document.cookie
            .split('; ')
            .find((row) => row.startsWith('XSRF-TOKEN='))
            ?.split('=')[1] || '',
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
