<template>
    <section class="page">
        <Loading :loading="isLoading" />

        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
            <div>
                <h1 class="h4 mb-0">Editar Permissão</h1>
            </div>
            <router-link
                class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-2 align-self-start align-self-lg-auto"
                :to="{ name: 'permissions' }">
                <i class="bi bi-arrow-left"></i>
                <span>Voltar</span>
            </router-link>
        </div>

        <form class="row g-4 justify-content-center" @submit.prevent="update">
            <div class="col-12 col-xl-8">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 pb-0">
                        <h2 class="h6 mb-0">Dados da Permissão</h2>
                    </div>
                    <div class="card-body bg-white">
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label">Nome</label>
                                <input v-model="permission.name" class="form-control" type="text" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label">Slug</label>
                                <input v-model="permission.slug" class="form-control" type="text" required>
                            </div>
                            <div class="col-12 col-md-12">
                                <label class="form-label">Descrição</label>
                                <textarea v-model="permission.description" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary btn-sm text-white" :disabled="isLoading">
                                    <span v-if="isLoading">
                                        <span class="spinner-border spinner-border-sm" role="status"
                                            aria-hidden="true"></span>
                                        <span class="visually-hidden">Loading...</span>
                                    </span>
                                    <span v-else>Salvar</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </section>
</template>

<script>
export default {
    name: 'EditPermission',
    props: {
        id: {
            type: [String, Number],
            required: true
        }
    },
    data() {
        return {
            isLoading: false,
            permission: {
                name: '',
                slug: '',
                description: ''
            }
        };
    },
    mounted() {
        this.find();
    },
    methods: {
        find() {
            this.isLoading = true;

            axios.get(`/api/permissions/find/${this.id}`)
                .then(response => {
                    this.permission = response.data;
                })
                .catch(error => {
                    alertDanger(error);
                })
                .finally(() => {
                    this.isLoading = false;
                });
        },
        update() {
            this.isLoading = true;

            axios.put(`/api/permissions/update/${this.id}`, this.permission)
                .then(response => {
                    this.$router.push({ name: 'permissions' });
                })
                .catch(error => {
                    alertDanger(error);
                })
                .finally(() => {
                    this.isLoading = false;
                });
        }
    }
};
</script>

<style scoped>
.page .card {
    border-radius: 0.5rem;
}
</style>
