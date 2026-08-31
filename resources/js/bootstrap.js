window._ = require('lodash');

try {
    window.Popper = require('popper.js').default;
    window.$ = window.jQuery = require('jquery');

    require('bootstrap');
} catch (e) { }

/**
 * Axios
 */
window.axios = require('axios');

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common['Accept'] = 'application/json';
window.axios.defaults.withCredentials = true;

/**
 * CSRF Laravel
 */
const token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}

/**
 * Tratamento global das respostas
 */
window.axios.interceptors.response.use(
    response => response,

    error => {
        const status = error.response?.status;

        if (status === 401) {
            console.error('API retornou 401 - usuário não autenticado.');
            console.error(error.response?.data);
        }

        if (status === 419) {
            console.error('API retornou 419 - sessão ou CSRF expirado.');
            console.error(error.response?.data);
        }

        return Promise.reject(error);
    }
);