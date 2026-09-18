window._ = require('lodash');

try {
    window.Popper = require('popper.js').default;
    window.$ = window.jQuery = require('jquery');

    require('bootstrap');
} catch (e) {}

/**
 * App
 */
window.App = window.App || {};

const tenant = window.App.tenant || null;
const permissions = window.App.permissions || [];

/**
 * Axios
 */
window.axios = require('axios');

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common['Accept'] = 'application/json';
window.axios.defaults.withCredentials = true;

/**
 * Tenant
 */
if (tenant?.slug) {
    window.axios.defaults.baseURL = `/api/${tenant.slug}`;
}

/**
 * Permissions
 */
window.App.permissions = permissions.map(permission => permission.slug);

/**
 * CSRF Laravel
 */
const token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}
