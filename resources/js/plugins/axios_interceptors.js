import { alertDanger } from './alerts.js';

let handlingForbidden = false;

export function registerAxiosInterceptors(router) {
    window.axios.interceptors.response.use(
        response => response,

        async error => {
            const status = error.response?.status;

            if (status === 403) {
                error.isGloballyHandled = true;

                const message =
                    error.response?.data?.message ??
                    'Você não tem permissão para acessar essa funcionalidade.';

                if (!handlingForbidden) {
                    handlingForbidden = true;

                    try {
                        alertDanger(message);

                        if (router.currentRoute.value.name !== 'dashboard') {
                            await router.replace({
                                name: 'dashboard'
                            });
                        }
                    } finally {
                        setTimeout(() => {
                            handlingForbidden = false;
                        }, 500);
                    }
                }
            }

            return Promise.reject(error);
        }
    );
}