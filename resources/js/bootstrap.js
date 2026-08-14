import axios from 'axios';

window.axios = axios;

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
const xsrfToken = document.cookie
  .split('; ')
  .find((row) => row.startsWith('XSRF-TOKEN='))
  ?.split('=')[1];

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
window.axios.defaults.headers.common['X-XSRF-TOKEN'] = xsrfToken || '';
