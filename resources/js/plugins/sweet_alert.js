import Swal from 'sweetalert2';

const SweetAlertPlugin = {
    install(app) {
        app.config.globalProperties.confirm = function (
            text,
            title = 'Confirmação',
            options = {}
        ) {
            return {
                then(callback) {
                    Swal.fire({
                        title,
                        text,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Confirmar',
                        cancelButtonText: 'Cancelar',
                        reverseButtons: true,
                        ...options
                    }).then((result) => {
                        if (result.isConfirmed) {
                            callback();
                        }
                    });
                }
            };
        };
    }
};

export default SweetAlertPlugin;