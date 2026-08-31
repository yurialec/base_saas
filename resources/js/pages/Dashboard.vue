<template>
    <section class="dashboard-page">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
            <div>
                <h1 class="h4 mb-0">Dashboard</h1>
            </div>
        </div>

        <button class="btn btn-danger btn-sm d-inline-flex align-items-center justify-content-center gap-2 mb-4"
            type="button" @click="handleDelete">
            <i class="bi bi-trash"></i>
            <span>Excluir item</span>
        </button>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pb-0">
                <h2 class="h6 mb-0">Teste de alertas</h2>
            </div>

            <div class="card-body">
                <div class="d-flex flex-column flex-sm-row gap-2">
                    <button class="btn btn-success btn-sm d-inline-flex align-items-center justify-content-center gap-2"
                        type="button" @click="showSuccessAlert">
                        <i class="bi bi-check-lg"></i>
                        <span>Alert sucesso</span>
                    </button>

                    <button class="btn btn-danger btn-sm d-inline-flex align-items-center justify-content-center gap-2"
                        type="button" @click="showDangerAlert">
                        <i class="bi bi-exclamation-triangle"></i>
                        <span>Alert erro</span>
                    </button>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
import Swal from 'sweetalert2';
export default {
    name: 'Dashboard',
    methods: {
        handleDelete() {
            this.confirm(
                'Essa ação não poderá ser desfeita.',
                'Deseja excluir este item?'
            ).then(() => {
                axios.delete(`/api/registros/123456`)
                    .then(() => {
                        alertSuccess('Registro excluído com sucesso!');
                    })
                    .catch(() => {
                        alertDanger([
                            'Não foi possível concluir a operação.',
                            'Verifique os dados e tente novamente.'
                        ]);
                    });
            });
        },
        showSuccessAlert() {
            alertSuccess('Registro salvo com sucesso!');
        },
        showDangerAlert() {
            alertDanger([
                'Não foi possível concluir a operação.',
                'Verifique os dados e tente novamente.'
            ]);
        }
    }
};
</script>